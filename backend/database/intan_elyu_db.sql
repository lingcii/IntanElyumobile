-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 29, 2026 at 01:38 AM
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
-- Database: `intan_elyu_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `alerts`
--

CREATE TABLE `alerts` (
  `id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `alerts`
--

INSERT INTO `alerts` (`id`, `type`, `message`, `is_read`, `created_at`) VALUES
(1, 'low_investment', 'Lomboy Grape Farm reported low municipal tourism funding request.', 0, '2026-06-22 16:05:39'),
(2, 'missing_data', 'Sudipen has not updated Q2 tourist attraction data profiles.', 0, '2026-06-22 14:20:39'),
(3, 'delayed_program', 'San Gabriel Eco-Guide training program has been delayed.', 0, '2026-06-21 16:20:39'),
(4, 'agricultural_decline', 'Balaoan seaweed farming area experiences heavy tourist boat traffic.', 0, '2026-06-19 16:20:39');

-- --------------------------------------------------------

--
-- Table structure for table `analytics`
--

CREATE TABLE `analytics` (
  `id` int(11) NOT NULL,
  `municipality_id` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `month` int(11) NOT NULL,
  `visits` int(11) NOT NULL DEFAULT 0,
  `transport_car` int(11) NOT NULL DEFAULT 0,
  `transport_bus` int(11) NOT NULL DEFAULT 0,
  `transport_van` int(11) NOT NULL DEFAULT 0,
  `transport_other` int(11) NOT NULL DEFAULT 0,
  `avg_spend` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `analytics`
--

INSERT INTO `analytics` (`id`, `municipality_id`, `year`, `month`, `visits`, `transport_car`, `transport_bus`, `transport_van`, `transport_other`, `avg_spend`) VALUES
(1, 1, 2025, 1, 7200, 3600, 1080, 1800, 720, 1200.00),
(2, 1, 2025, 2, 6800, 3400, 1020, 1700, 680, 1150.00),
(3, 1, 2025, 3, 10800, 5400, 1620, 2700, 1080, 1400.00),
(4, 1, 2025, 4, 11200, 5600, 1680, 2800, 1120, 1450.00),
(5, 1, 2025, 5, 11500, 5750, 1725, 2875, 1150, 1500.00),
(6, 1, 2025, 6, 5040, 2520, 756, 1260, 504, 1100.00),
(7, 1, 2025, 7, 5600, 2800, 840, 1400, 560, 1050.00),
(8, 1, 2025, 8, 5400, 2700, 810, 1350, 540, 1020.00),
(9, 1, 2025, 9, 5200, 2600, 780, 1300, 520, 1000.00),
(10, 1, 2025, 10, 7000, 3500, 1050, 1750, 700, 1180.00),
(11, 1, 2025, 11, 7500, 3750, 1125, 1875, 750, 1220.00),
(12, 1, 2025, 12, 9100, 4550, 1365, 2275, 910, 1350.00),
(13, 1, 2026, 1, 8100, 4212, 1053, 2187, 648, 1260.00),
(14, 1, 2026, 2, 7600, 3952, 988, 2052, 608, 1208.00),
(15, 1, 2026, 3, 12100, 6292, 1573, 3267, 968, 1470.00),
(16, 1, 2026, 4, 12500, 6500, 1625, 3375, 1000, 1523.00),
(17, 1, 2026, 5, 12800, 6656, 1664, 3456, 1024, 1575.00),
(18, 1, 2026, 6, 5600, 2912, 728, 1512, 448, 1155.00),
(19, 2, 2025, 1, 5400, 2700, 810, 1350, 540, 1100.00),
(20, 2, 2025, 6, 3780, 1890, 567, 945, 378, 980.00),
(21, 2, 2026, 6, 4200, 2184, 546, 1134, 336, 1029.00),
(22, 3, 2025, 6, 3150, 1575, 473, 788, 315, 850.00),
(23, 3, 2026, 6, 3500, 1820, 455, 945, 280, 893.00),
(24, 4, 2025, 6, 2625, 1313, 394, 656, 263, 780.00),
(25, 4, 2026, 6, 2900, 1508, 377, 783, 232, 819.00),
(26, 5, 2025, 6, 2975, 1488, 446, 744, 297, 920.00),
(27, 5, 2026, 6, 3300, 1716, 429, 891, 264, 966.00),
(28, 6, 2025, 6, 2520, 1260, 378, 630, 252, 750.00),
(29, 6, 2026, 6, 2800, 1456, 364, 756, 224, 788.00);

-- --------------------------------------------------------

--
-- Table structure for table `fare_guides`
--

CREATE TABLE `fare_guides` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `vehicle_type` enum('PUB_Aircon','PUB_Ordinary','PUJ_Aircon','PUJ_Ordinary','Tricycle','Van') NOT NULL,
  `region` varchar(100) NOT NULL,
  `effective_date` date NOT NULL,
  `plate_number` varchar(50) DEFAULT NULL,
  `status` enum('draft','active','archived') DEFAULT 'draft',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fare_guides`
--

INSERT INTO `fare_guides` (`id`, `title`, `vehicle_type`, `region`, `effective_date`, `plate_number`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'PUB (Aircon) GENERAL FARE GUIDE', 'PUB_Aircon', 'Metro Manila', '2022-10-03', '', 'active', 1, '2026-06-24 15:31:55', '2026-06-24 15:31:59'),
(2, 'PUJ (Ordinary) GENERAL FARE GUIDE', 'PUJ_Ordinary', 'Provincial', '2026-06-24', '', 'active', 7, '2026-06-24 15:45:15', '2026-06-24 15:45:23');

-- --------------------------------------------------------

--
-- Table structure for table `fare_matrices`
--

CREATE TABLE `fare_matrices` (
  `id` int(11) NOT NULL,
  `fare_guide_id` int(11) NOT NULL,
  `distance_km` decimal(5,2) NOT NULL,
  `regular_fare` decimal(8,2) NOT NULL,
  `discounted_fare` decimal(8,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fare_matrices`
--

INSERT INTO `fare_matrices` (`id`, `fare_guide_id`, `distance_km`, `regular_fare`, `discounted_fare`, `created_at`) VALUES
(1, 1, 1.00, 15.00, 12.00, '2026-06-24 15:31:55'),
(2, 1, 2.00, 16.50, 13.20, '2026-06-24 15:31:55'),
(3, 1, 3.00, 18.00, 14.40, '2026-06-24 15:31:55'),
(4, 1, 4.00, 19.50, 15.60, '2026-06-24 15:31:55'),
(5, 1, 5.00, 21.00, 16.80, '2026-06-24 15:31:55'),
(6, 1, 6.00, 22.50, 18.00, '2026-06-24 15:31:55'),
(7, 1, 7.00, 24.00, 19.20, '2026-06-24 15:31:55'),
(8, 1, 8.00, 25.50, 20.40, '2026-06-24 15:31:55'),
(9, 1, 9.00, 27.00, 21.60, '2026-06-24 15:31:55'),
(10, 1, 10.00, 28.50, 22.80, '2026-06-24 15:31:55'),
(11, 1, 11.00, 30.00, 24.00, '2026-06-24 15:31:55'),
(12, 1, 12.00, 31.50, 25.20, '2026-06-24 15:31:55'),
(13, 1, 13.00, 33.00, 26.40, '2026-06-24 15:31:55'),
(14, 1, 14.00, 34.50, 27.60, '2026-06-24 15:31:55'),
(15, 1, 15.00, 36.00, 28.80, '2026-06-24 15:31:55'),
(16, 1, 16.00, 37.50, 30.00, '2026-06-24 15:31:55'),
(17, 1, 17.00, 39.00, 31.20, '2026-06-24 15:31:55'),
(18, 1, 18.00, 40.50, 32.40, '2026-06-24 15:31:55'),
(19, 1, 19.00, 42.00, 33.60, '2026-06-24 15:31:55'),
(20, 1, 20.00, 43.50, 34.80, '2026-06-24 15:31:55'),
(21, 1, 21.00, 45.00, 36.00, '2026-06-24 15:31:55'),
(22, 1, 22.00, 46.50, 37.20, '2026-06-24 15:31:55'),
(23, 1, 23.00, 48.00, 38.40, '2026-06-24 15:31:55'),
(24, 1, 24.00, 49.50, 39.60, '2026-06-24 15:31:55'),
(25, 1, 25.00, 51.00, 40.80, '2026-06-24 15:31:55'),
(26, 1, 26.00, 52.50, 42.00, '2026-06-24 15:31:55'),
(27, 1, 27.00, 54.00, 43.20, '2026-06-24 15:31:55'),
(28, 1, 28.00, 55.50, 44.40, '2026-06-24 15:31:55'),
(29, 1, 29.00, 57.00, 45.60, '2026-06-24 15:31:55'),
(30, 1, 30.00, 58.50, 46.80, '2026-06-24 15:31:55'),
(31, 1, 31.00, 60.00, 48.00, '2026-06-24 15:31:55'),
(32, 1, 32.00, 61.50, 49.20, '2026-06-24 15:31:55'),
(33, 1, 33.00, 63.00, 50.40, '2026-06-24 15:31:55'),
(34, 1, 34.00, 64.50, 51.60, '2026-06-24 15:31:55'),
(35, 1, 35.00, 66.00, 52.80, '2026-06-24 15:31:55'),
(36, 1, 36.00, 67.50, 54.00, '2026-06-24 15:31:55'),
(37, 1, 37.00, 69.00, 55.20, '2026-06-24 15:31:55'),
(38, 1, 38.00, 70.50, 56.40, '2026-06-24 15:31:55'),
(39, 1, 39.00, 72.00, 57.60, '2026-06-24 15:31:55'),
(40, 1, 40.00, 73.50, 58.80, '2026-06-24 15:31:55'),
(41, 2, 1.00, 12.00, 9.60, '2026-06-24 15:45:15'),
(42, 2, 2.00, 13.20, 10.56, '2026-06-24 15:45:15'),
(43, 2, 3.00, 14.40, 11.52, '2026-06-24 15:45:15'),
(44, 2, 4.00, 15.60, 12.48, '2026-06-24 15:45:15'),
(45, 2, 5.00, 16.80, 13.44, '2026-06-24 15:45:15'),
(46, 2, 6.00, 18.00, 14.40, '2026-06-24 15:45:15'),
(47, 2, 7.00, 19.20, 15.36, '2026-06-24 15:45:15'),
(48, 2, 8.00, 20.40, 16.32, '2026-06-24 15:45:15'),
(49, 2, 9.00, 21.60, 17.28, '2026-06-24 15:45:15'),
(50, 2, 10.00, 22.80, 18.24, '2026-06-24 15:45:15'),
(51, 2, 11.00, 24.00, 19.20, '2026-06-24 15:45:15'),
(52, 2, 12.00, 25.20, 20.16, '2026-06-24 15:45:15'),
(53, 2, 13.00, 26.40, 21.12, '2026-06-24 15:45:15'),
(54, 2, 14.00, 27.60, 22.08, '2026-06-24 15:45:15'),
(55, 2, 15.00, 28.80, 23.04, '2026-06-24 15:45:15'),
(56, 2, 16.00, 30.00, 24.00, '2026-06-24 15:45:15'),
(57, 2, 17.00, 31.20, 24.96, '2026-06-24 15:45:15'),
(58, 2, 18.00, 32.40, 25.92, '2026-06-24 15:45:15'),
(59, 2, 19.00, 33.60, 26.88, '2026-06-24 15:45:15'),
(60, 2, 20.00, 34.80, 27.84, '2026-06-24 15:45:15'),
(61, 2, 21.00, 36.00, 28.80, '2026-06-24 15:45:15'),
(62, 2, 22.00, 37.20, 29.76, '2026-06-24 15:45:15'),
(63, 2, 23.00, 38.40, 30.72, '2026-06-24 15:45:15'),
(64, 2, 24.00, 39.60, 31.68, '2026-06-24 15:45:15'),
(65, 2, 25.00, 40.80, 32.64, '2026-06-24 15:45:15'),
(66, 2, 26.00, 42.00, 33.60, '2026-06-24 15:45:15'),
(67, 2, 27.00, 43.20, 34.56, '2026-06-24 15:45:15'),
(68, 2, 28.00, 44.40, 35.52, '2026-06-24 15:45:15'),
(69, 2, 29.00, 45.60, 36.48, '2026-06-24 15:45:15'),
(70, 2, 30.00, 46.80, 37.44, '2026-06-24 15:45:15'),
(71, 2, 31.00, 48.00, 38.40, '2026-06-24 15:45:15'),
(72, 2, 32.00, 49.20, 39.36, '2026-06-24 15:45:15'),
(73, 2, 33.00, 50.40, 40.32, '2026-06-24 15:45:15'),
(74, 2, 34.00, 51.60, 41.28, '2026-06-24 15:45:15'),
(75, 2, 35.00, 52.80, 42.24, '2026-06-24 15:45:15'),
(76, 2, 36.00, 54.00, 43.20, '2026-06-24 15:45:15'),
(77, 2, 37.00, 55.20, 44.16, '2026-06-24 15:45:15'),
(78, 2, 38.00, 56.40, 45.12, '2026-06-24 15:45:15'),
(79, 2, 39.00, 57.60, 46.08, '2026-06-24 15:45:15'),
(80, 2, 40.00, 58.80, 47.04, '2026-06-24 15:45:15');

-- --------------------------------------------------------

--
-- Table structure for table `fare_uploads`
--

CREATE TABLE `fare_uploads` (
  `id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` int(11) NOT NULL,
  `file_type` varchar(50) NOT NULL,
  `uploaded_by` int(11) NOT NULL,
  `status` enum('pending','processing','completed','failed') DEFAULT 'pending',
  `total_records` int(11) DEFAULT 0,
  `valid_records` int(11) DEFAULT 0,
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `processed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fare_uploads`
--

INSERT INTO `fare_uploads` (`id`, `file_name`, `file_path`, `file_size`, `file_type`, `uploaded_by`, `status`, `total_records`, `valid_records`, `error_message`, `created_at`, `processed_at`) VALUES
(1, 'Fare-Guide_Modernized-Aircon-Provisional-Fare-Increase_08Oct2023.pdf', 'C:\\xamp\\htdocs\\Gaw-at-GO-System\\backendWebsite\\controllers\\PITCO/../../uploads/fare_pitco_6a3bf86b7caa72.42744332_Fare-Guide_Modernized-Aircon-Provisional-Fare-Increase_08Oct2023.pdf', 121294, 'application/pdf', 1, 'completed', 40, 40, NULL, '2026-06-24 15:31:55', '2026-06-24 15:31:55'),
(2, 'TAXI-Fare-Rates.pdf', 'C:\\xamp\\htdocs\\Gaw-at-GO-System\\backendWebsite\\controllers\\MUNICIPAL/../../uploads/fare_mto_6a3bfb8bdaa774.67825875_TAXI-Fare-Rates.pdf', 117746, 'application/pdf', 7, 'completed', 40, 40, NULL, '2026-06-24 15:45:15', '2026-06-24 15:45:15');

-- --------------------------------------------------------

--
-- Table structure for table `import_logs`
--

CREATE TABLE `import_logs` (
  `id` int(11) NOT NULL,
  `fare_upload_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `details` text NOT NULL,
  `severity` enum('info','warning','error') DEFAULT 'info',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `import_logs`
--

INSERT INTO `import_logs` (`id`, `fare_upload_id`, `action`, `details`, `severity`, `created_at`) VALUES
(1, 1, 'Upload record created', 'Upload ID: 1, Original filename: Fare-Guide_Modernized-Aircon-Provisional-Fare-Increase_08Oct2023.pdf', 'info', '2026-06-24 15:31:55'),
(2, 1, 'Starting PDF parsing', 'File: C:\\xamp\\htdocs\\Gaw-at-GO-System\\backendWebsite\\controllers\\PITCO/../../uploads/fare_pitco_6a3bf86b7caa72.42744332_Fare-Guide_Modernized-Aircon-Provisional-Fare-Increase_08Oct2023.pdf, Original filename: Fare-Guide_Modernized-Aircon-Provisional-Fare-Increase_08Oct2023.pdf', 'info', '2026-06-24 15:31:55'),
(3, 1, 'Selecting simulation for filename', 'Fare-Guide_Modernized-Aircon-Provisional-Fare-Increase_08Oct2023.pdf', 'info', '2026-06-24 15:31:55'),
(4, 1, 'Matched simulation keyword', 'aircon', 'info', '2026-06-24 15:31:55'),
(5, 1, 'Text extracted successfully', 'Length: 588', 'info', '2026-06-24 15:31:55'),
(6, 1, 'Extracted title', 'PUB (Aircon) GENERAL FARE GUIDE', 'info', '2026-06-24 15:31:55'),
(7, 1, 'Extracted region', 'Metro Manila', 'info', '2026-06-24 15:31:55'),
(8, 1, 'Extracted effective date', '2022-10-03', 'info', '2026-06-24 15:31:55'),
(9, 1, 'Extracted plate number', '', 'info', '2026-06-24 15:31:55'),
(10, 1, 'Table header detected', 'Distance (kms.)	Regular	Student/Elderly/Disabled', 'info', '2026-06-24 15:31:55'),
(11, 1, 'Fare data extracted', 'Records: 40, Vehicle Type: PUB_Aircon', 'info', '2026-06-24 15:31:55'),
(12, 1, 'Data saved successfully', 'Fare Guide ID: 1', 'info', '2026-06-24 15:31:55'),
(13, 1, 'Processing completed', '', 'info', '2026-06-24 15:31:55'),
(14, 1, 'status_change', 'Fare guide #1 set to active by user #1', 'info', '2026-06-24 15:31:59'),
(15, 2, 'Upload record created', 'Upload ID: 2, Original filename: TAXI-Fare-Rates.pdf', 'info', '2026-06-24 15:45:15'),
(16, 2, 'Starting PDF parsing', 'File: C:\\xamp\\htdocs\\Gaw-at-GO-System\\backendWebsite\\controllers\\MUNICIPAL/../../uploads/fare_mto_6a3bfb8bdaa774.67825875_TAXI-Fare-Rates.pdf, Original filename: TAXI-Fare-Rates.pdf', 'info', '2026-06-24 15:45:15'),
(17, 2, 'Selecting simulation for filename', 'TAXI-Fare-Rates.pdf', 'info', '2026-06-24 15:45:15'),
(18, 2, 'No keyword match, using default simulation', 'puj', 'info', '2026-06-24 15:45:15'),
(19, 2, 'Text extracted successfully', 'Length: 654', 'info', '2026-06-24 15:45:15'),
(20, 2, 'Extracted title', 'PUJ (Ordinary) GENERAL FARE GUIDE', 'info', '2026-06-24 15:45:15'),
(21, 2, 'Extracted region', 'Provincial', 'info', '2026-06-24 15:45:15'),
(22, 2, 'Extracted effective date', '2026-06-24', 'info', '2026-06-24 15:45:15'),
(23, 2, 'Extracted plate number', '', 'info', '2026-06-24 15:45:15'),
(24, 2, 'Table header detected', 'Distance (kms.)	Regular	Student/Elderly/Disabled', 'info', '2026-06-24 15:45:15'),
(25, 2, 'Fare data extracted', 'Records: 40, Vehicle Type: PUJ_Ordinary', 'info', '2026-06-24 15:45:15'),
(26, 2, 'Data saved successfully', 'Fare Guide ID: 2', 'info', '2026-06-24 15:45:15'),
(27, 2, 'Processing completed', '', 'info', '2026-06-24 15:45:15');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `municipalities`
--

CREATE TABLE `municipalities` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `latitude` decimal(10,6) NOT NULL,
  `longitude` decimal(10,6) NOT NULL,
  `attraction_count` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `municipalities`
--

INSERT INTO `municipalities` (`id`, `name`, `latitude`, `longitude`, `attraction_count`) VALUES
(1, 'San Juan', 16.664400, 120.320800, 131),
(2, 'San Fernando City', 16.615600, 120.316700, 110),
(3, 'Bauang', 16.529700, 120.330800, 90),
(4, 'Agoo', 16.321700, 120.368300, 79),
(5, 'Luna', 16.852500, 120.379700, 68),
(6, 'San Gabriel', 16.666700, 120.416700, 59),
(7, 'Balaoan', 16.824400, 120.400300, 45),
(8, 'Aringay', 16.395600, 120.354700, 39),
(9, 'Rosario', 16.230000, 120.486400, 35),
(10, 'Bacnotan', 16.726400, 120.351900, 32),
(11, 'Naguilian', 16.536700, 120.395300, 31),
(12, 'Tubao', 16.347500, 120.434200, 27),
(13, 'Pugo', 16.326700, 120.482800, 24),
(14, 'Caba', 16.429400, 120.350300, 22),
(15, 'Santo Tomas', 16.273100, 120.384700, 20),
(16, 'Bangar', 16.896700, 120.418100, 18),
(17, 'Burgos', 16.520800, 120.485000, 15),
(18, 'Bagulin', 16.606700, 120.450300, 12),
(19, 'Santol', 16.766700, 120.466700, 11),
(20, 'Sudipen', 16.902200, 120.444700, 10);

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `id` int(11) NOT NULL,
  `role` varchar(50) NOT NULL,
  `permission` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`id`, `role`, `permission`, `created_at`) VALUES
(1, 'san_juan_mto', 'view_dashboard', '2026-06-22 16:20:39'),
(2, 'san_juan_mto', 'manage_tourist_spots', '2026-06-22 16:20:39'),
(3, 'san_juan_mto', 'manage_fares', '2026-06-22 16:20:39'),
(4, 'san_juan_mto', 'view_analytics', '2026-06-22 16:20:39'),
(5, 'san_fernando_mto', 'view_dashboard', '2026-06-22 16:20:39'),
(6, 'san_fernando_mto', 'manage_tourist_spots', '2026-06-22 16:20:39'),
(7, 'san_fernando_mto', 'manage_fares', '2026-06-22 16:20:39'),
(8, 'san_fernando_mto', 'view_analytics', '2026-06-22 16:20:39'),
(9, 'bauang_mto', 'view_dashboard', '2026-06-22 16:20:39'),
(10, 'bauang_mto', 'manage_tourist_spots', '2026-06-22 16:20:39'),
(11, 'bauang_mto', 'manage_fares', '2026-06-22 16:20:39'),
(12, 'bauang_mto', 'view_analytics', '2026-06-22 16:20:39'),
(13, 'agoo_mto', 'view_dashboard', '2026-06-22 16:20:39'),
(14, 'agoo_mto', 'manage_tourist_spots', '2026-06-22 16:20:39'),
(15, 'agoo_mto', 'manage_fares', '2026-06-22 16:20:39'),
(16, 'agoo_mto', 'view_analytics', '2026-06-22 16:20:39'),
(17, 'luna_mto', 'view_dashboard', '2026-06-22 16:20:39'),
(18, 'luna_mto', 'manage_tourist_spots', '2026-06-22 16:20:39'),
(19, 'luna_mto', 'manage_fares', '2026-06-22 16:20:39'),
(20, 'luna_mto', 'view_analytics', '2026-06-22 16:20:39'),
(21, 'san_gabriel_mto', 'view_dashboard', '2026-06-22 16:20:39'),
(22, 'san_gabriel_mto', 'manage_tourist_spots', '2026-06-22 16:20:39'),
(23, 'san_gabriel_mto', 'manage_fares', '2026-06-22 16:20:39'),
(24, 'san_gabriel_mto', 'view_analytics', '2026-06-22 16:20:39'),
(25, 'balaoan_mto', 'view_dashboard', '2026-06-22 16:20:39'),
(26, 'balaoan_mto', 'manage_tourist_spots', '2026-06-22 16:20:39'),
(27, 'balaoan_mto', 'manage_fares', '2026-06-22 16:20:39'),
(28, 'balaoan_mto', 'view_analytics', '2026-06-22 16:20:39'),
(29, 'aringay_mto', 'view_dashboard', '2026-06-22 16:20:39'),
(30, 'aringay_mto', 'manage_tourist_spots', '2026-06-22 16:20:39'),
(31, 'aringay_mto', 'manage_fares', '2026-06-22 16:20:39'),
(32, 'aringay_mto', 'view_analytics', '2026-06-22 16:20:39'),
(33, 'rosario_mto', 'view_dashboard', '2026-06-22 16:20:39'),
(34, 'rosario_mto', 'manage_tourist_spots', '2026-06-22 16:20:39'),
(35, 'rosario_mto', 'manage_fares', '2026-06-22 16:20:39'),
(36, 'rosario_mto', 'view_analytics', '2026-06-22 16:20:39'),
(37, 'bacnotan_mto', 'view_dashboard', '2026-06-22 16:20:39'),
(38, 'bacnotan_mto', 'manage_tourist_spots', '2026-06-22 16:20:39'),
(39, 'bacnotan_mto', 'manage_fares', '2026-06-22 16:20:39'),
(40, 'bacnotan_mto', 'view_analytics', '2026-06-22 16:20:39'),
(41, 'naguilian_mto', 'view_dashboard', '2026-06-22 16:20:39'),
(42, 'naguilian_mto', 'manage_tourist_spots', '2026-06-22 16:20:39'),
(43, 'naguilian_mto', 'manage_fares', '2026-06-22 16:20:39'),
(44, 'naguilian_mto', 'view_analytics', '2026-06-22 16:20:39'),
(45, 'tubao_mto', 'view_dashboard', '2026-06-22 16:20:39'),
(46, 'tubao_mto', 'manage_tourist_spots', '2026-06-22 16:20:39'),
(47, 'tubao_mto', 'manage_fares', '2026-06-22 16:20:39'),
(48, 'tubao_mto', 'view_analytics', '2026-06-22 16:20:39'),
(49, 'pugo_mto', 'view_dashboard', '2026-06-22 16:20:39'),
(50, 'pugo_mto', 'manage_tourist_spots', '2026-06-22 16:20:39'),
(51, 'pugo_mto', 'manage_fares', '2026-06-22 16:20:39'),
(52, 'pugo_mto', 'view_analytics', '2026-06-22 16:20:39'),
(53, 'caba_mto', 'view_dashboard', '2026-06-22 16:20:39'),
(54, 'caba_mto', 'manage_tourist_spots', '2026-06-22 16:20:39'),
(55, 'caba_mto', 'manage_fares', '2026-06-22 16:20:39'),
(56, 'caba_mto', 'view_analytics', '2026-06-22 16:20:39'),
(57, 'santo_tomas_mto', 'view_dashboard', '2026-06-22 16:20:39'),
(58, 'santo_tomas_mto', 'manage_tourist_spots', '2026-06-22 16:20:39'),
(59, 'santo_tomas_mto', 'manage_fares', '2026-06-22 16:20:39'),
(60, 'santo_tomas_mto', 'view_analytics', '2026-06-22 16:20:39'),
(61, 'bangar_mto', 'view_dashboard', '2026-06-22 16:20:39'),
(62, 'bangar_mto', 'manage_tourist_spots', '2026-06-22 16:20:39'),
(63, 'bangar_mto', 'manage_fares', '2026-06-22 16:20:39'),
(64, 'bangar_mto', 'view_analytics', '2026-06-22 16:20:39'),
(65, 'burgos_mto', 'view_dashboard', '2026-06-22 16:20:39'),
(66, 'burgos_mto', 'manage_tourist_spots', '2026-06-22 16:20:39'),
(67, 'burgos_mto', 'manage_fares', '2026-06-22 16:20:39'),
(68, 'burgos_mto', 'view_analytics', '2026-06-22 16:20:39'),
(69, 'bagulin_mto', 'view_dashboard', '2026-06-22 16:20:39'),
(70, 'bagulin_mto', 'manage_tourist_spots', '2026-06-22 16:20:39'),
(71, 'bagulin_mto', 'manage_fares', '2026-06-22 16:20:39'),
(72, 'bagulin_mto', 'view_analytics', '2026-06-22 16:20:39'),
(73, 'santol_mto', 'view_dashboard', '2026-06-22 16:20:39'),
(74, 'santol_mto', 'manage_tourist_spots', '2026-06-22 16:20:39'),
(75, 'santol_mto', 'manage_fares', '2026-06-22 16:20:39'),
(76, 'santol_mto', 'view_analytics', '2026-06-22 16:20:39'),
(77, 'sudipen_mto', 'view_dashboard', '2026-06-22 16:20:39'),
(78, 'sudipen_mto', 'manage_tourist_spots', '2026-06-22 16:20:39'),
(79, 'sudipen_mto', 'manage_fares', '2026-06-22 16:20:39'),
(80, 'sudipen_mto', 'view_analytics', '2026-06-22 16:20:39');

-- --------------------------------------------------------

--
-- Table structure for table `system_status`
--

CREATE TABLE `system_status` (
  `id` int(11) NOT NULL,
  `service_name` varchar(100) NOT NULL,
  `status` enum('online','warning','offline') NOT NULL DEFAULT 'online',
  `uptime` varchar(50) NOT NULL DEFAULT '99.9%',
  `last_checked` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `system_status`
--

INSERT INTO `system_status` (`id`, `service_name`, `status`, `uptime`, `last_checked`) VALUES
(1, 'Database Service (MySQL)', 'online', '99.98%', '2026-06-22 16:20:39'),
(2, 'User Management Control', 'online', '99.95%', '2026-06-22 16:20:39'),
(3, 'Leaflet.js Mapping Integration', 'online', '100%', '2026-06-22 16:20:39'),
(4, 'Analytics Engine (YoY Reporting)', 'online', '99.90%', '2026-06-22 16:20:39');

-- --------------------------------------------------------

--
-- Table structure for table `tourist_spots`
--

CREATE TABLE `tourist_spots` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `municipality_id` int(11) NOT NULL,
  `latitude` decimal(10,6) DEFAULT NULL,
  `longitude` decimal(10,6) DEFAULT NULL,
  `opening_time` time DEFAULT NULL,
  `closing_time` time DEFAULT NULL,
  `is_maintenance` tinyint(1) NOT NULL DEFAULT 0,
  `category` enum('Beach','Mountain','Historical','Waterfalls','Adventure','Farm','Religious','Other') NOT NULL,
  `entrance_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `visits` int(11) DEFAULT 0,
  `rating` decimal(2,1) DEFAULT 4.0,
  `status` enum('pending','approved','rejected','under_review') NOT NULL DEFAULT 'pending',
  `classification_status` enum('EXIST','POTENTIAL','EMERGE') DEFAULT NULL,
  `photo_url` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tourist_spots`
--

INSERT INTO `tourist_spots` (`id`, `name`, `municipality_id`, `latitude`, `longitude`, `opening_time`, `closing_time`, `is_maintenance`, `category`, `entrance_fee`, `visits`, `rating`, `status`, `classification_status`, `photo_url`, `description`, `created_at`) VALUES
(1, 'Urbiztondo Surf Spot', 1, 16.664400, 120.320800, NULL, NULL, 0, 'Beach', 50.00, 15200, 4.8, 'pending', 'POTENTIAL', 'urbiztondo.jpg', 'The surfing capital of Northern Luzon. Perfect for beginners and professionals.', '2026-06-22 16:20:39'),
(2, 'Tangadan Falls Adventure', 6, 16.666700, 120.416700, NULL, NULL, 0, 'Waterfalls', 30.00, 8400, 4.7, 'pending', 'POTENTIAL', 'tangadan.jpg', 'A cold spring waterfall hidden in the valleys of San Gabriel.', '2026-06-22 16:20:39'),
(3, 'Luna Baluarte Watchtower', 5, 16.852500, 120.379700, NULL, NULL, 0, 'Historical', 0.00, 6200, 4.5, 'pending', 'POTENTIAL', 'baluarte.jpg', 'A restored Spanish-era watchtower along the pebble shores of Luna.', '2026-06-22 16:20:39'),
(4, 'Ma-Cho Temple Sanctuary', 2, 16.615600, 120.316700, NULL, NULL, 0, 'Religious', 0.00, 9100, 4.6, 'pending', 'POTENTIAL', 'macho.jpg', 'A grand Taoist temple overlooking the San Fernando bay.', '2026-06-22 16:20:39'),
(5, 'Lomboy Grape Farm Tour', 3, 16.529700, 120.330800, NULL, NULL, 0, 'Farm', 100.00, 7300, 4.4, 'pending', 'POTENTIAL', 'lomboy.jpg', 'The pioneer grape farm in Bauang. Experience grape picking.', '2026-06-22 16:20:39'),
(6, 'Pugo Adventure (Pugad)', 13, 16.326700, 120.482800, NULL, NULL, 0, 'Adventure', 250.00, 5400, 4.5, 'pending', 'POTENTIAL', 'pugad.jpg', 'Adrenaline-pumping ziplines, pool slides, ATV tours.', '2026-06-22 16:20:39'),
(7, 'Bakas ng Higante Rock', 18, 16.606700, 120.450300, NULL, NULL, 0, 'Mountain', 20.00, 1100, 4.2, 'pending', 'EMERGE', 'bakas.jpg', 'A mythical giant footprint embedded on a rock formation.', '2026-06-22 16:20:39'),
(9, 'Immuki Island Lagoon', 7, 16.824400, 120.400300, NULL, NULL, 0, 'Beach', 50.00, 6900, 4.6, 'pending', 'POTENTIAL', 'immuki.jpg', 'Crystal-clear lagoons surrounded by dead coral walls.', '2026-06-22 16:20:39'),
(10, 'Bangar Loom Weaving Center', 16, 16.896700, 120.418100, NULL, NULL, 0, 'Historical', 0.00, 2100, 4.3, 'pending', 'POTENTIAL', 'weaving.jpg', 'Watch local craftsmen weave traditional Abel Iloco fabrics.', '2026-06-22 16:20:39'),
(11, 'Aringay Centenary Bridge', 8, 16.395600, 120.354700, NULL, NULL, 0, 'Historical', 0.00, 1800, 4.0, 'pending', 'EMERGE', 'bridge.jpg', 'An abandoned rail-bridge from the Spanish era.', '2026-06-22 16:20:39'),
(12, 'Mt. Bulik Ridge Peak', 17, 16.520800, 120.485000, NULL, NULL, 0, 'Mountain', 50.00, 950, 4.2, 'pending', 'EMERGE', 'bulik.jpg', 'A challenging hike with scenic Benguet border views.', '2026-06-22 16:20:39'),
(13, 'Occalong Waterfalls', 19, 16.766700, 120.466700, NULL, NULL, 0, 'Waterfalls', 20.00, 1300, 4.4, 'pending', 'POTENTIAL', 'occalong.jpg', 'A serene cascade with deep swimming pools.', '2026-06-22 16:20:39'),
(14, 'Damortis Protected Landscape', 8, 16.395600, 120.354700, NULL, NULL, 0, 'Mountain', 15.00, 2200, 4.1, 'pending', 'EMERGE', 'damortis.jpg', 'Coastal mangrove and bird sanctuary in Aringay.', '2026-06-22 16:20:39'),
(15, 'Caba Beach Cove', 14, 16.429400, 120.350300, NULL, NULL, 0, 'Beach', 25.00, 3100, 4.3, 'pending', 'POTENTIAL', 'caba_cove.jpg', 'Hidden cove with calm waters ideal for family outings.', '2026-06-22 16:20:39'),
(16, 'Pebble Beach of Luna', 5, 16.852500, 120.379700, NULL, NULL, 0, 'Beach', 0.00, 14300, 4.7, 'approved', 'EXIST', 'pebble.jpg', 'A unique shoreline made entirely of multi-colored pebbles.', '2026-06-22 16:20:39'),
(17, 'Bauang Beach Resorts', 3, 16.529700, 120.330800, NULL, NULL, 0, 'Beach', 0.00, 12500, 4.4, 'approved', 'EXIST', 'resorts.jpg', 'Famous stretch of sandy beach lined with historical resorts.', '2026-06-22 16:20:39'),
(18, 'Tapuakan River', 13, 16.326700, 120.482800, NULL, NULL, 0, 'Adventure', 30.00, 8800, 4.6, 'approved', 'EXIST', 'tapuakan.jpg', 'Known as the cleanest inland river in the region.', '2026-06-22 16:20:39'),
(20, 'Red Clay Pagdamilian', 1, 16.664400, 120.320800, NULL, NULL, 0, 'Farm', 150.00, 9800, 4.5, 'approved', 'EXIST', 'pottery.jpg', 'Interactive pottery studio. Craft your own souvenirs.', '2026-06-22 16:20:39'),
(44, 'grapes', 4, 16.321700, 120.368300, NULL, NULL, 0, 'Beach', 20.00, 0, 4.0, 'approved', 'EXIST', '/Gaw-at-GO-System/backendWebsite/uploads/tourist_spots/spot_6a3d183c0b25f6.27343421.jpg', 'asasas', '2026-06-25 12:00:18'),
(45, 'immuki island', 4, 16.321700, 120.368300, NULL, NULL, 0, 'Beach', 9.97, 0, 4.0, 'approved', 'EXIST', '/Gaw-at-GO-System/backendWebsite/uploads/tourist_spots/spot_6a3d185ae838c9.49081524.jpg', 'dsd', '2026-06-25 12:00:52'),
(46, 'tangadan', 8, 16.395600, 120.354700, NULL, NULL, 0, 'Adventure', 200.00, 0, 4.0, 'approved', 'EMERGE', '/Gaw-at-GO-System/backendWebsite/uploads/tourist-spots/spot_6a3ddb3bd03795.45468284.jpg', 'ccxcx', '2026-06-26 01:52:20'),
(50, 'park', 4, 16.321700, 120.368300, NULL, NULL, 0, 'Farm', 200.00, 0, 4.0, 'approved', 'EMERGE', '/Gaw-at-GO-System/backendWebsite/uploads/tourist_spots/spot_6a3de3a584a2b9.95724813.png', 'cccc', '2026-06-26 02:28:11'),
(51, 'dfdfd', 4, 16.321700, 120.368300, NULL, NULL, 0, 'Beach', 20.00, 0, 4.0, 'approved', 'EMERGE', '/Gaw-at-GO-System/backendWebsite/uploads/tourist_spots/spot_6a3dea81dbf310.69860936.jpg', 'sdds', '2026-06-26 02:57:23'),
(52, 'PASSS', 4, 16.321700, 120.368300, NULL, NULL, 0, 'Beach', 12.00, 0, 4.0, 'approved', 'EXIST', '/Gaw-at-GO-System/backendWebsite/uploads/tourist_spots/spot_6a3dedc01910d7.12279173.jpg', 'fff', '2026-06-26 03:11:07'),
(53, 'erd', 11, 16.536700, 120.395300, NULL, NULL, 0, 'Religious', 200.00, 0, 4.0, 'approved', 'EXIST', '/Gaw-at-GO-System/backendWebsite/uploads/tourist-spots/spot_6a3df261ae3636.61049041.png', 'sadsds', '2026-06-26 03:30:58'),
(54, 'fff', 6, 16.666700, 120.416700, '00:12:00', '12:12:00', 0, 'Religious', 121.98, 0, 4.0, 'approved', 'EXIST', '/Gaw-at-GO-System/backendWebsite/uploads/tourist-spots/spot_6a3df5c0bdf0b9.94389287.jpg', 'dsdsd', '2026-06-26 03:45:30'),
(55, 'ZOOM', 4, 16.321700, 120.368300, '02:12:00', '12:01:00', 0, 'Other', 200.00, 0, 4.0, 'approved', 'EXIST', '/Gaw-at-GO-System/backendWebsite/uploads/tourist_spots/spot_6a3df641d3d366.14468797.png', 'vffff', '2026-06-26 03:47:32'),
(56, 'eg', 1, 16.540188, 120.237014, '00:12:00', '23:11:00', 0, 'Other', 10.00, 0, 4.0, 'approved', 'EXIST', '/Gaw-at-GO-System/backendWebsite/uploads/tourist-spots/spot_6a40ed65a2e149.42558021.png', 'zxzx', '2026-06-28 09:46:44');

-- --------------------------------------------------------

--
-- Table structure for table `tourist_spot_audit`
--

CREATE TABLE `tourist_spot_audit` (
  `id` int(11) NOT NULL,
  `spot_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `old_status` enum('pending','approved','rejected','under_review') DEFAULT NULL,
  `new_status` enum('pending','approved','rejected','under_review') DEFAULT NULL,
  `changes` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tourist_spot_audit`
--

INSERT INTO `tourist_spot_audit` (`id`, `spot_id`, `user_id`, `action`, `old_status`, `new_status`, `changes`, `ip_address`, `user_agent`, `created_at`) VALUES
(49, 44, 9, 'created', NULL, NULL, '{\"name\":\"grapes\",\"category\":\"Beach\",\"classification_status\":\"EXIST\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-25 12:00:18'),
(50, 45, 9, 'created', NULL, NULL, '{\"name\":\"immuki island\",\"category\":\"Beach\",\"classification_status\":\"EXIST\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-25 12:00:52'),
(57, 50, 9, 'created', NULL, NULL, '{\"name\":\"park\",\"category\":\"Farm\",\"classification_status\":\"EMERGE\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-26 02:28:11'),
(58, 51, 9, 'created', NULL, NULL, '{\"name\":\"dfdfd\",\"category\":\"Beach\",\"classification_status\":\"EMERGE\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-26 02:57:23'),
(59, 52, 9, 'created', NULL, NULL, '{\"name\":\"hhh\",\"category\":\"Beach\",\"classification_status\":\"EXIST\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-26 03:11:07'),
(60, 55, 9, 'created', NULL, NULL, '{\"name\":\"fdf\",\"category\":\"Other\",\"classification_status\":\"EXIST\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-26 03:47:32'),
(61, 52, 9, 'updated', NULL, NULL, '{\"old\":{\"name\":\"hhh\",\"category\":\"Beach\",\"entrance_fee\":\"12.00\",\"classification_status\":\"EXIST\"},\"new\":{\"name\":\"PASSS\",\"category\":\"Beach\",\"entrance_fee\":12,\"classification_status\":\"EXIST\"}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-26 03:51:11'),
(62, 45, 9, 'updated', NULL, NULL, '{\"old\":{\"name\":\"immuki island\",\"category\":\"Beach\",\"entrance_fee\":\"9.97\",\"classification_status\":\"EXIST\"},\"new\":{\"name\":\"immuki island\",\"category\":\"Beach\",\"entrance_fee\":9.97,\"classification_status\":\"EXIST\"}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-26 04:08:51');

-- --------------------------------------------------------

--
-- Table structure for table `tourist_spot_images`
--

CREATE TABLE `tourist_spot_images` (
  `id` int(11) NOT NULL,
  `spot_id` int(11) NOT NULL,
  `photo_url` varchar(255) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tourist_spot_images`
--

INSERT INTO `tourist_spot_images` (`id`, `spot_id`, `photo_url`, `is_primary`, `sort_order`, `created_at`) VALUES
(1, 1, 'urbiztondo.jpg', 1, 0, '2026-06-22 16:20:39'),
(2, 2, 'tangadan.jpg', 1, 0, '2026-06-22 16:20:39'),
(3, 3, 'baluarte.jpg', 1, 0, '2026-06-22 16:20:39'),
(4, 4, 'macho.jpg', 1, 0, '2026-06-22 16:20:39'),
(5, 5, 'lomboy.jpg', 1, 0, '2026-06-22 16:20:39'),
(6, 6, 'pugad.jpg', 1, 0, '2026-06-22 16:20:39'),
(7, 7, 'bakas.jpg', 1, 0, '2026-06-22 16:20:39'),
(9, 9, 'immuki.jpg', 1, 0, '2026-06-22 16:20:39'),
(10, 10, 'weaving.jpg', 1, 0, '2026-06-22 16:20:39'),
(11, 11, 'bridge.jpg', 1, 0, '2026-06-22 16:20:39'),
(12, 12, 'bulik.jpg', 1, 0, '2026-06-22 16:20:39'),
(13, 13, 'occalong.jpg', 1, 0, '2026-06-22 16:20:39'),
(14, 14, 'damortis.jpg', 1, 0, '2026-06-22 16:20:39'),
(15, 15, 'caba_cove.jpg', 1, 0, '2026-06-22 16:20:39'),
(16, 16, 'pebble.jpg', 1, 0, '2026-06-22 16:20:39'),
(17, 17, 'resorts.jpg', 1, 0, '2026-06-22 16:20:39'),
(18, 18, 'tapuakan.jpg', 1, 0, '2026-06-22 16:20:39'),
(20, 20, 'pottery.jpg', 1, 0, '2026-06-22 16:20:39'),
(45, 44, '/Gaw-at-GO-System/backendWebsite/uploads/tourist_spots/spot_6a3d183c0b25f6.27343421.jpg', 1, 0, '2026-06-25 12:00:18'),
(47, 46, '/Gaw-at-GO-System/backendWebsite/uploads/tourist-spots/spot_6a3ddb3bd03795.45468284.jpg', 1, 0, '2026-06-26 01:52:20'),
(51, 50, '/Gaw-at-GO-System/backendWebsite/uploads/tourist_spots/spot_6a3de3a584a2b9.95724813.png', 1, 0, '2026-06-26 02:28:11'),
(52, 51, '/Gaw-at-GO-System/backendWebsite/uploads/tourist_spots/spot_6a3dea81dbf310.69860936.jpg', 1, 0, '2026-06-26 02:57:23'),
(54, 53, '/Gaw-at-GO-System/backendWebsite/uploads/tourist-spots/spot_6a3df261ae3636.61049041.png', 1, 0, '2026-06-26 03:30:58'),
(55, 54, '/Gaw-at-GO-System/backendWebsite/uploads/tourist-spots/spot_6a3df5c0bdf0b9.94389287.jpg', 1, 0, '2026-06-26 03:45:30'),
(57, 52, '/Gaw-at-GO-System/backendWebsite/uploads/tourist_spots/spot_6a3dedc01910d7.12279173.jpg', 1, 0, '2026-06-26 03:51:11'),
(58, 55, '/Gaw-at-GO-System/backendWebsite/uploads/tourist_spots/spot_6a3df641d3d366.14468797.png', 1, 0, '2026-06-26 03:58:20'),
(59, 45, '/Gaw-at-GO-System/backendWebsite/uploads/tourist_spots/spot_6a3d185ae838c9.49081524.jpg', 1, 0, '2026-06-26 04:08:51'),
(60, 56, '/Gaw-at-GO-System/backendWebsite/uploads/tourist-spots/spot_6a40ed65a2e149.42558021.png', 1, 0, '2026-06-28 09:46:44');

-- --------------------------------------------------------

--
-- Table structure for table `transportation_routes`
--

CREATE TABLE `transportation_routes` (
  `id` int(11) NOT NULL,
  `origin` varchar(100) NOT NULL,
  `destination` varchar(100) NOT NULL,
  `vehicle_type` varchar(50) NOT NULL,
  `fare_amount` decimal(8,2) NOT NULL,
  `effective_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('pitco','lupto','municipal','tourist','san_juan_mto','san_fernando_mto','bauang_mto','agoo_mto','luna_mto','san_gabriel_mto','balaoan_mto','aringay_mto','rosario_mto','bacnotan_mto','naguilian_mto','tubao_mto','pugo_mto','caba_mto','santo_tomas_mto','bangar_mto','burgos_mto','bagulin_mto','santol_mto','sudipen_mto') NOT NULL,
  `municipality_id` int(11) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `last_activity` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `municipality_id`, `status`, `last_activity`, `created_at`) VALUES
(1, 'PICTO Super Admin', 'picto@gawat.com', '$2y$12$gvo1fahuuH52ZKz2WN1Zz.6Azwl0XjNibXLQtnK4Lp1PnpooD5Cp6', 'pitco', NULL, 'active', '2026-06-28 14:37:47', '2026-06-22 16:20:39'),
(2, 'LUPTO Provincial Admin', 'lupto@gawat.com', '$2y$12$LipYQlQB9cYplliu1m27i.qqYGYwU1FzbfLHRMEa/WM3jYLkK/xI6', 'lupto', NULL, 'active', '2026-06-28 15:35:36', '2026-06-22 16:20:39'),
(3, 'Juan dela Cruz (Tourist)', 'tourist@gawat.com', '$2y$12$4WG.Q33ajZ8yOGXKQ0pGr.73vhpQWAbzHYd.Bu/m7Qsm6HX1qCZf.', 'tourist', NULL, 'active', '2026-06-21 16:20:39', '2026-06-22 16:20:39'),
(4, 'Maria Santos', 'maria@gawat.com', '$2y$12$4WG.Q33ajZ8yOGXKQ0pGr.73vhpQWAbzHYd.Bu/m7Qsm6HX1qCZf.', 'tourist', NULL, 'active', '2026-06-22 16:20:39', '2026-06-22 16:20:39'),
(5, 'John Doe', 'johndoe@gawat.com', '$2y$12$4WG.Q33ajZ8yOGXKQ0pGr.73vhpQWAbzHYd.Bu/m7Qsm6HX1qCZf.', 'tourist', NULL, 'active', '2026-06-22 12:20:39', '2026-06-22 16:20:39'),
(6, 'San Juan MTO Officer', 'san_juan@gawat.com', '$2y$12$anT.lL.4PKBlXntIpv2giusP71SI671bvJFzP3EZPGK/TkNFinhz.', 'san_juan_mto', 1, 'active', '2026-06-28 15:14:45', '2026-06-22 16:20:39'),
(7, 'San Fernando City MTO Officer', 'san_fernando@gawat.com', '$2y$12$Uave/FiPv7TkM1P08Fin9OOeAwzjnuUt7Kne3.6IpbZppgiS9N98C', 'san_fernando_mto', 2, 'active', '2026-06-28 15:55:35', '2026-06-22 16:20:39'),
(8, 'Bauang MTO Officer', 'bauang@gawat.com', '$2y$12$foxCafPCwElQ.jqpL/Dof.7CWx9qobmuLjo2CH6N8iusaErzRKPC6', 'bauang_mto', 3, 'active', '2026-06-28 15:55:35', '2026-06-22 16:20:39'),
(9, 'Agoo MTO Officer', 'agoo@gawat.com', '$2y$12$o6BCcXh4XgNUvXq.wReVeunJA2E0V9kRzZzlqf7tvA964Yjx6t9TK', 'agoo_mto', 4, 'active', '2026-06-28 15:55:35', '2026-06-22 16:20:39'),
(10, 'Luna MTO Officer', 'luna@gawat.com', '$2y$12$h8p3DqPoFPNWrlRJgwCRgOzkitkV5bonG8kD0.mMCb6L3KmXtGBAi', 'luna_mto', 5, 'active', '2026-06-28 15:55:36', '2026-06-22 16:20:39'),
(11, 'San Gabriel MTO Officer', 'san_gabriel@gawat.com', '$2y$12$giCFhSTFQW2uAVBNchAI/OzaXhuYlB.o4CAMKCgwFAIYNFzuPdviu', 'san_gabriel_mto', 6, 'active', '2026-06-28 15:55:36', '2026-06-22 16:20:39'),
(12, 'Balaoan MTO Officer', 'balaoan@gawat.com', '$2y$12$3N2KkIoSyFDcXzpEBFIXj.SiH9TSeZsfq5vawkFDCyivM6bYTIJri', 'balaoan_mto', 7, 'active', '2026-06-28 15:55:37', '2026-06-22 16:20:39'),
(13, 'Aringay MTO Officer', 'aringay@gawat.com', '$2y$12$smxUoOk8IRqeX9Hz9W3Vx.WwjQXj1P9/zbgeOcIMAjbFfL8Xb7MVC', 'aringay_mto', 8, 'active', '2026-06-28 15:55:37', '2026-06-22 16:20:39'),
(14, 'Rosario MTO Officer', 'rosario@gawat.com', '$2y$12$IPHcLsmNo1pKFzV3V7uUKuqKBgO3o2o0waO9ycE25uUXU9AjSO0Yi', 'rosario_mto', 9, 'active', '2026-06-28 15:55:38', '2026-06-22 16:20:39'),
(15, 'Bacnotan MTO Officer', 'bacnotan@gawat.com', '$2y$12$cPb/tyFgKCL.BwMEOrFZTur3OZ0ybXvkzgal7AEIxzFCqD99WIGB.', 'bacnotan_mto', 10, 'active', '2026-06-28 15:55:38', '2026-06-22 16:20:39'),
(16, 'Naguilian MTO Officer', 'naguilian@gawat.com', '$2y$12$T/2cQhFLTrko3/oGsISPp.m8S81HPWrVkQI9rYfz/7t5X9skBYIyu', 'naguilian_mto', 11, 'active', '2026-06-28 15:55:38', '2026-06-22 16:20:39'),
(17, 'Tubao MTO Officer', 'tubao@gawat.com', '$2y$12$pd5dHZSPGWLB4e.aMX9a7u8gmSQUY/VZ5OM0cl1mqO5HqPd/XU8SW', 'tubao_mto', 12, 'active', '2026-06-28 15:55:39', '2026-06-22 16:20:39'),
(18, 'Pugo MTO Officer', 'pugo@gawat.com', '$2y$12$1Y/oe4XIv1zFc5phgcItyuv0myn6Iz7SGqxEz5oLB/wHZC4F/KJzq', 'pugo_mto', 13, 'active', '2026-06-28 15:55:39', '2026-06-22 16:20:39'),
(19, 'Caba MTO Officer', 'caba@gawat.com', '$2y$12$VfBtOrw8Ttfbyv/3ohWDUONMFiihuIidB0ep9idh0gfWla9DN1SYa', 'caba_mto', 14, 'active', '2026-06-28 15:55:40', '2026-06-22 16:20:39'),
(20, 'Santo Tomas MTO Officer', 'santo_tomas@gawat.com', '$2y$12$PZnWQVf3Woulg0dTmUBg9.4sAQAoyOq.hbcMtzvjIHH4kZMmPIBje', 'santo_tomas_mto', 15, 'active', '2026-06-28 15:55:40', '2026-06-22 16:20:39'),
(21, 'Bangar MTO Officer', 'bangar@gawat.com', '$2y$12$7TU.rLLo0gT5jVOgqHi6X.sqRKAuuKrWFXMCyPN426Y97EcMWk./G', 'bangar_mto', 16, 'active', '2026-06-28 15:55:41', '2026-06-22 16:20:39'),
(22, 'Burgos MTO Officer', 'burgos@gawat.com', '$2y$12$IWuDRAZx2rRtOHw3mARWBOkgrx.me9G5c/XHttCldErUoVwws0cE.', 'burgos_mto', 17, 'active', '2026-06-28 15:55:41', '2026-06-22 16:20:39'),
(23, 'Bagulin MTO Officer', 'bagulin@gawat.com', '$2y$12$Fk9wSL4c1gPC345P8oPYL.CENg2T71vdSA8z/So90EnjeOJIvUE4W', 'bagulin_mto', 18, 'active', '2026-06-28 15:55:41', '2026-06-22 16:20:39'),
(24, 'Santol MTO Officer', 'santol@gawat.com', '$2y$12$TPFVev9N6yUhxLj5DCKA8OsPGTe4eznW6iSfoZwlYUO9vs8.d32oG', 'santol_mto', 19, 'active', '2026-06-28 15:55:42', '2026-06-22 16:20:39'),
(25, 'Sudipen MTO Officer', 'sudipen@gawat.com', '$2y$12$/w8dg5fHUM1bzlwnwgMePOoCwA0UkfAS5/DEcBcRUvy8jJlL9cTbO', 'sudipen_mto', 20, 'active', '2026-06-28 15:55:42', '2026-06-22 16:20:39');

-- --------------------------------------------------------

--
-- Table structure for table `user_points`
--

CREATE TABLE `user_points` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_points` int(11) NOT NULL DEFAULT 0,
  `completed_activities` int(11) NOT NULL DEFAULT 0,
  `last_activity_date` timestamp NULL DEFAULT NULL,
  `points_since` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'when the user first earned any points (used for tie-breaking)',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_points`
--

INSERT INTO `user_points` (`id`, `user_id`, `total_points`, `completed_activities`, `last_activity_date`, `points_since`, `updated_at`) VALUES
(1, 3, 4850, 23, '2026-06-25 02:30:00', '2026-05-01 00:00:00', '2026-06-26 13:27:06'),
(2, 4, 4850, 19, '2026-06-24 06:15:00', '2026-05-03 01:30:00', '2026-06-26 13:27:06'),
(3, 5, 3200, 15, '2026-06-23 03:00:00', '2026-05-05 02:00:00', '2026-06-26 13:27:06');

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `validation_errors`
--

CREATE TABLE `validation_errors` (
  `id` int(11) NOT NULL,
  `fare_upload_id` int(11) NOT NULL,
  `row_number` int(11) DEFAULT NULL,
  `field_name` varchar(100) DEFAULT NULL,
  `error_type` varchar(100) NOT NULL,
  `error_message` text NOT NULL,
  `invalid_value` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `alerts`
--
ALTER TABLE `alerts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `analytics`
--
ALTER TABLE `analytics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `municipality_id` (`municipality_id`);

--
-- Indexes for table `fare_guides`
--
ALTER TABLE `fare_guides`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `fare_matrices`
--
ALTER TABLE `fare_matrices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_distance_per_guide` (`fare_guide_id`,`distance_km`);

--
-- Indexes for table `fare_uploads`
--
ALTER TABLE `fare_uploads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uploaded_by` (`uploaded_by`);

--
-- Indexes for table `import_logs`
--
ALTER TABLE `import_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fare_upload_id` (`fare_upload_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `municipalities`
--
ALTER TABLE `municipalities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_role_permission` (`role`,`permission`);

--
-- Indexes for table `system_status`
--
ALTER TABLE `system_status`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `service_name` (`service_name`);

--
-- Indexes for table `tourist_spots`
--
ALTER TABLE `tourist_spots`
  ADD PRIMARY KEY (`id`),
  ADD KEY `municipality_id` (`municipality_id`);

--
-- Indexes for table `tourist_spot_audit`
--
ALTER TABLE `tourist_spot_audit`
  ADD PRIMARY KEY (`id`),
  ADD KEY `spot_id` (`spot_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tourist_spot_images`
--
ALTER TABLE `tourist_spot_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_spot_id` (`spot_id`);

--
-- Indexes for table `transportation_routes`
--
ALTER TABLE `transportation_routes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `municipality_id` (`municipality_id`);

--
-- Indexes for table `user_points`
--
ALTER TABLE `user_points`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_user_id` (`user_id`),
  ADD KEY `idx_total_points` (`total_points`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `validation_errors`
--
ALTER TABLE `validation_errors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fare_upload_id` (`fare_upload_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `alerts`
--
ALTER TABLE `alerts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `analytics`
--
ALTER TABLE `analytics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `fare_guides`
--
ALTER TABLE `fare_guides`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `fare_matrices`
--
ALTER TABLE `fare_matrices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT for table `fare_uploads`
--
ALTER TABLE `fare_uploads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `import_logs`
--
ALTER TABLE `import_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `municipalities`
--
ALTER TABLE `municipalities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT for table `system_status`
--
ALTER TABLE `system_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tourist_spots`
--
ALTER TABLE `tourist_spots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `tourist_spot_audit`
--
ALTER TABLE `tourist_spot_audit`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `tourist_spot_images`
--
ALTER TABLE `tourist_spot_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `transportation_routes`
--
ALTER TABLE `transportation_routes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `user_points`
--
ALTER TABLE `user_points`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `validation_errors`
--
ALTER TABLE `validation_errors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `analytics`
--
ALTER TABLE `analytics`
  ADD CONSTRAINT `analytics_ibfk_1` FOREIGN KEY (`municipality_id`) REFERENCES `municipalities` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `fare_guides`
--
ALTER TABLE `fare_guides`
  ADD CONSTRAINT `fare_guides_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `fare_matrices`
--
ALTER TABLE `fare_matrices`
  ADD CONSTRAINT `fare_matrices_ibfk_1` FOREIGN KEY (`fare_guide_id`) REFERENCES `fare_guides` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `fare_uploads`
--
ALTER TABLE `fare_uploads`
  ADD CONSTRAINT `fare_uploads_ibfk_1` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `import_logs`
--
ALTER TABLE `import_logs`
  ADD CONSTRAINT `import_logs_ibfk_1` FOREIGN KEY (`fare_upload_id`) REFERENCES `fare_uploads` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tourist_spots`
--
ALTER TABLE `tourist_spots`
  ADD CONSTRAINT `tourist_spots_ibfk_1` FOREIGN KEY (`municipality_id`) REFERENCES `municipalities` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tourist_spot_audit`
--
ALTER TABLE `tourist_spot_audit`
  ADD CONSTRAINT `tourist_spot_audit_ibfk_1` FOREIGN KEY (`spot_id`) REFERENCES `tourist_spots` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tourist_spot_audit_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tourist_spot_images`
--
ALTER TABLE `tourist_spot_images`
  ADD CONSTRAINT `tourist_spot_images_ibfk_1` FOREIGN KEY (`spot_id`) REFERENCES `tourist_spots` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`municipality_id`) REFERENCES `municipalities` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_points`
--
ALTER TABLE `user_points`
  ADD CONSTRAINT `fk_up_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `validation_errors`
--
ALTER TABLE `validation_errors`
  ADD CONSTRAINT `validation_errors_ibfk_1` FOREIGN KEY (`fare_upload_id`) REFERENCES `fare_uploads` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;