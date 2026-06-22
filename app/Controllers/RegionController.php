<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Redirect;
use App\Core\Session;
use App\Models\AuditLog;
use App\Models\Organization;
use App\Models\Region;

class RegionController extends Controller
{
    public function index(): void
    {
        Auth::requirePermission('manage_regions');

        $regions = Region::all();

        $regionRows = array_map(function (array $region): array {
            $usedCount =
                (int) ($region['total_users'] ?? 0)
                + (int) ($region['total_voters'] ?? 0)
                + (int) ($region['total_elections'] ?? 0);

            $region['used_count'] = $usedCount;
            $region['can_delete'] = (int) ($region['total_children'] ?? 0) === 0 && $usedCount === 0;

            return $region;
        }, $regions);

        $this->view('regions/index', [
            'title' => 'Region Management',
            'regions' => $regionRows,
        ]);
    }

    public function create(): void
    {
        Auth::requirePermission('manage_regions');

        $formOptions = $this->regionFormOptions();

        $this->view('regions/create', [
            'title' => 'Tambah Wilayah',
            'organizations' => $formOptions['organizations'],
            'regions' => $formOptions['regions'],
            'levels' => $formOptions['levels'],
        ]);
    }

    private function regionFormOptions(?int $excludeRegionId = null, ?int $organizationId = null): array
    {
        $levels = Region::levels();

        $organizations = array_map(function (array $organization): array {
            $organization['display_label'] = $organization['name'] . ' (' . $organization['type'] . ')';

            return $organization;
        }, Organization::options());

        $regions = array_map(function (array $region) use ($levels): array {
            $levelLabel = $levels[$region['level']] ?? strtoupper((string) $region['level']);

            $region['display_label'] =
                '[' . $region['organization_name'] . '] '
                . $levelLabel
                . ' - '
                . $region['code']
                . ' - '
                . $region['name'];

            return $region;
        }, Region::options($organizationId, $excludeRegionId));

        return [
            'organizations' => $organizations,
            'regions' => $regions,
            'levels' => $levels,
        ];
    }

    public function store(): void
    {
        Auth::requirePermission('manage_regions');
        Csrf::verify();

        $organizationId = (int) ($_POST['organization_id'] ?? 0);
        $parentId = !empty($_POST['parent_id']) ? (int) $_POST['parent_id'] : null;
        $level = trim($_POST['level'] ?? '');
        $code = $this->normalizeCode($_POST['code'] ?? '');
        $name = trim($_POST['name'] ?? '');

        $organization = Organization::find($organizationId);

        if (!$organization) {
            Session::flash('error', 'Organization tidak valid.');
            Redirect::to('/regions/create');
        }

        if (!array_key_exists($level, Region::levels())) {
            Session::flash('error', 'Level wilayah tidak valid.');
            Redirect::to('/regions/create');
        }

        if ($code === '') {
            Session::flash('error', 'Kode wilayah wajib diisi.');
            Redirect::to('/regions/create');
        }

        if ($name === '') {
            Session::flash('error', 'Nama wilayah wajib diisi.');
            Redirect::to('/regions/create');
        }

        if (Region::codeExists($organizationId, $code)) {
            Session::flash('error', 'Kode wilayah sudah digunakan dalam organization ini.');
            Redirect::to('/regions/create');
        }

        if ($parentId) {
            $parent = Region::find($parentId);

            if (!$parent) {
                Session::flash('error', 'Parent wilayah tidak valid.');
                Redirect::to('/regions/create');
            }

            if ((int) $parent['organization_id'] !== $organizationId) {
                Session::flash('error', 'Parent wilayah harus berada dalam organization yang sama.');
                Redirect::to('/regions/create');
            }
        }

        Region::create([
            'organization_id' => $organizationId,
            'parent_id' => $parentId,
            'level' => $level,
            'code' => $code,
            'name' => $name,
        ]);

        AuditLog::record(
            'region_create',
            'Membuat wilayah: ' . $code . ' - ' . $name
        );

        Session::flash('success', 'Wilayah berhasil ditambahkan.');
        Redirect::to('/regions');
    }

    public function edit(string $id): void
    {
        Auth::requirePermission('manage_regions');

        $region = Region::find((int) $id);

        if (!$region) {
            http_response_code(404);
            die('Wilayah tidak ditemukan.');
        }

        $formOptions = $this->regionFormOptions(
            (int) $id,
            (int) $region['organization_id']
        );

        $this->view('regions/edit', [
            'title' => 'Edit Wilayah',
            'region' => $region,
            'organizations' => $formOptions['organizations'],
            'regions' => $formOptions['regions'],
            'levels' => $formOptions['levels'],
        ]);
    }

