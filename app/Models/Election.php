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
                u.name AS created_by_name
            FROM elections e
            LEFT JOIN users u ON u.id = e.created_by
            ORDER BY e.created_at DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function find(int $id): ?array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            SELECT *
            FROM elections
            WHERE id = ?
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
                description,
                status,
                start_at,
                end_at,
                created_by,
                created_at
            ) VALUES (
                :title,
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
}