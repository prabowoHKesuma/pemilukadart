<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Candidate
{
    public static function allByElection(int $electionId): array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            SELECT *
            FROM candidates
            WHERE election_id = ?
            ORDER BY number_order ASC, name ASC
        ");

        $stmt->execute([$electionId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findByElection(int $electionId, int $id): ?array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            SELECT *
            FROM candidates
            WHERE election_id = ?
              AND id = ?
            LIMIT 1
        ");

        $stmt->execute([$electionId, $id]);

        $candidate = $stmt->fetch(PDO::FETCH_ASSOC);

        return $candidate ?: null;
    }

    public static function numberExists(int $electionId, int $numberOrder, ?int $ignoreId = null): bool
    {
        $pdo = Database::connection();

        if ($ignoreId) {
            $stmt = $pdo->prepare("
                SELECT COUNT(*)
                FROM candidates
                WHERE election_id = ?
                  AND number_order = ?
                  AND id != ?
            ");

            $stmt->execute([$electionId, $numberOrder, $ignoreId]);
        } else {
            $stmt = $pdo->prepare("
                SELECT COUNT(*)
                FROM candidates
                WHERE election_id = ?
                  AND number_order = ?
            ");

            $stmt->execute([$electionId, $numberOrder]);
        }

        return (int) $stmt->fetchColumn() > 0;
    }

    public static function create(array $data): bool
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            INSERT INTO candidates (
                election_id,
                number_order,
                name,
                photo,
                vision,
                mission,
                is_active,
                created_at
            ) VALUES (
                :election_id,
                :number_order,
                :name,
                :photo,
                :vision,
                :mission,
                :is_active,
                NOW()
            )
        ");

        return $stmt->execute([
            'election_id' => $data['election_id'],
            'number_order' => $data['number_order'],
            'name' => $data['name'],
            'photo' => $data['photo'],
            'vision' => $data['vision'],
            'mission' => $data['mission'],
            'is_active' => $data['is_active'],
        ]);
    }

    public static function update(int $id, array $data): bool
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            UPDATE candidates
            SET
                number_order = :number_order,
                name = :name,
                photo = :photo,
                vision = :vision,
                mission = :mission,
                is_active = :is_active,
                updated_at = NOW()
            WHERE id = :id
        ");

        return $stmt->execute([
            'id' => $id,
            'number_order' => $data['number_order'],
            'name' => $data['name'],
            'photo' => $data['photo'],
            'vision' => $data['vision'],
            'mission' => $data['mission'],
            'is_active' => $data['is_active'],
        ]);
    }

    public static function delete(int $id): bool
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            DELETE FROM candidates
            WHERE id = ?
        ");

        return $stmt->execute([$id]);
    }
}