<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Redirect;
use App\Core\Session;
use App\Models\Election;
use App\Models\ElectionVoter;
use App\Models\Voter;
use App\Models\AuditLog;
use App\Core\RegionScope;

class ElectionVoterController extends Controller
{
    public function index(string $electionId): void
    {
        Auth::requirePermission('assign_voters');

        $election = Election::find((int) $electionId);

        if (!$election) {
            http_response_code(404);
            die('Data pemilihan tidak ditemukan.');
        }

        $electionVoters = ElectionVoter::byElection((int) $electionId);

        $totalVoters = count($electionVoters);
        $totalVoted = 0;

        foreach ($electionVoters as $item) {
            if ((int) ($item['has_voted'] ?? 0) === 1) {
                $totalVoted++;
            }
        }

        $canModifyVoters = Auth::can('assign_voters') && ($election['status'] ?? '') === 'draft';

        $rows = array_map(function (array $item) use ($canModifyVoters): array {
            $item['can_manage_row'] = $canModifyVoters && (int) ($item['has_voted'] ?? 0) !== 1;
            $item['is_master_inactive'] = (int) ($item['is_active'] ?? 0) !== 1;

            return $item;
        }, $electionVoters);

        $this->view('election_voters/index', [
            'title' => 'Daftar Pemilih Pemilihan',
            'election' => $election,
            'electionVoters' => $rows,
            'totalVoters' => $totalVoters,
            'totalVoted' => $totalVoted,
            'totalNotVoted' => $totalVoters - $totalVoted,
            'isElectionLocked' => ($election['status'] ?? '') !== 'draft',
            'canModifyVoters' => $canModifyVoters,
            'channelOptions' => [
                'tps' => 'TPS',
                'remote' => 'Remote',
                'both' => 'TPS / Remote',
            ],
        ]);
    }

    public function create(string $electionId): void
    {
        Auth::requirePermission('assign_voters');

        $election = Election::find((int) $electionId);

        if (!$election) {
            http_response_code(404);
            die('Data pemilihan tidak ditemukan.');
        }

        if ($election['status'] !== 'draft') {
            Session::flash('error', 'Pemilih hanya boleh ditambahkan saat status pemilihan masih draft.');
            Redirect::to('/elections/' . $electionId . '/voters');
        }

        $availableVoters = ElectionVoter::availableVoters((int) $electionId);
        $voters = Voter::availableForElection($election);

        /* $this->view('election_voters/create', [
            'title' => 'Tambah Pemilih ke Pemilihan',
            'election' => $election,
            'availableVoters' => $availableVoters,
        ]); */

        $this->view('election_voters/create', [
            'title' => 'Tambah Pemilih Pemilihan',
            'election' => $election,
            'voters' => $voters,
            'channelOptions' => [
                'tps' => 'TPS',
                'remote' => 'Remote',
                'both' => 'TPS / Remote',
            ],
        ]);
    }

    public function store(string $electionId): void
    {
        Auth::requirePermission('assign_voters');
        Csrf::verify();

        $election = Election::find((int) $electionId);

        if (!$election) {
            http_response_code(404);
            die('Data pemilihan tidak ditemukan.');
        }

        if ($election['status'] !== 'draft') {
            Session::flash('error', 'Pemilih hanya boleh ditambahkan saat status pemilihan masih draft.');
            Redirect::to('/elections/' . $electionId . '/voters');
        }

        $voterIds = $_POST['voter_ids'] ?? [];
        $allowedChannel = $_POST['allowed_channel'] ?? 'tps';

        if (!in_array($allowedChannel, ['tps', 'remote', 'both'], true)) {
            Session::flash('error', 'Channel pemilih tidak valid.');
            Redirect::to('/elections/' . $electionId . '/voters/create');
        }

        if (!is_array($voterIds) || empty($voterIds)) {
            Session::flash('error', 'Pilih minimal satu pemilih.');
            Redirect::to('/elections/' . $electionId . '/voters/create');
        }

        $successCount = 0;
        $skippedCount = 0;

        foreach ($voterIds as $voterId) {
            $voterId = (int) $voterId;

            if ($voterId <= 0) {
                $skippedCount++;
                continue;
            }

            $voter = Voter::find($voterId);

            if (!$voter || (int) $voter['is_active'] !== 1) {
                $skippedCount++;
                continue;
            }

            if (ElectionVoter::exists((int) $electionId, $voterId)) {
                $skippedCount++;
                continue;
            }

            ElectionVoter::create((int) $electionId, $voterId, $allowedChannel);
            $successCount++;
        }

        if ($successCount > 0) {
            AuditLog::record(
                'election_voter_assign',
                'Menambahkan ' . $successCount . ' pemilih ke election ID ' . $electionId . ' dengan channel ' . $allowedChannel . '. Dilewati: ' . $skippedCount
            );

            Session::flash('success', "{$successCount} pemilih berhasil ditambahkan. {$skippedCount} data dilewati.");
        } else {
            Session::flash('error', 'Tidak ada pemilih yang berhasil ditambahkan.');
        }

        $voter = Voter::find((int) $voterId);

        if (!$voter) {
            Session::flash('error', 'Pemilih tidak ditemukan atau berada di luar scope wilayah Anda.');
            Redirect::to('/elections/' . $electionId . '/voters/create');
        }

        $targetRegionId = !empty($election['region_id']) ? (int) $election['region_id'] : null;

        if (!RegionScope::canAccessRegionInTarget((int) ($voter['region_id'] ?? 0), $targetRegionId)) {
            Session::flash('error', 'Pemilih ini tidak berada dalam wilayah pemilihan atau di luar scope Anda.');
            Redirect::to('/elections/' . $electionId . '/voters/create');
        }

        if (!empty($election['organization_id']) && (int) ($voter['organization_id'] ?? 0) !== (int) $election['organization_id']) {
            Session::flash('error', 'Pemilih ini berbeda organization dengan pemilihan.');
            Redirect::to('/elections/' . $electionId . '/voters/create');
        }


        Redirect::to('/elections/' . $electionId . '/voters');
    }

