<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Redirect;
use App\Core\Session;
use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;

class UserController extends Controller
{
    public function index(): void
    {
        Auth::requirePermission('manage_users');

        $users = User::all();

        $this->view('users/index', [
            'title' => 'User Management',
            'users' => $users,
        ]);
    }

    public function create(): void
    {
        Auth::requirePermission('manage_users');

        $roles = Role::options();

        $this->view('users/create', [
            'title' => 'Tambah User',
            'roles' => $roles,
        ]);
    }

    public function store(): void
    {
        Auth::requirePermission('manage_users');
        Csrf::verify();

        $name = trim($_POST['name'] ?? '');
        $username = strtolower(trim($_POST['username'] ?? ''));
        $password = $_POST['password'] ?? '';
        $passwordConfirmation = $_POST['password_confirmation'] ?? '';
        $roleId = (int) ($_POST['role_id'] ?? 0);
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if ($name === '') {
            Session::flash('error', 'Nama user wajib diisi.');
            Redirect::to('/users/create');
        }

        if ($username === '') {
            Session::flash('error', 'Username wajib diisi.');
            Redirect::to('/users/create');
        }

        if (!preg_match('/^[a-z0-9_.-]+$/', $username)) {
            Session::flash('error', 'Username hanya boleh huruf kecil, angka, titik, underscore, dan strip.');
            Redirect::to('/users/create');
        }

        if (User::usernameExists($username)) {
            Session::flash('error', 'Username sudah digunakan.');
            Redirect::to('/users/create');
        }

        if (strlen($password) < 8) {
            Session::flash('error', 'Password minimal 8 karakter.');
            Redirect::to('/users/create');
        }

        if ($password !== $passwordConfirmation) {
            Session::flash('error', 'Konfirmasi password tidak sama.');
            Redirect::to('/users/create');
        }

        $role = Role::find($roleId);

        if (!$role) {
            Session::flash('error', 'Role tidak valid.');
            Redirect::to('/users/create');
        }

        User::create([
            'name' => $name,
            'username' => $username,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role' => $role['name'],
            'role_id' => $roleId,
            'is_active' => $isActive,
        ]);

        AuditLog::record(
            'user_create',
            'Membuat user baru: ' . $username . ' dengan role ' . $role['name']
        );

        Session::flash('success', 'User berhasil ditambahkan.');
        Redirect::to('/users');
    }

    public function edit(string $id): void
    {
        Auth::requirePermission('manage_users');

        $user = User::find((int) $id);

        if (!$user) {
            http_response_code(404);
            die('User tidak ditemukan.');
        }

        $roles = Role::options();

        $this->view('users/edit', [
            'title' => 'Edit User',
            'userData' => $user,
            'roles' => $roles,
        ]);
    }

    public function update(string $id): void
    {
        Auth::requirePermission('manage_users');
        Csrf::verify();

        $user = User::find((int) $id);

        if (!$user) {
            http_response_code(404);
            die('User tidak ditemukan.');
        }

        $name = trim($_POST['name'] ?? '');
        $username = strtolower(trim($_POST['username'] ?? ''));
        $roleId = (int) ($_POST['role_id'] ?? 0);
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if ($name === '') {
            Session::flash('error', 'Nama user wajib diisi.');
            Redirect::to('/users/' . $id . '/edit');
        }

        if ($username === '') {
            Session::flash('error', 'Username wajib diisi.');
            Redirect::to('/users/' . $id . '/edit');
        }

        if (!preg_match('/^[a-z0-9_.-]+$/', $username)) {
            Session::flash('error', 'Username hanya boleh huruf kecil, angka, titik, underscore, dan strip.');
            Redirect::to('/users/' . $id . '/edit');
        }

        if (User::usernameExists($username, (int) $id)) {
            Session::flash('error', 'Username sudah digunakan.');
            Redirect::to('/users/' . $id . '/edit');
        }

        if ((int) $id === (int) Auth::id()) {
            if ($isActive !== 1) {
                Session::flash('error', 'Tidak boleh menonaktifkan akun yang sedang dipakai.');
                Redirect::to('/users/' . $id . '/edit');
            }

            if ($roleId !== (int) $user['role_id']) {
                Session::flash('error', 'Tidak boleh mengubah role akun yang sedang dipakai.');
                Redirect::to('/users/' . $id . '/edit');
            }
        }

        $role = Role::find($roleId);

        if (!$role) {
            Session::flash('error', 'Role tidak valid.');
            Redirect::to('/users/' . $id . '/edit');
        }

        User::update((int) $id, [
            'name' => $name,
            'username' => $username,
            'role' => $role['name'],
            'role_id' => $roleId,
            'is_active' => $isActive,
        ]);

        $newPassword = $_POST['new_password'] ?? '';
        $newPasswordConfirmation = $_POST['new_password_confirmation'] ?? '';

        if ($newPassword !== '') {
            if (strlen($newPassword) < 8) {
                Session::flash('error', 'Password baru minimal 8 karakter.');
                Redirect::to('/users/' . $id . '/edit');
            }

            if ($newPassword !== $newPasswordConfirmation) {
                Session::flash('error', 'Konfirmasi password baru tidak sama.');
                Redirect::to('/users/' . $id . '/edit');
            }

            User::updatePassword((int) $id, password_hash($newPassword, PASSWORD_DEFAULT));

            AuditLog::record(
                'user_password_reset',
                'Reset password user ID ' . $id . ': ' . $username
            );
        }

        AuditLog::record(
            'user_update',
            'Memperbarui user ID ' . $id . ': ' . $username . ' dengan role ' . $role['name']
        );

        Session::flash('success', 'User berhasil diperbarui. Jika role/permission berubah, user tersebut harus login ulang.');
        Redirect::to('/users');
    }

    public function destroy(string $id): void
    {
        Auth::requirePermission('manage_users');
        Csrf::verify();

        $user = User::find((int) $id);

        if (!$user) {
            http_response_code(404);
            die('User tidak ditemukan.');
        }

        if ((int) $id === (int) Auth::id()) {
            Session::flash('error', 'Tidak boleh menghapus akun yang sedang dipakai.');
            Redirect::to('/users');
        }

        User::delete((int) $id);

        AuditLog::record(
            'user_delete',
            'Menghapus user ID ' . $id . ': ' . $user['username']
        );

        Session::flash('success', 'User berhasil dihapus.');
        Redirect::to('/users');
    }
}