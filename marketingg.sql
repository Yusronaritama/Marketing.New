-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 03, 2025 at 12:29 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `marketing`
--

-- --------------------------------------------------------

--
-- Table structure for table `batches`
--

CREATE TABLE `batches` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_questions` int NOT NULL,
  `answers_per_question` int NOT NULL,
  `duration` int NOT NULL,
  `expired_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `batches`
--

INSERT INTO `batches` (`id`, `uuid`, `batch_name`, `total_questions`, `answers_per_question`, `duration`, `expired_at`, `created_at`, `updated_at`) VALUES
(2, '4f441878-2280-4fa0-beb0-e923d03112e8', 'dadadad', 40, 4, 120, '2025-10-21 13:35:00', '2025-10-19 23:35:57', '2025-10-19 23:35:57'),
(3, '6d2f627d-67c6-48ad-adf6-a45d4d77f580', 'asdsa', 2, 3, 2, '2025-10-20 14:05:00', '2025-10-20 00:04:09', '2025-10-20 00:04:09');

-- --------------------------------------------------------

--
-- Table structure for table `biodatas`
--

CREATE TABLE `biodatas` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `nama_lengkap` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alamat` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggal_lahir` date NOT NULL,
  `nama_ibu` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_ayah` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `asal_sekolah` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jurusan_sekolah` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `biodatas`
--

INSERT INTO `biodatas` (`id`, `user_id`, `nama_lengkap`, `alamat`, `tanggal_lahir`, `nama_ibu`, `nama_ayah`, `asal_sekolah`, `jurusan_sekolah`, `created_at`, `updated_at`) VALUES
(1, 151, 'asdasd', 'asdsad', '2004-06-16', 'asda', 'dsfcf', 'dalnl', 'lkjbsasdla', '2025-10-30 03:41:42', '2025-10-30 03:43:02');

-- --------------------------------------------------------

--
-- Table structure for table `bni_billings`
--

CREATE TABLE `bni_billings` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `trx_id` varchar(50) DEFAULT NULL,
  `virtual_account` varchar(30) DEFAULT NULL,
  `trx_amount` decimal(15,2) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `payment_ntb` varchar(50) DEFAULT NULL,
  `datetime_payment` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bni_callbacks`
--

CREATE TABLE `bni_callbacks` (
  `id` bigint UNSIGNED NOT NULL,
  `trx_id` varchar(50) DEFAULT NULL,
  `virtual_account` varchar(30) DEFAULT NULL,
  `payment_ntb` varchar(50) DEFAULT NULL,
  `payment_amount` decimal(15,2) DEFAULT NULL,
  `datetime_payment` datetime DEFAULT NULL,
  `raw_data` text,
  `decrypted_data` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bni_configs`
--

