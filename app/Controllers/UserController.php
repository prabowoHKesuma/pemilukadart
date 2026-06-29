<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Redirect;
use App\Core\Session;
use App\Core\RegionScope;
use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use App\Models\Organization;
use App\Models\Region;

class UserController extends Controller
{
    public function index(): void
    {
        Auth::requirePermission('manage_users');

        $users = User::allScoped();

        $this->view('users/index', [
            'title' => 'User Management',
            'users' => $users,
        ]);
    }

    private function isSuperadmin(): bool
    {
        return Auth::role() === 'superadmin';
    }

    private function userOr404(string|int $id): array
    {
        $user = User::findScoped((int) $id);

        if (!$user) {
            http_response_code(404);
            die('User tidak ditemukan atau berada di luar scope wilayah Anda.');
        }

        return $user;
    }

    private function regionOrReject(?int $regionId, string $redirectUrl): ?array
    {
        if (!$regionId) {
            if ($this->isSuperadmin()) {
                return null;
            }

            Session::flash('error', 'Wilayah user wajib dipilih.');
            Redirect::to($redirectUrl);
        }

        if (!RegionScope::canAccessRegion((int) $regionId)) {
            Session::flash('error', 'Wilayah yang dipilih berada di luar scope Anda.');
            Redirect::to($redirectUrl);
        }

        $region = Region::find((int) $regionId);

        if (!$region) {
            Session::flash('error', 'Wilayah tidak ditemukan.');
            Redirect::to($redirectUrl);
        }

        return $region;
    }

    private function normalizeUserPayload(string $redirectUrl): array
    {
        $name = trim($_POST['name'] ?? '');
        $username = strtolower(trim($_POST['username'] ?? ''));
        $roleId = (int) ($_POST['role_id'] ?? 0);
        $regionId = !empty($_POST['region_id']) ? (int) $_POST['region_id'] : null;
        $organizationId = !empty($_POST['organization_id']) ? (int) $_POST['organization_id'] : null;
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if ($name === '') {
            Session::flash('error', 'Nama user wajib diisi.');
            Redirect::to($redirectUrl);
        }

        if ($username === '') {
            Session::flash('error', 'Username wajib diisi.');
            Redirect::to($redirectUrl);
        }

        if ($roleId <= 0 || !Role::canAssignRoleId($roleId)) {
            Session::flash('error', 'Role tidak valid atau tidak boleh Anda assign.');
            Redirect::to($redirectUrl);
        }

        $region = $this->regionOrReject($regionId, $redirectUrl);

        if (!$this->isSuperadmin()) {
            $currentUser = Auth::user();

            $organizationId = !empty($currentUser['organization_id'])
                ? (int) $currentUser['organization_id']
                : ($region['organization_id'] ?? null);
        }

        if ($region && !empty($region['organization_id'])) {
            $organizationId = (int) $region['organization_id'];
        }

        if (!$organizationId) {
            Session::flash('error', 'Organization user tidak valid.');
            Redirect::to($redirectUrl);
        }

        return [
            'name' => $name,
            'username' => $username,
            'role_id' => $roleId,
            'organization_id' => $organizationId,
            'region_id' => $regionId,
            'is_active' => $isActive,
        ];
    }

    public function create(): void
    {
        Auth::requirePermission('manage_users');

        $roles = Role::options();
        $organizations = Organization::options();
        $regions = Region::options();

        $this->view('users/create', [
            'title' => 'Tambah User',
            'roles' => Role::manageableOptions(),
            'regions' => Region::optionsScoped(),
            'organizations' => $this->isSuperadmin() ? Organization::options() : [],
            'isSuperadmin' => $this->isSuperadmin(),
        ]);
    }

