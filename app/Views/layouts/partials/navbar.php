<?php
use App\Core\Csrf;
?>
<nav class="navbar navbar-dark bg-dark desktop-navbar">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= htmlspecialchars($appUrl) ?>/">
            <?= htmlspecialchars($appName) ?>
        </a>

        <div class="d-flex align-items-center gap-3">
            <div class="desktop-user-info">
                <?= htmlspecialchars($user['name'] ?? '-') ?>

                <?php if (!empty($user['role_label'] ?? $user['role'] ?? null)): ?>
                    <span class="text-white-50">
                        (<?= htmlspecialchars($user['role_label'] ?? $user['role']) ?>)
                    </span>
                <?php endif; ?>
            </div>

            <form method="post" action="<?= htmlspecialchars($appUrl) ?>/logout" class="mb-0">
                <?= Csrf::field() ?>
                <button type="submit" class="btn btn-sm btn-outline-light">
                    Logout
                </button>
            </form>
        </div>
    </div>
</nav>

<div class="mobile-topbar">
    <button
        class="btn btn-outline-dark btn-sm"
        type="button"
        data-bs-toggle="offcanvas"
        data-bs-target="#mobileSidebar"
        aria-controls="mobileSidebar"
    >
        ☰
    </button>

    <div class="mobile-brand">
        <?= htmlspecialchars($appName) ?>
    </div>

    <form method="post" action="<?= htmlspecialchars($appUrl) ?>/logout" class="mobile-logout-form mb-0">
        <?= Csrf::field() ?>
        <button type="submit" class="btn btn-sm btn-outline-danger">
            Logout
        </button>
    </form>
</div>

<div
    class="offcanvas offcanvas-start offcanvas-sidebar"
    tabindex="-1"
    id="mobileSidebar"
>
    <div class="offcanvas-header">
        <div>
            <div class="fw-bold">
                <?= htmlspecialchars($appName) ?>
            </div>
            <div class="small text-muted">
                E-Voting System
            </div>
        </div>

        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>

    <div class="offcanvas-user">
        <div class="fw-semibold">
            <?= htmlspecialchars($user['name'] ?? '-') ?>
        </div>
        <div class="small text-muted">
            <?= htmlspecialchars($user['role_label'] ?? $user['role'] ?? '-') ?>
        </div>
    </div>

    <div class="offcanvas-body p-0">
        <div class="list-group list-group-flush">
            <?= $mobileMenuHtml ?: '<div class="list-group-item text-muted">Tidak ada menu.</div>' ?>
        </div>
    </div>
</div>