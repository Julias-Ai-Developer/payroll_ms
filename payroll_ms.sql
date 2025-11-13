-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Nov 13, 2025 at 08:07 AM
-- Server version: 8.0.42
-- PHP Version: 8.4.7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `payroll_ms`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password`, `created_at`) VALUES
(1, 'admin', '$2y$12$E568c/5gc8utorUQPo4m1OG9lkLXbq97h5Lhg3N75bM6IBTImpS4i', '2025-10-01 19:25:13');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int NOT NULL,
  `admin_id` int NOT NULL,
  `admin_username` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `action` varchar(100) DEFAULT NULL,
  `details` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `admin_id`, `admin_username`, `action`, `details`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, NULL, 'Admin logout', 'Admin user adminKamatrustai logged out', '2025-10-02 16:56:05', '2025-10-02 16:56:05', NULL),
(2, 1, NULL, 'Admin login', 'Admin user adminKamatrustai logged in', '2025-10-02 16:56:07', '2025-10-02 16:56:07', NULL),
(3, 1, 'adminKamatrustai', 'Business Registration', 'Registered new business: MFUKO PLUS INVESTMENTS', '2025-10-02 20:36:52', '2025-10-02 20:36:52', NULL),
(4, 1, 'adminKamatrustai', 'Business Deactivation', 'Deactivated business: kkkkkkkkkkkkk', '2025-10-02 20:37:06', '2025-10-02 20:37:06', NULL),
(5, 1, 'adminKamatrustai', 'Business Update', 'Updated business: MFUKO PLUS INVESTMENTS', '2025-10-02 20:38:55', '2025-10-02 20:38:55', NULL),
(6, 1, 'adminKamatrustai', 'Business Update', 'Updated business: Nugsoft systems', '2025-10-02 20:39:15', '2025-10-02 20:39:15', NULL),
(7, 1, 'adminKamatrustai', 'Owner Registration', 'Registered new owner: Richard Katongole', '2025-10-02 20:40:26', '2025-10-02 20:40:26', NULL),
(8, 1, NULL, 'Admin login', 'Admin user adminKamatrustai logged in', '2025-10-02 21:24:49', '2025-10-02 21:24:49', NULL),
(9, 1, NULL, 'Admin login', 'Admin user adminKamatrustai logged in', '2025-10-03 06:17:22', '2025-10-03 06:17:22', NULL),
(10, 1, NULL, 'Admin logout', 'Admin user adminKamatrustai logged out', '2025-10-03 06:17:25', '2025-10-03 06:17:25', NULL),
(11, 1, NULL, 'Admin login', 'Admin user adminKamatrustai logged in', '2025-10-03 06:18:22', '2025-10-03 06:18:22', NULL),
(12, 1, 'adminKamatrustai', 'Business Registration', 'Registered new business: FREEMAN INVESTMENTS', '2025-10-03 06:19:23', '2025-10-03 06:19:23', NULL),
(13, 1, 'adminKamatrustai', 'Business Update', 'Updated business: FREEMAN INVESTMENTS', '2025-10-03 06:19:33', '2025-10-03 06:19:33', NULL),
(14, 1, 'adminKamatrustai', 'Owner Registration', 'Registered new owner: FREEMAN', '2025-10-03 06:20:32', '2025-10-03 06:20:32', NULL),
(15, 1, NULL, 'Admin login', 'Admin user adminKamatrustai logged in', '2025-10-03 06:34:35', '2025-10-03 06:34:35', NULL),
(16, 1, NULL, 'Admin login', 'Admin user adminKamatrustai logged in', '2025-10-03 06:43:52', '2025-10-03 06:43:52', NULL),
(17, 1, NULL, 'Admin login', 'Admin user adminKamatrustai logged in', '2025-10-03 07:46:41', '2025-10-03 07:46:41', NULL),
(18, 1, NULL, 'Admin login', 'Admin user superAdmin logged in', '2025-10-03 10:13:29', '2025-10-03 10:13:29', NULL),
(19, 1, 'superAdmin', 'Business Registration', 'Registered new business: Isaac inc', '2025-10-03 10:14:58', '2025-10-03 10:14:58', NULL),
(20, 1, 'superAdmin', 'Business Update', 'Updated business: Isaac inc', '2025-10-03 10:15:19', '2025-10-03 10:15:19', NULL),
(21, 1, 'superAdmin', 'Owner Registration', 'Registered new owner: kakumba isaac moses', '2025-10-03 10:16:49', '2025-10-03 10:16:49', NULL),
(22, 1, NULL, 'Admin login', 'Admin user superAdmin logged in', '2025-10-03 10:18:39', '2025-10-03 10:18:39', NULL),
(23, 1, NULL, 'Admin login', 'Admin user superAdmin logged in', '2025-10-03 16:05:07', '2025-10-03 16:05:07', NULL),
(24, 1, 'superAdmin', 'Business Update', 'Updated business: Isaac inc', '2025-10-03 16:16:50', '2025-10-03 16:16:50', NULL),
(25, 1, NULL, 'Admin login', 'Admin user superAdmin logged in', '2025-10-03 16:28:32', '2025-10-03 16:28:32', NULL),
(26, 1, 'superAdmin', 'Business Update', 'Updated business: Isaac inc', '2025-10-03 16:35:58', '2025-10-03 16:35:58', NULL),
(27, 1, NULL, 'Admin login', 'Admin user superAdmin logged in', '2025-10-04 11:42:06', '2025-10-04 11:42:06', NULL),
(28, 1, NULL, 'Admin logout', 'Admin user superAdmin logged out', '2025-10-04 11:49:41', '2025-10-04 11:49:41', NULL),
(29, 1, NULL, 'Admin login', 'Admin user superAdmin logged in', '2025-10-04 12:01:18', '2025-10-04 12:01:18', NULL),
(30, 1, NULL, 'Admin login', 'Admin user superAdmin logged in', '2025-10-20 10:57:43', '2025-10-20 10:57:43', NULL),
(31, 1, 'superAdmin', 'Business Update', 'Updated business: kamatrust ai', '2025-10-20 11:03:43', '2025-10-20 11:03:43', NULL),
(32, 1, NULL, 'Admin login', 'Admin user admin logged in', '2025-10-24 12:59:52', '2025-10-24 12:59:52', NULL),
(33, 1, 'admin', 'Business Registration', 'Registered new business: SASICO OILS', '2025-10-24 13:03:42', '2025-10-24 13:03:42', NULL),
(34, 1, 'admin', 'Business Update', 'Updated business: SASICO OILS', '2025-10-24 13:06:26', '2025-10-24 13:06:26', NULL),
(35, 1, 'admin', 'Owner Registration', 'Registered new owner: BAZIGAPAULPETER', '2025-10-24 13:07:39', '2025-10-24 13:07:39', NULL),
(36, 1, 'admin', 'Business Registration', 'Registered new business: KOMBUCHA LTD', '2025-10-24 13:13:30', '2025-10-24 13:13:30', NULL),
(37, 1, 'admin', 'Business Update', 'Updated business: KOMBUCHA LTD', '2025-10-24 13:14:02', '2025-10-24 13:14:02', NULL),
(38, 1, 'admin', 'Owner Registration', 'Registered new owner: group E Engineers', '2025-10-24 13:15:40', '2025-10-24 13:15:40', NULL),
(39, 1, NULL, 'Admin login', 'Admin user admin logged in', '2025-10-31 12:44:14', '2025-10-31 12:44:14', NULL),
(40, 1, NULL, 'Admin logout', 'Admin user admin logged out', '2025-10-31 13:03:20', '2025-10-31 13:03:20', NULL),
(41, 1, NULL, 'Admin login', 'Admin user admin logged in', '2025-11-03 09:47:20', '2025-11-03 09:47:20', NULL),
(42, 1, NULL, 'Admin login', 'Admin user admin logged in', '2025-11-11 12:22:09', '2025-11-11 12:22:09', NULL),
(43, 1, NULL, 'Admin logout', 'Admin user admin logged out', '2025-11-11 12:25:37', '2025-11-11 12:25:37', NULL),
(44, 1, NULL, 'Admin login', 'Admin user admin logged in', '2025-11-11 13:35:09', '2025-11-11 13:35:09', NULL),
(45, 1, NULL, 'Admin login', 'Admin user admin logged in', '2025-11-11 13:39:38', '2025-11-11 13:39:38', NULL),
(46, 1, NULL, 'Admin login', 'Admin user admin logged in', '2025-11-11 15:42:00', '2025-11-11 15:42:00', NULL),
(47, 1, NULL, 'Admin logout', 'Admin user admin logged out', '2025-11-11 18:27:26', '2025-11-11 18:27:26', NULL),
(48, 1, NULL, 'Admin login', 'Admin user admin logged in', '2025-11-11 18:47:07', '2025-11-11 18:47:07', NULL),
(49, 1, NULL, 'Admin login', 'Admin user admin logged in', '2025-11-11 20:01:40', '2025-11-11 20:01:40', NULL),
(50, 1, 'admin', 'Business Update', 'Updated business: KOMBUCHA LTD', '2025-11-11 20:02:56', '2025-11-11 20:02:56', NULL),
(51, 1, 'admin', 'Business Update', 'Updated business: KOMBUCHA LTD', '2025-11-11 20:03:35', '2025-11-11 20:03:35', NULL),
(52, 1, NULL, 'Admin login', 'Admin user admin logged in', '2025-11-11 20:04:27', '2025-11-11 20:04:27', NULL),
(53, 1, NULL, 'Admin login', 'Admin user admin logged in', '2025-11-11 20:25:04', '2025-11-11 20:25:04', NULL),
(54, 1, 'admin', 'Business Update', 'Updated business: KOMBUCHA LTD', '2025-11-11 20:27:44', '2025-11-11 20:27:44', NULL),
(55, 1, NULL, 'Admin login', 'Admin user admin logged in', '2025-11-11 20:35:39', '2025-11-11 20:35:39', NULL),
(56, 1, NULL, 'Admin login', 'Admin user admin logged in', '2025-11-12 05:13:25', '2025-11-12 05:13:25', NULL),
(57, 1, 'admin', 'Business Registration', 'Registered new business: KAMATRUST ELECTRONICS', '2025-11-12 05:16:14', '2025-11-12 05:16:14', NULL),
(58, 1, 'admin', 'Business Update', 'Updated business: KAMATRUST ELECTRONICS', '2025-11-12 05:19:10', '2025-11-12 05:19:10', NULL),
(59, 1, 'admin', 'Owner Registration', 'Registered new owner: Julias Muyambi', '2025-11-12 05:20:45', '2025-11-12 05:20:45', NULL),
(60, 1, NULL, 'Admin logout', 'Admin user admin logged out', '2025-11-12 05:24:24', '2025-11-12 05:24:24', NULL),
(61, 1, NULL, 'Admin login', 'Admin user admin logged in', '2025-11-12 06:02:27', '2025-11-12 06:02:27', NULL),
(62, 1, 'admin', 'Business Registration', 'Registered new business: Amilal Technology', '2025-11-12 06:04:20', '2025-11-12 06:04:20', NULL),
(63, 1, 'admin', 'Business Update', 'Updated business: Amilal Technology', '2025-11-12 06:04:46', '2025-11-12 06:04:46', NULL),
(64, 1, 'admin', 'Owner Registration', 'Registered new owner: Ahmed Abukar', '2025-11-12 06:05:37', '2025-11-12 06:05:37', NULL),
(65, 1, NULL, 'Admin logout', 'Admin user admin logged out', '2025-11-12 06:08:32', '2025-11-12 06:08:32', NULL),
(66, 1, NULL, 'Admin login', 'Admin user admin logged in', '2025-11-12 10:26:16', '2025-11-12 10:26:16', NULL),
(67, 1, NULL, 'Admin logout', 'Admin user admin logged out', '2025-11-12 10:26:28', '2025-11-12 10:26:28', NULL),
(68, 1, NULL, 'Admin login', 'Admin user admin logged in', '2025-11-12 10:26:59', '2025-11-12 10:26:59', NULL),
(69, 1, NULL, 'Admin logout', 'Admin user admin logged out', '2025-11-12 10:27:09', '2025-11-12 10:27:09', NULL),
(70, 1, NULL, 'Admin login', 'Admin user admin logged in', '2025-11-12 10:55:45', '2025-11-12 10:55:45', NULL),
(71, 1, NULL, 'Admin login', 'Admin user admin logged in', '2025-11-12 11:58:38', '2025-11-12 11:58:38', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `businesses`
--

CREATE TABLE `businesses` (
  `id` int NOT NULL,
  `business_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `registration_number` varchar(20) NOT NULL,
  `registration_date` date NOT NULL,
  `business_type` varchar(100) NOT NULL,
  `status` varchar(100) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `businesses`
