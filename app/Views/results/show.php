<?php

use App\Core\Env;

$appUrl = rtrim(Env::get('APP_URL'), '/');

function statusBadgeClassDetail(string $status): string
{
    return match ($status) {
        'draft' => 'secondary',
        'open' => 'success',
        'closed' => 'warning',
        'finished' => 'dark',
        default => 'secondary',
    };
}

function safePercent(int $value, int $total): float
{
    if ($total <= 0) {
        return 0;
    }

    return round(($value / $total) * 100, 2);
}

function channelLabelResult(string $channel): string
{
    return match ($channel) {
        'tps' => 'TPS',
        'remote' => 'Remote',
        'both' => 'TPS / Remote',
        default => $channel,
    };
}

$totalVoters = (int) ($summary['total_voters'] ?? 0);
$totalVoted = (int) ($summary['total_voted'] ?? 0);
$totalBallots = (int) ($summary['total_ballots'] ?? 0);
$totalCandidates = (int) ($summary['total_candidates'] ?? 0);
$totalNotVoted = max($totalVoters - $totalVoted, 0);

$turnoutPercent = safePercent($totalVoted, $totalVoters);
$ballotMismatch = $totalVoted !== $totalBallots;

?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-0">Detail Hasil Pemilihan</h1>
        <div class="text-muted small">
            <?= htmlspecialchars($election['title']) ?>
        </div>
    </div>

    <div class="d-flex gap-2">
        <?php if (\App\Core\Auth::can('print_results')): ?>
            <a 
                href="<?= htmlspecialchars($appUrl) ?>/elections/<?= $election['id'] ?>/results/print" 
                class="btn btn-dark"
                target="_blank"
            >
                Cetak Berita Acara
            </a>

            <a 
                href="<?= htmlspecialchars($appUrl) ?>/elections/<?= $election['id'] ?>/results/export-csv" 
                class="btn btn-success"
            >
                Export CSV
            </a>
        <?php endif; ?>

        <a href="<?= htmlspecialchars($appUrl) ?>/results" class="btn btn-secondary">
            Kembali
        </a>
    </div>
</div>

<?php if ($election['status'] === 'open'): ?>
    <div class="alert alert-warning">
        Pemilihan masih berstatus <strong>OPEN</strong>. Data di halaman ini adalah hasil sementara.
    </div>
<?php endif; ?>

<?php if ($ballotMismatch): ?>
    <div class="alert alert-danger">
        <strong>Peringatan integritas:</strong>
        jumlah pemilih yang tercatat sudah mencoblos adalah
        <strong><?= htmlspecialchars((string) $totalVoted) ?></strong>,
        sedangkan jumlah suara masuk di tabel ballots adalah
        <strong><?= htmlspecialchars((string) $totalBallots) ?></strong>.
        Seharusnya angka ini sama.
    </div>
<?php endif; ?>

