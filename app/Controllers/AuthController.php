<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Redirect;
use App\Core\Session;
use App\Models\User;
use App\Models\AuditLog;

class AuthController extends Controller
{
    public function showLogin(): void
    {
        if (Auth::check()) {
            Redirect::to('/');
        }

        $this->view('auth/login', [
            'title' => 'Login'
        ], 'auth');
    }

    public function login(): void
    {
        Csrf::verify();

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            Session::flash('error', 'Username dan password wajib diisi.');
            Redirect::to('/login');
        }

        $user = User::findByUsername($username);

        if (!$user || !password_verify($password, $user['password'])) {
            AuditLog::record(
                'login_failed',
                'Percobaan login gagal untuk username: ' . $username,
                $user['id'] ?? null
            );
            Session::flash('error', 'Username atau password salah.');
            Redirect::to('/login');
        }

        if ((int) $user['is_active'] !== 1) {
            Session::flash('error', 'Akun tidak aktif.');
            Redirect::to('/login');
        }

        Auth::login($user);
        User::updateLastLogin((int) $user['id']);

        AuditLog::record(
            'login_success',
            'User berhasil login: ' . $user['username'],
            (int) $user['id']
        );

        Redirect::to('/');
    }

    public function logout(): void
    {
        Csrf::verify();

        $user = Auth::user();

        AuditLog::record(
            'logout',
            'User logout: ' . ($user['username'] ?? '-'),
            $user['id'] ?? null
        );

        Auth::logout();

        Redirect::to('/login');
    }
}