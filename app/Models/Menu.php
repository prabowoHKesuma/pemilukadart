<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Menu
{
    public static function all(): array
    {
        $pdo = Database::connection();

        $stmt = $pdo->query("
            SELECT
                m.*,
                p.title AS parent_title,
                p.menu_key AS parent_key,
                perm.label AS permission_label,

                (
                    SELECT COUNT(*)
                    FROM menus child
                    WHERE child.parent_id = m.id
                ) AS total_children,

                (
                    SELECT COUNT(*)
                    FROM role_menus rm
                    WHERE rm.menu_id = m.id
                ) AS total_roles

            FROM menus m
            LEFT JOIN menus p ON p.id = m.parent_id
            LEFT JOIN permissions perm ON perm.name = m.permission_name

            ORDER BY 
                COALESCE(m.parent_id, m.id) ASC,
                m.parent_id IS NOT NULL ASC,
                m.sort_order ASC,
                m.title ASC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function find(int $id): ?array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            SELECT *
            FROM menus
            WHERE id = ?
            LIMIT 1
        ");

        $stmt->execute([$id]);

        $menu = $stmt->fetch(PDO::FETCH_ASSOC);

        return $menu ?: null;
    }

    public static function parentOptions(?int $excludeId = null): array
    {
        $pdo = Database::connection();

        if ($excludeId) {
            $stmt = $pdo->prepare("
                SELECT id, parent_id, menu_key, title
                FROM menus
                WHERE id != ?
                ORDER BY sort_order ASC, title ASC
            ");

            $stmt->execute([$excludeId]);
        } else {
            $stmt = $pdo->query("
                SELECT id, parent_id, menu_key, title
                FROM menus
                ORDER BY sort_order ASC, title ASC
            ");
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function roleIds(int $menuId): array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            SELECT role_id
            FROM role_menus
            WHERE menu_id = ?
        ");

        $stmt->execute([$menuId]);

        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    public static function keyExists(string $menuKey, ?int $ignoreId = null): bool
    {
        $pdo = Database::connection();

        if ($ignoreId) {
            $stmt = $pdo->prepare("
                SELECT COUNT(*)
                FROM menus
                WHERE menu_key = ?
                  AND id != ?
            ");

            $stmt->execute([$menuKey, $ignoreId]);
        } else {
            $stmt = $pdo->prepare("
                SELECT COUNT(*)
                FROM menus
                WHERE menu_key = ?
            ");

            $stmt->execute([$menuKey]);
        }

        return (int) $stmt->fetchColumn() > 0;
    }

    public static function create(array $data): int
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            INSERT INTO menus (
                parent_id,
                menu_key,
                title,
                url,
                icon_class,
                permission_name,
                target,
                sort_order,
                is_active,
                created_at
            ) VALUES (
                :parent_id,
                :menu_key,
                :title,
                :url,
                :icon_class,
                :permission_name,
                :target,
                :sort_order,
                :is_active,
                NOW()
            )
        ");

        $stmt->execute([
            'parent_id' => $data['parent_id'],
            'menu_key' => $data['menu_key'],
            'title' => $data['title'],
            'url' => $data['url'],
            'icon_class' => $data['icon_class'],
            'permission_name' => $data['permission_name'],
            'target' => $data['target'],
            'sort_order' => $data['sort_order'],
            'is_active' => $data['is_active'],
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            UPDATE menus
            SET
                parent_id = :parent_id,
                menu_key = :menu_key,
                title = :title,
                url = :url,
                icon_class = :icon_class,
                permission_name = :permission_name,
                target = :target,
                sort_order = :sort_order,
                is_active = :is_active,
                updated_at = NOW()
            WHERE id = :id
        ");

        return $stmt->execute([
            'id' => $id,
            'parent_id' => $data['parent_id'],
            'menu_key' => $data['menu_key'],
            'title' => $data['title'],
            'url' => $data['url'],
            'icon_class' => $data['icon_class'],
            'permission_name' => $data['permission_name'],
            'target' => $data['target'],
            'sort_order' => $data['sort_order'],
            'is_active' => $data['is_active'],
        ]);
    }

    public static function syncRoles(int $menuId, array $roleIds): void
    {
        $pdo = Database::connection();

        $pdo->beginTransaction();

        try {
            $delete = $pdo->prepare("
                DELETE FROM role_menus
                WHERE menu_id = ?
            ");

            $delete->execute([$menuId]);

            if (!empty($roleIds)) {
                $insert = $pdo->prepare("
                    INSERT INTO role_menus (
                        role_id,
                        menu_id,
                        created_at
                    ) VALUES (
                        ?,
                        ?,
                        NOW()
                    )
                ");

                foreach ($roleIds as $roleId) {
                    $roleId = (int) $roleId;

                    if ($roleId > 0) {
                        $insert->execute([$roleId, $menuId]);
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

    public static function countChildren(int $id): int
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM menus
            WHERE parent_id = ?
        ");

        $stmt->execute([$id]);

        return (int) $stmt->fetchColumn();
    }

    public static function delete(int $id): bool
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            DELETE FROM menus
            WHERE id = ?
        ");

        return $stmt->execute([$id]);
    }

    public static function isDescendantOf(int $childId, int $parentId): bool
    {
        $current = self::find($childId);

        while ($current && !empty($current['parent_id'])) {
            if ((int) $current['parent_id'] === $parentId) {
                return true;
            }

            $current = self::find((int) $current['parent_id']);
        }

        return false;
    }
}