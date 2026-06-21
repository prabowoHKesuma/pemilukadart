<?php

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Env;

?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-0">Data Pemilihan</h1>
        <div class="text-muted small">
            Kelola sesi pemilihan RT.
        </div>
    </div>

    <?php if (in_array(Auth::role(), ['superadmin', 'panitia'], true)): ?>
        <a href="<?= htmlspecialchars(Env::get('APP_URL')) ?>/elections/create" class="btn btn-primary">
            + Tambah Pemilihan
        </a>
    <?php endif; ?>
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
                        <th>Wilayah</th>
                        <th>Status</th>
                        <th>Mulai</th>
                        <th>Selesai</th>
                        <th>Dibuat Oleh</th>
                        <th style="width: 280px;">Aksi</th>
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
                                <?php if (!empty($election['region_name'])): ?>
                                    <code><?= htmlspecialchars($election['region_code']) ?></code>
                                    -
                                    <?= htmlspecialchars($election['region_name']) ?>
                                    <div class="small text-muted">
                                        <?= htmlspecialchars($election['organization_name'] ?? '-') ?>
                                        /
                                        <?= htmlspecialchars(strtoupper($election['region_level'])) ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">Belum diset</span>
                                <?php endif; ?>
                            </td>
                            
                            <td>
                                <?php
                                    $badgeClass = match ($election['status']) {
                                        'draft' => 'secondary',
                                        'open' => 'success',
                                        'closed' => 'warning',
                                        'finished' => 'dark',
                                        default => 'secondary',
                                    };
                                ?>

                                <span class="badge bg-<?= $badgeClass ?>">
                                    <?= htmlspecialchars(strtoupper($election['status'])) ?>
                                </span>
                            </td>
                            <td>
                                <?= $election['start_at'] ? date('d/m/Y H:i', strtotime($election['start_at'])) : '-' ?>
                            </td>
                            <td>
                                <?= $election['end_at'] ? date('d/m/Y H:i', strtotime($election['end_at'])) : '-' ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($election['created_by_name'] ?? '-') ?>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    <?php if (in_array(Auth::role(), ['superadmin', 'panitia'], true)): ?>
                                        <a 
                                            href="<?= htmlspecialchars(Env::get('APP_URL')) ?>/elections/<?= $election['id'] ?>/candidates" 
                                            class="btn btn-sm btn-info text-white"
                                        >
                                            Kandidat
                                        </a>
                                        <a 
                                            href="<?= htmlspecialchars(Env::get('APP_URL')) ?>/elections/<?= $election['id'] ?>/voters" 
                                            class="btn btn-sm btn-secondary"
                                        >
                                            Pemilih
                                        </a>
                                        <a 
                                            href="<?= htmlspecialchars(Env::get('APP_URL')) ?>/elections/<?= $election['id'] ?>/remote-verifications" 
                                            class="btn btn-sm btn-outline-primary"
                                        >
                                            Remote
                                        </a>
                                        <a 
                                            href="<?= htmlspecialchars(Env::get('APP_URL')) ?>/elections/<?= $election['id'] ?>/remote-tokens" 
                                            class="btn btn-sm btn-outline-success"
                                        >
                                            Token Remote
                                        </a>
                                        <?php if ($election['status'] === 'open'): ?>
                                            <a 
                                                href="<?= htmlspecialchars(Env::get('APP_URL')) ?>/elections/<?= $election['id'] ?>/tps-voting" 
                                                class="btn btn-sm btn-success"
                                            >
                                                Voting TPS
                                            </a>
                                        <?php endif; ?>
                                        <a 
                                            href="<?= htmlspecialchars(Env::get('APP_URL')) ?>/elections/<?= $election['id'] ?>/results" 
                                            class="btn btn-sm btn-dark"
                                        >
                                            Hasil
                                        </a>
                                        <a 
                                            href="<?= htmlspecialchars(Env::get('APP_URL')) ?>/elections/<?= $election['id'] ?>/edit" 
                                            class="btn btn-sm btn-warning"
                                        >
                                            Edit
                                        </a>

                                        <form 
                                            method="post" 
                                            action="<?= htmlspecialchars(Env::get('APP_URL')) ?>/elections/<?= $election['id'] ?>/status"
                                            class="d-inline"
                                        >
                                            <?= Csrf::field() ?>

                                            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                                <option value="draft" <?= $election['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                                                <option value="open" <?= $election['status'] === 'open' ? 'selected' : '' ?>>Open</option>
                                                <option value="closed" <?= $election['status'] === 'closed' ? 'selected' : '' ?>>Closed</option>
                                                <option value="finished" <?= $election['status'] === 'finished' ? 'selected' : '' ?>>Finished</option>
                                            </select>
                                        </form>
                                    <?php endif; ?>

                                    <?php if (Auth::role() === 'superadmin' && $election['status'] === 'draft'): ?>
                                        <form 
                                            method="post" 
                                            action="<?= htmlspecialchars(Env::get('APP_URL')) ?>/elections/<?= $election['id'] ?>/delete"
                                            onsubmit="return confirm('Yakin hapus pemilihan ini? Data kandidat, pemilih terkait, dan ballot bisa ikut terpengaruh kalau sudah ada relasi.');"
                                        >
                                            <?= Csrf::field() ?>

                                            <button type="submit" class="btn btn-sm btn-danger">
                                                Hapus
                                            </button>
                                        </form>
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