<?php

namespace App\Models;

use App\Core\Database;
use App\Core\RegionScope;
use PDO;

class RemoteVerification
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

                COALESCE(ev.total_remote_eligible, 0) AS total_remote_eligible,
                COALESCE(rv.total_requests, 0) AS total_requests,
                COALESCE(rv.total_pending, 0) AS total_pending,
                COALESCE(rv.total_approved, 0) AS total_approved,
                COALESCE(rv.total_rejected, 0) AS total_rejected

            FROM elections e

            LEFT JOIN organizations o ON o.id = e.organization_id
            LEFT JOIN regions rg ON rg.id = e.region_id

            LEFT JOIN (
                SELECT
                    election_id,
                    COUNT(*) AS total_remote_eligible
                FROM election_voters
                WHERE allowed_channel IN ('remote', 'both')
                GROUP BY election_id
            ) ev ON ev.election_id = e.id

            LEFT JOIN (
                SELECT
                    election_id,
                    COUNT(*) AS total_requests,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS total_pending,
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) AS total_approved,
                    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) AS total_rejected
                FROM remote_verifications
                GROUP BY election_id
            ) rv ON rv.election_id = e.id

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
                rv.*,
                v.voter_code,
                v.name AS voter_name,
                v.address,
                v.phone,
                v.rt,
                v.rw,
                ev.allowed_channel,
                ev.has_voted,
                u1.name AS verifier_1_name,
                u2.name AS verifier_2_name
            FROM remote_verifications rv
            JOIN voters v ON v.id = rv.voter_id
            LEFT JOIN election_voters ev
                ON ev.election_id = rv.election_id
               AND ev.voter_id = rv.voter_id
            LEFT JOIN users u1 ON u1.id = rv.verified_by_1
            LEFT JOIN users u2 ON u2.id = rv.verified_by_2
            WHERE rv.election_id = ?
            ORDER BY rv.created_at DESC
        ");

        $stmt->execute([$electionId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function availableElectionVoters(int $electionId): array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            SELECT
                ev.id AS election_voter_id,
                ev.election_id,
                ev.voter_id,
                ev.allowed_channel,
                ev.has_voted,
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
              AND ev.allowed_channel IN ('remote', 'both')
              AND ev.has_voted = 0
              AND v.is_active = 1
              AND ev.voter_id NOT IN (
                  SELECT voter_id
                  FROM remote_verifications
                  WHERE election_id = ?
              )
            ORDER BY v.name ASC
        ");

        $stmt->execute([$electionId, $electionId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findAvailableElectionVoter(int $electionId, int $electionVoterId): ?array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            SELECT
                ev.id AS election_voter_id,
                ev.election_id,
                ev.voter_id,
                ev.allowed_channel,
                ev.has_voted,
                v.voter_code,
                v.name,
                v.is_active
            FROM election_voters ev
            JOIN voters v ON v.id = ev.voter_id
            WHERE ev.election_id = ?
              AND ev.id = ?
              AND ev.allowed_channel IN ('remote', 'both')
              AND ev.has_voted = 0
              AND v.is_active = 1
            LIMIT 1
        ");

        $stmt->execute([$electionId, $electionVoterId]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public static function exists(int $electionId, int $voterId): bool
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM remote_verifications
            WHERE election_id = ?
              AND voter_id = ?
        ");

        $stmt->execute([$electionId, $voterId]);

        return (int) $stmt->fetchColumn() > 0;
    }

    public static function find(int $id): ?array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            SELECT
                rv.*,
                e.title AS election_title,
                e.status AS election_status,
                v.voter_code,
                v.name AS voter_name,
                v.address,
                v.phone,
                v.rt,
                v.rw,
                ev.allowed_channel,
                ev.has_voted,
                u1.name AS verifier_1_name,
                u2.name AS verifier_2_name
            FROM remote_verifications rv
            JOIN elections e ON e.id = rv.election_id
            JOIN voters v ON v.id = rv.voter_id
            LEFT JOIN election_voters ev
                ON ev.election_id = rv.election_id
               AND ev.voter_id = rv.voter_id
            LEFT JOIN users u1 ON u1.id = rv.verified_by_1
            LEFT JOIN users u2 ON u2.id = rv.verified_by_2
            WHERE rv.id = ?
            LIMIT 1
        ");

        $stmt->execute([$id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public static function findByElection(int $electionId, int $id): ?array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            SELECT *
            FROM remote_verifications
            WHERE election_id = ?
              AND id = ?
            LIMIT 1
        ");

        $stmt->execute([$electionId, $id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public static function create(array $data): bool
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            INSERT INTO remote_verifications (
                election_id,
                voter_id,
                verification_code,
                status,
                expires_at,
                created_at
            ) VALUES (
                :election_id,
                :voter_id,
                :verification_code,
                'pending',
                :expires_at,
                NOW()
            )
        ");

        /* return $stmt->execute([
            'election_id' => $data['election_id'],
            'voter_id' => $data['voter_id'],
            'verification_code' => $data['verification_code'],
            'expires_at' => $data['expires_at'],
        ]); */

            $stmt->execute([
            'election_id' => $data['election_id'],
            'voter_id' => $data['voter_id'],
            'verification_code' => $data['verification_code'],
            'expires_at' => $data['expires_at'],
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function updatePhotos(int $id, string $ktpPath, string $selfiePath): bool
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            UPDATE remote_verifications
            SET
                ktp_photo_path = :ktp_photo_path,
                selfie_photo_path = :selfie_photo_path,
                consent_accepted = 1,
                consent_at = NOW(),
                updated_at = NOW()
            WHERE id = :id
        ");

        return $stmt->execute([
            'id' => $id,
            'ktp_photo_path' => $ktpPath,
            'selfie_photo_path' => $selfiePath,
        ]);
    }

    public static function approveFirst(int $id, int $userId): bool
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            UPDATE remote_verifications
            SET
                verified_by_1 = ?,
                updated_at = NOW()
            WHERE id = ?
        ");

        return $stmt->execute([$userId, $id]);
    }

    public static function approveSecond(int $id, int $userId): bool
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            UPDATE remote_verifications
            SET
                verified_by_2 = ?,
                status = 'approved',
                verified_at = NOW(),
                updated_at = NOW()
            WHERE id = ?
        ");

        return $stmt->execute([$userId, $id]);
    }

    public static function reject(int $id, int $userId, string $reason): bool
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            UPDATE remote_verifications
            SET
                status = 'rejected',
                reject_reason = :reject_reason,
                verified_by_1 = COALESCE(verified_by_1, :user_id),
                verified_at = NOW(),
                updated_at = NOW()
            WHERE id = :id
        ");

        return $stmt->execute([
            'id' => $id,
            'user_id' => $userId,
            'reject_reason' => $reason,
        ]);
    }

    public static function findBasic(int $id): ?array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            SELECT
                id,
                election_id,
                voter_id,
                status,
                ktp_photo_path,
                selfie_photo_path
            FROM remote_verifications
            WHERE id = ?
            LIMIT 1
        ");

        $stmt->execute([$id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public static function findDetailScoped(int $id, array $election): ?array
    {
        $pdo = Database::connection();

        $targetRegionId = !empty($election['region_id']) ? (int) $election['region_id'] : null;

        [$regionSql, $regionParams] = RegionScope::andSqlForTarget('v', $targetRegionId);

        $stmt = $pdo->prepare("
            SELECT
                rv.*,

                v.voter_code,
                v.name AS voter_name,
                v.address,
                v.phone,
                v.rt,
                v.rw,
                v.is_active,
                v.organization_id AS voter_organization_id,
                v.region_id AS voter_region_id,

                ev.allowed_channel,
                ev.has_voted,
                ev.voted_at,

                r1.name AS verifier_1_name,
                r2.name AS verifier_2_name,

                rg.code AS region_code,
                rg.name AS region_name,
                rg.level AS region_level

            FROM remote_verifications rv

            JOIN voters v
                ON v.id = rv.voter_id

            JOIN election_voters ev
                ON ev.election_id = rv.election_id
            AND ev.voter_id = rv.voter_id

            LEFT JOIN users r1
                ON r1.id = rv.verified_by_1

            LEFT JOIN users r2
                ON r2.id = rv.verified_by_2

            LEFT JOIN regions rg
                ON rg.id = v.region_id

            WHERE rv.id = ?
            AND rv.election_id = ?
            {$regionSql}

            LIMIT 1
        ");

        $stmt->execute(array_merge([
            $id,
            (int) $election['id'],
        ], $regionParams));

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public static function allByElectionScoped(array $election): array
    {
        $pdo = Database::connection();

        $targetRegionId = !empty($election['region_id']) ? (int) $election['region_id'] : null;

        [$regionSql, $regionParams] = RegionScope::andSqlForTarget('v', $targetRegionId);

        $stmt = $pdo->prepare("
            SELECT
                rv.*,

                v.voter_code,
                v.name AS voter_name,
                v.phone,
                v.rt,
                v.rw,
                v.region_id AS voter_region_id,

                ev.allowed_channel,
                ev.has_voted,

                r1.name AS verifier_1_name,
                r2.name AS verifier_2_name

            FROM remote_verifications rv

            JOIN voters v
                ON v.id = rv.voter_id

            JOIN election_voters ev
                ON ev.election_id = rv.election_id
            AND ev.voter_id = rv.voter_id

            LEFT JOIN users r1
                ON r1.id = rv.verified_by_1

            LEFT JOIN users r2
                ON r2.id = rv.verified_by_2

            WHERE rv.election_id = ?
            {$regionSql}

            ORDER BY rv.created_at DESC
        ");

        $stmt->execute(array_merge([
            (int) $election['id'],
        ], $regionParams));

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function generateUploadToken(int $id, int $expiresHours = 24): string
    {
        $pdo = Database::connection();

        $plainToken = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $plainToken);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+' . $expiresHours . ' hours'));

        $stmt = $pdo->prepare("
            UPDATE remote_verifications
            SET
                upload_token_hash = ?,
                upload_token_expires_at = ?,
                updated_at = NOW()
            WHERE id = ?
        ");

        $stmt->execute([
            $tokenHash,
            $expiresAt,
            $id,
        ]);

        return $plainToken;
    }

    public static function findByUploadToken(string $plainToken): ?array
    {
        $pdo = Database::connection();

        $tokenHash = hash('sha256', $plainToken);

        $stmt = $pdo->prepare("
            SELECT
                rv.*,

                e.title AS election_title,
                e.status AS election_status,

                v.voter_code,
                v.name AS voter_name,
                v.phone,
                v.rt,
                v.rw,
                v.is_active,

                ev.allowed_channel,
                ev.has_voted

            FROM remote_verifications rv

            JOIN elections e
                ON e.id = rv.election_id

            JOIN voters v
                ON v.id = rv.voter_id

            JOIN election_voters ev
                ON ev.election_id = rv.election_id
            AND ev.voter_id = rv.voter_id

            WHERE rv.upload_token_hash = ?
            LIMIT 1
        ");

        $stmt->execute([$tokenHash]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public static function markPublicUploaded(int $id, string $ktpPath, string $selfiePath): bool
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            UPDATE remote_verifications
            SET
                ktp_photo_path = ?,
                selfie_photo_path = ?,
                upload_uploaded_at = NOW(),
                updated_at = NOW()
            WHERE id = ?
        ");

        return $stmt->execute([
            $ktpPath,
            $selfiePath,
            $id,
        ]);
    }
}