<?php

use App\Core\Csrf;
use App\Core\Env;

$appUrl = rtrim(Env::get('APP_URL'), '/');

function regionLevelLabel(string $level): string
{
    return match ($level) {
        'kota' => 'Kota / Kabupaten',
        'kecamatan' => 'Kecamatan',
        'kelurahan' => 'Kelurahan / Desa',
        'rw' => 'RW',
        'rt' => 'RT',
        'custom' => 'Custom',
        default => $level,
    };
}

?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-0">Region Management</h1>
        <div class="text-muted small">
            Kelola struktur wilayah: Kota, Kecamatan, Kelurahan, RW, dan RT.
        </div>
    </div>

    <a href="<?= htmlspecialchars($appUrl) ?>/regions/create" class="btn btn-primary">
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
                        <?php
                            $usedCount = (int) $region['total_users'] + (int) $region['total_voters'] + (int) $region['total_elections'];
                            $canDelete = (int) $region['total_children'] === 0 && $usedCount === 0;
                        ?>

                        <tr>
                            <td><?= $index + 1 ?></td>

                            <td>
                                <?= htmlspecialchars($region['organization_name']) ?>
                            </td>

                            <td>
                                <span class="badge bg-dark">
                                    <?= htmlspecialchars(regionLevelLabel($region['level'])) ?>
                                </span>
                            </td>

                            <td>
                                <code><?= htmlspecialchars($region['code']) ?></code>
                            </td>

                            <td>
                                <strong><?= htmlspecialchars($region['name']) ?></strong>
                            </td>

                            <td>
                                <?php if (!empty($region['parent_name'])): ?>
                                    <code><?= htmlspecialchars($region['parent_code']) ?></code>
                                    -
                                    <?= htmlspecialchars($region['parent_name']) ?>
                                <?php else: ?>
                                    <span class="text-muted">Root</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?= htmlspecialchars((string) $region['total_children']) ?>
                            </td>

                            <td>
                                <div class="small">
                                    User: <?= htmlspecialchars((string) $region['total_users']) ?><br>
                                    Pemilih: <?= htmlspecialchars((string) $region['total_voters']) ?><br>
                                    Pemilihan: <?= htmlspecialchars((string) $region['total_elections']) ?>
                                </div>
                            </td>

                            <td>
                                <div class="d-flex gap-1">
                                    <a 
                                        href="<?= htmlspecialchars($appUrl) ?>/regions/<?= $region['id'] ?>/edit" 
                                        class="btn btn-sm btn-warning"
                                    >
                                        Edit
                                    </a>

                                    <?php if ($canDelete): ?>
                                        <form 
                                            method="post" 
                                            action="<?= htmlspecialchars($appUrl) ?>/regions/<?= $region['id'] ?>/delete"
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