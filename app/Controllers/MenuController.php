<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Redirect;
use App\Core\Session;
use App\Models\AuditLog;
use App\Models\Menu;
use App\Models\Permission;
use App\Models\Role;

class MenuController extends Controller
{
    public function index(): void
    {
        Auth::requirePermission('manage_menus');

        $menus = Menu::all();

        $this->view('menus/index', [
            'title' => 'Menu Management',
            'menus' => $menus,
        ]);
    }

    public function create(): void
    {
        Auth::requirePermission('manage_menus');

        $this->view('menus/create', [
            'title' => 'Tambah Menu',
            'parentMenus' => Menu::parentOptions(),
            'permissions' => Permission::all(),
            'roles' => Role::options(),
        ]);
    }

    public function store(): void
    {
        Auth::requirePermission('manage_menus');
        Csrf::verify();

        $parentId = !empty($_POST['parent_id']) ? (int) $_POST['parent_id'] : null;
        $menuKey = $this->normalizeMenuKey($_POST['menu_key'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $url = trim($_POST['url'] ?? '');
        $iconClass = trim($_POST['icon_class'] ?? '');
        $permissionName = trim($_POST['permission_name'] ?? '');
        $target = trim($_POST['target'] ?? '_self');
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $roleIds = $_POST['role_ids'] ?? [];

        if ($menuKey === '') {
            Session::flash('error', 'Menu key wajib diisi.');
            Redirect::to('/menus/create');
        }

        if (!preg_match('/^[a-z0-9_]+$/', $menuKey)) {
            Session::flash('error', 'Menu key hanya boleh huruf kecil, angka, dan underscore.');
            Redirect::to('/menus/create');
        }

        if (Menu::keyExists($menuKey)) {
            Session::flash('error', 'Menu key sudah digunakan.');
            Redirect::to('/menus/create');
        }

        if ($title === '') {
            Session::flash('error', 'Judul menu wajib diisi.');
            Redirect::to('/menus/create');
        }

        if (!in_array($target, ['_self', '_blank'], true)) {
            $target = '_self';
        }

        if ($parentId && !Menu::find($parentId)) {
            Session::flash('error', 'Parent menu tidak valid.');
            Redirect::to('/menus/create');
        }

        if ($permissionName === '') {
            $permissionName = null;
        }

        if ($url === '') {
            $url = null;
        }

        if ($iconClass === '') {
            $iconClass = null;
        }

        if (!is_array($roleIds)) {
            $roleIds = [];
        }

        $menuId = Menu::create([
            'parent_id' => $parentId,
            'menu_key' => $menuKey,
            'title' => $title,
            'url' => $url,
            'icon_class' => $iconClass,
            'permission_name' => $permissionName,
            'target' => $target,
            'sort_order' => $sortOrder,
            'is_active' => $isActive,
        ]);

        Menu::syncRoles($menuId, $roleIds);

        AuditLog::record(
            'menu_create',
            'Membuat menu: ' . $menuKey . ' - ' . $title
        );

        Session::flash('success', 'Menu berhasil ditambahkan.');
        Redirect::to('/menus');
    }

    public function edit(string $id): void
    {
        Auth::requirePermission('manage_menus');

        $menu = Menu::find((int) $id);

        if (!$menu) {
            http_response_code(404);
            die('Menu tidak ditemukan.');
        }

        $this->view('menus/edit', [
            'title' => 'Edit Menu',
            'menu' => $menu,
            'parentMenus' => Menu::parentOptions((int) $id),
            'permissions' => Permission::all(),
            'roles' => Role::options(),
            'selectedRoleIds' => Menu::roleIds((int) $id),
        ]);
    }

    public function update(string $id): void
    {
        Auth::requirePermission('manage_menus');
        Csrf::verify();

        $menu = Menu::find((int) $id);

        if (!$menu) {
            http_response_code(404);
            die('Menu tidak ditemukan.');
        }

        $parentId = !empty($_POST['parent_id']) ? (int) $_POST['parent_id'] : null;
        $menuKey = $this->normalizeMenuKey($_POST['menu_key'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $url = trim($_POST['url'] ?? '');
        $iconClass = trim($_POST['icon_class'] ?? '');
        $permissionName = trim($_POST['permission_name'] ?? '');
        $target = trim($_POST['target'] ?? '_self');
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $roleIds = $_POST['role_ids'] ?? [];

        if ($menuKey === '') {
            Session::flash('error', 'Menu key wajib diisi.');
            Redirect::to('/menus/' . $id . '/edit');
        }

        if (!preg_match('/^[a-z0-9_]+$/', $menuKey)) {
            Session::flash('error', 'Menu key hanya boleh huruf kecil, angka, dan underscore.');
            Redirect::to('/menus/' . $id . '/edit');
        }

        if (Menu::keyExists($menuKey, (int) $id)) {
            Session::flash('error', 'Menu key sudah digunakan.');
            Redirect::to('/menus/' . $id . '/edit');
        }

        if ($title === '') {
            Session::flash('error', 'Judul menu wajib diisi.');
            Redirect::to('/menus/' . $id . '/edit');
        }

        if (!in_array($target, ['_self', '_blank'], true)) {
            $target = '_self';
        }

        if ($parentId) {
            if ($parentId === (int) $id) {
                Session::flash('error', 'Menu tidak boleh menjadi parent dirinya sendiri.');
                Redirect::to('/menus/' . $id . '/edit');
            }

            if (!Menu::find($parentId)) {
                Session::flash('error', 'Parent menu tidak valid.');
                Redirect::to('/menus/' . $id . '/edit');
            }

            if (Menu::isDescendantOf($parentId, (int) $id)) {
                Session::flash('error', 'Parent tidak boleh berasal dari child menu ini.');
                Redirect::to('/menus/' . $id . '/edit');
            }
        }

        if ($permissionName === '') {
            $permissionName = null;
        }

        if ($url === '') {
            $url = null;
        }

        if ($iconClass === '') {
            $iconClass = null;
        }

        if (!is_array($roleIds)) {
            $roleIds = [];
        }

        Menu::update((int) $id, [
            'parent_id' => $parentId,
            'menu_key' => $menuKey,
            'title' => $title,
            'url' => $url,
            'icon_class' => $iconClass,
            'permission_name' => $permissionName,
            'target' => $target,
            'sort_order' => $sortOrder,
            'is_active' => $isActive,
        ]);

        Menu::syncRoles((int) $id, $roleIds);

        AuditLog::record(
            'menu_update',
            'Memperbarui menu ID ' . $id . ': ' . $menuKey . ' - ' . $title
        );

        Session::flash('success', 'Menu berhasil diperbarui.');
        Redirect::to('/menus');
    }

    public function destroy(string $id): void
    {
        Auth::requirePermission('manage_menus');
        Csrf::verify();

        $menu = Menu::find((int) $id);

        if (!$menu) {
            http_response_code(404);
            die('Menu tidak ditemukan.');
        }

        if (Menu::countChildren((int) $id) > 0) {
            Session::flash('error', 'Menu tidak bisa dihapus karena masih punya child.');
            Redirect::to('/menus');
        }

        Menu::delete((int) $id);

        AuditLog::record(
            'menu_delete',
            'Menghapus menu ID ' . $id . ': ' . $menu['menu_key']
        );

        Session::flash('success', 'Menu berhasil dihapus.');
        Redirect::to('/menus');
    }

    private function normalizeMenuKey(string $menuKey): string
    {
        $menuKey = strtolower(trim($menuKey));
        $menuKey = preg_replace('/[^a-z0-9_]+/', '_', $menuKey);
        $menuKey = trim($menuKey, '_');

        return $menuKey;
    }
}