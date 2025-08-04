-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: dockerdatabase:3306
-- Generation Time: Aug 04, 2025 at 09:28 AM
-- Server version: 10.5.29-MariaDB-ubu2004
-- PHP Version: 8.2.27

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
  `delivery_address_lat` decimal(10,7) DEFAULT NULL,
  `delivery_address_lng` decimal(10,7) DEFAULT NULL,
  `status` enum('inactive','active') NOT NULL DEFAULT 'inactive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `b2b_details`
--

CREATE TABLE `b2b_details` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `certificate_registration` varchar(255) NOT NULL,
  `business_permit` varchar(255) NOT NULL,
  `status` enum('approved','rejected') DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `b2b_details`
--

INSERT INTO `b2b_details` (`id`, `user_id`, `certificate_registration`, `business_permit`, `status`, `created_at`, `updated_at`) VALUES
(1, 4, 'assets/upload/requirements/certificate_4_1754285069.pdf', 'assets/upload/requirements/permit_4_1754285069.pdf', 'approved', '2025-08-04 13:24:29', '2025-08-04 13:27:51');

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
  `company_address` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `company_settings`
--

INSERT INTO `company_settings` (`id`, `company_logo`, `company_email`, `company_phone`, `company_address`, `created_at`, `updated_at`) VALUES
(1, 'assets/upload/1752061775_TantucoCTC_Logo.png', 'tantucoconstruction@gmail.com', '(042)525-8888', 'Barangay Balubal, Sariaya, 4322, Quezon Province', NULL, '2025-07-09 11:49:35');

-- --------------------------------------------------------

--
-- Table structure for table `credit_payments`
--

CREATE TABLE `credit_payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `purchase_request_id` bigint(20) UNSIGNED NOT NULL,
  `bank_id` bigint(20) UNSIGNED DEFAULT NULL,
  `credit_amount` decimal(10,2) NOT NULL,
  `paid_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `due_date` date NOT NULL,
  `paid_date` date DEFAULT NULL,
  `status` enum('unpaid','partially_paid','paid','overdue') NOT NULL DEFAULT 'unpaid',
  `proof_payment` varchar(255) DEFAULT NULL,
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
  `delivery_latitude` decimal(10,7) NOT NULL DEFAULT 13.9655000,
  `delivery_longitude` decimal(10,7) NOT NULL DEFAULT 121.5348000,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(4, 1, 'out', 20, 'sold', '2025-07-10 19:26:11', '2025-07-10 19:26:11'),
