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

    public static function searchForVoting(int $electionId, string $keyword): array
    {
        $pdo = Database::connection();

        $like = '%' . $keyword . '%';

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
            AND (
                    v.voter_code LIKE ?
                OR v.name LIKE ?
                OR v.phone LIKE ?
            )
            ORDER BY v.name ASC
            LIMIT 30
        ");

        $stmt->execute([
            $electionId,
            $like,
            $like,
            $like,
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findDetailForVoting(int $electionId, int $electionVoterId): ?array
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
            AND ev.id = ?
            LIMIT 1
        ");

        $stmt->execute([$electionId, $electionVoterId]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public static function lockForVoting(\PDO $pdo, int $electionId, int $electionVoterId): ?array
    {
        $stmt = $pdo->prepare("
            SELECT 
                ev.*,
                v.is_active,
                v.name,
                v.voter_code
            FROM election_voters ev
            JOIN voters v ON v.id = ev.voter_id
            WHERE ev.election_id = ?
            AND ev.id = ?
            LIMIT 1
            FOR UPDATE
        ");

        $stmt->execute([$electionId, $electionVoterId]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public static function markVoted(\PDO $pdo, int $electionVoterId): bool
    {
        $stmt = $pdo->prepare("
            UPDATE election_voters
            SET has_voted = 1,
                voted_at = NOW()
            WHERE id = ?
        ");

        return $stmt->execute([$electionVoterId]);
    }
}