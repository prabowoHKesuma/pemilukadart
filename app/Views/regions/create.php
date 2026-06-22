<?php

use App\Core\Csrf;
use App\Core\ViewFormatter as F;

?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-0">Tambah Wilayah</h1>
        <div class="text-muted small">
            Tambahkan Kota, Kecamatan, Kelurahan, RW, atau RT.
        </div>
    </div>

    <a href="<?= F::e($appUrl) ?>/regions" class="btn btn-secondary">
        Kembali
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form method="post" action="<?= F::e($appUrl) ?>/regions/store">
            <?= Csrf::field() ?>

            <div class="mb-3">
                <label class="form-label">
                    Organization <span class="text-danger">*</span>
                </label>

                <select name="organization_id" class="form-select" required>
                    <option value="">-- Pilih Organization --</option>

                    <?php foreach ($organizations as $organization): ?>
                        <option value="<?= F::e($organization['id']) ?>">
                            <?= F::e($organization['display_label']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Parent Wilayah</label>

                <select name="parent_id" class="form-select">
                    <option value="">Root / Tidak punya parent</option>

                    <?php foreach ($regions as $region): ?>
                        <option value="<?= F::e($region['id']) ?>">
                            <?= F::e($region['display_label']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <div class="form-text">
                    Contoh: RT punya parent RW, RW punya parent Kelurahan.
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">
                        Level <span class="text-danger">*</span>
                    </label>

                    <select name="level" class="form-select" required>
                        <option value="">-- Pilih Level --</option>

                        <?php foreach ($levels as $value => $label): ?>
                            <option value="<?= F::e($value) ?>">
                                <?= F::e($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">
                        Kode Wilayah <span class="text-danger">*</span>
                    </label>

                    <input
                        type="text"
                        name="code"
                        class="form-control"
                        placeholder="Contoh: RT-011, RW-001"
                        required
                    >
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">
                        Nama Wilayah <span class="text-danger">*</span>
                    </label>

                    <input
                        type="text"
                        name="name"
                        class="form-control"
                        placeholder="Contoh: RT 011"
                        required
                        autofocus
                    >
                </div>
            </div>

            <hr>

            <button type="submit" class="btn btn-primary">
                Simpan
            </button>

            <a href="<?= F::e($appUrl) ?>/regions" class="btn btn-light">
                Batal
            </a>
        </form>
    </div>
</div>