(5, 2, 'out', 100, 'sold', '2025-07-10 19:26:11', '2025-07-10 19:26:11'),
(6, 3, 'out', 100, 'sold', '2025-07-10 19:26:11', '2025-07-10 19:26:11'),
(7, 1, 'in', 1000, 'restock', '2025-07-11 20:52:43', '2025-07-11 20:52:43'),
(8, 2, 'in', 100, 'restock', '2025-07-11 20:52:57', '2025-07-11 20:52:57'),
(9, 3, 'in', 900, 'restock', '2025-07-11 20:53:20', '2025-07-11 20:53:20');

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
(26, '2025_07_10_203040_create_purchase_request_returns', 2),
(27, '2025_07_10_203153_create_purchase_request_refunds', 2),
(28, '2025_07_11_024508_create_delivery_ratings_table', 3),
(30, '2025_07_12_141102_create_credit_payments_table', 4);

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
(1, 3, 'purchase_request', 'A new purchase request has been updated by John B2B. <br><a href=\"http://localhost:8000/salesofficer/purchase-requests/all\">Visit</a>', NULL, '2025-08-04 16:18:01', '2025-08-04 16:18:01'),
(2, 3, 'purchase_request', 'A new purchase request has been updated by John B2B. <br><a href=\"http://localhost:8000/salesofficer/purchase-requests/all\">Visit</a>', NULL, '2025-08-04 16:19:22', '2025-08-04 16:19:22'),
(3, 3, 'purchase_request', 'A new purchase request has been updated by John B2B. <br><a href=\"http://localhost:8000/salesofficer/purchase-requests/all\">Visit</a>', NULL, '2025-08-04 16:21:15', '2025-08-04 16:21:15'),
(4, 3, 'purchase_request', 'A new purchase request has been updated by John B2B. <br><a href=\"http://localhost:8000/salesofficer/purchase-requests/all\">Visit</a>', NULL, '2025-08-04 16:22:09', '2025-08-04 16:22:09'),
(5, 3, 'purchase_request', 'A new purchase request has been updated by John B2B. <br><a href=\"http://localhost:8000/salesofficer/purchase-requests/all\">Visit</a>', NULL, '2025-08-04 16:24:35', '2025-08-04 16:24:35'),
(6, 3, 'purchase_request', 'A new purchase request has been updated by John B2B. <br><a href=\"http://localhost:8000/salesofficer/purchase-requests/all\">Visit</a>', NULL, '2025-08-04 16:27:26', '2025-08-04 16:27:26'),
(7, 3, 'purchase_request', 'A new purchase request has been updated by John B2B. <br><a href=\"http://localhost:8000/salesofficer/purchase-requests/all\">Visit</a>', NULL, '2025-08-04 16:27:30', '2025-08-04 16:27:30'),
(8, 3, 'purchase_request', 'A new purchase request has been updated by John B2B. <br><a href=\"http://localhost:8000/salesofficer/purchase-requests/all\">Visit</a>', NULL, '2025-08-04 16:31:15', '2025-08-04 16:31:15'),
(9, 3, 'purchase_request', 'A new purchase request has been updated by John B2B. <br><a href=\"http://localhost:8000/salesofficer/purchase-requests/all\">Visit</a>', NULL, '2025-08-04 16:31:19', '2025-08-04 16:31:19'),
(10, 3, 'purchase_request', 'A new purchase request has been updated by John B2B. <br><a href=\"http://localhost:8000/salesofficer/purchase-requests/all\">Visit</a>', NULL, '2025-08-04 16:31:45', '2025-08-04 16:31:45'),
(11, 3, 'purchase_request', 'A new purchase request has been updated by John B2B. <br><a href=\"http://localhost:8000/salesofficer/purchase-requests/all\">Visit</a>', NULL, '2025-08-04 16:31:48', '2025-08-04 16:31:48'),
(12, 3, 'purchase_request', 'A new purchase request has been updated by John B2B. <br><a href=\"http://localhost:8000/salesofficer/purchase-requests/all\">Visit</a>', NULL, '2025-08-04 16:57:08', '2025-08-04 16:57:08'),
(13, 3, 'purchase_request', 'A new purchase request has been updated by John B2B. <br><a href=\"http://localhost:8000/salesofficer/purchase-requests/all\">Visit</a>', NULL, '2025-08-04 16:57:11', '2025-08-04 16:57:11'),
(14, 3, 'purchase_request', 'A new purchase request has been updated by John B2B. <br><a href=\"http://localhost:8000/salesofficer/purchase-requests/all\">Visit</a>', NULL, '2025-08-04 17:04:34', '2025-08-04 17:04:34'),
(15, 3, 'purchase_request', 'A new purchase request has been updated by John B2B. <br><a href=\"http://localhost:8000/salesofficer/purchase-requests/all\">Visit</a>', NULL, '2025-08-04 17:04:37', '2025-08-04 17:04:37');

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
  `expiry_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `sku`, `description`, `price`, `expiry_date`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'Cement (40kg bag)', 'SKU-685BCEE4C5E58', 'Portland type 1 cement ideal for general construction use', 260.00, NULL, '2025-06-24 18:26:44', '2025-06-24 18:26:44', NULL),
(2, 1, 'Concrete Hollow Block 6”', 'SKU-685BCF19EE185', 'Standard 6-inch hollow block for wall partitions', 18.00, NULL, '2025-06-24 18:27:37', '2025-06-24 18:27:37', NULL),
(3, 2, '10mm Rebar (Steel Bar)', 'SKU-685BCF7725412', '10mm diameter steel reinforcement bar for slabs and columns', 175.00, NULL, '2025-06-24 18:29:11', '2025-06-24 18:29:11', NULL),
(4, 3, 'Plywood (1/4” x 4’ x 8’)', 'SKU-685BCF9C6B631', 'Commercial plywood used for temporary walls and ceiling work', 495.00, NULL, '2025-06-24 18:29:48', '2025-06-24 18:29:48', NULL),
(5, 4, 'GL Sheet (Corrugated, 8ft)', 'SKU-685BCFCA3C81A', 'Galvanized iron sheet for roofing and siding', 320.00, NULL, '2025-06-24 18:30:34', '2025-06-24 18:30:34', NULL),
(6, 5, '1” PVC Pipe (10ft)', 'SKU-685BCFF824CAD', 'Polyvinyl chloride pipe for water supply systems', 120.00, NULL, '2025-06-24 18:31:20', '2025-06-24 18:31:20', NULL),
(7, 6, 'Electrical Tape (Black', 'SKU-685BD014DFD3C', 'Insulating tape for electrical wiring', 25.00, NULL, '2025-06-24 18:31:48', '2025-06-24 18:31:48', NULL),
(8, 7, 'Paint – White Latex (4L)', 'SKU-685BD06953EF9', 'Water-based paint for interior walls and ceilings', 560.00, NULL, '2025-06-24 18:33:13', '2025-06-24 18:33:13', NULL),
(9, 8, 'Door Knob (Entrance Set)', 'SKU-685BD0943711E', 'Heavy-duty cylindrical door knob with lockset', 620.00, NULL, '2025-06-24 18:33:56', '2025-06-24 18:33:56', NULL),
(10, 9, '1” Paint Brush', 'SKU-685BD0B953CE5', 'Standard 1-inch brush used for painting and touch-ups', 35.00, NULL, '2025-06-24 18:34:33', '2025-06-24 18:34:33', NULL),
(11, 1, 'sdfdsf', 'SKU-686A5E1E39DBB', 'dgdfgdfg', 4435.00, '2025-07-23', '2025-07-06 03:29:34', '2025-07-06 03:30:29', '2025-07-06 03:30:29'),
(12, 9, 'The Saw', 'SKU-689050706A4D3', 'This is the saw episodes tools.', 100.00, NULL, '2025-08-04 14:17:20', '2025-08-04 14:17:20', NULL);

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
(1, 1, 'assets/upload/products/1750847204_Cement.jpg', NULL, 1, '2025-06-24 18:26:44', '2025-06-24 18:26:44'),
(2, 2, 'assets/upload/products/1750847258_Concrete Hollow Block.jpg', NULL, 1, '2025-06-24 18:27:38', '2025-06-24 18:27:38'),
(3, 3, 'assets/upload/products/1750847351_texture-steel-deformed-bars-background-600nw-2371218435.webp', NULL, 1, '2025-06-24 18:29:11', '2025-06-24 18:29:11'),
(4, 4, 'assets/upload/products/1750847388_Plywood.jpg', NULL, 1, '2025-06-24 18:29:48', '2025-06-24 18:29:48'),
(5, 5, 'assets/upload/products/1750847434_Roof GL Sheet Corrugated .jpg', NULL, 1, '2025-06-24 18:30:34', '2025-06-24 18:30:34'),
(6, 6, 'assets/upload/products/1750847480_PVC pipe.jpg', NULL, 1, '2025-06-24 18:31:20', '2025-06-24 18:31:20'),
(7, 8, 'assets/upload/products/1750847593_2017596014.webp', NULL, 1, '2025-06-24 18:33:13', '2025-06-24 18:33:13'),
(8, 9, 'assets/upload/products/1750847636_Door Knob set.jpg', NULL, 1, '2025-06-24 18:33:56', '2025-06-24 18:33:56'),
(9, 10, 'assets/upload/products/1750847673_Paint brush.jpg', NULL, 1, '2025-06-24 18:34:33', '2025-06-24 18:34:33'),
(10, 7, 'assets/upload/products/1750847714_Electric tape .jpg', NULL, 1, '2025-06-24 18:35:14', '2025-06-24 18:35:14'),
(11, 11, 'assets/upload/products/1751801374_TantucoCTC_Logo.png', NULL, 1, '2025-07-06 03:29:34', '2025-07-06 03:30:03'),
(12, 12, 'assets/upload/products/1754288240_saw_1.png', NULL, 0, '2025-08-04 14:17:20', '2025-08-04 14:17:20'),
(13, 12, 'assets/upload/products/1754288240_saw_2.png', NULL, 0, '2025-08-04 14:17:20', '2025-08-04 14:17:20'),
(14, 12, 'assets/upload/products/1754288240_saw_3.png', NULL, 1, '2025-08-04 14:17:20', '2025-08-04 14:17:20');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_requests`
--

