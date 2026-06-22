<?php

use App\Core\Csrf;
use App\Core\ViewFormatter as F;

?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-0">Region Management</h1>
        <div class="text-muted small">
            Kelola struktur wilayah: Kota, Kecamatan, Kelurahan, RW, dan RT.
        </div>
    </div>

    <a href="<?= F::e($appUrl) ?>/regions/create" class="btn btn-primary">
        + Tambah Wilayah
    </a>
</div>

<div class="alert alert-info">
    Tahap ini baru membuat struktur wilayah. Filter visibilitas data per wilayah akan kita aktifkan di fase berikutnya.
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($regions)): ?>
            <div class="alert alert-info mb-0">
                Belum ada data wilayah.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Organization</th>
                        <th>Level</th>
                        <th>Kode</th>
                        <th>Nama Wilayah</th>
                        <th>Parent</th>
                        <th>Child</th>
                        <th>Dipakai</th>
                        <th style="width: 170px;">Aksi</th>
                    </tr>
                    </thead>

                    <tbody>
                    <?php foreach ($regions as $index => $region): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>

                            <td>
                                <?= F::dash($region['organization_name'] ?? null) ?>
                            </td>

                            <td>
                                <span class="badge bg-dark">
                                    <?= F::e(F::regionLevelLabel($region['level'] ?? null)) ?>
                                </span>
                            </td>

                            <td>
                                <code><?= F::dash($region['code'] ?? null) ?></code>
                            </td>

                            <td>
                                <strong><?= F::dash($region['name'] ?? null) ?></strong>
                            </td>

                            <td>
                                <?php if (!empty($region['parent_name'])): ?>
                                    <code><?= F::e($region['parent_code'] ?? '-') ?></code>
                                    -
                                    <?= F::e($region['parent_name']) ?>
                                <?php else: ?>
                                    <span class="text-muted">Root</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?= F::e($region['total_children'] ?? 0) ?>
                            </td>

                            <td>
                                <div class="small">
                                    User: <?= F::e($region['total_users'] ?? 0) ?><br>
                                    Pemilih: <?= F::e($region['total_voters'] ?? 0) ?><br>
                                    Pemilihan: <?= F::e($region['total_elections'] ?? 0) ?>
                                </div>
                            </td>

                            <td>
                                <div class="d-flex gap-1">
                                    <a
                                        href="<?= F::e($appUrl) ?>/regions/<?= F::e($region['id']) ?>/edit"
                                        class="btn btn-sm btn-warning"
                                    >
                                        Edit
                                    </a>

                                    <?php if (!empty($region['can_delete'])): ?>
                                        <form
                                            method="post"
                                            action="<?= F::e($appUrl) ?>/regions/<?= F::e($region['id']) ?>/delete"
                                            onsubmit="return confirm('Yakin hapus wilayah ini?');"
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