-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 23, 2026 at 05:46 AM
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
-- Database: `ispsc_tracer`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel-cache-juderaganit@gmail.com|127.0.0.1', 'i:3;', 1780879659),
('laravel-cache-juderaganit@gmail.com|127.0.0.1:timer', 'i:1780879659;', 1780879659),
('laravel-cache-juderaganit56000@gmail.com|127.0.0.1', 'i:2;', 1781149167),
('laravel-cache-juderaganit56000@gmail.com|127.0.0.1:timer', 'i:1781149167;', 1781149167),
('laravel-cache-nylmomrilla@gmail.com|127.0.0.1', 'i:1;', 1780561191),
('laravel-cache-nylmomrilla@gmail.com|127.0.0.1:timer', 'i:1780561191;', 1780561191);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tracking_id` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `classification` varchar(255) NOT NULL,
  `is_hard_copy` tinyint(1) NOT NULL DEFAULT 0,
  `file_path` varchar(255) NOT NULL,
  `uploader_id` bigint(20) UNSIGNED NOT NULL,
  `current_office_id` varchar(50) NOT NULL,
  `target_office_id` varchar(50) NOT NULL,
  `current_step` int(11) NOT NULL DEFAULT 1,
  `status` enum('mapping','needs_review','pending','accepted','returned') DEFAULT 'mapping',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `priority` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`id`, `tracking_id`, `title`, `classification`, `is_hard_copy`, `file_path`, `uploader_id`, `current_office_id`, `target_office_id`, `current_step`, `status`, `created_at`, `updated_at`, `priority`) VALUES
(91, 'ISPSC-06/22/2026-09:52:25-NOR', 'Communication Letter', 'Communication Letter', 0, 'documents/1782093145_Sprint-plan.pdf', 15, 'ISPSC-MC-FIN-2026-W6CH2Y', 'ISPSC-MC-FIS-2026-ALV5MQ', 2, 'accepted', '2026-06-22 01:52:25', '2026-06-22 02:05:17', 1),
(93, 'ISPSC-06/22/2026-10:07:27-URG', 'Communication Letter', 'Communication Letter', 0, 'documents/1782094047_Test-Cases-Documentation.pdf', 9, 'ISPSC-MC-FIN-2026-W6CH2Y', 'ISPSC-MC-SEC-2026-DB8IKR', 1, 'accepted', '2026-06-22 02:07:27', '2026-06-22 03:12:14', 2),
(94, 'ISPSC-06/22/2026-11:15:49-URG', 'Communication Letter', 'Communication Letter', 0, 'documents/1782098149_STREET-DANCING-COMPETITION-KABAYBAYAN-2026.pdf', 11, 'ISPSC-MC-SAG-2026-W2BHIU', 'ISPSC-MC-HWS-2026-6XGOLQ', 2, 'accepted', '2026-06-22 03:15:49', '2026-06-22 03:23:10', 2),
(96, 'ISPSC-06/23/2026-09:07:47-NOR', 'Papel ni Mori', 'Special Order', 0, 'PHYSICAL_ITEM', 15, 'ISPSC-MC-SAG-2026-W2BHIU', 'ISPSC-MC-FIS-2026-ALV5MQ', 2, 'accepted', '2026-06-23 01:07:47', '2026-06-23 02:52:03', 1);

-- --------------------------------------------------------

--
-- Table structure for table `document_attachments`
--

CREATE TABLE `document_attachments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `document_id` bigint(20) UNSIGNED NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_type` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `document_attachments`
--

INSERT INTO `document_attachments` (`id`, `document_id`, `file_path`, `file_name`, `file_type`, `created_at`, `updated_at`) VALUES
(45, 91, 'documents/1782093145_Sprint-plan.pdf', 'Sprint-plan.pdf', 'application/pdf', '2026-06-22 01:52:25', '2026-06-22 01:52:25'),
(47, 93, 'documents/1782094047_Test-Cases-Documentation.pdf', 'Test-Cases-Documentation.pdf', 'application/pdf', '2026-06-22 02:07:27', '2026-06-22 02:07:27'),
(48, 94, 'documents/1782098149_STREET-DANCING-COMPETITION-KABAYBAYAN-2026.pdf', 'STREET-DANCING-COMPETITION-KABAYBAYAN-2026.pdf', 'application/pdf', '2026-06-22 03:15:49', '2026-06-22 03:15:49');

-- --------------------------------------------------------

--
-- Table structure for table `document_logs`
--

CREATE TABLE `document_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `document_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `office_id` varchar(50) NOT NULL,
  `action` varchar(255) NOT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `document_logs`
--

INSERT INTO `document_logs` (`id`, `document_id`, `user_id`, `office_id`, `action`, `remarks`, `created_at`, `updated_at`) VALUES
(267, 91, 15, 'ISPSC-MC-REC-2026-4URQGK', 'CREATED', 'Phase 1: Registration Complete. Document in tag-mapping state.', '2026-06-22 01:52:25', '2026-06-22 01:52:25'),
(268, 91, 15, 'ISPSC-MC-REC-2026-4URQGK', 'FINALIZED', 'Signature mapping completed. Awaiting Priority Assignment from Records Office.', '2026-06-22 01:52:43', '2026-06-22 01:52:43'),
(269, 91, 15, 'ISPSC-MC-REC-2026-4URQGK', 'FINALIZED', 'Signature mapping completed. Awaiting Priority Assignment from Records Office.', '2026-06-22 01:52:48', '2026-06-22 01:52:48'),
(270, 91, 15, 'ISPSC-MC-REC-2026-4URQGK', 'FINALIZED', 'Signature mapping completed. Awaiting Priority Assignment from Records Office.', '2026-06-22 01:52:54', '2026-06-22 01:52:54'),
(273, 91, 4, 'ISPSC-MC-SAG-2026-W2BHIU', 'DOCUMENT RETURNED', 'REASON: whywhywhy', '2026-06-22 01:55:33', '2026-06-22 01:55:33'),
(274, 91, 15, 'ISPSC-MC-REC-2026-4URQGK', 'RE-VALIDATED', 'CREATOR EXPLANATION: because because because', '2026-06-22 01:57:04', '2026-06-22 01:57:04'),
(275, 91, 4, 'ISPSC-MC-SAG-2026-W2BHIU', 'DIGITAL SIGNATURE APPLIED', 'Digital signature applied.', '2026-06-22 02:01:03', '2026-06-22 02:01:03'),
(276, 91, 9, 'ISPSC-MC-FIN-2026-W6CH2Y', 'DIGITAL SIGNATURE APPLIED', 'Digital signature applied.', '2026-06-22 02:05:17', '2026-06-22 02:05:17'),
(277, 93, 9, 'ISPSC-MC-FIN-2026-W6CH2Y', 'CREATED', 'Phase 1: Registration Complete. Document in tag-mapping state.', '2026-06-22 02:07:27', '2026-06-22 02:07:27'),
(278, 93, 9, 'ISPSC-MC-FIN-2026-W6CH2Y', 'FINALIZED', 'Signature mapping completed. Awaiting Priority Assignment from Records Office.', '2026-06-22 02:07:50', '2026-06-22 02:07:50'),
(279, 93, 15, 'ISPSC-MC-REC-2026-4URQGK', 'PRIORITY ASSIGNED', 'Priority set to 2', '2026-06-22 02:09:05', '2026-06-22 02:09:05'),
(281, 93, 11, 'ISPSC-MC-SAG-2026-W2BHIU', 'DIGITAL SIGNATURE APPLIED', 'Digital signature applied.', '2026-06-22 03:12:14', '2026-06-22 03:12:14'),
(282, 94, 11, 'ISPSC-MC-SAG-2026-W2BHIU', 'CREATED', 'Phase 1: Registration Complete. Document in tag-mapping state.', '2026-06-22 03:15:49', '2026-06-22 03:15:49'),
(283, 94, 11, 'ISPSC-MC-SAG-2026-W2BHIU', 'FINALIZED', 'Signature mapping completed. Awaiting Priority Assignment from Records Office.', '2026-06-22 03:16:26', '2026-06-22 03:16:26'),
(284, 94, 15, 'ISPSC-MC-REC-2026-4URQGK', 'PRIORITY ASSIGNED', 'Priority set to 2', '2026-06-22 03:20:10', '2026-06-22 03:20:10'),
(285, 94, 9, 'ISPSC-MC-FIN-2026-W6CH2Y', 'DIGITAL SIGNATURE APPLIED', 'Digital signature applied.', '2026-06-22 03:22:35', '2026-06-22 03:22:35'),
(286, 94, 4, 'ISPSC-MC-SAG-2026-W2BHIU', 'DIGITAL SIGNATURE APPLIED', 'Digital signature applied.', '2026-06-22 03:23:10', '2026-06-22 03:23:10'),
(288, 96, 15, 'ISPSC-MC-REC-2026-4URQGK', 'CREATED (HARD COPY)', 'Physical Item registration complete. Mapping phase skipped.', '2026-06-23 01:07:56', '2026-06-23 01:07:56'),
(289, 96, 9, 'ISPSC-MC-FIN-2026-W6CH2Y', 'DIGITAL SIGNATURE APPLIED', 'Digital signature applied.', '2026-06-23 02:51:45', '2026-06-23 02:51:45'),
(290, 96, 4, 'ISPSC-MC-SAG-2026-W2BHIU', 'DIGITAL SIGNATURE APPLIED', 'Digital signature applied.', '2026-06-23 02:52:03', '2026-06-23 02:52:03');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_05_26_010620_create_personal_access_tokens_table', 1),
(5, '2026_05_26_053026_create_document_system_tables', 1),
(6, '2026_05_29_005945_create_signature_positions_table', 1),
(7, '2026_05_29_091325_create_notifications_table', 1),
(8, '2026_06_01_093650_automate_office_id_format', 1),
(9, '2026_06_01_130303_create_document_attachments_table', 2),
(10, '2026_06_01_152621_create_document_attachments_table', 3),
(11, '2026_06_02_130832_add_is_hard_copy_to_documents_table', 3),
(12, '2026_06_03_143554_add_priority_to_documents_table', 3),
(13, '2026_06_03_144526_add_priority_to_documents_table', 3),
(14, '2026_06_03_144526_add_priority_to_documents_table', 3),
(15, '2026_06_11_084249_add_reminder_timestamp_to_signatories', 4),
(16, '2026_06_15_143837_add_role_title_to_users_table', 5),
(17, '2026_06_16_082925_add_mapping_status_to_documents_table', 6),
(18, '2026_06_16_092136_update_documents_table_for_review_process', 7);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `link` varchar(255) NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `message`, `link`, `is_read`, `created_at`, `updated_at`) VALUES
(4, 4, 'incoming', 'Incoming document: ISPSC-UIP-6A1CF3EFCD832 is ready for your signature.', 'http://127.0.0.1:8000/document/view/2', 0, '2026-06-01 02:54:32', '2026-06-01 02:54:32'),
(6, 4, 'incoming', 'Document ISPSC-UIP-6A1CF3EFCD832 has been resubmitted. Please review again.', 'http://127.0.0.1:8000/document/view/2', 0, '2026-06-01 02:55:26', '2026-06-01 02:55:26'),
(11, 4, 'incoming', 'Incoming document: ISPSC-UIP-6A1CF580A9279 is ready for your signature.', 'http://127.0.0.1:8000/document/view/5', 0, '2026-06-01 03:00:11', '2026-06-01 03:00:11'),
(14, 4, 'disseminated', 'Records Office shared a finalized document: 5', 'http://127.0.0.1:8000/document/view/5', 0, '2026-06-01 03:02:29', '2026-06-01 03:02:29'),
(17, 4, 'finished', 'CONGRATS! Document ISPSC-UIP-6A1D10C52CFCE is now fully signed.', 'http://127.0.0.1:8000/document/view/6', 0, '2026-06-01 05:16:02', '2026-06-01 05:16:02'),
(20, 4, 'finished', 'CONGRATS! Document ISPSC-UIP-6A1D1F88AA5C1 is now fully signed.', 'http://127.0.0.1:8000/document/view/8', 0, '2026-06-01 06:05:33', '2026-06-01 06:05:33'),
(21, 4, 'incoming', 'New Document ISPSC-UIP-6A1D22F9A1DA8 requires your signature.', 'http://127.0.0.1:8000/document/view/9', 0, '2026-06-01 06:13:13', '2026-06-01 06:13:13'),
(31, 4, 'finished', 'CONGRATS! Document ISPSC-UIP-6A1E23A8B134C is now fully signed.', 'http://127.0.0.1:8000/document/view/15', 0, '2026-06-02 00:29:01', '2026-06-02 00:29:01'),
(35, 4, 'incoming', 'New Document ISPSC-UIP-6A1E374B383F6 requires your signature.', 'http://127.0.0.1:8000/document/view/20', 0, '2026-06-02 01:52:11', '2026-06-02 01:52:11'),
(37, 4, 'incoming', 'Action Required: Receive Item ISPSC-UIP-6A1E73C925C71', 'http://127.0.0.1:8000/document/view/21', 0, '2026-06-02 06:10:17', '2026-06-02 06:10:17'),
(40, 4, 'finished', 'CONGRATS! Document ISPSC-UIP-6A1E79D6D578D is now fully signed.', 'http://127.0.0.1:8000/document/view/22', 0, '2026-06-02 07:31:10', '2026-06-02 07:31:10'),
(43, 4, 'incoming', 'Action Required: Receive Item ISPSC-UIP-6A1F72871C4C8', 'http://127.0.0.1:8000/document/view/24', 0, '2026-06-03 00:17:11', '2026-06-03 00:17:11'),
(44, 4, 'finished', 'CONGRATS! Document ISPSC-UIP-6A1E234AEC115 is now fully signed.', 'http://127.0.0.1:8000/document/view/14', 0, '2026-06-03 00:27:41', '2026-06-03 00:27:41'),
(45, 4, 'incoming', 'Incoming document: ISPSC-UIP-6A1E3016BCCBC is ready for your signature.', 'http://127.0.0.1:8000/document/view/17', 0, '2026-06-03 00:27:59', '2026-06-03 00:27:59'),
(60, 9, 'incoming', 'Action Required: ISPSC-06/04/2026-11:09:10', 'http://127.0.0.1:8000/document/view/40', 0, '2026-06-04 03:09:10', '2026-06-04 03:09:10'),
(61, 9, 'incoming', 'Action Required: ISPSC-06/04/2026-11:20:55', 'http://127.0.0.1:8000/document/view/41', 0, '2026-06-04 03:20:55', '2026-06-04 03:20:55'),
(64, 9, 'incoming', 'Action Required: ISPSC-06/04/2026-02:06:08', 'http://127.0.0.1:8000/document/view/43', 0, '2026-06-04 06:06:08', '2026-06-04 06:06:08'),
(65, 9, 'incoming', 'Action Required: ISPSC-06/04/2026-02:11:05', 'http://127.0.0.1:8000/document/view/44', 0, '2026-06-04 06:11:05', '2026-06-04 06:11:05'),
(70, 9, 'incoming', 'Action Required: ISPSC-06/04/2026-02:50:13', 'http://127.0.0.1:8000/document/view/45', 0, '2026-06-04 06:50:13', '2026-06-04 06:50:13'),
(71, 11, 'incoming', 'Action Required: ISPSC-06/04/2026-03:07:57', 'http://127.0.0.1:8000/document/view/46', 0, '2026-06-04 07:07:57', '2026-06-04 07:07:57'),
(73, 4, 'incoming', 'Action Required: ISPSC-06/04/2026-03:15:15', 'http://127.0.0.1:8000/document/view/47', 0, '2026-06-04 07:15:16', '2026-06-04 07:15:16'),
(75, 4, 'incoming', 'Incoming document: ISPSC-06/04/2026-04:16:28', 'http://127.0.0.1:8000/document/view/48', 0, '2026-06-04 08:18:22', '2026-06-04 08:18:22'),
(76, 11, 'finished', 'Congrats! Your document is fully signed.', 'http://127.0.0.1:8000/document/view/48', 0, '2026-06-04 08:19:11', '2026-06-04 08:19:11'),
(77, 4, 'incoming', 'Action Required: ISPSC-06/04/2026-04:19:54', 'http://127.0.0.1:8000/document/view/49', 0, '2026-06-04 08:19:54', '2026-06-04 08:19:54'),
(80, 4, 'finished', 'Congrats! Your document is fully signed.', 'http://127.0.0.1:8000/document/view/49', 0, '2026-06-04 08:21:33', '2026-06-04 08:21:33'),
(81, 9, 'incoming', 'Action Required: New Document ISPSC-06082026-090011-GZPX', 'http://127.0.0.1:8000/document/view/51', 0, '2026-06-08 01:00:11', '2026-06-08 01:00:11'),
(82, 9, 'incoming', 'Action Required: ISPSC-06/10/2026-16:40:57-URG', 'http://127.0.0.1:8000/document/view/52', 0, '2026-06-10 08:40:57', '2026-06-10 08:40:57'),
(83, 9, 'incoming', 'Action Required: ISPSC-06/11/2026-09:07:44-EXT', 'http://127.0.0.1:8000/document/view/53', 0, '2026-06-11 01:07:46', '2026-06-11 01:07:46'),
(84, 4, 'incoming', 'Action Required: ISPSC-06/11/2026-09:09:37-EXT', 'http://127.0.0.1:8000/document/view/54', 0, '2026-06-11 01:09:37', '2026-06-11 01:09:37'),
(85, 9, 'incoming', 'Incoming document: ISPSC-06/11/2026-09:09:37-EXT', 'http://127.0.0.1:8000/document/view/54', 0, '2026-06-11 01:20:39', '2026-06-11 01:20:39'),
(86, 9, 'incoming', 'Action Required: ISPSC-06/11/2026-09:21:17-URG', 'http://127.0.0.1:8000/document/view/55', 0, '2026-06-11 01:21:17', '2026-06-11 01:21:17'),
(89, 11, 'finished', 'Congrats! Your document is fully signed.', 'http://127.0.0.1:8000/document/view/51', 0, '2026-06-11 01:50:03', '2026-06-11 01:50:03'),
(91, 9, 'incoming', 'Action Required: ISPSC-06/11/2026-09:51:32-NOR', 'http://127.0.0.1:8000/document/view/56', 0, '2026-06-11 01:51:32', '2026-06-11 01:51:32'),
(93, 9, 'incoming', 'Action Required: ISPSC-06/11/2026-10:17:02-NOR', 'http://127.0.0.1:8000/document/view/58', 0, '2026-06-11 02:17:02', '2026-06-11 02:17:02'),
(94, 9, 'incoming', 'Action Required: ISPSC-06/11/2026-10:35:33-NOR', 'http://127.0.0.1:8000/document/view/59', 0, '2026-06-11 02:35:33', '2026-06-11 02:35:33'),
(95, 9, 'incoming', 'Action Required: ISPSC-06/11/2026-10:40:56-EXT', 'http://127.0.0.1:8000/document/view/60', 0, '2026-06-11 02:40:56', '2026-06-11 02:40:56'),
(98, 4, 'disseminated', 'New Document Shared: ISPSC-06/11/2026-10:40:56-EXT is now available in your finished records.', 'http://127.0.0.1:8000?filter=accepted', 0, '2026-06-11 02:43:24', '2026-06-11 02:43:24'),
(99, 11, 'disseminated', 'New Document Shared: ISPSC-06/11/2026-10:40:56-EXT is now available in your finished records.', 'http://127.0.0.1:8000?filter=accepted', 0, '2026-06-11 02:43:24', '2026-06-11 02:43:24'),
(100, 9, 'disseminated', 'New Document Shared: ISPSC-06/11/2026-10:40:56-EXT is now available in your finished records.', 'http://127.0.0.1:8000?filter=accepted', 0, '2026-06-11 02:43:24', '2026-06-11 02:43:24'),
(101, 9, 'incoming', 'Action Required: ISPSC-06/11/2026-11:57:47-EXT', 'http://127.0.0.1:8000/document/view/61', 0, '2026-06-11 03:57:47', '2026-06-11 03:57:47'),
(102, 9, 'incoming', 'Action Required: ISPSC-06/11/2026-12:59:39-NOR', 'http://127.0.0.1:8000/document/view/62', 0, '2026-06-11 04:59:39', '2026-06-11 04:59:39'),
(103, 11, 'incoming', 'Action Required: ISPSC-06/11/2026-13:08:07-EXT', 'http://127.0.0.1:8000/document/view/63', 0, '2026-06-11 05:08:07', '2026-06-11 05:08:07'),
(104, 9, 'incoming', 'Action Required: ISPSC-06/11/2026-13:08:32-EXT', 'http://127.0.0.1:8000/document/view/64', 0, '2026-06-11 05:08:32', '2026-06-11 05:08:32'),
(105, 9, 'incoming', 'Action Required: ISPSC-06/11/2026-13:13:51-EXT', 'http://127.0.0.1:8000/document/view/65', 0, '2026-06-11 05:13:51', '2026-06-11 05:13:51'),
(106, 9, 'incoming', 'Action Required: ISPSC-06/11/2026-13:19:24-EXT', 'http://127.0.0.1:8000/document/view/66', 0, '2026-06-11 05:19:24', '2026-06-11 05:19:24'),
(109, 11, 'incoming', 'Action Required: ISPSC-06/15/2026-08:21:45-EXT', 'http://127.0.0.1:8000/document/view/67', 0, '2026-06-15 00:21:45', '2026-06-15 00:21:45'),
(110, 11, 'incoming', 'Action Required: ISPSC-06/15/2026-08:23:45-EXT', 'http://127.0.0.1:8000/document/view/68', 0, '2026-06-15 00:23:45', '2026-06-15 00:23:45'),
(111, 4, 'finished', 'Congrats! Your document is fully signed.', 'http://127.0.0.1:8000/document/view/68', 0, '2026-06-15 00:26:40', '2026-06-15 00:26:40'),
(112, 9, 'incoming', 'Action Required: ISPSC-06/15/2026-09:12:24-EXT', 'http://127.0.0.1:8000/document/view/69', 0, '2026-06-15 01:12:24', '2026-06-15 01:12:24'),
(113, 14, 'finished', 'Process Finished: ISPSC-06/15/2026-09:12:24-EXT', 'http://127.0.0.1:8000/document/view/ISPSC-06/15/2026-09:12:24-EXT', 0, '2026-06-15 01:31:47', '2026-06-15 01:31:47'),
(114, 14, 'finished', 'Process Finished: ISPSC-06/15/2026-08:21:45-EXT', 'http://127.0.0.1:8000/document/view/ISPSC-06/15/2026-08:21:45-EXT', 0, '2026-06-15 01:32:08', '2026-06-15 01:32:08'),
(115, 9, 'incoming', 'Action Required: ISPSC-06/15/2026-11:11:12-NOR', 'http://127.0.0.1:8000/document/view/70', 0, '2026-06-15 03:11:13', '2026-06-15 03:11:13'),
(116, 4, 'incoming', 'Incoming document: ISPSC-06/15/2026-11:11:12-NOR', 'http://127.0.0.1:8000/document/view/ISPSC-06/15/2026-11:11:12-NOR', 0, '2026-06-15 03:13:43', '2026-06-15 03:13:43'),
(118, 9, 'incoming', 'Action Required: ISPSC-06/15/2026-11:17:47-NOR', 'http://127.0.0.1:8000/document/view/71', 0, '2026-06-15 03:17:47', '2026-06-15 03:17:47'),
(120, 4, 'incoming', 'Action Required: ISPSC-06/15/2026-14:05:36-EXT', 'http://127.0.0.1:8000/document/view/72', 0, '2026-06-15 06:05:36', '2026-06-15 06:05:36'),
(121, 4, 'incoming', 'Action Required: Document ISPSC-06/15/2026-15:21:27-NOR', 'http://127.0.0.1:8000/document/view/73', 0, '2026-06-15 07:21:27', '2026-06-15 07:21:27'),
(122, 4, 'incoming', 'Action Required: Document ISPSC-06/15/2026-15:34:26-NOR', 'http://127.0.0.1:8000/document/view/74', 0, '2026-06-15 07:34:26', '2026-06-15 07:34:26'),
(123, 9, 'incoming', 'Action Required: Document ISPSC-06/15/2026-16:15:52-NOR', 'http://127.0.0.1:8000/document/view/75', 0, '2026-06-15 08:15:53', '2026-06-15 08:15:53'),
(124, 11, 'incoming', 'Action Required: Document ISPSC-06/15/2026-16:19:02-NOR', 'http://127.0.0.1:8000/document/view/76', 0, '2026-06-15 08:19:02', '2026-06-15 08:19:02'),
(125, 9, 'incoming', 'Action Required: Document ISPSC-06/16/2026-08:31:21-NOR', 'http://127.0.0.1:8000/document/view/77', 0, '2026-06-16 00:31:22', '2026-06-16 00:31:22'),
(126, 9, 'incoming', 'Action Required: Document ISPSC-06/16/2026-08:39:22-EXT', 'http://127.0.0.1:8000/document/view/78', 0, '2026-06-16 00:39:22', '2026-06-16 00:39:22'),
(127, 9, 'incoming', 'Action Required: Document ISPSC-06/16/2026-09:38:00-EXT is ready for your signature.', 'http://127.0.0.1:8000/document/view/ISPSC-06/16/2026-09:38:00-EXT', 0, '2026-06-16 02:04:28', '2026-06-16 02:04:28'),
(129, 9, 'incoming', 'Action Required: Document ISPSC-06/16/2026-10:13:27-URG assigned priority.', 'http://127.0.0.1:8000/document/view/ISPSC-06/16/2026-10:13:27-URG', 0, '2026-06-16 02:14:26', '2026-06-16 02:14:26'),
(130, 11, 'incoming', 'Incoming document for signature: ISPSC-06/16/2026-10:13:27-URG', 'http://127.0.0.1:8000/document/view/ISPSC-06/16/2026-10:13:27-URG', 0, '2026-06-16 02:15:04', '2026-06-16 02:15:04'),
(131, 4, 'incoming', 'Incoming document for signature: ISPSC-06/16/2026-10:13:27-URG', 'http://127.0.0.1:8000/document/view/ISPSC-06/16/2026-10:13:27-URG', 0, '2026-06-16 02:15:40', '2026-06-16 02:15:40'),
(132, 4, 'finished', 'Process Finished: Document ISPSC-06/16/2026-10:13:27-URG is now complete.', 'http://127.0.0.1:8000/document/view/ISPSC-06/16/2026-10:13:27-URG', 0, '2026-06-16 02:27:21', '2026-06-16 02:27:21'),
(134, 4, 'incoming', 'RE-VALIDATED: Creator maintained original file for ISPSC-06/15/2026-15:34:26-NOR.', 'http://127.0.0.1:8000/document/view/ISPSC-06/15/2026-15:34:26-NOR', 0, '2026-06-16 03:17:58', '2026-06-16 03:17:58'),
(136, 9, 'incoming', 'Incoming document for signature: ISPSC-06/16/2026-13:11:16-EXT', 'http://127.0.0.1:8000/document/view/ISPSC-06/16/2026-13:11:16-EXT', 0, '2026-06-16 05:11:21', '2026-06-16 05:11:21'),
(137, 9, 'incoming', 'Action Required: Document ISPSC-06/16/2026-12:57:14-URG assigned priority.', 'http://127.0.0.1:8000/document/view/ISPSC-06/16/2026-12:57:14-URG', 0, '2026-06-16 05:11:44', '2026-06-16 05:11:44'),
(138, 11, 'incoming', 'Incoming document for signature: ISPSC-06/16/2026-13:11:16-EXT', 'http://127.0.0.1:8000/document/view/ISPSC-06/16/2026-13:11:16-EXT', 0, '2026-06-16 05:13:07', '2026-06-16 05:13:07'),
(140, 11, 'incoming', 'RE-VALIDATED: Creator maintained original file for ISPSC-06/16/2026-13:11:16-EXT.', 'http://127.0.0.1:8000/document/view/ISPSC-06/16/2026-13:11:16-EXT', 0, '2026-06-16 05:14:20', '2026-06-16 05:14:20'),
(142, 11, 'incoming', 'RE-VALIDATED: Creator maintained original file for ISPSC-06/16/2026-13:11:16-EXT.', 'http://127.0.0.1:8000/document/view/ISPSC-06/16/2026-13:11:16-EXT', 0, '2026-06-16 05:32:27', '2026-06-16 05:32:27'),
(144, 4, 'incoming', 'Action Required: Document ISPSC-06/16/2026-14:06:57-NOR assigned priority.', 'http://127.0.0.1:8000/document/view/ISPSC-06/16/2026-14:06:57-NOR', 0, '2026-06-16 06:07:18', '2026-06-16 06:07:18'),
(145, 4, 'incoming', 'Incoming document for signature: ISPSC-06/16/2026-14:38:16-EXT', 'http://127.0.0.1:8000/document/view/ISPSC-06/16/2026-14:38:16-EXT', 0, '2026-06-16 06:38:48', '2026-06-16 06:38:48'),
(146, 4, 'incoming', 'Phase 3 Dispatch: Action Required on ISPSC-06/16/2026-15:09:57-EXT', 'http://127.0.0.1:8000/document/view/ISPSC-06/16/2026-15:09:57-EXT', 0, '2026-06-16 07:10:46', '2026-06-16 07:10:46'),
(149, 4, 'incoming', 'RESUBMITTED: Document ISPSC-06/16/2026-15:09:57-EXT has been updated. Please review corrections.', 'http://127.0.0.1:8000/document/view/ISPSC-06/16/2026-15:09:57-EXT', 0, '2026-06-16 07:13:03', '2026-06-16 07:13:03'),
(151, 9, 'incoming', 'Phase 3 Dispatch: Action Required on ISPSC-06/16/2026-15:44:01-URG', 'http://127.0.0.1:8000/document/view/ISPSC-06/16/2026-15:44:01-URG', 0, '2026-06-16 07:44:05', '2026-06-16 07:44:05'),
(152, 11, 'incoming', 'Phase 3 Dispatch: Action Required on ISPSC-06/16/2026-15:44:35-EXT', 'http://127.0.0.1:8000/document/view/ISPSC-06/16/2026-15:44:35-EXT', 0, '2026-06-16 07:44:38', '2026-06-16 07:44:38'),
(153, 9, 'incoming', 'Phase 3 Dispatch: Action Required on ISPSC-06/17/2026-12:53:29-EXT', 'http://127.0.0.1:8000/document/view/ISPSC-06/17/2026-12:53:29-EXT', 0, '2026-06-17 04:53:59', '2026-06-17 04:53:59'),
(155, 4, 'incoming', 'Incoming document for signature: ISPSC-06/17/2026-12:53:29-EXT', 'http://127.0.0.1:8000/document/view/ISPSC-06/17/2026-12:53:29-EXT', 0, '2026-06-17 04:57:01', '2026-06-17 04:57:01'),
(156, 9, 'incoming', 'Incoming document for signature: ISPSC-06/16/2026-15:09:57-EXT', 'http://127.0.0.1:8000/document/view/ISPSC-06/16/2026-15:09:57-EXT', 0, '2026-06-17 04:57:41', '2026-06-17 04:57:41'),
(158, 11, 'incoming', 'Incoming document for signature: ISPSC-06/16/2026-15:09:57-EXT', 'http://127.0.0.1:8000/document/view/ISPSC-06/16/2026-15:09:57-EXT', 0, '2026-06-17 04:59:08', '2026-06-17 04:59:08'),
(162, 11, 'incoming', 'RE-VALIDATED: Creator maintained original file for ISPSC-06/16/2026-15:09:57-EXT.', 'http://127.0.0.1:8000/document/view/ISPSC-06/16/2026-15:09:57-EXT', 0, '2026-06-17 05:00:44', '2026-06-17 05:00:44'),
(164, 4, 'incoming', 'RESUBMITTED: Document ISPSC-06/16/2026-15:09:57-EXT has been updated. Please review corrections.', 'http://127.0.0.1:8000/document/view/ISPSC-06/16/2026-15:09:57-EXT', 0, '2026-06-17 05:01:31', '2026-06-17 05:01:31'),
(165, 4, 'incoming', 'RESUBMITTED: Document ISPSC-06/16/2026-15:09:57-EXT has been updated. Please review corrections.', 'http://127.0.0.1:8000/document/view/ISPSC-06/16/2026-15:09:57-EXT', 0, '2026-06-17 05:01:36', '2026-06-17 05:01:36'),
(166, 4, 'incoming', 'RESUBMITTED: Document ISPSC-06/16/2026-15:09:57-EXT has been updated. Please review corrections.', 'http://127.0.0.1:8000/document/view/ISPSC-06/16/2026-15:09:57-EXT', 0, '2026-06-17 05:01:40', '2026-06-17 05:01:40'),
(168, 9, 'incoming', 'Incoming document for signature: ISPSC-06/16/2026-15:09:57-EXT', 'http://127.0.0.1:8000/document/view/ISPSC-06/16/2026-15:09:57-EXT', 0, '2026-06-17 05:02:33', '2026-06-17 05:02:33'),
(169, 11, 'incoming', 'Incoming document for signature: ISPSC-06/16/2026-15:09:57-EXT', 'http://127.0.0.1:8000/document/view/ISPSC-06/16/2026-15:09:57-EXT', 0, '2026-06-17 05:03:06', '2026-06-17 05:03:06');

-- --------------------------------------------------------

--
-- Table structure for table `offices`
--

CREATE TABLE `offices` (
  `id` varchar(50) NOT NULL,
  `office_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `offices`
