<?php

use App\Core\Csrf;
use App\Core\Env;

$appUrl = rtrim(Env::get('APP_URL'), '/');

?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-0">Role Management</h1>
        <div class="text-muted small">
            Kelola role dan permission sistem.
        </div>
    </div>

    <a href="<?= htmlspecialchars($appUrl) ?>/roles/create" class="btn btn-primary">
        + Tambah Role
    </a>
</div>

<div class="alert alert-warning">
    Perubahan permission baru aktif untuk user setelah user tersebut logout dan login ulang.
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($roles)): ?>
            <div class="alert alert-info mb-0">
                Belum ada data role.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Role</th>
                        <th>Label</th>
                        <th>Deskripsi</th>
                        <th>System</th>
                        <th>Total User</th>
                        <th>Total Permission</th>
                        <th style="width: 170px;">Aksi</th>
                    </tr>
                    </thead>

                    <tbody>
                    <?php foreach ($roles as $index => $role): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>

                            <td>
                                <code><?= htmlspecialchars($role['name']) ?></code>
                            </td>

                            <td>
                                <strong><?= htmlspecialchars($role['label']) ?></strong>
                            </td>

                            <td>
                                <?= !empty($role['description']) ? nl2br(htmlspecialchars($role['description'])) : '-' ?>
                            </td>

                            <td>
                                <?php if ((int) $role['is_system'] === 1): ?>
                                    <span class="badge bg-dark">System</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Custom</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?= htmlspecialchars((string) $role['total_users']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars((string) $role['total_permissions']) ?>
                            </td>

                            <td>
                                <div class="d-flex gap-1">
                                    <a 
                                        href="<?= htmlspecialchars($appUrl) ?>/roles/<?= $role['id'] ?>/edit" 
                                        class="btn btn-sm btn-warning"
                                    >
                                        Edit
                                    </a>

                                    <?php if ((int) $role['is_system'] !== 1 && (int) $role['total_users'] === 0): ?>
                                        <form 
                                            method="post" 
                                            action="<?= htmlspecialchars($appUrl) ?>/roles/<?= $role['id'] ?>/delete"
                                            onsubmit="return confirm('Yakin hapus role ini?');"
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