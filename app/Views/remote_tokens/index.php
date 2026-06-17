<?php

use App\Core\Env;

$appUrl = rtrim(Env::get('APP_URL'), '/');

function tokenStatusBadgeElection(string $status): string
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
        <h1 class="h3 mb-0">Token Voting Remote</h1>
        <div class="text-muted small">
            Generate dan kelola token coblos remote.
        </div>
    </div>
</div>

<div class="alert alert-warning">
    Token hanya boleh diberikan kepada pemilih remote yang verifikasinya sudah <strong>APPROVED</strong>.
    Token asli hanya muncul sekali saat dibuat.
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
                        <th>Remote Approved</th>
                        <th>Total Token</th>
                        <th>Aktif</th>
                        <th>Used</th>
                        <th>Revoked</th>
                        <th>Expired</th>
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
                                <span class="badge bg-<?= tokenStatusBadgeElection($election['status']) ?>">
                                    <?= htmlspecialchars(strtoupper($election['status'])) ?>
                                </span>
                            </td>

                            <td><?= htmlspecialchars((string) $election['total_approved']) ?></td>
                            <td><?= htmlspecialchars((string) $election['total_tokens']) ?></td>
                            <td><?= htmlspecialchars((string) $election['total_active']) ?></td>
                            <td><?= htmlspecialchars((string) $election['total_used']) ?></td>
                            <td><?= htmlspecialchars((string) $election['total_revoked']) ?></td>
                            <td><?= htmlspecialchars((string) $election['total_expired']) ?></td>

                            <td>
                                <a 
                                    href="<?= htmlspecialchars($appUrl) ?>/elections/<?= $election['id'] ?>/remote-tokens" 
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