    public function update(string $id): void
    {
        Auth::requirePermission('manage_regions');
        Csrf::verify();

        $region = Region::find((int) $id);

        if (!$region) {
            http_response_code(404);
            die('Wilayah tidak ditemukan.');
        }

        $organizationId = (int) ($_POST['organization_id'] ?? 0);
        $parentId = !empty($_POST['parent_id']) ? (int) $_POST['parent_id'] : null;
        $level = trim($_POST['level'] ?? '');
        $code = $this->normalizeCode($_POST['code'] ?? '');
        $name = trim($_POST['name'] ?? '');

        $organization = Organization::find($organizationId);

        if (!$organization) {
            Session::flash('error', 'Organization tidak valid.');
            Redirect::to('/regions/' . $id . '/edit');
        }

        if (!array_key_exists($level, Region::levels())) {
            Session::flash('error', 'Level wilayah tidak valid.');
            Redirect::to('/regions/' . $id . '/edit');
        }

        if ($code === '') {
            Session::flash('error', 'Kode wilayah wajib diisi.');
            Redirect::to('/regions/' . $id . '/edit');
        }

        if ($name === '') {
            Session::flash('error', 'Nama wilayah wajib diisi.');
            Redirect::to('/regions/' . $id . '/edit');
        }

        if (Region::codeExists($organizationId, $code, (int) $id)) {
            Session::flash('error', 'Kode wilayah sudah digunakan dalam organization ini.');
            Redirect::to('/regions/' . $id . '/edit');
        }

        if ($parentId) {
            if ($parentId === (int) $id) {
                Session::flash('error', 'Wilayah tidak boleh menjadi parent dirinya sendiri.');
                Redirect::to('/regions/' . $id . '/edit');
            }

            $parent = Region::find($parentId);

            if (!$parent) {
                Session::flash('error', 'Parent wilayah tidak valid.');
                Redirect::to('/regions/' . $id . '/edit');
            }

            if ((int) $parent['organization_id'] !== $organizationId) {
                Session::flash('error', 'Parent wilayah harus berada dalam organization yang sama.');
                Redirect::to('/regions/' . $id . '/edit');
            }

            if (Region::isDescendantOf($parentId, (int) $id)) {
                Session::flash('error', 'Parent tidak boleh berasal dari child wilayah ini.');
                Redirect::to('/regions/' . $id . '/edit');
            }
        }

        Region::update((int) $id, [
            'organization_id' => $organizationId,
            'parent_id' => $parentId,
            'level' => $level,
            'code' => $code,
            'name' => $name,
        ]);

        AuditLog::record(
            'region_update',
            'Memperbarui wilayah ID ' . $id . ': ' . $code . ' - ' . $name
        );

        Session::flash('success', 'Wilayah berhasil diperbarui.');
        Redirect::to('/regions');
    }

    public function destroy(string $id): void
    {
        Auth::requirePermission('manage_regions');
        Csrf::verify();

        $region = Region::find((int) $id);

        if (!$region) {
            http_response_code(404);
            die('Wilayah tidak ditemukan.');
        }

        if (Region::countChildren((int) $id) > 0) {
            Session::flash('error', 'Wilayah tidak bisa dihapus karena masih punya child wilayah.');
            Redirect::to('/regions');
        }

        $usage = Region::usageCount((int) $id);

        if ($usage['users'] > 0 || $usage['voters'] > 0 || $usage['elections'] > 0) {
            Session::flash('error', 'Wilayah tidak bisa dihapus karena sudah digunakan user, pemilih, atau pemilihan.');
            Redirect::to('/regions');
        }

        Region::delete((int) $id);

        AuditLog::record(
            'region_delete',
            'Menghapus wilayah ID ' . $id . ': ' . $region['code'] . ' - ' . $region['name']
        );

        Session::flash('success', 'Wilayah berhasil dihapus.');
        Redirect::to('/regions');
    }

    private function normalizeCode(string $code): string
    {
        $code = strtoupper(trim($code));
        $code = preg_replace('/\s+/', '-', $code);
        $code = preg_replace('/[^A-Z0-9_-]/', '', $code);

        return $code;
    }
}