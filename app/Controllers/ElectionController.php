<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Redirect;
use App\Core\Session;
use App\Core\RegionScope;
use App\Models\Election;
use App\Models\AuditLog;
use App\Models\Organization;
use App\Models\Region;

class ElectionController extends Controller
{
    public function index(): void
    {
        Auth::requirePermission('manage_elections');

        $elections = Election::allScoped();

        $this->view('elections/index', [
            'title' => 'Data Pemilihan',
            'elections' => $elections,
        ]);
    }

    public function create(): void
    {
        Auth::requirePermission('manage_elections');

        $formOptions = $this->electionFormOptions();

        $this->view('elections/create', [
            'title' => 'Tambah Pemilihan',
            'organizations' => $formOptions['organizations'],
            'regions' => $formOptions['regions'],
            'statusOptions' => $formOptions['statusOptions'],
            'isSuperadmin' => $formOptions['isSuperadmin'],
        ]);
    }

    public function store(): void
    {
        Auth::requirePermission('manage_elections');
        Csrf::verify();

        $data = $this->normalizeElectionPayload('/elections/create');

        $title = trim($_POST['title'] ?? '');
        $organizationId = !empty($_POST['organization_id']) ? (int) $_POST['organization_id'] : null;
        $regionId = !empty($_POST['region_id']) ? (int) $_POST['region_id'] : null;
        $description = trim($_POST['description'] ?? '');
        $status = $_POST['status'] ?? 'draft';
        $startAt = $this->normalizeDateTime($_POST['start_at'] ?? null);
        $endAt = $this->normalizeDateTime($_POST['end_at'] ?? null);

        if ($title === '') {
            Session::flash('error', 'Nama pemilihan wajib diisi.');
            Redirect::to('/elections/create');
        }

        if (!in_array($status, ['draft', 'open', 'closed', 'finished'], true)) {
            Session::flash('error', 'Status pemilihan tidak valid.');
            Redirect::to('/elections/create');
        }

        if ($startAt && $endAt && strtotime($startAt) > strtotime($endAt)) {
            Session::flash('error', 'Tanggal mulai tidak boleh lebih besar dari tanggal selesai.');
            Redirect::to('/elections/create');
        }

        if ($regionId) {
            $region = Region::find($regionId);

            if (!$region) {
                Session::flash('error', 'Wilayah tidak valid.');
                Redirect::to('/elections/create');
            }

            if ($organizationId && (int) $region['organization_id'] !== (int) $organizationId) {
                Session::flash('error', 'Wilayah tidak sesuai dengan organization.');
                Redirect::to('/elections/create');
            }

            if (!$organizationId) {
                $organizationId = (int) $region['organization_id'];
            }
        }

        if ($organizationId && !Organization::find($organizationId)) {
            Session::flash('error', 'Organization tidak valid.');
            Redirect::to('/elections/create');
        }

        Election::create([
            'title' => $title,
            'organization_id' => $organizationId,
            'region_id' => $regionId,
            'description' => $description !== '' ? $description : null,
            'status' => $status,
            'start_at' => $startAt,
            'end_at' => $endAt,
            'created_by' => Auth::id(),
        ]);

        AuditLog::record(
            'election_create',
            'Membuat pemilihan: ' . $title . ' dengan status ' . $status
        );

        Session::flash('success', 'Data pemilihan berhasil ditambahkan.');
        Redirect::to('/elections');
    }

    public function edit(string $id): void
    {
        Auth::requirePermission('manage_elections');

        $election = $this->electionOr404($id);

        if (!RegionScope::canAccessRegion((int) ($election['region_id'] ?? 0))) {
            http_response_code(404);
            die('Pemilihan berada di luar scope wilayah Anda.');
        }

        $formOptions = $this->electionFormOptions();

        $this->view('elections/edit', [
            'title' => 'Edit Pemilihan',
            'election' => $election,
            'organizations' => $formOptions['organizations'],
            'regions' => $formOptions['regions'],
            'statusOptions' => $formOptions['statusOptions'],
            'isSuperadmin' => $formOptions['isSuperadmin'],
        ]);
    }

    private function isSuperadmin(): bool
    {
        return Auth::role() === 'superadmin';
    }

    private function electionFormOptions(): array
    {
        $currentUser = Auth::user();

        $organizations = Organization::options();
        $regions = Region::optionsScoped();

        if (!$this->isSuperadmin()) {
            $currentOrganizationId = (int) ($currentUser['organization_id'] ?? 0);

            $organizations = array_values(array_filter(
                $organizations,
                fn (array $organization): bool => (int) $organization['id'] === $currentOrganizationId
            ));
        }

        $organizations = array_map(function (array $organization): array {
            $organization['display_label'] = $organization['name'] . ' (' . $organization['type'] . ')';

            return $organization;
        }, $organizations);

        $regions = array_map(function (array $region): array {
            $region['display_label'] =
                $region['code']
                . ' - '
                . $region['name']
                . ' / '
                . strtoupper((string) $region['level'])
                . ' ('
                . ($region['organization_name'] ?? '-')
                . ')';

            return $region;
        }, $regions);

        return [
            'organizations' => $organizations,
            'regions' => $regions,
            'statusOptions' => [
                'draft' => 'Draft',
                'open' => 'Open',
                'closed' => 'Closed',
                'finished' => 'Finished',
            ],
            'isSuperadmin' => $this->isSuperadmin(),
        ];
    }

