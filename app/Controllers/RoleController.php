<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Redirect;
use App\Core\Session;
use App\Models\AuditLog;
use App\Models\Permission;
use App\Models\Role;

class RoleController extends Controller
{
    public function index(): void
    {
        Auth::requirePermission('manage_roles');

        $roles = Role::all();

        $this->view('roles/index', [
            'title' => 'Role Management',
            'roles' => $roles,
        ]);
    }

    public function create(): void
    {
        Auth::requirePermission('manage_roles');

        $groupedPermissions = Permission::grouped();

        $this->view('roles/create', [
            'title' => 'Tambah Role',
            'groupedPermissions' => $groupedPermissions,
        ]);
    }

    public function store(): void
    {
        Auth::requirePermission('manage_roles');
        Csrf::verify();

        $name = $this->normalizeRoleName($_POST['name'] ?? '');
        $label = trim($_POST['label'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $permissionIds = $_POST['permission_ids'] ?? [];

        if ($name === '') {
            Session::flash('error', 'Nama role wajib diisi.');
            Redirect::to('/roles/create');
        }

        if ($label === '') {
            Session::flash('error', 'Label role wajib diisi.');
            Redirect::to('/roles/create');
        }

        if (!preg_match('/^[a-z0-9_]+$/', $name)) {
            Session::flash('error', 'Nama role hanya boleh huruf kecil, angka, dan underscore.');
            Redirect::to('/roles/create');
        }

        if (Role::nameExists($name)) {
            Session::flash('error', 'Nama role sudah digunakan.');
            Redirect::to('/roles/create');
        }

        if (!is_array($permissionIds)) {
            $permissionIds = [];
        }

        $roleId = Role::create([
            'name' => $name,
            'label' => $label,
            'description' => $description !== '' ? $description : null,
        ]);

        Role::syncPermissions($roleId, $permissionIds);

        AuditLog::record(
            'role_create',
            'Membuat role baru: ' . $name
        );

        Session::flash('success', 'Role berhasil ditambahkan.');
        Redirect::to('/roles');
    }

    public function edit(string $id): void
    {
        Auth::requirePermission('manage_roles');

        $role = Role::find((int) $id);

        if (!$role) {
            http_response_code(404);
            die('Role tidak ditemukan.');
        }

        $groupedPermissions = Permission::grouped();
        $rolePermissions = Role::findPermissions((int) $id);

        $this->view('roles/edit', [
            'title' => 'Edit Role',
            'role' => $role,
            'groupedPermissions' => $groupedPermissions,
            'rolePermissions' => $rolePermissions,
        ]);
    }

    public function update(string $id): void
    {
        Auth::requirePermission('manage_roles');
        Csrf::verify();

        $role = Role::find((int) $id);

        if (!$role) {
            http_response_code(404);
            die('Role tidak ditemukan.');
        }

        $name = $this->normalizeRoleName($_POST['name'] ?? '');
        $label = trim($_POST['label'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $permissionIds = $_POST['permission_ids'] ?? [];

        if ($label === '') {
            Session::flash('error', 'Label role wajib diisi.');
            Redirect::to('/roles/' . $id . '/edit');
        }

        if (!is_array($permissionIds)) {
            $permissionIds = [];
        }

        if ((int) $role['is_system'] === 1) {
            // Role system tidak boleh ganti name supaya kompatibilitas lama tetap aman.
            Role::updateSystemRoleLabel((int) $id, [
                'label' => $label,
                'description' => $description !== '' ? $description : null,
            ]);
        } else {
            if ($name === '') {
                Session::flash('error', 'Nama role wajib diisi.');
                Redirect::to('/roles/' . $id . '/edit');
            }

            if (!preg_match('/^[a-z0-9_]+$/', $name)) {
                Session::flash('error', 'Nama role hanya boleh huruf kecil, angka, dan underscore.');
                Redirect::to('/roles/' . $id . '/edit');
            }

            if (Role::nameExists($name, (int) $id)) {
                Session::flash('error', 'Nama role sudah digunakan.');
                Redirect::to('/roles/' . $id . '/edit');
            }

            Role::update((int) $id, [
                'name' => $name,
                'label' => $label,
                'description' => $description !== '' ? $description : null,
            ]);
        }

        // Jangan biarkan superadmin kehilangan permission.
        // Kalau role superadmin diedit, tetap kasih semua permission yang dicentang.
        // Dalam kondisi normal, centang semua untuk superadmin.
        Role::syncPermissions((int) $id, $permissionIds);

        AuditLog::record(
            'role_update',
            'Memperbarui role ID ' . $id . ': ' . $role['name']
        );

        Session::flash('success', 'Role berhasil diperbarui. User dengan role ini perlu login ulang agar permission session ikut refresh.');
        Redirect::to('/roles');
    }

    public function destroy(string $id): void
    {
        Auth::requirePermission('manage_roles');
        Csrf::verify();

        $role = Role::find((int) $id);

        if (!$role) {
            http_response_code(404);
            die('Role tidak ditemukan.');
        }

        if ((int) $role['is_system'] === 1) {
            Session::flash('error', 'Role system tidak boleh dihapus.');
            Redirect::to('/roles');
        }

        if (Role::countUsers((int) $id) > 0) {
            Session::flash('error', 'Role tidak boleh dihapus karena masih digunakan user.');
            Redirect::to('/roles');
        }

        Role::delete((int) $id);

        AuditLog::record(
            'role_delete',
            'Menghapus role ID ' . $id . ': ' . $role['name']
        );

        Session::flash('success', 'Role berhasil dihapus.');
        Redirect::to('/roles');
    }

    private function normalizeRoleName(string $name): string
    {
        $name = strtolower(trim($name));
        $name = preg_replace('/[^a-z0-9_]+/', '_', $name);
        $name = trim($name, '_');

        return $name;
    }
}