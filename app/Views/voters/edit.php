<?php

use App\Core\Csrf;
use App\Core\Env;

$appUrl = rtrim(Env::get('APP_URL'), '/');

?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-0">Edit Pemilih</h1>
        <div class="text-muted small">
            Perbarui data warga pemilih.
        </div>
    </div>

    <a href="<?= htmlspecialchars($appUrl) ?>/voters" class="btn btn-secondary">
        Kembali
    </a>
</div>

<div class="alert alert-warning">
    NIK dan KK tidak ditampilkan ulang karena sistem tidak menyimpan angka aslinya. Isi ulang hanya jika ingin mengganti data NIK/KK.
</div>

<div class="card">
    <div class="card-body">
        <form method="post" action="<?= htmlspecialchars($appUrl) ?>/voters/<?= $voter['id'] ?>/update">
            <?= Csrf::field() ?>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Kode Pemilih <span class="text-danger">*</span></label>
                    <input 
                        type="text" 
                        name="voter_code" 
                        class="form-control" 
                        value="<?= htmlspecialchars($voter['voter_code']) ?>"
                        required
                    >
                </div>

                <div class="col-md-8 mb-3">
                    <label class="form-label">Nama Pemilih <span class="text-danger">*</span></label>
                    <input 
                        type="text" 
                        name="name" 
                        class="form-control" 
                        value="<?= htmlspecialchars($voter['name']) ?>"
                        required
                        autofocus
                    >
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">NIK Baru</label>
                    <input 
                        type="text" 
                        name="nik" 
                        class="form-control" 
                        maxlength="20"
                        placeholder="<?= !empty($voter['nik_hash']) ? 'Sudah tersimpan. Isi hanya jika ingin mengganti.' : 'Belum ada NIK. Isi 16 digit.' ?>"
                    >
                    <div class="form-text">
                        Status: <?= !empty($voter['nik_hash']) ? 'NIK sudah tersimpan sebagai hash.' : 'NIK belum tersimpan.' ?>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Nomor KK Baru</label>
                    <input 
                        type="text" 
                        name="kk" 
                        class="form-control" 
                        maxlength="20"
                        placeholder="<?= !empty($voter['kk_hash']) ? 'Sudah tersimpan. Isi hanya jika ingin mengganti.' : 'Belum ada KK. Isi 16 digit.' ?>"
                    >
                    <div class="form-text">
                        Status: <?= !empty($voter['kk_hash']) ? 'KK sudah tersimpan sebagai hash.' : 'KK belum tersimpan.' ?>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Alamat</label>
                <textarea 
                    name="address" 
                    class="form-control" 
                    rows="3"
                ><?= htmlspecialchars($voter['address'] ?? '') ?></textarea>
            </div>

            <div class="row">
                <div class="col-md-2 mb-3">
                    <label class="form-label">RT</label>
                    <input 
                        type="text" 
                        name="rt" 
                        class="form-control" 
                        value="<?= htmlspecialchars($voter['rt'] ?? '') ?>"
                    >
                </div>

                <div class="col-md-2 mb-3">
                    <label class="form-label">RW</label>
                    <input 
                        type="text" 
                        name="rw" 
                        class="form-control" 
                        value="<?= htmlspecialchars($voter['rw'] ?? '') ?>"
                    >
                </div>

                <div class="col-md-8 mb-3">
                    <label class="form-label">Nomor HP / WhatsApp</label>
                    <input 
                        type="text" 
                        name="phone" 
                        class="form-control" 
                        value="<?= htmlspecialchars($voter['phone'] ?? '') ?>"
                    >
                </div>
            </div>

            <div class="form-check mb-3">
                <input 
                    class="form-check-input" 
                    type="checkbox" 
                    name="is_active" 
                    id="is_active"
                    <?= (int) $voter['is_active'] === 1 ? 'checked' : '' ?>
                >
                <label class="form-check-label" for="is_active">
                    Pemilih aktif
                </label>
            </div>

            <hr>

            <button type="submit" class="btn btn-primary">
                Update
            </button>

            <a href="<?= htmlspecialchars($appUrl) ?>/voters" class="btn btn-light">
                Batal
            </a>
        </form>
    </div>
</div>