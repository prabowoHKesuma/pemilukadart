<?php

use App\Core\Csrf;
use App\Core\Env;

$appUrl = rtrim(Env::get('APP_URL'), '/');

?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-0">Menu Management</h1>
        <div class="text-muted small">
            Kelola menu sidebar, parent menu, permission, dan role menu.
        </div>
    </div>

    <a href="<?= htmlspecialchars($appUrl) ?>/menus/create" class="btn btn-primary">
        + Tambah Menu
    </a>
</div>

<div class="alert alert-warning">
    Menu hanya mengatur tampilan sidebar. Keamanan halaman tetap dikunci dari controller menggunakan permission.
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($menus)): ?>
            <div class="alert alert-info mb-0">
                Belum ada data menu.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Menu</th>
                        <th>Parent</th>
                        <th>URL</th>
                        <th>Permission</th>
                        <th>Target</th>
                        <th>Order</th>
                        <th>Status</th>
                        <th>Role</th>
                        <th>Child</th>
                        <th style="width: 170px;">Aksi</th>
                    </tr>
                    </thead>

                    <tbody>
                    <?php foreach ($menus as $index => $menu): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>

                            <td>
                                <strong><?= htmlspecialchars($menu['title']) ?></strong>
                                <div class="small text-muted">
                                    <code><?= htmlspecialchars($menu['menu_key']) ?></code>
                                </div>
                            </td>

                            <td>
                                <?php if (!empty($menu['parent_title'])): ?>
                                    <?= htmlspecialchars($menu['parent_title']) ?>
                                    <div class="small text-muted">
                                        <code><?= htmlspecialchars($menu['parent_key']) ?></code>
                                    </div>
                                <?php else: ?>
                                    <span class="badge bg-dark">Root</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?= $menu['url'] ? '<code>' . htmlspecialchars($menu['url']) . '</code>' : '<span class="text-muted">Parent / Group</span>' ?>
                            </td>

                            <td>
                                <?php if (!empty($menu['permission_name'])): ?>
                                    <code><?= htmlspecialchars($menu['permission_name']) ?></code>
                                    <?php if (!empty($menu['permission_label'])): ?>
                                        <div class="small text-muted">
                                            <?= htmlspecialchars($menu['permission_label']) ?>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($menu['target']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars((string) $menu['sort_order']) ?>
                            </td>

                            <td>
                                <?php if ((int) $menu['is_active'] === 1): ?>
                                    <span class="badge bg-success">Aktif</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Nonaktif</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?= htmlspecialchars((string) $menu['total_roles']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars((string) $menu['total_children']) ?>
                            </td>

                            <td>
                                <div class="d-flex gap-1">
                                    <a 
                                        href="<?= htmlspecialchars($appUrl) ?>/menus/<?= $menu['id'] ?>/edit" 
                                        class="btn btn-sm btn-warning"
                                    >
                                        Edit
                                    </a>

                                    <?php if ((int) $menu['total_children'] === 0): ?>
                                        <form 
                                            method="post" 
                                            action="<?= htmlspecialchars($appUrl) ?>/menus/<?= $menu['id'] ?>/delete"
                                            onsubmit="return confirm('Yakin hapus menu ini?');"
                                        >
                                            <?= Csrf::field() ?>

                                            <button type="submit" class="btn btn-sm btn-danger">
                                                Hapus
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted small align-self-center">Terkunci</span>
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