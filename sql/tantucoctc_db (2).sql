-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 06, 2025 at 01:26 AM
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
-- Database: `tantucoctc_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `b2b_address`
--

CREATE TABLE `b2b_address` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `street` varchar(255) DEFAULT NULL,
  `barangay` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `province` varchar(255) DEFAULT NULL,
  `zip_code` varchar(255) DEFAULT NULL,
  `full_address` text DEFAULT NULL,
  `address_notes` text DEFAULT NULL,
  `delivery_address_lat` decimal(10,7) DEFAULT NULL,
  `delivery_address_lng` decimal(10,7) DEFAULT NULL,
  `status` enum('inactive','active') NOT NULL DEFAULT 'inactive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `b2b_address`
--

INSERT INTO `b2b_address` (`id`, `user_id`, `street`, `barangay`, `city`, `province`, `zip_code`, `full_address`, `address_notes`, `delivery_address_lat`, `delivery_address_lng`, `status`, `created_at`, `updated_at`) VALUES
(1, 4, 'Tuliao St.', 'Atulayan Sur', 'Tuguegarao City', 'Cagayan', '3600', 'Tuliao St., Atulayan Sur, Tuguegarao City, Cagayan, 3600', NULL, 17.6333100, 121.7189366, 'active', '2025-10-05 14:18:48', '2025-10-05 14:18:48');

-- --------------------------------------------------------

--
-- Table structure for table `b2b_details`
--

CREATE TABLE `b2b_details` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `certificate_registration` varchar(255) NOT NULL,
  `business_permit` varchar(255) NOT NULL,
  `business_name` varchar(100) DEFAULT NULL,
  `tin_number` varchar(20) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `contact_person_number` varchar(20) NOT NULL,
  `status` enum('approved','rejected') DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `b2b_details`
--

INSERT INTO `b2b_details` (`id`, `user_id`, `certificate_registration`, `business_permit`, `business_name`, `tin_number`, `contact_number`, `contact_person`, `contact_person_number`, `status`, `created_at`, `updated_at`) VALUES
(1, 4, 'assets/upload/requirements/certificate_4_1759671987.pdf', 'assets/upload/requirements/permit_4_1759671987.pdf', 'Ace Hardware', '000-11100-111', '09453813159', 'Renza Cabrera Bancud', '09453813159', 'approved', '2025-10-05 13:46:27', '2025-10-05 13:46:27');

-- --------------------------------------------------------

--
-- Table structure for table `banks`
--

CREATE TABLE `banks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `account_number` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `banks`
--