    public function store(): void
    {
        Auth::requirePermission('manage_users');
        Csrf::verify();

        $data = $this->normalizeUserPayload('/users/create');

        $name = trim($_POST['name'] ?? '');
        $username = strtolower(trim($_POST['username'] ?? ''));
        $password = trim($_POST['password'] ?? '');
        $passwordConfirmation = $_POST['password_confirmation'] ?? '';
        $roleId = (int) ($_POST['role_id'] ?? 0);
        $organizationId = !empty($_POST['organization_id']) ? (int) $_POST['organization_id'] : null;
        $regionId = !empty($_POST['region_id']) ? (int) $_POST['region_id'] : null;
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if ($password === '') {
            Session::flash('error', 'Password wajib diisi.');
            Redirect::to('/users/create');
        }

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

        if ($regionId) {
            $region = Region::find($regionId);

            if (!$region) {
                Session::flash('error', 'Wilayah tidak valid.');
                Redirect::to('/users/create');
            }

            if ($organizationId && (int) $region['organization_id'] !== (int) $organizationId) {
                Session::flash('error', 'Wilayah tidak sesuai dengan organization.');
                Redirect::to('/users/create');
            }

            if (!$organizationId) {
                $organizationId = (int) $region['organization_id'];
            }
        }

        if ($organizationId && !Organization::find($organizationId)) {
            Session::flash('error', 'Organization tidak valid.');
            Redirect::to('/users/create');
        }

        User::create([
            'name' => $name,
            'username' => $username,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role' => $role['name'],
            'role_id' => $roleId,
            'organization_id' => $organizationId,
            'region_id' => $regionId,
            'is_active' => $isActive,
        ]);

        AuditLog::record(
            'user_create',
            'Membuat user baru: ' . $username . ' dengan role ' . $role['name'],
            null,
            [
                'organization_id' => $data['organization_id'],
                'region_id' => $data['region_id'],
            ]
        );

        Session::flash('success', 'User berhasil ditambahkan.');
        Redirect::to('/users');
    }

    public function edit(string $id): void
    {
        Auth::requirePermission('manage_users');

        $user = $this->userOr404($id);

        if (($user['role_name'] ?? '') === 'superadmin' && !$this->isSuperadmin()) {
            http_response_code(403);
            die('Anda tidak boleh mengelola user superadmin.');
        }

        $this->view('users/edit', [
            'title' => 'Edit User',
            'user' => $user,
            'roles' => Role::manageableOptions(),
            'regions' => Region::optionsScoped(),
            'organizations' => $this->isSuperadmin() ? Organization::options() : [],
            'isSuperadmin' => $this->isSuperadmin(),
        ]);
    }

    public function update(string $id): void
    {
        Auth::requirePermission('manage_users');
        Csrf::verify();

        $user = $this->userOr404($id);

        if (($user['role_name'] ?? '') === 'superadmin' && !$this->isSuperadmin()) {
            http_response_code(403);
            die('Anda tidak boleh mengubah user superadmin.');
        }

        $data = $this->normalizeUserPayload('/users/' . $id . '/edit');

        if (User::usernameExists($data['username'], (int) $id)) {
            Session::flash('error', 'Username sudah digunakan.');
            Redirect::to('/users/' . $id . '/edit');
        }

        if ((int) $id === (int) Auth::id()) {
            unset($data['role_id']);
            unset($data['is_active']);
        }

        User::update((int) $id, $data);

        AuditLog::record(
            'user_update',
            'Memperbarui user ID ' . $id . ': ' . $data['username'],
            null,
            [
                'organization_id' => $data['organization_id'],
                'region_id' => $data['region_id'],
            ]
        );

        Session::flash('success', 'User berhasil diperbarui.');
        Redirect::to('/users');
    }

    public function destroy(string $id): void
    {
        Auth::requirePermission('manage_users');
        Csrf::verify();

        $user = $this->userOr404($id);

        if ((int) $id === (int) Auth::id()) {
            Session::flash('error', 'Anda tidak bisa menghapus akun sendiri.');
            Redirect::to('/users');
        }

        if (($user['role_name'] ?? '') === 'superadmin' && !$this->isSuperadmin()) {
            http_response_code(403);
            die('Anda tidak boleh menghapus user superadmin.');
        }

        User::delete((int) $id);

        AuditLog::record(
            'user_delete',
            'Menghapus user ID ' . $id . ': ' . ($user['username'] ?? '-'),
            null,
            [
                'organization_id' => $user['organization_id'] ?? null,
                'region_id' => $user['region_id'] ?? null,
            ]
        );

        Session::flash('success', 'User berhasil dihapus.');
        Redirect::to('/users');
    }
}