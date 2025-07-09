-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 08, 2025 at 01:48 AM
-- Server version: 10.4.25-MariaDB
-- PHP Version: 7.4.30

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
  `street` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `barangay` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `province` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zip_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `full_address` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `delivery_address_lat` decimal(10,7) DEFAULT NULL,
  `delivery_address_lng` decimal(10,7) DEFAULT NULL,
  `status` enum('inactive','active') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'inactive',
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
  `firstname` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lastname` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `certificate_registration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `business_permit` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `image`, `description`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Cement & Concrete', NULL, 'Includes cement, blocks, and concrete materials.', 1, '2025-07-06 10:40:29', '2025-07-06 10:40:29', NULL),
(2, 'Steel & Metal Works', NULL, 'Steel rebars and other structural metals.', 1, '2025-07-06 10:40:29', '2025-07-06 10:40:29', NULL),
(3, 'Wood & Boards', NULL, 'Plywood, lumber, and board materials.', 1, '2025-07-06 10:40:29', '2025-07-06 10:40:29', NULL),
(4, 'Roofing Materials', NULL, 'Corrugated sheets and roofing accessories.', 1, '2025-07-06 10:40:29', '2025-07-06 10:40:29', NULL),
(5, 'Plumbing', NULL, 'Pipes and fittings for water systems.', 1, '2025-07-06 10:40:29', '2025-07-06 10:40:29', NULL),
(6, 'Electrical', NULL, 'Electrical wires, tapes, and tools.', 1, '2025-07-06 10:40:29', '2025-07-06 10:40:29', NULL),
(7, 'Paint & Finishing', NULL, 'Paints, coatings, and finishing products.', 1, '2025-07-06 10:40:29', '2025-07-06 10:40:29', NULL),
(8, 'Hardware & Fixtures', NULL, 'Handles, knobs, locks, and similar items.', 1, '2025-07-06 10:40:29', '2025-07-06 10:40:29', NULL),
(9, 'Tools', NULL, 'Manual tools used in construction or repair.', 1, '2025-07-06 10:40:29', '2025-07-06 10:40:29', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `company_settings`
--

CREATE TABLE `company_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `company_logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company_phone` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `company_settings`
--

INSERT INTO `company_settings` (`id`, `company_logo`, `company_email`, `company_phone`, `company_address`, `created_at`, `updated_at`) VALUES
(1, 'assets/upload/TantucoCTC_Logo.png', 'tantucoconstruction@gmail.com', '(042) 525 - 8888 | (042) 525 - 8188', 'Barangay Balubal, Sariaya, 4322, Quezon Province, Philippines', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `deliveries`
--

CREATE TABLE `deliveries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `delivery_rider_id` bigint(20) UNSIGNED DEFAULT NULL,
  `quantity` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `tracking_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','assigned','on_the_way','delivered','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `delivery_date` timestamp NULL DEFAULT NULL,
  `proof_delivery` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
  `remarks` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventories`
--

CREATE TABLE `inventories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('in','out') COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int(11) NOT NULL,
  `reason` enum('restock','sold','returned','damaged','stock update','other') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
  `text` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_file` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `recipient_id`, `text`, `is_file`, `created_at`, `updated_at`) VALUES
