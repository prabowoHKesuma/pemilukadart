<?php

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Env;

$appUrl = rtrim(Env::get('APP_URL'), '/');

?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-0">User Management</h1>
        <div class="text-muted small">
            Kelola akun operator, panitia, saksi, auditor, dan admin.
        </div>
    </div>

    <a href="<?= htmlspecialchars($appUrl) ?>/users/create" class="btn btn-primary">
        + Tambah User
    </a>
</div>

<div class="alert alert-warning">
    Jika role atau permission user diubah, user tersebut perlu logout dan login ulang agar session permission ter-refresh.
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($users)): ?>
            <div class="alert alert-info mb-0">
                Belum ada data user.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th style="width: 170px;">Aksi</th>
                    </tr>
                    </thead>

                    <tbody>
                    <?php foreach ($users as $index => $item): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>

                            <td>
                                <strong><?= htmlspecialchars($item['name']) ?></strong>

                                <?php if ((int) $item['id'] === (int) Auth::id()): ?>
                                    <span class="badge bg-info">Anda</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <code><?= htmlspecialchars($item['username']) ?></code>
                            </td>

                            <td>
                                <strong><?= htmlspecialchars($item['role_label'] ?? $item['role'] ?? '-') ?></strong>
                                <div class="small text-muted">
                                    <?= htmlspecialchars($item['role_name'] ?? $item['role'] ?? '-') ?>
                                </div>
                            </td>

                            <td>
                                <?php if ((int) $item['is_active'] === 1): ?>
                                    <span class="badge bg-success">Aktif</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Nonaktif</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?= !empty($item['last_login_at']) ? date('d/m/Y H:i', strtotime($item['last_login_at'])) : '-' ?>
                            </td>

                            <td>
                                <div class="d-flex gap-1">
                                    <a 
                                        href="<?= htmlspecialchars($appUrl) ?>/users/<?= $item['id'] ?>/edit" 
                                        class="btn btn-sm btn-warning"
                                    >
                                        Edit
                                    </a>

                                    <?php if ((int) $item['id'] !== (int) Auth::id()): ?>
                                        <form 
                                            method="post" 
                                            action="<?= htmlspecialchars($appUrl) ?>/users/<?= $item['id'] ?>/delete"
                                            onsubmit="return confirm('Yakin hapus user ini? Untuk keamanan, lebih baik nonaktifkan jika masih perlu histori audit.');"
                                        >
                                            <?= Csrf::field() ?>

                                            <button type="submit" class="btn btn-sm btn-danger">
                                                Hapus
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted small align-self-center">-</span>
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