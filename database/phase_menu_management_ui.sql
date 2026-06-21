INSERT INTO permissions (name, label, group_name, description)
VALUES
('manage_menus', 'Kelola Menu', 'System', 'Mengelola menu sidebar dan role menu.')
ON DUPLICATE KEY UPDATE
    label = VALUES(label),
    group_name = VALUES(group_name),
    description = VALUES(description);

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p ON p.name = 'manage_menus'
WHERE r.name = 'superadmin';

INSERT INTO menus (
    parent_id,
    menu_key,
    title,
    url,
    icon_class,
    permission_name,
    target,
    sort_order,
    is_active
)
VALUES (
    (SELECT id FROM menus p WHERE p.menu_key = 'group_admin'),
    'admin_menus',
    'Menu Management',
    '/menus',
    NULL,
    'manage_menus',
    '_self',
    40,
    1
)
ON DUPLICATE KEY UPDATE
    parent_id = VALUES(parent_id),
    title = VALUES(title),
    url = VALUES(url),
    permission_name = VALUES(permission_name),
    target = VALUES(target),
    sort_order = VALUES(sort_order),
    is_active = VALUES(is_active),
    updated_at = NOW();

INSERT IGNORE INTO role_menus (role_id, menu_id)
SELECT r.id, m.id
FROM roles r
JOIN menus m ON m.menu_key = 'admin_menus'
WHERE r.name = 'superadmin';


ALTER TABLE menus CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE role_menus CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE roles CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE permissions CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE role_permissions CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;