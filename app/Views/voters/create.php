<?php

use App\Core\Csrf;
use App\Core\Env;

$appUrl = rtrim(Env::get('APP_URL'), '/');

?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-0">Tambah Pemilih</h1>
        <div class="text-muted small">
            Tambahkan data warga yang memiliki hak pilih.
        </div>
    </div>

    <a href="<?= htmlspecialchars($appUrl) ?>/voters" class="btn btn-secondary">
        Kembali
    </a>
</div>

<div class="alert alert-warning">
    NIK dan KK tidak akan disimpan dalam bentuk angka asli. Sistem hanya menyimpan hash untuk validasi duplikasi.
</div>

<div class="card">
    <div class="card-body">
        <form method="post" action="<?= htmlspecialchars($appUrl) ?>/voters/store">
            <?= Csrf::field() ?>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Kode Pemilih</label>
                    <input 
                        type="text" 
                        name="voter_code" 
                        class="form-control" 
                        placeholder="Kosongkan untuk otomatis"
                    >
                    <div class="form-text">
                        Contoh: PM-260616-A1B2C3. Bisa dikosongkan.
                    </div>
                </div>

                <div class="col-md-8 mb-3">
                    <label class="form-label">Nama Pemilih <span class="text-danger">*</span></label>
                    <input 
                        type="text" 
                        name="name" 
                        class="form-control" 
                        required
                        autofocus
                        placeholder="Nama lengkap warga"
                    >
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">NIK</label>
                    <input 
                        type="text" 
                        name="nik" 
                        class="form-control" 
                        maxlength="20"
                        placeholder="16 digit NIK"
                    >
                    <div class="form-text">
                        Opsional, tapi disarankan untuk mencegah data ganda.
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Nomor KK</label>
                    <input 
                        type="text" 
                        name="kk" 
                        class="form-control" 
                        maxlength="20"
                        placeholder="16 digit nomor KK"
                    >
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Alamat</label>
                <textarea 
                    name="address" 
                    class="form-control" 
                    rows="3"
                    placeholder="Alamat domisili di lingkungan RT"
                ></textarea>
            </div>

            <div class="row">
                <div class="col-md-2 mb-3">
                    <label class="form-label">RT</label>
                    <input 
                        type="text" 
                        name="rt" 
                        class="form-control" 
                        placeholder="011"
                    >
                </div>

                <div class="col-md-2 mb-3">
                    <label class="form-label">RW</label>
                    <input 
                        type="text" 
                        name="rw" 
                        class="form-control" 
                        placeholder="001"
                    >
                </div>

                <div class="col-md-8 mb-3">
                    <label class="form-label">Nomor HP / WhatsApp</label>
                    <input 
                        type="text" 
                        name="phone" 
                        class="form-control" 
                        placeholder="08xxxxxxxxxx"
                    >
                </div>
            </div>

            <div class="form-check mb-3">
                <input 
                    class="form-check-input" 
                    type="checkbox" 
                    name="is_active" 
                    id="is_active"
                    checked
                >
                <label class="form-check-label" for="is_active">
                    Pemilih aktif
                </label>
            </div>

            <hr>

            <button type="submit" class="btn btn-primary">
                Simpan
            </button>

            <a href="<?= htmlspecialchars($appUrl) ?>/voters" class="btn btn-light">
                Batal
            </a>
        </form>
    </div>
</div>