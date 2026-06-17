<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Env;
use App\Core\Redirect;
use App\Core\Session;
use App\Models\Ballot;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\ElectionVoter;
use Throwable;
use App\Models\AuditLog;

class VotingController extends Controller
{
    public function index(): void
    {
        Auth::requireRole(['superadmin', 'panitia']);

        $elections = Election::openElections();

        $this->view('voting_tps/index', [
            'title' => 'Voting TPS',
            'elections' => $elections,
        ]);
    }

    public function searchVoter(string $electionId): void
    {
        Auth::requireRole(['superadmin', 'panitia']);

        $election = Election::find((int) $electionId);

        if (!$election) {
            http_response_code(404);
            die('Data pemilihan tidak ditemukan.');
        }

        if ($election['status'] !== 'open') {
            Session::flash('error', 'Voting TPS hanya bisa digunakan saat status pemilihan OPEN.');
            Redirect::to('/tps-voting');
        }

        $keyword = trim($_GET['q'] ?? '');
        $results = [];

        if ($keyword !== '') {
            $results = ElectionVoter::searchForVoting((int) $electionId, $keyword);
        }

        $this->view('voting_tps/search', [
            'title' => 'Cari Pemilih TPS',
            'election' => $election,
            'keyword' => $keyword,
            'results' => $results,
        ]);
    }

    public function ballot(string $electionId, string $electionVoterId): void
    {
        Auth::requireRole(['superadmin', 'panitia']);

        $election = Election::find((int) $electionId);

        if (!$election) {
            http_response_code(404);
            die('Data pemilihan tidak ditemukan.');
        }

        if ($election['status'] !== 'open') {
            Session::flash('error', 'Pemilihan belum dibuka atau sudah ditutup.');
            Redirect::to('/tps-voting');
        }

        $electionVoter = ElectionVoter::findDetailForVoting((int) $electionId, (int) $electionVoterId);

        if (!$electionVoter) {
            http_response_code(404);
            die('Data pemilih tidak ditemukan dalam pemilihan ini.');
        }

        if ((int) $electionVoter['is_active'] !== 1) {
            Session::flash('error', 'Pemilih ini nonaktif di master data.');
            Redirect::to('/elections/' . $electionId . '/tps-voting');
        }

        if ((int) $electionVoter['has_voted'] === 1) {
            Session::flash('error', 'Pemilih ini sudah mencoblos.');
            Redirect::to('/elections/' . $electionId . '/tps-voting');
        }

        if (!in_array($electionVoter['allowed_channel'], ['tps', 'both'], true)) {
            Session::flash('error', 'Pemilih ini tidak diizinkan mencoblos melalui TPS.');
            Redirect::to('/elections/' . $electionId . '/tps-voting');
        }

        $candidates = Candidate::activeByElection((int) $electionId);

        if (empty($candidates)) {
            Session::flash('error', 'Belum ada kandidat aktif untuk pemilihan ini.');
            Redirect::to('/elections/' . $electionId . '/tps-voting');
        }

        $this->view('voting_tps/ballot', [
            'title' => 'Halaman Coblos',
            'election' => $election,
            'electionVoter' => $electionVoter,
            'candidates' => $candidates,
        ]);
    }

    public function submit(string $electionId, string $electionVoterId): void
    {
        Auth::requireRole(['superadmin', 'panitia']);
        Csrf::verify();

        $candidateId = (int) ($_POST['candidate_id'] ?? 0);

        if ($candidateId <= 0) {
            Session::flash('error', 'Pilih salah satu kandidat.');
            Redirect::to('/elections/' . $electionId . '/tps-voting/' . $electionVoterId . '/ballot');
        }

        $pdo = Database::connection();

        try {
            $pdo->beginTransaction();

            $election = Election::lockById($pdo, (int) $electionId);

            if (!$election) {
                $pdo->rollBack();
                http_response_code(404);
                die('Data pemilihan tidak ditemukan.');
            }

            if ($election['status'] !== 'open') {
                $pdo->rollBack();
                Session::flash('error', 'Pemilihan tidak sedang dibuka.');
                Redirect::to('/tps-voting');
            }

            $electionVoter = ElectionVoter::lockForVoting($pdo, (int) $electionId, (int) $electionVoterId);

            if (!$electionVoter) {
                $pdo->rollBack();
                Session::flash('error', 'Pemilih tidak ditemukan dalam daftar pemilihan.');
                Redirect::to('/elections/' . $electionId . '/tps-voting');
            }

            if ((int) $electionVoter['is_active'] !== 1) {
                $pdo->rollBack();
                Session::flash('error', 'Pemilih ini nonaktif.');
                Redirect::to('/elections/' . $electionId . '/tps-voting');
            }

            if ((int) $electionVoter['has_voted'] === 1) {
                $pdo->rollBack();
                Session::flash('error', 'Pemilih ini sudah mencoblos.');
                Redirect::to('/elections/' . $electionId . '/tps-voting');
            }

            if (!in_array($electionVoter['allowed_channel'], ['tps', 'both'], true)) {
                $pdo->rollBack();
                Session::flash('error', 'Pemilih ini tidak diizinkan mencoblos melalui TPS.');
                Redirect::to('/elections/' . $electionId . '/tps-voting');
            }

            $candidate = Candidate::findActiveForVote($pdo, (int) $electionId, $candidateId);

            if (!$candidate) {
                $pdo->rollBack();
                Session::flash('error', 'Kandidat tidak valid.');
                Redirect::to('/elections/' . $electionId . '/tps-voting/' . $electionVoterId . '/ballot');
            }

            Ballot::create($pdo, (int) $electionId, $candidateId, 'tps');

            ElectionVoter::markVoted($pdo, (int) $electionVoterId);

            $pdo->commit();

            AuditLog::record(
                'vote_tps_success',
                'Suara TPS berhasil disimpan untuk election ID ' . $electionId . '.'
            );

            Session::flash('success', 'Suara berhasil disimpan. Terima kasih.');
            Redirect::to('/elections/' . $electionId . '/tps-voting/success');

        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            if (Env::get('APP_DEBUG') === 'true') {
                die('Voting gagal: ' . $e->getMessage());
            }

            Session::flash('error', 'Voting gagal diproses.');
            Redirect::to('/elections/' . $electionId . '/tps-voting');
        }
    }

    public function success(string $electionId): void
    {
        Auth::requireRole(['superadmin', 'panitia']);

        $election = Election::find((int) $electionId);

        if (!$election) {
            http_response_code(404);
            die('Data pemilihan tidak ditemukan.');
        }

        $this->view('voting_tps/success', [
            'title' => 'Voting Berhasil',
            'election' => $election,
        ]);
    }
}