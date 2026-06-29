<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Redirect;
use App\Core\Session;
use App\Core\Env;
use App\Models\AuditLog;
use App\Models\Election;
use App\Models\RemoteVerification;

class RemoteVerificationController extends Controller
{
    public function index(): void
    {
        Auth::requirePermission('manage_remote_verification');

        $elections = RemoteVerification::electionsWithStats();

        $this->view('remote_verifications/index', [
            'title' => 'Verifikasi Remote',
            'elections' => $elections,
        ]);
    }

    private function electionOr404(string|int $electionId): array
    {
        $election = Election::find((int) $electionId);

        if (!$election) {
            http_response_code(404);
            die('Data pemilihan tidak ditemukan atau berada di luar scope wilayah Anda.');
        }

        return $election;
    }

    private function requestOr404(array $election, string|int $requestId): array
    {
        $request = RemoteVerification::findDetailScoped((int) $requestId, $election);

        if (!$request) {
            http_response_code(404);
            die('Request verifikasi remote tidak ditemukan atau berada di luar scope wilayah Anda.');
        }

        return $request;
    }

    private function ensureRequestCanProcess(array $request): void
    {
        if (($request['status'] ?? '') !== 'pending') {
            Session::flash('error', 'Request verifikasi ini sudah tidak bisa diproses.');
            Redirect::to('/elections/' . $request['election_id'] . '/remote-verifications/' . $request['id']);
        }
    }

    private function ensureRequestHasPhotos(array $request): void
    {
        if (empty($request['ktp_photo_path']) || empty($request['selfie_photo_path'])) {
            Session::flash('error', 'Foto KTP dan selfie wajib diupload sebelum approval.');
            Redirect::to('/elections/' . $request['election_id'] . '/remote-verifications/' . $request['id']);
        }
    }

    public function election(string $electionId): void
    {
        Auth::requirePermission('manage_remote_verification');

        // $election = Election::find((int) $electionId);

        $election = $this->electionOr404($electionId);

        /* if (!$election) {
            http_response_code(404);
            die('Data pemilihan tidak ditemukan.');
        } */

        //$requests = RemoteVerification::allByElection((int) $electionId);
        $requests = RemoteVerification::allByElectionScoped($election);

        $isLocked = !in_array($election['status'] ?? '', ['draft', 'open'], true);
        
        $this->view('remote_verifications/election', [
            'title' => 'Verifikasi Remote Pemilihan',
            'election' => $election,
            'requests' => $this->prepareRequests($requests),
            'canCreateRequest' => !$isLocked,
            'isLocked' => $isLocked,
        ]);
    }

    private function prepareRequests(array $requests): array
    {
        return array_map(function (array $request): array {
            $status = $this->remoteStatus($request);

            $request['status_label'] = $status['label'];
            $request['status_badge'] = $status['badge'];
            $request['rt_rw_label'] = ($request['rt'] ?: '-') . ' / ' . ($request['rw'] ?: '-');

            return $request;
        }, $requests);
    }

    private function remoteStatus(array $request): array
    {
        if (($request['status'] ?? '') === 'approved') {
            return [
                'label' => 'Approved',
                'badge' => 'success',
            ];
        }

        if (($request['status'] ?? '') === 'rejected') {
            return [
                'label' => 'Rejected',
                'badge' => 'danger',
            ];
        }

        if (empty($request['ktp_photo_path']) || empty($request['selfie_photo_path'])) {
            return [
                'label' => 'Menunggu Upload',
                'badge' => 'secondary',
            ];
        }

        if (!empty($request['verified_by_1']) && empty($request['verified_by_2'])) {
            return [
                'label' => 'Menunggu Approval 2',
                'badge' => 'warning',
            ];
        }

        return [
            'label' => 'Menunggu Approval 1',
            'badge' => 'info',
        ];
    }

