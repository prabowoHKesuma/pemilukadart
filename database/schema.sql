-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 17, 2026 at 10:36 AM
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

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'logout', 'User logout: admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 08:10:47'),
(2, 1, 'login_success', 'User berhasil login: admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 08:10:49'),
(3, 1, 'voter_create', 'Menambahkan pemilih: test pemilih 1 dengan kode PM-260617-95BEBF', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 08:12:48'),
(4, 1, 'voter_update', 'Memperbarui pemilih ID 1: test pemilih 1 dengan kode PM-260617-95BEBF', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 08:13:07'),
(5, 1, 'election_create', 'Membuat pemilihan: test pemilihan rt 11 dengan status draft', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 08:14:02'),
(6, 1, 'candidate_create', 'Menambahkan kandidat \"test kandidat 1\" nomor urut 1 pada election ID 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 08:15:37'),
(7, 1, 'candidate_create', 'Menambahkan kandidat \"test kandidat 2\" nomor urut 2 pada election ID 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 08:16:18'),
(8, 1, 'candidate_update', 'Memperbarui kandidat ID 1 menjadi \"test kandidat 1\" nomor urut 1 pada election ID 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 08:16:49'),
(9, 1, 'election_status_update', 'Mengubah status pemilihan ID 1 dari draft ke open', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 08:17:03'),
(10, 1, 'election_status_update', 'Mengubah status pemilihan ID 1 dari open ke draft', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 08:17:23'),
(11, 1, 'voter_create', 'Menambahkan pemilih: test pemilih 2 dengan kode PM-260617-223359', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 08:18:32'),
(12, 1, 'voter_create', 'Menambahkan pemilih: test pemilih 3 dengan kode PM-260617-5194E6', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 08:19:06'),
(13, 1, 'voter_update', 'Memperbarui pemilih ID 1: test pemilih 1 dengan kode PM-260617-95BEBF', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 08:19:15'),
(14, 1, 'election_voter_assign', 'Menambahkan 3 pemilih ke election ID 1 dengan channel tps. Dilewati: 0', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 08:19:45'),
(15, 1, 'election_voter_channel_update', 'Mengubah channel election_voter ID 2 pada election ID 1 menjadi remote', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 08:19:57'),
(16, 1, 'election_voter_channel_update', 'Mengubah channel election_voter ID 3 pada election ID 1 menjadi both', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 08:20:00'),
(17, 1, 'election_status_update', 'Mengubah status pemilihan ID 1 dari draft ke open', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 08:20:34'),
(18, 1, 'vote_tps_success', 'Suara TPS berhasil disimpan untuk election ID 1.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 08:20:57'),
(19, 1, 'vote_tps_success', 'Suara TPS berhasil disimpan untuk election ID 1.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 08:21:29'),
(20, 1, 'result_view', 'Membuka detail hasil election ID 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 08:21:41'),
(21, 1, 'result_view', 'Membuka detail hasil election ID 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 08:23:07'),
(22, 1, 'result_view', 'Membuka detail hasil election ID 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 08:24:52'),
(23, 1, 'remote_verification_create', 'Membuat request verifikasi remote untuk election ID 1 dengan kode RV-001-3178', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 10:43:37'),
(24, 1, 'remote_verification_upload', 'Upload foto KTP dan selfie untuk remote verification ID 1 pada election ID 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 10:45:44'),
(25, 1, 'remote_verification_approve_1', 'Approval pertama remote verification ID 1 pada election ID 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 10:45:55'),
(26, 1, 'logout', 'User logout: admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 10:46:09'),
(27, 2, 'login_failed', 'Percobaan login gagal untuk username: panitia2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 10:46:25'),
(28, 2, 'login_success', 'User berhasil login: panitia2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 10:46:51'),
(29, 2, 'remote_verification_approve_2', 'Approval kedua remote verification ID 1 pada election ID 1. Status menjadi approved.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 10:47:08'),
(30, 2, 'logout', 'User logout: panitia2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 10:48:29'),
(31, 1, 'login_success', 'User berhasil login: admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 10:48:34'),
(32, 1, 'result_view', 'Membuka detail hasil election ID 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 10:52:48'),
(33, 1, 'result_view', 'Membuka detail hasil election ID 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 13:11:02'),
(34, 1, 'remote_token_generate', 'Generate token voting remote untuk election ID 1 dan remote verification ID 1. Expired: 2026-06-17 14:04:26', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 13:34:26'),
(35, NULL, 'vote_remote_success', 'Suara remote berhasil disimpan untuk election ID 1.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 13:35:11'),
(36, 1, 'result_view', 'Membuka detail hasil election ID 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 13:35:28'),
(37, 1, 'voter_create', 'Menambahkan pemilih: test pemilih 4 dengan kode PM-260617-DB0578', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 13:41:14'),
(38, 1, 'election_status_update', 'Mengubah status pemilihan ID 1 dari open ke draft', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 13:41:28'),
(39, 1, 'election_voter_assign', 'Menambahkan 1 pemilih ke election ID 1 dengan channel both. Dilewati: 0', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 13:41:45'),
(40, 1, 'election_status_update', 'Mengubah status pemilihan ID 1 dari draft ke open', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 13:41:57'),
(41, 1, 'remote_verification_create', 'Membuat request verifikasi remote untuk election ID 1 dengan kode RV-001-6427', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 13:42:11'),
(42, 1, 'remote_verification_upload', 'Upload foto KTP dan selfie untuk remote verification ID 2 pada election ID 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 13:42:45'),
(43, 1, 'remote_verification_approve_1', 'Approval pertama remote verification ID 2 pada election ID 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 13:42:49'),
(44, 1, 'logout', 'User logout: admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 13:42:52'),
(45, 2, 'login_success', 'User berhasil login: panitia2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 13:42:56'),
(46, 2, 'remote_verification_approve_2', 'Approval kedua remote verification ID 2 pada election ID 1. Status menjadi approved.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 13:43:07'),
(47, 2, 'logout', 'User logout: panitia2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 13:43:09'),
(48, 1, 'login_success', 'User berhasil login: admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 13:43:15'),
(49, 1, 'remote_token_generate', 'Generate token voting remote untuk election ID 1 dan remote verification ID 2. Expired: 2026-06-17 13:48:41', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 13:43:41'),
(50, 1, 'remote_token_revoke', 'Revoke token voting remote ID 2 pada election ID 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 13:43:54'),
(51, 1, 'remote_token_generate', 'Generate token voting remote untuk election ID 1 dan remote verification ID 2. Expired: 2026-06-17 13:49:13', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 13:44:13'),
(52, 1, 'remote_token_generate', 'Generate token voting remote untuk election ID 1 dan remote verification ID 2. Expired: 2026-06-17 13:54:59', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 13:49:59'),
(53, 1, 'election_status_update', 'Mengubah status pemilihan ID 1 dari open ke closed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 13:50:06'),
(54, 1, 'election_status_update', 'Mengubah status pemilihan ID 1 dari closed ke open', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 13:50:30'),
(55, 1, 'vote_remote_success', 'Suara remote berhasil disimpan untuk election ID 1.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 13:50:53'),
(56, 1, 'result_view', 'Membuka detail hasil election ID 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 13:51:08'),
(57, 1, 'election_status_update', 'Mengubah status pemilihan ID 1 dari open ke closed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 13:51:19'),
(58, 1, 'result_view', 'Membuka detail hasil election ID 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 13:51:27'),
(59, 1, 'election_status_update', 'Mengubah status pemilihan ID 1 dari closed ke finished', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 13:51:37'),
(60, 1, 'result_view', 'Membuka detail hasil election ID 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 13:51:42'),
(61, 1, 'result_view', 'Membuka detail hasil election ID 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 14:06:32'),
(62, 1, 'result_print', 'Mencetak berita acara hasil election ID 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 14:06:37'),
(63, 1, 'result_print', 'Mencetak berita acara hasil election ID 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 14:08:06'),
(64, 1, 'result_print', 'Mencetak berita acara hasil election ID 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 14:09:17'),
(65, 1, 'result_export_csv', 'Export CSV hasil election ID 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 14:13:27'),
(66, 1, 'election_status_update', 'Mengubah status pemilihan ID 1 dari finished ke open', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 14:18:19'),
(67, 1, 'election_status_update', 'Mengubah status pemilihan ID 1 dari open ke finished', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 14:30:37'),
(68, 1, 'election_create', 'Membuat pemilihan: test pemilihan lagi dengan status draft', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 14:31:24'),
(69, 1, 'candidate_create', 'Menambahkan kandidat \"test kandidat 1\" nomor urut 1 pada election ID 2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 14:31:58'),
(70, 1, 'candidate_create', 'Menambahkan kandidat \"test kandidat 2\" nomor urut 2 pada election ID 2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 14:32:12'),
(71, 1, 'election_voter_assign', 'Menambahkan 4 pemilih ke election ID 2 dengan channel tps. Dilewati: 0', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 14:33:31'),
(72, 1, 'election_voter_channel_update', 'Mengubah channel election_voter ID 7 pada election ID 2 menjadi remote', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 14:33:36'),
(73, 1, 'election_voter_channel_update', 'Mengubah channel election_voter ID 8 pada election ID 2 menjadi both', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 14:33:39'),
(74, 1, 'election_voter_channel_update', 'Mengubah channel election_voter ID 7 pada election ID 2 menjadi tps', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 14:33:46'),
(75, 1, 'remote_verification_create', 'Membuat request verifikasi remote untuk election ID 2 dengan kode RV-002-2901', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 14:34:23'),
(76, 1, 'remote_verification_upload', 'Upload foto KTP dan selfie untuk remote verification ID 3 pada election ID 2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 14:34:54'),
(77, 1, 'remote_verification_approve_1', 'Approval pertama remote verification ID 3 pada election ID 2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 14:34:58'),
(78, 1, 'logout', 'User logout: admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 14:34:59'),
(79, 2, 'login_success', 'User berhasil login: panitia2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 14:35:03'),
(80, 2, 'remote_verification_approve_2', 'Approval kedua remote verification ID 3 pada election ID 2. Status menjadi approved.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 14:35:13'),
(81, 2, 'logout', 'User logout: panitia2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 14:35:17'),
(82, 1, 'login_success', 'User berhasil login: admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 14:35:21'),
(83, 1, 'result_view', 'Membuka detail hasil election ID 2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 14:35:31'),
(84, 1, 'election_status_update', 'Mengubah status pemilihan ID 2 dari draft ke open', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 14:36:01'),
(85, 1, 'remote_token_generate', 'Generate token voting remote untuk election ID 2 dan remote verification ID 3. Expired: 2026-06-17 15:06:13', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 14:36:13'),
(86, 1, 'tps_booth_code_generate', 'Generate kode bilik TPS untuk election ID 2. Expired: 2026-06-17 15:24:24', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 15:19:24'),
(87, 1, 'vote_tps_booth_success', 'Suara TPS via bilik berhasil disimpan untuk election ID 2.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 15:20:00'),
(88, 1, 'remote_token_generate', 'Generate token voting remote untuk election ID 2 dan remote verification ID 3. Expired: 2026-06-17 16:22:03', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 15:22:03'),
(89, 1, 'tps_booth_code_generate', 'Generate kode bilik TPS untuk election ID 2. Expired: 2026-06-17 15:27:45', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 15:22:45'),
(90, 1, 'vote_tps_booth_success', 'Suara TPS via bilik berhasil disimpan untuk election ID 2.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 15:22:57'),
(91, 1, 'tps_booth_code_generate', 'Generate kode bilik TPS untuk election ID 2. Expired: 2026-06-17 15:33:08', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 15:23:08'),
(92, 1, 'vote_tps_booth_success', 'Suara TPS via bilik berhasil disimpan untuk election ID 2.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 15:23:18'),
(93, 1, 'vote_remote_success', 'Suara remote berhasil disimpan untuk election ID 2.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 15:23:42'),
(94, 1, 'election_status_update', 'Mengubah status pemilihan ID 2 dari open ke closed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 15:23:59'),
(95, 1, 'result_view', 'Membuka detail hasil election ID 2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 15:24:11'),
(96, 1, 'result_print', 'Mencetak berita acara hasil election ID 2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 15:24:23'),
(97, 1, 'result_view', 'Membuka detail hasil election ID 2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 15:24:37'),
(98, 1, 'election_status_update', 'Mengubah status pemilihan ID 2 dari closed ke finished', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 15:24:43'),
(99, 1, 'result_view', 'Membuka detail hasil election ID 2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-17 15:24:46');

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

--
-- Dumping data for table `ballots`
--

INSERT INTO `ballots` (`id`, `election_id`, `candidate_id`, `ballot_code`, `vote_channel`, `created_at`) VALUES
(1, 1, 1, '8bb27af114f6e89e41be36acff77d177', 'tps', '2026-06-17 08:20:57'),
(2, 1, 2, '7382d4b6ad1537fbf844280191476296', 'tps', '2026-06-17 08:21:29'),
(3, 1, 2, 'fdd1cb06f6a831173e730ef8b43e9ba7', 'remote', '2026-06-17 13:35:11'),
(4, 1, 2, '361a2a17e6b5fe518c02d3eb728225f4', 'remote', '2026-06-17 13:50:53'),
(5, 2, 4, 'bb0e1f2189ea9d38122f4f55e351651e', 'tps', '2026-06-17 15:20:00'),
(6, 2, 3, 'c11c3656eb5be3c4417ab1d3e206b065', 'tps', '2026-06-17 15:22:57'),
(7, 2, 4, '6fbec044be0a78f69a7e4bba621dee76', 'tps', '2026-06-17 15:23:18'),
(8, 2, 3, '7b047a239899716367269cb8e6e04be5', 'remote', '2026-06-17 15:23:42');

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

--
-- Dumping data for table `candidates`
--

INSERT INTO `candidates` (`id`, `election_id`, `number_order`, `name`, `photo`, `vision`, `mission`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'test kandidat 1', 'assets/uploads/candidates/candidate_20260617081537_9b72a49c07b0bdce.png', 'test visi kandidat 1 edit', 'test misi kandidat 1', 1, '2026-06-17 08:15:37', '2026-06-17 08:16:49'),
(2, 1, 2, 'test kandidat 2', 'assets/uploads/candidates/candidate_20260617081618_6c84adf071bab421.png', 'test visi kandidat 2', 'test misi kandidat 2', 1, '2026-06-17 08:16:18', NULL),
(3, 2, 1, 'test kandidat 1', 'assets/uploads/candidates/candidate_20260617143158_847d82ed2e6d337e.png', 'test', 'test', 1, '2026-06-17 14:31:58', NULL),
(4, 2, 2, 'test kandidat 2', 'assets/uploads/candidates/candidate_20260617143212_742d66d64878eb4a.png', 'test', 'test', 1, '2026-06-17 14:32:12', NULL);

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

--
-- Dumping data for table `elections`
--

INSERT INTO `elections` (`id`, `title`, `description`, `status`, `start_at`, `end_at`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'test pemilihan rt 11', 'ini masih test', 'finished', '2026-06-17 08:13:00', '2026-06-17 23:16:00', 1, '2026-06-17 08:14:02', '2026-06-17 14:30:37'),
(2, 'test pemilihan lagi', 'test', 'finished', '2026-06-17 14:30:00', '2026-06-17 20:31:00', 1, '2026-06-17 14:31:24', '2026-06-17 15:24:43');

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

--
-- Dumping data for table `election_voters`
--

INSERT INTO `election_voters` (`id`, `election_id`, `voter_id`, `allowed_channel`, `has_voted`, `voted_at`, `created_at`) VALUES
(1, 1, 1, 'tps', 1, '2026-06-17 08:20:57', '2026-06-17 08:19:45'),
(2, 1, 2, 'remote', 1, '2026-06-17 13:35:11', '2026-06-17 08:19:45'),
(3, 1, 3, 'both', 1, '2026-06-17 08:21:29', '2026-06-17 08:19:45'),
(4, 1, 4, 'both', 1, '2026-06-17 13:50:53', '2026-06-17 13:41:45'),
(5, 2, 1, 'tps', 1, '2026-06-17 15:20:00', '2026-06-17 14:33:31'),
(6, 2, 2, 'tps', 1, '2026-06-17 15:22:57', '2026-06-17 14:33:31'),
(7, 2, 3, 'tps', 1, '2026-06-17 15:23:18', '2026-06-17 14:33:31'),
(8, 2, 4, 'both', 1, '2026-06-17 15:23:42', '2026-06-17 14:33:31');

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

--
-- Dumping data for table `remote_verifications`
--

INSERT INTO `remote_verifications` (`id`, `election_id`, `voter_id`, `verification_code`, `ktp_photo_path`, `selfie_photo_path`, `consent_accepted`, `consent_at`, `status`, `verified_by_1`, `verified_by_2`, `verified_at`, `reject_reason`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 'RV-001-3178', 'storage/private/verifications/election_1/ktp_20260617104544_12c02c3693a9ed5bfae87e60.jpg', 'storage/private/verifications/election_1/selfie_20260617104544_3c74e56f2bce4ea22d083bed.jpg', 1, '2026-06-17 10:45:44', 'approved', 1, 2, '2026-06-17 10:47:08', NULL, '2026-06-19 10:43:37', '2026-06-17 10:43:37', '2026-06-17 10:47:08'),
(2, 1, 4, 'RV-001-6427', 'storage/private/verifications/election_1/ktp_20260617134245_4fceff6588622f8aa1ddb261.jpeg', 'storage/private/verifications/election_1/selfie_20260617134245_66a1141091533e46d62f578a.png', 1, '2026-06-17 13:42:45', 'approved', 1, 2, '2026-06-17 13:43:07', NULL, '2026-06-19 13:42:11', '2026-06-17 13:42:11', '2026-06-17 13:43:07'),
(3, 2, 4, 'RV-002-2901', 'storage/private/verifications/election_2/ktp_20260617143454_f6da7aaf1ed21999a10e7e76.jpg', 'storage/private/verifications/election_2/selfie_20260617143454_061b675fd194a1f3328a126c.png', 1, '2026-06-17 14:34:54', 'approved', 1, 2, '2026-06-17 14:35:13', NULL, '2026-06-19 14:34:23', '2026-06-17 14:34:23', '2026-06-17 14:35:13');

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

--
-- Dumping data for table `tps_booth_tokens`
--

INSERT INTO `tps_booth_tokens` (`id`, `election_id`, `election_voter_id`, `token_hash`, `expires_at`, `used_at`, `revoked_at`, `created_by`, `created_at`) VALUES
(1, 2, 5, '702d7f68435d39ea9c8be0d39beada134c219a44ca571ce42b9308e2a558ec29', '2026-06-17 15:24:24', '2026-06-17 15:20:00', NULL, 1, '2026-06-17 15:19:24'),
(2, 2, 6, 'd512203321b50739e2a12db2b2f0d23abf8e6f8069a3e12e29d8575aaa53ac08', '2026-06-17 15:27:45', '2026-06-17 15:22:57', NULL, 1, '2026-06-17 15:22:45'),
(3, 2, 7, '083e1def9ba13b0542d5f488f76c8f7cd215ec65c623a260cf543a569ec95a06', '2026-06-17 15:33:08', '2026-06-17 15:23:18', NULL, 1, '2026-06-17 15:23:08');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('superadmin','panitia','saksi') NOT NULL DEFAULT 'panitia',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `username`, `password`, `role`, `is_active`, `last_login_at`, `created_at`, `updated_at`) VALUES
(1, 'Administrator', 'admin', '$2y$10$cnqm8wwPK0qxRzTdEOzM8uevHGOtmLF55iiGu3v7Ez9HHPAosLcq6', 'superadmin', 1, '2026-06-17 14:35:21', '2026-06-17 06:45:51', NULL),
(2, 'Panitia 2', 'panitia2', '$2y$10$kP6RWydXzlFyG1lPg5/1Ku2xpoBlxBPNL3QIno2/LKzUuEgCirwre', 'panitia', 1, '2026-06-17 14:35:03', '2026-06-17 10:42:18', NULL);

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

--
-- Dumping data for table `voters`
--

INSERT INTO `voters` (`id`, `voter_code`, `name`, `nik_hash`, `kk_hash`, `address`, `phone`, `rt`, `rw`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'PM-260617-95BEBF', 'test pemilih 1', 'c4d1c0a576622bde87f1821c4956797b348a06315042bfc7d5c82e71a6258c08', 'c4d1c0a576622bde87f1821c4956797b348a06315042bfc7d5c82e71a6258c08', 'test alamat pemilih', '081234567890', '011', '006', 1, '2026-06-17 08:12:48', '2026-06-17 08:19:15'),
(2, 'PM-260617-223359', 'test pemilih 2', '2f0accee1fa27e2bac437ebdd720d28c810d89f4cdea44c713022afb4fa313bf', '2f0accee1fa27e2bac437ebdd720d28c810d89f4cdea44c713022afb4fa313bf', 'test alamat pemilih 2', '081234567899', '011', '006', 1, '2026-06-17 08:18:32', NULL),
(3, 'PM-260617-5194E6', 'test pemilih 3', 'a31effc4ef3f3132e35822b1359f475e58e218c3aa6900429affdad23a9ef48d', 'a31effc4ef3f3132e35822b1359f475e58e218c3aa6900429affdad23a9ef48d', 'test alamat pemilih 3', '081234567899', '011', '006', 1, '2026-06-17 08:19:06', NULL),
(4, 'PM-260617-DB0578', 'test pemilih 4', '8b1fc3b7ae335f92d45efb5d720e139490dceb530f8801ec12798809e3072987', '8b1fc3b7ae335f92d45efb5d720e139490dceb530f8801ec12798809e3072987', 'test 4', '081234567892', '011', '006', 1, '2026-06-17 13:41:14', NULL);

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
-- Dumping data for table `voting_tokens`
--

INSERT INTO `voting_tokens` (`id`, `election_id`, `voter_id`, `remote_verification_id`, `token_hash`, `expires_at`, `used_at`, `revoked_at`, `created_by`, `created_at`) VALUES
(1, 1, 2, 1, '964c34a2c65d22ca763cc3fae5f021c139be6b44d2ffd478e4c99da2aa7266f7', '2026-06-17 14:04:26', '2026-06-17 13:35:11', NULL, 1, '2026-06-17 13:34:26'),
(2, 1, 4, 2, '64496e49fc7a1aa8040078a112e75919c871df89eab8954fafaaedb268b4d8a8', '2026-06-17 13:48:41', NULL, '2026-06-17 13:43:54', 1, '2026-06-17 13:43:41'),
(3, 1, 4, 2, 'eb85bca050717b10bdad721a833311221e8ad9a4316036218301cebc9f84805c', '2026-06-17 13:49:13', NULL, NULL, 1, '2026-06-17 13:44:13'),
(4, 1, 4, 2, 'fc7430c61ba84dccf2934f0ccd1a662515c1f9a1ebf9d7ead3673b5225bd3f8a', '2026-06-17 13:54:59', '2026-06-17 13:50:53', NULL, 1, '2026-06-17 13:49:59'),
(5, 2, 4, 3, '4a4aaf51e0a3a890d2be274f1dd848fe41e267d6ca5196d82a33ee9133304340', '2026-06-17 15:06:13', NULL, NULL, 1, '2026-06-17 14:36:13'),
(6, 2, 4, 3, '515e9bcc9db6d24c6189136bddd351db72aff0d6d66511294a704f3b0fca7106', '2026-06-17 16:22:03', '2026-06-17 15:23:42', NULL, 1, '2026-06-17 15:22:03');

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
-- Indexes for table `remote_verifications`
--
ALTER TABLE `remote_verifications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_remote_request` (`election_id`,`voter_id`),
  ADD KEY `voter_id` (`voter_id`),
  ADD KEY `verified_by_1` (`verified_by_1`),
  ADD KEY `verified_by_2` (`verified_by_2`);

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
  ADD UNIQUE KEY `username` (`username`);

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
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100;

--
-- AUTO_INCREMENT for table `ballots`
--
ALTER TABLE `ballots`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `candidates`
--
ALTER TABLE `candidates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `elections`
--
ALTER TABLE `elections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `election_voters`
--
ALTER TABLE `election_voters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `remote_verifications`
--
ALTER TABLE `remote_verifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tps_booth_tokens`
--
ALTER TABLE `tps_booth_tokens`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `voters`
--
ALTER TABLE `voters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `voting_tokens`
--
ALTER TABLE `voting_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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
-- Constraints for table `tps_booth_tokens`
--
ALTER TABLE `tps_booth_tokens`
  ADD CONSTRAINT `tps_booth_tokens_ibfk_1` FOREIGN KEY (`election_id`) REFERENCES `elections` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tps_booth_tokens_ibfk_2` FOREIGN KEY (`election_voter_id`) REFERENCES `election_voters` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tps_booth_tokens_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

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
