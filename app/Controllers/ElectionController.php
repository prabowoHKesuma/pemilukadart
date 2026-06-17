<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Redirect;
use App\Core\Session;
use App\Models\Election;
use App\Models\AuditLog;

class ElectionController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();

        $elections = Election::all();

        $this->view('elections/index', [
            'title' => 'Data Pemilihan',
            'elections' => $elections,
        ]);
    }

    public function create(): void
    {
        Auth::requireRole(['superadmin', 'panitia']);

        $this->view('elections/create', [
            'title' => 'Tambah Pemilihan',
        ]);
    }

    public function store(): void
    {
        Auth::requireRole(['superadmin', 'panitia']);
        Csrf::verify();

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $status = $_POST['status'] ?? 'draft';
        $startAt = $this->normalizeDateTime($_POST['start_at'] ?? null);
        $endAt = $this->normalizeDateTime($_POST['end_at'] ?? null);

        if ($title === '') {
            Session::flash('error', 'Nama pemilihan wajib diisi.');
            Redirect::to('/elections/create');
        }

        if (!in_array($status, ['draft', 'open', 'closed', 'finished'], true)) {
            Session::flash('error', 'Status pemilihan tidak valid.');
            Redirect::to('/elections/create');
        }

        if ($startAt && $endAt && strtotime($startAt) > strtotime($endAt)) {
            Session::flash('error', 'Tanggal mulai tidak boleh lebih besar dari tanggal selesai.');
            Redirect::to('/elections/create');
        }

        Election::create([
            'title' => $title,
            'description' => $description !== '' ? $description : null,
            'status' => $status,
            'start_at' => $startAt,
            'end_at' => $endAt,
            'created_by' => Auth::id(),
        ]);

        AuditLog::record(
            'election_create',
            'Membuat pemilihan: ' . $title . ' dengan status ' . $status
        );

        Session::flash('success', 'Data pemilihan berhasil ditambahkan.');
        Redirect::to('/elections');
    }

    public function edit(string $id): void
    {
        Auth::requireRole(['superadmin', 'panitia']);

        $election = Election::find((int) $id);

        if (!$election) {
            http_response_code(404);
            die('Data pemilihan tidak ditemukan.');
        }

        $this->view('elections/edit', [
            'title' => 'Edit Pemilihan',
            'election' => $election,
        ]);
    }

    public function update(string $id): void
    {
        Auth::requireRole(['superadmin', 'panitia']);
        Csrf::verify();

        $election = Election::find((int) $id);

        if (!$election) {
            http_response_code(404);
            die('Data pemilihan tidak ditemukan.');
        }

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $status = $_POST['status'] ?? 'draft';
        $startAt = $this->normalizeDateTime($_POST['start_at'] ?? null);
        $endAt = $this->normalizeDateTime($_POST['end_at'] ?? null);

        if ($title === '') {
            Session::flash('error', 'Nama pemilihan wajib diisi.');
            Redirect::to('/elections/' . $id . '/edit');
        }

        if (!in_array($status, ['draft', 'open', 'closed', 'finished'], true)) {
            Session::flash('error', 'Status pemilihan tidak valid.');
            Redirect::to('/elections/' . $id . '/edit');
        }

        if ($startAt && $endAt && strtotime($startAt) > strtotime($endAt)) {
            Session::flash('error', 'Tanggal mulai tidak boleh lebih besar dari tanggal selesai.');
            Redirect::to('/elections/' . $id . '/edit');
        }

        Election::update((int) $id, [
            'title' => $title,
            'description' => $description !== '' ? $description : null,
            'status' => $status,
            'start_at' => $startAt,
            'end_at' => $endAt,
        ]);

        AuditLog::record(
            'election_update',
            'Memperbarui pemilihan ID ' . $id . ': ' . $title . ' dengan status ' . $status
        );

        Session::flash('success', 'Data pemilihan berhasil diperbarui.');
        Redirect::to('/elections');
    }

    public function destroy(string $id): void
    {
        Auth::requireRole(['superadmin']);
        Csrf::verify();

        $election = Election::find((int) $id);

        if (!$election) {
            http_response_code(404);
            die('Data pemilihan tidak ditemukan.');
        }

        if ($election['status'] !== 'draft') {
            Session::flash('error', 'Pemilihan hanya boleh dihapus saat status masih draft.');
            Redirect::to('/elections');
        }

        Election::delete((int) $id);

        AuditLog::record(
            'election_delete',
            'Menghapus pemilihan ID ' . $id . ': ' . $election['title']
        );

        Session::flash('success', 'Data pemilihan berhasil dihapus.');
        Redirect::to('/elections');
    }

    public function changeStatus(string $id): void
    {
        Auth::requireRole(['superadmin', 'panitia']);
        Csrf::verify();

        $election = Election::find((int) $id);

        if (!$election) {
            http_response_code(404);
            die('Data pemilihan tidak ditemukan.');
        }

        $status = $_POST['status'] ?? '';

        if (!in_array($status, ['draft', 'open', 'closed', 'finished'], true)) {
            Session::flash('error', 'Status tidak valid.');
            Redirect::to('/elections');
        }

        Election::updateStatus((int) $id, $status);

        AuditLog::record(
            'election_status_update',
            'Mengubah status pemilihan ID ' . $id . ' dari ' . $election['status'] . ' ke ' . $status
        );

        Session::flash('success', 'Status pemilihan berhasil diubah.');
        Redirect::to('/elections');
    }

    private function normalizeDateTime(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        $timestamp = strtotime($value);

        if (!$timestamp) {
            return null;
        }

        return date('Y-m-d H:i:s', $timestamp);
    }
}