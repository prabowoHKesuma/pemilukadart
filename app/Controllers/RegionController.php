<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Redirect;
use App\Core\Session;
use App\Models\AuditLog;
use App\Models\Organization;
use App\Models\Region;

class RegionController extends Controller
{
    public function index(): void
    {
        Auth::requirePermission('manage_regions');

        $regions = Region::all();

        $this->view('regions/index', [
            'title' => 'Region Management',
            'regions' => $regions,
        ]);
    }

    public function create(): void
    {
        Auth::requirePermission('manage_regions');

        $organizations = Organization::options();
        $regions = Region::options();
        $levels = Region::levels();

        $this->view('regions/create', [
            'title' => 'Tambah Wilayah',
            'organizations' => $organizations,
            'regions' => $regions,
            'levels' => $levels,
        ]);
    }

    public function store(): void
    {
        Auth::requirePermission('manage_regions');
        Csrf::verify();

        $organizationId = (int) ($_POST['organization_id'] ?? 0);
        $parentId = !empty($_POST['parent_id']) ? (int) $_POST['parent_id'] : null;
        $level = trim($_POST['level'] ?? '');
        $code = $this->normalizeCode($_POST['code'] ?? '');
        $name = trim($_POST['name'] ?? '');

        $organization = Organization::find($organizationId);

        if (!$organization) {
            Session::flash('error', 'Organization tidak valid.');
            Redirect::to('/regions/create');
        }

        if (!array_key_exists($level, Region::levels())) {
            Session::flash('error', 'Level wilayah tidak valid.');
            Redirect::to('/regions/create');
        }

        if ($code === '') {
            Session::flash('error', 'Kode wilayah wajib diisi.');
            Redirect::to('/regions/create');
        }

        if ($name === '') {
            Session::flash('error', 'Nama wilayah wajib diisi.');
            Redirect::to('/regions/create');
        }

        if (Region::codeExists($organizationId, $code)) {
            Session::flash('error', 'Kode wilayah sudah digunakan dalam organization ini.');
            Redirect::to('/regions/create');
        }

        if ($parentId) {
            $parent = Region::find($parentId);

            if (!$parent) {
                Session::flash('error', 'Parent wilayah tidak valid.');
                Redirect::to('/regions/create');
            }

            if ((int) $parent['organization_id'] !== $organizationId) {
                Session::flash('error', 'Parent wilayah harus berada dalam organization yang sama.');
                Redirect::to('/regions/create');
            }
        }

        Region::create([
            'organization_id' => $organizationId,
            'parent_id' => $parentId,
            'level' => $level,
            'code' => $code,
            'name' => $name,
        ]);

        AuditLog::record(
            'region_create',
            'Membuat wilayah: ' . $code . ' - ' . $name
        );

        Session::flash('success', 'Wilayah berhasil ditambahkan.');
        Redirect::to('/regions');
    }

    public function edit(string $id): void
    {
        Auth::requirePermission('manage_regions');

        $region = Region::find((int) $id);

        if (!$region) {
            http_response_code(404);
            die('Wilayah tidak ditemukan.');
        }

        $organizations = Organization::options();
        $regions = Region::options((int) $region['organization_id'], (int) $id);
        $levels = Region::levels();

        $this->view('regions/edit', [
            'title' => 'Edit Wilayah',
            'region' => $region,
            'organizations' => $organizations,
            'regions' => $regions,
            'levels' => $levels,
        ]);
    }

    public function update(string $id): void
    {
        Auth::requirePermission('manage_regions');
        Csrf::verify();

        $region = Region::find((int) $id);

        if (!$region) {
            http_response_code(404);
            die('Wilayah tidak ditemukan.');
        }

        $organizationId = (int) ($_POST['organization_id'] ?? 0);
        $parentId = !empty($_POST['parent_id']) ? (int) $_POST['parent_id'] : null;
        $level = trim($_POST['level'] ?? '');
        $code = $this->normalizeCode($_POST['code'] ?? '');
        $name = trim($_POST['name'] ?? '');

        $organization = Organization::find($organizationId);

        if (!$organization) {
            Session::flash('error', 'Organization tidak valid.');
            Redirect::to('/regions/' . $id . '/edit');
        }

        if (!array_key_exists($level, Region::levels())) {
            Session::flash('error', 'Level wilayah tidak valid.');
            Redirect::to('/regions/' . $id . '/edit');
        }

        if ($code === '') {
            Session::flash('error', 'Kode wilayah wajib diisi.');
            Redirect::to('/regions/' . $id . '/edit');
        }

        if ($name === '') {
            Session::flash('error', 'Nama wilayah wajib diisi.');
            Redirect::to('/regions/' . $id . '/edit');
        }

        if (Region::codeExists($organizationId, $code, (int) $id)) {
            Session::flash('error', 'Kode wilayah sudah digunakan dalam organization ini.');
            Redirect::to('/regions/' . $id . '/edit');
        }

        if ($parentId) {
            if ($parentId === (int) $id) {
                Session::flash('error', 'Wilayah tidak boleh menjadi parent dirinya sendiri.');
                Redirect::to('/regions/' . $id . '/edit');
            }

            $parent = Region::find($parentId);

            if (!$parent) {
                Session::flash('error', 'Parent wilayah tidak valid.');
                Redirect::to('/regions/' . $id . '/edit');
            }

            if ((int) $parent['organization_id'] !== $organizationId) {
                Session::flash('error', 'Parent wilayah harus berada dalam organization yang sama.');
                Redirect::to('/regions/' . $id . '/edit');
            }

            if (Region::isDescendantOf($parentId, (int) $id)) {
                Session::flash('error', 'Parent tidak boleh berasal dari child wilayah ini.');
                Redirect::to('/regions/' . $id . '/edit');
            }
        }

        Region::update((int) $id, [
            'organization_id' => $organizationId,
            'parent_id' => $parentId,
            'level' => $level,
            'code' => $code,
            'name' => $name,
        ]);

        AuditLog::record(
            'region_update',
            'Memperbarui wilayah ID ' . $id . ': ' . $code . ' - ' . $name
        );

        Session::flash('success', 'Wilayah berhasil diperbarui.');
        Redirect::to('/regions');
    }

    public function destroy(string $id): void
    {
        Auth::requirePermission('manage_regions');
        Csrf::verify();

        $region = Region::find((int) $id);

        if (!$region) {
            http_response_code(404);
            die('Wilayah tidak ditemukan.');
        }

        if (Region::countChildren((int) $id) > 0) {
            Session::flash('error', 'Wilayah tidak bisa dihapus karena masih punya child wilayah.');
            Redirect::to('/regions');
        }

        $usage = Region::usageCount((int) $id);

        if ($usage['users'] > 0 || $usage['voters'] > 0 || $usage['elections'] > 0) {
            Session::flash('error', 'Wilayah tidak bisa dihapus karena sudah digunakan user, pemilih, atau pemilihan.');
            Redirect::to('/regions');
        }

        Region::delete((int) $id);

        AuditLog::record(
            'region_delete',
            'Menghapus wilayah ID ' . $id . ': ' . $region['code'] . ' - ' . $region['name']
        );

        Session::flash('success', 'Wilayah berhasil dihapus.');
        Redirect::to('/regions');
    }

    private function normalizeCode(string $code): string
    {
        $code = strtoupper(trim($code));
        $code = preg_replace('/\s+/', '-', $code);
        $code = preg_replace('/[^A-Z0-9_-]/', '', $code);

        return $code;
    }
}