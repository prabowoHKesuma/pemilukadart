<?php

namespace App\Controllers;

use App\Core\Auth;
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
use App\Models\RemoteVerification;
use App\Models\VotingToken;
use Throwable;


class RemoteTokenController extends Controller
{
    public function index(): void
{
    Auth::requirePermission('manage_remote_token');

    $elections = VotingToken::electionsWithStats();

    $this->view('remote_tokens/index', [
        'title' => 'Token Voting Remote',
        'elections' => $elections,
    ]);
}

public function election(string $electionId): void
{
    Auth::requirePermission('manage_remote_token');

    $election = Election::find((int) $electionId);

    if (!$election) {
        http_response_code(404);
        die('Data pemilihan tidak ditemukan.');
    }

    $approvedRequests = VotingToken::approvedRemoteVerifications((int) $electionId);
    $tokens = VotingToken::allByElection((int) $electionId);

    // Fallback remote_token_link hanya untuk kompatibilitas dari flash key lama.
    $generatedLink = Session::flash('generated_remote_token_link') ?? Session::flash('remote_token_link');

    $this->view('remote_tokens/election', [
        'title' => 'Token Voting Remote',
        'election' => $election,
        'approvedRequests' => $this->prepareApprovedRequests($approvedRequests, $election),
        'tokens' => $this->prepareTokens($tokens),
        'generatedLink' => $generatedLink,
        'isElectionOpen' => $this->isElectionOpen($election),
        'expiryOptions' => $this->expiryOptions(),
    ]);
}

private function prepareApprovedRequests(array $approvedRequests, array $election): array
{
    $isElectionOpen = $this->isElectionOpen($election);

    return array_map(function (array $request) use ($isElectionOpen): array {
        $status = $this->approvedRequestTokenStatus($request);

        $hasAlreadyVoted = (int) ($request['has_voted'] ?? 0) === 1;

        $request['token_status_text'] = $status['text'];
        $request['token_status_badge'] = $status['badge'];
        $request['token_is_active'] = $status['is_active'];
        $request['has_already_voted'] = $hasAlreadyVoted;

        $request['can_generate_token'] =
            $isElectionOpen
            && !$hasAlreadyVoted
            && !$status['is_active'];

        return $request;
    }, $approvedRequests);
}

private function prepareTokens(array $tokens): array
{
    return array_map(function (array $token): array {
        $status = $this->tokenRowStatus($token);

        $token['status_text'] = $status['text'];
        $token['status_badge'] = $status['badge'];
        $token['can_revoke'] = $status['can_revoke'];

        return $token;
    }, $tokens);
}

private function approvedRequestTokenStatus(array $row): array
{
    if (empty($row['token_id'])) {
        return [
            'text' => 'Belum Ada Token',
            'badge' => 'secondary',
            'is_active' => false,
        ];
    }

    if (!empty($row['token_used_at'])) {
        return [
            'text' => 'Sudah Dipakai',
            'badge' => 'success',
            'is_active' => false,
        ];
    }

    if (!empty($row['token_revoked_at'])) {
        return [
            'text' => 'Revoked',
            'badge' => 'danger',
            'is_active' => false,
        ];
    }

    if ($this->isExpired($row['token_expires_at'] ?? null)) {
        return [
            'text' => 'Expired',
            'badge' => 'warning',
            'is_active' => false,
        ];
    }

    return [
        'text' => 'Aktif',
        'badge' => 'primary',
        'is_active' => true,
    ];
}

private function tokenRowStatus(array $token): array
{
    if (!empty($token['used_at'])) {
        return [
            'text' => 'Sudah Dipakai',
            'badge' => 'success',
            'can_revoke' => false,
        ];
    }

    if (!empty($token['revoked_at'])) {
        return [
            'text' => 'Revoked',
            'badge' => 'danger',
            'can_revoke' => false,
        ];
    }

    if ($this->isExpired($token['expires_at'] ?? null)) {
        return [
            'text' => 'Expired',
            'badge' => 'warning',
            'can_revoke' => false,
        ];
    }

    return [
        'text' => 'Aktif',
        'badge' => 'primary',
        'can_revoke' => true,
    ];
}

private function isElectionOpen(array $election): bool
{
    return ($election['status'] ?? '') === 'open';
}

private function isExpired(?string $dateTime): bool
{
    if (!$dateTime) {
        return true;
    }

    return strtotime($dateTime) <= time();
}

private function expiryOptions(): array
{
    return [
        5 => '5 menit',
        15 => '15 menit',
        30 => '30 menit',
        60 => '60 menit',
    ];
}

private function normalizeExpiryMinutes(mixed $value): int
{
    $minutes = (int) $value;
    $allowed = array_keys($this->expiryOptions());

    return in_array($minutes, $allowed, true) ? $minutes : 30;
}

public function generate(string $electionId, string $remoteVerificationId): void
{
    Auth::requirePermission('manage_remote_token');
    Csrf::verify();

    $election = Election::find((int) $electionId);

    if (!$election) {
        http_response_code(404);
        die('Data pemilihan tidak ditemukan.');
    }

    if (!$this->isElectionOpen($election)) {
        Session::flash('error', 'Token remote hanya boleh dibuat saat status pemilihan OPEN.');
        Redirect::to('/elections/' . $electionId . '/remote-tokens');
    }

    $request = RemoteVerification::find((int) $remoteVerificationId);

    if (!$request || (int) $request['election_id'] !== (int) $electionId) {
        http_response_code(404);
        die('Request verifikasi remote tidak ditemukan.');
    }

    if (($request['status'] ?? '') !== 'approved') {
        Session::flash('error', 'Token hanya bisa dibuat untuk verifikasi remote yang sudah APPROVED.');
        Redirect::to('/elections/' . $electionId . '/remote-tokens');
    }

    if ((int) ($request['has_voted'] ?? 0) === 1) {
        Session::flash('error', 'Pemilih ini sudah mencoblos.');
        Redirect::to('/elections/' . $electionId . '/remote-tokens');
    }

    if (!in_array($request['allowed_channel'] ?? '', ['remote', 'both'], true)) {
        Session::flash('error', 'Pemilih ini tidak memiliki hak channel remote.');
        Redirect::to('/elections/' . $electionId . '/remote-tokens');
    }

    $activeToken = VotingToken::activeByRemoteVerification((int) $remoteVerificationId);

    if ($activeToken) {
        Session::flash('error', 'Pemilih ini masih punya token aktif. Revoke token lama dulu jika perlu membuat baru.');
        Redirect::to('/elections/' . $electionId . '/remote-tokens');
    }

    $expiresMinutes = $this->normalizeExpiryMinutes($_POST['expires_minutes'] ?? 30);

    $plainToken = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $plainToken);
    $expiresAt = date('Y-m-d H:i:s', strtotime('+' . $expiresMinutes . ' minutes'));

