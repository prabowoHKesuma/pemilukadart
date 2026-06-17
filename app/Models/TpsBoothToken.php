<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class TpsBoothToken
{
    public static function hashCode(string $code): string
    {
        $appKey = $_ENV['APP_KEY'] ?? getenv('APP_KEY') ?: 'fallback_key_change_this';

        return hash_hmac('sha256', $code, $appKey);
    }

    public static function hashExists(string $tokenHash): bool
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM tps_booth_tokens
            WHERE token_hash = ?
        ");

        $stmt->execute([$tokenHash]);

        return (int) $stmt->fetchColumn() > 0;
    }

    public static function create(array $data): bool
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            INSERT INTO tps_booth_tokens (
                election_id,
                election_voter_id,
                token_hash,
                expires_at,
                created_by,
                created_at
            ) VALUES (
                :election_id,
                :election_voter_id,
                :token_hash,
                :expires_at,
                :created_by,
                NOW()
            )
        ");

        return $stmt->execute([
            'election_id' => $data['election_id'],
            'election_voter_id' => $data['election_voter_id'],
            'token_hash' => $data['token_hash'],
            'expires_at' => $data['expires_at'],
            'created_by' => $data['created_by'],
        ]);
    }

    public static function activeByElectionVoter(int $electionVoterId): ?array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            SELECT *
            FROM tps_booth_tokens
            WHERE election_voter_id = ?
              AND used_at IS NULL
              AND revoked_at IS NULL
              AND expires_at > NOW()
            ORDER BY created_at DESC
            LIMIT 1
        ");

        $stmt->execute([$electionVoterId]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public static function find(int $id): ?array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            SELECT *
            FROM tps_booth_tokens
            WHERE id = ?
            LIMIT 1
        ");

        $stmt->execute([$id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public static function revoke(int $id): bool
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            UPDATE tps_booth_tokens
            SET revoked_at = NOW()
            WHERE id = ?
              AND used_at IS NULL
              AND revoked_at IS NULL
        ");

        return $stmt->execute([$id]);
    }

    public static function findByHash(string $tokenHash): ?array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            SELECT
                tbt.*,
                e.title AS election_title,
                e.status AS election_status,
                ev.voter_id,
                ev.allowed_channel,
                ev.has_voted,
                v.voter_code,
                v.name AS voter_name,
                v.rt,
                v.rw,
                v.is_active
            FROM tps_booth_tokens tbt
            JOIN elections e ON e.id = tbt.election_id
            JOIN election_voters ev ON ev.id = tbt.election_voter_id
            JOIN voters v ON v.id = ev.voter_id
            WHERE tbt.token_hash = ?
            LIMIT 1
        ");

        $stmt->execute([$tokenHash]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public static function lockByHash(PDO $pdo, string $tokenHash): ?array
    {
        $stmt = $pdo->prepare("
            SELECT
                tbt.*,
                ev.voter_id,
                ev.allowed_channel,
                ev.has_voted,
                v.is_active,
                v.voter_code,
                v.name AS voter_name
            FROM tps_booth_tokens tbt
            JOIN election_voters ev ON ev.id = tbt.election_voter_id
            JOIN voters v ON v.id = ev.voter_id
            WHERE tbt.token_hash = ?
            LIMIT 1
            FOR UPDATE
        ");

        $stmt->execute([$tokenHash]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public static function markUsed(PDO $pdo, int $id): bool
    {
        $stmt = $pdo->prepare("
            UPDATE tps_booth_tokens
            SET used_at = NOW()
            WHERE id = ?
        ");

        return $stmt->execute([$id]);
    }
}