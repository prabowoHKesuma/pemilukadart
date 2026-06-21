<?php

namespace App\Models;

use App\Core\Database;
use App\Core\RegionScope;
use PDO;

class Result
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
                e.created_at,

                o.name AS organization_name,
                rg.code AS region_code,
                rg.name AS region_name,
                rg.level AS region_level,

                COALESCE(c.total_candidates, 0) AS total_candidates,
                COALESCE(ev.total_voters, 0) AS total_voters,
                COALESCE(ev.total_voted, 0) AS total_voted,
                COALESCE(b.total_ballots, 0) AS total_ballots

            FROM elections e

            LEFT JOIN organizations o ON o.id = e.organization_id
            LEFT JOIN regions rg ON rg.id = e.region_id

            LEFT JOIN (
                SELECT 
                    election_id,
                    COUNT(*) AS total_candidates
                FROM candidates
                GROUP BY election_id
            ) c ON c.election_id = e.id

            LEFT JOIN (
                SELECT 
                    election_id,
                    COUNT(*) AS total_voters,
                    SUM(CASE WHEN has_voted = 1 THEN 1 ELSE 0 END) AS total_voted
                FROM election_voters
                GROUP BY election_id
            ) ev ON ev.election_id = e.id

            LEFT JOIN (
                SELECT 
                    election_id,
                    COUNT(*) AS total_ballots
                FROM ballots
                GROUP BY election_id
            ) b ON b.election_id = e.id

            {$scopeSql}

            ORDER BY e.created_at DESC
        ");

        $stmt->execute($scopeParams);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function summary(int $electionId): array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            SELECT
                e.id,
                e.title,
                e.description,
                e.status,
                e.start_at,
                e.end_at,

                COALESCE(c.total_candidates, 0) AS total_candidates,
                COALESCE(ev.total_voters, 0) AS total_voters,
                COALESCE(ev.total_voted, 0) AS total_voted,
                COALESCE(b.total_ballots, 0) AS total_ballots,

                COALESCE(ev.total_channel_tps, 0) AS total_channel_tps,
                COALESCE(ev.total_channel_remote, 0) AS total_channel_remote,
                COALESCE(ev.total_channel_both, 0) AS total_channel_both

            FROM elections e

            LEFT JOIN (
                SELECT 
                    election_id,
                    COUNT(*) AS total_candidates
                FROM candidates
                GROUP BY election_id
            ) c ON c.election_id = e.id

            LEFT JOIN (
                SELECT 
                    election_id,
                    COUNT(*) AS total_voters,
                    SUM(CASE WHEN has_voted = 1 THEN 1 ELSE 0 END) AS total_voted,
                    SUM(CASE WHEN allowed_channel = 'tps' THEN 1 ELSE 0 END) AS total_channel_tps,
                    SUM(CASE WHEN allowed_channel = 'remote' THEN 1 ELSE 0 END) AS total_channel_remote,
                    SUM(CASE WHEN allowed_channel = 'both' THEN 1 ELSE 0 END) AS total_channel_both
                FROM election_voters
                GROUP BY election_id
            ) ev ON ev.election_id = e.id

            LEFT JOIN (
                SELECT 
                    election_id,
                    COUNT(*) AS total_ballots
                FROM ballots
                GROUP BY election_id
            ) b ON b.election_id = e.id

            WHERE e.id = ?

            LIMIT 1
        ");

        $stmt->execute([$electionId]);

        $summary = $stmt->fetch(PDO::FETCH_ASSOC);

        return $summary ?: [];
    }

    public static function votesByCandidate(int $electionId): array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            SELECT
                c.id,
                c.number_order,
                c.name,
                c.photo,
                c.vision,
                c.mission,
                c.is_active,
                COUNT(b.id) AS total_votes
            FROM candidates c

            LEFT JOIN ballots b
                ON b.candidate_id = c.id
               AND b.election_id = c.election_id

            WHERE c.election_id = ?

            GROUP BY 
                c.id,
                c.number_order,
                c.name,
                c.photo,
                c.vision,
                c.mission,
                c.is_active

            ORDER BY total_votes DESC, c.number_order ASC, c.name ASC
        ");

        $stmt->execute([$electionId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function votesByChannel(int $electionId): array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            SELECT
                vote_channel,
                COUNT(*) AS total_votes
            FROM ballots
            WHERE election_id = ?
            GROUP BY vote_channel
        ");

        $stmt->execute([$electionId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function turnoutByChannel(int $electionId): array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            SELECT
                allowed_channel,
                COUNT(*) AS total_voters,
                SUM(CASE WHEN has_voted = 1 THEN 1 ELSE 0 END) AS total_voted
            FROM election_voters
            WHERE election_id = ?
            GROUP BY allowed_channel
        ");

        $stmt->execute([$electionId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}