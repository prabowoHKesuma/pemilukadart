<?php

namespace App\Models;

use App\Core\Database;
use App\Core\RegionScope;
use PDO;

class VotingToken
{
    public static function electionsWithStats(): array
    {
        $pdo = Database::connection();

        [$scopeSql, $scopeParams] = RegionScope::whereSql('e');

        $stmt = $pdo->prepare("
            SELECT
                e.id,
                e.title,
                e.description,
                e.status,
                e.start_at,
                e.end_at,

                o.name AS organization_name,
                rg.code AS region_code,
                rg.name AS region_name,
                rg.level AS region_level,

                COALESCE(rv.total_approved, 0) AS total_approved,
                COALESCE(vt.total_tokens, 0) AS total_tokens,
                COALESCE(vt.total_active, 0) AS total_active,
                COALESCE(vt.total_used, 0) AS total_used,
                COALESCE(vt.total_revoked, 0) AS total_revoked,
                COALESCE(vt.total_expired, 0) AS total_expired

            FROM elections e

            LEFT JOIN organizations o ON o.id = e.organization_id
            LEFT JOIN regions rg ON rg.id = e.region_id

            LEFT JOIN (
                SELECT
                    election_id,
                    COUNT(*) AS total_approved
                FROM remote_verifications
                WHERE status = 'approved'
                GROUP BY election_id
            ) rv ON rv.election_id = e.id

            LEFT JOIN (
                SELECT
                    election_id,
                    COUNT(*) AS total_tokens,
                    SUM(
                        CASE 
                            WHEN used_at IS NULL 
                            AND revoked_at IS NULL 
                            AND expires_at > NOW()
                            THEN 1 ELSE 0 
                        END
                    ) AS total_active,
                    SUM(CASE WHEN used_at IS NOT NULL THEN 1 ELSE 0 END) AS total_used,
                    SUM(CASE WHEN revoked_at IS NOT NULL THEN 1 ELSE 0 END) AS total_revoked,
                    SUM(
                        CASE 
                            WHEN used_at IS NULL 
                            AND revoked_at IS NULL 
                            AND expires_at <= NOW()
                            THEN 1 ELSE 0 
                        END
                    ) AS total_expired
                FROM voting_tokens
                GROUP BY election_id
            ) vt ON vt.election_id = e.id

            {$scopeSql}

            ORDER BY e.created_at DESC
        ");

        $stmt->execute($scopeParams);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function allByElection(int $electionId): array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            SELECT
                vt.*,
                v.voter_code,
                v.name AS voter_name,
                v.phone,
                v.rt,
                v.rw,
                rv.verification_code,
                rv.status AS remote_status,
                u.name AS created_by_name
            FROM voting_tokens vt
            JOIN voters v ON v.id = vt.voter_id
            LEFT JOIN remote_verifications rv ON rv.id = vt.remote_verification_id
            LEFT JOIN users u ON u.id = vt.created_by
            WHERE vt.election_id = ?
            ORDER BY vt.created_at DESC
        ");

        $stmt->execute([$electionId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function approvedRemoteVerifications(int $electionId): array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            SELECT
                rv.*,
                v.voter_code,
                v.name AS voter_name,
                v.phone,
                v.rt,
                v.rw,
                ev.allowed_channel,
                ev.has_voted,

                vt.id AS token_id,
                vt.expires_at AS token_expires_at,
                vt.used_at AS token_used_at,
                vt.revoked_at AS token_revoked_at,
                vt.created_at AS token_created_at

            FROM remote_verifications rv
            JOIN voters v ON v.id = rv.voter_id
            JOIN election_voters ev 
                ON ev.election_id = rv.election_id
               AND ev.voter_id = rv.voter_id

            LEFT JOIN voting_tokens vt
                ON vt.remote_verification_id = rv.id

            WHERE rv.election_id = ?
              AND rv.status = 'approved'

            ORDER BY rv.verified_at DESC, rv.created_at DESC
        ");

        $stmt->execute([$electionId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function find(int $id): ?array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            SELECT *
            FROM voting_tokens
            WHERE id = ?
            LIMIT 1
        ");

        $stmt->execute([$id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public static function activeByRemoteVerification(int $remoteVerificationId): ?array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            SELECT *
            FROM voting_tokens
            WHERE remote_verification_id = ?
              AND used_at IS NULL
              AND revoked_at IS NULL
              AND expires_at > NOW()
            ORDER BY created_at DESC
            LIMIT 1
        ");

        $stmt->execute([$remoteVerificationId]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public static function create(array $data): bool
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            INSERT INTO voting_tokens (
                election_id,
                voter_id,
                remote_verification_id,
                token_hash,
                expires_at,
                created_by,
                created_at
            ) VALUES (
                :election_id,
                :voter_id,
                :remote_verification_id,
                :token_hash,
                :expires_at,
                :created_by,
                NOW()
            )
        ");

        return $stmt->execute([
            'election_id' => $data['election_id'],
            'voter_id' => $data['voter_id'],
            'remote_verification_id' => $data['remote_verification_id'],
            'token_hash' => $data['token_hash'],
            'expires_at' => $data['expires_at'],
            'created_by' => $data['created_by'],
        ]);
    }

    public static function revoke(int $id): bool
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            UPDATE voting_tokens
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
                vt.*,
                e.title AS election_title,
                e.status AS election_status,
                v.voter_code,
                v.name AS voter_name,
                v.rt,
                v.rw,
                rv.status AS remote_status,
                rv.verification_code
            FROM voting_tokens vt
            JOIN elections e ON e.id = vt.election_id
            JOIN voters v ON v.id = vt.voter_id
            LEFT JOIN remote_verifications rv ON rv.id = vt.remote_verification_id
            WHERE vt.token_hash = ?
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
                vt.*,
                rv.status AS remote_status
            FROM voting_tokens vt
            LEFT JOIN remote_verifications rv ON rv.id = vt.remote_verification_id
            WHERE vt.token_hash = ?
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
            UPDATE voting_tokens
            SET used_at = NOW()
            WHERE id = ?
        ");

        return $stmt->execute([$id]);
    }
}