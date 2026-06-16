<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class ElectionVoter
{
    public static function allByElection(int $electionId): array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            SELECT 
                ev.*,
                v.voter_code,
                v.name,
                v.address,
                v.phone,
                v.rt,
                v.rw,
                v.is_active
            FROM election_voters ev
            JOIN voters v ON v.id = ev.voter_id
            WHERE ev.election_id = ?
            ORDER BY v.name ASC
        ");

        $stmt->execute([$electionId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function availableVoters(int $electionId): array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            SELECT *
            FROM voters
            WHERE is_active = 1
              AND id NOT IN (
                  SELECT voter_id
                  FROM election_voters
                  WHERE election_id = ?
              )
            ORDER BY name ASC
        ");

        $stmt->execute([$electionId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findByElection(int $electionId, int $id): ?array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            SELECT *
            FROM election_voters
            WHERE election_id = ?
              AND id = ?
            LIMIT 1
        ");

        $stmt->execute([$electionId, $id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public static function exists(int $electionId, int $voterId): bool
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM election_voters
            WHERE election_id = ?
              AND voter_id = ?
        ");

        $stmt->execute([$electionId, $voterId]);

        return (int) $stmt->fetchColumn() > 0;
    }

    public static function create(int $electionId, int $voterId, string $allowedChannel): bool
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            INSERT INTO election_voters (
                election_id,
                voter_id,
                allowed_channel,
                has_voted,
                created_at
            ) VALUES (
                :election_id,
                :voter_id,
                :allowed_channel,
                0,
                NOW()
            )
        ");

        return $stmt->execute([
            'election_id' => $electionId,
            'voter_id' => $voterId,
            'allowed_channel' => $allowedChannel,
        ]);
    }

    public static function updateChannel(int $id, string $allowedChannel): bool
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            UPDATE election_voters
            SET allowed_channel = ?
            WHERE id = ?
        ");

        return $stmt->execute([$allowedChannel, $id]);
    }

    public static function delete(int $id): bool
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            DELETE FROM election_voters
            WHERE id = ?
        ");

        return $stmt->execute([$id]);
    }

    public static function countByElection(int $electionId): int
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM election_voters
            WHERE election_id = ?
        ");

        $stmt->execute([$electionId]);

        return (int) $stmt->fetchColumn();
    }

    public static function countVotedByElection(int $electionId): int
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM election_voters
            WHERE election_id = ?
              AND has_voted = 1
        ");

        $stmt->execute([$electionId]);

        return (int) $stmt->fetchColumn();
    }
}