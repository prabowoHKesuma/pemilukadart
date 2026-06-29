<?php

namespace App\Models;

use App\Core\Database;
use App\Core\RegionScope;
use PDO;

class Region
{
    public static function levels(): array
    {
        return [
            'kota' => 'Kota / Kabupaten',
            'kecamatan' => 'Kecamatan',
            'kelurahan' => 'Kelurahan / Desa',
            'rw' => 'RW',
            'rt' => 'RT',
            'custom' => 'Custom',
        ];
    }

    public static function all(): array
    {
        $pdo = Database::connection();

        $stmt = $pdo->query("
            SELECT
                r.*,
                o.name AS organization_name,
                p.name AS parent_name,
                p.code AS parent_code,

                (
                    SELECT COUNT(*)
                    FROM regions child
                    WHERE child.parent_id = r.id
                ) AS total_children,

                (
                    SELECT COUNT(*)
                    FROM users u
                    WHERE u.region_id = r.id
                ) AS total_users,

                (
                    SELECT COUNT(*)
                    FROM voters v
                    WHERE v.region_id = r.id
                ) AS total_voters,

                (
                    SELECT COUNT(*)
                    FROM elections e
                    WHERE e.region_id = r.id
                ) AS total_elections

            FROM regions r
            JOIN organizations o ON o.id = r.organization_id
            LEFT JOIN regions p ON p.id = r.parent_id

            ORDER BY o.name ASC, r.level ASC, r.name ASC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function options(?int $organizationId = null, ?int $excludeId = null): array
    {
        $pdo = Database::connection();

        $where = [];
        $params = [];

        if ($organizationId) {
            $where[] = "r.organization_id = ?";
            $params[] = $organizationId;
        }

        if ($excludeId) {
            $where[] = "r.id != ?";
            $params[] = $excludeId;
        }

        $whereSql = '';

        if (!empty($where)) {
            $whereSql = 'WHERE ' . implode(' AND ', $where);
        }

        $stmt = $pdo->prepare("
            SELECT
                r.id,
                r.organization_id,
                r.parent_id,
                r.level,
                r.code,
                r.name,
                o.name AS organization_name
            FROM regions r
            JOIN organizations o ON o.id = r.organization_id
            {$whereSql}
            ORDER BY o.name ASC, r.level ASC, r.name ASC
        ");

        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function find(int $id): ?array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            SELECT *
            FROM regions
            WHERE id = ?
            LIMIT 1
        ");

        $stmt->execute([$id]);

        $region = $stmt->fetch(PDO::FETCH_ASSOC);

        return $region ?: null;
    }

    public static function codeExists(int $organizationId, string $code, ?int $ignoreId = null): bool
    {
        $pdo = Database::connection();

        if ($ignoreId) {
            $stmt = $pdo->prepare("
                SELECT COUNT(*)
                FROM regions
                WHERE organization_id = ?
                  AND code = ?
                  AND id != ?
            ");

            $stmt->execute([$organizationId, $code, $ignoreId]);
        } else {
            $stmt = $pdo->prepare("
                SELECT COUNT(*)
                FROM regions
                WHERE organization_id = ?
                  AND code = ?
            ");

            $stmt->execute([$organizationId, $code]);
        }

        return (int) $stmt->fetchColumn() > 0;
    }

    public static function create(array $data): bool
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            INSERT INTO regions (
                organization_id,
                parent_id,
                level,
                code,
                name,
                created_at
            ) VALUES (
                :organization_id,
                :parent_id,
                :level,
                :code,
                :name,
                NOW()
            )
        ");

        return $stmt->execute([
            'organization_id' => $data['organization_id'],
            'parent_id' => $data['parent_id'],
            'level' => $data['level'],
            'code' => $data['code'],
            'name' => $data['name'],
        ]);
    }

    public static function update(int $id, array $data): bool
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            UPDATE regions
            SET
                organization_id = :organization_id,
                parent_id = :parent_id,
                level = :level,
                code = :code,
                name = :name,
                updated_at = NOW()
            WHERE id = :id
        ");

        return $stmt->execute([
            'id' => $id,
            'organization_id' => $data['organization_id'],
            'parent_id' => $data['parent_id'],
            'level' => $data['level'],
            'code' => $data['code'],
            'name' => $data['name'],
        ]);
    }

    public static function countChildren(int $id): int
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM regions
            WHERE parent_id = ?
        ");

        $stmt->execute([$id]);

        return (int) $stmt->fetchColumn();
    }

    public static function usageCount(int $id): array
    {
        $pdo = Database::connection();

        $users = $pdo->prepare("SELECT COUNT(*) FROM users WHERE region_id = ?");
        $users->execute([$id]);

        $voters = $pdo->prepare("SELECT COUNT(*) FROM voters WHERE region_id = ?");
        $voters->execute([$id]);

        $elections = $pdo->prepare("SELECT COUNT(*) FROM elections WHERE region_id = ?");
        $elections->execute([$id]);

        return [
            'users' => (int) $users->fetchColumn(),
            'voters' => (int) $voters->fetchColumn(),
            'elections' => (int) $elections->fetchColumn(),
        ];
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

    public static function delete(int $id): bool
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            DELETE FROM regions
            WHERE id = ?
        ");

        return $stmt->execute([$id]);
    }

    public static function descendantsIncludingSelf(int $regionId): array
    {
        $pdo = Database::connection();

        $ids = [$regionId];
        $queue = [$regionId];

        while (!empty($queue)) {
            $currentId = array_shift($queue);

            $stmt = $pdo->prepare("
                SELECT id
                FROM regions
                WHERE parent_id = ?
            ");

            $stmt->execute([$currentId]);

            $children = $stmt->fetchAll(PDO::FETCH_COLUMN);

            foreach ($children as $childId) {
                $childId = (int) $childId;

                if (!in_array($childId, $ids, true)) {
                    $ids[] = $childId;
                    $queue[] = $childId;
                }
            }
        }

        return $ids;
    }

    public static function optionsScoped(): array
    {
        $pdo = Database::connection();

        $whereSql = '';
        $params = [];

        if (!RegionScope::isUnrestricted()) {
            $allowedIds = RegionScope::allowedRegionIds();

            if (empty($allowedIds)) {
                return [];
            }

            $placeholders = implode(',', array_fill(0, count($allowedIds), '?'));
            $whereSql = "WHERE r.id IN ({$placeholders})";
            $params = $allowedIds;
        }

        $stmt = $pdo->prepare("
            SELECT
                r.id,
                r.organization_id,
                r.parent_id,
                r.code,
                r.name,
                r.level,
                o.name AS organization_name
            FROM regions r
            LEFT JOIN organizations o ON o.id = r.organization_id
            {$whereSql}
            ORDER BY
                o.name ASC,
                FIELD(r.level, 'kota', 'kecamatan', 'kelurahan', 'rw', 'rt', 'custom'),
                r.code ASC,
                r.name ASC
        ");

        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}