<?php

use App\Core\Csrf;
use App\Core\Env;

$appUrl = rtrim(Env::get('APP_URL'), '/');

?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-0">Edit Wilayah</h1>
        <div class="text-muted small">
            Wilayah: <strong><?= htmlspecialchars($region['name']) ?></strong>
        </div>
    </div>

    <a href="<?= htmlspecialchars($appUrl) ?>/regions" class="btn btn-secondary">
        Kembali
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form method="post" action="<?= htmlspecialchars($appUrl) ?>/regions/<?= $region['id'] ?>/update">
            <?= Csrf::field() ?>

            <div class="mb-3">
                <label class="form-label">Organization <span class="text-danger">*</span></label>
                <select name="organization_id" class="form-select" required>
                    <option value="">-- Pilih Organization --</option>

                    <?php foreach ($organizations as $organization): ?>
                        <option 
                            value="<?= htmlspecialchars((string) $organization['id']) ?>"
                            <?= (int) $region['organization_id'] === (int) $organization['id'] ? 'selected' : '' ?>
                        >
                            <?= htmlspecialchars($organization['name']) ?> (<?= htmlspecialchars($organization['type']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Parent Wilayah</label>
                <select name="parent_id" class="form-select">
                    <option value="">Root / Tidak punya parent</option>

                    <?php foreach ($regions as $item): ?>
                        <option 
                            value="<?= htmlspecialchars((string) $item['id']) ?>"
                            <?= (int) ($region['parent_id'] ?? 0) === (int) $item['id'] ? 'selected' : '' ?>
                        >
                            [<?= htmlspecialchars($item['organization_name']) ?>]
                            <?= htmlspecialchars(strtoupper($item['level'])) ?>
                            -
                            <?= htmlspecialchars($item['code']) ?>
                            -
                            <?= htmlspecialchars($item['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <div class="form-text">
                    Jangan pilih child wilayah ini sebagai parent.
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Level <span class="text-danger">*</span></label>
                    <select name="level" class="form-select" required>
                        <option value="">-- Pilih Level --</option>

                        <?php foreach ($levels as $value => $label): ?>
                            <option 
                                value="<?= htmlspecialchars($value) ?>"
                                <?= $region['level'] === $value ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Kode Wilayah <span class="text-danger">*</span></label>
                    <input 
                        type="text" 
                        name="code" 
                        class="form-control"
                        value="<?= htmlspecialchars($region['code']) ?>"
                        required
                    >
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Nama Wilayah <span class="text-danger">*</span></label>
                    <input 
                        type="text" 
                        name="name" 
                        class="form-control"
                        value="<?= htmlspecialchars($region['name']) ?>"
                        required
                        autofocus
                    >
                </div>
            </div>

            <hr>

            <button type="submit" class="btn btn-primary">
                Update
            </button>

            <a href="<?= htmlspecialchars($appUrl) ?>/regions" class="btn btn-light">
                Batal
            </a>
        </form>
    </div>
</div>