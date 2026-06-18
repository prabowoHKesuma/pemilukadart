<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Permission
{
    public static function all(): array
    {
        $pdo = Database::connection();

        $stmt = $pdo->query("
            SELECT *
            FROM permissions
            ORDER BY group_name ASC, label ASC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function grouped(): array
    {
        $permissions = self::all();

        $grouped = [];

        foreach ($permissions as $permission) {
            $groupName = $permission['group_name'] ?: 'Lainnya';

            if (!isset($grouped[$groupName])) {
                $grouped[$groupName] = [];
            }

            $grouped[$groupName][] = $permission;
        }

        return $grouped;
    }
}