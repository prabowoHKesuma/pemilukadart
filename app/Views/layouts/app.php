<?php

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Env;
use App\Core\Session;

$appName = Env::get('APP_NAME', 'RT Voting');
$appUrl = rtrim(Env::get('APP_URL'), '/');
$user = Auth::user();

$error = Session::flash('error');
$success = Session::flash('success');

$menuTree = \App\Models\NavigationMenu::treeForCurrentUser();

$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$appPath = parse_url($appUrl, PHP_URL_PATH) ?: '';
$currentRoute = $currentPath;

if ($appPath !== '' && str_starts_with($currentPath, $appPath)) {
    $currentRoute = substr($currentPath, strlen($appPath));
}

$currentRoute = '/' . trim($currentRoute, '/');

if ($currentRoute === '/') {
    $currentRoute = '/';
}

function menuLinkUrl(string $url, string $appUrl): string
{
    if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
        return $url;
    }

    if ($url === '/') {
        return $appUrl . '/';
    }

    return $appUrl . '/' . ltrim($url, '/');
}

function isMenuActive(?string $url, string $currentRoute): bool
{
    if (!$url) {
        return false;
    }

    $urlRoute = '/' . trim($url, '/');

    if ($urlRoute === '/') {
        return $currentRoute === '/';
    }

    return $currentRoute === $urlRoute || str_starts_with($currentRoute, $urlRoute . '/');
}

function menuHasActiveChild(array $menu, string $currentRoute): bool
{
    if (!empty($menu['url']) && isMenuActive($menu['url'], $currentRoute)) {
        return true;
    }

    if (empty($menu['children'])) {
        return false;
    }

    foreach ($menu['children'] as $child) {
        if (menuHasActiveChild($child, $currentRoute)) {
            return true;
        }
    }

    return false;
}

function renderMenuTree(array $menus, string $appUrl, string $currentRoute, int $level = 0, string $prefix = 'desktop'): void
{
    foreach ($menus as $menu) {
        $hasChildren = !empty($menu['children']);
        $url = $menu['url'] ?? null;
        $title = $menu['title'];
        $target = $menu['target'] ?? '_self';
        $menuId = (int) $menu['id'];

        if ($hasChildren) {
            $isOpen = menuHasActiveChild($menu, $currentRoute);
            $collapseId = $prefix . '-menu-collapse-' . $menuId;

            echo '<div class="menu-parent">';

            echo '<button type="button" ';
            echo 'class="list-group-item list-group-item-action d-flex justify-content-between align-items-center menu-toggle ' . ($isOpen ? 'active-parent' : '') . '" ';
            echo 'data-bs-toggle="collapse" ';
            echo 'data-bs-target="#' . htmlspecialchars($collapseId) . '" ';
            echo 'aria-expanded="' . ($isOpen ? 'true' : 'false') . '">';

            echo '<span>' . htmlspecialchars($title) . '</span>';
            echo '<span class="menu-arrow">' . ($isOpen ? '▾' : '▸') . '</span>';
            echo '</button>';

            echo '<div id="' . htmlspecialchars($collapseId) . '" class="collapse ' . ($isOpen ? 'show' : '') . '">';
            renderMenuTree($menu['children'], $appUrl, $currentRoute, $level + 1, $prefix);
            echo '</div>';

            echo '</div>';

            continue;
        }

        if (!$url) {
            echo '<div class="list-group-item text-muted">';
            echo htmlspecialchars($title);
            echo '</div>';
            continue;
        }

        $activeClass = isMenuActive($url, $currentRoute) ? ' active' : '';
        $indentClass = $level > 0 ? ' menu-child' : '';

        echo '<a href="' . htmlspecialchars(menuLinkUrl($url, $appUrl)) . '" ';
        echo 'target="' . htmlspecialchars($target) . '" ';
        echo 'class="list-group-item list-group-item-action' . $activeClass . $indentClass . '">';
        echo htmlspecialchars($title);

        if ($target === '_blank') {
            echo ' <span class="small">↗</span>';
        }

        echo '</a>';
    }
}

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

        .sidebar-brand {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
        }

        .sidebar-brand-title {
            font-weight: 700;
            font-size: 1.1rem;
            line-height: 1.2;
        }

        .sidebar-brand-subtitle {
            color: var(--text-muted);
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .sidebar-user {
            padding: 0.9rem 1rem;
            border-bottom: 1px solid var(--border-color);
            background: #f8f9fa;
        }

        .sidebar-user-name {
            font-weight: 600;
            line-height: 1.2;
        }

        .sidebar-user-role {
            font-size: 0.875rem;
            color: var(--text-muted);
            margin-top: 0.25rem;
        }

        .sidebar-logout {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid var(--border-color);
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

        .offcanvas-logout {
            padding: 0.75rem 1rem;
            border-top: 1px solid var(--border-color);
        }

        .flash-wrapper {
            margin-bottom: 1rem;
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
            <?php if (empty($menuTree)): ?>
                <div class="list-group-item text-muted">
                    Tidak ada menu.
                </div>
            <?php else: ?>
                <?php renderMenuTree($menuTree, $appUrl, $currentRoute, 0, 'mobile'); ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="offcanvas-logout">
        <form method="post" action="<?= htmlspecialchars($appUrl) ?>/logout" class="mb-0">
            <?= Csrf::field() ?>
            <button type="submit" class="btn btn-outline-danger w-100">
                Logout
            </button>
        </form>
    </div>
</div>

<div class="app-shell">
    <aside class="app-sidebar app-sidebar-desktop">
        <div class="sidebar-brand">
            <div class="sidebar-brand-title">
                <?= htmlspecialchars($appName) ?>
            </div>
            <div class="sidebar-brand-subtitle">
                E-Voting System
            </div>
        </div>

        <div class="sidebar-user">
            <div class="sidebar-user-name">
                <?= htmlspecialchars($user['name'] ?? '-') ?>
            </div>
            <div class="sidebar-user-role">
                <?= htmlspecialchars($user['role_label'] ?? $user['role'] ?? '-') ?>
            </div>
        </div>

        <div class="sidebar-logout">
            <form method="post" action="<?= htmlspecialchars($appUrl) ?>/logout" class="mb-0">
                <?= Csrf::field() ?>
                <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                    Logout
                </button>
            </form>
        </div>

        <div class="list-group list-group-flush">
            <?php if (empty($menuTree)): ?>
                <div class="list-group-item text-muted">
                    Tidak ada menu.
                </div>
            <?php else: ?>
                <?php renderMenuTree($menuTree, $appUrl, $currentRoute, 0, 'desktop'); ?>
            <?php endif; ?>
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

    if (button) {
        const arrow = button.querySelector('.menu-arrow');

        if (arrow) {
            arrow.textContent = '▾';
        }
    }
});

document.addEventListener('hidden.bs.collapse', function (event) {
    const button = document.querySelector('[data-bs-target="#' + event.target.id + '"]');

    if (button) {
        const arrow = button.querySelector('.menu-arrow');

        if (arrow) {
            arrow.textContent = '▸';
        }
    }
});
</script>

</body>
</html>