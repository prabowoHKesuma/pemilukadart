<?php

use App\Core\Csrf;
use App\Core\Env;

$appUrl = rtrim(Env::get('APP_URL'), '/');

?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-0">Tambah Role</h1>
        <div class="text-muted small">
            Buat role baru dan pilih permission.
        </div>
    </div>

    <a href="<?= htmlspecialchars($appUrl) ?>/roles" class="btn btn-secondary">
        Kembali
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form method="post" action="<?= htmlspecialchars($appUrl) ?>/roles/store">
            <?= Csrf::field() ?>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Nama Role <span class="text-danger">*</span></label>
                    <input 
                        type="text" 
                        name="name" 
                        class="form-control"
                        placeholder="contoh: admin_rw"
                        required
                        autofocus
                    >
                    <div class="form-text">
                        Huruf kecil, angka, underscore. Contoh: admin_rt, admin_rw, operator_tps.
                    </div>
                </div>

                <div class="col-md-8 mb-3">
                    <label class="form-label">Label Role <span class="text-danger">*</span></label>
                    <input 
                        type="text" 
                        name="label" 
                        class="form-control"
                        placeholder="Contoh: Admin RW"
                        required
                    >
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Deskripsi</label>
                <textarea 
                    name="description" 
                    class="form-control" 
                    rows="3"
                    placeholder="Jelaskan fungsi role ini"
                ></textarea>
            </div>

            <hr>

            <h5 class="mb-3">Permission</h5>

            <div class="mb-3">
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="checkAllPermissions(true)">
                    Centang Semua
                </button>

                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="checkAllPermissions(false)">
                    Hapus Centang
                </button>
            </div>

            <?php foreach ($groupedPermissions as $groupName => $permissions): ?>
                <div class="card mb-3">
                    <div class="card-header">
                        <strong><?= htmlspecialchars($groupName) ?></strong>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($permissions as $permission): ?>
                                <div class="col-md-6 col-lg-4 mb-2">
                                    <div class="form-check">
                                        <input 
                                            class="form-check-input permission-checkbox" 
                                            type="checkbox" 
                                            name="permission_ids[]" 
                                            value="<?= htmlspecialchars((string) $permission['id']) ?>"
                                            id="perm_<?= htmlspecialchars((string) $permission['id']) ?>"
                                        >

                                        <label class="form-check-label" for="perm_<?= htmlspecialchars((string) $permission['id']) ?>">
                                            <strong><?= htmlspecialchars($permission['label']) ?></strong>
                                            <div class="small text-muted">
                                                <?= htmlspecialchars($permission['name']) ?>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <hr>

            <button type="submit" class="btn btn-primary">
                Simpan
            </button>

            <a href="<?= htmlspecialchars($appUrl) ?>/roles" class="btn btn-light">
                Batal
            </a>
        </form>
    </div>
</div>

<script>
function checkAllPermissions(checked) {
    document.querySelectorAll('.permission-checkbox').forEach(function (checkbox) {
        checkbox.checked = checked;
    });
}
</script>