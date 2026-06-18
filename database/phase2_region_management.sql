CREATE TABLE IF NOT EXISTS organizations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    type ENUM('rt','rw','kelurahan','kecamatan','kota','custom') NOT NULL DEFAULT 'custom',
    description TEXT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS regions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    organization_id INT NOT NULL,
    parent_id INT NULL,
    level ENUM('kota','kecamatan','kelurahan','rw','rt','custom') NOT NULL DEFAULT 'custom',
    code VARCHAR(50) NOT NULL,
    name VARCHAR(150) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL,

    UNIQUE KEY unique_org_region_code (organization_id, code),
    INDEX idx_regions_organization (organization_id),
    INDEX idx_regions_parent (parent_id),
    INDEX idx_regions_level (level),

    FOREIGN KEY (organization_id) REFERENCES organizations(id)
        ON DELETE CASCADE,

    FOREIGN KEY (parent_id) REFERENCES regions(id)
        ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO organizations (name, type, description, is_active)
SELECT 'Default Organization', 'custom', 'Default organization untuk data awal sistem.', 1
WHERE NOT EXISTS (
    SELECT 1 FROM organizations WHERE name = 'Default Organization'
);

INSERT INTO permissions (name, label, group_name, description)
VALUES
('manage_regions', 'Kelola Wilayah', 'Wilayah', 'Mengelola struktur organisasi dan wilayah.'),
('manage_organizations', 'Kelola Organization', 'Wilayah', 'Mengelola organization / tenant sistem.')
ON DUPLICATE KEY UPDATE
    label = VALUES(label),
    group_name = VALUES(group_name),
    description = VALUES(description);

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p ON p.name IN (
    'manage_regions',
    'manage_organizations'
)
WHERE r.name = 'superadmin';

ALTER TABLE users
ADD COLUMN organization_id INT NULL AFTER role_id,
ADD COLUMN region_id INT NULL AFTER organization_id;

ALTER TABLE voters
ADD COLUMN organization_id INT NULL AFTER voter_code,
ADD COLUMN region_id INT NULL AFTER organization_id;

ALTER TABLE elections
ADD COLUMN organization_id INT NULL AFTER id,
ADD COLUMN region_id INT NULL AFTER organization_id;

ALTER TABLE audit_logs
ADD COLUMN organization_id INT NULL AFTER user_id,
ADD COLUMN region_id INT NULL AFTER organization_id,
ADD COLUMN election_id INT NULL AFTER region_id;

ALTER TABLE users
ADD INDEX idx_users_organization (organization_id),
ADD INDEX idx_users_region (region_id);

ALTER TABLE voters
ADD INDEX idx_voters_organization (organization_id),
ADD INDEX idx_voters_region (region_id);

ALTER TABLE elections
ADD INDEX idx_elections_organization (organization_id),
ADD INDEX idx_elections_region (region_id);

ALTER TABLE audit_logs
ADD INDEX idx_audit_organization (organization_id),
ADD INDEX idx_audit_region (region_id),
ADD INDEX idx_audit_election (election_id);

ALTER TABLE users
ADD CONSTRAINT fk_users_organization
FOREIGN KEY (organization_id) REFERENCES organizations(id)
ON DELETE SET NULL;

ALTER TABLE users
ADD CONSTRAINT fk_users_region
FOREIGN KEY (region_id) REFERENCES regions(id)
ON DELETE SET NULL;

ALTER TABLE voters
ADD CONSTRAINT fk_voters_organization
FOREIGN KEY (organization_id) REFERENCES organizations(id)
ON DELETE SET NULL;

ALTER TABLE voters
ADD CONSTRAINT fk_voters_region
FOREIGN KEY (region_id) REFERENCES regions(id)
ON DELETE SET NULL;

ALTER TABLE elections
ADD CONSTRAINT fk_elections_organization
FOREIGN KEY (organization_id) REFERENCES organizations(id)
ON DELETE SET NULL;

ALTER TABLE elections
ADD CONSTRAINT fk_elections_region
FOREIGN KEY (region_id) REFERENCES regions(id)
ON DELETE SET NULL;

ALTER TABLE audit_logs
ADD CONSTRAINT fk_audit_organization
FOREIGN KEY (organization_id) REFERENCES organizations(id)
ON DELETE SET NULL;

ALTER TABLE audit_logs
ADD CONSTRAINT fk_audit_region
FOREIGN KEY (region_id) REFERENCES regions(id)
ON DELETE SET NULL;

ALTER TABLE audit_logs
ADD CONSTRAINT fk_audit_election
FOREIGN KEY (election_id) REFERENCES elections(id)
ON DELETE SET NULL;