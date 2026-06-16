CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('superadmin','panitia','saksi') NOT NULL DEFAULT 'panitia',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    last_login_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL
) ENGINE=InnoDB;

CREATE TABLE elections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    description TEXT NULL,
    status ENUM('draft','open','closed','finished') NOT NULL DEFAULT 'draft',
    start_at DATETIME NULL,
    end_at DATETIME NULL,
    created_by INT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL,

    FOREIGN KEY (created_by) REFERENCES users(id)
        ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE voters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    voter_code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(120) NOT NULL,
    nik_hash VARCHAR(255) NULL,
    kk_hash VARCHAR(255) NULL,
    address TEXT NULL,
    phone VARCHAR(30) NULL,
    rt VARCHAR(10) NULL,
    rw VARCHAR(10) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL
) ENGINE=InnoDB;

CREATE TABLE election_voters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    election_id INT NOT NULL,
    voter_id INT NOT NULL,
    allowed_channel ENUM('tps','remote','both') NOT NULL DEFAULT 'tps',
    has_voted TINYINT(1) NOT NULL DEFAULT 0,
    voted_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY unique_election_voter (election_id, voter_id),

    FOREIGN KEY (election_id) REFERENCES elections(id)
        ON DELETE CASCADE,

    FOREIGN KEY (voter_id) REFERENCES voters(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE candidates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    election_id INT NOT NULL,
    number_order INT NOT NULL,
    name VARCHAR(120) NOT NULL,
    photo VARCHAR(255) NULL,
    vision TEXT NULL,
    mission TEXT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL,

    UNIQUE KEY unique_candidate_number (election_id, number_order),

    FOREIGN KEY (election_id) REFERENCES elections(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE ballots (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    election_id INT NOT NULL,
    candidate_id INT NOT NULL,
    ballot_code VARCHAR(64) NOT NULL UNIQUE,
    vote_channel ENUM('tps','remote') NOT NULL DEFAULT 'tps',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_ballots_election (election_id),
    INDEX idx_ballots_candidate (candidate_id),

    FOREIGN KEY (election_id) REFERENCES elections(id)
        ON DELETE CASCADE,

    FOREIGN KEY (candidate_id) REFERENCES candidates(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE remote_verifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    election_id INT NOT NULL,
    voter_id INT NOT NULL,
    verification_code VARCHAR(30) NOT NULL,
    ktp_photo_path VARCHAR(255) NULL,
    selfie_photo_path VARCHAR(255) NULL,
    status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    verified_by_1 INT NULL,
    verified_by_2 INT NULL,
    verified_at DATETIME NULL,
    reject_reason TEXT NULL,
    expires_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL,

    UNIQUE KEY unique_remote_request (election_id, voter_id),

    FOREIGN KEY (election_id) REFERENCES elections(id)
        ON DELETE CASCADE,

    FOREIGN KEY (voter_id) REFERENCES voters(id)
        ON DELETE CASCADE,

    FOREIGN KEY (verified_by_1) REFERENCES users(id)
        ON DELETE SET NULL,

    FOREIGN KEY (verified_by_2) REFERENCES users(id)
        ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE voting_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    election_id INT NOT NULL,
    voter_id INT NOT NULL,
    token_hash VARCHAR(255) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_token_election_voter (election_id, voter_id),

    FOREIGN KEY (election_id) REFERENCES elections(id)
        ON DELETE CASCADE,

    FOREIGN KEY (voter_id) REFERENCES voters(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE audit_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT NULL,
    ip_address VARCHAR(50) NULL,
    user_agent TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_audit_user (user_id),
    INDEX idx_audit_action (action),

    FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE SET NULL
) ENGINE=InnoDB;