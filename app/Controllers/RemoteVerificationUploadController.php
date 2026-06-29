<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Env;
use App\Core\Redirect;
use App\Core\Session;
use App\Models\AuditLog;
use App\Models\RemoteVerification;

class RemoteVerificationUploadController extends Controller
{
    public function show(string $plainToken): void
    {
        $request = RemoteVerification::findByUploadToken($plainToken);

        $validation = $this->validateUploadRequest($request);

        if ($validation !== true) {
            $this->view('remote_verification_upload/invalid', [
                'title' => 'Link Upload Tidak Valid',
                'message' => $validation,
            ], 'remote');

            return;
        }

        $this->view('remote_verification_upload/form', [
            'title' => 'Upload Verifikasi Remote',
            'plainToken' => $plainToken,
            'request' => $request,
        ], 'remote');
    }

    public function submit(string $plainToken): void
    {
        Csrf::verify();

        $request = RemoteVerification::findByUploadToken($plainToken);

        $validation = $this->validateUploadRequest($request);

        if ($validation !== true) {
            $this->view('remote_verification_upload/invalid', [
                'title' => 'Link Upload Tidak Valid',
                'message' => $validation,
            ], 'remote');

            return;
        }

        if (empty($_POST['consent_accepted'])) {
            Session::flash('error', 'Persetujuan penggunaan data wajib dicentang.');
            Redirect::to('/remote-verification-upload/' . $plainToken);
        }

        if (empty($_FILES['ktp_photo']) || empty($_FILES['selfie_photo'])) {
            Session::flash('error', 'Foto KTP dan selfie wajib diupload.');
            Redirect::to('/remote-verification-upload/' . $plainToken);
        }

        $ktpPath = $this->storeImage($_FILES['ktp_photo'], 'ktp', (int) $request['id']);
        $selfiePath = $this->storeImage($_FILES['selfie_photo'], 'selfie', (int) $request['id']);

        RemoteVerification::markPublicUploaded(
            (int) $request['id'],
            $ktpPath,
            $selfiePath
        );

        AuditLog::record(
            'remote_verification_public_upload',
            'Pemilih upload foto verifikasi remote ID ' . $request['id'],
            null,
            [
                'election_id' => (int) $request['election_id'],
            ]
        );

        $this->view('remote_verification_upload/success', [
            'title' => 'Upload Berhasil',
            'request' => $request,
        ], 'remote');
    }

    private function validateUploadRequest(?array $request): true|string
    {
        if (!$request) {
            return 'Link upload tidak ditemukan.';
        }

        if (($request['status'] ?? '') !== 'pending') {
            return 'Request verifikasi ini sudah tidak aktif.';
        }

        if (!empty($request['upload_token_expires_at']) && strtotime($request['upload_token_expires_at']) <= time()) {
            return 'Link upload sudah expired. Hubungi panitia untuk meminta link baru.';
        }

        if ((int) ($request['is_active'] ?? 0) !== 1) {
            return 'Status pemilih tidak aktif.';
        }

        if ((int) ($request['has_voted'] ?? 0) === 1) {
            return 'Pemilih ini sudah tercatat mencoblos.';
        }

        if (!in_array($request['allowed_channel'] ?? '', ['remote', 'both'], true)) {
            return 'Pemilih ini tidak memiliki hak verifikasi remote.';
        }

        if (!in_array($request['election_status'] ?? '', ['draft', 'open'], true)) {
            return 'Pemilihan sudah dikunci.';
        }

        return true;
    }

    private function storeImage(array $file, string $type, int $requestId): string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            Session::flash('error', 'Upload file gagal.');
            Redirect::back();
        }

        $maxSize = 3 * 1024 * 1024;

        if ((int) $file['size'] > $maxSize) {
            Session::flash('error', 'Ukuran file maksimal 3MB.');
            Redirect::back();
        }

        $allowedMime = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];

        $mime = mime_content_type($file['tmp_name']);

        if (!isset($allowedMime[$mime])) {
            Session::flash('error', 'Format file harus JPG, PNG, atau WEBP.');
            Redirect::back();
        }

        $extension = $allowedMime[$mime];

        //$dir = BASE_PATH . '/storage/remote_verifications/' . $requestId;

        $dir = realpath(dirname(__DIR__, 2) . '/storage/private/verifications/election_' . $requestId . '/');

        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $filename = $type . '_' . date('YmdHis') . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
        $targetPath = $dir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            Session::flash('error', 'Gagal menyimpan file.');
            Redirect::back();
        }

        return '/storage/private/verifications/election_' . $requestId . '/' . $filename;
    }
}