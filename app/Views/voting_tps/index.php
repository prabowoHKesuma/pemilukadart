<?php

use App\Core\Env;

$appUrl = rtrim(Env::get('APP_URL'), '/');

?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-0">Voting TPS</h1>
        <div class="text-muted small">
            Pilih pemilihan yang sedang dibuka.
        </div>
    </div>
</div>

<div class="alert alert-warning">
    Voting TPS hanya menampilkan pemilihan dengan status <strong>OPEN</strong>.
    Pastikan kandidat dan daftar pemilih sudah benar sebelum membuka pemilihan.
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($elections)): ?>
            <div class="alert alert-info mb-0">
                Belum ada pemilihan yang berstatus OPEN.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Nama Pemilihan</th>
                        <th>Waktu</th>
                        <th>Kandidat Aktif</th>
                        <th>Total Pemilih</th>
                        <th>Sudah Coblos</th>
                        <th style="width: 150px;">Aksi</th>
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
                                <div>
                                    Mulai:
                                    <?= $election['start_at'] ? date('d/m/Y H:i', strtotime($election['start_at'])) : '-' ?>
                                </div>
                                <div>
                                    Selesai:
                                    <?= $election['end_at'] ? date('d/m/Y H:i', strtotime($election['end_at'])) : '-' ?>
                                </div>
                            </td>

                            <td>
                                <?= htmlspecialchars((string) $election['total_candidates']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars((string) $election['total_voters']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars((string) ($election['total_voted'] ?? 0)) ?>
                            </td>

                            <td>
                                <a 
                                    href="<?= htmlspecialchars($appUrl) ?>/elections/<?= $election['id'] ?>/tps-voting" 
                                    class="btn btn-primary btn-sm"
                                >
                                    Buka TPS
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