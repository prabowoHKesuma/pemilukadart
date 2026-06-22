<?php

use App\Core\ViewFormatter as F;

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
                                <strong><?= F::dash($election['title'] ?? null) ?></strong>

                                <?php if (!empty($election['description'])): ?>
                                    <div class="small text-muted">
                                        <?= F::nl2brSafe($election['description']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <td>
                                <span class="badge bg-<?= F::e(F::electionStatusBadgeClass($election['status'] ?? null)) ?>">
                                    <?= F::e(strtoupper((string) ($election['status'] ?? '-'))) ?>
                                </span>
                            </td>

                            <td><?= F::e($election['total_approved'] ?? 0) ?></td>
                            <td><?= F::e($election['total_tokens'] ?? 0) ?></td>
                            <td><?= F::e($election['total_active'] ?? 0) ?></td>
                            <td><?= F::e($election['total_used'] ?? 0) ?></td>
                            <td><?= F::e($election['total_revoked'] ?? 0) ?></td>
                            <td><?= F::e($election['total_expired'] ?? 0) ?></td>

                            <td>
                                <a
                                    href="<?= F::e($appUrl) ?>/elections/<?= F::e($election['id']) ?>/remote-tokens"
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