INSERT INTO `banks` (`id`, `name`, `image`, `account_number`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Gcash', 'assets/upload/bank/1752103590_686efaa6981e2.jpg', '09453813158', '2025-07-09 23:26:30', '2025-07-09 23:26:30', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `image`, `description`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Cement & Concrete', NULL, 'Includes cement, blocks, and concrete materials.', 1, '2025-07-06 02:40:29', '2025-07-06 02:40:29', NULL),
(2, 'Steel & Metal Works', NULL, 'Steel rebars and other structural metals.', 1, '2025-07-06 02:40:29', '2025-07-06 02:40:29', NULL),
(3, 'Wood & Boards', NULL, 'Plywood, lumber, and board materials.', 1, '2025-07-06 02:40:29', '2025-07-06 02:40:29', NULL),
(4, 'Roofing Materials', NULL, 'Corrugated sheets and roofing accessories.', 1, '2025-07-06 02:40:29', '2025-07-06 02:40:29', NULL),
(5, 'Plumbing', NULL, 'Pipes and fittings for water systems.', 1, '2025-07-06 02:40:29', '2025-07-06 02:40:29', NULL),
(6, 'Electrical', NULL, 'Electrical wires, tapes, and tools.', 1, '2025-07-06 02:40:29', '2025-07-06 02:40:29', NULL),
(7, 'Paint & Finishing', NULL, 'Paints, coatings, and finishing products.', 1, '2025-07-06 02:40:29', '2025-07-06 02:40:29', NULL),
(8, 'Hardware & Fixtures', NULL, 'Handles, knobs, locks, and similar items.', 1, '2025-07-06 02:40:29', '2025-07-06 02:40:29', NULL),
(9, 'Tools', NULL, 'Manual tools used in construction or repair.', 1, '2025-07-06 02:40:29', '2025-07-06 02:40:29', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `company_settings`
--

CREATE TABLE `company_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `company_logo` varchar(255) DEFAULT NULL,
  `company_email` varchar(255) DEFAULT NULL,
  `company_phone` text DEFAULT NULL,
  `company_tel` varchar(30) DEFAULT NULL,
  `company_telefax` varchar(30) DEFAULT NULL,
  `company_address` varchar(255) DEFAULT NULL,
  `company_vat_reg` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `company_settings`
--

INSERT INTO `company_settings` (`id`, `company_logo`, `company_email`, `company_phone`, `company_tel`, `company_telefax`, `company_address`, `company_vat_reg`, `created_at`, `updated_at`) VALUES
(1, 'assets/upload/1759400911_Group 1000004820.png', 'tantucoconstruction@gmail.com', '(042)525-8888', '(042) 525-8888 / 717-02551', '(042) 525-8188', 'Barangay Balubal, Sariaya, 4322, Quezon Province', '005-345-069-000', NULL, '2025-07-09 11:49:35');

-- --------------------------------------------------------

--
-- Table structure for table `credit_partial_payments`
--

CREATE TABLE `credit_partial_payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `purchase_request_id` bigint(20) UNSIGNED NOT NULL,
  `bank_id` bigint(20) UNSIGNED DEFAULT NULL,
  `paid_amount` decimal(10,2) NOT NULL,
  `due_date` date NOT NULL,
  `amount_to_pay` decimal(10,2) DEFAULT NULL,
  `paid_date` date DEFAULT NULL,
  `status` enum('pending','unpaid','paid','overdue','reject') NOT NULL DEFAULT 'pending',
  `proof_payment` varchar(255) DEFAULT NULL,
  `reference_number` varchar(255) DEFAULT NULL,
  `approved_at` date DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `credit_payments`
--

CREATE TABLE `credit_payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `purchase_request_id` bigint(20) UNSIGNED NOT NULL,
  `bank_id` bigint(20) UNSIGNED DEFAULT NULL,
  `paid_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `due_date` date NOT NULL,
  `paid_date` date DEFAULT NULL,
  `status` enum('pending','unpaid','paid','overdue','reject') NOT NULL DEFAULT 'pending',
  `proof_payment` varchar(255) DEFAULT NULL,
  `reference_number` varchar(255) DEFAULT NULL,
  `approved_at` date DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `deliveries`
--

CREATE TABLE `deliveries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `delivery_rider_id` bigint(20) UNSIGNED DEFAULT NULL,
  `quantity` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `tracking_number` varchar(255) DEFAULT NULL,
  `status` enum('pending','assigned','on_the_way','delivered','cancelled','returned','refunded') NOT NULL DEFAULT 'pending',
  `delivery_date` timestamp NULL DEFAULT NULL,
  `proof_delivery` varchar(255) DEFAULT NULL,
  `delivery_remarks` text DEFAULT NULL,
  `sales_invoice_flg` int(11) NOT NULL DEFAULT 0,
  `delivery_latitude` decimal(10,7) NOT NULL DEFAULT 13.9655000,
  `delivery_longitude` decimal(10,7) NOT NULL DEFAULT 121.5348000,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `deliveries`
--

INSERT INTO `deliveries` (`id`, `order_id`, `delivery_rider_id`, `quantity`, `tracking_number`, `status`, `delivery_date`, `proof_delivery`, `delivery_remarks`, `sales_invoice_flg`, `delivery_latitude`, `delivery_longitude`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 10, '3C38AC8A-FFC0-4CF9-84EC-B8200C6C85DF', 'on_the_way', NULL, NULL, NULL, 0, 13.9655000, 121.5348000, '2025-10-05 15:09:11', '2025-10-05 16:18:41');

-- --------------------------------------------------------

--
-- Table structure for table `delivery_histories`
--

CREATE TABLE `delivery_histories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `delivery_id` bigint(20) UNSIGNED NOT NULL,
  `logged_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `delivery_ratings`
--

CREATE TABLE `delivery_ratings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `delivery_id` bigint(20) UNSIGNED NOT NULL,
  `rating` tinyint(3) UNSIGNED NOT NULL,
  `feedback` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
-- Table structure for table `inventories`
--

CREATE TABLE `inventories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('in','out') NOT NULL,
  `quantity` int(11) NOT NULL,
  `reason` enum('restock','sold','returned','damaged','stock update','other') DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventories`
--

INSERT INTO `inventories` (`id`, `product_id`, `type`, `quantity`, `reason`, `created_at`, `updated_at`) VALUES
(1, 1, 'in', 1000, 'restock', '2025-08-19 11:06:08', '2025-08-19 11:06:08'),
(2, 2, 'in', 600, 'restock', '2025-08-19 11:06:36', '2025-08-19 11:06:36'),
(3, 4, 'in', 300, 'restock', '2025-08-19 11:07:08', '2025-08-19 11:07:08'),
(4, 3, 'in', 500, 'restock', '2025-08-19 11:07:42', '2025-08-19 11:07:42'),
(5, 5, 'in', 500, 'restock', '2025-08-19 11:08:21', '2025-08-19 11:08:21'),
(6, 6, 'in', 300, 'restock', '2025-08-19 11:09:20', '2025-08-19 11:09:20'),
(7, 7, 'in', 100, 'restock', '2025-08-19 11:11:31', '2025-08-19 11:11:31'),
(8, 10, 'in', 300, 'restock', '2025-08-19 11:11:58', '2025-08-19 11:11:58'),
(9, 8, 'in', 200, 'restock', '2025-08-19 11:12:24', '2025-08-19 11:12:24'),
(10, 9, 'in', 300, 'restock', '2025-08-19 11:12:39', '2025-08-19 11:12:39'),
(11, 12, 'in', 200, 'restock', '2025-08-19 11:12:55', '2025-08-19 11:12:55'),
(12, 1, 'out', 988, 'sold', '2025-09-06 03:42:34', '2025-09-06 03:42:34'),
(13, 1, 'out', 4, 'sold', '2025-09-06 03:46:04', '2025-09-06 03:46:04'),
(14, 1, 'out', 3, 'sold', '2025-09-27 04:28:47', '2025-09-27 04:28:47'),
(15, 1, 'out', 1, 'sold', '2025-09-27 04:35:08', '2025-09-27 04:35:08');

-- --------------------------------------------------------

--
-- Table structure for table `manual_email_order`
--

CREATE TABLE `manual_email_order` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `customer_type` varchar(20) DEFAULT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `customer_address` varchar(255) DEFAULT NULL,
  `customer_phone_number` varchar(255) DEFAULT NULL,
  `order_date` date DEFAULT NULL,
  `purchase_request` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`purchase_request`)),
  `remarks` text DEFAULT NULL,
  `delivery_fee` int(11) DEFAULT 0,
  `status` enum('pending','waiting','approve','rejected') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sender_id` int(11) NOT NULL,
  `recipient_id` int(11) NOT NULL,
  `text` text DEFAULT NULL,
  `is_file` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
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
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_resets_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
(4, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(5, '2024_08_06_225508_create_company_settings_table', 1),
(6, '2024_09_01_075226_create_b2b_details_table', 1),
(7, '2024_10_03_113319_create_terms_conditions_table', 1),
(8, '2025_06_21_135400_create_categories_table', 1),
(9, '2025_06_21_135453_create_products_table', 1),
(10, '2025_06_21_140752_create_product_images_table', 1),
(11, '2025_06_21_140757_create_inventories_table', 1),
(12, '2025_06_21_233529_add_expiry_date_to_products_table', 1),
(13, '2025_06_22_004215_create_b2b_address_table', 1),
(14, '2025_06_22_004301_create_orders_table', 1),
(15, '2025_06_22_004327_create_order_items_table', 1),
(16, '2025_06_22_004359_create_deliveries_table', 1),
(17, '2025_07_01_014724_create_banks_table', 1),
(18, '2025_07_01_014725_create_purchase_requests_table', 1),
(19, '2025_07_01_014835_create_purchase_request_items_table', 1),
(20, '2025_07_05_190918_create_delivery_histories_table', 1),
(21, '2025_07_06_170246_create_user_logs_table', 1),
(22, '2025_07_06_231655_create_messages_table', 1),
(23, '2025_07_08_073237_create_notifications_table', 1),
(28, '2025_07_11_024508_create_delivery_ratings_table', 3),
(31, '2025_07_12_141102_create_credit_payments_table', 4),
(32, '2025_08_08_212910_create_paid_payments_table', 4),
(33, '2025_08_08_212934_credit_partial_payments_table', 4),
(34, '2025_08_10_081210_create_manual_email_order_table', 5),
(35, '2025_08_23_194117_create_product_ratings_table', 6),
(36, '2025_07_10_203040_create_purchase_request_returns', 7),
(37, '2025_07_10_203153_create_purchase_request_refunds', 8);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Recipient user',
  `type` varchar(255) NOT NULL COMMENT 'purchase_request, delivery, etc',
  `message` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `message`, `read_at`, `created_at`, `updated_at`) VALUES
(1, 3, 'purchase_request', 'A new purchase request has been submitted by John B2B. <br><a href=\"http://127.0.0.1:8000/salesofficer/purchase-requests/all\">Visit</a>', NULL, '2025-08-31 13:17:13', '2025-08-31 13:17:13'),
(2, 3, 'purchase_request', 'A new purchase request has been submitted by John B2B. <br><a href=\"http://127.0.0.1:8000/salesofficer/purchase-requests/all\">Visit</a>', NULL, '2025-08-31 13:29:38', '2025-08-31 13:29:38'),
(3, 3, 'purchase_request', 'A new purchase request has been submitted by John B2B. <br><a href=\"http://127.0.0.1:8000/salesofficer/purchase-requests/all\">Visit</a>', NULL, '2025-08-31 13:29:50', '2025-08-31 13:29:50'),
(4, 3, 'purchase_request', 'A new purchase request has been submitted by John B2B. <br><a href=\"http://127.0.0.1:8000/salesofficer/purchase-requests/all\">Visit</a>', NULL, '2025-08-31 14:15:15', '2025-08-31 14:15:15'),
(5, 3, 'purchase_request', 'A new purchase request has been submitted by John B2B. <br><a href=\"http://127.0.0.1:8000/salesofficer/purchase-requests/all\">Visit</a>', NULL, '2025-08-31 14:16:53', '2025-08-31 14:16:53'),
(6, 3, 'purchase_request', 'A new purchase request has been submitted by John B2B. <br><a href=\"http://127.0.0.1:8000/salesofficer/purchase-requests/all\">Visit</a>', NULL, '2025-08-31 14:39:11', '2025-08-31 14:39:11'),
(7, 3, 'purchase_request', 'A new purchase request has been submitted by John B2B. <br><a href=\"http://127.0.0.1:8000/salesofficer/purchase-requests/all\">Visit</a>', NULL, '2025-08-31 14:39:38', '2025-08-31 14:39:38'),
(8, 4, 'quotation_sent', 'A quotation has been sent for your purchase request #1. <br><a href=\"http://127.0.0.1:8000/b2b/purchase-requests\">Visit Link</a>', NULL, '2025-08-31 14:44:48', '2025-08-31 14:44:48'),
(9, 3, 'purchase_request', 'A new purchase request has been submitted by John B2B. <br><a href=\"http://127.0.0.1:8000/salesofficer/purchase-requests/all\">Visit</a>', NULL, '2025-08-31 14:54:35', '2025-08-31 14:54:35'),
(10, 3, 'purchase_request', 'A new purchase request has been submitted by John B2B. <br><a href=\"http://127.0.0.1:8000/salesofficer/purchase-requests/all\">Visit</a>', NULL, '2025-08-31 15:10:32', '2025-08-31 15:10:32'),
(11, 3, 'purchase_request', 'A new purchase request has been submitted by John B2B. <br><a href=\"http://127.0.0.1:8000/salesofficer/purchase-requests/all\">Visit</a>', NULL, '2025-08-31 15:13:49', '2025-08-31 15:13:49'),
(12, 3, 'purchase_request', 'A new purchase request has been submitted by John B2B. <br><a href=\"http://127.0.0.1:8000/salesofficer/purchase-requests/all\">Visit</a>', NULL, '2025-08-31 15:13:55', '2025-08-31 15:13:55'),
(13, 3, 'purchase_request', 'A new purchase request has been submitted by John B2B. <br><a href=\"http://127.0.0.1:8000/salesofficer/purchase-requests/all\">Visit</a>', NULL, '2025-08-31 15:20:41', '2025-08-31 15:20:41'),
(14, 3, 'purchase_request', 'A new purchase request has been submitted by John B2B. <br><a href=\"http://127.0.0.1:8000/salesofficer/purchase-requests/all\">Visit</a>', NULL, '2025-08-31 15:21:49', '2025-08-31 15:21:49'),
(15, 3, 'purchase_request', 'A new purchase request has been submitted by John B2B. <br><a href=\"http://127.0.0.1:8000/salesofficer/purchase-requests/all\">Visit</a>', NULL, '2025-08-31 15:23:21', '2025-08-31 15:23:21'),
(16, 3, 'purchase_request', 'A new purchase request has been submitted by John B2B. <br><a href=\"http://127.0.0.1:8000/salesofficer/purchase-requests/all\">Visit</a>', NULL, '2025-08-31 15:23:49', '2025-08-31 15:23:49'),
(17, 3, 'purchase_request', 'A new purchase request has been updated by John B2B. <br><a href=\"http://127.0.0.1:8000/salesofficer/purchase-requests/all\">Visit</a>', NULL, '2025-09-04 14:18:52', '2025-09-04 14:18:52'),
(18, 3, 'purchase_request', 'A new purchase request has been updated by John B2B. <br><a href=\"http://127.0.0.1:8000/salesofficer/purchase-requests/all\">Visit</a>', NULL, '2025-09-04 14:18:56', '2025-09-04 14:18:56'),
(19, 1, 'purchase_request', 'A PO (ID: 1) was submitted by John B2B with (Pay Now). <a href=\"http://127.0.0.1:8000/home?1\" class=\'d-none\'>Visit Link</a>', '2025-09-06 08:34:03', '2025-09-04 14:21:11', '2025-09-06 08:34:03'),
(20, 4, 'order', 'Your submitted PO has been processed. A sales order #REF 1-68B9A39A7521B was created. <br><a href=\"http://127.0.0.1:8000/b2b/quotations/review?1\">Visit Link</a>', NULL, '2025-09-04 14:35:06', '2025-09-04 14:35:06'),
(21, 2, 'assignment', 'You have been assigned to deliver order #REF 1-68B9A39A7521B. <br><a href=\"http://127.0.0.1:8000/home\">Visit Link</a>', NULL, '2025-09-04 14:40:31', '2025-09-04 14:40:31'),
(22, 4, 'delivery', 'Your order #REF 1-68B9A39A7521B is now assigned for delivery. <br><a href=\"http://127.0.0.1:8000/b2b/delivery\">Visit Link</a>', NULL, '2025-09-04 14:40:31', '2025-09-04 14:40:31'),
(23, 4, 'delivery', 'Your order #REF 1-68B9A39A7521B is now on the way. <br><a href=\"http://127.0.0.1:8000/b2b/delivery/track/1\">Visit Link</a>', NULL, '2025-09-04 14:40:59', '2025-09-04 14:40:59'),
(24, 4, 'delivery', 'Your order #REF 1-68B9A39A7521B is now on the way. <br><a href=\"http://127.0.0.1:8000/b2b/delivery/track/1\">Visit Link</a>', NULL, '2025-09-04 14:44:54', '2025-09-04 14:44:54'),
(25, 3, 'purchase_request', 'A new purchase request has been updated by John B2B Two. <br><a href=\"http://127.0.0.1:8000/salesofficer/purchase-requests/all\">Visit</a>', NULL, '2025-09-04 14:56:20', '2025-09-04 14:56:20'),
(26, 3, 'purchase_request', 'A new purchase request has been updated by John B2B Two. <br><a href=\"http://127.0.0.1:8000/salesofficer/purchase-requests/all\">Visit</a>', NULL, '2025-09-04 14:56:22', '2025-09-04 14:56:22'),
(27, 1, 'purchase_request', 'A PO (ID: 2) was submitted by Ben Tulfo with (Pay Now). <a href=\"http://127.0.0.1:8000/home?2\" class=\'d-none\'>Visit Link</a>', '2025-09-06 08:34:03', '2025-09-04 14:58:11', '2025-09-06 08:34:03'),
(28, 8, 'order', 'Your submitted PO has been processed. A sales order #REF 2-68B9A92F98022 was created. <br><a href=\"http://127.0.0.1:8000/b2b/quotations/review?2\">Visit Link</a>', NULL, '2025-09-04 14:58:55', '2025-09-04 14:58:55'),
(29, 2, 'assignment', 'You have been assigned to deliver order #REF 2-68B9A92F98022. <br><a href=\"http://127.0.0.1:8000/home\">Visit Link</a>', NULL, '2025-09-04 14:59:10', '2025-09-04 14:59:10'),
(30, 8, 'delivery', 'Your order #REF 2-68B9A92F98022 is now assigned for delivery. <br><a href=\"http://127.0.0.1:8000/b2b/delivery\">Visit Link</a>', NULL, '2025-09-04 14:59:11', '2025-09-04 14:59:11'),
(31, 3, 'purchase_request', 'A new purchase request has been updated by Ben Tulfo. <br><a href=\"http://127.0.0.1:8000/salesofficer/purchase-requests/all\">Visit</a>', NULL, '2025-09-04 15:37:15', '2025-09-04 15:37:15'),
(32, 3, 'purchase_request', 'A new purchase request has been updated by Ben Tulfo. <br><a href=\"http://127.0.0.1:8000/salesofficer/purchase-requests/all\">Visit</a>', NULL, '2025-09-04 15:37:17', '2025-09-04 15:37:17'),
(33, 1, 'purchase_request', 'PO #4 submitted by Ben Tulfo with (Pay Later) - Total: ₱0.00', '2025-09-06 08:34:03', '2025-09-04 15:40:31', '2025-09-06 08:34:03'),
(34, 1, 'purchase_request', 'PO #4 submitted by Ben Tulfo with (Pay Later) - Total: ₱0.00', '2025-09-06 08:34:03', '2025-09-04 15:47:54', '2025-09-06 08:34:03'),
(35, 3, 'purchase_request', 'A new purchase request has been updated by John B2B. <br><a href=\"http://127.0.0.1:8000/salesofficer/purchase-requests/all\">Visit</a>', NULL, '2025-09-06 12:54:18', '2025-09-06 12:54:18'),
(36, 3, 'purchase_request', 'A new purchase request has been updated by John B2B. <br><a href=\"http://127.0.0.1:8000/salesofficer/purchase-requests/all\">Visit</a>', NULL, '2025-09-07 09:51:42', '2025-09-07 09:51:42'),
(37, 1, 'purchase_request', 'A PO (ID: 1) was submitted by John B2B with (Pay Now). <a href=\"http://127.0.0.1:8000/home?1\" class=\'d-none\'>Visit Link</a>', NULL, '2025-09-07 09:52:53', '2025-09-07 09:52:53'),
(38, 4, 'order', 'Your submitted PO has been processed. A sales order #REF 1-68BD5779423DF was created. <br><a href=\"http://127.0.0.1:8000/b2b/quotations/review?1\">Visit Link</a>', NULL, '2025-09-07 09:59:21', '2025-09-07 09:59:21'),
(39, 2, 'assignment', 'You have been assigned to deliver order #REF 1-68BD5779423DF. <br><a href=\"http://127.0.0.1:8000/home\">Visit Link</a>', NULL, '2025-09-07 09:59:34', '2025-09-07 09:59:34'),
(40, 4, 'delivery', 'Your order #REF 1-68BD5779423DF is now assigned for delivery. <br><a href=\"http://127.0.0.1:8000/b2b/delivery\">Visit Link</a>', NULL, '2025-09-07 09:59:34', '2025-09-07 09:59:34'),
(41, 3, 'purchase_request', 'A new purchase request has been updated by John B2B. <br><a href=\"http://127.0.0.1:8000/salesofficer/purchase-requests/all\">Visit</a>', NULL, '2025-09-07 10:01:19', '2025-09-07 10:01:19'),
(42, 1, 'purchase_request', 'PO #2 submitted by John B2B with (Pay Later) - Total: ₱0.00', NULL, '2025-09-07 10:01:55', '2025-09-07 10:01:55'),
(43, 3, 'purchase_request', 'A new purchase request has been updated by John B2B. <br><a href=\"http://127.0.0.1:8000/salesofficer/purchase-requests/all\">Visit</a>', NULL, '2025-09-27 04:05:07', '2025-09-27 04:05:07'),
(44, 1, 'purchase_request', 'PO #1 submitted by John B2B with (Pay Later) - Total: ₱0.00', NULL, '2025-09-27 04:06:02', '2025-09-27 04:06:02'),
(45, 3, 'purchase_request', 'A new purchase request has been updated by John B2B. <br><a href=\"http://127.0.0.1:8000/salesofficer/purchase-requests/all\">Visit</a>', NULL, '2025-09-27 04:21:59', '2025-09-27 04:21:59'),
(46, 1, 'purchase_request', 'A PO (ID: 2) was submitted by John B2B with (Pay Now). <a href=\"http://127.0.0.1:8000/home?2\" class=\'d-none\'>Visit Link</a>', NULL, '2025-09-27 04:22:41', '2025-09-27 04:22:41'),
(47, 4, 'order', 'Your submitted PO has been processed. A sales order #REF 1-68D76AC11005E was created. <br><a href=\"http://127.0.0.1:8000/b2b/quotations/review?1\">Visit Link</a>', NULL, '2025-09-27 04:40:33', '2025-09-27 04:40:33'),
(48, 2, 'assignment', 'You have been assigned to deliver order #REF 1-68D76AC11005E. <br><a href=\"http://127.0.0.1:8000/home\">Visit Link</a>', NULL, '2025-09-27 04:40:47', '2025-09-27 04:40:47'),
(49, 4, 'delivery', 'Your order #REF 1-68D76AC11005E is now assigned for delivery. <br><a href=\"http://127.0.0.1:8000/b2b/delivery\">Visit Link</a>', NULL, '2025-09-27 04:40:47', '2025-09-27 04:40:47'),
(50, 3, 'purchase_request', 'A new purchase request has been updated by John B2B. <br><a href=\"http://127.0.0.1:8000/salesofficer/purchase-requests/all\">Visit</a>', NULL, '2025-10-05 14:32:48', '2025-10-05 14:32:48'),
(51, 1, 'purchase_request', 'A PO (ID: 1) was submitted by John B2B with (Pay Now). <a href=\"http://127.0.0.1:8000/home?1\" class=\'d-none\'>Visit Link</a>', NULL, '2025-10-05 15:04:45', '2025-10-05 15:04:45'),
(52, 4, 'order', 'Your submitted PO has been processed. A sales order #REF 1-68E28A17C861A was created. <br><a href=\"http://127.0.0.1:8000/b2b/quotations/review?1\">Visit Link</a>', NULL, '2025-10-05 15:09:11', '2025-10-05 15:09:11'),
(53, 2, 'assignment', 'You have been assigned to deliver order #REF 1-68E28A17C861A. <br><a href=\"http://127.0.0.1:8000/home\">Visit Link</a>', NULL, '2025-10-05 15:09:24', '2025-10-05 15:09:24'),
(54, 4, 'delivery', 'Your order #REF 1-68E28A17C861A is now assigned for delivery. <br><a href=\"http://127.0.0.1:8000/b2b/delivery\">Visit Link</a>', NULL, '2025-10-05 15:09:24', '2025-10-05 15:09:24'),
(55, 4, 'delivery', 'Your order #REF 1-68E28A17 is now on the way. <br><a href=\"http://127.0.0.1:8000/b2b/delivery/track/1\">Visit Link</a>', NULL, '2025-10-05 16:18:41', '2025-10-05 16:18:41');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `order_number` varchar(255) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `b2b_address_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ordered_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_number`, `total_amount`, `b2b_address_id`, `ordered_at`, `created_at`, `updated_at`) VALUES
(1, 4, 'REF 1-68E28A17', 180.00, 1, '2025-10-05 15:09:11', '2025-10-05 15:09:11', '2025-10-05 15:09:11');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`, `subtotal`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 10, 18.00, 180.00, '2025-10-05 15:09:11', '2025-10-05 15:09:11');

