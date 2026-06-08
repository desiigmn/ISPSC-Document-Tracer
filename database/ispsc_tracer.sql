-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 08, 2026 at 03:02 AM
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
  `status` enum('pending','accepted','returned','archived') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `priority` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`id`, `tracking_id`, `title`, `classification`, `is_hard_copy`, `file_path`, `uploader_id`, `current_office_id`, `target_office_id`, `current_step`, `status`, `created_at`, `updated_at`, `priority`) VALUES
(48, 'ISPSC-06/04/2026-04:16:28', 'beep beep', 'Memorandum', 0, 'PHYSICAL_ITEM', 11, 'ISPSC-MC-SAG-2026-W2BHIU', 'ISPSC-MC-FIN-2026-W6CH2Y', 2, 'accepted', '2026-06-04 08:16:28', '2026-06-04 08:19:11', 2),
(49, 'ISPSC-06/04/2026-04:19:54', 'Apple Pen', 'Hard Copy Document', 0, 'PHYSICAL_ITEM', 4, 'ISPSC-MC-REC-2026-4URQGK', 'ISPSC-MC-RDP-2026-4TOVMV', 2, 'accepted', '2026-06-04 08:19:54', '2026-06-04 08:21:33', 3),
(50, 'ISPSC-06/04/2026-04:20:37', 'qwe', 'Device/Equipment', 0, 'PHYSICAL_ITEM', 4, 'ISPSC-MC-SAG-2026-W2BHIU', 'ISPSC-MC-HWS-2026-6XGOLQ', 1, 'pending', '2026-06-04 08:20:37', '2026-06-04 08:20:37', 1),
(51, 'ISPSC-06082026-090011-GZPX', 'Budget Realignment', 'Resolution', 0, 'PHYSICAL_ITEM', 11, 'ISPSC-MC-SAG-2026-W2BHIU', 'ISPSC-MC-SAG-2026-W2BHIU', 1, 'pending', '2026-06-08 01:00:11', '2026-06-08 01:00:11', 3);

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
(79, 48, 11, 'ISPSC-MC-SAG-2026-W2BHIU', 'TIME OF HELLO', 'Physical item registered for tracking.', '2026-06-04 08:16:36', '2026-06-04 08:16:36'),
(80, 48, 5, 'ISPSC-MC-REC-2026-4URQGK', 'DIGITAL SIGNATURE APPLIED', 'Signature applied.', '2026-06-04 08:18:38', '2026-06-04 08:18:38'),
(81, 48, 4, 'ISPSC-MC-SAG-2026-W2BHIU', 'DIGITAL SIGNATURE APPLIED', 'Signature applied.', '2026-06-04 08:19:11', '2026-06-04 08:19:11'),
(82, 49, 4, 'ISPSC-MC-SAG-2026-W2BHIU', 'TIME OF HELLO', 'Physical item registered for tracking.', '2026-06-04 08:19:57', '2026-06-04 08:19:57'),
(83, 50, 4, 'ISPSC-MC-SAG-2026-W2BHIU', 'TIME OF HELLO', 'Physical item registered for tracking.', '2026-06-04 08:20:37', '2026-06-04 08:20:37'),
(84, 49, 4, 'ISPSC-MC-SAG-2026-W2BHIU', 'DIGITAL SIGNATURE APPLIED', 'Signature applied.', '2026-06-04 08:20:56', '2026-06-04 08:20:56'),
(85, 49, 6, 'ISPSC-MC-REC-2026-4URQGK', 'DIGITAL SIGNATURE APPLIED', 'Signature applied.', '2026-06-04 08:21:33', '2026-06-04 08:21:33'),
(86, 51, 11, 'ISPSC-MC-SAG-2026-W2BHIU', 'CREATED', 'Physical item registered: Papel ni Juday', '2026-06-08 01:00:18', '2026-06-08 01:00:18');

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
(12, '2026_06_03_143554_add_priority_to_documents_table', 3);

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
(12, 5, 'finished', 'CONGRATS! Document ISPSC-UIP-6A1CF580A9279 is now fully signed.', 'http://127.0.0.1:8000/document/view/5', 0, '2026-06-01 03:00:29', '2026-06-01 03:00:29'),
(14, 4, 'disseminated', 'Records Office shared a finalized document: 5', 'http://127.0.0.1:8000/document/view/5', 0, '2026-06-01 03:02:29', '2026-06-01 03:02:29'),
(16, 5, 'incoming', 'Incoming document: ISPSC-UIP-6A1D10C52CFCE is ready for your signature.', 'http://127.0.0.1:8000/document/view/6', 0, '2026-06-01 04:56:38', '2026-06-01 04:56:38'),
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
(56, 6, 'finished', 'CONGRATS! Document ISPSC-06/03/2026-03:46:42 is now fully signed.', 'http://127.0.0.1:8000/document/view/35', 0, '2026-06-03 08:07:30', '2026-06-03 08:07:30'),
(60, 9, 'incoming', 'Action Required: ISPSC-06/04/2026-11:09:10', 'http://127.0.0.1:8000/document/view/40', 0, '2026-06-04 03:09:10', '2026-06-04 03:09:10'),
(61, 9, 'incoming', 'Action Required: ISPSC-06/04/2026-11:20:55', 'http://127.0.0.1:8000/document/view/41', 0, '2026-06-04 03:20:55', '2026-06-04 03:20:55'),
(63, 6, 'finished', 'Congrats! Your document is fully signed.', 'http://127.0.0.1:8000/document/view/42', 0, '2026-06-04 03:32:39', '2026-06-04 03:32:39'),
(64, 9, 'incoming', 'Action Required: ISPSC-06/04/2026-02:06:08', 'http://127.0.0.1:8000/document/view/43', 0, '2026-06-04 06:06:08', '2026-06-04 06:06:08'),
(65, 9, 'incoming', 'Action Required: ISPSC-06/04/2026-02:11:05', 'http://127.0.0.1:8000/document/view/44', 0, '2026-06-04 06:11:05', '2026-06-04 06:11:05'),
(66, 6, 'finished', 'Congrats! Your document is fully signed.', 'http://127.0.0.1:8000/document/view/44', 0, '2026-06-04 06:12:17', '2026-06-04 06:12:17'),
(67, 6, 'finished', 'Congrats! Your document is fully signed.', 'http://127.0.0.1:8000/document/view/43', 0, '2026-06-04 06:12:27', '2026-06-04 06:12:27'),
(68, 6, 'finished', 'Congrats! Your document is fully signed.', 'http://127.0.0.1:8000/document/view/41', 0, '2026-06-04 06:12:42', '2026-06-04 06:12:42'),
(69, 6, 'finished', 'Congrats! Your document is fully signed.', 'http://127.0.0.1:8000/document/view/40', 0, '2026-06-04 06:13:04', '2026-06-04 06:13:04'),
(70, 9, 'incoming', 'Action Required: ISPSC-06/04/2026-02:50:13', 'http://127.0.0.1:8000/document/view/45', 0, '2026-06-04 06:50:13', '2026-06-04 06:50:13'),
(71, 11, 'incoming', 'Action Required: ISPSC-06/04/2026-03:07:57', 'http://127.0.0.1:8000/document/view/46', 0, '2026-06-04 07:07:57', '2026-06-04 07:07:57'),
(72, 6, 'finished', 'Congrats! Your document is fully signed.', 'http://127.0.0.1:8000/document/view/46', 0, '2026-06-04 07:08:32', '2026-06-04 07:08:32'),
(73, 4, 'incoming', 'Action Required: ISPSC-06/04/2026-03:15:15', 'http://127.0.0.1:8000/document/view/47', 0, '2026-06-04 07:15:16', '2026-06-04 07:15:16'),
(74, 5, 'incoming', 'Action Required: ISPSC-06/04/2026-04:16:28', 'http://127.0.0.1:8000/document/view/48', 0, '2026-06-04 08:16:28', '2026-06-04 08:16:28'),
(75, 4, 'incoming', 'Incoming document: ISPSC-06/04/2026-04:16:28', 'http://127.0.0.1:8000/document/view/48', 0, '2026-06-04 08:18:22', '2026-06-04 08:18:22'),
(76, 11, 'finished', 'Congrats! Your document is fully signed.', 'http://127.0.0.1:8000/document/view/48', 0, '2026-06-04 08:19:11', '2026-06-04 08:19:11'),
(77, 4, 'incoming', 'Action Required: ISPSC-06/04/2026-04:19:54', 'http://127.0.0.1:8000/document/view/49', 0, '2026-06-04 08:19:54', '2026-06-04 08:19:54'),
(78, 5, 'incoming', 'Action Required: ISPSC-06/04/2026-04:20:37', 'http://127.0.0.1:8000/document/view/50', 0, '2026-06-04 08:20:37', '2026-06-04 08:20:37'),
(79, 6, 'incoming', 'Incoming document: ISPSC-06/04/2026-04:19:54', 'http://127.0.0.1:8000/document/view/49', 0, '2026-06-04 08:20:52', '2026-06-04 08:20:52'),
(80, 4, 'finished', 'Congrats! Your document is fully signed.', 'http://127.0.0.1:8000/document/view/49', 0, '2026-06-04 08:21:33', '2026-06-04 08:21:33'),
(81, 9, 'incoming', 'Action Required: New Document ISPSC-06082026-090011-GZPX', 'http://127.0.0.1:8000/document/view/51', 0, '2026-06-08 01:00:11', '2026-06-08 01:00:11');

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
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `signatories`
--