    public function create(string $electionId): void
    {
        Auth::requirePermission('manage_remote_verification');

        $election = Election::find((int) $electionId);

        if (!$election) {
            http_response_code(404);
            die('Data pemilihan tidak ditemukan.');
        }

        if (!in_array($election['status'], ['draft', 'open'], true)) {
            Session::flash('error', 'Verifikasi remote hanya boleh dibuat saat pemilihan masih draft atau open.');
            Redirect::to('/elections/' . $electionId . '/remote-verifications');
        }

        $availableVoters = RemoteVerification::availableElectionVoters((int) $electionId);

        $this->view('remote_verifications/create', [
            'title' => 'Buat Verifikasi Remote',
            'election' => $election,
            'availableVoters' => $availableVoters,
        ]);
    }

    public function store(string $electionId): void
    {
        Auth::requirePermission('manage_remote_verification');
        Csrf::verify();

        $election = Election::find((int) $electionId);

        if (!$election) {
            http_response_code(404);
            die('Data pemilihan tidak ditemukan.');
        }

        if (!in_array($election['status'], ['draft', 'open'], true)) {
            Session::flash('error', 'Verifikasi remote hanya boleh dibuat saat pemilihan masih draft atau open.');
            Redirect::to('/elections/' . $electionId . '/remote-verifications');
        }

        $electionVoterId = (int) ($_POST['election_voter_id'] ?? 0);

        if ($electionVoterId <= 0) {
            Session::flash('error', 'Pilih pemilih remote.');
            Redirect::to('/elections/' . $electionId . '/remote-verifications/create');
        }

        $electionVoter = RemoteVerification::findAvailableElectionVoter((int) $electionId, $electionVoterId);

        if (!$electionVoter) {
            Session::flash('error', 'Pemilih tidak valid, bukan channel remote/both, sudah mencoblos, atau tidak aktif.');
            Redirect::to('/elections/' . $electionId . '/remote-verifications/create');
        }

        if (RemoteVerification::exists((int) $electionId, (int) $electionVoter['voter_id'])) {
            Session::flash('error', 'Pemilih ini sudah memiliki request verifikasi remote.');
            Redirect::to('/elections/' . $electionId . '/remote-verifications');
        }

        $verificationCode = $this->generateVerificationCode((int) $electionId);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+2 days'));

        /* RemoteVerification::create([
            'election_id' => (int) $electionId,
            'voter_id' => (int) $electionVoter['voter_id'],
            'verification_code' => $verificationCode,
            'expires_at' => $expiresAt,
        ]);

        AuditLog::record(
            'remote_verification_create',
            'Membuat request verifikasi remote untuk election ID ' . $electionId . ' dengan kode ' . $verificationCode
        );

        Session::flash('success', 'Request verifikasi remote berhasil dibuat. Berikan kode ke pemilih.');
        Redirect::to('/elections/' . $electionId . '/remote-verifications'); */

        $requestId = RemoteVerification::create([
            'election_id' => (int) $electionId,
            'voter_id' => (int) $electionVoter['voter_id'],
            'verification_code' => $verificationCode,
            'expires_at' => $expiresAt,
        ]);

        $plainUploadToken = RemoteVerification::generateUploadToken((int) $requestId, 24);

        $uploadLink = rtrim(Env::get('APP_URL'), '/') . '/remote-verification-upload/' . $plainUploadToken;

        Session::flash('remote_verification_upload_link', $uploadLink);

        AuditLog::record(
            'remote_verification_create',
            'Membuat request verifikasi remote untuk election ID ' . $electionId . ' dengan kode ' . $verificationCode,
            null,
            [
                'election_id' => (int) $electionId,
                'organization_id' => $election['organization_id'] ?? null,
                'region_id' => $election['region_id'] ?? null,
            ]
        );

        Session::flash('success', 'Request verifikasi remote berhasil dibuat. Copy link upload dan kirim ke pemilih.');

        Redirect::to('/elections/' . $electionId . '/remote-verifications/' . $requestId);
    }