<div class="row mb-3">
    <div class="col-md-3 mb-2">
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Status</div>
                <div class="h5 mb-0">
                    <span class="badge bg-<?= statusBadgeClassDetail($election['status']) ?>">
                        <?= htmlspecialchars(strtoupper($election['status'])) ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-2">
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Total Kandidat</div>
                <div class="h4 mb-0">
                    <?= htmlspecialchars((string) $totalCandidates) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-2">
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Total Pemilih</div>
                <div class="h4 mb-0">
                    <?= htmlspecialchars((string) $totalVoters) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-2">
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Suara Masuk</div>
                <div class="h4 mb-0">
                    <?= htmlspecialchars((string) $totalBallots) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-4 mb-2">
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Sudah Mencoblos</div>
                <div class="h4 mb-0 text-success">
                    <?= htmlspecialchars((string) $totalVoted) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-2">
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Belum Mencoblos</div>
                <div class="h4 mb-0 text-secondary">
                    <?= htmlspecialchars((string) $totalNotVoted) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-2">
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Partisipasi</div>
                <div class="h4 mb-2">
                    <?= htmlspecialchars((string) $turnoutPercent) ?>%
                </div>

                <div class="progress" style="height: 10px;">
                    <div 
                        class="progress-bar" 
                        role="progressbar" 
                        style="width: <?= htmlspecialchars((string) $turnoutPercent) ?>%;"
                        aria-valuenow="<?= htmlspecialchars((string) $turnoutPercent) ?>"
                        aria-valuemin="0"
                        aria-valuemax="100"
                    ></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">
        <strong>Perolehan Suara Kandidat</strong>
    </div>

    <div class="card-body">
        <?php if (empty($candidateResults)): ?>
            <div class="alert alert-info mb-0">
                Belum ada kandidat.
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($candidateResults as $candidate): ?>
                    <?php
                        $votes = (int) $candidate['total_votes'];
                        $percent = safePercent($votes, $totalBallots);
                    ?>

                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body text-center">
                                <div class="mb-2">
                                    <span class="badge bg-dark fs-6">
                                        No. <?= htmlspecialchars((string) $candidate['number_order']) ?>
                                    </span>
                                </div>

                                <?php if (!empty($candidate['photo'])): ?>
                                    <img 
                                        src="<?= htmlspecialchars($appUrl . '/' . $candidate['photo']) ?>" 
                                        alt="Foto kandidat"
                                        class="rounded border mb-3"
                                        style="width: 130px; height: 130px; object-fit: cover;"
                                    >
                                <?php else: ?>
                                    <div 
                                        class="bg-light border rounded d-flex align-items-center justify-content-center text-muted mx-auto mb-3"
                                        style="width: 130px; height: 130px;"
                                    >
                                        No Foto
                                    </div>
                                <?php endif; ?>

                                <h4 class="mb-1">
                                    <?= htmlspecialchars($candidate['name']) ?>
                                </h4>

                                <?php if ((int) $candidate['is_active'] !== 1): ?>
                                    <div class="mb-2">
                                        <span class="badge bg-secondary">Nonaktif</span>
                                    </div>
                                <?php endif; ?>

                                <div class="display-6 fw-bold mb-1">
                                    <?= htmlspecialchars((string) $votes) ?>
                                </div>

                                <div class="text-muted mb-2">
                                    suara / <?= htmlspecialchars((string) $percent) ?>%
                                </div>

                                <div class="progress" style="height: 10px;">
                                    <div 
                                        class="progress-bar" 
                                        role="progressbar" 
                                        style="width: <?= htmlspecialchars((string) $percent) ?>%;"
                                        aria-valuenow="<?= htmlspecialchars((string) $percent) ?>"
                                        aria-valuemin="0"
                                        aria-valuemax="100"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="table-responsive mt-3">
                <table class="table table-bordered table-striped align-middle">
                    <thead>
                    <tr>
                        <th style="width: 80px;">No Urut</th>
                        <th>Nama Kandidat</th>
                        <th style="width: 150px;">Suara</th>
                        <th style="width: 150px;">Persentase</th>
                    </tr>
                    </thead>

                    <tbody>
                    <?php foreach ($candidateResults as $candidate): ?>
                        <?php
                            $votes = (int) $candidate['total_votes'];
                            $percent = safePercent($votes, $totalBallots);
                        ?>

                        <tr>
                            <td>
                                <?= htmlspecialchars((string) $candidate['number_order']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($candidate['name']) ?>
                            </td>

                            <td>
                                <strong><?= htmlspecialchars((string) $votes) ?></strong>
                            </td>

                            <td>
                                <?= htmlspecialchars((string) $percent) ?>%
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <div class="card h-100">
            <div class="card-header">
                <strong>Suara Masuk Berdasarkan Channel</strong>
            </div>

            <div class="card-body">
                <?php if (empty($channelResults)): ?>
                    <div class="alert alert-info mb-0">
                        Belum ada suara masuk.
                    </div>
                <?php else: ?>
                    <table class="table table-bordered align-middle mb-0">
                        <thead>
                        <tr>
                            <th>Channel Suara</th>
                            <th>Total Suara</th>
                            <th>Persentase</th>
                        </tr>
                        </thead>

                        <tbody>
                        <?php foreach ($channelResults as $row): ?>
                            <?php
                                $votes = (int) $row['total_votes'];
                                $percent = safePercent($votes, $totalBallots);
                            ?>

                            <tr>
                                <td><?= htmlspecialchars(channelLabelResult($row['vote_channel'])) ?></td>
                                <td><?= htmlspecialchars((string) $votes) ?></td>
                                <td><?= htmlspecialchars((string) $percent) ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-3">
        <div class="card h-100">
            <div class="card-header">
                <strong>Partisipasi Berdasarkan Hak Channel</strong>
            </div>

            <div class="card-body">
                <?php if (empty($turnoutByChannel)): ?>
                    <div class="alert alert-info mb-0">
                        Belum ada daftar pemilih.
                    </div>
                <?php else: ?>
                    <table class="table table-bordered align-middle mb-0">
                        <thead>
                        <tr>
                            <th>Hak Channel</th>
                            <th>Total Pemilih</th>
                            <th>Sudah Coblos</th>
                            <th>Partisipasi</th>
                        </tr>
                        </thead>

                        <tbody>
                        <?php foreach ($turnoutByChannel as $row): ?>
                            <?php
                                $channelVoters = (int) $row['total_voters'];
                                $channelVoted = (int) $row['total_voted'];
                                $percent = safePercent($channelVoted, $channelVoters);
                            ?>

                            <tr>
                                <td><?= htmlspecialchars(channelLabelResult($row['allowed_channel'])) ?></td>
                                <td><?= htmlspecialchars((string) $channelVoters) ?></td>
                                <td><?= htmlspecialchars((string) $channelVoted) ?></td>
                                <td><?= htmlspecialchars((string) $percent) ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>