INSERT INTO `signatories` (`id`, `document_id`, `user_id`, `x_pos`, `y_pos`, `page_num`, `sign_order`, `status`, `signature_data`, `signed_at`, `created_at`, `updated_at`) VALUES
(57, 48, 5, NULL, NULL, 1, 1, 'signed', 'PHYSICAL_RECEIPT', '2026-06-04 08:18:22', '2026-06-04 08:16:28', '2026-06-04 08:18:22'),
(58, 48, 4, NULL, NULL, 1, 2, 'signed', 'PHYSICAL_RECEIPT', '2026-06-04 08:19:11', '2026-06-04 08:16:36', '2026-06-04 08:19:11'),
(59, 49, 4, NULL, NULL, 1, 1, 'signed', 'PHYSICAL_RECEIPT', '2026-06-04 08:20:52', '2026-06-04 08:19:54', '2026-06-04 08:20:52'),
(60, 49, 6, NULL, NULL, 1, 2, 'signed', 'PHYSICAL_RECEIPT', '2026-06-04 08:21:33', '2026-06-04 08:19:57', '2026-06-04 08:21:33'),
(61, 50, 5, NULL, NULL, 1, 1, 'pending', NULL, NULL, '2026-06-04 08:20:37', '2026-06-04 08:20:37'),
(62, 51, 9, NULL, NULL, 1, 1, 'pending', NULL, NULL, '2026-06-08 01:00:11', '2026-06-08 01:00:11');

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
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'staff',
  `office_id` varchar(50) DEFAULT NULL,
  `campus_code` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `role`, `office_id`, `campus_code`) VALUES
