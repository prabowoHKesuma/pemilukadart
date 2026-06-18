<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Organization
{
    public static function options(): array
    {
        $pdo = Database::connection();

        $stmt = $pdo->query("
            SELECT id, name, type
            FROM organizations
            WHERE is_active = 1
            ORDER BY name ASC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function find(int $id): ?array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            SELECT *
            FROM organizations
            WHERE id = ?
            LIMIT 1
        ");

        $stmt->execute([$id]);

        $organization = $stmt->fetch(PDO::FETCH_ASSOC);

        return $organization ?: null;
    }
}