<?php

namespace App\Core;

class Csrf
{
    public static function token(): string
    {
        if (!Session::has('_csrf_token')) {
            Session::set('_csrf_token', bin2hex(random_bytes(32)));
        }

        return Session::get('_csrf_token');
    }

    public static function field(): string
    {
        $token = self::token();

        return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    public static function verify(): void
    {
        $sessionToken = Session::get('_csrf_token');
        $postedToken = $_POST['_csrf_token'] ?? '';

        if (!$sessionToken || !$postedToken || !hash_equals($sessionToken, $postedToken)) {
            http_response_code(419);
            die('CSRF token tidak valid.');
        }
    }
}