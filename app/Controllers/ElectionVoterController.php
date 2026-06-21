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

class ElectionVoterController extends Controller
{
    public function index(string $electionId): void
    {
        Auth::requireLogin();

        $election = Election::find((int) $electionId);

        if (!$election) {
            http_response_code(404);
            die('Data pemilihan tidak ditemukan.');
        }

        $electionVoters = ElectionVoter::allByElection((int) $electionId);
        $totalVoters = ElectionVoter::countByElection((int) $electionId);
        $totalVoted = ElectionVoter::countVotedByElection((int) $electionId);

        $this->view('election_voters/index', [
            'title' => 'Daftar Pemilih Pemilihan',
            'election' => $election,
            'electionVoters' => $electionVoters,
            'totalVoters' => $totalVoters,
            'totalVoted' => $totalVoted,
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

        $this->view('election_voters/create', [
            'title' => 'Tambah Pemilih ke Pemilihan',
            'election' => $election,
            'availableVoters' => $availableVoters,
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