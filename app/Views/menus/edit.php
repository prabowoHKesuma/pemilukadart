<?php

use App\Core\Csrf;
use App\Core\Env;

$appUrl = rtrim(Env::get('APP_URL'), '/');

?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-0">Edit Menu</h1>
        <div class="text-muted small">
            Menu: <strong><?= htmlspecialchars($menu['title']) ?></strong>
        </div>
    </div>

    <a href="<?= htmlspecialchars($appUrl) ?>/menus" class="btn btn-secondary">
        Kembali
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form method="post" action="<?= htmlspecialchars($appUrl) ?>/menus/<?= $menu['id'] ?>/update">
            <?= Csrf::field() ?>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Parent Menu</label>
                    <select name="parent_id" class="form-select">
                        <option value="">Root / Parent Utama</option>

                        <?php foreach ($parentMenus as $parent): ?>
                            <option 
                                value="<?= htmlspecialchars((string) $parent['id']) ?>"
                                <?= (int) ($menu['parent_id'] ?? 0) === (int) $parent['id'] ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($parent['title']) ?>
                                -
                                <?= htmlspecialchars($parent['menu_key']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Menu Key <span class="text-danger">*</span></label>
                    <input 
                        type="text" 
                        name="menu_key" 
                        class="form-control"
                        value="<?= htmlspecialchars($menu['menu_key']) ?>"
                        required
                    >
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Judul Menu <span class="text-danger">*</span></label>
                    <input 
                        type="text" 
                        name="title" 
                        class="form-control"
                        value="<?= htmlspecialchars($menu['title']) ?>"
                        required
                        autofocus
                    >
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">URL</label>
                    <input 
                        type="text" 
                        name="url" 
                        class="form-control"
                        value="<?= htmlspecialchars($menu['url'] ?? '') ?>"
                    >
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Icon Class</label>
                    <input 
                        type="text" 
                        name="icon_class" 
                        class="form-control"
                        value="<?= htmlspecialchars($menu['icon_class'] ?? '') ?>"
                    >
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Permission</label>
                    <select name="permission_name" class="form-select">
                        <option value="">Tanpa permission</option>

                        <?php foreach ($permissions as $permission): ?>
                            <option 
                                value="<?= htmlspecialchars($permission['name']) ?>"
                                <?= ($menu['permission_name'] ?? '') === $permission['name'] ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($permission['label']) ?>
                                -
                                <?= htmlspecialchars($permission['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2 mb-3">
                    <label class="form-label">Target</label>
                    <select name="target" class="form-select">
                        <option value="_self" <?= $menu['target'] === '_self' ? 'selected' : '' ?>>Same Tab</option>
                        <option value="_blank" <?= $menu['target'] === '_blank' ? 'selected' : '' ?>>New Tab</option>
                    </select>
                </div>

                <div class="col-md-2 mb-3">
                    <label class="form-label">Urutan</label>
                    <input 
                        type="number" 
                        name="sort_order" 
                        class="form-control"
                        value="<?= htmlspecialchars((string) $menu['sort_order']) ?>"
                    >
                </div>
            </div>

            <div class="form-check mb-3">
                <input 
                    type="checkbox" 
                    name="is_active" 
                    id="is_active" 
                    class="form-check-input"
                    <?= (int) $menu['is_active'] === 1 ? 'checked' : '' ?>
                >
                <label class="form-check-label" for="is_active">
                    Menu aktif
                </label>
            </div>

            <hr>

            <h5>Role yang boleh melihat menu</h5>

            <div class="row">
                <?php foreach ($roles as $role): ?>
                    <?php $checked = in_array((int) $role['id'], $selectedRoleIds, true); ?>

                    <div class="col-md-4 mb-2">
                        <div class="form-check">
                            <input 
                                type="checkbox" 
                                name="role_ids[]" 
                                value="<?= htmlspecialchars((string) $role['id']) ?>"
                                id="role_<?= htmlspecialchars((string) $role['id']) ?>"
                                class="form-check-input"
                                <?= $checked ? 'checked' : '' ?>
                            >
                            <label class="form-check-label" for="role_<?= htmlspecialchars((string) $role['id']) ?>">
                                <?= htmlspecialchars($role['label']) ?>
                                <div class="small text-muted">
                                    <?= htmlspecialchars($role['name']) ?>
                                </div>
                            </label>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <hr>

            <button type="submit" class="btn btn-primary">
                Update
            </button>

            <a href="<?= htmlspecialchars($appUrl) ?>/menus" class="btn btn-light">
                Batal
            </a>
        </form>
    </div>
</div>