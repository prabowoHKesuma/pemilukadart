CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    label VARCHAR(150) NOT NULL,
    description TEXT NULL,
    is_system TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    label VARCHAR(150) NOT NULL,
    group_name VARCHAR(100) NULL,
    description TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS role_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    permission_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY unique_role_permission (role_id, permission_id),

    FOREIGN KEY (role_id) REFERENCES roles(id)
        ON DELETE CASCADE,

    FOREIGN KEY (permission_id) REFERENCES permissions(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

ALTER TABLE users
ADD COLUMN role_id INT NULL AFTER role;

ALTER TABLE users
ADD INDEX idx_users_role_id (role_id);

ALTER TABLE users
ADD CONSTRAINT fk_users_role_id
FOREIGN KEY (role_id) REFERENCES roles(id)
ON DELETE SET NULL;

INSERT INTO roles (name, label, description, is_system)
VALUES
('superadmin', 'Super Admin', 'Akses penuh seluruh sistem.', 1),
('panitia', 'Panitia', 'Operator pemilihan, validasi TPS, remote verification, dan token.', 1),
('saksi', 'Saksi', 'Akses lihat hasil dan berita acara.', 1),
('auditor', 'Auditor', 'Akses audit log dan hasil.', 1),
('viewer', 'Viewer', 'Akses baca terbatas.', 1)
ON DUPLICATE KEY UPDATE
    label = VALUES(label),
    description = VALUES(description),
    is_system = VALUES(is_system);

INSERT INTO permissions (name, label, group_name, description)
VALUES
('manage_users', 'Kelola User', 'User Management', 'Tambah, edit, reset password, aktif/nonaktif user.'),
('manage_roles', 'Kelola Role', 'Role Management', 'Tambah, edit, dan mengatur role.'),
('manage_permissions', 'Kelola Permission', 'Role Management', 'Mengatur permission role.'),

('manage_elections', 'Kelola Pemilihan', 'Pemilihan', 'Tambah, edit, hapus, dan ubah status pemilihan.'),
('manage_candidates', 'Kelola Kandidat', 'Pemilihan', 'Tambah, edit, hapus kandidat.'),
('manage_voters', 'Kelola Master Pemilih', 'Pemilih', 'Tambah, edit, hapus, aktif/nonaktif pemilih.'),
('assign_voters', 'Assign Pemilih', 'Pemilih', 'Menambahkan pemilih ke pemilihan.'),

('tps_validate', 'Validasi TPS', 'TPS', 'Validasi pemilih TPS dan generate kode bilik.'),
('tps_booth_access', 'Akses Bilik TPS', 'TPS', 'Akses mode bilik TPS.'),

('manage_remote_verification', 'Kelola Verifikasi Remote', 'Remote Voting', 'Upload, approve, reject remote verification.'),
('manage_remote_token', 'Kelola Token Remote', 'Remote Voting', 'Generate dan revoke token remote.'),

('view_results', 'Lihat Hasil', 'Hasil', 'Melihat hasil pemilihan.'),
('print_results', 'Cetak Berita Acara', 'Hasil', 'Cetak dan export hasil pemilihan.'),

('view_audit_logs', 'Lihat Audit Log', 'Audit', 'Melihat audit log sistem.'),

('manage_system_settings', 'Kelola Setting Sistem', 'System', 'Mengelola konfigurasi sistem.')
ON DUPLICATE KEY UPDATE
    label = VALUES(label),
    group_name = VALUES(group_name),
    description = VALUES(description);

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p
WHERE r.name = 'superadmin';

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p ON p.name IN (
    'manage_elections',
    'manage_candidates',
    'manage_voters',
    'assign_voters',
    'tps_validate',
    'tps_booth_access',
    'manage_remote_verification',
    'manage_remote_token',
    'view_results',
    'print_results'
)
WHERE r.name = 'panitia';

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p ON p.name IN (
    'view_results',
    'print_results'
)
WHERE r.name = 'saksi';

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p ON p.name IN (
    'view_results',
    'print_results',
    'view_audit_logs'
)
WHERE r.name = 'auditor';

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p ON p.name IN (
    'view_results'
)
WHERE r.name = 'viewer';

UPDATE users u
JOIN roles r ON r.name = u.role
SET u.role_id = r.id
WHERE u.role_id IS NULL;