--

INSERT INTO `businesses` (`id`, `business_name`, `address`, `email`, `phone`, `registration_number`, `registration_date`, `business_type`, `status`, `created_at`) VALUES
(1, 'kamatrust ai', 'kyanja', 'muyambijulias@gmail.com', '0776828355', '00089', '2025-10-01', 'Company', 'active', '2025-10-01 20:24:31'),
(2, 'Nugsoft systems', 'Kampala', 'nugsoft@gmail.com', '0776828351', '000811', '2025-10-02', 'Company', 'active', '2025-10-01 21:59:17'),
(3, 'kuku', 'kulambilo', 'kuku@gmail.com', '077682111', '0000022', '2025-10-02', 'NGO', 'inactive', '2025-10-01 22:09:28'),
(4, 'English LTD', 'Kungu-Kampala', 'info@eng.com', '077774334', '00231WE', '2025-10-02', 'Company', 'active', '2025-10-02 04:07:15'),
(5, 'kkkkkkkkkkkkk', 'kulungi', 'jam@gmail.com', '0776828312', '000112', '2025-10-02', 'Company', 'inactive', '2025-10-02 04:20:49'),
(6, 'MFUKO PLUS INVESTMENTS', 'Mbarara', 'mfuko@gmail.com', '0776828311', '0000022EW', '2025-10-02', 'NGO', 'active', '2025-10-02 20:36:52'),
(7, 'FREEMAN INVESTMENTS', 'Naguru', 'info@freemaninvestments.com', '0776828311', '0004RE', '2025-10-03', 'NGO', 'active', '2025-10-03 06:19:23'),
(8, 'Isaac inc', 'Kyanja', 'kakumbaisaac@gmail.com', '0707657667', '000876', '2025-10-30', 'Company', 'active', '2025-10-03 10:14:58'),
(9, 'SASICO OILS', 'NEMA SALIGO', 'bazigapaulpeter@gmail.com', '0707276745', '10101010', '2005-12-12', 'Company', 'active', '2025-10-24 13:03:42'),
(10, 'KOMBUCHA LTD', 'Kampala-Kyanja', 'julixeai@gmail.com', '0707276745', '789TR4E0947', '2025-10-24', 'Company', 'active', '2025-10-24 13:13:30'),
(11, 'KAMATRUST ELECTRONICS', 'KYANJA,KAMPALA,UGANDA', 'muyambijulias@gmail.com', '0776828355', 'NIKA001', '2025-11-12', 'Other', 'active', '2025-11-12 05:16:14'),
(12, 'Amilal Technology', 'Rubaga Road, Kampala, Uganda', 'amilal123@gmail.com', '0755110000', '00115', '2025-11-12', 'Company', 'active', '2025-11-12 06:04:19');

