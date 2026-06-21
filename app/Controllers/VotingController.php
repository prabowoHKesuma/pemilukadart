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
use App\Models\TpsBoothToken;

class VotingController extends Controller
{
    public function index(): void
    {
        Auth::requirePermission('tps_validate');

        $elections = Election::openElections();

        $this->view('voting_tps/index', [
            'title' => 'Voting TPS',
            'elections' => $elections,
        ]);
    }

    public function searchVoter(string $electionId): void
    {
        Auth::requirePermission('tps_validate');

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
            'title' => 'Validasi Pemilih TPS',
            'election' => $election,
            'keyword' => $keyword,
            'results' => $results,
            'generatedBoothCode' => Session::flash('tps_booth_code'),
            'generatedBoothVoter' => Session::flash('tps_booth_voter'),
            'generatedBoothExpiresAt' => Session::flash('tps_booth_expires_at'),
        ]);
    }

    public function ballot(string $electionId, string $electionVoterId): void
    {
        Auth::requirePermission('tps_validate');

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
            'title' => 'Halaman Coblos TPS',
            'election' => $election,
            'electionVoter' => $electionVoter,
            'candidates' => $candidates,
        ], 'tps');
    }

    public function submit(string $electionId, string $electionVoterId): void
    {
        Auth::requirePermission('tps_validate');
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
        Auth::requirePermission('tps_validate');

        $election = Election::find((int) $electionId);

        if (!$election) {
            http_response_code(404);
            die('Data pemilihan tidak ditemukan.');
        }

        $this->view('voting_tps/success', [
            'title' => 'Voting Berhasil',
            'election' => $election,
        ], 'tps');
    }

    public function generateBoothCode(string $electionId, string $electionVoterId): void
    {
        Auth::requirePermission('tps_validate');
        Csrf::verify();

        $election = Election::find((int) $electionId);

        if (!$election) {
            http_response_code(404);
            die('Data pemilihan tidak ditemukan.');
        }

        if ($election['status'] !== 'open') {
            Session::flash('error', 'Kode bilik hanya bisa dibuat saat pemilihan berstatus OPEN.');
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
            Redirect::to('/elections/' . $electionId . '/tps-voting?q=' . urlencode($electionVoter['voter_code']));
        }

        if (!in_array($electionVoter['allowed_channel'], ['tps', 'both'], true)) {
            Session::flash('error', 'Pemilih ini tidak diizinkan mencoblos melalui TPS.');
            Redirect::to('/elections/' . $electionId . '/tps-voting?q=' . urlencode($electionVoter['voter_code']));
        }

        $activeToken = TpsBoothToken::activeByElectionVoter((int) $electionVoterId);

        if ($activeToken) {
            Session::flash('error', 'Pemilih ini masih punya kode bilik aktif. Revoke dulu atau tunggu expired.');
            Redirect::to('/elections/' . $electionId . '/tps-voting?q=' . urlencode($electionVoter['voter_code']));
        }

        $expiresMinutes = (int) ($_POST['expires_minutes'] ?? 10);

        if ($expiresMinutes < 3) {
            $expiresMinutes = 3;
        }

        if ($expiresMinutes > 30) {
            $expiresMinutes = 30;
        }

        do {
            $plainCode = (string) random_int(100000, 999999);
            $tokenHash = TpsBoothToken::hashCode($plainCode);
        } while (TpsBoothToken::hashExists($tokenHash));

        $expiresAt = date('Y-m-d H:i:s', strtotime('+' . $expiresMinutes . ' minutes'));

        TpsBoothToken::create([
            'election_id' => (int) $electionId,
            'election_voter_id' => (int) $electionVoterId,
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt,
            'created_by' => Auth::id(),
        ]);

        AuditLog::record(
            'tps_booth_code_generate',
            'Generate kode bilik TPS untuk election ID ' . $electionId . '. Expired: ' . $expiresAt
        );

        Session::flash('tps_booth_code', $plainCode);
        Session::flash('tps_booth_voter', $electionVoter['name'] . ' / ' . $electionVoter['voter_code']);
        Session::flash('tps_booth_expires_at', $expiresAt);
        Session::flash('success', 'Kode bilik berhasil dibuat. Berikan kode ini ke pemilih.');

        Redirect::to('/elections/' . $electionId . '/tps-voting?q=' . urlencode($electionVoter['voter_code']));
    }

    public function revokeBoothCode(string $electionId, string $tokenId): void
    {
        Auth::requirePermission('tps_validate');
        Csrf::verify();

        $election = Election::find((int) $electionId);

        if (!$election) {
            http_response_code(404);
            die('Data pemilihan tidak ditemukan.');
        }

        $token = TpsBoothToken::find((int) $tokenId);

        if (!$token || (int) $token['election_id'] !== (int) $electionId) {
            http_response_code(404);
            die('Kode bilik tidak ditemukan.');
        }

        if ($token['used_at']) {
            Session::flash('error', 'Kode bilik tidak bisa direvoke karena sudah digunakan.');
            Redirect::to('/elections/' . $electionId . '/tps-voting');
        }

        if ($token['revoked_at']) {
            Session::flash('error', 'Kode bilik sudah direvoke sebelumnya.');
            Redirect::to('/elections/' . $electionId . '/tps-voting');
        }

        TpsBoothToken::revoke((int) $tokenId);

        AuditLog::record(
            'tps_booth_code_revoke',
            'Revoke kode bilik TPS ID ' . $tokenId . ' pada election ID ' . $electionId
        );

        Session::flash('success', 'Kode bilik berhasil direvoke.');
        Redirect::to('/elections/' . $electionId . '/tps-voting');
    }
}