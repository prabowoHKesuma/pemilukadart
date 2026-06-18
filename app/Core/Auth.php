<?php

namespace App\Core;

class Auth
{
    public static function check(): bool
    {
        return Session::has('user');
    }

    public static function user(): ?array
    {
        return Session::get('user');
    }

    public static function id(): ?int
    {
        $user = self::user();

        return $user['id'] ?? null;
    }

    public static function role(): ?string
    {
        $user = self::user();

        return $user['role'] ?? null;
    }

    public static function login(array $user): void
    {
        session_regenerate_id(true);

        $roleName = $user['role_name'] ?? $user['role'] ?? null;
        $roleLabel = $user['role_label'] ?? $roleName;

        $permissions = [];

        if (!empty($user['id'])) {
            $permissions = \App\Models\User::permissions((int) $user['id']);
        }

        Session::set('user', [
            'id' => $user['id'],
            'name' => $user['name'],
            'username' => $user['username'],
            'role_id' => $user['role_id'] ?? null,
            'role' => $roleName,
            'role_label' => $roleLabel,
            'permissions' => $permissions,
        ]);
    }

    public static function logout(): void
    {
        Session::destroy();
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            Redirect::to('/login');
        }
    }

    public static function requireRole(array $roles): void
    {
        self::requireLogin();

        if (!in_array(self::role(), $roles, true)) {
            http_response_code(403);
            die('Akses ditolak.');
        }
    }

    public static function permissions(): array
    {
        $user = self::user();

        return $user['permissions'] ?? [];
    }

    public static function can(string $permission): bool
    {
        $user = self::user();

        if (!$user) {
            return false;
        }

        if (($user['role'] ?? null) === 'superadmin') {
            return true;
        }

        return in_array($permission, $user['permissions'] ?? [], true);
    }

    public static function requirePermission(string $permission): void
    {
        self::requireLogin();

        if (!self::can($permission)) {
            http_response_code(403);
            die('Akses ditolak. Permission dibutuhkan: ' . htmlspecialchars($permission, ENT_QUOTES, 'UTF-8'));
        }
    }
}