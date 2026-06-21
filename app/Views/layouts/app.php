<?php

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Env;
use App\Core\Session;

$appName = Env::get('APP_NAME', 'RT Voting');
$user = Auth::user();

$error = Session::flash('error');
$success = Session::flash('success');

$menuTree = \App\Models\NavigationMenu::treeForCurrentUser();
$appUrl = rtrim(Env::get('APP_URL'), '/');

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

function renderMenuTree(array $menus, string $appUrl, string $currentRoute, int $level = 0): void
{
    foreach ($menus as $menu) {
        $hasChildren = !empty($menu['children']);
        $url = $menu['url'] ?? null;
        $title = $menu['title'];
        $target = $menu['target'] ?? '_self';
        $menuId = (int) $menu['id'];

        if ($hasChildren) {
            $isOpen = menuHasActiveChild($menu, $currentRoute);
            $collapseId = 'menu-collapse-' . $menuId;

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
            renderMenuTree($menu['children'], $appUrl, $currentRoute, $level + 1);
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
            background: #0d6efd !important;
            color: #ffffff !important;
        }

        .menu-arrow {
            font-size: 0.85rem;
            opacity: 0.75;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= htmlspecialchars(Env::get('APP_URL')) ?>">
            <?= htmlspecialchars($appName) ?>
        </a>

        <div class="d-flex align-items-center gap-3">
            <span class="text-white small">
                <?= htmlspecialchars($user['name'] ?? '') ?> 
                <?php if (!empty($user['role'])): ?>
                    (<?= htmlspecialchars($user['role']) ?>)
                <?php endif; ?>
            </span>

            <form method="post" action="<?= htmlspecialchars(Env::get('APP_URL')) ?>/logout" class="mb-0">
                <?= Csrf::field() ?>
                <button type="submit" class="btn btn-sm btn-outline-light">
                    Logout
                </button>
            </form>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <aside class="col-md-2 bg-light min-vh-100 border-end pt-3">
            <div class="list-group">
                <?php if (empty($menuTree)): ?>
                    <div class="list-group-item text-muted">
                        Tidak ada menu.
                    </div>
                <?php else: ?>
                    <?php renderMenuTree($menuTree, $appUrl, $currentRoute); ?>
                <?php endif; ?>
            </div>
        </aside>

        <main class="col-md-10 pt-4 px-4">
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <?= $content ?>
        </main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>