    public function updateChannel(string $electionId, string $id): void
    {
        Auth::requirePermission('assign_voters');
        Csrf::verify();

        $election = Election::find((int) $electionId);

        if (!$election) {
            http_response_code(404);
            die('Data pemilihan tidak ditemukan.');
        }

        $row = ElectionVoter::findScopedForElection(
            (int) $electionId,
            (int) $id,
            !empty($election['region_id']) ? (int) $election['region_id'] : null
        );

        if (!$row) {
            http_response_code(404);
            die('Data pemilih pemilihan tidak ditemukan atau di luar scope.');
        }

        if ($election['status'] !== 'draft') {
            Session::flash('error', 'Channel pemilih hanya boleh diubah saat status pemilihan masih draft.');
            Redirect::to('/elections/' . $electionId . '/voters');
        }

        $electionVoter = ElectionVoter::findByElection((int) $electionId, (int) $id);

        if (!$electionVoter) {
            http_response_code(404);
            die('Data pemilih pemilihan tidak ditemukan.');
        }

        if ((int) $electionVoter['has_voted'] === 1) {
            Session::flash('error', 'Channel tidak boleh diubah karena pemilih sudah mencoblos.');
            Redirect::to('/elections/' . $electionId . '/voters');
        }

        $allowedChannel = $_POST['allowed_channel'] ?? 'tps';

        if (!in_array($allowedChannel, ['tps', 'remote', 'both'], true)) {
            Session::flash('error', 'Channel tidak valid.');
            Redirect::to('/elections/' . $electionId . '/voters');
        }

        ElectionVoter::updateChannel((int) $id, $allowedChannel);

        AuditLog::record(
            'election_voter_channel_update',
            'Mengubah channel election_voter ID ' . $id . ' pada election ID ' . $electionId . ' menjadi ' . $allowedChannel
        );

        Session::flash('success', 'Channel pemilih berhasil diperbarui.');
        Redirect::to('/elections/' . $electionId . '/voters');
    }

    public function destroy(string $electionId, string $id): void
    {
        Auth::requirePermission('assign_voters');
        Csrf::verify();

        $election = Election::find((int) $electionId);

        if (!$election) {
            http_response_code(404);
            die('Data pemilihan tidak ditemukan.');
        }

        $row = ElectionVoter::findScopedForElection(
            (int) $electionId,
            (int) $id,
            !empty($election['region_id']) ? (int) $election['region_id'] : null
        );

        if (!$row) {
            http_response_code(404);
            die('Data pemilih pemilihan tidak ditemukan atau di luar scope.');
        }

        if ($election['status'] !== 'draft') {
            Session::flash('error', 'Pemilih hanya boleh dihapus dari pemilihan saat status masih draft.');
            Redirect::to('/elections/' . $electionId . '/voters');
        }

        $electionVoter = ElectionVoter::findByElection((int) $electionId, (int) $id);

        if (!$electionVoter) {
            http_response_code(404);
            die('Data pemilih pemilihan tidak ditemukan.');
        }

        if ((int) $electionVoter['has_voted'] === 1) {
            Session::flash('error', 'Pemilih tidak boleh dihapus karena sudah mencoblos.');
            Redirect::to('/elections/' . $electionId . '/voters');
        }

        ElectionVoter::delete((int) $id);

        AuditLog::record(
            'election_voter_remove',
            'Menghapus election_voter ID ' . $id . ' dari election ID ' . $electionId
        );

        Session::flash('success', 'Pemilih berhasil dihapus dari daftar pemilihan.');
        Redirect::to('/elections/' . $electionId . '/voters');
    }
}