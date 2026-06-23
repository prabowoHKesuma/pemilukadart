<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\AuditLog;
use App\Models\Election;
use App\Models\Result;

class ResultController extends Controller
{
    public function index(): void
    {
        Auth::requirePermission('view_results');

        $elections = Result::electionsWithStats();

         $this->view('results/index', [
            'title' => 'Hasil Pemilihan',
            'elections' => $this->prepareElectionRows($elections),
            'canPrint' => Auth::can('print_results'),
        ]);
    }

    public function show(string $electionId): void
    {
        Auth::requirePermission('view_results');

        $payload = $this->resultPayload((int) $electionId);
        $payload = $this->prepareResultPayload($payload);

        AuditLog::record(
            'result_view',
            'Membuka detail hasil election ID ' . $electionId,
            null,
            [
                'election_id' => (int) $electionId,
                'organization_id' => $payload['election']['organization_id'] ?? null,
                'region_id' => $payload['election']['region_id'] ?? null,
            ]
        );

        $this->view('results/show', array_merge($payload, [
            'title' => 'Detail Hasil Pemilihan',
            'canPrint' => Auth::can('print_results'),
        ]));
    }

    public function printReport(string $electionId): void
    {
        Auth::requirePermission('print_results');

        $payload = $this->resultPayload((int) $electionId);
         $payload = $this->prepareResultPayload($payload);

        AuditLog::record(
            'result_print',
            'Mencetak berita acara hasil election ID ' . $electionId,
            null,
            [
                'election_id' => (int) $electionId,
                'organization_id' => $payload['election']['organization_id'] ?? null,
                'region_id' => $payload['election']['region_id'] ?? null,
            ]
        );

        $this->view('results/print', array_merge($payload, [
            'title' => 'Cetak Berita Acara',
            'printedAt' => date('d/m/Y H:i:s'),
        ]), 'print');
    }

    private function prepareElectionRows(array $elections): array
    {
        return array_map(function (array $election): array {
            $totalVoters = (int) ($election['total_voters'] ?? 0);
            $totalVoted = (int) ($election['total_voted'] ?? 0);
            $totalBallots = (int) ($election['total_ballots'] ?? 0);

            $election['total_voters'] = $totalVoters;
            $election['total_voted'] = $totalVoted;
            $election['total_ballots'] = $totalBallots;
            $election['total_not_voted'] = max($totalVoters - $totalVoted, 0);
            $election['turnout_percent'] = $this->percent($totalVoted, $totalVoters);
            $election['ballot_mismatch'] = $totalVoted !== $totalBallots;

            $election['detail_url'] = '/elections/' . (int) $election['id'] . '/results';
            $election['print_url'] = '/elections/' . (int) $election['id'] . '/results/print';
            $election['export_url'] = '/elections/' . (int) $election['id'] . '/results/export-csv';

            return $election;
        }, $elections);
    }

    private function prepareResultPayload(array $payload): array
    {
        $election = $payload['election'];
        $summary = $payload['summary'] ?? [];

        $totalVoters = (int) ($summary['total_voters'] ?? 0);
        $totalVoted = (int) ($summary['total_voted'] ?? 0);
        $totalBallots = (int) ($summary['total_ballots'] ?? 0);
        $totalCandidates = (int) ($summary['total_candidates'] ?? 0);

        $summary['total_voters'] = $totalVoters;
        $summary['total_voted'] = $totalVoted;
        $summary['total_ballots'] = $totalBallots;
        $summary['total_candidates'] = $totalCandidates;
        $summary['total_not_voted'] = max($totalVoters - $totalVoted, 0);
        $summary['turnout_percent'] = $this->percent($totalVoted, $totalVoters);
        $summary['ballot_mismatch'] = $totalVoted !== $totalBallots;
        $summary['integrity_label'] = $summary['ballot_mismatch'] ? 'TIDAK SAMA' : 'OK';

        $candidateResults = array_map(function (array $candidate) use ($totalBallots): array {
            $votes = (int) ($candidate['total_votes'] ?? 0);

            $candidate['total_votes'] = $votes;
            $candidate['vote_percent'] = $this->percent($votes, $totalBallots);
            $candidate['active_label'] = (int) ($candidate['is_active'] ?? 0) === 1 ? 'Aktif' : 'Nonaktif';

            $candidate['photo_url'] = !empty($candidate['photo'])
                ? '/' . ltrim((string) $candidate['photo'], '/')
                : null;

            return $candidate;
        }, $payload['candidateResults'] ?? []);

        $channelResults = array_map(function (array $row) use ($totalBallots): array {
            $votes = (int) ($row['total_votes'] ?? 0);

            $row['total_votes'] = $votes;
            $row['channel_label'] = $this->channelLabel($row['vote_channel'] ?? null);
            $row['vote_percent'] = $this->percent($votes, $totalBallots);

            return $row;
        }, $payload['channelResults'] ?? []);

        $turnoutByChannel = array_map(function (array $row): array {
            $totalVoters = (int) ($row['total_voters'] ?? 0);
            $totalVoted = (int) ($row['total_voted'] ?? 0);

            $row['total_voters'] = $totalVoters;
            $row['total_voted'] = $totalVoted;
            $row['channel_label'] = $this->channelLabel($row['allowed_channel'] ?? null);
            $row['turnout_percent'] = $this->percent($totalVoted, $totalVoters);

            return $row;
        }, $payload['turnoutByChannel'] ?? []);

        $payload['summary'] = $summary;
        $payload['candidateResults'] = $candidateResults;
        $payload['channelResults'] = $channelResults;
        $payload['turnoutByChannel'] = $turnoutByChannel;

        $payload['isElectionOpen'] = ($election['status'] ?? '') === 'open';
        $payload['print_url'] = '/elections/' . (int) $election['id'] . '/results/print';
        $payload['export_url'] = '/elections/' . (int) $election['id'] . '/results/export-csv';
        $payload['back_url'] = '/results';

        return $payload;
    }