(4, 'Lynmuel Morilla', 'nylmorilla@gmail.com', NULL, '$2y$12$IabWcuON6FIAtX.YsyFoRuMnhTe3sWOpHJJqjd8DAsQLmPrSaZT/S', NULL, '2026-06-01 02:50:36', '2026-06-01 02:50:36', 'staff', 'ISPSC-MC-SAG-2026-W2BHIU', '0001'),
(5, 'Marc Jerick', 'emarcjerick@gmail.com', NULL, '$2y$12$AFSORSlr4197xoWA1bFB9e0WxzydjvONdXfJLlFHE6OiaoQZk7l6W', NULL, '2026-06-01 02:58:37', '2026-06-01 02:58:37', 'staff', 'ISPSC-MC-REC-2026-4URQGK', '0001'),
(6, 'Test Admin', 'testadmin@gmail.com', NULL, '$2y$12$7PHhvggEtJavLGuQ7j8v.e.EwwyCjT3pp6QRV1.yQESPoGAPPII0i', 'lmfhpmNzHSACpc1YzpZWXB2wdIkO32WKoHPxEeOLUUJWjrw7XdsSEXQx6QBS', '2026-06-03 01:50:01', '2026-06-03 01:50:01', 'superadmin', 'ISPSC-MC-REC-2026-4URQGK', '0001'),
(9, 'Desiree Ann P. Guzman', 'desireeann000@gmail.com', NULL, '$2y$12$HBZYlZRVag8pOYKeUN0td.YMeORyE7HYaNSPlb3Lg0KKYNAchUHzi', NULL, '2026-06-04 03:00:02', '2026-06-04 03:00:02', 'staff', 'ISPSC-MC-FIN-2026-W6CH2Y', '0001'),
(11, 'Jude Lawrence J. Raganit', 'juderaganit56@gmail.com', NULL, '$2y$12$u.0DuPdG4C02ELSBz7czPu1FuLpJ7yyNxB4wcD5FQb13BWmPmyTbq', NULL, '2026-06-04 07:06:59', '2026-06-08 00:41:21', 'staff', 'ISPSC-MC-SAG-2026-W2BHIU', '0001');

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `document_attachments`
--
ALTER TABLE `document_attachments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `document_logs`
--
ALTER TABLE `document_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `signatories`
--
ALTER TABLE `signatories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `signature_positions`
--
ALTER TABLE `signature_positions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

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
