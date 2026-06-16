<?php

use App\Core\Csrf;
use App\Core\Env;

$appUrl = rtrim(Env::get('APP_URL'), '/');

?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-0">Tambah Kandidat</h1>
        <div class="text-muted small">
            Pemilihan: <strong><?= htmlspecialchars($election['title']) ?></strong>
        </div>
    </div>

    <a href="<?= htmlspecialchars($appUrl) ?>/elections/<?= $election['id'] ?>/candidates" class="btn btn-secondary">
        Kembali
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form 
            method="post" 
            action="<?= htmlspecialchars($appUrl) ?>/elections/<?= $election['id'] ?>/candidates/store"
            enctype="multipart/form-data"
        >
            <?= Csrf::field() ?>

            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Nomor Urut <span class="text-danger">*</span></label>
                    <input 
                        type="number" 
                        name="number_order" 
                        class="form-control" 
                        min="1"
                        required
                        autofocus
                    >
                </div>

                <div class="col-md-9 mb-3">
                    <label class="form-label">Nama Kandidat <span class="text-danger">*</span></label>
                    <input 
                        type="text" 
                        name="name" 
                        class="form-control" 
                        placeholder="Contoh: Bapak Ahmad"
                        required
                    >
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Foto Kandidat</label>
                <input 
                    type="file" 
                    name="photo" 
                    class="form-control"
                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                >
                <div class="form-text">
                    Format JPG, JPEG, PNG, atau WEBP. Maksimal 2MB.
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Visi</label>
                <textarea 
                    name="vision" 
                    class="form-control" 
                    rows="3"
                    placeholder="Tulis visi kandidat"
                ></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Misi</label>
                <textarea 
                    name="mission" 
                    class="form-control" 
                    rows="5"
                    placeholder="Tulis misi kandidat"
                ></textarea>
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
                    Kandidat aktif
                </label>
            </div>

            <hr>

            <button type="submit" class="btn btn-primary">
                Simpan
            </button>

            <a href="<?= htmlspecialchars($appUrl) ?>/elections/<?= $election['id'] ?>/candidates" class="btn btn-light">
                Batal
            </a>
        </form>
    </div>
</div>