(1, 3, 1, 'Hello', NULL, '2025-07-06 15:33:54', '2025-07-06 15:33:54'),
(2, 1, 3, 'Hi', NULL, '2025-07-06 15:33:54', '2025-07-06 15:33:54'),
(3, 3, 1, 'How are you today?', NULL, '2025-07-06 16:09:43', '2025-07-06 16:09:43'),
(4, 1, 3, 'Im fine.', NULL, '2025-07-06 16:09:43', '2025-07-06 16:09:43'),
(5, 3, 1, 'Hello', NULL, '2025-07-06 15:33:54', '2025-07-06 15:33:54'),
(6, 1, 3, 'Hi', NULL, '2025-07-06 15:33:54', '2025-07-06 15:33:54'),
(7, 3, 1, 'How are you today?', NULL, '2025-07-06 16:09:43', '2025-07-06 16:09:43'),
(8, 1, 3, 'Im fine.', NULL, '2025-07-06 16:09:43', '2025-07-06 16:09:43'),
(9, 3, 1, 'Did you finish you task?', NULL, '2025-07-06 16:37:24', '2025-07-06 16:37:24'),
(10, 3, 1, 'Great', NULL, '2025-07-06 16:37:42', '2025-07-06 16:37:42'),
(11, 3, 1, 'Why your not replying?', NULL, '2025-07-06 23:36:35', '2025-07-06 23:36:35'),
(12, 3, 1, 'Uy, please message me', NULL, '2025-07-06 23:39:47', '2025-07-06 23:39:47'),
(13, 1, 3, 'Sorry for late response', NULL, '2025-07-07 10:38:20', '0000-00-00 00:00:00'),
(14, 3, 1, 'Its okay', NULL, '2025-07-07 11:38:20', '2025-07-07 11:38:20'),
(15, 1, 3, 'Did you eat already?', NULL, '2025-07-07 11:40:20', '2025-07-07 11:38:20'),
(16, 3, 1, 'Yes sir.', NULL, '2025-07-07 12:06:15', '2025-07-07 12:06:15'),
(17, 1, 3, 'What did you eat?', NULL, '2025-07-07 12:24:15', '2025-07-07 12:06:15'),
(18, 3, 1, 'Banana', NULL, '2025-07-07 12:31:26', '2025-07-07 12:31:26'),
(19, 2, 1, 'Hello Superadmin.', NULL, '2025-07-07 22:25:42', '2025-07-07 22:25:42'),
(20, 2, 3, 'Hello Sales Officer', NULL, '2025-07-07 22:30:32', '2025-07-07 22:30:32'),
(21, 2, 3, 'Can you send me my delivery report count?', NULL, '2025-07-07 22:35:40', '2025-07-07 22:35:40'),
(22, 3, 2, 'I will send it later.', NULL, '2025-07-07 22:56:05', '2025-07-07 22:56:05'),
(23, 1, 3, 'Nice', NULL, '2025-07-07 22:57:55', '2025-07-07 22:57:55'),
(24, 1, 2, 'Hello rider', NULL, '2025-07-07 22:58:26', '2025-07-07 22:58:26');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
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
(17, '2025_07_01_014725_create_purchase_requests_table', 1),
(18, '2025_07_01_014835_create_purchase_request_items_table', 1),
(19, '2025_07_05_190918_create_delivery_histories_table', 1),
(20, '2025_07_06_170246_create_user_logs_table', 1),
(21, '2025_07_06_231655_create_messages_table', 2),
(22, '2025_07_08_073237_create_notifications_table', 3);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Recipient user',
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'purchase_request, delivery, etc',
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `order_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
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
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sku` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
(1, 1, 'Cement (40kg bag)', 'SKU-685BCEE4C5E58', 'Portland type 1 cement ideal for general construction use', '260.00', NULL, '2025-06-25 02:26:44', '2025-06-25 02:26:44', NULL),
(2, 1, 'Concrete Hollow Block 6”', 'SKU-685BCF19EE185', 'Standard 6-inch hollow block for wall partitions', '18.00', NULL, '2025-06-25 02:27:37', '2025-06-25 02:27:37', NULL),
(3, 2, '10mm Rebar (Steel Bar)', 'SKU-685BCF7725412', '10mm diameter steel reinforcement bar for slabs and columns', '175.00', NULL, '2025-06-25 02:29:11', '2025-06-25 02:29:11', NULL),
(4, 3, 'Plywood (1/4” x 4’ x 8’)', 'SKU-685BCF9C6B631', 'Commercial plywood used for temporary walls and ceiling work', '495.00', NULL, '2025-06-25 02:29:48', '2025-06-25 02:29:48', NULL),
(5, 4, 'GL Sheet (Corrugated, 8ft)', 'SKU-685BCFCA3C81A', 'Galvanized iron sheet for roofing and siding', '320.00', NULL, '2025-06-25 02:30:34', '2025-06-25 02:30:34', NULL),
(6, 5, '1” PVC Pipe (10ft)', 'SKU-685BCFF824CAD', 'Polyvinyl chloride pipe for water supply systems', '120.00', NULL, '2025-06-25 02:31:20', '2025-06-25 02:31:20', NULL),
(7, 6, 'Electrical Tape (Black', 'SKU-685BD014DFD3C', 'Insulating tape for electrical wiring', '25.00', NULL, '2025-06-25 02:31:48', '2025-06-25 02:31:48', NULL),
(8, 7, 'Paint – White Latex (4L)', 'SKU-685BD06953EF9', 'Water-based paint for interior walls and ceilings', '560.00', NULL, '2025-06-25 02:33:13', '2025-06-25 02:33:13', NULL),
(9, 8, 'Door Knob (Entrance Set)', 'SKU-685BD0943711E', 'Heavy-duty cylindrical door knob with lockset', '620.00', NULL, '2025-06-25 02:33:56', '2025-06-25 02:33:56', NULL),
(10, 9, '1” Paint Brush', 'SKU-685BD0B953CE5', 'Standard 1-inch brush used for painting and touch-ups', '35.00', NULL, '2025-06-25 02:34:33', '2025-06-25 02:34:33', NULL),
(11, 1, 'sdfdsf', 'SKU-686A5E1E39DBB', 'dgdfgdfg', '4435.00', '2025-07-23', '2025-07-06 11:29:34', '2025-07-06 11:30:29', '2025-07-06 11:30:29');

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_main` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image_path`, `is_main`, `created_at`, `updated_at`) VALUES
(1, 1, 'assets/upload/products/1750847204_Cement.jpg', 1, '2025-06-25 02:26:44', '2025-06-25 02:26:44'),
(2, 2, 'assets/upload/products/1750847258_Concrete Hollow Block.jpg', 1, '2025-06-25 02:27:38', '2025-06-25 02:27:38'),
(3, 3, 'assets/upload/products/1750847351_texture-steel-deformed-bars-background-600nw-2371218435.webp', 1, '2025-06-25 02:29:11', '2025-06-25 02:29:11'),
(4, 4, 'assets/upload/products/1750847388_Plywood.jpg', 1, '2025-06-25 02:29:48', '2025-06-25 02:29:48'),
(5, 5, 'assets/upload/products/1750847434_Roof GL Sheet Corrugated .jpg', 1, '2025-06-25 02:30:34', '2025-06-25 02:30:34'),
(6, 6, 'assets/upload/products/1750847480_PVC pipe.jpg', 1, '2025-06-25 02:31:20', '2025-06-25 02:31:20'),
(7, 8, 'assets/upload/products/1750847593_2017596014.webp', 1, '2025-06-25 02:33:13', '2025-06-25 02:33:13'),
(8, 9, 'assets/upload/products/1750847636_Door Knob set.jpg', 1, '2025-06-25 02:33:56', '2025-06-25 02:33:56'),
(9, 10, 'assets/upload/products/1750847673_Paint brush.jpg', 1, '2025-06-25 02:34:33', '2025-06-25 02:34:33'),
(10, 7, 'assets/upload/products/1750847714_Electric tape .jpg', 1, '2025-06-25 02:35:14', '2025-06-25 02:35:14'),
(11, 11, 'assets/upload/products/1751801374_TantucoCTC_Logo.png', 1, '2025-07-06 11:29:34', '2025-07-06 11:30:03');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_requests`
--

CREATE TABLE `purchase_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `status` enum('pending','quotation_sent','po_submitted','so_created','delivery_in_progress','delivered','invoice_sent') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_request_items`
--

CREATE TABLE `purchase_request_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `purchase_request_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `terms_conditions`
--

CREATE TABLE `terms_conditions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `content_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `profile` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `force_password_change` tinyint(1) NOT NULL DEFAULT 0,
  `created_by_admin` tinyint(1) NOT NULL DEFAULT 0,
  `role` enum('b2b','deliveryrider','salesofficer','superadmin') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'b2b',
  `otp_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `otp_expire` timestamp NULL DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `profile`, `username`, `email`, `email_verified_at`, `password`, `force_password_change`, `created_by_admin`, `role`, `otp_code`, `otp_expire`, `status`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'John Superadmin', NULL, 'superadmin', 'superadmin@example.com', '2025-07-06 11:13:52', '$2y$10$PbSwf0ovjnjHQxRlJdpGpuasDr0nKRKUab324EJlEjaem6Cd2PQL2', 0, 0, 'superadmin', '490612', '2025-07-06 11:23:34', 1, NULL, '2025-07-06 10:50:58', '2025-07-06 11:13:52', NULL),
