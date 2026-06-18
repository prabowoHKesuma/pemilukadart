ALTER TABLE users
MODIFY role VARCHAR(100) NOT NULL DEFAULT 'viewer';

ALTER TABLE users
ADD UNIQUE KEY unique_users_username (username);