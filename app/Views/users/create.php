<?php

use App\Core\Csrf;
use App\Core\Env;

$appUrl = rtrim(Env::get('APP_URL'), '/');

?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-0">Tambah User</h1>
        <div class="text-muted small">
            Buat akun baru untuk operator sistem.
        </div>
    </div>

    <a href="<?= htmlspecialchars($appUrl) ?>/users" class="btn btn-secondary">
        Kembali
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form method="post" action="<?= htmlspecialchars($appUrl) ?>/users/store">
            <?= Csrf::field() ?>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                    <input 
                        type="text" 
                        name="name" 
                        class="form-control"
                        required
                        autofocus
                    >
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Username <span class="text-danger">*</span></label>
                    <input 
                        type="text" 
                        name="username" 
                        class="form-control"
                        placeholder="contoh: panitia01"
                        required
                    >
                    <div class="form-text">
                        Huruf kecil, angka, titik, underscore, dan strip.
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Password <span class="text-danger">*</span></label>
                    <input 
                        type="password" 
                        name="password" 
                        class="form-control"
                        minlength="8"
                        required
                    >
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                    <input 
                        type="password" 
                        name="password_confirmation" 
                        class="form-control"
                        minlength="8"
                        required
                    >
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Role <span class="text-danger">*</span></label>
                <select name="role_id" class="form-select" required>
                    <option value="">-- Pilih Role --</option>

                    <?php foreach ($roles as $role): ?>
                        <option value="<?= htmlspecialchars((string) $role['id']) ?>">
                            <?= htmlspecialchars($role['label']) ?> (<?= htmlspecialchars($role['name']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Organization</label>
                    <select name="organization_id" class="form-select">
                        <option value="">-- Pilih Organization --</option>

                        <?php foreach ($organizations as $organization): ?>
                            <option value="<?= htmlspecialchars((string) $organization['id']) ?>">
                                <?= htmlspecialchars($organization['name']) ?> (<?= htmlspecialchars($organization['type']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Wilayah / Region</label>
                    <select name="region_id" class="form-select">
                        <option value="">-- Pilih Wilayah --</option>

                        <?php foreach ($regions as $region): ?>
                            <option value="<?= htmlspecialchars((string) $region['id']) ?>">
                                [<?= htmlspecialchars($region['organization_name']) ?>]
                                <?= htmlspecialchars(strtoupper($region['level'])) ?>
                                -
                                <?= htmlspecialchars($region['code']) ?>
                                -
                                <?= htmlspecialchars($region['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <div class="form-text">
                        Untuk admin RT/RW/Kelurahan, pilih wilayah kerja user.
                    </div>
                </div>
            </div>

            <div class="form-check mb-3">
                <input 
                    type="checkbox" 
                    name="is_active" 
                    id="is_active" 
                    class="form-check-input"
                    checked
                >
                <label class="form-check-label" for="is_active">
                    User aktif
                </label>
            </div>

            <hr>

            <button type="submit" class="btn btn-primary">
                Simpan
            </button>

            <a href="<?= htmlspecialchars($appUrl) ?>/users" class="btn btn-light">
                Batal
            </a>
        </form>
    </div>
</div>