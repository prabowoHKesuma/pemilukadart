<?php

use App\Core\ViewFormatter as F;

?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-0">Audit Log</h1>
        <div class="text-muted small">
            Catatan aktivitas penting di sistem.
        </div>
    </div>
</div>

<div class="alert alert-info">
    Audit log bersifat read-only. Tidak ada fitur edit atau hapus log.
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="get" action="<?= F::e($appUrl) ?>/audit-logs">
            <div class="row">
                <div class="col-md-4 mb-2">
                    <label class="form-label">Keyword</label>

                    <input
                        type="text"
                        name="keyword"
                        class="form-control"
                        value="<?= F::e($filters['keyword'] ?? '') ?>"
                        placeholder="Cari action, deskripsi, user..."
                    >
                </div>

                <div class="col-md-3 mb-2">
                    <label class="form-label">Action</label>

                    <select name="action" class="form-select">
                        <option value="">Semua Action</option>

                        <?php foreach ($actions as $action): ?>
                            <option
                                value="<?= F::e($action) ?>"
                                <?= ($filters['action'] ?? '') === $action ? 'selected' : '' ?>
                            >
                                <?= F::e($action) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2 mb-2">
                    <label class="form-label">Dari Tanggal</label>

                    <input
                        type="date"
                        name="date_from"
                        class="form-control"
                        value="<?= F::e($filters['date_from'] ?? '') ?>"
                    >
                </div>

                <div class="col-md-2 mb-2">
                    <label class="form-label">Sampai Tanggal</label>

                    <input
                        type="date"
                        name="date_to"
                        class="form-control"
                        value="<?= F::e($filters['date_to'] ?? '') ?>"
                    >
                </div>

                <div class="col-md-1 mb-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        Filter
                    </button>
                </div>
            </div>

            <div class="mt-2">
                <a href="<?= F::e($appUrl) ?>/audit-logs" class="btn btn-sm btn-light">
                    Reset Filter
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($logs)): ?>
            <div class="alert alert-warning mb-0">
                Belum ada audit log.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th style="width: 150px;">Waktu</th>
                        <th style="width: 190px;">User</th>
                        <th style="width: 170px;">Action</th>
                        <th>Deskripsi</th>
                        <th style="width: 130px;">IP</th>
                        <th>User Agent</th>
                    </tr>
                    </thead>

                    <tbody>
                    <?php foreach ($logs as $index => $log): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>

                            <td>
                                <?= F::dateTimeSecond($log['created_at'] ?? null) ?>
                            </td>

                            <td>
                                <?php if (!empty($log['user_name'])): ?>
                                    <strong><?= F::e($log['user_name']) ?></strong>

                                    <div class="small text-muted">
                                        <?= F::dash($log['username'] ?? null) ?>

                                        <?php if (!empty($log['user_role'])): ?>
                                            / <?= F::e($log['user_role']) ?>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">System / Guest</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <span class="badge bg-<?= F::e(F::actionBadgeClass($log['action'] ?? null)) ?>">
                                    <?= F::dash($log['action'] ?? null) ?>
                                </span>
                            </td>

                            <td>
                                <?= F::nl2brSafe($log['description'] ?? null) ?>
                            </td>

                            <td>
                                <?= F::dash($log['ip_address'] ?? null) ?>
                            </td>

                            <td class="small">
                                <?= F::e(F::shortText($log['user_agent'] ?? null, 80)) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="text-muted small mt-2">
                Maksimal menampilkan 500 log terbaru sesuai filter.
            </div>
        <?php endif; ?>
    </div>
</div>