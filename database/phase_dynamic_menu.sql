CREATE TABLE IF NOT EXISTS menus (
    id INT AUTO_INCREMENT PRIMARY KEY,
    parent_id INT NULL,
    menu_key VARCHAR(100) NOT NULL UNIQUE,
    title VARCHAR(150) NOT NULL,
    url VARCHAR(255) NULL,
    icon_class VARCHAR(100) NULL,
    permission_name VARCHAR(100) NULL,
    target VARCHAR(20) NOT NULL DEFAULT '_self',
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL,

    INDEX idx_menus_parent (parent_id),
    INDEX idx_menus_permission (permission_name),
    INDEX idx_menus_active_order (is_active, sort_order),

    FOREIGN KEY (parent_id) REFERENCES menus(id)
        ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS role_menus (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    menu_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY unique_role_menu (role_id, menu_id),

    FOREIGN KEY (role_id) REFERENCES roles(id)
        ON DELETE CASCADE,

    FOREIGN KEY (menu_id) REFERENCES menus(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

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
VALUES
(NULL, 'dashboard', 'Dashboard', '/', NULL, NULL, '_self', 10, 1),
(NULL, 'group_panitia', 'Panitia', NULL, NULL, NULL, '_self', 20, 1),
(NULL, 'group_auditor', 'Auditor', NULL, NULL, NULL, '_self', 30, 1),
(NULL, 'group_saksi', 'Saksi', NULL, NULL, NULL, '_self', 40, 1),
(NULL, 'group_admin', 'Administrasi Sistem', NULL, NULL, NULL, '_self', 90, 1)
ON DUPLICATE KEY UPDATE
    title = VALUES(title),
    url = VALUES(url),
    icon_class = VALUES(icon_class),
    permission_name = VALUES(permission_name),
    target = VALUES(target),
    sort_order = VALUES(sort_order),
    is_active = VALUES(is_active),
    updated_at = NOW();

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
VALUES
(
    (SELECT id FROM menus p WHERE p.menu_key = 'group_panitia'),
    'panitia_pemilihan',
    'Pemilihan',
    '/elections',
    NULL,
    'manage_elections',
    '_self',
    10,
    1
),
(
    (SELECT id FROM menus p WHERE p.menu_key = 'group_panitia'),
    'panitia_pemilih',
    'Pemilih',
    '/voters',
    NULL,
    'manage_voters',
    '_self',
    20,
    1
),
(
    (SELECT id FROM menus p WHERE p.menu_key = 'group_panitia'),
    'panitia_kandidat',
    'Kandidat',
    '/elections',
    NULL,
    'manage_candidates',
    '_self',
    30,
    1
),
(
    (SELECT id FROM menus p WHERE p.menu_key = 'group_panitia'),
    'panitia_validasi_tps',
    'Validasi TPS',
    '/tps-voting',
    NULL,
    'tps_validate',
    '_self',
    40,
    1
),
(
    (SELECT id FROM menus p WHERE p.menu_key = 'group_panitia'),
    'panitia_bilik_tps',
    'Bilik TPS',
    '/tps-booth',
    NULL,
    'tps_booth_access',
    '_blank',
    50,
    1
),
(
    (SELECT id FROM menus p WHERE p.menu_key = 'group_panitia'),
    'panitia_remote_verification',
    'Verifikasi Remote',
    '/remote-verifications',
    NULL,
    'manage_remote_verification',
    '_self',
    60,
    1
),
(
    (SELECT id FROM menus p WHERE p.menu_key = 'group_panitia'),
    'panitia_remote_token',
    'Token Remote',
    '/remote-tokens',
    NULL,
    'manage_remote_token',
    '_self',
    70,
    1
),
(
    (SELECT id FROM menus p WHERE p.menu_key = 'group_panitia'),
    'panitia_hasil',
    'Hasil',
    '/results',
    NULL,
    'view_results',
    '_self',
    80,
    1
),
(
    (SELECT id FROM menus p WHERE p.menu_key = 'group_auditor'),
    'auditor_audit_log',
    'Audit Log',
    '/audit-logs',
    NULL,
    'view_audit_logs',
    '_self',
    10,
    1
),
(
    (SELECT id FROM menus p WHERE p.menu_key = 'group_auditor'),
    'auditor_hasil',
    'Hasil',
    '/results',
    NULL,
    'view_results',
    '_self',
    20,
    1
),
(
    (SELECT id FROM menus p WHERE p.menu_key = 'group_saksi'),
    'saksi_hasil',
    'Hasil',
    '/results',
    NULL,
    'view_results',
    '_self',
    10,
    1
),
(
    (SELECT id FROM menus p WHERE p.menu_key = 'group_admin'),
    'admin_users',
    'User Management',
    '/users',
    NULL,
    'manage_users',
    '_self',
    10,
    1
),
(
    (SELECT id FROM menus p WHERE p.menu_key = 'group_admin'),
    'admin_roles',
    'Role Management',
    '/roles',
    NULL,
    'manage_roles',
    '_self',
    20,
    1
),
(
    (SELECT id FROM menus p WHERE p.menu_key = 'group_admin'),
    'admin_regions',
    'Region Management',
    '/regions',
    NULL,
    'manage_regions',
    '_self',
    30,
    1
)
ON DUPLICATE KEY UPDATE
    parent_id = VALUES(parent_id),
    title = VALUES(title),
    url = VALUES(url),
    icon_class = VALUES(icon_class),
    permission_name = VALUES(permission_name),
    target = VALUES(target),
    sort_order = VALUES(sort_order),
    is_active = VALUES(is_active),
    updated_at = NOW();

INSERT IGNORE INTO role_menus (role_id, menu_id)
SELECT r.id, m.id
FROM roles r
JOIN menus m
WHERE m.menu_key = 'dashboard';

INSERT IGNORE INTO role_menus (role_id, menu_id)
SELECT r.id, m.id
FROM roles r
JOIN menus m ON m.menu_key IN (
    'panitia_pemilihan',
    'panitia_pemilih',
    'panitia_kandidat',
    'panitia_validasi_tps',
    'panitia_bilik_tps',
    'panitia_remote_verification',
    'panitia_remote_token',
    'panitia_hasil'
)
WHERE r.name = 'panitia';

INSERT IGNORE INTO role_menus (role_id, menu_id)
SELECT r.id, m.id
FROM roles r
JOIN menus m ON m.menu_key IN (
    'saksi_hasil'
)
WHERE r.name = 'saksi';

INSERT IGNORE INTO role_menus (role_id, menu_id)
SELECT r.id, m.id
FROM roles r
JOIN menus m ON m.menu_key IN (
    'auditor_audit_log',
    'auditor_hasil'
)
WHERE r.name = 'auditor';

INSERT IGNORE INTO role_menus (role_id, menu_id)
SELECT r.id, m.id
FROM roles r
JOIN menus m ON m.menu_key IN (
    'saksi_hasil'
)
WHERE r.name = 'viewer';

INSERT IGNORE INTO role_menus (role_id, menu_id)
SELECT r.id, m.id
FROM roles r
JOIN menus m ON m.menu_key IN (
    'panitia_pemilihan',
    'panitia_pemilih',
    'panitia_kandidat',
    'panitia_validasi_tps',
    'panitia_bilik_tps',
    'panitia_remote_verification',
    'panitia_remote_token',
    'panitia_hasil',
    'auditor_audit_log',
    'admin_users',
    'admin_roles',
    'admin_regions'
)
WHERE r.name = 'superadmin';