    public function show(string $electionId, string $id): void
    {
        Auth::requirePermission('manage_remote_verification');

        $election = $this->electionOr404($electionId);
        $request = $this->requestOr404($election, $id);

        $preparedRequest = $this->prepareRequestDetail($request, $election);

        $this->view('remote_verifications/show', [
            'title' => 'Detail Verifikasi Remote',
            'election' => $election,
            'request' => $preparedRequest,
            'uploadLink' => Session::flash('remote_verification_upload_link'),
        ]);
    }

    private function prepareRequestDetail(array $request, array $election): array
    {
        $status = $this->remoteDetailStatus($request);

        $hasPhotos =
            !empty($request['ktp_photo_path'])
            && !empty($request['selfie_photo_path']);

        $canProcess = ($request['status'] ?? '') === 'pending';

        $request['detail_status_text'] = $status['text'];
        $request['detail_status_badge'] = $status['badge'];

        $request['has_photos'] = $hasPhotos;
        $request['can_process'] = $canProcess;
        $request['can_upload_photos'] = $canProcess && !$hasPhotos;
        $request['can_approve'] = $canProcess && $hasPhotos;
        $request['can_reject'] = $canProcess;

        $request['can_regenerate_upload_link'] =
        ($request['status'] ?? '') === 'pending'
        && empty($request['verified_by_1'])
        && empty($request['verified_by_2']);

        $request['rt_rw_label'] = ($request['rt'] ?: '-') . ' / ' . ($request['rw'] ?: '-');

        $baseUrl = '/elections/' . (int) $election['id'] . '/remote-verifications/' . (int) $request['id'];

        $request['back_url'] = '/elections/' . (int) $election['id'] . '/remote-verifications';
        $request['upload_url'] = $baseUrl . '/upload';
        $request['approve_url'] = $baseUrl . '/approve';
        $request['reject_url'] = $baseUrl . '/reject';
        $request['regenerate_upload_link_url'] = $baseUrl . '/regenerate-upload-link';

        $request['ktp_file_url'] = !empty($request['ktp_photo_path'])
            ? '/remote-verifications/' . (int) $request['id'] . '/file/ktp'
            : null;

        $request['selfie_file_url'] = !empty($request['selfie_photo_path'])
            ? '/remote-verifications/' . (int) $request['id'] . '/file/selfie'
            : null;

        return $request;
    }

    private function remoteDetailStatus(array $request): array
    {
        if (($request['status'] ?? '') === 'approved') {
            return [
                'text' => 'APPROVED',
                'badge' => 'success',
            ];
        }

        if (($request['status'] ?? '') === 'rejected') {
            return [
                'text' => 'REJECTED',
                'badge' => 'danger',
            ];
        }

        if (empty($request['ktp_photo_path']) || empty($request['selfie_photo_path'])) {
            return [
                'text' => 'MENUNGGU UPLOAD FOTO',
                'badge' => 'secondary',
            ];
        }

        if (!empty($request['verified_by_1']) && empty($request['verified_by_2'])) {
            return [
                'text' => 'MENUNGGU APPROVAL KEDUA',
                'badge' => 'warning',
            ];
        }

        return [
            'text' => 'MENUNGGU APPROVAL PERTAMA',
            'badge' => 'info',
        ];
    }