-- --------------------------------------------------------

--
-- Table structure for table `paid_payments`
--

CREATE TABLE `paid_payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `purchase_request_id` bigint(20) UNSIGNED NOT NULL,
  `bank_id` bigint(20) UNSIGNED DEFAULT NULL,
  `paid_amount` decimal(10,2) NOT NULL,
  `paid_date` date DEFAULT NULL,
  `status` enum('pending','paid') NOT NULL DEFAULT 'pending',
  `proof_payment` varchar(255) DEFAULT NULL,
  `reference_number` varchar(255) DEFAULT NULL,
  `approved_at` date DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`email`, `token`, `created_at`) VALUES
('superadmin@example.com', '$2y$10$k6arU1/lzH2WYUMLn9zKcuV.FLULdSSWoGoFDoA7WN4iTLC57sp9K', '2025-07-20 05:17:41');

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `sku` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount` int(5) NOT NULL DEFAULT 0,
  `discounted_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `expiry_date` date DEFAULT NULL,
  `maximum_stock` int(11) DEFAULT 0,
  `critical_stock_level` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `sku`, `description`, `price`, `discount`, `discounted_price`, `expiry_date`, `maximum_stock`, `critical_stock_level`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'Cement (40kg bag)', 'SKU-000001', 'Portland type 1 cement ideal for general construction use', 260.00, 0, 0.00, NULL, 1000, 20, '2025-06-24 18:26:44', '2025-08-19 10:29:22', NULL),
(2, 1, 'Concrete Hollow Block 6”', 'SKU-000002', 'Standard 6-inch hollow block for wall partitions', 18.00, 0, 0.00, NULL, 600, 15, '2025-06-24 18:27:37', '2025-08-19 10:42:33', NULL),
(3, 2, '10mm Rebar (Steel Bar)', 'SKU-000003', '10mm diameter steel reinforcement bar for slabs and columns', 175.00, 0, 0.00, NULL, 500, 10, '2025-06-24 18:29:11', '2025-08-19 10:42:13', NULL),
(4, 3, 'Plywood (1/4” x 4’ x 8’)', 'SKU-000004', 'Commercial plywood used for temporary walls and ceiling work', 495.00, 0, 0.00, NULL, 1000, 20, '2025-06-24 18:29:48', '2025-08-19 10:44:15', NULL),
(5, 4, 'GL Sheet (Corrugated, 8ft)', 'SKU-000005', 'Galvanized iron sheet for roofing and siding', 320.00, 0, 0.00, NULL, 1000, 20, '2025-06-24 18:30:34', '2025-08-19 10:43:12', NULL),
(6, 5, '1” PVC Pipe (10ft)', 'SKU-000006', 'Polyvinyl chloride pipe for water supply systems', 120.00, 0, 0.00, NULL, 1000, 20, '2025-06-24 18:31:20', '2025-08-19 10:42:02', NULL),
(7, 6, 'Electrical Tape (Black', 'SKU-000007', 'Insulating tape for electrical wiring', 25.00, 0, 0.00, NULL, 300, 5, '2025-06-24 18:31:48', '2025-08-19 10:42:58', NULL),
(8, 7, 'Paint – White Latex (4L)', 'SKU-000008', 'Water-based paint for interior walls and ceilings', 560.00, 0, 0.00, NULL, 500, 10, '2025-06-24 18:33:13', '2025-08-19 10:43:21', NULL),
(9, 8, 'Door Knob (Entrance Set)', 'SKU-000009', 'Heavy-duty cylindrical door knob with lockset', 620.00, 0, 0.00, NULL, 1000, 20, '2025-06-24 18:33:56', '2025-08-19 10:42:42', NULL),
(10, 9, '1” Paint Brush', 'SKU-000010', 'Standard 1-inch brush used for painting and touch-ups', 35.00, 2, 34.30, NULL, 1000, 20, '2025-06-24 18:34:33', '2025-08-23 07:33:37', NULL),
(11, 1, 'sdfdsf', 'SKU-000011', 'dgdfgdfg', 4435.00, 0, 0.00, '2025-07-23', 0, 0, '2025-07-06 03:29:34', '2025-07-06 03:30:29', '2025-07-06 03:30:29'),
(12, 9, 'The Saw', 'SKU-000012', 'This is the saw episodes tools.', 100.00, 0, 0.00, NULL, 500, 10, '2025-08-04 14:17:20', '2025-08-19 10:44:27', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `main_image_path` text DEFAULT NULL,
  `is_main` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image_path`, `main_image_path`, `is_main`, `created_at`, `updated_at`) VALUES
