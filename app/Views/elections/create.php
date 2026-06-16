<?php

use App\Core\Csrf;
use App\Core\Env;

?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-0">Tambah Pemilihan</h1>
        <div class="text-muted small">
            Buat sesi pemilihan baru.
        </div>
    </div>

    <a href="<?= htmlspecialchars(Env::get('APP_URL')) ?>/elections" class="btn btn-secondary">
        Kembali
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form method="post" action="<?= htmlspecialchars(Env::get('APP_URL')) ?>/elections/store">
            <?= Csrf::field() ?>

            <div class="mb-3">
                <label class="form-label">Nama Pemilihan <span class="text-danger">*</span></label>
                <input 
                    type="text" 
                    name="title" 
                    class="form-control" 
                    placeholder="Contoh: Pemilihan Ketua RT 05 Tahun 2026"
                    required
                    autofocus
                >
            </div>

            <div class="mb-3">
                <label class="form-label">Deskripsi</label>
                <textarea 
                    name="description" 
                    class="form-control" 
                    rows="3"
                    placeholder="Contoh: Pemilihan Ketua RT periode 2026-2029"
                ></textarea>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="draft" selected>Draft</option>
                        <option value="open">Open</option>
                        <option value="closed">Closed</option>
                        <option value="finished">Finished</option>
                    </select>
                    <div class="form-text">
                        Saran: gunakan draft dulu saat input data.
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="datetime-local" name="start_at" class="form-control">
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Tanggal Selesai</label>
                    <input type="datetime-local" name="end_at" class="form-control">
                </div>
            </div>

            <hr>

            <button type="submit" class="btn btn-primary">
                Simpan
            </button>

            <a href="<?= htmlspecialchars(Env::get('APP_URL')) ?>/elections" class="btn btn-light">
                Batal
            </a>
        </form>
    </div>
</div>