    public function upload(string $electionId, string $id): void
    {
        Auth::requirePermission('manage_remote_verification');
        Csrf::verify();

        $election = $this->electionOr404($electionId);
        $request = $this->requestOr404($election, $id);

        $this->ensureRequestCanProcess($request);

        if ($request['status'] !== 'pending') {
            Session::flash('error', 'Foto hanya bisa diupload saat status masih pending.');
            Redirect::to('/elections/' . $electionId . '/remote-verifications/' . $id);
        }

        if (!isset($_POST['consent_accepted'])) {
            Session::flash('error', 'Persetujuan penggunaan data wajib dicentang.');
            Redirect::to('/elections/' . $electionId . '/remote-verifications/' . $id);
        }

        $ktpPath = $this->uploadPrivateImage('ktp_photo', (int) $electionId, 'ktp');
        $selfiePath = $this->uploadPrivateImage('selfie_photo', (int) $electionId, 'selfie');

        if ($request['ktp_photo_path']) {
            $this->deletePrivateFile($request['ktp_photo_path']);
        }

        if ($request['selfie_photo_path']) {
            $this->deletePrivateFile($request['selfie_photo_path']);
        }

        RemoteVerification::updatePhotos((int) $id, $ktpPath, $selfiePath);

        AuditLog::record(
            'remote_verification_upload',
            'Upload foto KTP dan selfie untuk remote verification ID ' . $id . ' pada election ID ' . $electionId,
            null,
            [
                'election_id' => (int) $electionId,
                'organization_id' => $election['organization_id'] ?? null,
                'region_id' => $request['voter_region_id'] ?? ($election['region_id'] ?? null),
            ]
        );

        Session::flash('success', 'Foto verifikasi berhasil diupload.');
        Redirect::to('/elections/' . $electionId . '/remote-verifications/' . $id);
    }

    public function approve(string $electionId, string $id): void
    {
        Auth::requirePermission('manage_remote_verification');
        Csrf::verify();

        $election = $this->electionOr404($electionId);
        $request = $this->requestOr404($election, $id);

        $this->ensureRequestCanProcess($request);
        $this->ensureRequestHasPhotos($request);

        if ($request['status'] !== 'pending') {
            Session::flash('error', 'Request ini sudah tidak berstatus pending.');
            Redirect::to('/elections/' . $electionId . '/remote-verifications/' . $id);
        }

        if (empty($request['ktp_photo_path']) || empty($request['selfie_photo_path'])) {
            Session::flash('error', 'Foto KTP dan selfie wajib diupload sebelum approve.');
            Redirect::to('/elections/' . $electionId . '/remote-verifications/' . $id);
        }

        $userId = Auth::id();

        if (!$userId) {
            Session::flash('error', 'User tidak valid.');
            Redirect::to('/elections/' . $electionId . '/remote-verifications/' . $id);
        }

        if (empty($request['verified_by_1'])) {
            RemoteVerification::approveFirst((int) $id, (int) $userId);

            AuditLog::record(
                'remote_verification_approve_1',
                'Approval pertama remote verification ID ' . $id . ' pada election ID ' . $electionId,
                null,
                [
                    'election_id' => (int) $electionId,
                    'organization_id' => $election['organization_id'] ?? null,
                    'region_id' => $request['voter_region_id'] ?? ($election['region_id'] ?? null),
                ]
            );

            Session::flash('success', 'Approval pertama berhasil. Menunggu approval kedua dari user berbeda.');
            Redirect::to('/elections/' . $electionId . '/remote-verifications/' . $id);
        }

        if ((int) $request['verified_by_1'] === (int) $userId) {
            Session::flash('error', 'Approval kedua harus dilakukan oleh user berbeda.');
            Redirect::to('/elections/' . $electionId . '/remote-verifications/' . $id);
        }

        if (empty($request['verified_by_2'])) {
            RemoteVerification::approveSecond((int) $id, (int) $userId);

            AuditLog::record(
                'remote_verification_approve_2',
                'Approval kedua remote verification ID ' . $id . '. Status menjadi APPROVED.',
                null,
                [
                    'election_id' => (int) $electionId,
                    'organization_id' => $election['organization_id'] ?? null,
                    'region_id' => $request['voter_region_id'] ?? ($election['region_id'] ?? null),
                ]
            );

            Session::flash('success', 'Approval kedua berhasil. Verifikasi remote sudah APPROVED.');
            Redirect::to('/elections/' . $electionId . '/remote-verifications/' . $id);
        }

        Session::flash('error', 'Request ini sudah lengkap approval-nya.');
        Redirect::to('/elections/' . $electionId . '/remote-verifications/' . $id);
    }

