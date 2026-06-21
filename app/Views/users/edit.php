<?php

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Env;

$appUrl = rtrim(Env::get('APP_URL'), '/');
$isSelf = (int) $userData['id'] === (int) Auth::id();

?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-0">Edit User</h1>
        <div class="text-muted small">
            User: <strong><?= htmlspecialchars($userData['username']) ?></strong>
        </div>
    </div>

    <a href="<?= htmlspecialchars($appUrl) ?>/users" class="btn btn-secondary">
        Kembali
    </a>
</div>

<?php if ($isSelf): ?>
    <div class="alert alert-warning">
        Anda sedang mengedit akun sendiri. Role dan status aktif tidak boleh diubah dari halaman ini.
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="post" action="<?= htmlspecialchars($appUrl) ?>/users/<?= $userData['id'] ?>/update">
            <?= Csrf::field() ?>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                    <input 
                        type="text" 
                        name="name" 
                        class="form-control"
                        value="<?= htmlspecialchars($userData['name']) ?>"
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
                        value="<?= htmlspecialchars($userData['username']) ?>"
                        required
                    >
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Role <span class="text-danger">*</span></label>
                <select name="role_id" class="form-select" <?= $isSelf ? 'disabled' : '' ?> required>
                    <option value="">-- Pilih Role --</option>

                    <?php foreach ($roles as $role): ?>
                        <option 
                            value="<?= htmlspecialchars((string) $role['id']) ?>"
                            <?= (int) $userData['role_id'] === (int) $role['id'] ? 'selected' : '' ?>
                        >
                            <?= htmlspecialchars($role['label']) ?> (<?= htmlspecialchars($role['name']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>

                <?php if ($isSelf): ?>
                    <input type="hidden" name="role_id" value="<?= htmlspecialchars((string) $userData['role_id']) ?>">
                <?php endif; ?>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Organization</label>
                    <select name="organization_id" class="form-select" <?= $isSelf ? 'disabled' : '' ?>>
                        <option value="">-- Pilih Organization --</option>

                        <?php foreach ($organizations as $organization): ?>
                            <option 
                                value="<?= htmlspecialchars((string) $organization['id']) ?>"
                                <?= (int) ($userData['organization_id'] ?? 0) === (int) $organization['id'] ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($organization['name']) ?> (<?= htmlspecialchars($organization['type']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <?php if ($isSelf): ?>
                        <input type="hidden" name="organization_id" value="<?= htmlspecialchars((string) ($userData['organization_id'] ?? '')) ?>">
                    <?php endif; ?>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Wilayah / Region</label>
                    <select name="region_id" class="form-select" <?= $isSelf ? 'disabled' : '' ?>>
                        <option value="">-- Pilih Wilayah --</option>

                        <?php foreach ($regions as $region): ?>
                            <option 
                                value="<?= htmlspecialchars((string) $region['id']) ?>"
                                <?= (int) ($userData['region_id'] ?? 0) === (int) $region['id'] ? 'selected' : '' ?>
                            >
                                [<?= htmlspecialchars($region['organization_name']) ?>]
                                <?= htmlspecialchars(strtoupper($region['level'])) ?>
                                -
                                <?= htmlspecialchars($region['code']) ?>
                                -
                                <?= htmlspecialchars($region['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <?php if ($isSelf): ?>
                        <input type="hidden" name="region_id" value="<?= htmlspecialchars((string) ($userData['region_id'] ?? '')) ?>">
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-check mb-3">
                <input 
                    type="checkbox" 
                    name="is_active" 
                    id="is_active" 
                    class="form-check-input"
                    <?= (int) $userData['is_active'] === 1 ? 'checked' : '' ?>
                    <?= $isSelf ? 'disabled' : '' ?>
                >
                <label class="form-check-label" for="is_active">
                    User aktif
                </label>

                <?php if ($isSelf): ?>
                    <input type="hidden" name="is_active" value="1">
                <?php endif; ?>
            </div>

            <hr>

            <h5>Reset Password</h5>
            <div class="text-muted small mb-3">
                Kosongkan jika tidak ingin mengganti password.
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Password Baru</label>
                    <input 
                        type="password" 
                        name="new_password" 
                        class="form-control"
                        minlength="8"
                    >
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Konfirmasi Password Baru</label>
                    <input 
                        type="password" 
                        name="new_password_confirmation" 
                        class="form-control"
                        minlength="8"
                    >
                </div>
            </div>

            <hr>

            <button type="submit" class="btn btn-primary">
                Update
            </button>

            <a href="<?= htmlspecialchars($appUrl) ?>/users" class="btn btn-light">
                Batal
            </a>
        </form>
    </div>
</div>