(1, 1, 'assets/upload/products/1750847204_Cement.jpg', NULL, 1, '2025-06-24 18:26:44', '2025-08-19 10:29:22'),
(2, 2, 'assets/upload/products/1750847258_Concrete Hollow Block.jpg', NULL, 1, '2025-06-24 18:27:38', '2025-08-19 10:42:33'),
(3, 3, 'assets/upload/products/1750847351_texture-steel-deformed-bars-background-600nw-2371218435.webp', NULL, 1, '2025-06-24 18:29:11', '2025-08-19 10:42:13'),
(4, 4, 'assets/upload/products/1750847388_Plywood.jpg', NULL, 1, '2025-06-24 18:29:48', '2025-08-19 10:44:15'),
(5, 5, 'assets/upload/products/1750847434_Roof GL Sheet Corrugated .jpg', NULL, 1, '2025-06-24 18:30:34', '2025-08-19 10:43:12'),
(6, 6, 'assets/upload/products/1750847480_PVC pipe.jpg', NULL, 1, '2025-06-24 18:31:20', '2025-08-19 10:42:02'),
(7, 8, 'assets/upload/products/1750847593_2017596014.webp', NULL, 1, '2025-06-24 18:33:13', '2025-08-19 10:43:21'),
(8, 9, 'assets/upload/products/1750847636_Door Knob set.jpg', NULL, 1, '2025-06-24 18:33:56', '2025-08-19 10:42:42'),
(9, 10, 'assets/upload/products/1750847673_Paint brush.jpg', NULL, 1, '2025-06-24 18:34:33', '2025-08-23 07:33:37'),
(10, 7, 'assets/upload/products/1750847714_Electric tape .jpg', NULL, 1, '2025-06-24 18:35:14', '2025-08-19 10:42:58'),
(11, 11, 'assets/upload/products/1751801374_TantucoCTC_Logo.png', NULL, 1, '2025-07-06 03:29:34', '2025-07-06 03:30:03'),
(12, 12, 'assets/upload/products/1754288240_saw_1.png', NULL, 0, '2025-08-04 14:17:20', '2025-08-19 10:44:27'),
(13, 12, 'assets/upload/products/1754288240_saw_2.png', NULL, 0, '2025-08-04 14:17:20', '2025-08-19 10:44:27'),
(14, 12, 'assets/upload/products/1754288240_saw_3.png', NULL, 1, '2025-08-04 14:17:20', '2025-08-19 10:44:27');

