<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Voter
{
    public static function all(): array
    {
        $pdo = Database::connection();

        $stmt = $pdo->query("
            SELECT
                v.*,
                o.name AS organization_name,
                rg.code AS region_code,
                rg.name AS region_name,
                rg.level AS region_level
            FROM voters v
            LEFT JOIN organizations o ON o.id = v.organization_id
            LEFT JOIN regions rg ON rg.id = v.region_id
            ORDER BY v.name ASC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function find(int $id): ?array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            SELECT
                v.*,
                o.name AS organization_name,
                rg.code AS region_code,
                rg.name AS region_name,
                rg.level AS region_level
            FROM voters v
            LEFT JOIN organizations o ON o.id = v.organization_id
            LEFT JOIN regions rg ON rg.id = v.region_id
            WHERE v.id = ?
            LIMIT 1
        ");

        $stmt->execute([$id]);

        $voter = $stmt->fetch(PDO::FETCH_ASSOC);

        return $voter ?: null;
    }

    public static function voterCodeExists(string $voterCode, ?int $ignoreId = null): bool
    {
        $pdo = Database::connection();

        if ($ignoreId) {
            $stmt = $pdo->prepare("
                SELECT COUNT(*)
                FROM voters
                WHERE voter_code = ?
                  AND id != ?
            ");

            $stmt->execute([$voterCode, $ignoreId]);
        } else {
            $stmt = $pdo->prepare("
                SELECT COUNT(*)
                FROM voters
                WHERE voter_code = ?
            ");

            $stmt->execute([$voterCode]);
        }

        return (int) $stmt->fetchColumn() > 0;
    }

    public static function nikHashExists(string $nikHash, ?int $ignoreId = null): bool
    {
        $pdo = Database::connection();

        if ($ignoreId) {
            $stmt = $pdo->prepare("
                SELECT COUNT(*)
                FROM voters
                WHERE nik_hash = ?
                  AND id != ?
            ");

            $stmt->execute([$nikHash, $ignoreId]);
        } else {
            $stmt = $pdo->prepare("
                SELECT COUNT(*)
                FROM voters
                WHERE nik_hash = ?
            ");

            $stmt->execute([$nikHash]);
        }

        return (int) $stmt->fetchColumn() > 0;
    }

    public static function create(array $data): bool
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            INSERT INTO voters (
                voter_code,
                organization_id,
                region_id,
                name,
                nik_hash,
                kk_hash,
                address,
                phone,
                rt,
                rw,
                is_active,
                created_at
            ) VALUES (
                :voter_code,
                :organization_id,
                :region_id,
                :name,
                :nik_hash,
                :kk_hash,
                :address,
                :phone,
                :rt,
                :rw,
                :is_active,
                NOW()
            )
        ");

        return $stmt->execute([
            'voter_code' => $data['voter_code'],
            'organization_id' => $data['organization_id'],
            'region_id' => $data['region_id'],
            'name' => $data['name'],
            'nik_hash' => $data['nik_hash'],
            'kk_hash' => $data['kk_hash'],
            'address' => $data['address'],
            'phone' => $data['phone'],
            'rt' => $data['rt'],
            'rw' => $data['rw'],
            'is_active' => $data['is_active'],
        ]);
    }

    public static function update(int $id, array $data): bool
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            UPDATE voters
            SET
                voter_code = :voter_code,
                organization_id = :organization_id,
                region_id = :region_id,
                name = :name,
                nik_hash = :nik_hash,
                kk_hash = :kk_hash,
                address = :address,
                phone = :phone,
                rt = :rt,
                rw = :rw,
                is_active = :is_active,
                updated_at = NOW()
            WHERE id = :id
        ");

        return $stmt->execute([
            'id' => $id,
            'voter_code' => $data['voter_code'],
            'organization_id' => $data['organization_id'],
            'region_id' => $data['region_id'],
            'name' => $data['name'],
            'nik_hash' => $data['nik_hash'],
            'kk_hash' => $data['kk_hash'],
            'address' => $data['address'],
            'phone' => $data['phone'],
            'rt' => $data['rt'],
            'rw' => $data['rw'],
            'is_active' => $data['is_active'],
        ]);
    }

    public static function delete(int $id): bool
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            DELETE FROM voters
            WHERE id = ?
        ");

        return $stmt->execute([$id]);
    }

    public static function hasElectionRelation(int $id): bool
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM election_voters
            WHERE voter_id = ?
        ");

        $stmt->execute([$id]);

        return (int) $stmt->fetchColumn() > 0;
    }
}