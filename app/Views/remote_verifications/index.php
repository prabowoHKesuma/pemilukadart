<?php

use App\Core\Env;

$appUrl = rtrim(Env::get('APP_URL'), '/');

function rvStatusBadge(string $status): string
{
    return match ($status) {
        'draft' => 'secondary',
        'open' => 'success',
        'closed' => 'warning',
        'finished' => 'dark',
        default => 'secondary',
    };
}

?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-0">Verifikasi Remote</h1>
        <div class="text-muted small">
            Kelola verifikasi pemilih luar kota / tidak hadir TPS.
        </div>
    </div>
</div>

<div class="alert alert-warning">
    Verifikasi remote hanya untuk pemilih dengan channel <strong>Remote</strong> atau <strong>TPS / Remote</strong>.
    Jangan gunakan untuk pemilih biasa yang bisa hadir di TPS.
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($elections)): ?>
            <div class="alert alert-info mb-0">
                Belum ada pemilihan.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Nama Pemilihan</th>
                        <th>Status</th>
                        <th>Eligible Remote</th>
                        <th>Request</th>
                        <th>Pending</th>
                        <th>Approved</th>
                        <th>Rejected</th>
                        <th style="width: 120px;">Aksi</th>
                    </tr>
                    </thead>

                    <tbody>
                    <?php foreach ($elections as $index => $election): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>

                            <td>
                                <strong><?= htmlspecialchars($election['title']) ?></strong>

                                <?php if (!empty($election['description'])): ?>
                                    <div class="small text-muted">
                                        <?= nl2br(htmlspecialchars($election['description'])) ?>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <td>
                                <span class="badge bg-<?= rvStatusBadge($election['status']) ?>">
                                    <?= htmlspecialchars(strtoupper($election['status'])) ?>
                                </span>
                            </td>

                            <td><?= htmlspecialchars((string) $election['total_remote_eligible']) ?></td>
                            <td><?= htmlspecialchars((string) $election['total_requests']) ?></td>
                            <td><?= htmlspecialchars((string) $election['total_pending']) ?></td>
                            <td><?= htmlspecialchars((string) $election['total_approved']) ?></td>
                            <td><?= htmlspecialchars((string) $election['total_rejected']) ?></td>

                            <td>
                                <a 
                                    href="<?= htmlspecialchars($appUrl) ?>/elections/<?= $election['id'] ?>/remote-verifications" 
                                    class="btn btn-sm btn-primary"
                                >
                                    Kelola
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