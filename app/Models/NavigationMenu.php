<?php

namespace App\Models;

use App\Core\Auth;
use App\Core\Database;
use PDO;

class NavigationMenu
{
    public static function treeForCurrentUser(): array
    {
        $user = Auth::user();

        if (!$user || empty($user['role_id'])) {
            return [];
        }

        $menus = self::activeMenus();
        $allowedMenuIds = self::allowedMenuIds((int) $user['role_id']);

        $menusByParent = [];

        foreach ($menus as $menu) {
            $parentId = $menu['parent_id'] ? (int) $menu['parent_id'] : 0;

            if (!isset($menusByParent[$parentId])) {
                $menusByParent[$parentId] = [];
            }

            $menusByParent[$parentId][] = $menu;
        }

        return self::buildTree(0, $menusByParent, $allowedMenuIds);
    }

    private static function activeMenus(): array
    {
        $pdo = Database::connection();

        $stmt = $pdo->query("
            SELECT *
            FROM menus
            WHERE is_active = 1
            ORDER BY parent_id ASC, sort_order ASC, title ASC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private static function allowedMenuIds(int $roleId): array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            SELECT menu_id
            FROM role_menus
            WHERE role_id = ?
        ");

        $stmt->execute([$roleId]);

        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    private static function buildTree(int $parentId, array $menusByParent, array $allowedMenuIds): array
    {
        $tree = [];

        if (empty($menusByParent[$parentId])) {
            return $tree;
        }

        foreach ($menusByParent[$parentId] as $menu) {
            $menuId = (int) $menu['id'];

            $children = self::buildTree($menuId, $menusByParent, $allowedMenuIds);

            $selfAllowed = in_array($menuId, $allowedMenuIds, true)
                && self::passesPermission($menu['permission_name'] ?? null);

            if ($selfAllowed || !empty($children)) {
                $menu['children'] = $children;
                $tree[] = $menu;
            }
        }

        return $tree;
    }

    private static function passesPermission(?string $permissionName): bool
    {
        if (!$permissionName) {
            return true;
        }

        return Auth::can($permissionName);
    }
}