CREATE TABLE `purchase_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `bank_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` enum('pending','quotation_sent','po_submitted','so_created','delivery_in_progress','delivered','invoice_sent','cancelled','returned','refunded') NOT NULL DEFAULT 'pending',
  `vat` int(11) DEFAULT NULL,
  `delivery_fee` decimal(10,2) DEFAULT NULL,
  `credit` tinyint(1) NOT NULL DEFAULT 0,
  `payment_method` enum('pay_now','pay_later') DEFAULT NULL,
  `proof_payment` varchar(255) DEFAULT NULL,
  `pr_remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `purchase_requests`
--

INSERT INTO `purchase_requests` (`id`, `customer_id`, `bank_id`, `status`, `vat`, `delivery_fee`, `credit`, `payment_method`, `proof_payment`, `pr_remarks`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 4, NULL, 'pending', NULL, NULL, 0, NULL, NULL, NULL, '2025-08-04 17:04:34', '2025-08-04 17:04:34', NULL);

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
(1, 1, 1, 14, 3640.00, '2025-08-04 17:04:34', '2025-08-04 17:18:23'),
(2, 1, 2, 13, 234.00, '2025-08-04 17:04:37', '2025-08-04 17:17:36');

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
  `processed_by` bigint(20) UNSIGNED DEFAULT NULL,
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
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `admin_response` text DEFAULT NULL,
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
  `credit_limit` decimal(10,2) DEFAULT NULL,
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
(2, 'John DeliveryRider', NULL, 'deliveryrider', 'deliveryrider@example.com', '2025-07-09 23:49:40', '$2y$10$0LUNUv/HDZ5kbz35U3Akju8ygpt79fMey.U3kXrt.Y2BgXGbsaOUi', 0, 0, 'deliveryrider', '237579', '2025-07-09 23:59:16', 1, NULL, NULL, NULL, '2025-07-09 11:29:00', '2025-07-09 23:49:40', NULL),
(3, 'John SalesOfficer', NULL, 'salesofficer', 'salesofficer@example.com', '2025-07-09 23:09:39', '$2y$10$lQBpIfaVKcZOhUkfNKqNCef1tDZ968unqOvi6OAZvxq1DkbhIX3AW', 0, 0, 'salesofficer', '121212', '2025-07-09 23:18:37', 1, NULL, NULL, NULL, '2025-07-09 11:29:00', '2025-07-09 23:09:39', NULL),
(4, 'John B2B', 'assets/upload/profiles/1752172600_68700838af669.jpg', 'b2b', 'b2b@example.com', '2025-07-09 23:05:05', '$2y$10$fpHuUdg4vFV0l.s4fzQFp.AkYcvwwJb1pGIknFNmAg.kKHv.omTNy', 0, 0, 'b2b', '960918', '2025-07-09 23:14:10', 1, 'Im Seller', 271219.00, NULL, '2025-07-09 11:29:00', '2025-07-20 07:32:18', NULL);

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
(1, 1, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-09 11:32:55', NULL, NULL),
(2, 1, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-09 22:46:56', NULL, NULL),
(3, 1, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-09 23:03:57', NULL, NULL),
(4, 4, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-09 23:04:17', NULL, NULL),
(5, 4, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-09 23:08:23', NULL, NULL),
(6, 3, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-09 23:08:41', NULL, NULL),
(7, 3, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-09 23:10:10', NULL, NULL),
(8, 4, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-09 23:10:22', NULL, NULL),
(9, 4, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-09 23:25:51', NULL, NULL),
(10, 1, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-09 23:26:01', NULL, NULL),
(11, 1, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-09 23:26:36', NULL, NULL),
(12, 1, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-09 23:26:49', NULL, NULL),
(13, 1, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-09 23:26:55', NULL, NULL),
(14, 4, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-09 23:27:08', NULL, NULL),
(15, 4, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-09 23:45:35', NULL, NULL),
(16, 1, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-09 23:45:48', NULL, NULL),
(17, 1, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-09 23:49:04', NULL, NULL),
(18, 2, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-09 23:49:21', NULL, NULL),
(19, 2, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-09 23:50:59', NULL, NULL),
(20, 1, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-09 23:51:12', NULL, NULL),
(21, 4, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-10 11:14:33', NULL, NULL),
(22, 4, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-10 15:11:36', NULL, NULL),
(23, 2, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-10 15:11:47', NULL, NULL),
(24, 2, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-10 15:32:03', NULL, NULL),
(25, 4, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-10 15:32:32', NULL, NULL),
(26, 4, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-10 15:41:40', NULL, NULL),
(27, 3, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-10 15:41:53', NULL, NULL),
(28, 3, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-10 15:51:54', NULL, NULL),
(29, 4, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-10 15:52:40', NULL, NULL),
(30, 4, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-10 17:13:16', NULL, NULL),
(31, 3, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-10 17:13:27', NULL, NULL),
(32, 3, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-10 17:44:01', NULL, NULL),
(33, 4, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-10 17:44:13', NULL, NULL),
(34, 4, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-10 18:07:26', NULL, NULL),
(35, 4, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-10 18:09:28', NULL, NULL),
(36, 4, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-10 19:19:52', NULL, NULL),
(37, 2, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-10 19:20:04', NULL, NULL),
(38, 3, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-11 20:44:40', NULL, NULL),
(39, 3, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-11 20:45:00', NULL, NULL),
(40, 1, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-11 20:45:09', NULL, NULL),
(41, 1, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-11 20:56:29', NULL, NULL),
(42, 4, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-11 20:58:02', NULL, NULL),
(43, 4, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-11 21:29:47', NULL, NULL),
(44, 3, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-11 21:30:05', NULL, NULL),
(45, 3, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-11 22:05:24', NULL, NULL),
(46, 2, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-11 22:06:01', NULL, NULL),
(47, 2, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-11 22:06:31', NULL, NULL),
(48, 1, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-11 22:09:22', NULL, NULL),
(49, 1, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-11 22:11:16', NULL, NULL),
(50, 3, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-11 22:11:33', NULL, NULL),
(51, 3, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-11 22:57:40', NULL, NULL),
(52, 2, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-11 22:58:03', NULL, NULL),
(53, 2, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-12 00:15:34', NULL, NULL),
(54, 1, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-12 00:15:42', NULL, NULL),
(55, 1, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-12 00:28:18', NULL, NULL),
(56, 1, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-12 01:23:31', NULL, NULL),
(57, 1, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-12 03:11:09', NULL, NULL),
(58, 1, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-12 03:56:25', NULL, NULL),
(59, 1, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-12 04:04:23', NULL, NULL),
(60, 2, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-12 04:04:48', NULL, NULL),
(61, 2, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-12 04:08:25', NULL, NULL),
(62, 4, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-12 04:09:22', NULL, NULL),
(63, 4, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-12 04:27:05', NULL, NULL),
(64, 1, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-12 04:27:43', NULL, NULL),
(65, 1, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-12 04:49:04', NULL, NULL),
(66, 4, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-12 04:49:15', NULL, NULL),
(67, 4, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-12 06:38:18', NULL, NULL),
(68, 1, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-12 06:38:27', NULL, NULL),
(69, 1, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-12 06:40:35', NULL, NULL),
(70, 3, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-12 06:40:47', NULL, NULL),
(71, 3, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-12 06:43:14', NULL, NULL),
(72, 4, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-12 06:43:49', NULL, NULL),
(73, 4, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-12 08:01:47', NULL, NULL),
(74, 4, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-12 08:13:50', NULL, NULL),
(75, 4, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-12 09:08:09', NULL, NULL),
(76, 1, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-12 09:08:20', NULL, NULL),
(77, 1, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-12 10:03:10', NULL, NULL),
(78, 1, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-12 10:06:02', NULL, NULL),
(79, 1, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-12 10:08:34', NULL, NULL),
(80, 4, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-12 10:10:14', NULL, NULL),
(81, 1, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-20 04:37:22', NULL, NULL),
(82, 1, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-20 04:46:24', NULL, NULL),
(83, 4, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-20 05:57:57', NULL, NULL),
(84, 4, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-20 07:23:21', NULL, NULL),
(85, 3, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-20 07:24:38', NULL, NULL),
(86, 3, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-20 07:30:40', NULL, NULL),
(87, 4, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-20 07:31:09', NULL, NULL),
(88, 4, 'login', '172.19.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-04 10:02:03', NULL, NULL),
(89, 4, 'logout', '172.19.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-04 10:42:37', NULL, NULL),
(90, 1, 'login', '172.19.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-04 10:42:49', NULL, NULL),
(91, 1, 'logout', '172.19.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-04 10:43:46', NULL, NULL),
(92, 4, 'login', '172.19.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-04 10:43:58', NULL, NULL),
(93, 4, 'logout', '172.19.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-04 10:44:25', NULL, NULL),
(94, 1, 'login', '172.19.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-04 10:44:35', NULL, NULL),
(95, 1, 'logout', '172.19.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-04 10:44:52', NULL, NULL),
(96, 4, 'login', '172.19.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-04 10:44:59', NULL, NULL),
(97, 4, 'logout', '172.19.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-04 13:16:11', NULL, NULL),
(98, 2, 'login', '172.19.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-04 13:16:20', NULL, NULL),
(99, 2, 'logout', '172.19.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-04 13:20:33', NULL, NULL),
(100, 4, 'login', '172.19.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-04 13:23:12', NULL, NULL),
(101, 4, 'logout', '172.19.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-04 13:24:51', NULL, NULL),
(102, 1, 'login', '172.19.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-04 13:25:01', NULL, NULL),
(103, 1, 'logout', '172.19.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-04 13:25:31', NULL, NULL),
(104, 4, 'login', '172.19.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-04 13:25:49', NULL, NULL),
(105, 4, 'logout', '172.19.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-04 13:27:38', NULL, NULL),
(106, 1, 'login', '172.19.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-04 13:27:44', NULL, NULL),
(107, 1, 'logout', '172.19.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-04 13:28:31', NULL, NULL),
(108, 4, 'login', '172.19.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-04 13:28:39', NULL, NULL),
(109, 4, 'logout', '172.19.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-04 14:12:48', NULL, NULL),
(110, 1, 'login', '172.19.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-04 14:16:27', NULL, NULL),
(111, 1, 'logout', '172.19.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-04 14:21:06', NULL, NULL),
(112, 4, 'login', '172.19.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-04 14:22:14', NULL, NULL),
(113, 4, 'logout', '172.19.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-04 14:36:24', NULL, NULL),
(114, 4, 'login', '172.19.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-04 14:44:21', NULL, NULL);

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
-- Indexes for table `purchase_requests`
--
ALTER TABLE `purchase_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `purchase_requests_customer_id_foreign` (`customer_id`),
  ADD KEY `purchase_requests_bank_id_foreign` (`bank_id`);

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
  ADD KEY `purchase_request_returns_product_id_foreign` (`product_id`);

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT for table `credit_payments`
--
ALTER TABLE `credit_payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `deliveries`
--
ALTER TABLE `deliveries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
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
-- AUTO_INCREMENT for table `purchase_requests`
--
ALTER TABLE `purchase_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `purchase_request_items`
--
ALTER TABLE `purchase_request_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user_logs`
--
ALTER TABLE `user_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=115;

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
-- Constraints for table `purchase_requests`
--
ALTER TABLE `purchase_requests`
  ADD CONSTRAINT `purchase_requests_bank_id_foreign` FOREIGN KEY (`bank_id`) REFERENCES `banks` (`id`) ON DELETE CASCADE,
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