-- --------------------------------------------------------

--
-- Table structure for table `business_owners`
--

CREATE TABLE `business_owners` (
  `id` int NOT NULL,
  `business_id` int NOT NULL,
  `id_number` varchar(100) NOT NULL,
  `full_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` varchar(100) NOT NULL,
  `business_role` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `status` varchar(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `business_owners`
--

INSERT INTO `business_owners` (`id`, `business_id`, `id_number`, `full_name`, `username`, `email`, `phone`, `address`, `business_role`, `password`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, '000983838MJ', 'SENIOR GROUP', 'ourgroup', 'kma@gmail.com', '0776828355', 'Kyanja', 'Co-Owner', 'group123', 'active', '2025-10-01 20:30:43', '2025-10-20 11:01:53', NULL),
(3, 6, '000983838RK', 'Richard Katongole', 'richard259', 'richard@mfuko.net', '+256776828390', 'Mbarara', 'Partner', '$2y$12$PUB.tS2/cVZNhcRnlUVkAuXdP4aQjrxImYYuXY8Rz6dJa67pFfNNa', 'active', '2025-10-02 20:40:26', '2025-10-02 20:40:26', NULL),
(4, 7, '000983838RE', 'FREEMAN', 'freeman108', 'freeman@gmail.com', '+256776828311', 'Naguru', 'Director', '$2y$12$FNNJbx8RJVgNRh67Vr7utetOk/k0lprxS4UskTiOKBcyhPNajfMiW', 'active', '2025-10-03 06:20:32', '2025-10-03 06:23:32', NULL),
(5, 8, '000983838Mk', 'kakumba isaac moses', 'kakumbaisaac297', 'kakumbaisaac@gmail.com', '+256707657667', 'Kyanja', 'Primary Owner', '$2y$12$Dvgzco9Nk1VuDIxBGyJbDujtn8mdCaL6aK0FByGtS4LkHgRn.C0gO', 'active', '2025-10-03 10:16:49', '2025-10-04 11:49:23', NULL),
(6, 9, '10101010', 'BAZIGAPAULPETER', 'bazigapaulpeter678', 'bazigapaulpeter@gmail.com', '0707276745', 'NEMA SALIGO', 'Managing Director', '$2y$12$4gwD.o4RGe3fp9UY/pLZROl8osLPGi9KIgjcVTaZfJ8BUeu4CF1oG', 'active', '2025-10-24 13:07:39', '2025-10-24 13:09:55', NULL),
(7, 10, '987890', 'group E Engineers', 'groupe-engineers', 'muyambijulias@gmail.com', '0707276745', 'kampala', 'Co-Owner', '$2y$12$5x.oa0PjV.lFgBnnLh8ftuFNeQcllwZymwBNeePfJzuMtm927LHP6', 'active', '2025-10-24 13:15:40', '2025-11-03 09:50:37', NULL),
(8, 11, '099999MJ', 'Julias Muyambi', 'muyambijulias2384', 'muyambijulias2@gmail.com', '+256776828355', 'Kyanja , Kampala, Uganda', 'Primary Owner', '$2y$12$i94Zw8gTtmTGH2K1V90E3ugaGoTtOWtmP9At7mkOlqPAA9Fr7Uf4q', 'active', '2025-11-12 05:20:45', '2025-11-12 05:22:33', NULL),
(9, 12, '09090909099', 'Ahmed Abukar', 'amilal123346', 'amilal123@gmail.com', '+256776828355', 'Kyanja , Kampala, Uganda', 'Primary Owner', '$2y$12$rgaPKUoV7d5jeEgYzQKF2eVHvQmt5VVNEaPKgJDBNgqum1EVV455K', 'active', '2025-11-12 06:05:37', '2025-11-12 06:07:01', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `deduction_types`
--

CREATE TABLE `deduction_types` (
  `id` int NOT NULL,
  `business_id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `code` varchar(50) NOT NULL,
  `method` enum('fixed','percent','bracket') NOT NULL,
  `amount` decimal(10,2) DEFAULT '0.00',
  `percent` decimal(5,2) DEFAULT '0.00',
  `employer_percent` decimal(5,2) DEFAULT '0.00',
  `brackets` text,
  `statutory` tinyint(1) DEFAULT '0',
  `enabled` tinyint(1) DEFAULT '1',
  `description` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `deduction_types`
--

INSERT INTO `deduction_types` (`id`, `business_id`, `name`, `code`, `method`, `amount`, `percent`, `employer_percent`, `brackets`, `statutory`, `enabled`, `description`, `created_at`, `updated_at`) VALUES
(1, 10, 'wellfare mng', 'WFF', 'fixed', 900.00, 50.00, 10.00, '', 0, 1, 'new', '2025-11-11 21:52:59', '2025-11-11 22:47:27'),
(2, 10, 'Paye', 'PAYE', 'percent', 20000.00, 20.00, 50.00, '', 0, 1, '', '2025-11-11 22:21:53', '2025-11-11 22:52:11'),
(3, 10, 'Nssf', 'NSSF', 'percent', 0.00, 20.00, 70.00, '', 1, 1, '', '2025-11-11 22:23:25', '2025-11-11 22:54:30'),
(4, 11, 'Nssf', 'NSSF', 'percent', 50000.00, 59.00, 70.00, '', 0, 1, 'Nssf paymment', '2025-11-12 05:34:23', '2025-11-12 05:41:54');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int NOT NULL,
  `employee_id` varchar(20) NOT NULL,
  `business_id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `position` varchar(100) NOT NULL,
  `basic_salary` decimal(15,2) NOT NULL,
  `allowances` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `employee_id`, `business_id`, `name`, `position`, `basic_salary`, `allowances`, `created_at`, `updated_at`) VALUES
(3, 'KA001', 1, 'Akankunda Norah', 'Developer', 8900000000.00, 200000.00, '2025-10-02 20:25:14', '2025-10-02 20:25:14'),
(4, 'KA002', 1, 'Julias Muyambi', 'CO-founder', 8000000000.00, 7000000.00, '2025-10-02 20:33:48', '2025-10-02 20:33:48'),
(5, 'KA33', 6, 'Julias Muyambi', 'Engineer', 4000000000.00, 200000.00, '2025-10-02 20:50:19', '2025-10-02 20:50:19'),
(6, 'K0078', 6, 'Ninsiima Leticia', 'Intern', 890000.00, 5000.00, '2025-10-02 20:51:07', '2025-10-02 20:51:07'),
(7, 'KA0077', 6, 'Akantoorana Mackline', 'Sales Officer', 1000000000.00, 100000.00, '2025-10-02 20:51:45', '2025-10-02 20:51:45'),
(8, 'FR001', 7, 'freeman', 'Engineer', 8000000000.00, 200000000.00, '2025-10-03 06:30:47', '2025-10-03 06:30:47'),
(9, '12345', 8, 'bosco bala', 'cleaner', 250000.00, 1000.00, '2025-10-03 10:21:44', '2025-10-03 10:21:44'),
(10, 'MMM667', 8, 'James atuhe', 'Engineer', 1000000.00, 100000.00, '2025-10-04 11:52:39', '2025-10-04 11:52:39'),
(11, '01123', 1, 'Benjamin Kaweesa', 'Developer', 9500000000.00, 200000.00, '2025-10-20 11:48:58', '2025-10-20 11:48:58'),
(14, '010', 9, 'stapula moses', 'employee', 100000.00, 10000.00, '2025-10-24 13:26:48', '2025-10-24 13:26:48'),
(15, '01', 9, 'abdul luswata', 'auditor', 120000.00, 10000.00, '2025-10-24 13:28:00', '2025-10-24 13:28:00'),
(16, '09', 9, 'ahemed', 'cleaner', 12000.00, 1000.00, '2025-10-24 13:32:26', '2025-10-24 13:32:26'),
(19, 'KO0001', 10, 'Julias Muyambi', 'Engineer', 9000000.00, 99999.99, '2025-11-11 20:16:11', '2025-11-11 20:16:11'),
(27, 'KA0001', 11, 'Kakumba Isaac', 'Engineer', 1200000.00, 150000.00, '2025-11-12 05:37:48', '2025-11-12 05:39:24'),
(28, 'AM0001', 12, 'kamaukama Moses', 'Developer', 10000000.00, 1000000.00, '2025-11-12 06:10:14', '2025-11-12 06:10:14'),
(29, 'KO0002', 10, 'Joshua Moses', 'teacher', 1000000.00, 200000.00, '2025-11-12 11:02:34', '2025-11-12 11:02:34');

-- --------------------------------------------------------

--
-- Table structure for table `employee_deductions`
--

CREATE TABLE `employee_deductions` (
  `id` int NOT NULL,
  `business_id` int NOT NULL,
  `employee_id` int NOT NULL,
  `deduction_type_id` int NOT NULL,
  `custom_amount` decimal(10,2) DEFAULT NULL,
  `custom_percent` decimal(5,2) DEFAULT NULL,
  `balance` decimal(10,2) DEFAULT NULL,
  `active` tinyint(1) DEFAULT '1',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `employee_deductions`
--

INSERT INTO `employee_deductions` (`id`, `business_id`, `employee_id`, `deduction_type_id`, `custom_amount`, `custom_percent`, `balance`, `active`, `start_date`, `end_date`, `created_at`) VALUES
(5, 10, 19, 2, 90000.00, 20.00, 2000000.00, 1, '2025-11-12', '2025-11-12', '2025-11-12 10:19:59'),
(6, 10, 29, 2, NULL, NULL, NULL, 1, NULL, NULL, '2025-11-12 11:02:34');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expiry` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `user_id`, `email`, `token`, `expiry`, `created_at`) VALUES
(1, 1, 'muyambijulias@gmail.com', 'f1e515fc2e22bfc4f278996adc13bd97d099ea648f3027459357063414bce1dc', '2025-11-10 20:54:22', '2025-11-10 19:54:22'),
(2, 1, 'muyambijulias@gmail.com', '037323dc528d346db2c1a3a09f85738c8028491f4373f0bba370909d09fc5634', '2025-11-10 21:25:14', '2025-11-10 20:25:14'),
(3, 1, 'muyambijulias@gmail.com', 'db9164e29193d8ce6fd545a4b9dc10b295906f9736c39723be0ce04aa30d8454', '2025-11-10 21:25:31', '2025-11-10 20:25:31'),
(4, 1, 'muyambijulias@gmail.com', '523d7773baee816bcca7a14a943f4d225ad8c7b69f389d6f6dd3a6b8e0ee8533', '2025-11-10 21:43:32', '2025-11-10 20:43:32'),
(5, 1, 'muyambijulias@gmail.com', 'b6efc180b5e6c7c454d4273c6d30dbeb1cb2ec17828a9c54de8771e3188cf2ee', '2025-11-10 21:47:15', '2025-11-10 20:47:15'),
(6, 1, 'muyambijulias@gmail.com', '354d1c7e6c09470e6fabbe6d8ad5a5c84d9fc09e495f77309334283e70bd338f', '2025-11-10 21:47:28', '2025-11-10 20:47:28'),
(7, 1, 'muyambijulias@gmail.com', '3493043573847d1019fe150f8f6eba2395f883193597c8e327b8d37d5714cdbe', '2025-11-10 21:59:32', '2025-11-10 20:59:32'),
(8, 1, 'muyambijulias@gmail.com', 'cc63f00b5f5c936c76bb4321ef9c76ec620ae999ddc92053ef4d1c492ed3a49a', '2025-11-10 21:59:56', '2025-11-10 20:59:56'),
(9, 1, 'muyambijulias@gmail.com', '80f347a057b1763e33c32fcce455a121e85b3c8658bebb429d41a9752057cb02', '2025-11-10 22:08:35', '2025-11-10 21:08:35'),
(10, 1, 'muyambijulias@gmail.com', '862e8da0142232e025b31c2022fc42a809a9e1548f1759ae0fa41daec40010a0', '2025-11-10 22:09:15', '2025-11-10 21:09:15'),
(11, 1, 'muyambijulias@gmail.com', '8487758f1569b0e0223c0bbb881cfb9df044aba98ce99a5adfe2f8a58f9ba348', '2025-11-10 22:09:57', '2025-11-10 21:09:57'),
(12, 1, 'muyambijulias@gmail.com', '13cebd42159b436cfc63aa9d715e10167f2f8b246f46164bdfdba8886ac7c7f8', '2025-11-10 22:20:20', '2025-11-10 21:20:20'),
(13, 1, 'muyambijulias@gmail.com', '073af6a11374f6c479c9ff9b1fadedbda65207c9271a720ba2ca6daf801f3e70', '2025-11-10 22:20:54', '2025-11-10 21:20:54'),
(14, 1, 'muyambijulias@gmail.com', '5e8526d15b7119785c2e9cdb39798d0dc557343ae48605128646a8bb9574d707', '2025-11-10 23:13:39', '2025-11-10 22:13:39'),
(15, 7, 'muyambijulias@gmail.com', 'd11e7253e12c3d460d3ead05908c3ee309df5f69761d6c5d2eac50dfb7a35197', '2025-11-10 23:20:15', '2025-11-10 22:20:15'),
(16, 7, 'muyambijulias@gmail.com', 'f16a17e279636a6df39f695bf53633786c7107f314577eef061cecab796644f0', '2025-11-10 23:20:46', '2025-11-10 22:20:46'),
(17, 7, 'muyambijulias@gmail.com', '9bdcc21465831ad6164a81558cda92f4cadcf3b09cebf36edf83d73410bb2302', '2025-11-10 23:36:42', '2025-11-10 22:36:42'),
(18, 7, 'muyambijulias@gmail.com', 'e7697f662fa5c44992c2d787e6c0da5286c46b251cc0329d8290c033d802296d', '2025-11-10 23:44:42', '2025-11-10 22:44:42'),
(19, 7, 'muyambijulias@gmail.com', '9365567f8a834dab6a46fbfe22348ea2a84614130a7eeaa92aa38a702826c8b5', '2025-11-11 13:17:17', '2025-11-11 12:17:17'),
(20, 7, 'muyambijulias@gmail.com', '167777020bd3e40bd9928686f37e90e2ff53766e6dd08c9cb360f7c88da25e9d', '2025-11-11 14:37:02', '2025-11-11 13:37:02'),
(21, 7, 'muyambijulias@gmail.com', 'ba27f27243b1d51201b34274942112c67bfc8042f72cd4655ba30f3dc586815c', '2025-11-11 21:23:43', '2025-11-11 20:23:43'),
(22, 7, 'muyambijulias@gmail.com', '4258cbba038cb932b2a927448d377d77c4bfc0ece9c90dc9ff3ee23a3fca8b66', '2025-11-12 01:41:21', '2025-11-12 00:41:21'),
(23, 7, 'muyambijulias@gmail.com', '7531b402dda257b412b33f8229120e5b4831acff1fa2956224dbe59d43cfd1c7', '2025-11-12 07:14:07', '2025-11-12 06:14:07'),
(24, 7, 'muyambijulias@gmail.com', '59419ed54904e84207e1f7ebf29f485c6a9b02140ce18317b714573e9ccae119', '2025-11-12 07:14:40', '2025-11-12 06:14:40'),
(25, 7, 'muyambijulias@gmail.com', '9066ab8255bae73dd7c7eb46f1d46bf4356bb3d693f85c77ed09fafdc1fbd67d', '2025-11-12 10:22:06', '2025-11-12 09:22:06');

-- --------------------------------------------------------

--
-- Table structure for table `payroll`
--

CREATE TABLE `payroll` (
  `id` int NOT NULL,
  `employee_id` int NOT NULL,
  `business_id` int NOT NULL,
  `month` varchar(20) NOT NULL,
  `year` int NOT NULL,
  `gross_salary` decimal(15,2) NOT NULL,
  `deductions` decimal(15,2) NOT NULL,
  `net_salary` decimal(15,2) NOT NULL,
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `payroll`
--

INSERT INTO `payroll` (`id`, `employee_id`, `business_id`, `month`, `year`, `gross_salary`, `deductions`, `net_salary`, `status`, `created_at`) VALUES
(13, 3, 1, 'October', 2025, 8900200000.00, 1335030000.00, 7565170000.00, 'active', '2025-10-02 20:31:11'),
(14, 4, 1, 'October', 2025, 8007000000.00, 1201050000.00, 6805950000.00, 'active', '2025-10-02 20:34:27'),
(15, 7, 6, 'October', 2025, 1000100000.00, 150015000.00, 850085000.00, 'active', '2025-10-02 20:52:06'),
(16, 6, 6, 'October', 2025, 895000.00, 134250.00, 760750.00, 'active', '2025-10-02 20:56:36'),
(17, 5, 6, 'October', 2025, 4000200000.00, 600030000.00, 3400170000.00, 'active', '2025-10-02 20:56:42'),
(18, 5, 6, 'February', 2025, 4000200000.00, 600030000.00, 3400170000.00, 'active', '2025-10-02 21:04:34'),
(19, 8, 7, 'October', 2025, 8200000000.00, 1230000000.00, 6970000000.00, 'active', '2025-10-03 06:31:20'),
(20, 9, 8, 'February', 2025, 251000.00, 37650.00, 213350.00, 'active', '2025-10-03 10:22:32'),
(21, 9, 8, 'October', 2025, 251000.00, 37650.00, 213350.00, 'active', '2025-10-03 23:27:28'),
(22, 10, 8, 'October', 2025, 1100000.00, 165000.00, 935000.00, 'inactive', '2025-10-04 11:53:20'),
(23, 11, 1, 'October', 2025, 9500200000.00, 1425030000.00, 8075170000.00, 'active', '2025-10-20 11:49:26'),
(27, 15, 9, 'October', 2025, 130000.00, 19500.00, 110500.00, 'active', '2025-10-24 13:29:35'),
(28, 16, 9, 'October', 2025, 13000.00, 1950.00, 11050.00, 'active', '2025-10-24 13:32:48'),
(34, 27, 11, 'November', 2025, 1350000.00, 796500.00, 553500.00, 'active', '2025-11-12 05:42:39'),
(35, 28, 12, 'November', 2025, 11000000.00, 0.00, 11000000.00, 'active', '2025-11-12 07:46:57'),
(36, 8, 7, 'November', 2025, 8200000000.00, 0.00, 8200000000.00, 'active', '2025-11-12 11:18:36');

-- --------------------------------------------------------

--
-- Table structure for table `payroll_deductions`
--

CREATE TABLE `payroll_deductions` (
  `id` int NOT NULL,
  `business_id` int NOT NULL,
  `payroll_id` int NOT NULL,
  `employee_id` int NOT NULL,
  `deduction_type_id` int NOT NULL,
  `method` varchar(20) NOT NULL,
  `amount_applied` decimal(10,2) NOT NULL,
  `employer_amount` decimal(10,2) DEFAULT '0.00',
  `meta` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `payroll_deductions`
--

INSERT INTO `payroll_deductions` (`id`, `business_id`, `payroll_id`, `employee_id`, `deduction_type_id`, `method`, `amount_applied`, `employer_amount`, `meta`, `created_at`) VALUES
(1, 11, 34, 27, 4, 'percent', 796500.00, 945000.00, '{\"basis\":\"percent\",\"rate\":59,\"employer_percent\":70}', '2025-11-12 05:42:39');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `created_at`) VALUES
(1, 'admin', '$2y$12$0xcjAMN4PUYs1HCd06ZBoeWGbOb8b/WWofB/9fKfd1HgPEGGn869C', '2025-09-30 01:48:38');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `businesses`
--
ALTER TABLE `businesses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `business_owners`
--
ALTER TABLE `business_owners`
  ADD PRIMARY KEY (`id`),
  ADD KEY `business_id` (`business_id`);

--
-- Indexes for table `deduction_types`
--
ALTER TABLE `deduction_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_code_business` (`code`,`business_id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_id` (`employee_id`);

--
-- Indexes for table `employee_deductions`
--
ALTER TABLE `employee_deductions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `deduction_type_id` (`deduction_type_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_token` (`token`),
  ADD KEY `idx_expiry` (`expiry`);

--
-- Indexes for table `payroll`
--
ALTER TABLE `payroll`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_id` (`employee_id`,`month`,`year`);

--
-- Indexes for table `payroll_deductions`
--
ALTER TABLE `payroll_deductions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payroll_id` (`payroll_id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `deduction_type_id` (`deduction_type_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT for table `businesses`
--
ALTER TABLE `businesses`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `business_owners`
--
ALTER TABLE `business_owners`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `deduction_types`
--
ALTER TABLE `deduction_types`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `employee_deductions`
--
ALTER TABLE `employee_deductions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `payroll`
--
ALTER TABLE `payroll`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `payroll_deductions`
--
ALTER TABLE `payroll_deductions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `business_owners`
--
ALTER TABLE `business_owners`
  ADD CONSTRAINT `business_owners_ibfk_1` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `employee_deductions`
--
ALTER TABLE `employee_deductions`
  ADD CONSTRAINT `employee_deductions_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `employee_deductions_ibfk_2` FOREIGN KEY (`deduction_type_id`) REFERENCES `deduction_types` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `business_owners` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payroll`
--
ALTER TABLE `payroll`
  ADD CONSTRAINT `payroll_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payroll_deductions`
--
ALTER TABLE `payroll_deductions`
  ADD CONSTRAINT `payroll_deductions_ibfk_1` FOREIGN KEY (`payroll_id`) REFERENCES `payroll` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payroll_deductions_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payroll_deductions_ibfk_3` FOREIGN KEY (`deduction_type_id`) REFERENCES `deduction_types` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