-- --------------------------------------------------------

--
-- Table structure for table `product_ratings`
--

CREATE TABLE `product_ratings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `rating` tinyint(4) NOT NULL COMMENT '1 to 5 stars',
  `review` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_requests`
--

CREATE TABLE `purchase_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `transaction_uuid` varchar(20) DEFAULT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `prepared_by_id` int(11) DEFAULT NULL,
  `status` enum('pending','quotation_sent','po_submitted','so_created','delivery_in_progress','delivered','invoice_sent','cancelled','returned','refunded','reject_quotation') DEFAULT NULL,
  `vat` int(11) DEFAULT 12,
  `b2b_delivery_date` date DEFAULT NULL,
  `delivery_fee` decimal(10,2) DEFAULT NULL,
  `credit` int(1) NOT NULL DEFAULT 0,
  `credit_amount` decimal(10,2) DEFAULT NULL,
  `credit_payment_type` varchar(20) DEFAULT NULL,
  `payment_method` enum('pay_now','pay_later') DEFAULT NULL,
  `cod_flg` tinyint(1) NOT NULL DEFAULT 0,
  `pr_remarks` text DEFAULT NULL,
  `pr_remarks_cancel` text DEFAULT NULL,
  `date_issued` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `purchase_requests`
--

INSERT INTO `purchase_requests` (`id`, `transaction_uuid`, `customer_id`, `prepared_by_id`, `status`, `vat`, `b2b_delivery_date`, `delivery_fee`, `credit`, `credit_amount`, `credit_payment_type`, `payment_method`, `cod_flg`, `pr_remarks`, `pr_remarks_cancel`, `date_issued`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, NULL, 4, NULL, 'delivery_in_progress', 12, NULL, NULL, 0, NULL, NULL, 'pay_now', 1, NULL, NULL, NULL, '2025-10-05 14:32:41', '2025-10-05 15:09:24', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `purchase_request_items`
--

CREATE TABLE `purchase_request_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `purchase_request_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `purchase_request_items`
--

INSERT INTO `purchase_request_items` (`id`, `purchase_request_id`, `product_id`, `quantity`, `subtotal`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 10, 180.00, '2025-10-05 14:32:48', '2025-10-05 14:32:48');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_request_refunds`
--

CREATE TABLE `purchase_request_refunds` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `purchase_request_id` bigint(20) UNSIGNED NOT NULL,
  `purchase_request_item_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `method` varchar(255) DEFAULT NULL,
  `reference` varchar(255) DEFAULT NULL,
  `proof` varchar(255) DEFAULT NULL,
  `admin_response` text DEFAULT NULL,
  `processed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_request_returns`
--

CREATE TABLE `purchase_request_returns` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `purchase_request_id` bigint(20) UNSIGNED NOT NULL,
  `purchase_request_item_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `reason` text DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `admin_response` text DEFAULT NULL,
  `processed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `terms_conditions`
--

CREATE TABLE `terms_conditions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `content_type` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `terms_conditions`
--

