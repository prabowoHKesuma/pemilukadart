<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Env;
use App\Core\Redirect;
use App\Core\Session;
use App\Models\Voter;
use App\Models\AuditLog;

class VoterController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();

        $voters = Voter::all();

        $this->view('voters/index', [
            'title' => 'Data Pemilih',
            'voters' => $voters,
        ]);
    }

    public function create(): void
    {
        Auth::requireRole(['superadmin', 'panitia']);

        $this->view('voters/create', [
            'title' => 'Tambah Pemilih',
        ]);
    }

    public function store(): void
    {
        Auth::requireRole(['superadmin', 'panitia']);
        Csrf::verify();

        $voterCode = strtoupper(trim($_POST['voter_code'] ?? ''));
        $name = trim($_POST['name'] ?? '');
        $nik = $this->onlyDigits($_POST['nik'] ?? '');
        $kk = $this->onlyDigits($_POST['kk'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $rt = trim($_POST['rt'] ?? '');
        $rw = trim($_POST['rw'] ?? '');
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if ($voterCode === '') {
            $voterCode = $this->generateVoterCode();
        }

        if ($name === '') {
            Session::flash('error', 'Nama pemilih wajib diisi.');
            Redirect::to('/voters/create');
        }

        if ($nik !== '' && strlen($nik) !== 16) {
            Session::flash('error', 'NIK harus 16 digit.');
            Redirect::to('/voters/create');
        }

        if ($kk !== '' && strlen($kk) !== 16) {
            Session::flash('error', 'Nomor KK harus 16 digit.');
            Redirect::to('/voters/create');
        }

        if (Voter::voterCodeExists($voterCode)) {
            Session::flash('error', 'Kode pemilih sudah digunakan.');
            Redirect::to('/voters/create');
        }

        $nikHash = $nik !== '' ? $this->hashIdentity($nik) : null;
        $kkHash = $kk !== '' ? $this->hashIdentity($kk) : null;

        if ($nikHash && Voter::nikHashExists($nikHash)) {
            Session::flash('error', 'NIK ini sudah terdaftar sebagai pemilih.');
            Redirect::to('/voters/create');
        }

        Voter::create([
            'voter_code' => $voterCode,
            'name' => $name,
            'nik_hash' => $nikHash,
            'kk_hash' => $kkHash,
            'address' => $address !== '' ? $address : null,
            'phone' => $phone !== '' ? $phone : null,
            'rt' => $rt !== '' ? $rt : null,
            'rw' => $rw !== '' ? $rw : null,
            'is_active' => $isActive,
        ]);

        AuditLog::record(
            'voter_create',
            'Menambahkan pemilih: ' . $name . ' dengan kode ' . $voterCode
        );

        Session::flash('success', 'Data pemilih berhasil ditambahkan.');
        Redirect::to('/voters');
    }

    public function edit(string $id): void
    {
        Auth::requireRole(['superadmin', 'panitia']);

        $voter = Voter::find((int) $id);

        if (!$voter) {
            http_response_code(404);
            die('Data pemilih tidak ditemukan.');
        }

        $this->view('voters/edit', [
            'title' => 'Edit Pemilih',
            'voter' => $voter,
        ]);
    }

    public function update(string $id): void
    {
        Auth::requireRole(['superadmin', 'panitia']);
        Csrf::verify();

        $voter = Voter::find((int) $id);

        if (!$voter) {
            http_response_code(404);
            die('Data pemilih tidak ditemukan.');
        }

        $voterCode = strtoupper(trim($_POST['voter_code'] ?? ''));
        $name = trim($_POST['name'] ?? '');
        $nik = $this->onlyDigits($_POST['nik'] ?? '');
        $kk = $this->onlyDigits($_POST['kk'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $rt = trim($_POST['rt'] ?? '');
        $rw = trim($_POST['rw'] ?? '');
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if ($voterCode === '') {
            Session::flash('error', 'Kode pemilih wajib diisi.');
            Redirect::to('/voters/' . $id . '/edit');
        }

        if ($name === '') {
            Session::flash('error', 'Nama pemilih wajib diisi.');
            Redirect::to('/voters/' . $id . '/edit');
        }

        if ($nik !== '' && strlen($nik) !== 16) {
            Session::flash('error', 'NIK harus 16 digit.');
            Redirect::to('/voters/' . $id . '/edit');
        }

        if ($kk !== '' && strlen($kk) !== 16) {
            Session::flash('error', 'Nomor KK harus 16 digit.');
            Redirect::to('/voters/' . $id . '/edit');
        }

        if (Voter::voterCodeExists($voterCode, (int) $id)) {
            Session::flash('error', 'Kode pemilih sudah digunakan.');
            Redirect::to('/voters/' . $id . '/edit');
        }

        $nikHash = $voter['nik_hash'];
        $kkHash = $voter['kk_hash'];

        if ($nik !== '') {
            $newNikHash = $this->hashIdentity($nik);

            if (Voter::nikHashExists($newNikHash, (int) $id)) {
                Session::flash('error', 'NIK ini sudah terdaftar sebagai pemilih lain.');
                Redirect::to('/voters/' . $id . '/edit');
            }

            $nikHash = $newNikHash;
        }

        if ($kk !== '') {
            $kkHash = $this->hashIdentity($kk);
        }

        Voter::update((int) $id, [
            'voter_code' => $voterCode,
            'name' => $name,
            'nik_hash' => $nikHash,
            'kk_hash' => $kkHash,
            'address' => $address !== '' ? $address : null,
            'phone' => $phone !== '' ? $phone : null,
            'rt' => $rt !== '' ? $rt : null,
            'rw' => $rw !== '' ? $rw : null,
            'is_active' => $isActive,
        ]);

        AuditLog::record(
            'voter_update',
            'Memperbarui pemilih ID ' . $id . ': ' . $name . ' dengan kode ' . $voterCode
        );

        Session::flash('success', 'Data pemilih berhasil diperbarui.');
        Redirect::to('/voters');
    }

    public function destroy(string $id): void
    {
        Auth::requireRole(['superadmin']);
        Csrf::verify();

        $voter = Voter::find((int) $id);

        if (!$voter) {
            http_response_code(404);
            die('Data pemilih tidak ditemukan.');
        }

        if (Voter::hasElectionRelation((int) $id)) {
            Session::flash('error', 'Pemilih tidak boleh dihapus karena sudah terhubung dengan data pemilihan. Nonaktifkan saja.');
            Redirect::to('/voters');
        }

        Voter::delete((int) $id);

        AuditLog::record(
            'voter_delete',
            'Menghapus pemilih ID ' . $id . ': ' . $voter['name'] . ' dengan kode ' . $voter['voter_code']
        );

        Session::flash('success', 'Data pemilih berhasil dihapus.');
        Redirect::to('/voters');
    }

    private function onlyDigits(string $value): string
    {
        return preg_replace('/\D/', '', $value);
    }

    private function hashIdentity(string $value): string
    {
        $appKey = Env::get('APP_KEY', 'fallback_key_change_this');

        return hash_hmac('sha256', $value, $appKey);
    }

    private function generateVoterCode(): string
    {
        do {
            $code = 'PM-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
        } while (Voter::voterCodeExists($code));

        return $code;
    }
}