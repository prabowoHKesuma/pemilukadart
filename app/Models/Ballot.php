<?php

namespace App\Models;

use PDO;

class Ballot
{
    public static function create(PDO $pdo, int $electionId, int $candidateId, string $voteChannel): bool
    {
        $stmt = $pdo->prepare("
            INSERT INTO ballots (
                election_id,
                candidate_id,
                ballot_code,
                vote_channel,
                created_at
            ) VALUES (
                :election_id,
                :candidate_id,
                :ballot_code,
                :vote_channel,
                NOW()
            )
        ");

        return $stmt->execute([
            'election_id' => $electionId,
            'candidate_id' => $candidateId,
            'ballot_code' => bin2hex(random_bytes(16)),
            'vote_channel' => $voteChannel,
        ]);
    }
}