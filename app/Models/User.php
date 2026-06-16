<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class User
{
    public static function findByUsername(string $username): ?array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            SELECT *
            FROM users
            WHERE username = ?
            LIMIT 1
        ");

        $stmt->execute([$username]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public static function updateLastLogin(int $id): void
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            UPDATE users
            SET last_login_at = NOW()
            WHERE id = ?
        ");

        $stmt->execute([$id]);
    }
}