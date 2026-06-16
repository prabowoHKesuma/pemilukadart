<?php

namespace App\Core;

class Redirect
{
    public static function to(string $path): void
    {
        $baseUrl = rtrim(Env::get('APP_URL'), '/');
        $path = '/' . ltrim($path, '/');

        header("Location: {$baseUrl}{$path}");
        exit;
    }

    public static function back(): void
    {
        $fallback = Env::get('APP_URL');

        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? $fallback));
        exit;
    }
}