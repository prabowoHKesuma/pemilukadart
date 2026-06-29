<?php

namespace App\Models;

use App\Core\Database;
use App\Core\RegionScope;
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
                r.label AS role_label,
                o.name AS organization_name,
                rg.code AS region_code,
                rg.name AS region_name,
                rg.level AS region_level
            FROM users u
            LEFT JOIN roles r ON r.id = u.role_id
            LEFT JOIN organizations o ON o.id = u.organization_id
            LEFT JOIN regions rg ON rg.id = u.region_id
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

    public static function all(): array
    {
        $pdo = Database::connection();

        $stmt = $pdo->query("
            SELECT
                u.*,
                r.name AS role_name,
                r.label AS role_label,
                o.name AS organization_name,
                rg.code AS region_code,
                rg.name AS region_name,
                rg.level AS region_level
            FROM users u
            LEFT JOIN roles r ON r.id = u.role_id
            LEFT JOIN organizations o ON o.id = u.organization_id
            LEFT JOIN regions rg ON rg.id = u.region_id
            ORDER BY u.name ASC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function find(int $id): ?array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            SELECT
                u.*,
                r.name AS role_name,
                r.label AS role_label,
                o.name AS organization_name,
                rg.code AS region_code,
                rg.name AS region_name,
                rg.level AS region_level
            FROM users u
            LEFT JOIN roles r ON r.id = u.role_id
            LEFT JOIN organizations o ON o.id = u.organization_id
            LEFT JOIN regions rg ON rg.id = u.region_id
            WHERE u.id = ?
            LIMIT 1
        ");

        $stmt->execute([$id]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public static function usernameExists(string $username, ?int $ignoreId = null): bool
    {
        $pdo = Database::connection();

        if ($ignoreId) {
            $stmt = $pdo->prepare("
                SELECT COUNT(*)
                FROM users
                WHERE username = ?
                AND id != ?
            ");

            $stmt->execute([$username, $ignoreId]);
        } else {
            $stmt = $pdo->prepare("
                SELECT COUNT(*)
                FROM users
                WHERE username = ?
            ");

            $stmt->execute([$username]);
        }

        return (int) $stmt->fetchColumn() > 0;
    }

    public static function create(array $data): bool
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            INSERT INTO users (
                name,
                username,
                password,
                role,
                role_id,
                organization_id,
                region_id,
                is_active,
                created_at
            ) VALUES (
                :name,
                :username,
                :password,
                :role,
                :role_id,
                :organization_id,
                :region_id,
                :is_active,
                NOW()
            )
        ");

        return $stmt->execute([
            'name' => $data['name'],
            'username' => $data['username'],
            'password' => $data['password'],
            'role' => $data['role'],
            'role_id' => $data['role_id'],
            'organization_id' => $data['organization_id'],
            'region_id' => $data['region_id'],
            'is_active' => $data['is_active'],
        ]);
    }

    public static function update(int $id, array $data): bool
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            UPDATE users
            SET
                name = :name,
                username = :username,
                role = :role,
                role_id = :role_id,
                organization_id = :organization_id,
                region_id = :region_id,
                is_active = :is_active
            WHERE id = :id
        ");

        return $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'username' => $data['username'],
            'role' => $data['role'],
            'role_id' => $data['role_id'],
            'organization_id' => $data['organization_id'],
            'region_id' => $data['region_id'],
            'is_active' => $data['is_active'],
        ]);
    }

    public static function updatePassword(int $id, string $hashedPassword): bool
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            UPDATE users
            SET password = ?
            WHERE id = ?
        ");

        return $stmt->execute([$hashedPassword, $id]);
    }

    public static function delete(int $id): bool
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            DELETE FROM users
            WHERE id = ?
        ");

        return $stmt->execute([$id]);
    }

    public static function allScoped(): array
    {
        $pdo = Database::connection();

        [$whereSql, $params] = RegionScope::whereSqlForTarget('u', null);

        $stmt = $pdo->prepare("
            SELECT
                u.*,

                r.name AS role_name,
                r.label AS role_label,

                o.name AS organization_name,
                o.type AS organization_type,

                rg.code AS region_code,
                rg.name AS region_name,
                rg.level AS region_level

            FROM users u
            LEFT JOIN roles r ON r.id = u.role_id
            LEFT JOIN organizations o ON o.id = u.organization_id
            LEFT JOIN regions rg ON rg.id = u.region_id

            {$whereSql}

            ORDER BY
                u.created_at DESC,
                u.name ASC
        ");

        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findScoped(int $id): ?array
    {
        $pdo = Database::connection();

        [$regionSql, $regionParams] = RegionScope::andSqlForTarget('u', null);

        $stmt = $pdo->prepare("
            SELECT
                u.*,

                r.name AS role_name,
                r.label AS role_label,

                o.name AS organization_name,
                o.type AS organization_type,

                rg.code AS region_code,
                rg.name AS region_name,
                rg.level AS region_level

            FROM users u
            LEFT JOIN roles r ON r.id = u.role_id
            LEFT JOIN organizations o ON o.id = u.organization_id
            LEFT JOIN regions rg ON rg.id = u.region_id

            WHERE u.id = ?
            {$regionSql}

            LIMIT 1
        ");

        $stmt->execute(array_merge([$id], $regionParams));

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public static function findRaw(int $id): ?array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            SELECT
                u.*,
                r.name AS role_name,
                r.label AS role_label
            FROM users u
            LEFT JOIN roles r ON r.id = u.role_id
            WHERE u.id = ?
            LIMIT 1
        ");

        $stmt->execute([$id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }
}