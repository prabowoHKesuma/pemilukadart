<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Env;
use App\Core\Redirect;
use App\Core\Session;
use App\Models\AuditLog;
use App\Models\Ballot;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\ElectionVoter;
use App\Models\TpsBoothToken;
use Throwable;

class TpsBoothController extends Controller
{
    public function index(): void
    {
        Session::remove('tps_booth_token_hash');

        $this->view('tps_booth/index', [
            'title' => 'Mode Bilik TPS',
        ], 'tps');
    }

    public function checkCode(): void
    {
        Csrf::verify();

        $code = preg_replace('/\D/', '', $_POST['booth_code'] ?? '');

        if ($code === '' || strlen($code) !== 6) {
            Session::flash('error', 'Kode bilik harus 6 digit.');
            Redirect::to('/tps-booth');
        }

        $tokenHash = TpsBoothToken::hashCode($code);
        $token = TpsBoothToken::findByHash($tokenHash);

        if (!$token) {
            Session::flash('error', 'Kode bilik tidak valid.');
            Redirect::to('/tps-booth');
        }

        $validation = $this->validateTokenForDisplay($token);

        if ($validation !== true) {
            Session::flash('error', $validation);
            Redirect::to('/tps-booth');
        }

        Session::set('tps_booth_token_hash', $tokenHash);

        Redirect::to('/tps-booth/ballot');
    }

    public function ballot(): void
    {
        $tokenHash = Session::get('tps_booth_token_hash');

        if (!$tokenHash) {
            Session::flash('error', 'Masukkan kode bilik terlebih dahulu.');
            Redirect::to('/tps-booth');
        }

        $token = TpsBoothToken::findByHash($tokenHash);

        if (!$token) {
            Session::remove('tps_booth_token_hash');
            Session::flash('error', 'Kode bilik tidak valid.');
            Redirect::to('/tps-booth');
        }

        $validation = $this->validateTokenForDisplay($token);

        if ($validation !== true) {
            Session::remove('tps_booth_token_hash');
            Session::flash('error', $validation);
            Redirect::to('/tps-booth');
        }

        $candidates = Candidate::activeByElection((int) $token['election_id']);

        if (empty($candidates)) {
            Session::remove('tps_booth_token_hash');
            Session::flash('error', 'Belum ada kandidat aktif untuk pemilihan ini.');
            Redirect::to('/tps-booth');
        }

        $this->view('tps_booth/ballot', [
            'title' => 'Coblos TPS',
            'token' => $token,
            'candidates' => $candidates,
        ], 'tps');
    }

