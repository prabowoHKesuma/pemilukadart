<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Election;
use App\Models\Result;
use App\Models\AuditLog;

class ResultController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();

        $elections = Result::electionsWithStats();

        $this->view('results/index', [
            'title' => 'Hasil Pemilihan',
            'elections' => $elections,
        ]);
    }

    public function show(string $electionId): void
    {
        Auth::requireLogin();

        $election = Election::find((int) $electionId);

        if (!$election) {
            http_response_code(404);
            die('Data pemilihan tidak ditemukan.');
        }

        $summary = Result::summary((int) $electionId);
        $candidateResults = Result::votesByCandidate((int) $electionId);
        $channelResults = Result::votesByChannel((int) $electionId);
        $turnoutByChannel = Result::turnoutByChannel((int) $electionId);

        AuditLog::record(
            'result_view',
            'Membuka detail hasil election ID ' . $electionId
        );

        $this->view('results/show', [
            'title' => 'Detail Hasil Pemilihan',
            'election' => $election,
            'summary' => $summary,
            'candidateResults' => $candidateResults,
            'channelResults' => $channelResults,
            'turnoutByChannel' => $turnoutByChannel,
        ]);
    }
}