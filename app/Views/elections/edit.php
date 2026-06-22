<?php

use App\Core\Csrf;
use App\Core\ViewFormatter as F;

?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-0">Edit Pemilihan</h1>
        <div class="text-muted small">
            Perbarui data sesi pemilihan.
        </div>
    </div>

    <a href="<?= F::e($appUrl) ?>/elections" class="btn btn-secondary">
        Kembali
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form method="post" action="<?= F::e($appUrl) ?>/elections/<?= F::e($election['id']) ?>/update">
            <?= Csrf::field() ?>

            <div class="mb-3">
                <label class="form-label">
                    Nama Pemilihan <span class="text-danger">*</span>
                </label>

                <input
                    type="text"
                    name="title"
                    class="form-control"
                    value="<?= F::e($election['title'] ?? '') ?>"
                    required
                    autofocus
                >
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Organization</label>

                    <select name="organization_id" class="form-select">
                        <option value="">-- Pilih Organization --</option>

                        <?php foreach ($organizations as $organization): ?>
                            <option
                                value="<?= F::e($organization['id']) ?>"
                                <?= (int) ($election['organization_id'] ?? 0) === (int) $organization['id'] ? 'selected' : '' ?>
                            >
                                <?= F::e($organization['display_label']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Wilayah / Region</label>

                    <select name="region_id" class="form-select">
                        <option value="">-- Pilih Wilayah --</option>

                        <?php foreach ($regions as $region): ?>
                            <option
                                value="<?= F::e($region['id']) ?>"
                                <?= (int) ($election['region_id'] ?? 0) === (int) $region['id'] ? 'selected' : '' ?>
                            >
                                <?= F::e($region['display_label']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Deskripsi</label>

                <textarea
                    name="description"
                    class="form-control"
                    rows="3"
                ><?= F::e($election['description'] ?? '') ?></textarea>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Status</label>

                    <select name="status" class="form-select">
                        <?php foreach ($statusOptions as $value => $label): ?>
                            <option
                                value="<?= F::e($value) ?>"
                                <?= ($election['status'] ?? '') === $value ? 'selected' : '' ?>
                            >
                                <?= F::e($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Tanggal Mulai</label>

                    <input
                        type="datetime-local"
                        name="start_at"
                        class="form-control"
                        value="<?= F::e(F::dateTimeLocal($election['start_at'] ?? null)) ?>"
                    >
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Tanggal Selesai</label>

                    <input
                        type="datetime-local"
                        name="end_at"
                        class="form-control"
                        value="<?= F::e(F::dateTimeLocal($election['end_at'] ?? null)) ?>"
                    >
                </div>
            </div>

            <hr>

            <button type="submit" class="btn btn-primary">
                Update
            </button>

            <a href="<?= F::e($appUrl) ?>/elections" class="btn btn-light">
                Batal
            </a>
        </form>
    </div>
</div>