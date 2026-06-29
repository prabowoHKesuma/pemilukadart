<?php

use App\Core\Csrf;
use App\Core\Env;

$appUrl = rtrim(Env::get('APP_URL'), '/');

?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-0">Tambah Pemilih ke Pemilihan</h1>
        <div class="text-muted small">
            Pemilihan: <strong><?= htmlspecialchars($election['title']) ?></strong>
        </div>
    </div>

    <a href="<?= htmlspecialchars($appUrl) ?>/elections/<?= $election['id'] ?>/voters" class="btn btn-secondary">
        Kembali
    </a>
</div>

<div class="alert alert-info">
    Hanya pemilih aktif dari master data yang belum masuk ke pemilihan ini yang ditampilkan.
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($voters)): ?>
            <div class="alert alert-warning mb-0">
                Tidak ada pemilih tersedia. Semua pemilih aktif sudah masuk ke pemilihan ini, atau master data pemilih masih kosong.
            </div>
        <?php else: ?>
            <form method="post" action="<?= htmlspecialchars($appUrl) ?>/elections/<?= $election['id'] ?>/voters/store">
                <?= Csrf::field() ?>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Channel untuk pemilih yang dipilih</label>
                        <select name="allowed_channel" class="form-select">
                            <option value="tps" selected>TPS</option>
                            <option value="remote">Remote</option>
                            <option value="both">TPS / Remote</option>
                        </select>
                        <div class="form-text">
                            Umumnya pakai TPS. Remote hanya untuk warga luar kota.
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="checkAllVoters(true)">
                        Pilih Semua
                    </button>

                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="checkAllVoters(false)">
                        Hapus Pilihan
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead>
                        <tr>
                            <th style="width: 50px;">Pilih</th>
                            <th>Kode</th>
                            <th>Nama Pemilih</th>
                            <th>Alamat</th>
                            <th>RT/RW</th>
                            <th>No HP</th>
                        </tr>
                        </thead>

                        <tbody>
                        <?php foreach ($voters as $voter): ?>
                            <tr>
                                <td class="text-center">
                                    <input 
                                        type="checkbox" 
                                        name="voter_ids[]" 
                                        value="<?= htmlspecialchars((string) $voter['id']) ?>"
                                        class="form-check-input voter-checkbox"
                                    >
                                </td>

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
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <hr>

                <button type="submit" class="btn btn-primary">
                    Tambahkan ke Pemilihan
                </button>

                <a href="<?= htmlspecialchars($appUrl) ?>/elections/<?= $election['id'] ?>/voters" class="btn btn-light">
                    Batal
                </a>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
function checkAllVoters(checked) {
    const checkboxes = document.querySelectorAll('.voter-checkbox');

    checkboxes.forEach(function (checkbox) {
        checkbox.checked = checked;
    });
}
</script>