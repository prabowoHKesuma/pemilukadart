<?php

use App\Core\Env;

$appUrl = rtrim(Env::get('APP_URL'), '/');

function statusBadgeClass(string $status): string
{
    return match ($status) {
        'draft' => 'secondary',
        'open' => 'success',
        'closed' => 'warning',
        'finished' => 'dark',
        default => 'secondary',
    };
}

function percentValue(int $value, int $total): float
{
    if ($total <= 0) {
        return 0;
    }

    return round(($value / $total) * 100, 2);
}

?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-0">Hasil Pemilihan</h1>
        <div class="text-muted small">
            Rekap suara dan partisipasi semua pemilihan.
        </div>
    </div>
</div>

<div class="alert alert-warning">
    Hasil untuk pemilihan berstatus <strong>OPEN</strong> adalah hasil sementara.
    Jangan tampilkan ke publik sebelum pemilihan ditutup.
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($elections)): ?>
            <div class="alert alert-info mb-0">
                Belum ada data pemilihan.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Nama Pemilihan</th>
                        <th>Status</th>
                        <th>Kandidat</th>
                        <th>Total Pemilih</th>
                        <th>Sudah Coblos</th>
                        <th>Belum Coblos</th>
                        <th>Suara Masuk</th>
                        <th>Partisipasi</th>
                        <th style="width: 120px;">Aksi</th>
                    </tr>
                    </thead>

                    <tbody>
                    <?php foreach ($elections as $index => $election): ?>
                        <?php
                            $totalVoters = (int) $election['total_voters'];
                            $totalVoted = (int) $election['total_voted'];
                            $totalBallots = (int) $election['total_ballots'];
                            $notVoted = max($totalVoters - $totalVoted, 0);
                            $turnoutPercent = percentValue($totalVoted, $totalVoters);
                            $isMismatch = $totalVoted !== $totalBallots;
                        ?>

                        <tr>
                            <td><?= $index + 1 ?></td>

                            <td>
                                <strong><?= htmlspecialchars($election['title']) ?></strong>

                                <?php if (!empty($election['description'])): ?>
                                    <div class="small text-muted">
                                        <?= nl2br(htmlspecialchars($election['description'])) ?>
                                    </div>
                                <?php endif; ?>

                                <?php if ($isMismatch): ?>
                                    <div class="small text-danger mt-1">
                                        Peringatan: jumlah sudah coblos tidak sama dengan suara masuk.
                                    </div>
                                <?php endif; ?>
                            </td>

                            <td>
                                <span class="badge bg-<?= statusBadgeClass($election['status']) ?>">
                                    <?= htmlspecialchars(strtoupper($election['status'])) ?>
                                </span>
                            </td>

                            <td>
                                <?= htmlspecialchars((string) $election['total_candidates']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars((string) $totalVoters) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars((string) $totalVoted) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars((string) $notVoted) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars((string) $totalBallots) ?>
                            </td>

                            <td>
                                <div><?= htmlspecialchars((string) $turnoutPercent) ?>%</div>

                                <div class="progress" style="height: 8px;">
                                    <div 
                                        class="progress-bar" 
                                        role="progressbar" 
                                        style="width: <?= htmlspecialchars((string) $turnoutPercent) ?>%;"
                                        aria-valuenow="<?= htmlspecialchars((string) $turnoutPercent) ?>"
                                        aria-valuemin="0"
                                        aria-valuemax="100"
                                    ></div>
                                </div>
                            </td>

                            <td>
                                <a 
                                    href="<?= htmlspecialchars($appUrl) ?>/elections/<?= $election['id'] ?>/results" 
                                    class="btn btn-sm btn-primary"
                                >
                                    Detail
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>

                </table>
            </div>
        <?php endif; ?>
    </div>
</div>