(2, 'John DeliveryRider', NULL, 'deliveryrider', 'deliveryrider@example.com', '2025-07-07 22:20:00', '$2y$10$Oz6sqhBKFN8zVKal/aYya.llv51HMQkNq9nq1JzyKk.R.Em2C7wve', 0, 0, 'deliveryrider', '512533', '2025-07-07 22:28:57', 1, NULL, '2025-07-06 10:50:58', '2025-07-07 22:20:00', NULL),
(3, 'John SalesOfficer', NULL, 'salesofficer', 'salesofficer@example.com', '2025-07-06 14:39:52', '$2y$10$nzqI4PGUFUiwcDLjbW6IUucOpY6QKbZdsW5I658EHVAZbbua.Cr7C', 0, 0, 'salesofficer', '376622', '2025-07-06 14:49:00', 1, NULL, '2025-07-06 10:50:58', '2025-07-06 14:39:52', NULL),
(4, 'John B2B', NULL, 'b2b', 'b2b@example.com', '2025-07-06 11:05:27', '$2y$10$jppqDfwPlTJqOce0.LFeAedXjdpElF8RAX31rGE/wyT5T7g20akA.', 0, 0, 'b2b', '109592', '2025-07-06 11:14:57', 1, NULL, '2025-07-06 10:50:58', '2025-07-06 13:54:56', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_logs`
--

CREATE TABLE `user_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `event` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logged_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_logs`
--

INSERT INTO `user_logs` (`id`, `user_id`, `event`, `ip_address`, `user_agent`, `logged_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-06 11:04:37', NULL, NULL),
(2, 4, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-06 11:05:02', NULL, NULL),
(3, 4, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-06 11:09:20', NULL, NULL),
(4, 4, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-06 11:10:47', NULL, NULL),
(5, 4, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-06 11:11:08', NULL, NULL),
(6, 1, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-06 11:13:39', NULL, NULL),
(7, 1, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-06 13:58:00', NULL, NULL),
(8, 4, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-06 14:01:18', NULL, NULL),
(9, 4, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-06 14:01:22', NULL, NULL),
(10, 4, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-06 14:04:56', NULL, NULL),
(11, 4, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-06 14:05:00', NULL, NULL),
(12, 4, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-06 14:06:09', NULL, NULL),
(13, 4, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-06 14:06:35', NULL, NULL),
(14, 3, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-06 14:39:04', NULL, NULL),
(15, 3, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-06 23:18:03', NULL, NULL),
(16, 3, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-07 11:04:49', NULL, NULL),
(17, 2, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-07 22:19:03', NULL, NULL),
(18, 2, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-07 22:35:57', NULL, NULL),
(19, 3, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-07 22:36:09', NULL, NULL),
(20, 3, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-07 22:56:19', NULL, NULL),
(21, 1, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-07 22:56:29', NULL, NULL),
(22, 1, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-07 23:43:03', NULL, NULL),
(23, 3, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-07 23:43:16', NULL, NULL);

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
  ADD KEY `purchase_requests_customer_id_foreign` (`customer_id`);

--
-- Indexes for table `purchase_request_items`
--
ALTER TABLE `purchase_request_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `purchase_request_items_purchase_request_id_foreign` (`purchase_request_id`),
  ADD KEY `purchase_request_items_product_id_foreign` (`product_id`);

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `company_settings`
--
ALTER TABLE `company_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventories`
--
ALTER TABLE `inventories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `purchase_requests`
--
ALTER TABLE `purchase_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_request_items`
--
ALTER TABLE `purchase_request_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `terms_conditions`
--
ALTER TABLE `terms_conditions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user_logs`
--
ALTER TABLE `user_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

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
  ADD CONSTRAINT `purchase_requests_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `purchase_request_items`
--
ALTER TABLE `purchase_request_items`
  ADD CONSTRAINT `purchase_request_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `purchase_request_items_purchase_request_id_foreign` FOREIGN KEY (`purchase_request_id`) REFERENCES `purchase_requests` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_logs`
--
ALTER TABLE `user_logs`
  ADD CONSTRAINT `user_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
