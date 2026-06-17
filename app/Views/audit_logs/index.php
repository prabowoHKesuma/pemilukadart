<?php

use App\Core\Env;

$appUrl = rtrim(Env::get('APP_URL'), '/');

function shortUserAgent(?string $userAgent): string
{
    if (!$userAgent) {
        return '-';
    }

    return strlen($userAgent) > 80
        ? substr($userAgent, 0, 80) . '...'
        : $userAgent;
}

function actionBadgeClass(string $action): string
{
    if (str_contains($action, 'login')) {
        return 'primary';
    }

    if (str_contains($action, 'create') || str_contains($action, 'store')) {
        return 'success';
    }

    if (str_contains($action, 'update') || str_contains($action, 'status')) {
        return 'warning';
    }

    if (str_contains($action, 'delete')) {
        return 'danger';
    }

    if (str_contains($action, 'vote')) {
        return 'dark';
    }

    if (str_contains($action, 'remote')) {
        return 'info';
    }

    return 'secondary';
}

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
        <form method="get" action="<?= htmlspecialchars($appUrl) ?>/audit-logs">
            <div class="row">
                <div class="col-md-4 mb-2">
                    <label class="form-label">Keyword</label>
                    <input 
                        type="text" 
                        name="keyword" 
                        class="form-control"
                        value="<?= htmlspecialchars($filters['keyword'] ?? '') ?>"
                        placeholder="Cari action, deskripsi, user..."
                    >
                </div>

                <div class="col-md-3 mb-2">
                    <label class="form-label">Action</label>
                    <select name="action" class="form-select">
                        <option value="">Semua Action</option>

                        <?php foreach ($actions as $action): ?>
                            <option 
                                value="<?= htmlspecialchars($action) ?>"
                                <?= ($filters['action'] ?? '') === $action ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($action) ?>
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
                        value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>"
                    >
                </div>

                <div class="col-md-2 mb-2">
                    <label class="form-label">Sampai Tanggal</label>
                    <input 
                        type="date" 
                        name="date_to" 
                        class="form-control"
                        value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>"
                    >
                </div>

                <div class="col-md-1 mb-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        Filter
                    </button>
                </div>
            </div>

            <div class="mt-2">
                <a href="<?= htmlspecialchars($appUrl) ?>/audit-logs" class="btn btn-sm btn-light">
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
                                <?= $log['created_at'] ? date('d/m/Y H:i:s', strtotime($log['created_at'])) : '-' ?>
                            </td>

                            <td>
                                <?php if (!empty($log['user_name'])): ?>
                                    <strong><?= htmlspecialchars($log['user_name']) ?></strong>
                                    <div class="small text-muted">
                                        <?= htmlspecialchars($log['username'] ?? '-') ?>
                                        <?php if (!empty($log['user_role'])): ?>
                                            / <?= htmlspecialchars($log['user_role']) ?>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">System / Guest</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <span class="badge bg-<?= actionBadgeClass($log['action']) ?>">
                                    <?= htmlspecialchars($log['action']) ?>
                                </span>
                            </td>

                            <td>
                                <?= !empty($log['description']) ? nl2br(htmlspecialchars($log['description'])) : '-' ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($log['ip_address'] ?? '-') ?>
                            </td>

                            <td class="small">
                                <?= htmlspecialchars(shortUserAgent($log['user_agent'] ?? null)) ?>
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