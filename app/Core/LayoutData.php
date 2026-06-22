<?php

namespace App\Core;

use App\Models\NavigationMenu;

class LayoutData
{
    public static function make(): array
    {
        $appName = Env::get('APP_NAME', 'RT Voting');
        $appUrl = rtrim(Env::get('APP_URL'), '/');
        $user = Auth::user();

        $currentRoute = self::currentRoute($appUrl);

        $menuTree = NavigationMenu::treeForCurrentUser();

        return [
            'appName' => $appName,
            'appUrl' => $appUrl,
            'user' => $user,
            'error' => Session::flash('error'),
            'success' => Session::flash('success'),
            'currentRoute' => $currentRoute,
            'desktopMenuHtml' => MenuPresenter::render($menuTree, $appUrl, $currentRoute, 'desktop'),
            'mobileMenuHtml' => MenuPresenter::render($menuTree, $appUrl, $currentRoute, 'mobile'),
        ];
    }

    private static function currentRoute(string $appUrl): string
    {
        $currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $appPath = parse_url($appUrl, PHP_URL_PATH) ?: '';

        $currentRoute = $currentPath;

        if ($appPath !== '' && str_starts_with($currentPath, $appPath)) {
            $currentRoute = substr($currentPath, strlen($appPath));
        }

        $currentRoute = '/' . trim($currentRoute, '/');

        return $currentRoute === '/' ? '/' : $currentRoute;
    }
}