    public function reject(string $electionId, string $id): void
    {
        Auth::requirePermission('manage_remote_verification');
        Csrf::verify();

        $election = $this->electionOr404($electionId);
        $request = $this->requestOr404($election, $id);

        $this->ensureRequestCanProcess($request);

        if ($request['status'] !== 'pending') {
            Session::flash('error', 'Request ini sudah tidak berstatus pending.');
            Redirect::to('/elections/' . $electionId . '/remote-verifications/' . $id);
        }

        $reason = trim($_POST['reject_reason'] ?? '');

        if ($reason === '') {
            Session::flash('error', 'Alasan penolakan wajib diisi.');
            Redirect::to('/elections/' . $electionId . '/remote-verifications/' . $id);
        }

        RemoteVerification::reject((int) $id, (int) Auth::id(), $reason);

        AuditLog::record(
            'remote_verification_reject',
            'Menolak remote verification ID ' . $id . ' pada election ID ' . $electionId . '. Alasan: ' . $reason,
            null,
            [
                'election_id' => (int) $electionId,
                'organization_id' => $election['organization_id'] ?? null,
                'region_id' => $request['voter_region_id'] ?? ($election['region_id'] ?? null),
            ]
        );

        Session::flash('success', 'Verifikasi remote berhasil ditolak.');
        Redirect::to('/elections/' . $electionId . '/remote-verifications/' . $id);
    }

    public function file(string $id, string $type): void
{
    Auth::requirePermission('manage_remote_verification');

    if (!in_array($type, ['ktp', 'selfie'], true)) {
        http_response_code(404);
        die('File tidak ditemukan.');
    }

    $basic = RemoteVerification::findBasic((int) $id);

    if (!$basic) {
        http_response_code(404);
        die('File tidak ditemukan.');
    }

    $election = Election::find((int) $basic['election_id']);

    if (!$election) {
        http_response_code(404);
        die('File tidak ditemukan atau berada di luar scope wilayah Anda.');
    }

    $request = RemoteVerification::findDetailScoped((int) $id, $election);

    if (!$request) {
        http_response_code(404);
        die('File tidak ditemukan atau berada di luar scope wilayah Anda.');
    }

    $relativePath = $type === 'ktp'
        ? ($request['ktp_photo_path'] ?? null)
        : ($request['selfie_photo_path'] ?? null);

    if (!$relativePath) {
        http_response_code(404);
        die('File belum tersedia.');
    }

    // Path di DB bisa diawali "/" atau tidak.
    $relativePath = ltrim((string) $relativePath, '/\\');

    // Base folder private upload Anda.
    $baseDir = realpath(dirname(__DIR__, 2) . '/storage/private/verifications');

    if (!$baseDir) {
        http_response_code(404);
        die('Folder storage tidak ditemukan.');
    }

    $filePath = realpath(dirname(__DIR__, 2) . '/' . $relativePath);

    if (!$filePath) {
        http_response_code(404);
        die('File fisik tidak ditemukan.');
    }

    // Security: pastikan file benar-benar masih di dalam storage/private/verifications
    if (!str_starts_with($filePath, $baseDir)) {
        http_response_code(403);
        die('Akses file ditolak.');
    }

    if (!is_file($filePath)) {
        http_response_code(404);
        die('File tidak ditemukan.');
    }

    $mime = mime_content_type($filePath) ?: 'application/octet-stream';

    if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
        http_response_code(403);
        die('Tipe file tidak valid.');
    }

    header('Content-Type: ' . $mime);
    header('Content-Length: ' . filesize($filePath));
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: private, max-age=3600');