--

INSERT INTO `offices` (`id`, `office_name`, `created_at`, `updated_at`) VALUES
('ISPSC-MC-ADM-2026-QN9YPQ', 'Director, Administrative Services/Supervising Administrative Officer', '2026-06-01 02:30:15', '2026-06-01 02:30:15'),
('ISPSC-MC-ALU-2026-QWFTKT', 'Director, Alumni Affairs', '2026-06-01 02:30:15', '2026-06-01 02:30:15'),
('ISPSC-MC-ANI-2026-WQQRCL', 'Director, Animal Research Center', '2026-06-01 02:30:15', '2026-06-01 02:30:15'),
('ISPSC-MC-AUX-2026-FAMRSB', 'Director, Auxiliary Services', '2026-06-01 02:30:15', '2026-06-01 02:30:15'),
('ISPSC-MC-EAP-2026-UCHQPQ', 'Executive Assistant/Director Presidential Management/ Private Secretary', '2026-06-01 02:30:15', '2026-06-01 02:30:15'),
('ISPSC-MC-ETE-2026-HDSB2S', 'Director, ETEEAP, TVET and Micro Credential Program', '2026-06-01 02:30:15', '2026-06-01 02:30:15'),
('ISPSC-MC-EXT-2026-HZCKVS', 'Director, Extension', '2026-06-01 02:30:15', '2026-06-01 02:30:15'),
('ISPSC-MC-FIN-2026-W6CH2Y', 'Director, Finance Services', '2026-06-01 02:30:15', '2026-06-01 02:30:15'),
('ISPSC-MC-FIS-2026-ALV5MQ', 'Director, Fisheries and Aquamarine Resources Research Center', '2026-06-01 02:30:15', '2026-06-01 02:30:15'),
('ISPSC-MC-FOR-2026-XNELHH', 'Director, Forest Advancement and Resources Management Research Center', '2026-06-01 02:30:15', '2026-06-01 02:30:15'),
('ISPSC-MC-GAD-2026-MXTSEE', 'Director, Gender and Development', '2026-06-01 02:30:15', '2026-06-01 02:30:15'),
('ISPSC-MC-GEN-2026-OARLXX', 'Director, General Services', '2026-06-01 02:30:15', '2026-06-01 02:30:15'),
('ISPSC-MC-HRD-2026-PRC2I6', 'Director, Human Resource Development Services', '2026-06-01 02:30:15', '2026-06-01 02:30:15'),
('ISPSC-MC-HWS-2026-6XGOLQ', 'Director, Health and Wellness Services', '2026-06-01 02:30:15', '2026-06-01 02:30:15'),
('ISPSC-MC-IAU-2026-EGDPSR', 'Director, Internal Audit Unit', '2026-06-01 02:30:15', '2026-06-01 02:30:15'),
('ISPSC-MC-ICT-2026-EUATRW', 'Director, Digital and ICT Development', '2026-06-01 02:30:15', '2026-06-01 02:30:15'),
('ISPSC-MC-ILP-2026-KLTCFF', 'Director, Internationalization, Linkages and Partnership', '2026-06-01 02:30:15', '2026-06-01 02:30:15'),
('ISPSC-MC-INS-2026-OAZUGN', 'Director, Instruction', '2026-06-01 02:30:15', '2026-06-01 02:30:15'),
('ISPSC-MC-IPE-2026-BRWHXB', 'Director, Indigenous People Education Research Center', '2026-06-01 02:30:15', '2026-06-01 02:30:15'),
('ISPSC-MC-IPU-2026-QKFUHJ', 'Director, Institutional Planning Unit', '2026-06-01 02:30:15', '2026-06-01 02:30:15'),
('ISPSC-MC-LEG-2026-FUZTEF', 'Director, Legal Services', '2026-06-01 02:30:15', '2026-06-01 02:30:15'),
('ISPSC-MC-LIB-2026-MHUJFZ', 'Director, Library Services', '2026-06-01 02:30:15', '2026-06-01 02:30:15'),
('ISPSC-MC-NST-2026-UMOGKY', 'Director, NSTP', '2026-06-01 02:30:15', '2026-06-01 02:30:15'),
('ISPSC-MC-PAG-2026-AHZK85', 'Director, Public Administration and Governance Policy Research Center', '2026-06-01 02:30:15', '2026-06-01 02:30:15'),
('ISPSC-MC-PID-2026-Y9UNOR', 'Director, Physical Infrastructure Development', '2026-06-01 02:30:15', '2026-06-01 02:30:15'),
('ISPSC-MC-PUB-2026-8VEFD2', 'Director, Publication Services', '2026-06-01 02:30:15', '2026-06-01 02:30:15'),
('ISPSC-MC-QAS-2026-7F0IYD', 'Director, Quality Assurance', '2026-06-01 02:30:15', '2026-06-01 02:30:15'),
('ISPSC-MC-RDP-2026-4TOVMV', 'Director, R&D Project and Facilities', '2026-06-01 02:30:15', '2026-06-01 02:30:15'),
('ISPSC-MC-REC-2026-4URQGK', 'Records Office', '2026-06-01 02:30:15', '2026-06-01 02:30:15'),
('ISPSC-MC-REG-2026-4GU9IZ', 'College Registrar', '2026-06-01 02:30:15', '2026-06-01 02:30:15'),
('ISPSC-MC-RES-2026-KKXIQ9', 'Director, Research', '2026-06-01 02:30:15', '2026-06-01 02:30:15'),
('ISPSC-MC-RHR-2026-78BA2A', 'Director, UIP Center for Rural Health Research, Reform and Policy', '2026-06-01 02:30:15', '2026-06-01 02:30:15'),
('ISPSC-MC-SAG-2026-W2BHIU', 'Director Student Affairs and Guidance Services', '2026-06-01 02:30:15', '2026-06-01 02:30:15'),
('ISPSC-MC-SCA-2026-RGEEHN', 'Director, Sports and Culture and the Arts Development', '2026-06-01 02:30:15', '2026-06-01 02:30:15'),
('ISPSC-MC-SCI-2026-4DIF6I', 'Director, Strategic Communication and Institutional Branding', '2026-06-01 02:30:15', '2026-06-01 02:30:15'),
('ISPSC-MC-SEC-2026-DB8IKR', 'College/Board Secretary', '2026-06-01 02:30:15', '2026-06-01 02:30:15'),
('ISPSC-MC-SWK-2026-TPK9LJ', 'Director, Sentro ng Wika at Kultura (SWK)', '2026-06-01 02:30:15', '2026-06-01 02:30:15'),
('ISPSC-MC-TAR-2026-B0T2PP', 'Director, Tobacco and Agricultural Research Center', '2026-06-01 02:30:15', '2026-06-01 02:30:15'),
('ISPSC-MC-TTP-2026-2BRZSR', 'Director, Director for Technology Transfer and Patent Unit', '2026-06-01 02:30:15', '2026-06-01 02:30:15');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('MbYiKMZ41tu9wcRwrHhbOIoWxHsFImMdvn9UvPaJ', 3, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiUTQzUzh3NzlMZW9SbE1ST2Y3bGZ4eUowWWJibXJOVGVYaVFuMUE0cyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjM4OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvZG9jdW1lbnQvdmlldy8yOCI7czo1OiJyb3V0ZSI7czoxNDoiZG9jdW1lbnRzLnZpZXciO31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aTozO30=', 1780465394),
('nDeHrFTWcf2JSkQPiGrgaseietPEP6ORLpy6V7g6', 7, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiS1gwNGpxVnBVRDdkQkhlSGhDbG42WDViSFRFY0RkS1dja0VlR054bCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjIxOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAiO3M6NToicm91dGUiO3M6OToiZGFzaGJvYXJkIjt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6Nzt9', 1780454187),
('UWRCUchfZlV9JzdR6KKidbBEQVnFBwsVKxt3cKZ6', 6, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiNTVldURkcFNERUhJOFlpbEcyMEh4QmpldlVYY1N6TExmRmUzb1JqbiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjM3OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYWRtaW4vcGVyc29ubmVsIjtzOjU6InJvdXRlIjtzOjE1OiJhZG1pbi5wZXJzb25uZWwiO31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aTo2O30=', 1780466369);

-- --------------------------------------------------------

--
-- Table structure for table `signatories`
--

CREATE TABLE `signatories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `document_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `x_pos` double DEFAULT NULL,
  `y_pos` double DEFAULT NULL,
  `page_num` int(11) DEFAULT 1,
  `sign_order` int(11) NOT NULL,
  `status` enum('pending','signed') NOT NULL DEFAULT 'pending',
  `signature_data` longtext DEFAULT NULL,
  `signed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `last_reminded_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `signatories`
--

INSERT INTO `signatories` (`id`, `document_id`, `user_id`, `x_pos`, `y_pos`, `page_num`, `sign_order`, `status`, `signature_data`, `signed_at`, `created_at`, `updated_at`, `last_reminded_at`) VALUES
(129, 91, 4, 25.591720655541, 5.7439870672619, 1, 1, 'signed', 'data:image/png;base64,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', '2026-06-22 02:00:58', '2026-06-22 01:52:25', '2026-06-22 02:00:58', NULL),
(130, 91, 9, 73.73539032279, 5.9023326723796, 1, 2, 'signed', 'data:image/png;base64,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', '2026-06-22 02:05:17', '2026-06-22 01:52:25', '2026-06-22 02:05:17', NULL),
(133, 93, 11, 62.472766884532, 8.2954548023365, 1, 1, 'signed', 'data:image/png;base64,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', '2026-06-22 03:12:14', '2026-06-22 02:07:27', '2026-06-22 03:12:14', NULL),
(135, 94, 9, 15.227613593811, 80.353961278062, 5, 1, 'signed', 'data:image/png;base64,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', '2026-06-22 03:22:30', '2026-06-22 03:15:49', '2026-06-22 03:22:30', NULL);
INSERT INTO `signatories` (`id`, `document_id`, `user_id`, `x_pos`, `y_pos`, `page_num`, `sign_order`, `status`, `signature_data`, `signed_at`, `created_at`, `updated_at`, `last_reminded_at`) VALUES
(136, 94, 4, 43.336690249839, 80.274775309815, 5, 2, 'signed', 'data:image/png;base64,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', '2026-06-22 03:23:10', '2026-06-22 03:15:49', '2026-06-22 03:23:10', NULL),
(139, 96, 9, NULL, NULL, 1, 1, 'signed', 'PHYSICAL_RECEIPT', '2026-06-23 02:51:41', '2026-06-23 01:07:47', '2026-06-23 02:51:41', NULL),
(140, 96, 4, NULL, NULL, 1, 2, 'signed', 'PHYSICAL_RECEIPT', '2026-06-23 02:52:03', '2026-06-23 01:07:47', '2026-06-23 02:52:03', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `signature_positions`
--

CREATE TABLE `signature_positions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `document_id` bigint(20) UNSIGNED NOT NULL,
  `signatory_name` varchar(255) NOT NULL,
  `page_number` int(11) NOT NULL DEFAULT 1,
  `x_pos` double NOT NULL,
  `y_pos` double NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'staff',
  `role_title` varchar(255) DEFAULT NULL,
  `office_id` varchar(50) DEFAULT NULL,
  `campus_code` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `avatar`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `role`, `role_title`, `office_id`, `campus_code`) VALUES
(4, 'Lynmuel Morilla', 'nylmorilla@gmail.com', 'avatars/eOeCJFFFLKEONqZnKo9Gy0Md6LAkRBVikkNh1utk.jpg', NULL, '$2y$12$Ru/4dXF2S1es4sVKra.oRO5Arc0ELSShFuPEciYpZNyhh9HXB7K5a', NULL, '2026-06-01 02:50:36', '2026-06-22 03:38:21', 'staff', NULL, 'ISPSC-MC-SAG-2026-W2BHIU', '0001'),
(9, 'Desiree Ann P. Guzman', 'desireeann000@gmail.com', 'avatars/3oWJhunfzivrhHHt28RDZkaqSzp1qTCw2JRgRy89.jpg', NULL, '$2y$12$HBZYlZRVag8pOYKeUN0td.YMeORyE7HYaNSPlb3Lg0KKYNAchUHzi', NULL, '2026-06-04 03:00:02', '2026-06-22 03:38:58', 'staff', NULL, 'ISPSC-MC-FIN-2026-W6CH2Y', '0001'),
(11, 'Jude Lawrence J. Raganit', 'juderaganit56@gmail.com', 'avatars/D68wlyRMOjWLfoSyOkBMYdJrB5U02ZSRIxdxN4gU.jpg', NULL, '$2y$12$u.0DuPdG4C02ELSBz7czPu1FuLpJ7yyNxB4wcD5FQb13BWmPmyTbq', NULL, '2026-06-04 07:06:59', '2026-06-22 03:39:16', 'staff', NULL, 'ISPSC-MC-SAG-2026-W2BHIU', '0001'),
(14, 'Evelyn Morilla', 'evelyn@gmail.com', NULL, NULL, '$2y$12$CSAe72uuGYdrxHkHKL4WDuYVwe4FcgNlilHTMjfq0Zv70YdSaRAwa', NULL, '2026-06-15 00:18:06', '2026-06-15 00:18:06', 'staff', NULL, 'ISPSC-MC-REG-2026-4GU9IZ', '0010'),
(15, 'Head of Records Office', 'brainnnotfound404@gmail.com', 'avatars/PtL9T6QIwRy5P2eX3qexOc4zJb01g3rJADB3FPZC.jpg', NULL, '$2y$12$gBtQct478nm5Kc7lZLRbuuvP4SUbZlJU1oH6sQxC5KYKN6ZgMgCmy', NULL, '2026-06-22 00:43:09', '2026-06-22 03:43:33', 'superadmin', NULL, 'ISPSC-MC-REC-2026-4URQGK', '0001'),
(16, 'Marc Jerick Urbano', 'umarcjerick@gmail.com', 'avatars/j7FE7IU2KwhqtgIxS3ZMKyAc4lZfqcGtDsqYguLS.jpg', NULL, '$2y$12$7IyUjym3bxpWouIocCSQFukvqkqZ8ZynGo.TacG7yrCXjBdY8hAny', NULL, '2026-06-22 02:11:25', '2026-06-22 03:39:32', 'staff', NULL, 'ISPSC-MC-REC-2026-4URQGK', '0001');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `documents_tracking_id_unique` (`tracking_id`),
  ADD KEY `documents_uploader_id_foreign` (`uploader_id`);

--
-- Indexes for table `document_attachments`
--
ALTER TABLE `document_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `document_attachments_document_id_foreign` (`document_id`);

--
-- Indexes for table `document_logs`
--
ALTER TABLE `document_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `document_logs_document_id_foreign` (`document_id`),
  ADD KEY `document_logs_user_id_foreign` (`user_id`);

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
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_user_id_foreign` (`user_id`);

--
-- Indexes for table `offices`
--
ALTER TABLE `offices`
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
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `signatories`
--
ALTER TABLE `signatories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `signatories_document_id_foreign` (`document_id`),
  ADD KEY `signatories_user_id_foreign` (`user_id`);

--
-- Indexes for table `signature_positions`
--
ALTER TABLE `signature_positions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `signature_positions_document_id_foreign` (`document_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- AUTO_INCREMENT for table `document_attachments`
--
ALTER TABLE `document_attachments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `document_logs`
--
ALTER TABLE `document_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=291;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=171;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `signatories`
--
ALTER TABLE `signatories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=141;

--
-- AUTO_INCREMENT for table `signature_positions`
--
ALTER TABLE `signature_positions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `documents_uploader_id_foreign` FOREIGN KEY (`uploader_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `document_attachments`
--
ALTER TABLE `document_attachments`
  ADD CONSTRAINT `document_attachments_document_id_foreign` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `document_logs`
--
ALTER TABLE `document_logs`
  ADD CONSTRAINT `document_logs_document_id_foreign` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `document_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `signatories`
--
ALTER TABLE `signatories`
  ADD CONSTRAINT `signatories_document_id_foreign` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `signatories_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `signature_positions`
--
ALTER TABLE `signature_positions`
  ADD CONSTRAINT `signature_positions_document_id_foreign` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
