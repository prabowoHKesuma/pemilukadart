<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Redirect;
use App\Core\Session;
use App\Models\AuditLog;
use App\Models\Election;
use App\Models\RemoteVerification;

class RemoteVerificationController extends Controller
{
    public function index(): void
    {
        Auth::requireRole(['superadmin', 'panitia']);

        $elections = RemoteVerification::electionsWithStats();

        $this->view('remote_verifications/index', [
            'title' => 'Verifikasi Remote',
            'elections' => $elections,
        ]);
    }

    public function election(string $electionId): void
    {
        Auth::requireRole(['superadmin', 'panitia']);

        $election = Election::find((int) $electionId);

        if (!$election) {
            http_response_code(404);
            die('Data pemilihan tidak ditemukan.');
        }

        $requests = RemoteVerification::allByElection((int) $electionId);

        $this->view('remote_verifications/election', [
            'title' => 'Verifikasi Remote Pemilihan',
            'election' => $election,
            'requests' => $requests,
        ]);
    }

    public function create(string $electionId): void
    {
        Auth::requireRole(['superadmin', 'panitia']);

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
        Auth::requireRole(['superadmin', 'panitia']);
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

        RemoteVerification::create([
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
        Redirect::to('/elections/' . $electionId . '/remote-verifications');
    }

    public function show(string $electionId, string $id): void
    {
        Auth::requireRole(['superadmin', 'panitia']);

        $election = Election::find((int) $electionId);

        if (!$election) {
            http_response_code(404);
            die('Data pemilihan tidak ditemukan.');
        }

        $request = RemoteVerification::find((int) $id);

        if (!$request || (int) $request['election_id'] !== (int) $electionId) {
            http_response_code(404);
            die('Request verifikasi remote tidak ditemukan.');
        }

        $this->view('remote_verifications/show', [
            'title' => 'Detail Verifikasi Remote',
            'election' => $election,
            'request' => $request,
        ]);
    }

    public function upload(string $electionId, string $id): void
    {
        Auth::requireRole(['superadmin', 'panitia']);
        Csrf::verify();

        $request = RemoteVerification::find((int) $id);

        if (!$request || (int) $request['election_id'] !== (int) $electionId) {
            http_response_code(404);
            die('Request verifikasi remote tidak ditemukan.');
        }

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
            'Upload foto KTP dan selfie untuk remote verification ID ' . $id . ' pada election ID ' . $electionId
        );

        Session::flash('success', 'Foto verifikasi berhasil diupload.');
        Redirect::to('/elections/' . $electionId . '/remote-verifications/' . $id);
    }

    public function approve(string $electionId, string $id): void
    {
        Auth::requireRole(['superadmin', 'panitia']);
        Csrf::verify();

        $request = RemoteVerification::find((int) $id);

        if (!$request || (int) $request['election_id'] !== (int) $electionId) {
            http_response_code(404);
            die('Request verifikasi remote tidak ditemukan.');
        }

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
                'Approval pertama remote verification ID ' . $id . ' pada election ID ' . $electionId
            );

            Session::flash('success', 'Approval pertama berhasil. Menunggu approval kedua dari user berbeda.');
            Redirect::to('/elections/' . $electionId . '/remote-verifications/' . $id);
        }

        if ((int) $request['verified_by_1'] === (int) $userId) {
            Session::flash('error', 'Approval kedua harus dilakukan oleh user berbeda.');
            Redirect::to('/elections/' . $electionId . '/remote-verifications/' . $id);
        }

        RemoteVerification::approveSecond((int) $id, (int) $userId);

        AuditLog::record(
            'remote_verification_approve_2',
            'Approval kedua remote verification ID ' . $id . ' pada election ID ' . $electionId . '. Status menjadi approved.'
        );

        Session::flash('success', 'Approval kedua berhasil. Verifikasi remote disetujui.');
        Redirect::to('/elections/' . $electionId . '/remote-verifications/' . $id);
    }

    public function reject(string $electionId, string $id): void
    {
        Auth::requireRole(['superadmin', 'panitia']);
        Csrf::verify();

        $request = RemoteVerification::find((int) $id);

        if (!$request || (int) $request['election_id'] !== (int) $electionId) {
            http_response_code(404);
            die('Request verifikasi remote tidak ditemukan.');
        }

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
            'Menolak remote verification ID ' . $id . ' pada election ID ' . $electionId . '. Alasan: ' . $reason
        );

        Session::flash('success', 'Verifikasi remote berhasil ditolak.');
        Redirect::to('/elections/' . $electionId . '/remote-verifications/' . $id);
    }

    public function file(string $id, string $type): void
    {
        Auth::requireRole(['superadmin', 'panitia']);

        $request = RemoteVerification::find((int) $id);

        if (!$request) {
            http_response_code(404);
            die('File tidak ditemukan.');
        }

        if (!in_array($type, ['ktp', 'selfie'], true)) {
            http_response_code(404);
            die('Tipe file tidak valid.');
        }

        $path = $type === 'ktp'
            ? $request['ktp_photo_path']
            : $request['selfie_photo_path'];

        if (!$path) {
            http_response_code(404);
            die('File belum diupload.');
        }

        $fullPath = dirname(__DIR__, 2) . '/' . ltrim($path, '/');

        if (!file_exists($fullPath) || !is_file($fullPath)) {
            http_response_code(404);
            die('File fisik tidak ditemukan.');
        }

        $mime = mime_content_type($fullPath) ?: 'application/octet-stream';

        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($fullPath));
        header('X-Content-Type-Options: nosniff');

        readfile($fullPath);
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
}