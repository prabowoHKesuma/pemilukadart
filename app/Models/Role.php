<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Role
{
    public static function all(): array
    {
        $pdo = Database::connection();

        $stmt = $pdo->query("
            SELECT
                r.*,
                COUNT(DISTINCT u.id) AS total_users,
                COUNT(DISTINCT rp.permission_id) AS total_permissions
            FROM roles r
            LEFT JOIN users u ON u.role_id = r.id
            LEFT JOIN role_permissions rp ON rp.role_id = r.id
            GROUP BY r.id
            ORDER BY r.is_system DESC, r.label ASC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function find(int $id): ?array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            SELECT *
            FROM roles
            WHERE id = ?
            LIMIT 1
        ");

        $stmt->execute([$id]);

        $role = $stmt->fetch(PDO::FETCH_ASSOC);

        return $role ?: null;
    }

    public static function findPermissions(int $roleId): array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            SELECT p.name
            FROM role_permissions rp
            JOIN permissions p ON p.id = rp.permission_id
            WHERE rp.role_id = ?
            ORDER BY p.name ASC
        ");

        $stmt->execute([$roleId]);

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public static function nameExists(string $name, ?int $ignoreId = null): bool
    {
        $pdo = Database::connection();

        if ($ignoreId) {
            $stmt = $pdo->prepare("
                SELECT COUNT(*)
                FROM roles
                WHERE name = ?
                  AND id != ?
            ");

            $stmt->execute([$name, $ignoreId]);
        } else {
            $stmt = $pdo->prepare("
                SELECT COUNT(*)
                FROM roles
                WHERE name = ?
            ");

            $stmt->execute([$name]);
        }

        return (int) $stmt->fetchColumn() > 0;
    }

    public static function create(array $data): int
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            INSERT INTO roles (
                name,
                label,
                description,
                is_system,
                created_at
            ) VALUES (
                :name,
                :label,
                :description,
                0,
                NOW()
            )
        ");

        $stmt->execute([
            'name' => $data['name'],
            'label' => $data['label'],
            'description' => $data['description'],
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            UPDATE roles
            SET
                name = :name,
                label = :label,
                description = :description,
                updated_at = NOW()
            WHERE id = :id
        ");

        return $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'label' => $data['label'],
            'description' => $data['description'],
        ]);
    }

    public static function updateSystemRoleLabel(int $id, array $data): bool
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            UPDATE roles
            SET
                label = :label,
                description = :description,
                updated_at = NOW()
            WHERE id = :id
              AND is_system = 1
        ");

        return $stmt->execute([
            'id' => $id,
            'label' => $data['label'],
            'description' => $data['description'],
        ]);
    }

    public static function syncPermissions(int $roleId, array $permissionIds): void
    {
        $pdo = Database::connection();

        $pdo->beginTransaction();

        try {
            $delete = $pdo->prepare("
                DELETE FROM role_permissions
                WHERE role_id = ?
            ");

            $delete->execute([$roleId]);

            if (!empty($permissionIds)) {
                $insert = $pdo->prepare("
                    INSERT INTO role_permissions (
                        role_id,
                        permission_id,
                        created_at
                    ) VALUES (
                        ?,
                        ?,
                        NOW()
                    )
                ");

                foreach ($permissionIds as $permissionId) {
                    $permissionId = (int) $permissionId;

                    if ($permissionId > 0) {
                        $insert->execute([$roleId, $permissionId]);
                    }
                }
            }

            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $e;
        }
    }

    public static function countUsers(int $roleId): int
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM users
            WHERE role_id = ?
        ");

        $stmt->execute([$roleId]);

        return (int) $stmt->fetchColumn();
    }

    public static function delete(int $id): bool
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            DELETE FROM roles
            WHERE id = ?
              AND is_system = 0
        ");

        return $stmt->execute([$id]);
    }

    public static function options(): array
    {
        $pdo = Database::connection();

        $stmt = $pdo->query("
            SELECT id, name, label
            FROM roles
            ORDER BY is_system DESC, label ASC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}