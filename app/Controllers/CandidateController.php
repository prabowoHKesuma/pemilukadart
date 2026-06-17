<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Env;
use App\Core\Redirect;
use App\Core\Session;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\AuditLog;

class CandidateController extends Controller
{
    public function index(string $electionId): void
    {
        Auth::requireLogin();

        $election = Election::find((int) $electionId);

        if (!$election) {
            http_response_code(404);
            die('Data pemilihan tidak ditemukan.');
        }

        $candidates = Candidate::allByElection((int) $electionId);

        $this->view('candidates/index', [
            'title' => 'Data Kandidat',
            'election' => $election,
            'candidates' => $candidates,
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

        if ($election['status'] !== 'draft') {
            Session::flash('error', 'Kandidat hanya boleh ditambahkan saat status pemilihan masih draft.');
            Redirect::to('/elections/' . $electionId . '/candidates');
        }

        $this->view('candidates/create', [
            'title' => 'Tambah Kandidat',
            'election' => $election,
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

        if ($election['status'] !== 'draft') {
            Session::flash('error', 'Kandidat hanya boleh ditambahkan saat status pemilihan masih draft.');
            Redirect::to('/elections/' . $electionId . '/candidates');
        }

        $numberOrder = (int) ($_POST['number_order'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $vision = trim($_POST['vision'] ?? '');
        $mission = trim($_POST['mission'] ?? '');
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if ($numberOrder <= 0) {
            Session::flash('error', 'Nomor urut kandidat wajib diisi dan harus lebih dari 0.');
            Redirect::to('/elections/' . $electionId . '/candidates/create');
        }

        if ($name === '') {
            Session::flash('error', 'Nama kandidat wajib diisi.');
            Redirect::to('/elections/' . $electionId . '/candidates/create');
        }

        if (Candidate::numberExists((int) $electionId, $numberOrder)) {
            Session::flash('error', 'Nomor urut kandidat sudah digunakan.');
            Redirect::to('/elections/' . $electionId . '/candidates/create');
        }

        $photoPath = $this->uploadPhoto('photo');

        Candidate::create([
            'election_id' => (int) $electionId,
            'number_order' => $numberOrder,
            'name' => $name,
            'photo' => $photoPath,
            'vision' => $vision !== '' ? $vision : null,
            'mission' => $mission !== '' ? $mission : null,
            'is_active' => $isActive,
        ]);

        AuditLog::record(
            'candidate_create',
            'Menambahkan kandidat "' . $name . '" nomor urut ' . $numberOrder . ' pada election ID ' . $electionId
        );

        Session::flash('success', 'Data kandidat berhasil ditambahkan.');
        Redirect::to('/elections/' . $electionId . '/candidates');
    }

    public function edit(string $electionId, string $id): void
    {
        Auth::requireRole(['superadmin', 'panitia']);

        $election = Election::find((int) $electionId);

        if (!$election) {
            http_response_code(404);
            die('Data pemilihan tidak ditemukan.');
        }

        $candidate = Candidate::findByElection((int) $electionId, (int) $id);

        if (!$candidate) {
            http_response_code(404);
            die('Data kandidat tidak ditemukan.');
        }

        if ($election['status'] !== 'draft') {
            Session::flash('error', 'Kandidat hanya boleh diedit saat status pemilihan masih draft.');
            Redirect::to('/elections/' . $electionId . '/candidates');
        }

        $this->view('candidates/edit', [
            'title' => 'Edit Kandidat',
            'election' => $election,
            'candidate' => $candidate,
        ]);
    }

    public function update(string $electionId, string $id): void
    {
        Auth::requireRole(['superadmin', 'panitia']);
        Csrf::verify();

        $election = Election::find((int) $electionId);

        if (!$election) {
            http_response_code(404);
            die('Data pemilihan tidak ditemukan.');
        }

        $candidate = Candidate::findByElection((int) $electionId, (int) $id);

        if (!$candidate) {
            http_response_code(404);
            die('Data kandidat tidak ditemukan.');
        }

        if ($election['status'] !== 'draft') {
            Session::flash('error', 'Kandidat hanya boleh diperbarui saat status pemilihan masih draft.');
            Redirect::to('/elections/' . $electionId . '/candidates');
        }

        $numberOrder = (int) ($_POST['number_order'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $vision = trim($_POST['vision'] ?? '');
        $mission = trim($_POST['mission'] ?? '');
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if ($numberOrder <= 0) {
            Session::flash('error', 'Nomor urut kandidat wajib diisi dan harus lebih dari 0.');
            Redirect::to('/elections/' . $electionId . '/candidates/' . $id . '/edit');
        }

        if ($name === '') {
            Session::flash('error', 'Nama kandidat wajib diisi.');
            Redirect::to('/elections/' . $electionId . '/candidates/' . $id . '/edit');
        }

        if (Candidate::numberExists((int) $electionId, $numberOrder, (int) $id)) {
            Session::flash('error', 'Nomor urut kandidat sudah digunakan.');
            Redirect::to('/elections/' . $electionId . '/candidates/' . $id . '/edit');
        }

        $photoPath = $candidate['photo'];

        $newPhoto = $this->uploadPhoto('photo');

        if ($newPhoto) {
            $this->deleteOldPhoto($candidate['photo']);
            $photoPath = $newPhoto;
        }

        Candidate::update((int) $id, [
            'number_order' => $numberOrder,
            'name' => $name,
            'photo' => $photoPath,
            'vision' => $vision !== '' ? $vision : null,
            'mission' => $mission !== '' ? $mission : null,
            'is_active' => $isActive,
        ]);

        AuditLog::record(
            'candidate_update',
            'Memperbarui kandidat ID ' . $id . ' menjadi "' . $name . '" nomor urut ' . $numberOrder . ' pada election ID ' . $electionId
        );

        Session::flash('success', 'Data kandidat berhasil diperbarui.');
        Redirect::to('/elections/' . $electionId . '/candidates');
    }

    public function destroy(string $electionId, string $id): void
    {
        Auth::requireRole(['superadmin']);
        Csrf::verify();

        $election = Election::find((int) $electionId);

        if (!$election) {
            http_response_code(404);
            die('Data pemilihan tidak ditemukan.');
        }

        $candidate = Candidate::findByElection((int) $electionId, (int) $id);

        if (!$candidate) {
            http_response_code(404);
            die('Data kandidat tidak ditemukan.');
        }

        if ($election['status'] !== 'draft') {
            Session::flash('error', 'Kandidat hanya boleh dihapus saat status pemilihan masih draft.');
            Redirect::to('/elections/' . $electionId . '/candidates');
        }

        Candidate::delete((int) $id);

        $this->deleteOldPhoto($candidate['photo']);

        AuditLog::record(
            'candidate_delete',
            'Menghapus kandidat ID ' . $id . ' "' . $candidate['name'] . '" pada election ID ' . $electionId
        );

        Session::flash('success', 'Data kandidat berhasil dihapus.');
        Redirect::to('/elections/' . $electionId . '/candidates');
    }

    private function uploadPhoto(string $inputName): ?string
    {
        if (
            !isset($_FILES[$inputName]) ||
            $_FILES[$inputName]['error'] === UPLOAD_ERR_NO_FILE
        ) {
            return null;
        }

        if ($_FILES[$inputName]['error'] !== UPLOAD_ERR_OK) {
            Session::flash('error', 'Upload foto gagal.');
            Redirect::back();
        }

        $maxSize = 2 * 1024 * 1024;

        if ($_FILES[$inputName]['size'] > $maxSize) {
            Session::flash('error', 'Ukuran foto maksimal 2MB.');
            Redirect::back();
        }

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

        $originalName = $_FILES[$inputName]['name'];
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (!in_array($extension, $allowedExtensions, true)) {
            Session::flash('error', 'Format foto harus JPG, JPEG, PNG, atau WEBP.');
            Redirect::back();
        }

        $tmpName = $_FILES[$inputName]['tmp_name'];

        $imageInfo = getimagesize($tmpName);

        if ($imageInfo === false) {
            Session::flash('error', 'File yang diupload bukan gambar valid.');
            Redirect::back();
        }

        $uploadDir = dirname(__DIR__, 2) . '/public/assets/uploads/candidates/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $fileName = 'candidate_' . date('YmdHis') . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
        $targetPath = $uploadDir . $fileName;

        if (!move_uploaded_file($tmpName, $targetPath)) {
            Session::flash('error', 'Gagal menyimpan foto kandidat.');
            Redirect::back();
        }

        return 'assets/uploads/candidates/' . $fileName;
    }

    private function deleteOldPhoto(?string $photoPath): void
    {
        if (!$photoPath) {
            return;
        }

        $fullPath = dirname(__DIR__, 2) . '/public/' . ltrim($photoPath, '/');

        if (file_exists($fullPath) && is_file($fullPath)) {
            unlink($fullPath);
        }
    }
}