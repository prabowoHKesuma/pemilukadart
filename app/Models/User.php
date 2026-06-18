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
            SELECT
                u.*,
                r.name AS role_name,
                r.label AS role_label
            FROM users u
            LEFT JOIN roles r ON r.id = u.role_id
            WHERE u.username = ?
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

    public static function permissions(int $userId): array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            SELECT p.name
            FROM users u
            JOIN roles r ON r.id = u.role_id
            JOIN role_permissions rp ON rp.role_id = r.id
            JOIN permissions p ON p.id = rp.permission_id
            WHERE u.id = ?
            ORDER BY p.name ASC
        ");

        $stmt->execute([$userId]);

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}