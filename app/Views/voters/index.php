<?php

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Env;

$appUrl = rtrim(Env::get('APP_URL'), '/');

?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-0">Data Pemilih</h1>
        <div class="text-muted small">
            Master data warga yang memiliki hak pilih.
        </div>
    </div>

    <?php if (in_array(Auth::role(), ['superadmin', 'panitia'], true)): ?>
        <a href="<?= htmlspecialchars($appUrl) ?>/voters/create" class="btn btn-primary">
            + Tambah Pemilih
        </a>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($voters)): ?>
            <div class="alert alert-info mb-0">
                Belum ada data pemilih.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Alamat</th>
                        <th>RT/RW</th>
                        <th>No HP</th>
                        <th>NIK</th>
                        <th>KK</th>
                        <th>Status</th>
                        <th style="width: 150px;">Aksi</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($voters as $index => $voter): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>

                            <td>
                                <strong><?= htmlspecialchars($voter['voter_code']) ?></strong>
                            </td>

                            <td>
                                <?= htmlspecialchars($voter['name']) ?>
                            </td>

                            <td>
                                <?= !empty($voter['address']) ? nl2br(htmlspecialchars($voter['address'])) : '-' ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(($voter['rt'] ?: '-') . ' / ' . ($voter['rw'] ?: '-')) ?>
                            </td>

                            <td>
                                <?= !empty($voter['phone']) ? htmlspecialchars($voter['phone']) : '-' ?>
                            </td>

                            <td>
                                <?php if (!empty($voter['nik_hash'])): ?>
                                    <span class="badge bg-success">Tersimpan</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Kosong</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php if (!empty($voter['kk_hash'])): ?>
                                    <span class="badge bg-success">Tersimpan</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Kosong</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php if ((int) $voter['is_active'] === 1): ?>
                                    <span class="badge bg-success">Aktif</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Nonaktif</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php if (in_array(Auth::role(), ['superadmin', 'panitia'], true)): ?>
                                    <div class="d-flex gap-1">
                                        <a 
                                            href="<?= htmlspecialchars($appUrl) ?>/voters/<?= $voter['id'] ?>/edit" 
                                            class="btn btn-sm btn-warning"
                                        >
                                            Edit
                                        </a>

                                        <?php if (Auth::role() === 'superadmin'): ?>
                                            <form 
                                                method="post" 
                                                action="<?= htmlspecialchars($appUrl) ?>/voters/<?= $voter['id'] ?>/delete"
                                                onsubmit="return confirm('Yakin hapus pemilih ini? Jika sudah pernah masuk daftar pemilihan, sistem akan menolak.');"
                                            >
                                                <?= Csrf::field() ?>

                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    Hapus
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted small">Read only</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>