    readfile($filePath);
    exit;
}

    private function generateVerificationCode(int $electionId): string
    {
        return 'RV-' . str_pad((string) $electionId, 3, '0', STR_PAD_LEFT) . '-' . random_int(1000, 9999);
    }

    private function uploadPrivateImage(string $inputName, int $electionId, string $prefix): string
    {
        if (
            !isset($_FILES[$inputName]) ||
            $_FILES[$inputName]['error'] === UPLOAD_ERR_NO_FILE
        ) {
            Session::flash('error', 'File ' . $prefix . ' wajib diupload.');
            Redirect::back();
        }

        if ($_FILES[$inputName]['error'] !== UPLOAD_ERR_OK) {
            Session::flash('error', 'Upload file ' . $prefix . ' gagal.');
            Redirect::back();
        }

        $maxSize = 3 * 1024 * 1024;

        if ($_FILES[$inputName]['size'] > $maxSize) {
            Session::flash('error', 'Ukuran file ' . $prefix . ' maksimal 3MB.');
            Redirect::back();
        }

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

        $originalName = $_FILES[$inputName]['name'];
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (!in_array($extension, $allowedExtensions, true)) {
            Session::flash('error', 'Format file harus JPG, JPEG, PNG, atau WEBP.');
            Redirect::back();
        }

        $tmpName = $_FILES[$inputName]['tmp_name'];

        if (getimagesize($tmpName) === false) {
            Session::flash('error', 'File yang diupload bukan gambar valid.');
            Redirect::back();
        }

        $uploadDir = dirname(__DIR__, 2) . '/storage/private/verifications/election_' . $electionId . '/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $fileName = $prefix . '_' . date('YmdHis') . '_' . bin2hex(random_bytes(12)) . '.' . $extension;
        $targetPath = $uploadDir . $fileName;

        if (!move_uploaded_file($tmpName, $targetPath)) {
            Session::flash('error', 'Gagal menyimpan file ' . $prefix . '.');
            Redirect::back();
        }

        return 'storage/private/verifications/election_' . $electionId . '/' . $fileName;
    }

    private function deletePrivateFile(?string $path): void
    {
        if (!$path) {
            return;
        }

        $fullPath = dirname(__DIR__, 2) . '/' . ltrim($path, '/');

        if (file_exists($fullPath) && is_file($fullPath)) {
            unlink($fullPath);
        }
    }

    public function regenerateUploadLink(string $electionId, string $id): void
    {
        Auth::requirePermission('manage_remote_verification');
        Csrf::verify();

        $election = $this->electionOr404($electionId);
        $request = $this->requestOr404($election, $id);

        if (($request['status'] ?? '') !== 'pending') {
            Session::flash('error', 'Link upload hanya bisa dibuat ulang untuk request yang masih pending.');
            Redirect::to('/elections/' . $electionId . '/remote-verifications/' . $id);
        }

        if (!empty($request['verified_by_1']) || !empty($request['verified_by_2'])) {
            Session::flash('error', 'Link upload tidak bisa dibuat ulang karena request sudah masuk proses approval.');
            Redirect::to('/elections/' . $electionId . '/remote-verifications/' . $id);
        }

        $plainUploadToken = RemoteVerification::generateUploadToken((int) $id, 24);

        $uploadLink = rtrim(Env::get('APP_URL'), '/') . '/remote-verification-upload/' . $plainUploadToken;

        Session::flash('remote_verification_upload_link', $uploadLink);

        AuditLog::record(
            'remote_verification_regenerate_upload_link',
            'Generate ulang link upload verifikasi remote ID ' . $id,
            null,
            [
                'election_id' => (int) $electionId,
                'organization_id' => $election['organization_id'] ?? null,
                'region_id' => $request['voter_region_id'] ?? ($election['region_id'] ?? null),
            ]
        );

        Session::flash('success', 'Link upload berhasil dibuat ulang. Copy link dan kirim ke pemilih.');
        Redirect::to('/elections/' . $electionId . '/remote-verifications/' . $id);
    }
}