    public function exportCsv(string $electionId): void
    {
        Auth::requirePermission('print_results');

        $payload = $this->resultPayload((int) $electionId);

        $election = $payload['election'];
        $summary = $payload['summary'];
        $candidateResults = $payload['candidateResults'];
        $channelResults = $payload['channelResults'];
        $turnoutByChannel = $payload['turnoutByChannel'];

        AuditLog::record(
            'result_export_csv',
            'Export CSV hasil election ID ' . $electionId
        );

        $filename = 'hasil-pemilihan-' . $electionId . '-' . date('Ymd-His') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        // BOM supaya Excel Windows membaca UTF-8 dengan benar
        echo "\xEF\xBB\xBF";

        $output = fopen('php://output', 'w');

        fputcsv($output, ['BERITA ACARA HASIL PEMILIHAN']);
        fputcsv($output, ['Nama Pemilihan', $election['title']]);
        fputcsv($output, ['Deskripsi', $election['description'] ?? '-']);
        fputcsv($output, ['Status', strtoupper($election['status'])]);
        fputcsv($output, ['Tanggal Mulai', $election['start_at'] ?? '-']);
        fputcsv($output, ['Tanggal Selesai', $election['end_at'] ?? '-']);
        fputcsv($output, ['Tanggal Export', date('Y-m-d H:i:s')]);
        fputcsv($output, []);

        $totalVoters = (int) ($summary['total_voters'] ?? 0);
        $totalVoted = (int) ($summary['total_voted'] ?? 0);
        $totalBallots = (int) ($summary['total_ballots'] ?? 0);
        $totalNotVoted = max($totalVoters - $totalVoted, 0);
        $turnoutPercent = $this->percent($totalVoted, $totalVoters);

        fputcsv($output, ['RINGKASAN']);
        fputcsv($output, ['Total Kandidat', (int) ($summary['total_candidates'] ?? 0)]);
        fputcsv($output, ['Total Pemilih', $totalVoters]);
        fputcsv($output, ['Sudah Mencoblos', $totalVoted]);
        fputcsv($output, ['Belum Mencoblos', $totalNotVoted]);
        fputcsv($output, ['Suara Masuk', $totalBallots]);
        fputcsv($output, ['Partisipasi', $turnoutPercent . '%']);
        fputcsv($output, ['Validasi Integritas', $totalVoted === $totalBallots ? 'OK' : 'TIDAK SAMA']);
        fputcsv($output, []);

        fputcsv($output, ['PEROLEHAN SUARA KANDIDAT']);
        fputcsv($output, ['No Urut', 'Nama Kandidat', 'Status Kandidat', 'Suara', 'Persentase']);

        foreach ($candidateResults as $candidate) {
            $votes = (int) $candidate['total_votes'];
            $percent = $this->percent($votes, $totalBallots);

            fputcsv($output, [
                $candidate['number_order'],
                $candidate['name'],
                ((int) $candidate['is_active'] === 1 ? 'Aktif' : 'Nonaktif'),
                $votes,
                $percent . '%',
            ]);
        }

        fputcsv($output, []);
        fputcsv($output, ['SUARA MASUK BERDASARKAN CHANNEL']);
        fputcsv($output, ['Channel', 'Total Suara', 'Persentase']);

        foreach ($channelResults as $row) {
            $votes = (int) $row['total_votes'];
            $percent = $this->percent($votes, $totalBallots);

            fputcsv($output, [
                $this->channelLabel($row['vote_channel']),
                $votes,
                $percent . '%',
            ]);
        }

        fputcsv($output, []);
        fputcsv($output, ['PARTISIPASI BERDASARKAN HAK CHANNEL']);
        fputcsv($output, ['Hak Channel', 'Total Pemilih', 'Sudah Coblos', 'Partisipasi']);

        foreach ($turnoutByChannel as $row) {
            $channelVoters = (int) $row['total_voters'];
            $channelVoted = (int) $row['total_voted'];
            $percent = $this->percent($channelVoted, $channelVoters);

            fputcsv($output, [
                $this->channelLabel($row['allowed_channel']),
                $channelVoters,
                $channelVoted,
                $percent . '%',
            ]);
        }

        fclose($output);
        exit;
    }

    private function resultPayload(int $electionId): array
    {
        $election = Election::find($electionId);

        if (!$election) {
            http_response_code(404);
            die('Data pemilihan tidak ditemukan.');
        }

        return [
            'election' => $election,
            'summary' => Result::summary($electionId),
            'candidateResults' => Result::votesByCandidate($electionId),
            'channelResults' => Result::votesByChannel($electionId),
            'turnoutByChannel' => Result::turnoutByChannel($electionId),
        ];
    }

    private function percent(int $value, int $total): float
    {
        if ($total <= 0) {
            return 0;
        }

        return round(($value / $total) * 100, 2);
    }

    private function channelLabel(string $channel): string
    {
        return match ($channel) {
            'tps' => 'TPS',
            'remote' => 'Remote',
            'both' => 'TPS / Remote',
            default => $channel,
        };
    }
}