-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 18, 2026 at 09:14 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pemilukadart`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(50) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ballots`
--

CREATE TABLE `ballots` (
  `id` bigint(20) NOT NULL,
  `election_id` int(11) NOT NULL,
  `candidate_id` int(11) NOT NULL,
  `ballot_code` varchar(64) NOT NULL,
  `vote_channel` enum('tps','remote') NOT NULL DEFAULT 'tps',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `candidates`
--

CREATE TABLE `candidates` (
  `id` int(11) NOT NULL,
  `election_id` int(11) NOT NULL,
  `number_order` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `vision` text DEFAULT NULL,
  `mission` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `elections`
--

CREATE TABLE `elections` (
  `id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('draft','open','closed','finished') NOT NULL DEFAULT 'draft',
  `start_at` datetime DEFAULT NULL,
  `end_at` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `election_voters`
--

CREATE TABLE `election_voters` (
  `id` int(11) NOT NULL,
  `election_id` int(11) NOT NULL,
  `voter_id` int(11) NOT NULL,
  `allowed_channel` enum('tps','remote','both') NOT NULL DEFAULT 'tps',
  `has_voted` tinyint(1) NOT NULL DEFAULT 0,
  `voted_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `label` varchar(150) NOT NULL,
  `group_name` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `label`, `group_name`, `description`, `created_at`) VALUES
(1, 'manage_users', 'Kelola User', 'User Management', 'Tambah, edit, reset password, aktif/nonaktif user.', '2026-06-18 09:45:45'),
(2, 'manage_roles', 'Kelola Role', 'Role Management', 'Tambah, edit, dan mengatur role.', '2026-06-18 09:45:45'),
(3, 'manage_permissions', 'Kelola Permission', 'Role Management', 'Mengatur permission role.', '2026-06-18 09:45:45'),
(4, 'manage_elections', 'Kelola Pemilihan', 'Pemilihan', 'Tambah, edit, hapus, dan ubah status pemilihan.', '2026-06-18 09:45:45'),
(5, 'manage_candidates', 'Kelola Kandidat', 'Pemilihan', 'Tambah, edit, hapus kandidat.', '2026-06-18 09:45:45'),
(6, 'manage_voters', 'Kelola Master Pemilih', 'Pemilih', 'Tambah, edit, hapus, aktif/nonaktif pemilih.', '2026-06-18 09:45:45'),
(7, 'assign_voters', 'Assign Pemilih', 'Pemilih', 'Menambahkan pemilih ke pemilihan.', '2026-06-18 09:45:45'),
(8, 'tps_validate', 'Validasi TPS', 'TPS', 'Validasi pemilih TPS dan generate kode bilik.', '2026-06-18 09:45:45'),
(9, 'tps_booth_access', 'Akses Bilik TPS', 'TPS', 'Akses mode bilik TPS.', '2026-06-18 09:45:45'),
(10, 'manage_remote_verification', 'Kelola Verifikasi Remote', 'Remote Voting', 'Upload, approve, reject remote verification.', '2026-06-18 09:45:45'),
(11, 'manage_remote_token', 'Kelola Token Remote', 'Remote Voting', 'Generate dan revoke token remote.', '2026-06-18 09:45:45'),
(12, 'view_results', 'Lihat Hasil', 'Hasil', 'Melihat hasil pemilihan.', '2026-06-18 09:45:45'),
(13, 'print_results', 'Cetak Berita Acara', 'Hasil', 'Cetak dan export hasil pemilihan.', '2026-06-18 09:45:45'),
(14, 'view_audit_logs', 'Lihat Audit Log', 'Audit', 'Melihat audit log sistem.', '2026-06-18 09:45:45'),
(15, 'manage_system_settings', 'Kelola Setting Sistem', 'System', 'Mengelola konfigurasi sistem.', '2026-06-18 09:45:45');

-- --------------------------------------------------------

--
-- Table structure for table `remote_verifications`
--

CREATE TABLE `remote_verifications` (
  `id` int(11) NOT NULL,
  `election_id` int(11) NOT NULL,
  `voter_id` int(11) NOT NULL,
  `verification_code` varchar(30) NOT NULL,
  `ktp_photo_path` varchar(255) DEFAULT NULL,
  `selfie_photo_path` varchar(255) DEFAULT NULL,
  `consent_accepted` tinyint(1) NOT NULL DEFAULT 0,
  `consent_at` datetime DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `verified_by_1` int(11) DEFAULT NULL,
  `verified_by_2` int(11) DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `reject_reason` text DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `label` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `is_system` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `label`, `description`, `is_system`, `created_at`, `updated_at`) VALUES
(1, 'superadmin', 'Super Admin', 'Akses penuh seluruh sistem.', 1, '2026-06-18 09:45:45', NULL),
(2, 'panitia', 'Panitia', 'Operator pemilihan, validasi TPS, remote verification, dan token.', 1, '2026-06-18 09:45:45', NULL),
(3, 'saksi', 'Saksi', 'Akses lihat hasil dan berita acara.', 1, '2026-06-18 09:45:45', NULL),
(4, 'auditor', 'Auditor', 'Akses audit log dan hasil.', 1, '2026-06-18 09:45:45', NULL),
(5, 'viewer', 'Viewer', 'Akses baca terbatas.', 1, '2026-06-18 09:45:45', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES
(1, 1, 7, '2026-06-18 09:45:45'),
(2, 1, 5, '2026-06-18 09:45:45'),
(3, 1, 4, '2026-06-18 09:45:45'),
(4, 1, 3, '2026-06-18 09:45:45'),
(5, 1, 11, '2026-06-18 09:45:45'),
(6, 1, 10, '2026-06-18 09:45:45'),
(7, 1, 2, '2026-06-18 09:45:45'),
(8, 1, 15, '2026-06-18 09:45:45'),
(9, 1, 1, '2026-06-18 09:45:45'),
(10, 1, 6, '2026-06-18 09:45:45'),
(11, 1, 13, '2026-06-18 09:45:45'),
(12, 1, 9, '2026-06-18 09:45:45'),
(13, 1, 8, '2026-06-18 09:45:45'),
(14, 1, 14, '2026-06-18 09:45:45'),
(15, 1, 12, '2026-06-18 09:45:45'),
(16, 2, 7, '2026-06-18 09:45:45'),
(17, 2, 5, '2026-06-18 09:45:45'),
(18, 2, 4, '2026-06-18 09:45:45'),
(19, 2, 11, '2026-06-18 09:45:45'),
(20, 2, 10, '2026-06-18 09:45:45'),
(21, 2, 6, '2026-06-18 09:45:45'),
(22, 2, 13, '2026-06-18 09:45:45'),
(23, 2, 9, '2026-06-18 09:45:45'),
(24, 2, 8, '2026-06-18 09:45:45'),
(25, 2, 12, '2026-06-18 09:45:45'),
(31, 3, 13, '2026-06-18 09:45:45'),
(32, 3, 12, '2026-06-18 09:45:45'),
(34, 4, 13, '2026-06-18 09:45:45'),
(35, 4, 14, '2026-06-18 09:45:45'),
(36, 4, 12, '2026-06-18 09:45:45'),
(37, 5, 12, '2026-06-18 09:45:45');

-- --------------------------------------------------------

--
-- Table structure for table `tps_booth_tokens`
--

CREATE TABLE `tps_booth_tokens` (
  `id` bigint(20) NOT NULL,
  `election_id` int(11) NOT NULL,
  `election_voter_id` int(11) NOT NULL,
  `token_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `revoked_at` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(100) NOT NULL DEFAULT 'viewer',
  `role_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `username`, `password`, `role`, `role_id`, `is_active`, `last_login_at`, `created_at`, `updated_at`) VALUES
(1, 'Administrator', 'admin', '$2y$10$cnqm8wwPK0qxRzTdEOzM8uevHGOtmLF55iiGu3v7Ez9HHPAosLcq6', 'superadmin', 1, 1, '2026-06-18 14:01:06', '2026-06-17 06:45:51', NULL),
(2, 'Panitia 2', 'panitia2', '$2y$10$kP6RWydXzlFyG1lPg5/1Ku2xpoBlxBPNL3QIno2/LKzUuEgCirwre', 'panitia', 2, 1, '2026-06-17 14:35:03', '2026-06-17 10:42:18', NULL),
(3, 'Operator 01', 'operator01', '$2y$10$3DMyYQStg6Yl6Hz4YAfBfeMEEpTbBqeOXEQ97xiF0VZZ06mb1xd16', 'panitia', 2, 0, '2026-06-18 14:00:03', '2026-06-18 13:59:51', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `voters`
--

CREATE TABLE `voters` (
  `id` int(11) NOT NULL,
  `voter_code` varchar(50) NOT NULL,
  `name` varchar(120) NOT NULL,
  `nik_hash` varchar(255) DEFAULT NULL,
  `kk_hash` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `rt` varchar(10) DEFAULT NULL,
  `rw` varchar(10) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `voting_tokens`
--

CREATE TABLE `voting_tokens` (
  `id` int(11) NOT NULL,
  `election_id` int(11) NOT NULL,
  `voter_id` int(11) NOT NULL,
  `remote_verification_id` int(11) DEFAULT NULL,
  `token_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `revoked_at` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_audit_user` (`user_id`),
  ADD KEY `idx_audit_action` (`action`);

--
-- Indexes for table `ballots`
--
ALTER TABLE `ballots`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ballot_code` (`ballot_code`),
  ADD KEY `idx_ballots_election` (`election_id`),
  ADD KEY `idx_ballots_candidate` (`candidate_id`);

--
-- Indexes for table `candidates`
--
ALTER TABLE `candidates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_candidate_number` (`election_id`,`number_order`);

--
-- Indexes for table `elections`
--
ALTER TABLE `elections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `election_voters`
--
ALTER TABLE `election_voters`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_election_voter` (`election_id`,`voter_id`),
  ADD KEY `voter_id` (`voter_id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `remote_verifications`
--
ALTER TABLE `remote_verifications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_remote_request` (`election_id`,`voter_id`),
  ADD KEY `voter_id` (`voter_id`),
  ADD KEY `verified_by_1` (`verified_by_1`),
  ADD KEY `verified_by_2` (`verified_by_2`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_role_permission` (`role_id`,`permission_id`),
  ADD KEY `permission_id` (`permission_id`);

--
-- Indexes for table `tps_booth_tokens`
--
ALTER TABLE `tps_booth_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token_hash` (`token_hash`),
  ADD KEY `idx_tps_booth_election` (`election_id`),
  ADD KEY `idx_tps_booth_election_voter` (`election_voter_id`),
  ADD KEY `idx_tps_booth_expires_at` (`expires_at`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `unique_users_username` (`username`),
  ADD KEY `idx_users_role_id` (`role_id`);

--
-- Indexes for table `voters`
--
ALTER TABLE `voters`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `voter_code` (`voter_code`);

--
-- Indexes for table `voting_tokens`
--
ALTER TABLE `voting_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token_hash` (`token_hash`),
  ADD KEY `idx_token_election_voter` (`election_id`,`voter_id`),
  ADD KEY `voter_id` (`voter_id`),
  ADD KEY `idx_token_remote_verification` (`remote_verification_id`),
  ADD KEY `idx_token_expires_at` (`expires_at`),
  ADD KEY `fk_voting_tokens_created_by` (`created_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ballots`
--
ALTER TABLE `ballots`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `candidates`
--
ALTER TABLE `candidates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `elections`
--
ALTER TABLE `elections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `election_voters`
--
ALTER TABLE `election_voters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `remote_verifications`
--
ALTER TABLE `remote_verifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `tps_booth_tokens`
--
ALTER TABLE `tps_booth_tokens`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `voters`
--
ALTER TABLE `voters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `voting_tokens`
--
ALTER TABLE `voting_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `ballots`
--
ALTER TABLE `ballots`
  ADD CONSTRAINT `ballots_ibfk_1` FOREIGN KEY (`election_id`) REFERENCES `elections` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ballots_ibfk_2` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `candidates`
--
ALTER TABLE `candidates`
  ADD CONSTRAINT `candidates_ibfk_1` FOREIGN KEY (`election_id`) REFERENCES `elections` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `elections`
--
ALTER TABLE `elections`
  ADD CONSTRAINT `elections_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `election_voters`
--
ALTER TABLE `election_voters`
  ADD CONSTRAINT `election_voters_ibfk_1` FOREIGN KEY (`election_id`) REFERENCES `elections` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `election_voters_ibfk_2` FOREIGN KEY (`voter_id`) REFERENCES `voters` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `remote_verifications`
--
ALTER TABLE `remote_verifications`
  ADD CONSTRAINT `remote_verifications_ibfk_1` FOREIGN KEY (`election_id`) REFERENCES `elections` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `remote_verifications_ibfk_2` FOREIGN KEY (`voter_id`) REFERENCES `voters` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `remote_verifications_ibfk_3` FOREIGN KEY (`verified_by_1`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `remote_verifications_ibfk_4` FOREIGN KEY (`verified_by_2`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tps_booth_tokens`
--
ALTER TABLE `tps_booth_tokens`
  ADD CONSTRAINT `tps_booth_tokens_ibfk_1` FOREIGN KEY (`election_id`) REFERENCES `elections` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tps_booth_tokens_ibfk_2` FOREIGN KEY (`election_voter_id`) REFERENCES `election_voters` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tps_booth_tokens_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_role_id` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `voting_tokens`
--
ALTER TABLE `voting_tokens`
  ADD CONSTRAINT `fk_voting_tokens_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_voting_tokens_remote_verification` FOREIGN KEY (`remote_verification_id`) REFERENCES `remote_verifications` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `voting_tokens_ibfk_1` FOREIGN KEY (`election_id`) REFERENCES `elections` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `voting_tokens_ibfk_2` FOREIGN KEY (`voter_id`) REFERENCES `voters` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