INSERT INTO `terms_conditions` (`id`, `content_type`, `content`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Terms', '<h5 data-start=\"231\" data-end=\"271\">🧾 <strong data-start=\"237\" data-end=\"271\">Tantuco CTC - Terms of Service</strong></h5>\n<h6 data-start=\"273\" data-end=\"299\">1. Acceptance of Terms</h3>\n<p data-start=\"300\" data-end=\"440\">By accessing or using Tantuco CTC\'s website, services, or purchasing any hardware products, you agree to be bound by these Terms of Service.</p>\n<h6 data-start=\"442\" data-end=\"470\">2. User Responsibilities</h3>\n<ul data-start=\"471\" data-end=\"649\">\n<li data-start=\"471\" data-end=\"545\">\n<p data-start=\"473\" data-end=\"545\">You must be at least 18 years old to use our services or place an order.</p>\n</li>\n<li data-start=\"546\" data-end=\"649\">\n<p data-start=\"548\" data-end=\"649\">You agree to provide accurate, up-to-date, and complete information during registration and checkout.</p>\n</li>\n</ul>\n<h6 data-start=\"651\" data-end=\"674\">3. Account Security</h3>\n<ul data-start=\"675\" data-end=\"839\">\n<li data-start=\"675\" data-end=\"761\">\n<p data-start=\"677\" data-end=\"761\">You are responsible for maintaining the confidentiality of your account credentials.</p>\n</li>\n<li data-start=\"762\" data-end=\"839\">\n<p data-start=\"764\" data-end=\"839\">You agree to notify us immediately of any unauthorized use of your account.</p>\n</li>\n</ul>\n<h6 data-start=\"841\" data-end=\"869\">4. Intellectual Property</h3>\n<ul data-start=\"870\" data-end=\"992\">\n<li data-start=\"870\" data-end=\"992\">\n<p data-start=\"872\" data-end=\"992\">All website content (logos, product images, text) is the property of Tantuco CTC and may not be used without permission.</p>\n</li>\n</ul>\n<h6 data-start=\"994\" data-end=\"1014\">5. Governing Law</h3>\n<ul data-start=\"1015\" data-end=\"1093\">\n<li data-start=\"1015\" data-end=\"1093\">\n<p data-start=\"1017\" data-end=\"1093\">These terms are governed by the laws of the <strong data-start=\"1061\" data-end=\"1092\">Republic of the Philippines</strong>.</p>\n</li>\n</ul>', '2025-07-08 04:03:51', '2025-07-12 02:46:32', NULL),
(2, 'Condition', '<h5 data-start=\"1100\" data-end=\"1142\">⚖️ <strong data-start=\"1106\" data-end=\"1142\">Tantuco CTC - Conditions of Sale</strong></h2>\n<h6 data-start=\"1144\" data-end=\"1170\">1. Product Information</h6>\n<ul data-start=\"1171\" data-end=\"1377\">\n<li data-start=\"1171\" data-end=\"1270\">\n<p data-start=\"1173\" data-end=\"1270\">We strive to provide accurate product descriptions, but actual product details may vary slightly.</p>\n</li>\n<li data-start=\"1271\" data-end=\"1377\">\n<p data-start=\"1273\" data-end=\"1377\">All product prices are in <strong data-start=\"1299\" data-end=\"1323\">Philippine Pesos (₱)</strong> and include applicable taxes unless otherwise stated.</p>\n</li>\n</ul>\n<h6 data-start=\"1379\" data-end=\"1404\">2. Order Confirmation</h6>\n<ul data-start=\"1405\" data-end=\"1589\">\n<li data-start=\"1405\" data-end=\"1489\">\n<p data-start=\"1407\" data-end=\"1489\">An order is considered confirmed only after full payment is received and verified.</p>\n</li>\n<li data-start=\"1490\" data-end=\"1589\">\n<p data-start=\"1492\" data-end=\"1589\">We reserve the right to cancel any order due to stock issues, pricing errors, or suspected fraud.</p>\n</li>\n</ul>\n<h6 data-start=\"1591\" data-end=\"1613\">3. Payment Methods</h6>\n<ul data-start=\"1614\" data-end=\"1699\">\n<li data-start=\"1614\" data-end=\"1699\">\n<p data-start=\"1616\" data-end=\"1699\">We accept <strong data-start=\"1626\" data-end=\"1656\">cash, GCash, bank transfer</strong>, or <strong data-start=\"1661\" data-end=\"1698\">approved business credit accounts</strong>.</p>\n</li>\n</ul>\n<h6 data-start=\"1701\" data-end=\"1716\">4. Delivery</h6>\n<ul data-start=\"1717\" data-end=\"1869\">\n<li data-start=\"1717\" data-end=\"1767\">\n<p data-start=\"1719\" data-end=\"1767\">Deliveries are made within select service areas.</p>\n</li>\n<li data-start=\"1768\" data-end=\"1869\">\n<p data-start=\"1770\" data-end=\"1869\">Delivery lead time is usually <strong data-start=\"1800\" data-end=\"1821\">1&ndash;5 business days</strong> depending on product availability and location.</p>\n</li>\n</ul>', '2025-07-08 04:04:01', '2025-07-12 02:45:25', NULL),
(3, 'Policy', '<h5 data-start=\"1876\" data-end=\"1924\">🔁 <strong data-start=\"1882\" data-end=\"1924\">Tantuco CTC - Return &amp; Exchange Policy</strong></h5>\n<p data-start=\"1926\" data-end=\"2019\">We want you to be satisfied with your purchase. Please review our return and exchange policy:</p>\n<h6 data-start=\"2021\" data-end=\"2054\">✅ <strong data-start=\"2027\" data-end=\"2054\">Eligibility for Returns</strong></h6>\n<ul data-start=\"2055\" data-end=\"2262\">\n<li data-start=\"2055\" data-end=\"2127\">\n<p data-start=\"2057\" data-end=\"2127\">Products must be returned within <strong data-start=\"2090\" data-end=\"2100\">7 days</strong> from the date of delivery.</p>\n</li>\n<li data-start=\"2128\" data-end=\"2262\">\n<p data-start=\"2130\" data-end=\"2144\">Items must be:</p>\n<ul data-start=\"2147\" data-end=\"2262\">\n<li data-start=\"2147\" data-end=\"2174\">\n<p data-start=\"2149\" data-end=\"2174\">In <strong data-start=\"2152\" data-end=\"2174\">original condition</strong></p>\n</li>\n<li data-start=\"2177\" data-end=\"2203\">\n<p data-start=\"2179\" data-end=\"2203\"><strong data-start=\"2179\" data-end=\"2203\">Unused and undamaged</strong></p>\n</li>\n<li data-start=\"2206\" data-end=\"2262\">\n<p data-start=\"2208\" data-end=\"2262\">In <strong data-start=\"2211\" data-end=\"2233\">original packaging</strong> with all accessories/manuals</p>\n</li>\n</ul>\n</li>\n</ul>\n<h6 data-start=\"2264\" data-end=\"2303\">❌ <strong data-start=\"2270\" data-end=\"2303\">Items Not Eligible for Return</strong></h6>\n<ul data-start=\"2304\" data-end=\"2416\">\n<li data-start=\"2304\" data-end=\"2344\">\n<p data-start=\"2306\" data-end=\"2344\">Custom-built or special-order hardware</p>\n</li>\n<li data-start=\"2345\" data-end=\"2384\">\n<p data-start=\"2347\" data-end=\"2384\">Used tools, equipment, or power tools</p>\n</li>\n<li data-start=\"2385\" data-end=\"2416\">\n<p data-start=\"2387\" data-end=\"2416\">Clearance or final sale items</p>\n</li>\n</ul>\n<h6 data-start=\"2418\" data-end=\"2443\">🔄 <strong data-start=\"2425\" data-end=\"2443\">Return Process</strong></h6>\n<ol data-start=\"2444\" data-end=\"2743\">\n<li data-start=\"2444\" data-end=\"2516\">\n<p data-start=\"2447\" data-end=\"2516\"><strong data-start=\"2447\" data-end=\"2460\">Notify us</strong> via email or phone within 7 days of receiving the item.</p>\n</li>\n<li data-start=\"2517\" data-end=\"2598\">\n<p data-start=\"2520\" data-end=\"2598\">Bring the item to our store or request a pickup (subject to approval and fee).</p>\n</li>\n<li data-start=\"2599\" data-end=\"2743\">\n<p data-start=\"2602\" data-end=\"2633\">Upon inspection, we will issue:</p>\n<ul data-start=\"2637\" data-end=\"2743\">\n<li data-start=\"2637\" data-end=\"2658\">\n<p data-start=\"2639\" data-end=\"2658\">A <strong data-start=\"2641\" data-end=\"2656\">replacement</strong></p>\n</li>\n<li data-start=\"2662\" data-end=\"2686\">\n<p data-start=\"2664\" data-end=\"2686\"><strong data-start=\"2664\" data-end=\"2680\">Store credit</strong>, or</p>\n</li>\n<li data-start=\"2690\" data-end=\"2743\">\n<p data-start=\"2692\" data-end=\"2743\">A <strong data-start=\"2694\" data-end=\"2720\">full or partial refund</strong>, depending on the case</p>\n</li>\n</ul>\n</li>\n</ol>\n<h6 data-start=\"2745\" data-end=\"2782\">⚠️ <strong data-start=\"2752\" data-end=\"2782\">Damaged or Defective Items</strong></h6>\n<ul data-start=\"2783\" data-end=\"2963\">\n<li data-start=\"2783\" data-end=\"2869\">\n<p data-start=\"2785\" data-end=\"2869\">If your item is <strong data-start=\"2801\" data-end=\"2825\">damaged upon arrival</strong>, notify us within <strong data-start=\"2844\" data-end=\"2856\">48 hours</strong> with photos.</p>\n</li>\n<li data-start=\"2870\" data-end=\"2963\">\n<p data-start=\"2872\" data-end=\"2963\">Defective items under <strong data-start=\"2894\" data-end=\"2919\">manufacturer warranty</strong> will follow the supplier&rsquo;s service process.</p>\n</li>\n</ul>', '2025-07-08 04:04:01', '2025-07-12 02:47:12', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `profile` varchar(255) DEFAULT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `force_password_change` tinyint(1) NOT NULL DEFAULT 0,
  `created_by_admin` tinyint(1) NOT NULL DEFAULT 0,
  `role` enum('b2b','deliveryrider','salesofficer','superadmin') NOT NULL DEFAULT 'b2b',
  `otp_code` varchar(255) DEFAULT NULL,
  `otp_expire` timestamp NULL DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `about` text DEFAULT NULL,
  `credit_limit` decimal(10,2) DEFAULT 300000.00,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `profile`, `username`, `email`, `email_verified_at`, `password`, `force_password_change`, `created_by_admin`, `role`, `otp_code`, `otp_expire`, `status`, `about`, `credit_limit`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'John Superadmin', NULL, 'superadmin', 'superadmin@example.com', '2025-07-09 11:33:35', '$2y$10$fpHuUdg4vFV0l.s4fzQFp.AkYcvwwJb1pGIknFNmAg.kKHv.omTNy', 0, 0, 'superadmin', '733006', '2025-07-09 11:42:49', 1, NULL, NULL, NULL, '2025-07-09 11:29:00', '2025-07-09 11:33:35', NULL),
(2, 'John DeliveryRider', 'assets/upload/profiles/1755589579_image 5.png', 'deliveryrider', 'deliveryrider@example.com', '2025-07-09 23:49:40', '$2y$10$0LUNUv/HDZ5kbz35U3Akju8ygpt79fMey.U3kXrt.Y2BgXGbsaOUi', 0, 0, 'deliveryrider', '237579', '2025-07-09 23:59:16', 1, NULL, NULL, NULL, '2025-07-09 11:29:00', '2025-08-19 15:46:19', NULL),
(3, 'John SalesOfficer', NULL, 'salesofficer', 'assistantsales@example.com', '2025-07-09 23:09:39', '$2y$10$lQBpIfaVKcZOhUkfNKqNCef1tDZ968unqOvi6OAZvxq1DkbhIX3AW', 0, 0, 'salesofficer', '121212', '2025-07-09 23:18:37', 1, NULL, 300000.00, NULL, '2025-07-09 11:29:00', '2025-08-19 17:40:48', NULL),
(4, 'John B2B', 'assets/upload/profiles/1752172600_68700838af669.jpg', 'b2b', 'b2b@example.com', '2025-07-09 23:05:05', '$2y$10$fpHuUdg4vFV0l.s4fzQFp.AkYcvwwJb1pGIknFNmAg.kKHv.omTNy', 0, 0, 'b2b', '960918', '2025-07-09 23:14:10', 1, 'Im Seller', 269632.00, NULL, '2025-07-09 11:29:00', '2025-09-27 04:06:02', NULL),
(8, 'Ben Tulfo', 'assets/upload/profiles/1752172600_68700838af669.jpg', 'b2btwo', 'b2btwo@example.com', '2025-07-09 23:05:05', '$2y$10$fpHuUdg4vFV0l.s4fzQFp.AkYcvwwJb1pGIknFNmAg.kKHv.omTNy', 0, 0, 'b2b', '960918', '2025-07-09 23:14:10', 1, 'Im Seller', 21741.44, NULL, '2025-07-09 11:29:00', '2025-09-04 16:06:26', NULL),
(9, 'Renzo Bancud', 'https://lh3.googleusercontent.com/a/ACg8ocIMVsDeQxvDOZw0Z_Oeb1f1n2EloZbk4YLh489ujI9TLmb_ME8=s96-c', 'Renzo Bancud', 'bancudzo3@gmail.com', '2025-08-30 12:17:59', '$2y$10$qi/hxdZIQVBsYG0ZLCYBLOHr.AcC6l1Y8XggAIHHDsHKciXCatGYm', 0, 0, 'b2b', NULL, NULL, 1, NULL, 300000.00, NULL, '2025-08-30 12:17:59', '2025-08-30 12:17:59', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_logs`
--

CREATE TABLE `user_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `event` varchar(255) NOT NULL,
  `ip_address` varchar(255) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `logged_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_logs`
--

INSERT INTO `user_logs` (`id`, `user_id`, `event`, `ip_address`, `user_agent`, `logged_at`, `created_at`, `updated_at`) VALUES
(1, 4, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', '2025-09-27 06:31:04', NULL, NULL),
(2, 3, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', '2025-09-27 06:31:44', NULL, NULL),
(3, 3, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', '2025-09-27 06:55:42', NULL, NULL),
(4, 4, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-05 13:46:32', NULL, NULL),
(5, 4, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-05 13:46:39', NULL, NULL),
(6, 4, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-05 14:30:36', NULL, NULL),
(7, 4, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-05 14:30:44', NULL, NULL),
(8, 4, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-05 15:05:28', NULL, NULL),
(9, 4, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-05 15:05:50', NULL, NULL),
(10, 4, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-05 15:05:57', NULL, NULL),
(11, 4, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-05 15:08:09', NULL, NULL),
(12, 4, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-05 15:08:40', NULL, NULL),
(13, 1, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-05 15:08:51', NULL, NULL),
(14, 1, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-05 15:09:30', NULL, NULL),
(15, 4, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-05 15:09:37', NULL, NULL),
(16, 4, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-05 15:19:46', NULL, NULL),
(17, 4, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-05 15:20:16', NULL, NULL),
(18, 4, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-05 16:18:23', NULL, NULL),
(19, 2, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-05 16:18:33', NULL, NULL),
(20, 2, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-05 16:19:11', NULL, NULL),
(21, 4, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-05 16:19:42', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `b2b_address`
--
ALTER TABLE `b2b_address`
  ADD PRIMARY KEY (`id`),
  ADD KEY `b2b_address_user_id_foreign` (`user_id`);

--
-- Indexes for table `b2b_details`
--
ALTER TABLE `b2b_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `b2b_details_user_id_foreign` (`user_id`);

--
-- Indexes for table `banks`
--
ALTER TABLE `banks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `company_settings`
--
ALTER TABLE `company_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `credit_partial_payments`
--
ALTER TABLE `credit_partial_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `credit_partial_payments_purchase_request_id_foreign` (`purchase_request_id`),
  ADD KEY `credit_partial_payments_bank_id_foreign` (`bank_id`);

--
-- Indexes for table `credit_payments`
--
ALTER TABLE `credit_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `credit_payments_purchase_request_id_foreign` (`purchase_request_id`),
  ADD KEY `credit_payments_bank_id_foreign` (`bank_id`);

--
-- Indexes for table `deliveries`
--
ALTER TABLE `deliveries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `deliveries_order_id_foreign` (`order_id`),
  ADD KEY `deliveries_delivery_rider_id_foreign` (`delivery_rider_id`);

--
-- Indexes for table `delivery_histories`
--
ALTER TABLE `delivery_histories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `delivery_histories_delivery_id_foreign` (`delivery_id`);

--
-- Indexes for table `delivery_ratings`
--
ALTER TABLE `delivery_ratings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `delivery_ratings_delivery_id_unique` (`delivery_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `inventories`
--
ALTER TABLE `inventories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inventories_product_id_foreign` (`product_id`);

--
-- Indexes for table `manual_email_order`
--
ALTER TABLE `manual_email_order`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
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
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `orders_order_number_unique` (`order_number`),
  ADD KEY `orders_user_id_foreign` (`user_id`),
  ADD KEY `orders_b2b_address_id_foreign` (`b2b_address_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_items_order_id_foreign` (`order_id`),
  ADD KEY `order_items_product_id_foreign` (`product_id`);

--
-- Indexes for table `paid_payments`
--
ALTER TABLE `paid_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `paid_payments_purchase_request_id_foreign` (`purchase_request_id`),
  ADD KEY `paid_payments_bank_id_foreign` (`bank_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `products_sku_unique` (`sku`),
  ADD KEY `products_category_id_foreign` (`category_id`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_images_product_id_foreign` (`product_id`);

--
-- Indexes for table `product_ratings`
--
ALTER TABLE `product_ratings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_ratings_product_id_user_id_unique` (`product_id`,`user_id`),
  ADD KEY `product_ratings_user_id_foreign` (`user_id`);

--
-- Indexes for table `purchase_requests`
--
ALTER TABLE `purchase_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `purchase_requests_customer_id_foreign` (`customer_id`);

--
-- Indexes for table `purchase_request_items`
--
ALTER TABLE `purchase_request_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `purchase_request_items_purchase_request_id_foreign` (`purchase_request_id`),
  ADD KEY `purchase_request_items_product_id_foreign` (`product_id`);

--
-- Indexes for table `purchase_request_refunds`
--
ALTER TABLE `purchase_request_refunds`
  ADD PRIMARY KEY (`id`),
  ADD KEY `purchase_request_refunds_purchase_request_id_foreign` (`purchase_request_id`),
  ADD KEY `purchase_request_refunds_purchase_request_item_id_foreign` (`purchase_request_item_id`),
  ADD KEY `purchase_request_refunds_product_id_foreign` (`product_id`),
  ADD KEY `purchase_request_refunds_processed_by_foreign` (`processed_by`);

--
-- Indexes for table `purchase_request_returns`
--
ALTER TABLE `purchase_request_returns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `purchase_request_returns_purchase_request_id_foreign` (`purchase_request_id`),
  ADD KEY `purchase_request_returns_purchase_request_item_id_foreign` (`purchase_request_item_id`),
  ADD KEY `purchase_request_returns_product_id_foreign` (`product_id`),
  ADD KEY `purchase_request_returns_processed_by_foreign` (`processed_by`);

--
-- Indexes for table `terms_conditions`
--
ALTER TABLE `terms_conditions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_username_unique` (`username`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `user_logs`
--
ALTER TABLE `user_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_logs_user_id_foreign` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `b2b_address`
--
ALTER TABLE `b2b_address`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `b2b_details`
--
ALTER TABLE `b2b_details`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `banks`
--
ALTER TABLE `banks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `company_settings`
--
ALTER TABLE `company_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `credit_partial_payments`
--
ALTER TABLE `credit_partial_payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `credit_payments`
--
ALTER TABLE `credit_payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `deliveries`
--
ALTER TABLE `deliveries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `delivery_histories`
--
ALTER TABLE `delivery_histories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `delivery_ratings`
--
ALTER TABLE `delivery_ratings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventories`
--
ALTER TABLE `inventories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `manual_email_order`
--
ALTER TABLE `manual_email_order`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `paid_payments`
--
ALTER TABLE `paid_payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `product_ratings`
--
ALTER TABLE `product_ratings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_requests`
--
ALTER TABLE `purchase_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `purchase_request_items`
--
ALTER TABLE `purchase_request_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `purchase_request_refunds`
--
ALTER TABLE `purchase_request_refunds`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_request_returns`
--
ALTER TABLE `purchase_request_returns`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `terms_conditions`
--
ALTER TABLE `terms_conditions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `user_logs`
--
ALTER TABLE `user_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `b2b_address`
--
ALTER TABLE `b2b_address`
  ADD CONSTRAINT `b2b_address_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `b2b_details`
--
ALTER TABLE `b2b_details`
  ADD CONSTRAINT `b2b_details_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `credit_partial_payments`
--
ALTER TABLE `credit_partial_payments`
  ADD CONSTRAINT `credit_partial_payments_bank_id_foreign` FOREIGN KEY (`bank_id`) REFERENCES `banks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `credit_partial_payments_purchase_request_id_foreign` FOREIGN KEY (`purchase_request_id`) REFERENCES `purchase_requests` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `credit_payments`
--
ALTER TABLE `credit_payments`
  ADD CONSTRAINT `credit_payments_bank_id_foreign` FOREIGN KEY (`bank_id`) REFERENCES `banks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `credit_payments_purchase_request_id_foreign` FOREIGN KEY (`purchase_request_id`) REFERENCES `purchase_requests` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `deliveries`
--
ALTER TABLE `deliveries`
  ADD CONSTRAINT `deliveries_delivery_rider_id_foreign` FOREIGN KEY (`delivery_rider_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `deliveries_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `delivery_histories`
--
ALTER TABLE `delivery_histories`
  ADD CONSTRAINT `delivery_histories_delivery_id_foreign` FOREIGN KEY (`delivery_id`) REFERENCES `deliveries` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `delivery_ratings`
--
ALTER TABLE `delivery_ratings`
  ADD CONSTRAINT `delivery_ratings_delivery_id_foreign` FOREIGN KEY (`delivery_id`) REFERENCES `deliveries` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `inventories`
--
ALTER TABLE `inventories`
  ADD CONSTRAINT `inventories_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_b2b_address_id_foreign` FOREIGN KEY (`b2b_address_id`) REFERENCES `b2b_address` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `paid_payments`
--
ALTER TABLE `paid_payments`
  ADD CONSTRAINT `paid_payments_bank_id_foreign` FOREIGN KEY (`bank_id`) REFERENCES `banks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `paid_payments_purchase_request_id_foreign` FOREIGN KEY (`purchase_request_id`) REFERENCES `purchase_requests` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_ratings`
--
ALTER TABLE `product_ratings`
  ADD CONSTRAINT `product_ratings_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_ratings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `purchase_requests`
--
ALTER TABLE `purchase_requests`
  ADD CONSTRAINT `purchase_requests_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `purchase_request_items`
--
ALTER TABLE `purchase_request_items`
  ADD CONSTRAINT `purchase_request_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `purchase_request_items_purchase_request_id_foreign` FOREIGN KEY (`purchase_request_id`) REFERENCES `purchase_requests` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `purchase_request_refunds`
--
ALTER TABLE `purchase_request_refunds`
  ADD CONSTRAINT `purchase_request_refunds_processed_by_foreign` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `purchase_request_refunds_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchase_request_refunds_purchase_request_id_foreign` FOREIGN KEY (`purchase_request_id`) REFERENCES `purchase_requests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchase_request_refunds_purchase_request_item_id_foreign` FOREIGN KEY (`purchase_request_item_id`) REFERENCES `purchase_request_items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `purchase_request_returns`
--
ALTER TABLE `purchase_request_returns`
  ADD CONSTRAINT `purchase_request_returns_processed_by_foreign` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `purchase_request_returns_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchase_request_returns_purchase_request_id_foreign` FOREIGN KEY (`purchase_request_id`) REFERENCES `purchase_requests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchase_request_returns_purchase_request_item_id_foreign` FOREIGN KEY (`purchase_request_item_id`) REFERENCES `purchase_request_items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_logs`
--
ALTER TABLE `user_logs`
  ADD CONSTRAINT `user_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
