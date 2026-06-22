<?php

use App\Core\Csrf;

?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title ?? $appName) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>
        :root {
            --sidebar-width: 280px;
            --desktop-navbar-height: 56px;
            --border-color: #dee2e6;
            --page-bg: #f5f6f8;
            --sidebar-bg: #ffffff;
            --text-main: #111827;
            --text-muted: #6b7280;
            --primary: #0d6efd;
        }

        html,
        body {
            min-height: 100%;
        }

        body {
            margin: 0;
            background: var(--page-bg);
            color: var(--text-main);
        }

        .desktop-navbar {
            min-height: var(--desktop-navbar-height);
            display: none;
        }

        .desktop-navbar .navbar-brand {
            font-weight: 700;
        }

        .desktop-user-info {
            color: #ffffff;
            font-size: 0.875rem;
        }

        .app-shell {
            min-height: 100vh;
            display: flex;
        }

        .app-sidebar {
            width: var(--sidebar-width);
            min-height: 100vh;
            background: var(--sidebar-bg);
            border-right: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            overflow-y: auto;
            flex-shrink: 0;
        }

        .app-content {
            flex: 1;
            min-width: 0;
            padding: 1.5rem;
        }

        .mobile-topbar {
            display: none;
        }

        .menu-toggle {
            border-radius: 0;
            border-left: 0;
            border-right: 0;
            background: #ffffff;
            font-weight: 600;
        }

        .menu-toggle:hover {
            background: #f8f9fa;
        }

        .menu-toggle.active-parent {
            background: #e9ecef;
            color: #111827;
        }

        .menu-child {
            padding-left: 2rem !important;
            font-size: 0.95rem;
            background: #ffffff;
        }

        .menu-child.active {
            background: var(--primary) !important;
            color: #ffffff !important;
        }

        .menu-arrow {
            font-size: 0.85rem;
            opacity: 0.75;
        }

        .offcanvas-sidebar {
            width: var(--sidebar-width) !important;
        }

        .offcanvas-user {
            padding: 0.75rem 1rem;
            background: #f8f9fa;
            border-bottom: 1px solid var(--border-color);
        }

        .flash-wrapper {
            margin-bottom: 1rem;
        }

        @media (min-width: 992px) {
            .desktop-navbar {
                display: flex;
            }

            .app-shell {
                min-height: calc(100vh - var(--desktop-navbar-height));
            }

            .app-sidebar {
                min-height: calc(100vh - var(--desktop-navbar-height));
            }
        }

        @media (max-width: 991.98px) {
            .app-shell {
                display: block;
                min-height: auto;
            }

            .app-sidebar-desktop {
                display: none !important;
            }

            .mobile-topbar {
                display: flex;
                position: sticky;
                top: 0;
                z-index: 1030;
                background: #ffffff;
                border-bottom: 1px solid var(--border-color);
                padding: 0.65rem 0.75rem;
                align-items: center;
                gap: 0.75rem;
            }

            .mobile-brand {
                flex: 1;
                min-width: 0;
                font-weight: 700;
                font-size: 0.95rem;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .mobile-logout-form {
                flex-shrink: 0;
            }

            .app-content {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>

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

<div class="app-shell">
    <aside class="app-sidebar app-sidebar-desktop">
        <div class="list-group list-group-flush">
            <?= $desktopMenuHtml ?: '<div class="list-group-item text-muted">Tidak ada menu.</div>' ?>
        </div>
    </aside>

    <main class="app-content">
        <?php if ($error): ?>
            <div class="flash-wrapper">
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error) ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="flash-wrapper">
                <div class="alert alert-success">
                    <?= htmlspecialchars($success) ?>
                </div>
            </div>
        <?php endif; ?>

        <?= $content ?>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('shown.bs.collapse', function (event) {
    const button = document.querySelector('[data-bs-target="#' + event.target.id + '"]');

    if (!button) {
        return;
    }

    const arrow = button.querySelector('.menu-arrow');

    if (arrow) {
        arrow.textContent = '▾';
    }
});

document.addEventListener('hidden.bs.collapse', function (event) {
    const button = document.querySelector('[data-bs-target="#' + event.target.id + '"]');

    if (!button) {
        return;
    }

    const arrow = button.querySelector('.menu-arrow');

    if (arrow) {
        arrow.textContent = '▸';
    }
});
</script>

</body>
</html>