    public function submit(): void
    {
        Csrf::verify();

        $tokenHash = Session::get('tps_booth_token_hash');

        if (!$tokenHash) {
            Session::flash('error', 'Session bilik tidak valid. Masukkan kode ulang.');
            Redirect::to('/tps-booth');
        }

        $candidateId = (int) ($_POST['candidate_id'] ?? 0);

        if ($candidateId <= 0) {
            Session::flash('error', 'Pilih salah satu kandidat.');
            Redirect::to('/tps-booth/ballot');
        }

        $pdo = Database::connection();

        try {
            $pdo->beginTransaction();

            $token = TpsBoothToken::lockByHash($pdo, $tokenHash);

            if (!$token) {
                $pdo->rollBack();
                Session::remove('tps_booth_token_hash');
                Session::flash('error', 'Kode bilik tidak valid.');
                Redirect::to('/tps-booth');
            }

            if ($token['used_at'] !== null) {
                $pdo->rollBack();
                Session::remove('tps_booth_token_hash');
                Session::flash('error', 'Kode bilik sudah digunakan.');
                Redirect::to('/tps-booth');
            }

            if ($token['revoked_at'] !== null) {
                $pdo->rollBack();
                Session::remove('tps_booth_token_hash');
                Session::flash('error', 'Kode bilik sudah direvoke panitia.');
                Redirect::to('/tps-booth');
            }

            if (strtotime($token['expires_at']) <= time()) {
                $pdo->rollBack();
                Session::remove('tps_booth_token_hash');
                Session::flash('error', 'Kode bilik sudah expired.');
                Redirect::to('/tps-booth');
            }

            $election = Election::lockById($pdo, (int) $token['election_id']);

            if (!$election || $election['status'] !== 'open') {
                $pdo->rollBack();
                Session::remove('tps_booth_token_hash');
                Session::flash('error', 'Pemilihan belum dibuka atau sudah ditutup.');
                Redirect::to('/tps-booth');
            }

            $electionVoter = ElectionVoter::lockForVoting(
                $pdo,
                (int) $token['election_id'],
                (int) $token['election_voter_id']
            );

            if (!$electionVoter) {
                $pdo->rollBack();
                Session::remove('tps_booth_token_hash');
                Session::flash('error', 'Pemilih tidak valid dalam pemilihan ini.');
                Redirect::to('/tps-booth');
            }

            if ((int) $electionVoter['is_active'] !== 1) {
                $pdo->rollBack();
                Session::remove('tps_booth_token_hash');
                Session::flash('error', 'Pemilih nonaktif.');
                Redirect::to('/tps-booth');
            }

            if ((int) $electionVoter['has_voted'] === 1) {
                $pdo->rollBack();
                Session::remove('tps_booth_token_hash');
                Session::flash('error', 'Pemilih ini sudah mencoblos.');
                Redirect::to('/tps-booth');
            }

            if (!in_array($electionVoter['allowed_channel'], ['tps', 'both'], true)) {
                $pdo->rollBack();
                Session::remove('tps_booth_token_hash');
                Session::flash('error', 'Pemilih ini tidak memiliki hak mencoblos TPS.');
                Redirect::to('/tps-booth');
            }

            $candidate = Candidate::findActiveForVote(
                $pdo,
                (int) $token['election_id'],
                $candidateId
            );

            if (!$candidate) {
                $pdo->rollBack();
                Session::flash('error', 'Kandidat tidak valid.');
                Redirect::to('/tps-booth/ballot');
            }

            Ballot::create($pdo, (int) $token['election_id'], $candidateId, 'tps');

            ElectionVoter::markVoted($pdo, (int) $token['election_voter_id']);

            TpsBoothToken::markUsed($pdo, (int) $token['id']);

            $pdo->commit();

            Session::remove('tps_booth_token_hash');

            AuditLog::record(
                'vote_tps_booth_success',
                'Suara TPS via bilik berhasil disimpan untuk election ID ' . $token['election_id'] . '.',
                null
            );

            Redirect::to('/tps-booth/success');

        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            Session::remove('tps_booth_token_hash');

            if (Env::get('APP_DEBUG') === 'true') {
                die('TPS booth voting gagal: ' . $e->getMessage());
            }

            Session::flash('error', 'Voting TPS gagal diproses.');
            Redirect::to('/tps-booth');
        }
    }

    public function success(): void
    {
        $this->view('tps_booth/success', [
            'title' => 'Voting TPS Berhasil',
        ], 'tps');
    }

    private function validateTokenForDisplay(array $token): true|string
    {
        if ($token['used_at'] !== null) {
            return 'Kode bilik ini sudah digunakan.';
        }

        if ($token['revoked_at'] !== null) {
            return 'Kode bilik ini sudah direvoke panitia.';
        }

        if (strtotime($token['expires_at']) <= time()) {
            return 'Kode bilik ini sudah expired.';
        }

        if ($token['election_status'] !== 'open') {
            return 'Pemilihan belum dibuka atau sudah ditutup.';
        }

        if ((int) $token['is_active'] !== 1) {
            return 'Pemilih tidak aktif.';
        }

        if ((int) $token['has_voted'] === 1) {
            return 'Pemilih ini sudah mencoblos.';
        }

        if (!in_array($token['allowed_channel'], ['tps', 'both'], true)) {
            return 'Pemilih ini tidak memiliki hak mencoblos TPS.';
        }

        return true;
    }
}