CREATE TABLE `bni_configs` (
  `id` int NOT NULL,
  `client_id` varchar(50) NOT NULL,
  `secret_key` varchar(255) NOT NULL,
  `api_url` varchar(255) NOT NULL,
  `va_prefix` varchar(50) DEFAULT NULL,
  `amount` decimal(10,0) NOT NULL DEFAULT '300000',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `bni_configs`
--

INSERT INTO `bni_configs` (`id`, `client_id`, `secret_key`, `api_url`, `va_prefix`, `amount`, `created_at`, `updated_at`) VALUES
(2, '41625', 'd2ea3883241ee95f7d0d6fd1de085c41', 'https://api.bni-ecollection.com/', '98841625', '300000', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel-cache-allancechain@gmail.com|103.73.74.181', 'i:1;', 1759300779),
('laravel-cache-allancechain@gmail.com|103.73.74.181:timer', 'i:1759300779;', 1759300779);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `calendar_events`
--

CREATE TABLE `calendar_events` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start` datetime NOT NULL,
  `end` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start` datetime NOT NULL,
  `end` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `start`, `end`, `created_at`, `updated_at`) VALUES
(2, 'dsfgs', '2025-10-25 21:31:00', '2025-10-30 21:34:00', '2025-10-25 07:29:59', '2025-10-25 07:29:59'),
(3, '1qw', '2025-10-25 21:41:00', '2025-11-08 21:37:00', '2025-10-25 07:34:41', '2025-10-25 07:34:41'),
(4, 'sa', '2025-10-25 21:39:00', '2025-11-05 21:37:00', '2025-10-25 07:35:11', '2025-10-25 07:35:11');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `master_ereg`
--

CREATE TABLE `master_ereg` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `whatsapp` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gmail` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kode_promo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jurusan1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jurusan2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_kk` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_ijazah` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `step_completed` int NOT NULL DEFAULT '0',
  `status` enum('in_progress','data_confirmed','completed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'in_progress',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `master_ereg`
--

INSERT INTO `master_ereg` (`id`, `user_id`, `nama`, `whatsapp`, `gmail`, `kode_promo`, `jurusan1`, `jurusan2`, `file_kk`, `file_ijazah`, `step_completed`, `status`, `created_at`, `updated_at`) VALUES
(1, 151, 'Muhammad Ikhsan N', '6285156052388', 'allancechain@gmail.com', NULL, 'Manajemen', 'Magister Manajemen', 'uploads/kk/3udQIqro4KR26ZTfr4XjJ4OEwf6C6zSiOhajbJ7R.pdf', 'uploads/ijazah/C2lX8LbJAb4UllO4cTVthmONX7NwnNFgUv0BzEbQ.pdf', 5, 'completed', '2025-11-02 15:22:41', '2025-11-02 16:37:33'),
(2, 161, 'Muhammad Ikhsan N', '62851560882638', 'adad123312344asda@gmail.com', NULL, 'Manajemen', 'Akuntansi', NULL, NULL, 2, 'in_progress', '2025-11-02 16:26:31', '2025-11-02 16:26:43');

-- --------------------------------------------------------

--
-- Table structure for table `master_moodle`
--

CREATE TABLE `master_moodle` (
  `id` bigint UNSIGNED NOT NULL,
  `master_user_id` bigint UNSIGNED NOT NULL,
  `moodle_user_id` bigint UNSIGNED DEFAULT NULL COMMENT 'ID user dari Moodle',
  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Username yang dikirim ke Moodle (VA Number)',
  `status` enum('success','failed') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Status pembuatan user di Moodle',
  `enrol_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Status pendaftaran ke course',
  `error_message` text COLLATE utf8mb4_unicode_ci COMMENT 'Pesan error jika gagal',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `master_moodle`
--

INSERT INTO `master_moodle` (`id`, `master_user_id`, `moodle_user_id`, `username`, `status`, `enrol_status`, `error_message`, `created_at`, `updated_at`) VALUES
(8, 6, 39887, '9884162526000144', 'success', 'enrolled', NULL, '2025-10-21 19:37:58', '2025-10-21 19:37:58'),
(9, 7, 39888, '9884162526000145', 'success', 'enrolled', NULL, '2025-10-21 21:16:13', '2025-10-21 21:16:13'),
(12, 13, 39893, '9884162526000149', 'success', 'enrolled', NULL, '2025-10-22 19:54:34', '2025-10-22 19:54:34'),
(13, 14, 39894, '9884162526000150', 'success', 'enrolled', NULL, '2025-10-22 20:39:36', '2025-10-22 20:39:36'),
(14, 15, 39895, '9884162526000151', 'success', 'enrolled', NULL, '2025-10-22 23:17:09', '2025-10-22 23:17:09'),
(15, 16, 39903, '9884162526000153', 'success', 'enrolled', NULL, '2025-10-28 21:05:30', '2025-10-28 21:05:30'),
(16, 17, 39904, '9884162526000154', 'success', 'enrolled', NULL, '2025-10-28 21:23:45', '2025-10-28 21:23:45'),
(17, 18, NULL, 'FREE-1761712515-155', 'failed', 'not_enrolled', 'Nilai parameter tidak valid terdeteksi', '2025-10-28 21:35:16', '2025-10-28 21:35:16'),
(18, 19, 39905, '9884162526000156', 'success', 'enrolled', NULL, '2025-10-28 21:39:40', '2025-10-28 21:39:40'),
(19, 20, 39906, '9884162526000157', 'success', 'enrolled', NULL, '2025-10-28 21:42:24', '2025-10-28 21:42:24'),
(20, 21, 39911, '9884162526000158', 'success', 'enrolled', NULL, '2025-10-30 00:58:08', '2025-10-30 00:58:08'),
(21, 22, 39912, '9884162526000159', 'success', 'enrolled', NULL, '2025-10-31 02:52:34', '2025-10-31 02:52:34'),
(22, 23, 39915, '9884162526000160', 'success', 'enrolled', NULL, '2025-10-31 03:39:38', '2025-10-31 03:39:38'),
(23, 24, 39916, '9884162526000161', 'success', 'enrolled', NULL, '2025-10-31 03:41:10', '2025-10-31 03:41:10');

-- --------------------------------------------------------

--
-- Table structure for table `master_users`
--

CREATE TABLE `master_users` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `gelombang` int DEFAULT NULL,
  `plain_password` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `trx_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `va_number` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `va_status` enum('pending','paid','expired','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `promo_code_used` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `va_amount` decimal(15,2) DEFAULT NULL,
  `original_amount` decimal(15,2) DEFAULT NULL,
  `discount_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `discount_percentage` int NOT NULL DEFAULT '0',
  `va_expired_at` datetime DEFAULT NULL,
  `payment_date` datetime DEFAULT NULL,
  `payment_amount` decimal(15,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `master_users`
--

INSERT INTO `master_users` (`id`, `user_id`, `gelombang`, `plain_password`, `password`, `trx_id`, `va_number`, `va_status`, `promo_code_used`, `va_amount`, `original_amount`, `discount_amount`, `discount_percentage`, `va_expired_at`, `payment_date`, `payment_amount`, `created_at`, `updated_at`) VALUES
(6, 144, NULL, 'PMB109884', '$2y$12$1lY/hin2gjIc4/Nc9BLYm.u5ndRWRmXvIHIyPt7Q8IxS/LMA6PdVu', 'PMB2025105299', '9884162526000144', 'pending', NULL, '300000.00', NULL, '0.00', 0, '2025-10-23 09:37:56', NULL, NULL, '2025-10-21 19:37:56', '2025-10-23 04:29:35'),
(7, 145, NULL, 'PMB109885', '$2y$12$VbHxFcAlQrJLafv4GQUxK.4r2c34VTgQC0POOboztItXEIvTm.aw2', 'PMB2025103933', '9884162526000145', 'pending', NULL, '300000.00', NULL, '0.00', 0, '2025-10-23 11:16:08', NULL, NULL, '2025-10-21 21:16:11', '2025-10-21 21:16:11'),
(13, 149, NULL, 'PMB109889', '$2y$12$HvQXV9oQeczY0LUz/fOR.uIhK.DJgtDckk.vcPciflUPeYdorbDU2', 'PMB2025102454', '9884162526000149', 'pending', NULL, '300000.00', NULL, '0.00', 0, '2025-10-24 09:54:30', NULL, NULL, '2025-10-23 02:54:32', '2025-10-22 19:54:33'),
(14, 150, NULL, 'PMB109880', '$2y$12$E561M1K.6kvRdeFxoB454ep8eTLmO1.7ImPktSJineRCQ65n0gtPW', 'PMB2025103676', '9884162526000150', 'pending', NULL, '300000.00', NULL, '0.00', 0, '2025-10-24 10:39:33', NULL, NULL, '2025-10-23 03:39:34', '2025-10-22 20:39:34'),
(15, 151, NULL, 'Kakoi123456', '$2y$12$uLp/UFM4/DGDenoTUCF5DOgeKOkgmlVcqjIT/TSTEOin/XvmMvpha', 'PMB2025102388', '9884162526000151', 'paid', NULL, '300000.00', NULL, '0.00', 0, '2025-10-24 13:17:07', NULL, NULL, '2025-10-23 06:17:08', '2025-10-31 03:54:30'),
(16, 153, NULL, 'PMB109883', '$2y$12$MI60VDxjA4GUNDuN9gZeU.lDlcKXY/F3FL.uZBauge4GppuEq.MxW', 'PMB2025102392', '9884162526000153', 'pending', NULL, '300000.00', NULL, '0.00', 0, '2025-10-30 11:05:28', NULL, NULL, '2025-10-29 04:05:29', '2025-10-28 21:05:29'),
(17, 154, NULL, 'PMB109884', '$2y$12$5pXb.NkzckMyekNbEqtDbOr4qrSp2350.ouj7tTffMC9npWSDVQTK', 'PMB2025105239', '9884162526000154', 'pending', NULL, '1000.00', NULL, '0.00', 0, '2025-10-30 11:23:44', NULL, NULL, '2025-10-29 04:23:44', '2025-10-28 21:23:45'),
(18, 155, NULL, 'free123', '$2y$12$dBC3dxccn18CAKYNuTQnV.wjYiH6sQG9ErCBjE/g6BaZkOXV3Wgq.', 'FREE-155-1761712515', 'FREE-1761712515-155', 'paid', NULL, '1000.00', NULL, '0.00', 0, '2035-10-29 04:35:15', NULL, NULL, '2025-10-28 21:35:15', '2025-10-28 21:35:15'),
(19, 156, NULL, 'PMB109886', '$2y$12$D6ttQnIDHL05Oe/6bGgjAehHNBCePpQzPZVDQbMgvuShkjr4jsUju', 'PMB2025104523', '9884162526000156', 'paid', NULL, '1000.00', NULL, '0.00', 0, '2025-10-30 11:39:39', NULL, NULL, '2025-10-29 04:39:40', '2025-10-28 21:39:40'),
(20, 157, NULL, 'PMB109887', '$2y$12$lvcMgXrxjkXq8BQS9iNUl.c/jFBCYh29dHTadu3gjvUXNMsv.ZNGm', 'PMB2025102134', '9884162526000157', 'pending', NULL, '180000.00', NULL, '0.00', 0, '2025-10-30 11:42:22', NULL, NULL, '2025-10-29 04:42:23', '2025-10-28 21:42:23'),
(21, 158, NULL, 'PMB109888', '$2y$12$PiVZMkwqqUHnEhgRgm4J2.ZwnGgzQM0dgPnpVIOuQdiPg0VLnNyue', 'PMB2025103444', '9884162526000158', 'pending', NULL, '300000.00', NULL, '0.00', 0, '2025-10-31 14:58:04', NULL, NULL, '2025-10-30 07:58:07', '2025-10-30 00:58:07'),
(22, 159, 10, 'PMB109889', '$2y$12$EEWDwhz3GvqHEgiGzifqTeAirYRr/qX61ifGPMr1IdRQFZ3tfy.ia', 'PMB2025106678', '9884162526000159', 'pending', NULL, '300000.00', NULL, '0.00', 0, '2025-11-01 09:52:31', NULL, NULL, '2025-10-31 02:52:33', '2025-10-31 02:52:33'),
(23, 160, 10, 'PMB109880', '$2y$12$zAZtIv6hx.EXIpPwSMm0M.PI9q8qmwbwJAg7v4fW/PbT7qDBumdE2', 'PMB2025103644', '9884162526000160', 'pending', NULL, '300000.00', NULL, '0.00', 0, '2025-11-01 10:39:35', NULL, NULL, '2025-10-31 03:39:37', '2025-10-31 03:39:37'),
(24, 161, 10, 'PMB109881', '$2y$12$4UVRvGHjSLxA8f7yFlDnnOM80xGzpEFdZQ8SZK8Ti8VvMw1Z9OIUW', 'PMB2025102638', '9884162526000161', 'paid', NULL, '1000.00', NULL, '0.00', 0, '2025-11-01 10:41:08', NULL, NULL, '2025-10-31 03:41:09', '2025-10-31 03:41:10');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_09_30_063253_add_phone_to_users_table', 2),
(5, '2025_10_02_035036_add_role_to_users_table', 3),
(6, '2025_10_06_154101_create_virtual_accounts_table', 4),
(7, '2019_12_14_000001_create_personal_access_tokens_table', 5),
(8, '2025_10_08_163938_add_bio_to_users_table', 5),
(9, '2025_10_08_165421_add_address_fields_to_users_table', 6),
(10, '2025_10_10_031830_create_profiles_table', 7),
(11, '2025_10_10_033758_create_promo_codes_table', 8),
(12, '2025_10_13_030337_create_events_table', 9),
(13, '2025_10_13_035940_create_calendar_events_table', 10),
(14, '2025_10_14_062611_create_bni_billings_table', 11),
(15, '2025_10_16_051410_create_questions_table', 12),
(16, '2025_10_20_033400_add_expired_time_to_questions_table', 13),
(17, '2025_10_20_061255_create_batches_table', 14),
(18, '2025_10_20_064825_add_batch_id_to_questions_table', 15),
(19, '2025_10_21_034421_add_amount_to_bni_configs_table', 16),
(20, '2025_10_21_062838_create_master_users', 17),
(21, '2025_10_21_063443_create_master_users_table', 18),
(22, '2025_10_21_164322_create_master_moodle_table', 19),
(23, '2025_10_25_122129_create_events_table', 20),
(24, '2025_10_29_035400_add_discount_columns_to_users_and_master_users_tables', 21),
(25, '2025_10_30_080515_create_biodata_table', 22),
(26, '2025_10_30_082708_create_biodata_table', 23),
(27, '2025_10_30_103933_create_biodatas_table', 24),
(28, '2025_10_30_213154_create_categories_table', 25),
(29, '2025_10_30_213231_create_posts_table', 25),
(30, '2025_10_30_223023_remove_category_id_from_posts_table', 26),
(31, '2025_10_31_001736_add_publish_schedule_to_posts_table', 27),
(32, '2025_10_31_095026_add_gelombang_to_users_table', 28),
(33, '2025_11_02_221405_create_master_ereg_table', 29);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `author_id` bigint UNSIGNED NOT NULL,
  `status` enum('draft','published') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `publish_start` timestamp NULL DEFAULT NULL,
  `publish_end` timestamp NULL DEFAULT NULL,
  `schedule_type` enum('immediate','scheduled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'immediate',
  `meta_description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tags` text COLLATE utf8mb4_unicode_ci,
  `views` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `title`, `slug`, `content`, `image`, `author_id`, `status`, `publish_start`, `publish_end`, `schedule_type`, `meta_description`, `tags`, `views`, `created_at`, `updated_at`) VALUES
(3, 'dadafaf', 'dadafaf', 'afafkbafiu', 'posts/ZGPnDSNtMVC1OOASTh0AbpBduSk7BeWR1IEpNS5j.png', 14, 'published', NULL, NULL, 'immediate', 'dadad', 'dadad', 0, '2025-10-30 17:44:22', '2025-10-31 00:55:03');

-- --------------------------------------------------------

--
-- Table structure for table `profiles`
--

CREATE TABLE `profiles` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bio` text COLLATE utf8mb4_unicode_ci,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `promo_codes`
--

CREATE TABLE `promo_codes` (
  `id` bigint UNSIGNED NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `discount` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `usage_limit` int DEFAULT NULL,
  `used_count` int DEFAULT '0',
  `expired_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `promo_codes`
--

INSERT INTO `promo_codes` (`id`, `code`, `description`, `discount`, `is_active`, `usage_limit`, `used_count`, `expired_at`, `created_at`, `updated_at`) VALUES
(1, '1234', 'adada', 100, 1, 12, 5, '2025-12-09 17:00:00', '2025-10-28 20:09:57', '2025-10-31 03:41:10'),
(2, 'ELPRJHIH', 'adada', 40, 1, 1, 1, '2025-10-29 17:00:00', '2025-10-28 21:41:43', '2025-10-28 21:42:23'),
(3, 'VIFZNPVI', 'pembayaran regist', 100, 1, 223, 0, '2025-12-11 17:00:00', '2025-10-31 03:50:16', '2025-10-31 03:50:16');

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch_id` bigint UNSIGNED DEFAULT NULL,
  `text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` json NOT NULL,
  `correct_answer` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expired_time` datetime DEFAULT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `document_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bio` text COLLATE utf8mb4_unicode_ci,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'mahasiswa_baru',
  `gelombang` int DEFAULT NULL,
  `promo_code_used` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `va_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `va_amount` int UNSIGNED DEFAULT NULL,
  `original_amount` decimal(15,2) DEFAULT NULL,
  `discount_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `va_expired_at` datetime DEFAULT NULL,
  `va_status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `phone`, `bio`, `country`, `city`, `postal_code`, `role`, `gelombang`, `promo_code_used`, `va_number`, `va_amount`, `original_amount`, `discount_amount`, `va_expired_at`, `va_status`) VALUES
(14, 'Admin PENMABAR', 'admin1@pmb.ubpkarawang.ac.id', NULL, '$2y$12$aOUuDPebsbi5sym9ZW1VgeH8EvP.2U7u8AuKms9MsnZoS/T3JU2ka', 'QV6o0XbNLui2I5uBCXqwpRarUf86W1nyfeNLFn8giC8BODDpuXblGmuVsYWF', '2025-10-02 00:08:08', '2025-10-08 10:17:01', '81038997283', 'Hai', NULL, NULL, NULL, 'admin', NULL, NULL, NULL, NULL, NULL, '0.00', NULL, NULL),
(15, 'Administrator', 'admin2@pmb.ubpkarawang.ac.id', NULL, '$2y$12$HR3adPzF7rM9.I15.ekQAeHXA0K1T9UojbLtkClQCRCoO4/AV/KAu', NULL, '2025-10-02 00:08:45', '2025-10-02 00:08:45', '85771189715', NULL, NULL, NULL, NULL, 'admin', NULL, NULL, NULL, NULL, NULL, '0.00', NULL, NULL),
(144, 'Muhammad Ikhsan N', 'admin121@pmb.ubpkarawang.ac.id', NULL, '$2y$12$auoiwJpLLa9sqn.fgYTwPexgZaDSLqFV6EhsWvjSuIPL7pwJ0HOTq', NULL, '2025-10-21 19:37:56', '2025-10-21 19:37:56', '628515605299', NULL, NULL, NULL, NULL, 'mahasiswa_baru', NULL, NULL, '9884162526000144', 300000, NULL, '0.00', '2025-10-23 09:37:56', 'PENDING'),
(145, 'Muhammad Ikhsan N', 'adadasda@gmail.com', NULL, '$2y$12$ebgT2yNhW0dsTSqE1vj03eSUlT0l5tSddMLQYPcElZoxNLbm6HLNa', NULL, '2025-10-21 21:16:08', '2025-10-21 21:16:11', '62851560523933', NULL, NULL, NULL, NULL, 'mahasiswa_baru', NULL, NULL, '9884162526000145', 300000, NULL, '0.00', '2025-10-23 11:16:08', 'PENDING'),
(149, 'Muhammad Ikhsan N', 'admin1as@psasmb.ubpkarawang.ac.id', NULL, '$2y$12$u9qcrCwRGlJfxx3SShbOMeSyOUu45Ogd3407zPsucT4FcO60FnmWK', NULL, '2025-10-22 19:54:30', '2025-10-22 19:54:33', '6285156052454', NULL, NULL, NULL, NULL, 'mahasiswa_baru', NULL, NULL, '9884162526000149', 300000, NULL, '0.00', '2025-10-24 09:54:30', 'PENDING'),
(150, 'asdada', 'asa12@gmail.com', NULL, '$2y$12$xnkQo2q.FbU1khr67/AJpO.oaQ0hsQ2kqi05NeJOmzApWZMBbks72', NULL, '2025-10-22 20:39:33', '2025-10-22 20:39:34', '62851560523676', NULL, NULL, NULL, NULL, 'mahasiswa_baru', NULL, NULL, '9884162526000150', 300000, NULL, '0.00', '2025-10-24 10:39:33', 'PENDING'),
(151, 'Muhammad Ikhsan N', 'allancechain@gmail.com', NULL, '$2y$12$rqqaZ1Kyz6YB8AgKS9MS.OW2ttNNdXY9eKNj82Kc5ieksfPVlSsu6', NULL, '2025-10-22 23:17:07', '2025-10-31 03:54:30', '6285156052388', NULL, NULL, NULL, NULL, 'mahasiswa_baru', NULL, NULL, '9884162526000151', 300000, NULL, '0.00', '2025-10-24 13:17:07', 'PAID'),
(153, 'Muhammad Ikhsan N', 'as12dad2@gmail.com', NULL, '$2y$12$codfVWAFfi4MUpTQTKPtAOOg350LX726V5PSysNXobZUZYeoF8P6i', NULL, '2025-10-28 21:05:28', '2025-10-28 21:05:29', '6285156052392', NULL, NULL, NULL, NULL, 'mahasiswa_baru', NULL, NULL, '9884162526000153', 300000, NULL, '0.00', '2025-10-30 11:05:28', 'PENDING'),
(154, 'Muhammad Ikhsan N', '12a4423@gmail.com', NULL, '$2y$12$57HlbyEC2VWhx06VIAfhDumAudsfbFBrR6Oh6epCsLh7kYWYlXEjK', NULL, '2025-10-28 21:23:44', '2025-10-28 21:23:45', '62851564205239', NULL, NULL, NULL, NULL, 'mahasiswa_baru', NULL, NULL, '9884162526000154', 1000, NULL, '0.00', '2025-10-30 11:23:44', 'PENDING'),
(155, 'Muhammad Ikhsan N', 'as9999999999999999999@gmail.com', NULL, '$2y$12$HpPZDdaxp3FMUxAaTnl9.OZX5AejaF0/hGoTkUJj06PhSVjf42oVy', NULL, '2025-10-28 21:35:15', '2025-10-28 21:35:15', '628515633333', NULL, NULL, NULL, NULL, 'mahasiswa_baru', NULL, NULL, 'FREE-1761712515-155', 1000, NULL, '0.00', '2035-10-29 04:35:15', 'PAID'),
(156, 'Muhammad Ikhsan N', 'adada11111sda@gmail.com', NULL, '$2y$12$gI3mW6Pv9jUNHXhvyAxbOemNvcgBC6Txv4YxVGVpvAjpC8Z34V.0q', NULL, '2025-10-28 21:39:39', '2025-10-28 21:39:40', '62812314523', NULL, NULL, NULL, NULL, 'mahasiswa_baru', NULL, NULL, '9884162526000156', 1000, NULL, '0.00', '2025-10-30 11:39:39', 'PAID'),
(157, 'Muhammad Ikhsan N', 'adad1124442344asda@gmail.com', NULL, '$2y$12$EUd0TtU.zWpPK1nrOUCmkeDjGqhn4hn1xrHDpzvmqvaO5VPnT0bOW', NULL, '2025-10-28 21:42:22', '2025-10-28 21:42:23', '6285156432134', NULL, NULL, NULL, NULL, 'mahasiswa_baru', NULL, NULL, '9884162526000157', 180000, NULL, '0.00', '2025-10-30 11:42:22', 'PENDING'),
(158, 'Muhammad Ikhsan N', 'ikhsan123@gmail.com', NULL, '$2y$12$fc0yB3I1EFNepp1FpSH91OubnwcMnRuMNeKjTapLPFA7QB3qPssgO', NULL, '2025-10-30 00:58:04', '2025-10-30 00:58:07', '62812472923444', NULL, NULL, NULL, NULL, 'mahasiswa_baru', NULL, NULL, '9884162526000158', 300000, NULL, '0.00', '2025-10-31 14:58:04', 'PENDING'),
(159, 'Muhammad Ikhsan N', 'da123dad12@gmail.com', NULL, '$2y$12$yZD/UD97861oj0tl2lN2quZCs70U6EuFpdC8viBmPuw1jEZYnvyxa', NULL, '2025-10-31 02:52:31', '2025-10-31 02:52:33', '6285156056678', NULL, NULL, NULL, NULL, 'mahasiswa_baru', 10, NULL, '9884162526000159', 300000, NULL, '0.00', '2025-11-01 09:52:31', 'PENDING'),
(160, 'daidjdhd', 'dad233@gmail.com', NULL, '$2y$12$wWyjA5EzSoEwOSLwZGNZRuy.qdmaKK0eV80HmaSReuHig.NGTf84q', NULL, '2025-10-31 03:39:35', '2025-10-31 03:39:37', '628126383644', NULL, NULL, NULL, NULL, 'mahasiswa_baru', 10, NULL, '9884162526000160', 300000, NULL, '0.00', '2025-11-01 10:39:35', 'PENDING'),
(161, 'Muhammad Ikhsan N', 'adad123312344asda@gmail.com', NULL, '$2y$12$eDmMk1fIUdaDfaoVtwLxROBfKqaRx5t4ljevh9J8VL231KDbb69Su', NULL, '2025-10-31 03:41:08', '2025-10-31 03:41:10', '62851560882638', NULL, NULL, NULL, NULL, 'mahasiswa_baru', 10, NULL, '9884162526000161', 1000, NULL, '0.00', '2025-11-01 10:41:08', 'PAID');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `batches`
--
ALTER TABLE `batches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `batches_uuid_unique` (`uuid`);

--
-- Indexes for table `biodatas`
--
ALTER TABLE `biodatas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `biodatas_user_id_unique` (`user_id`);

--
-- Indexes for table `bni_billings`
--
ALTER TABLE `bni_billings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bni_callbacks`
--
ALTER TABLE `bni_callbacks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bni_configs`
--
ALTER TABLE `bni_configs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `calendar_events`
--
ALTER TABLE `calendar_events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `categories_slug_unique` (`slug`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `master_ereg`
--
ALTER TABLE `master_ereg`
  ADD PRIMARY KEY (`id`),
  ADD KEY `master_ereg_user_id_index` (`user_id`),
  ADD KEY `master_ereg_status_index` (`status`);

--
-- Indexes for table `master_moodle`
--
ALTER TABLE `master_moodle`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `master_moodle_master_user_id_unique` (`master_user_id`);

--
-- Indexes for table `master_users`
--
ALTER TABLE `master_users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_master_users_user` (`user_id`),
  ADD KEY `master_users_gelombang_index` (`gelombang`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `posts_slug_unique` (`slug`),
  ADD KEY `posts_author_id_foreign` (`author_id`);

--
-- Indexes for table `profiles`
--
ALTER TABLE `profiles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `profiles_user_id_foreign` (`user_id`);

--
-- Indexes for table `promo_codes`
--
ALTER TABLE `promo_codes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `promo_codes_code_unique` (`code`);

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `questions_batch_id_foreign` (`batch_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_gelombang_index` (`gelombang`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `batches`
--
ALTER TABLE `batches`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `biodatas`
--
ALTER TABLE `biodatas`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `bni_billings`
--
ALTER TABLE `bni_billings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bni_callbacks`
--
ALTER TABLE `bni_callbacks`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bni_configs`
--
ALTER TABLE `bni_configs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `calendar_events`
--
ALTER TABLE `calendar_events`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `master_ereg`
--
ALTER TABLE `master_ereg`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `master_moodle`
--
ALTER TABLE `master_moodle`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `master_users`
--
ALTER TABLE `master_users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `promo_codes`
--
ALTER TABLE `promo_codes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=162;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `biodatas`
--
ALTER TABLE `biodatas`
  ADD CONSTRAINT `biodatas_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `master_ereg`
--
ALTER TABLE `master_ereg`
  ADD CONSTRAINT `master_ereg_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `master_moodle`
--
ALTER TABLE `master_moodle`
  ADD CONSTRAINT `master_moodle_master_user_id_foreign` FOREIGN KEY (`master_user_id`) REFERENCES `master_users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `master_users`
--
ALTER TABLE `master_users`
  ADD CONSTRAINT `fk_master_users_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_author_id_foreign` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `profiles`
--
ALTER TABLE `profiles`
  ADD CONSTRAINT `profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_batch_id_foreign` FOREIGN KEY (`batch_id`) REFERENCES `batches` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
