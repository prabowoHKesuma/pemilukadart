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

        Session::set('user', [
            'id' => $user['id'],
            'name' => $user['name'],
            'username' => $user['username'],
            'role' => $user['role'],
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
}