    VotingToken::create([
        'election_id' => (int) $electionId,
        'voter_id' => (int) $request['voter_id'],
        'remote_verification_id' => (int) $remoteVerificationId,
        'token_hash' => $tokenHash,
        'expires_at' => $expiresAt,
        'created_by' => Auth::id(),
    ]);

    $link = rtrim(Env::get('APP_URL'), '/') . '/remote-vote/' . $plainToken;

    // Key ini harus sama dengan yang dibaca di method election().
    Session::flash('generated_remote_token_link', $link);

    AuditLog::record(
        'remote_token_generate',
        'Generate token voting remote untuk election ID ' . $electionId . ' dan remote verification ID ' . $remoteVerificationId . '. Expired: ' . $expiresAt,
        null,
        [
            'election_id' => (int) $electionId,
            'organization_id' => $election['organization_id'] ?? null,
            'region_id' => $election['region_id'] ?? null,
        ]
    );

    Session::flash('success', 'Token remote berhasil dibuat. Copy link sekarang, token asli tidak disimpan.');
    Redirect::to('/elections/' . $electionId . '/remote-tokens');
}

public function revoke(string $electionId, string $tokenId): void
{
    Auth::requirePermission('manage_remote_token');
    Csrf::verify();

    $election = Election::find((int) $electionId);

    if (!$election) {
        http_response_code(404);
        die('Data pemilihan tidak ditemukan.');
    }

    $token = VotingToken::find((int) $tokenId);

    if (!$token || (int) $token['election_id'] !== (int) $electionId) {
        http_response_code(404);
        die('Token tidak ditemukan.');
    }

    if (!empty($token['used_at'])) {
        Session::flash('error', 'Token tidak bisa direvoke karena sudah digunakan.');
        Redirect::to('/elections/' . $electionId . '/remote-tokens');
    }

    if (!empty($token['revoked_at'])) {
        Session::flash('error', 'Token sudah direvoke sebelumnya.');
        Redirect::to('/elections/' . $electionId . '/remote-tokens');
    }

    if ($this->isExpired($token['expires_at'] ?? null)) {
        Session::flash('error', 'Token sudah expired, tidak perlu direvoke.');
        Redirect::to('/elections/' . $electionId . '/remote-tokens');
    }

    VotingToken::revoke((int) $tokenId);

    AuditLog::record(
        'remote_token_revoke',
        'Revoke token voting remote ID ' . $tokenId . ' pada election ID ' . $electionId,
        null,
        [
            'election_id' => (int) $electionId,
            'organization_id' => $election['organization_id'] ?? null,
            'region_id' => $election['region_id'] ?? null,
        ]
    );

    Session::flash('success', 'Token berhasil direvoke.');
    Redirect::to('/elections/' . $electionId . '/remote-tokens');
}

    public function showVote(string $plainToken): void
    {
        $tokenHash = hash('sha256', $plainToken);

        $token = VotingToken::findByHash($tokenHash);

        if (!$token) {
            $this->view('remote_vote/invalid', [
                'title' => 'Token Tidak Valid',
                'message' => 'Token voting tidak ditemukan.',
            ], 'remote');
            return;
        }

        $validation = $this->validateTokenForDisplay($token);

        if ($validation !== true) {
            $this->view('remote_vote/invalid', [
                'title' => 'Token Tidak Valid',
                'message' => $validation,
            ], 'remote');
            return;
        }

        $candidates = Candidate::activeByElection((int) $token['election_id']);

        if (empty($candidates)) {
            $this->view('remote_vote/invalid', [
                'title' => 'Kandidat Tidak Tersedia',
                'message' => 'Belum ada kandidat aktif untuk pemilihan ini.',
            ], 'remote');
            return;
        }

        $this->view('remote_vote/ballot', [
            'title' => 'Coblos Remote',
            'token' => $token,
            'plainToken' => $plainToken,
            'candidates' => $candidates,
        ], 'remote');
    }

    public function submitVote(string $plainToken): void
    {
        Csrf::verify();

        $candidateId = (int) ($_POST['candidate_id'] ?? 0);

        if ($candidateId <= 0) {
            Session::flash('error', 'Pilih salah satu kandidat.');
            Redirect::to('/remote-vote/' . $plainToken);
        }

        $tokenHash = hash('sha256', $plainToken);
        $pdo = Database::connection();

        try {
            $pdo->beginTransaction();

            $token = VotingToken::lockByHash($pdo, $tokenHash);

            if (!$token) {
                $pdo->rollBack();
                $this->view('remote_vote/invalid', [
                    'title' => 'Token Tidak Valid',
                    'message' => 'Token voting tidak ditemukan.',
                ], 'remote');
                return;
            }

            if ($token['used_at'] !== null) {
                $pdo->rollBack();
                $this->view('remote_vote/invalid', [
                    'title' => 'Token Sudah Digunakan',
                    'message' => 'Token ini sudah digunakan untuk mencoblos.',
                ], 'remote');
                return;
            }

            if ($token['revoked_at'] !== null) {
                $pdo->rollBack();
                $this->view('remote_vote/invalid', [
                    'title' => 'Token Dicabut',
                    'message' => 'Token ini sudah dicabut oleh panitia.',
                ], 'remote');
                return;
            }

            if (strtotime($token['expires_at']) <= time()) {
                $pdo->rollBack();
                $this->view('remote_vote/invalid', [
                    'title' => 'Token Expired',
                    'message' => 'Token ini sudah melewati batas waktu.',
                ], 'remote');
                return;
            }

            if ($token['remote_status'] !== 'approved') {
                $pdo->rollBack();
                $this->view('remote_vote/invalid', [
                    'title' => 'Verifikasi Belum Approved',
                    'message' => 'Verifikasi remote belum disetujui.',
                ], 'remote');
                return;
            }

            $election = Election::lockById($pdo, (int) $token['election_id']);

            if (!$election || $election['status'] !== 'open') {
                $pdo->rollBack();
                $this->view('remote_vote/invalid', [
                    'title' => 'Pemilihan Tidak Dibuka',
                    'message' => 'Pemilihan belum dibuka atau sudah ditutup.',
                ], 'remote');
                return;
            }

            $electionVoter = ElectionVoter::lockForRemoteVoting(
                $pdo,
                (int) $token['election_id'],
                (int) $token['voter_id']
            );

            if (!$electionVoter) {
                $pdo->rollBack();
                $this->view('remote_vote/invalid', [
                    'title' => 'Pemilih Tidak Valid',
                    'message' => 'Pemilih tidak ditemukan dalam daftar pemilihan.',
                ], 'remote');
                return;
            }

            if ((int) $electionVoter['is_active'] !== 1) {
                $pdo->rollBack();
                $this->view('remote_vote/invalid', [
                    'title' => 'Pemilih Nonaktif',
                    'message' => 'Status pemilih tidak aktif.',
                ], 'remote');
                return;
            }

            if ((int) $electionVoter['has_voted'] === 1) {
                $pdo->rollBack();
                $this->view('remote_vote/invalid', [
                    'title' => 'Sudah Mencoblos',
                    'message' => 'Pemilih ini sudah tercatat mencoblos.',
                ], 'remote');
                return;
            }

            if (!in_array($electionVoter['allowed_channel'], ['remote', 'both'], true)) {
                $pdo->rollBack();
                $this->view('remote_vote/invalid', [
                    'title' => 'Channel Tidak Valid',
                    'message' => 'Pemilih ini tidak memiliki hak mencoblos remote.',
                ], 'remote');
                return;
            }

            $candidate = Candidate::findActiveForVote(
                $pdo,
                (int) $token['election_id'],
                $candidateId
            );

            if (!$candidate) {
                $pdo->rollBack();
                Session::flash('error', 'Kandidat tidak valid.');
                Redirect::to('/remote-vote/' . $plainToken);
            }

            Ballot::create($pdo, (int) $token['election_id'], $candidateId, 'remote');

            ElectionVoter::markVoted($pdo, (int) $electionVoter['id']);

            VotingToken::markUsed($pdo, (int) $token['id']);

            $pdo->commit();

            AuditLog::record(
                'vote_remote_success',
                'Suara remote berhasil disimpan untuk election ID ' . $token['election_id'] . '.',
                null
            );

            Redirect::to('/remote-vote/' . $plainToken . '/success');

        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            if (Env::get('APP_DEBUG') === 'true') {
                die('Remote voting gagal: ' . $e->getMessage());
            }

            $this->view('remote_vote/invalid', [
                'title' => 'Voting Gagal',
                'message' => 'Voting remote gagal diproses.',
            ], 'remote');
        }
    }

    public function success(string $plainToken): void
    {
        $this->view('remote_vote/success', [
            'title' => 'Voting Berhasil',
        ], 'remote');
    }

    private function validateTokenForDisplay(array $token): true|string
    {
        if ($token['used_at'] !== null) {
            return 'Token ini sudah digunakan.';
        }

        if ($token['revoked_at'] !== null) {
            return 'Token ini sudah dicabut oleh panitia.';
        }

        if (strtotime($token['expires_at']) <= time()) {
            return 'Token ini sudah expired.';
        }

        if ($token['election_status'] !== 'open') {
            return 'Pemilihan belum dibuka atau sudah ditutup.';
        }

        if ($token['remote_status'] !== 'approved') {
            return 'Verifikasi remote belum disetujui.';
        }

        return true;
    }
}