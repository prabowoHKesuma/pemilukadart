<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Election
{
    public static function all(): array
    {
        $pdo = Database::connection();

        $stmt = $pdo->query("
            SELECT 
                e.*,
                o.name AS organization_name,
                rg.code AS region_code,
                rg.name AS region_name,
                rg.level AS region_level
            FROM elections e
            LEFT JOIN organizations o ON o.id = e.organization_id
            LEFT JOIN regions rg ON rg.id = e.region_id
            ORDER BY e.created_at DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function find(int $id): ?array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            SELECT
                e.*,
                o.name AS organization_name,
                rg.code AS region_code,
                rg.name AS region_name,
                rg.level AS region_level
            FROM elections e
            LEFT JOIN organizations o ON o.id = e.organization_id
            LEFT JOIN regions rg ON rg.id = e.region_id
            WHERE e.id = ?
            LIMIT 1
        ");

        $stmt->execute([$id]);

        $election = $stmt->fetch(PDO::FETCH_ASSOC);

        return $election ?: null;
    }

    public static function create(array $data): bool
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            INSERT INTO elections (
                title,
                organization_id,
                region_id,
                description,
                status,
                start_at,
                end_at,
                created_by,
                created_at
            ) VALUES (
                :title,
                :organization_id,
                :region_id,
                :description,
                :status,
                :start_at,
                :end_at,
                :created_by,
                NOW()
            )
        ");

        return $stmt->execute([
            'title' => $data['title'],
            'organization_id' => $data['organization_id'] ?? null,
            'region_id' => $data['region_id'] ?? null,
            'description' => $data['description'],
            'status' => $data['status'],
            'start_at' => $data['start_at'],
            'end_at' => $data['end_at'],
            'created_by' => $data['created_by'],
        ]);
    }

    public static function update(int $id, array $data): bool
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            UPDATE elections
            SET
                title = :title,
                description = :description,
                status = :status,
                organization_id = :organization_id,
                region_id = :region_id,
                start_at = :start_at,
                end_at = :end_at,
                updated_at = NOW()
            WHERE id = :id
        ");

        return $stmt->execute([
            'id' => $id,
            'title' => $data['title'],
            'description' => $data['description'],
            'status' => $data['status'],
            'organization_id' => $data['organization_id'] ?? null,
            'region_id' => $data['region_id'] ?? null,
            'start_at' => $data['start_at'],
            'end_at' => $data['end_at'],
        ]);
    }

    public static function delete(int $id): bool
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            DELETE FROM elections
            WHERE id = ?
        ");

        return $stmt->execute([$id]);
    }

    public static function updateStatus(int $id, string $status): bool
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            UPDATE elections
            SET status = ?, updated_at = NOW()
            WHERE id = ?
        ");

        return $stmt->execute([$status, $id]);
    }


    public static function openElections(): array
    {
        $pdo = Database::connection();

        $stmt = $pdo->query("
            SELECT 
                e.*,
                COUNT(DISTINCT c.id) AS total_candidates,
                COUNT(DISTINCT ev.id) AS total_voters,
                SUM(CASE WHEN ev.has_voted = 1 THEN 1 ELSE 0 END) AS total_voted
            FROM elections e
            LEFT JOIN candidates c 
                ON c.election_id = e.id 
            AND c.is_active = 1
            LEFT JOIN election_voters ev 
                ON ev.election_id = e.id
            WHERE e.status = 'open'
            GROUP BY e.id
            ORDER BY e.start_at ASC, e.created_at DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function lockById(\PDO $pdo, int $id): ?array
    {
        $stmt = $pdo->prepare("
            SELECT *
            FROM elections
            WHERE id = ?
            LIMIT 1
            FOR UPDATE
        ");

        $stmt->execute([$id]);

        $election = $stmt->fetch(PDO::FETCH_ASSOC);

        return $election ?: null;
    }
}