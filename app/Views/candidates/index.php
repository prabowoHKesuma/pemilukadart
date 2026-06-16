<?php

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Env;

$appUrl = rtrim(Env::get('APP_URL'), '/');

?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-0">Data Kandidat</h1>
        <div class="text-muted small">
            Pemilihan: <strong><?= htmlspecialchars($election['title']) ?></strong>
        </div>
    </div>

    <div class="d-flex gap-2">
        <a href="<?= htmlspecialchars($appUrl) ?>/elections" class="btn btn-secondary">
            Kembali
        </a>

        <?php if (in_array(Auth::role(), ['superadmin', 'panitia'], true) && $election['status'] === 'draft'): ?>
            <a href="<?= htmlspecialchars($appUrl) ?>/elections/<?= $election['id'] ?>/candidates/create" class="btn btn-primary">
                + Tambah Kandidat
            </a>
        <?php endif; ?>
    </div>
</div>

<?php if ($election['status'] !== 'draft'): ?>
    <div class="alert alert-warning">
        Pemilihan sudah berstatus <strong><?= htmlspecialchars(strtoupper($election['status'])) ?></strong>.
        Data kandidat dikunci dan tidak boleh diubah.
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <?php if (empty($candidates)): ?>
            <div class="alert alert-info mb-0">
                Belum ada data kandidat untuk pemilihan ini.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead>
                    <tr>
                        <th style="width: 70px;">No Urut</th>
                        <th style="width: 110px;">Foto</th>
                        <th>Nama Kandidat</th>
                        <th>Visi</th>
                        <th>Misi</th>
                        <th>Status</th>
                        <th style="width: 170px;">Aksi</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($candidates as $candidate): ?>
                        <tr>
                            <td class="text-center">
                                <span class="badge bg-dark fs-6">
                                    <?= htmlspecialchars((string) $candidate['number_order']) ?>
                                </span>
                            </td>

                            <td>
                                <?php if (!empty($candidate['photo'])): ?>
                                    <img 
                                        src="<?= htmlspecialchars($appUrl . '/' . $candidate['photo']) ?>" 
                                        alt="Foto kandidat"
                                        style="width: 80px; height: 80px; object-fit: cover;"
                                        class="rounded border"
                                    >
                                <?php else: ?>
                                    <div 
                                        class="bg-light border rounded d-flex align-items-center justify-content-center text-muted small"
                                        style="width: 80px; height: 80px;"
                                    >
                                        No Foto
                                    </div>
                                <?php endif; ?>
                            </td>

                            <td>
                                <strong><?= htmlspecialchars($candidate['name']) ?></strong>
                            </td>

                            <td>
                                <?= !empty($candidate['vision']) ? nl2br(htmlspecialchars($candidate['vision'])) : '-' ?>
                            </td>

                            <td>
                                <?= !empty($candidate['mission']) ? nl2br(htmlspecialchars($candidate['mission'])) : '-' ?>
                            </td>

                            <td>
                                <?php if ((int) $candidate['is_active'] === 1): ?>
                                    <span class="badge bg-success">Aktif</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Nonaktif</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php if (in_array(Auth::role(), ['superadmin', 'panitia'], true) && $election['status'] === 'draft'): ?>
                                    <div class="d-flex gap-1">
                                        <a 
                                            href="<?= htmlspecialchars($appUrl) ?>/elections/<?= $election['id'] ?>/candidates/<?= $candidate['id'] ?>/edit" 
                                            class="btn btn-sm btn-warning"
                                        >
                                            Edit
                                        </a>

                                        <?php if (Auth::role() === 'superadmin'): ?>
                                            <form 
                                                method="post" 
                                                action="<?= htmlspecialchars($appUrl) ?>/elections/<?= $election['id'] ?>/candidates/<?= $candidate['id'] ?>/delete"
                                                onsubmit="return confirm('Yakin hapus kandidat ini?');"
                                            >
                                                <?= Csrf::field() ?>

                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    Hapus
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted small">Terkunci</span>
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