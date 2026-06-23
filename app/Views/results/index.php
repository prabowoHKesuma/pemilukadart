<?php

use App\Core\ViewFormatter as F;

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
                        <th style="width: 220px;">Aksi</th>
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

                                <?php if (!empty($election['ballot_mismatch'])): ?>
                                    <div class="small text-danger mt-1">
                                        Peringatan: jumlah sudah coblos tidak sama dengan suara masuk.
                                    </div>
                                <?php endif; ?>
                            </td>

                            <td>
                                <span class="badge bg-<?= F::e(F::electionStatusBadgeClass($election['status'] ?? null)) ?>">
                                    <?= F::e(strtoupper((string) ($election['status'] ?? '-'))) ?>
                                </span>
                            </td>

                            <td><?= F::e($election['total_candidates'] ?? 0) ?></td>
                            <td><?= F::e($election['total_voters'] ?? 0) ?></td>
                            <td><?= F::e($election['total_voted'] ?? 0) ?></td>
                            <td><?= F::e($election['total_not_voted'] ?? 0) ?></td>
                            <td><?= F::e($election['total_ballots'] ?? 0) ?></td>

                            <td>
                                <div><?= F::e($election['turnout_percent'] ?? 0) ?>%</div>

                                <div class="progress" style="height: 8px;">
                                    <div
                                        class="progress-bar"
                                        role="progressbar"
                                        style="width: <?= F::e($election['turnout_percent'] ?? 0) ?>%;"
                                        aria-valuenow="<?= F::e($election['turnout_percent'] ?? 0) ?>"
                                        aria-valuemin="0"
                                        aria-valuemax="100"
                                    ></div>
                                </div>
                            </td>

                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    <a
                                        href="<?= F::e($appUrl . $election['detail_url']) ?>"
                                        class="btn btn-sm btn-primary"
                                    >
                                        Detail
                                    </a>

                                    <?php if (!empty($canPrint)): ?>
                                        <a
                                            href="<?= F::e($appUrl . $election['print_url']) ?>"
                                            class="btn btn-sm btn-dark"
                                            target="_blank"
                                        >
                                            Cetak
                                        </a>

                                        <a
                                            href="<?= F::e($appUrl . $election['export_url']) ?>"
                                            class="btn btn-sm btn-success"
                                        >
                                            CSV
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>

                </table>
            </div>
        <?php endif; ?>
    </div>
</div>