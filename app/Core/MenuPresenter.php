<?php

namespace App\Core;

class MenuPresenter
{
    public static function render(array $menus, string $appUrl, string $currentRoute, string $prefix): string
    {
        return self::renderTree($menus, $appUrl, $currentRoute, 0, $prefix);
    }

    private static function renderTree(array $menus, string $appUrl, string $currentRoute, int $level, string $prefix): string
    {
        $html = '';

        foreach ($menus as $menu) {
            $hasChildren = !empty($menu['children']);
            $url = $menu['url'] ?? null;
            $title = $menu['title'] ?? '-';
            $target = $menu['target'] ?? '_self';
            $menuId = (int) ($menu['id'] ?? 0);

            if ($hasChildren) {
                $isOpen = self::hasActiveChild($menu, $currentRoute);
                $collapseId = $prefix . '-menu-collapse-' . $menuId;

                $html .= '<div class="menu-parent">';

                $html .= '<button type="button" ';
                $html .= 'class="list-group-item list-group-item-action d-flex justify-content-between align-items-center menu-toggle ' . ($isOpen ? 'active-parent' : '') . '" ';
                $html .= 'data-bs-toggle="collapse" ';
                $html .= 'data-bs-target="#' . self::e($collapseId) . '" ';
                $html .= 'aria-expanded="' . ($isOpen ? 'true' : 'false') . '">';

                $html .= '<span>' . self::e($title) . '</span>';
                $html .= '<span class="menu-arrow">' . ($isOpen ? '▾' : '▸') . '</span>';
                $html .= '</button>';

                $html .= '<div id="' . self::e($collapseId) . '" class="collapse ' . ($isOpen ? 'show' : '') . '">';
                $html .= self::renderTree($menu['children'], $appUrl, $currentRoute, $level + 1, $prefix);
                $html .= '</div>';

                $html .= '</div>';

                continue;
            }

            if (!$url) {
                $html .= '<div class="list-group-item text-muted">';
                $html .= self::e($title);
                $html .= '</div>';

                continue;
            }

            $activeClass = self::isActive($url, $currentRoute) ? ' active' : '';
            $indentClass = $level > 0 ? ' menu-child' : '';

            $html .= '<a href="' . self::e(self::linkUrl($url, $appUrl)) . '" ';
            $html .= 'target="' . self::e($target) . '" ';
            $html .= 'class="list-group-item list-group-item-action' . $activeClass . $indentClass . '">';
            $html .= self::e($title);

            if ($target === '_blank') {
                $html .= ' <span class="small">↗</span>';
            }

            $html .= '</a>';
        }

        return $html;
    }

    private static function linkUrl(string $url, string $appUrl): string
    {
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        if ($url === '/') {
            return $appUrl . '/';
        }

        return $appUrl . '/' . ltrim($url, '/');
    }

    private static function isActive(?string $url, string $currentRoute): bool
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

    private static function hasActiveChild(array $menu, string $currentRoute): bool
    {
        if (!empty($menu['url']) && self::isActive($menu['url'], $currentRoute)) {
            return true;
        }

        if (empty($menu['children'])) {
            return false;
        }

        foreach ($menu['children'] as $child) {
            if (self::hasActiveChild($child, $currentRoute)) {
                return true;
            }
        }

        return false;
    }

    private static function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}