    private function normalizeElectionPayload(string $redirectUrl): array
    {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $status = $_POST['status'] ?? 'draft';

        $organizationId = !empty($_POST['organization_id']) ? (int) $_POST['organization_id'] : null;
        $regionId = !empty($_POST['region_id']) ? (int) $_POST['region_id'] : null;

        $startAt = trim($_POST['start_at'] ?? '');
        $endAt = trim($_POST['end_at'] ?? '');

        if ($title === '') {
            Session::flash('error', 'Nama pemilihan wajib diisi.');
            Redirect::to($redirectUrl);
        }

        if (!in_array($status, ['draft', 'open', 'closed', 'finished'], true)) {
            Session::flash('error', 'Status pemilihan tidak valid.');
            Redirect::to($redirectUrl);
        }

        if (!$regionId) {
            Session::flash('error', 'Wilayah pemilihan wajib dipilih.');
            Redirect::to($redirectUrl);
        }

        if (!RegionScope::canAccessRegion($regionId)) {
            Session::flash('error', 'Wilayah pemilihan berada di luar scope Anda.');
            Redirect::to($redirectUrl);
        }

        $region = Region::find($regionId);

        if (!$region) {
            Session::flash('error', 'Wilayah tidak ditemukan.');
            Redirect::to($redirectUrl);
        }

        // Organization wajib mengikuti region.
        $organizationId = (int) $region['organization_id'];

        if (!$this->isSuperadmin()) {
            $currentUser = Auth::user();
            $currentOrganizationId = (int) ($currentUser['organization_id'] ?? 0);

            if ($organizationId !== $currentOrganizationId) {
                Session::flash('error', 'Organization pemilihan berada di luar scope Anda.');
                Redirect::to($redirectUrl);
            }
        }

        return [
            'title' => $title,
            'description' => $description !== '' ? $description : null,
            'status' => $status,
            'organization_id' => $organizationId,
            'region_id' => $regionId,
            'start_at' => $startAt !== '' ? date('Y-m-d H:i:s', strtotime($startAt)) : null,
            'end_at' => $endAt !== '' ? date('Y-m-d H:i:s', strtotime($endAt)) : null,
        ];
    }

    public function update(string $id): void
    {
        Auth::requirePermission('manage_elections');
        Csrf::verify();

        $election = $this->electionOr404($id);

        $data = $this->normalizeElectionPayload('/elections/' . $id . '/edit');

        Election::update((int) $id, $data);

        AuditLog::record(
            'election_update',
            'Memperbarui pemilihan ID ' . $id . ': ' . ($data['title'] ?? $election['title']),
            null,
            [
                'election_id' => (int) $id,
                'organization_id' => $data['organization_id'] ?? ($election['organization_id'] ?? null),
                'region_id' => $data['region_id'] ?? ($election['region_id'] ?? null),
            ]
        );

        Session::flash('success', 'Pemilihan berhasil diperbarui.');
        Redirect::to('/elections');
    }

    public function destroy(string $id): void
    {
        Auth::requirePermission('manage_elections');
        Csrf::verify();

        $election = $this->electionOr404($id);

        if (($election['status'] ?? '') !== 'draft') {
            Session::flash('error', 'Pemilihan hanya bisa dihapus saat masih draft.');
            Redirect::to('/elections');
        }

        Election::delete((int) $id);

        AuditLog::record(
            'election_delete',
            'Menghapus pemilihan ID ' . $id . ': ' . ($election['title'] ?? '-'),
            null,
            [
                'election_id' => (int) $id,
                'organization_id' => $election['organization_id'] ?? null,
                'region_id' => $election['region_id'] ?? null,
            ]
        );

        Session::flash('success', 'Pemilihan berhasil dihapus.');
        Redirect::to('/elections');
    }

    public function changeStatus(string $id): void
    {
        Auth::requirePermission('manage_elections');
        Csrf::verify();

        $election = $this->electionOr404($id);

        $status = $_POST['status'] ?? '';

        if (!in_array($status, ['draft', 'open', 'closed', 'finished'], true)) {
            Session::flash('error', 'Status tidak valid.');
            Redirect::to('/elections');
        }

        Election::updateStatus((int) $id, $status);

        AuditLog::record(
            'election_status_update',
            'Mengubah status pemilihan ID ' . $id . ' dari ' . $election['status'] . ' ke ' . $status,
            null,
            [
                'election_id' => (int) $id,
                'organization_id' => $election['organization_id'] ?? null,
                'region_id' => $election['region_id'] ?? null,
            ]
        );

        Session::flash('success', 'Status pemilihan berhasil diubah.');
        Redirect::to('/elections');
    }

    private function normalizeDateTime(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        $timestamp = strtotime($value);

        if (!$timestamp) {
            return null;
        }

        return date('Y-m-d H:i:s', $timestamp);
    }

    private function electionOr404(string|int $id): array
    {
        $election = Election::findScoped((int) $id);

        if (!$election) {
            http_response_code(404);
            die('Pemilihan tidak ditemukan atau berada di luar scope wilayah Anda.');
        }

        return $election;
    }
}