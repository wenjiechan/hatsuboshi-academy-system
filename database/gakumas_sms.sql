-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 08, 2026 at 11:07 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gakumas_sms`
--

-- --------------------------------------------------------

--
-- Table structure for table `daily_student_stats`
--

CREATE TABLE `daily_student_stats` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `stat_date` date NOT NULL,
  `vocal` int(11) NOT NULL,
  `dance` int(11) NOT NULL,
  `visual` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `daily_student_stats`
--

INSERT INTO `daily_student_stats` (`id`, `student_id`, `stat_date`, `vocal`, `dance`, `visual`, `created_at`, `updated_at`) VALUES
(13, 1, '2026-05-28', 75, 75, 80, '2026-05-28 17:34:26', '2026-05-28 17:34:26'),
(14, 1, '2026-06-03', 75, 75, 80, '2026-06-03 08:06:06', '2026-06-03 08:06:06'),
(15, 1, '2026-06-03', 75, 75, 80, '2026-06-03 12:04:21', '2026-06-03 12:04:21'),
(16, 1, '2026-06-03', 75, 75, 80, '2026-06-03 12:06:39', '2026-06-03 12:06:39'),
(17, 1, '2026-06-03', 75, 75, 80, '2026-06-03 12:06:47', '2026-06-03 12:06:47'),
(36, 2, '2026-06-01', 75, 65, 55, '2026-06-01 22:54:32', '2026-06-01 22:54:32'),
(37, 2, '2026-06-03', 75, 65, 55, '2026-06-03 07:40:36', '2026-06-03 07:40:36'),
(38, 2, '2026-06-03', 75, 65, 55, '2026-06-03 08:04:28', '2026-06-03 08:04:28'),
(39, 2, '2026-06-03', 75, 65, 55, '2026-06-03 08:05:44', '2026-06-03 08:05:44'),
(40, 2, '2026-06-03', 75, 65, 55, '2026-06-03 08:05:49', '2026-06-03 08:05:49'),
(41, 2, '2026-06-03', 75, 65, 55, '2026-06-03 08:05:50', '2026-06-03 08:05:50'),
(42, 2, '2026-06-03', 75, 65, 55, '2026-06-03 08:06:26', '2026-06-03 08:06:26'),
(43, 2, '2026-06-03', 75, 65, 55, '2026-06-03 11:51:01', '2026-06-03 11:51:01'),
(44, 2, '2026-06-03', 75, 65, 55, '2026-06-03 11:53:36', '2026-06-03 11:53:36'),
(45, 2, '2026-06-03', 75, 65, 55, '2026-06-03 11:53:38', '2026-06-03 11:53:38'),
(46, 2, '2026-06-03', 75, 65, 55, '2026-06-03 11:53:39', '2026-06-03 11:53:39'),
(47, 2, '2026-06-03', 75, 65, 55, '2026-06-03 11:54:27', '2026-06-03 11:54:27'),
(48, 2, '2026-06-03', 75, 65, 55, '2026-06-03 11:56:59', '2026-06-03 11:56:59'),
(49, 2, '2026-06-03', 75, 65, 55, '2026-06-03 11:57:25', '2026-06-03 11:57:25'),
(50, 2, '2026-06-03', 75, 65, 55, '2026-06-03 12:01:40', '2026-06-03 12:01:40'),
(51, 2, '2026-06-03', 75, 65, 55, '2026-06-03 12:01:43', '2026-06-03 12:01:43'),
(52, 2, '2026-06-03', 75, 65, 55, '2026-06-03 12:01:44', '2026-06-03 12:01:44'),
(53, 2, '2026-06-03', 75, 65, 55, '2026-06-03 12:01:46', '2026-06-03 12:01:46'),
(54, 2, '2026-06-03', 75, 65, 55, '2026-06-03 12:01:48', '2026-06-03 12:01:48'),
(55, 2, '2026-06-03', 75, 65, 55, '2026-06-03 13:06:07', '2026-06-03 13:06:07'),
(56, 2, '2026-06-03', 75, 65, 55, '2026-06-03 15:14:01', '2026-06-03 15:14:01'),
(57, 2, '2026-06-03', 75, 65, 75, '2026-06-03 15:14:24', '2026-06-03 15:14:24'),
(58, 2, '2026-06-03', 75, 65, 75, '2026-06-03 15:15:03', '2026-06-03 15:15:03'),
(59, 2, '2026-06-03', 65, 55, 75, '2026-06-03 15:15:22', '2026-06-03 15:15:22'),
(60, 2, '2026-06-03', 65, 55, 75, '2026-06-03 15:15:34', '2026-06-03 15:15:34'),
(61, 2, '2026-06-03', 65, 55, 75, '2026-06-03 15:15:40', '2026-06-03 15:15:40'),
(62, 2, '2026-06-03', 75, 65, 55, '2026-06-03 15:15:59', '2026-06-03 15:15:59'),
(63, 2, '2026-06-03', 75, 65, 55, '2026-06-03 15:20:55', '2026-06-03 15:20:55'),
(64, 2, '2026-06-03', 85, 75, 65, '2026-06-03 15:21:33', '2026-06-03 15:21:33'),
(65, 2, '2026-06-03', 85, 75, 65, '2026-06-03 15:22:58', '2026-06-03 15:22:58'),
(66, 2, '2026-06-03', 85, 75, 65, '2026-06-03 15:26:48', '2026-06-03 15:26:48'),
(67, 2, '2026-06-03', 85, 75, 65, '2026-06-03 15:27:40', '2026-06-03 15:27:40'),
(68, 2, '2026-06-03', 85, 75, 65, '2026-06-03 15:27:44', '2026-06-03 15:27:44'),
(69, 3, '2026-05-28', 65, 55, 75, '2026-05-28 15:58:24', '2026-05-28 15:58:24'),
(70, 3, '2026-05-28', 65, 55, 75, '2026-05-28 15:59:14', '2026-05-28 15:59:14'),
(71, 3, '2026-05-28', 65, 55, 75, '2026-05-28 15:59:16', '2026-05-28 15:59:16'),
(72, 3, '2026-05-28', 65, 55, 75, '2026-05-28 15:59:17', '2026-05-28 15:59:17'),
(73, 3, '2026-05-28', 65, 55, 75, '2026-05-28 16:00:17', '2026-05-28 16:00:17'),
(81, 4, '2026-05-31', 55, 55, 65, '2026-05-31 20:57:17', '2026-05-31 20:57:17'),
(82, 4, '2026-06-03', 55, 55, 65, '2026-06-03 13:07:09', '2026-06-03 13:07:09'),
(98, 5, '2026-06-01', 65, 55, 65, '2026-06-01 19:44:53', '2026-06-01 19:44:53'),
(99, 5, '2026-06-03', 65, 55, 65, '2026-06-03 12:08:43', '2026-06-03 12:08:43'),
(104, 6, '2026-06-01', 55, 55, 60, '2026-06-01 23:24:22', '2026-06-01 23:24:22'),
(105, 6, '2026-06-03', 55, 55, 60, '2026-06-03 12:09:30', '2026-06-03 12:09:30'),
(106, 6, '2026-06-03', 55, 55, 60, '2026-06-03 13:06:48', '2026-06-03 13:06:48'),
(107, 7, '2026-06-01', 55, 55, 60, '2026-06-01 23:24:44', '2026-06-01 23:24:44'),
(108, 7, '2026-06-03', 55, 55, 60, '2026-06-03 12:10:17', '2026-06-03 12:10:17'),
(109, 7, '2026-06-03', 55, 55, 60, '2026-06-03 13:06:31', '2026-06-03 13:06:31'),
(110, 7, '2026-06-03', 55, 55, 60, '2026-06-03 13:06:38', '2026-06-03 13:06:38'),
(111, 8, '2026-06-01', 50, 60, 60, '2026-06-01 23:36:09', '2026-06-01 23:36:09'),
(112, 8, '2026-06-02', 50, 60, 60, '2026-06-02 09:16:05', '2026-06-02 09:16:05'),
(113, 8, '2026-06-03', 50, 60, 60, '2026-06-03 13:07:24', '2026-06-03 13:07:24'),
(114, 9, '2026-06-01', 65, 70, 80, '2026-06-01 23:35:39', '2026-06-01 23:35:39'),
(115, 9, '2026-06-02', 65, 70, 80, '2026-06-02 09:16:31', '2026-06-02 09:16:31'),
(116, 9, '2026-06-03', 65, 70, 80, '2026-06-03 12:03:23', '2026-06-03 12:03:23'),
(117, 9, '2026-06-03', 65, 70, 80, '2026-06-03 12:03:49', '2026-06-03 12:03:49'),
(118, 9, '2026-06-03', 65, 70, 80, '2026-06-03 12:03:51', '2026-06-03 12:03:51'),
(119, 9, '2026-06-03', 65, 70, 80, '2026-06-03 12:04:11', '2026-06-03 12:04:11'),
(128, 1, '2026-06-03', 75, 75, 80, '2026-06-03 15:30:37', '2026-06-03 15:30:37'),
(129, 2, '2026-06-03', 85, 75, 65, '2026-06-03 15:30:37', '2026-06-03 15:30:37'),
(130, 3, '2026-06-03', 65, 55, 75, '2026-06-03 15:30:37', '2026-06-03 15:30:37'),
(131, 4, '2026-06-03', 55, 55, 65, '2026-06-03 15:30:37', '2026-06-03 15:30:37'),
(132, 5, '2026-06-03', 65, 55, 65, '2026-06-03 15:30:37', '2026-06-03 15:30:37'),
(133, 6, '2026-06-03', 55, 55, 60, '2026-06-03 15:30:37', '2026-06-03 15:30:37'),
(134, 7, '2026-06-03', 55, 55, 60, '2026-06-03 15:30:37', '2026-06-03 15:30:37'),
(135, 8, '2026-06-03', 50, 60, 60, '2026-06-03 15:30:37', '2026-06-03 15:30:37'),
(136, 9, '2026-06-03', 65, 70, 80, '2026-06-03 15:30:37', '2026-06-03 15:30:37'),
(137, 10, '2026-06-03', 55, 55, 55, '2026-06-03 15:30:37', '2026-06-03 15:30:37'),
(138, 11, '2026-06-03', 55, 55, 55, '2026-06-03 15:30:37', '2026-06-03 15:30:37'),
(139, 12, '2026-06-03', 55, 55, 55, '2026-06-03 15:30:37', '2026-06-03 15:30:37'),
(140, 13, '2026-06-03', 55, 55, 55, '2026-06-03 15:30:37', '2026-06-03 15:30:37'),
(141, 2, '2026-06-03', 85, 75, 65, '2026-06-03 15:31:36', '2026-06-03 15:31:36'),
(142, 2, '2026-06-03', 75, 65, 55, '2026-06-03 15:32:26', '2026-06-03 15:32:26'),
(143, 2, '2026-06-03', 75, 65, 55, '2026-06-03 16:18:38', '2026-06-03 16:18:38'),
(144, 2, '2026-06-03', 75, 65, 55, '2026-06-03 16:19:24', '2026-06-03 16:19:24'),
(145, 2, '2026-06-03', 75, 65, 55, '2026-06-03 16:19:27', '2026-06-03 16:19:27'),
(146, 2, '2026-06-03', 75, 65, 55, '2026-06-03 17:18:29', '2026-06-03 17:18:29'),
(147, 2, '2026-06-03', 75, 65, 55, '2026-06-03 17:18:31', '2026-06-03 17:18:31'),
(148, 2, '2026-06-03', 75, 65, 55, '2026-06-03 17:53:57', '2026-06-03 17:53:57'),
(149, 2, '2026-06-03', 75, 65, 55, '2026-06-03 17:55:17', '2026-06-03 17:55:17'),
(150, 2, '2026-06-03', 75, 65, 55, '2026-06-03 17:55:57', '2026-06-03 17:55:57'),
(151, 2, '2026-06-03', 75, 65, 55, '2026-06-03 17:57:00', '2026-06-03 17:57:00'),
(152, 10, '2026-06-04', 75, 65, 55, '2026-06-04 11:41:05', '2026-06-04 11:41:05'),
(153, 11, '2026-06-04', 60, 70, 55, '2026-06-04 11:41:46', '2026-06-04 11:41:46'),
(154, 12, '2026-06-04', 130, 100, 105, '2026-06-04 11:42:20', '2026-06-04 11:42:20'),
(155, 12, '2026-06-04', 130, 100, 105, '2026-06-04 11:42:38', '2026-06-04 11:42:38'),
(156, 13, '2026-06-04', 80, 90, 70, '2026-06-04 11:43:00', '2026-06-04 11:43:00'),
(157, 12, '2026-06-04', 130, 100, 105, '2026-06-04 12:15:10', '2026-06-04 12:15:10'),
(158, 12, '2026-06-04', 130, 100, 105, '2026-06-04 12:15:22', '2026-06-04 12:15:22'),
(159, 12, '2026-06-04', 130, 100, 105, '2026-06-04 12:15:24', '2026-06-04 12:15:24'),
(160, 11, '2026-06-04', 60, 70, 55, '2026-06-04 12:15:44', '2026-06-04 12:15:44'),
(161, 2, '2026-06-08', 75, 65, 55, '2026-06-08 10:05:00', '2026-06-08 10:05:00'),
(162, 2, '2026-06-08', 75, 65, 55, '2026-06-08 10:43:52', '2026-06-08 10:43:52'),
(163, 2, '2026-06-08', 75, 65, 55, '2026-06-08 10:43:58', '2026-06-08 10:43:58'),
(164, 2, '2026-06-08', 75, 65, 55, '2026-06-08 10:44:11', '2026-06-08 10:44:11'),
(165, 1, '2026-06-08', 75, 75, 80, '2026-06-08 10:44:22', '2026-06-08 10:44:22'),
(166, 4, '2026-06-08', 55, 55, 65, '2026-06-08 10:49:40', '2026-06-08 10:49:40'),
(167, 6, '2026-06-08', 55, 55, 60, '2026-06-08 10:54:06', '2026-06-08 10:54:06'),
(168, 5, '2026-06-08', 65, 55, 65, '2026-06-08 10:54:21', '2026-06-08 10:54:21'),
(169, 10, '2026-06-08', 75, 65, 55, '2026-06-08 11:22:25', '2026-06-08 11:22:25'),
(170, 10, '2026-06-08', 75, 65, 55, '2026-06-08 11:23:37', '2026-06-08 11:23:37'),
(171, 2, '2026-06-08', 75, 65, 55, '2026-06-08 11:24:02', '2026-06-08 11:24:02'),
(172, 2, '2026-06-08', 75, 65, 55, '2026-06-08 11:31:31', '2026-06-08 11:31:31'),
(173, 10, '2026-06-08', 75, 65, 55, '2026-06-08 11:34:17', '2026-06-08 11:34:17'),
(174, 9, '2026-06-08', 65, 70, 80, '2026-06-08 11:34:47', '2026-06-08 11:34:47'),
(175, 9, '2026-06-08', 65, 70, 80, '2026-06-08 11:48:48', '2026-06-08 11:48:48'),
(176, 9, '2026-06-08', 65, 70, 80, '2026-06-08 11:48:53', '2026-06-08 11:48:53'),
(177, 9, '2026-06-08', 65, 70, 80, '2026-06-08 11:48:55', '2026-06-08 11:48:55'),
(178, 9, '2026-06-08', 65, 70, 80, '2026-06-08 11:48:57', '2026-06-08 11:48:57'),
(179, 9, '2026-06-08', 65, 70, 80, '2026-06-08 11:48:58', '2026-06-08 11:48:58'),
(180, 9, '2026-06-08', 65, 70, 80, '2026-06-08 11:48:59', '2026-06-08 11:48:59'),
(181, 9, '2026-06-08', 65, 70, 80, '2026-06-08 11:49:01', '2026-06-08 11:49:01'),
(182, 9, '2026-06-08', 65, 70, 80, '2026-06-08 11:49:02', '2026-06-08 11:49:02'),
(183, 9, '2026-06-08', 65, 70, 80, '2026-06-08 11:49:03', '2026-06-08 11:49:03'),
(184, 9, '2026-06-08', 65, 70, 80, '2026-06-08 11:49:04', '2026-06-08 11:49:04'),
(185, 9, '2026-06-08', 65, 70, 80, '2026-06-08 11:49:05', '2026-06-08 11:49:05'),
(186, 7, '2026-06-08', 55, 55, 60, '2026-06-08 11:53:15', '2026-06-08 11:53:15'),
(187, 7, '2026-06-08', 55, 55, 60, '2026-06-08 11:57:41', '2026-06-08 11:57:41'),
(188, 7, '2026-06-08', 55, 55, 60, '2026-06-08 11:57:43', '2026-06-08 11:57:43'),
(189, 7, '2026-06-08', 55, 55, 60, '2026-06-08 11:57:44', '2026-06-08 11:57:44'),
(190, 7, '2026-06-08', 55, 55, 60, '2026-06-08 11:57:51', '2026-06-08 11:57:51'),
(191, 7, '2026-06-08', 55, 55, 60, '2026-06-08 11:57:53', '2026-06-08 11:57:53'),
(192, 7, '2026-06-08', 55, 55, 60, '2026-06-08 11:57:54', '2026-06-08 11:57:54'),
(193, 7, '2026-06-08', 55, 55, 60, '2026-06-08 11:57:57', '2026-06-08 11:57:57'),
(194, 2, '2026-06-08', 75, 65, 55, '2026-06-08 11:58:12', '2026-06-08 11:58:12'),
(195, 2, '2026-06-08', 75, 65, 55, '2026-06-08 11:58:19', '2026-06-08 11:58:19'),
(196, 2, '2026-06-08', 75, 65, 55, '2026-06-08 11:58:22', '2026-06-08 11:58:22'),
(197, 2, '2026-06-08', 75, 65, 55, '2026-06-08 11:58:27', '2026-06-08 11:58:27'),
(198, 2, '2026-06-08', 75, 65, 55, '2026-06-08 11:58:28', '2026-06-08 11:58:28'),
(199, 13, '2026-06-08', 80, 90, 70, '2026-06-08 12:00:43', '2026-06-08 12:00:43'),
(200, 13, '2026-06-08', 80, 90, 70, '2026-06-08 12:01:18', '2026-06-08 12:01:18'),
(201, 13, '2026-06-08', 80, 90, 70, '2026-06-08 12:01:23', '2026-06-08 12:01:23'),
(202, 13, '2026-06-08', 80, 90, 70, '2026-06-08 12:01:24', '2026-06-08 12:01:24'),
(203, 13, '2026-06-08', 80, 90, 70, '2026-06-08 12:01:54', '2026-06-08 12:01:54'),
(204, 13, '2026-06-08', 80, 90, 70, '2026-06-08 14:29:20', '2026-06-08 14:29:20'),
(205, 12, '2026-06-08', 130, 100, 105, '2026-06-08 14:46:20', '2026-06-08 14:46:20'),
(206, 11, '2026-06-08', 60, 70, 55, '2026-06-08 16:37:19', '2026-06-08 16:37:19');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` enum('audition','concert','photoshoot') NOT NULL,
  `date` datetime NOT NULL,
  `required_vocal` int(11) NOT NULL DEFAULT 0,
  `required_dance` int(11) NOT NULL DEFAULT 0,
  `required_visual` int(11) NOT NULL DEFAULT 0,
  `location` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_participants`
--

CREATE TABLE `event_participants` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `result` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lessons`
--

CREATE TABLE `lessons` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` enum('vocal','dance','visual') NOT NULL,
  `stat_gain` int(11) NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `receiver_id` int(11) DEFAULT NULL,
  `subject` varchar(200) DEFAULT NULL,
  `body` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `is_deleted_by_sender` tinyint(1) NOT NULL DEFAULT 0,
  `is_deleted_by_receiver` tinyint(1) NOT NULL DEFAULT 0,
  `parent_message_id` int(11) DEFAULT NULL,
  `sent_at` datetime NOT NULL DEFAULT current_timestamp(),
  `read_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `title` varchar(200) NOT NULL,
  `body` text DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `producer_messages`
--

CREATE TABLE `producer_messages` (
  `id` int(11) NOT NULL,
  `producer_id` int(11) DEFAULT NULL,
  `student_id` int(11) DEFAULT NULL,
  `message_type` varchar(50) NOT NULL,
  `tone` varchar(20) DEFAULT NULL,
  `message_text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `producer_messages`
--

INSERT INTO `producer_messages` (`id`, `producer_id`, `student_id`, `message_type`, `tone`, `message_text`) VALUES
(1, 1, 1, 'morning', 'firm', 'Saki, you were probably on the track before the sun came up. Today, save enough fire for the lessons after homeroom.'),
(2, 1, 1, 'morning', 'warm', 'Good morning. Your four o clock discipline is impressive, but the real test is pacing yourself until evening.'),
(3, 1, 1, 'morning', 'playful', 'Morning, Saki. If you already beat your personal best before breakfast, please do not challenge the staircase too.'),
(4, 1, 1, 'afternoon', 'firm', 'You are halfway through the academy day. Eat, drink water, and then go win the next block properly.'),
(5, 1, 1, 'afternoon', 'warm', 'That focus is good. Just remember, the goal is becoming Saki Hanami, not becoming the most exhausted student here.'),
(6, 1, 1, 'afternoon', 'serious', 'If Ume is in your head again, reset. Your sister is a rival, but your schedule is the opponent in front of you.'),
(7, 1, 1, 'evening', 'firm', 'Saki, stop here. Your body cannot wake at four if you steal sleep from tonight.'),
(8, 1, 1, 'evening', 'gentle', 'You did enough today. Stretch, eat something real, and let the academy be quiet for once.'),
(9, 1, 1, 'evening', 'playful', 'It is evening. If your shoes mysteriously move toward the track, I am blaming you.'),
(10, 1, 1, 'low_vocal', 'firm', 'Your vocals were rushed. Treat the phrase like breath control, not the final lap of a race.'),
(11, 1, 1, 'low_vocal', 'warm', 'A weak vocal score is fixable. You learn fast when you stop being angry at the lesson.'),
(12, 1, 1, 'low_vocal', 'serious', 'Tomorrow we slow the tempo and rebuild support from the diaphragm. No brute force singing.'),
(13, 1, 1, 'low_dance', 'serious', 'Your footwork looked tired today. That is not a challenge to push harder; it is a warning to recover.'),
(14, 1, 1, 'low_dance', 'firm', 'Dance dipped because your body was already spending tomorrow energy. Rest first, correct second.'),
(15, 1, 1, 'low_dance', 'gentle', 'Even former athletes have off days. The smart competitor learns from the miss and comes back cleaner.'),
(16, 1, 1, 'low_visual', 'warm', 'The camera caught you bracing like you were waiting for a starter pistol. Relax the shoulders.'),
(17, 1, 1, 'low_visual', 'firm', 'Visual practice needs softness today. Winning the frame is not the same as fighting it.'),
(18, 1, 1, 'low_visual', 'gentle', 'Let the audience see the person behind the effort. That part of you is worth showing too.'),
(19, 1, 1, 'audition_day', 'firm', 'Audition day. Do not chase perfect. Step out, compete cleanly, and make them remember your name.'),
(20, 1, 1, 'audition_day', 'warm', 'All those early mornings are already inside you. Trust the work and face forward.'),
(21, 1, 1, 'audition_day', 'serious', 'Whatever the result, leave no careless second on that stage. That is the standard today.'),
(22, 1, 1, 'rest_day', 'firm', 'Rest day, Saki. This is not a loophole for secret training. Recovery is the assignment.'),
(23, 1, 1, 'rest_day', 'gentle', 'Let your legs be still today. You are not falling behind by healing.'),
(24, 1, 1, 'rest_day', 'playful', 'I am not saying I hid your running shoes. I am saying do not test me.'),
(25, 1, 1, 'good_progress', 'warm', 'Your progress is not just bigger numbers. You are learning when control matters more than force.'),
(26, 1, 1, 'good_progress', 'firm', 'Good work. Keep the discipline, but do not turn every improvement into a new punishment.'),
(27, 1, 1, 'good_progress', 'gentle', 'You looked less like you were chasing someone today. That is real growth.'),
(28, 1, 1, 'birthday', 'warm', 'Happy birthday, Saki. Today, let people celebrate you without asking whether celebration has rankings.'),
(29, 1, 1, 'birthday', 'firm', 'Birthday order from your Producer: no overtraining, no extra laps, and yes, you are accepting the cake.'),
(30, 1, 1, 'birthday', 'playful', 'Happy birthday. If you race the candles, I am only counting it if everyone gets cake first.'),
(31, 1, 2, 'morning', 'gentle', 'Morning, Temari. Before the sarcasm clocks in, let us get one honest effort started.'),
(32, 1, 2, 'morning', 'firm', 'Good morning. You do not have to feel ready, but you do have to arrive. Vocal room after class.'),
(33, 1, 2, 'morning', 'playful', 'I brought the schedule early so you cannot pretend it became lost on the way to you.'),
(34, 1, 2, 'afternoon', 'warm', 'Your voice sounded steady earlier. Try trusting it before picking a fight with yourself.'),
(35, 1, 2, 'afternoon', 'firm', 'The lazy side may complain, but it does not get voting rights this afternoon.'),
(36, 1, 2, 'afternoon', 'gentle', 'You do not need the old number-one title right now. You need the next clean practice.'),
(37, 1, 2, 'evening', 'gentle', 'Good work today. You stayed longer than your excuses did, and I noticed.'),
(38, 1, 2, 'evening', 'warm', 'You made it through without letting the sharp words win every time. That matters.'),
(39, 1, 2, 'evening', 'playful', 'Evening, Temari. Please eat something decent before Misuzu starts mothering you by instinct.'),
(40, 1, 2, 'low_vocal', 'serious', 'Your singing slipped, and I know that stings. We fix the technique, not your self-worth.'),
(41, 1, 2, 'low_vocal', 'firm', 'Do not hide behind a cold face. Your voice matters to you, so let us work like it does.'),
(42, 1, 2, 'low_vocal', 'gentle', 'The old elite version of you is not the only one allowed to sing well. Start again tomorrow.'),
(43, 1, 2, 'low_dance', 'warm', 'Dance is not your safest ground yet. We will make the steps predictable before asking for style.'),
(44, 1, 2, 'low_dance', 'firm', 'You rushed because you wanted the awkward part over. Slow is better than false cool.'),
(45, 1, 2, 'low_dance', 'gentle', 'Being seen while learning is uncomfortable. You still stayed, and that counts.'),
(46, 1, 2, 'low_visual', 'serious', 'The camera got the mask, not the girl. Next time, one honest expression is the goal.'),
(47, 1, 2, 'low_visual', 'warm', 'You looked guarded today. A little vulnerability will carry farther than another perfect angle.'),
(48, 1, 2, 'low_visual', 'firm', 'Stop performing indifference. The audience cannot support someone who keeps vanishing behind it.'),
(49, 1, 2, 'audition_day', 'firm', 'Audition day. You were called number one before; today, sing as someone choosing herself now.'),
(50, 1, 2, 'audition_day', 'warm', 'You do not need to prove the past was real. Give them the present version of your voice.'),
(51, 1, 2, 'audition_day', 'serious', 'Stand there without apologizing for wanting the top. I will be watching from here.'),
(52, 1, 2, 'rest_day', 'gentle', 'Rest today. No guilt, no performance, no pretending you forgot how to relax.'),
(53, 1, 2, 'rest_day', 'playful', 'Your rest-day menu is not my jurisdiction, unless you try to call dessert vocal training.'),
(54, 1, 2, 'rest_day', 'warm', 'Use the quiet to be kind to yourself. I know that is harder than practice sometimes.'),
(55, 1, 2, 'good_progress', 'warm', 'You sounded more like yourself today. Not the old title, not the rumor, just you.'),
(56, 1, 2, 'good_progress', 'firm', 'Progress came because you stopped dodging the uncomfortable part. Keep that pattern.'),
(57, 1, 2, 'good_progress', 'gentle', 'I saw you accept help before turning it into a joke. That is a real step forward.'),
(58, 1, 2, 'birthday', 'warm', 'Happy birthday, Temari. Today is allowed to be good without you arguing it down first.'),
(59, 1, 2, 'birthday', 'playful', 'Happy birthday. I considered giving you a sarcasm coupon, but you already have unlimited supply.'),
(60, 1, 2, 'birthday', 'gentle', 'Happy birthday. I hope this year gives you more reasons to like the person behind the voice.'),
(61, 1, 3, 'morning', 'warm', 'Morning, Kotone. A money-making idol still starts with attendance, breakfast, and honest work.'),
(62, 1, 3, 'morning', 'playful', 'Cute face confirmed. Now we add practice, because charm alone does not pass the whole schedule.'),
(63, 1, 3, 'morning', 'firm', 'Fans can believe in ambition when they see effort behind it. Show them that today.'),
(64, 1, 3, 'afternoon', 'warm', 'You are doing better than you are letting yourself admit. Keep going before doubt negotiates you down.'),
(65, 1, 3, 'afternoon', 'firm', 'Do not turn low confidence into a discount on your own effort. Full price today.'),
(66, 1, 3, 'afternoon', 'playful', 'Your smile has market value, yes. The choreography still requires exact footwork.'),
(67, 1, 3, 'evening', 'gentle', 'Good work today. You do not need to earn rest with one more useful thing.'),
(68, 1, 3, 'evening', 'warm', 'A fan would have seen effort today, not just cuteness. That is the version we keep building.'),
(69, 1, 3, 'evening', 'playful', 'No invoice for praise tonight, Kotone. But for the record, you did well.'),
(70, 1, 3, 'low_vocal', 'warm', 'Vocals wobbled. We will make the voice as trustworthy as the smile.'),
(71, 1, 3, 'low_vocal', 'firm', 'Do not laugh off the score. If fans are going to follow you, they need to hear confidence too.'),
(72, 1, 3, 'low_vocal', 'gentle', 'Being weak at something does not make you cheap. It makes the next lesson important.'),
(73, 1, 3, 'low_dance', 'serious', 'Dance was below target. Cute appeal works best when the body supports it cleanly.'),
(74, 1, 3, 'low_dance', 'warm', 'The steps can catch up to your charm. We polish one count at a time.'),
(75, 1, 3, 'low_dance', 'firm', 'No bargaining out of dance practice. Future Kotone will thank present Kotone for suffering a little.'),
(76, 1, 3, 'low_visual', 'gentle', 'You pushed the expression too hard today. Trust your natural cuteness before decorating it.'),
(77, 1, 3, 'low_visual', 'warm', 'The camera already likes you. Our job is to make you believe that.'),
(78, 1, 3, 'low_visual', 'firm', 'Confidence is not the same as overselling. Tomorrow we practice the difference.'),
(79, 1, 3, 'audition_day', 'warm', 'Audition day. Make them want to see you again, not because you begged, but because you shined.'),
(80, 1, 3, 'audition_day', 'firm', 'Forget the profit for one performance. Give the audience something worth investing in.'),
(81, 1, 3, 'audition_day', 'playful', 'Today is the pitch: one cute idol, surprisingly stubborn, currently improving fast.'),
(82, 1, 3, 'rest_day', 'gentle', 'Rest today. You are not losing money by letting yourself recover.'),
(83, 1, 3, 'rest_day', 'playful', 'No academy side hustle today. I can see the business plan forming in your eyes.'),
(84, 1, 3, 'rest_day', 'warm', 'A tired smile is still cute, but a rested Kotone is stronger. Choose stronger.'),
(85, 1, 3, 'good_progress', 'warm', 'You are not being overestimated. Your work is finally catching up to the praise.'),
(86, 1, 3, 'good_progress', 'firm', 'Good progress. Stack enough small wins and even your self-doubt will run out of arguments.'),
(87, 1, 3, 'good_progress', 'playful', 'That improvement was marketable. More importantly, it was real.'),
(88, 1, 3, 'birthday', 'warm', 'Happy birthday, Kotone. Today, the compliments are free and fully deserved.'),
(89, 1, 3, 'birthday', 'playful', 'Happy birthday. No receipt, no invoice, no negotiation. Just cake.'),
(90, 1, 3, 'birthday', 'gentle', 'Happy birthday. I hope this year teaches you that your value is not only something you earn.'),
(91, 1, 4, 'morning', 'gentle', 'Good morning, Lilja. One small brave step first. The big dream does not need to be solved before breakfast.'),
(92, 1, 4, 'morning', 'warm', 'You came here with no experience and a real promise. That is enough reason to begin again today.'),
(93, 1, 4, 'morning', 'firm', 'Basics again today. Repetition is not proof you are behind; it is how you become steady.'),
(94, 1, 4, 'afternoon', 'gentle', 'You are doing better than your face says. Stay with the lesson a little longer.'),
(95, 1, 4, 'afternoon', 'warm', 'Practicing beside Sumika is not about copying her. Stand next to her as Lilja.'),
(96, 1, 4, 'afternoon', 'firm', 'Do not shrink when the room gets difficult. You earned your place here by trying.'),
(97, 1, 4, 'evening', 'gentle', 'Good work today. Pick one thing you did better and let yourself keep it.'),
(98, 1, 4, 'evening', 'warm', 'You got nervous and continued anyway. That is the kind of courage stages remember.'),
(99, 1, 4, 'evening', 'playful', 'Evening homework: no sentence starting with \"someone like me.\" I am serious.'),
(100, 1, 4, 'low_vocal', 'gentle', 'Your voice hid today. Tomorrow we start with one calm phrase and let it grow from there.'),
(101, 1, 4, 'low_vocal', 'warm', 'You do not need a loud voice to deserve being heard. We will build confidence into it.'),
(102, 1, 4, 'low_vocal', 'firm', 'No apologizing before the note. Breathe, stand properly, and try again.'),
(103, 1, 4, 'low_dance', 'gentle', 'Dance was hard. That is expected for someone building from the first step.'),
(104, 1, 4, 'low_dance', 'warm', 'Your feet are still learning where confidence lives. Give them time.'),
(105, 1, 4, 'low_dance', 'firm', 'When you freeze, reset and continue. Stopping is allowed; giving up is not.'),
(106, 1, 4, 'low_visual', 'warm', 'The camera cannot see your dream if you keep hiding from it. Lift your eyes.'),
(107, 1, 4, 'low_visual', 'gentle', 'There was one moment today where you looked forward instead of down. We build from that.'),
(108, 1, 4, 'low_visual', 'firm', 'Stand in the frame like someone who made a promise to reach the stage.'),
(109, 1, 4, 'audition_day', 'gentle', 'Audition day. You do not need to become someone else; show them the girl who kept trying.'),
(110, 1, 4, 'audition_day', 'warm', 'Every careful practice brought you here. Take the stage one breath at a time.'),
(111, 1, 4, 'audition_day', 'firm', 'If fear appears, look forward. Your dream is still in that direction.'),
(112, 1, 4, 'rest_day', 'gentle', 'Rest today. Effort grows better when it has room to breathe.'),
(113, 1, 4, 'rest_day', 'warm', 'Recharge with something sweet and familiar. Tomorrow, the basics will still welcome you.'),
(114, 1, 4, 'rest_day', 'playful', 'Baking is allowed today, as long as you do not call it secret visual training.'),
(115, 1, 4, 'good_progress', 'warm', 'You improved quietly today, Lilja. Quiet progress still counts.'),
(116, 1, 4, 'good_progress', 'gentle', 'Your confidence appeared for a few seconds. I saw it, and next time we make it stay longer.'),
(117, 1, 4, 'good_progress', 'firm', 'Good progress. Keep stacking basics until the stage stops feeling impossible.'),
(118, 1, 4, 'birthday', 'warm', 'Happy birthday, Lilja. Your dream has already carried you farther than fear wanted to let you go.'),
(119, 1, 4, 'birthday', 'gentle', 'Happy birthday. I hope today reminds you that you belong in this academy.'),
(120, 1, 4, 'birthday', 'playful', 'Birthday rule: cake first, self-doubt never. That is the whole lesson.'),
(121, 1, 5, 'morning', 'playful', 'Morning, Sumika. Attendance is real today, and charm still does not count as a hall pass.'),
(122, 1, 5, 'morning', 'warm', 'If you show up, your talent gets a chance to surprise even you. Let us start there.'),
(123, 1, 5, 'morning', 'firm', 'Your body knows dance. Today, your motivation needs to arrive on time too.'),
(124, 1, 5, 'afternoon', 'playful', 'I know skipping sounds tempting. Unfortunately, the dance studio already knows your name.'),
(125, 1, 5, 'afternoon', 'warm', 'Lilja is working with all her heart. Match that sincerity with the talent you keep pretending is casual.'),
(126, 1, 5, 'afternoon', 'firm', 'Ballet did not disappear from your body. Use it before it turns into only a story.'),
(127, 1, 5, 'evening', 'warm', 'Good work. Showing up did more for you than pretending not to care ever could.'),
(128, 1, 5, 'evening', 'playful', 'You survived the schedule. Amazing what happens when you attend the lessons on it.'),
(129, 1, 5, 'evening', 'gentle', 'Rest now. Bright energy still needs sleep, even if it runs on karaoke and jokes.'),
(130, 1, 5, 'low_vocal', 'warm', 'Vocals had sparkle, but not enough weight. Put more honest feeling behind the sound.'),
(131, 1, 5, 'low_vocal', 'firm', 'You cannot charm your way through every weak note. Pitch drills tomorrow.'),
(132, 1, 5, 'low_vocal', 'playful', 'The melody wandered off like it was checking social media. Bring it back next time.'),
(133, 1, 5, 'low_dance', 'serious', 'Dance was below what your body can do. That means focus, not ability, was the problem.'),
(134, 1, 5, 'low_dance', 'firm', 'Your movement has history behind it. Stop hiding serious skill behind unserious habits.'),
(135, 1, 5, 'low_dance', 'warm', 'When you commit, the room changes. I want that Sumika more often.'),
(136, 1, 5, 'low_visual', 'warm', 'Visual practice was too casual. Let the camera see the warmth you show your friends.'),
(137, 1, 5, 'low_visual', 'firm', 'The pose looked fine, but the intention wandered. Stay present in the frame.'),
(138, 1, 5, 'low_visual', 'playful', 'SNS confidence and stage-camera confidence are cousins, not twins. We train the second one.'),
(139, 1, 5, 'audition_day', 'warm', 'Audition day. Dance like you finally decided everyone is allowed to see your real skill.'),
(140, 1, 5, 'audition_day', 'firm', 'No skipping, no hiding behind jokes. Step on stage and commit.'),
(141, 1, 5, 'audition_day', 'playful', 'Today the audience gets serious Sumika. Try not to shock them too much.'),
(142, 1, 5, 'rest_day', 'playful', 'Rest day. For once, skipping is officially approved. Enjoy the rare legal loophole.'),
(143, 1, 5, 'rest_day', 'warm', 'Recharge today. Tomorrow, we turn easy charm back into effort.'),
(144, 1, 5, 'rest_day', 'gentle', 'Take it slow. Even bright people need quiet when the academy stops asking for noise.'),
(145, 1, 5, 'good_progress', 'warm', 'Talent and effort arrived together today. That is the version of you I keep betting on.'),
(146, 1, 5, 'good_progress', 'firm', 'You improved because you stayed present. Remember that before pretending it was luck.'),
(147, 1, 5, 'good_progress', 'playful', 'Attendance plus effort equals progress. Revolutionary discovery, Sumika.'),
(148, 1, 5, 'birthday', 'warm', 'Happy birthday, Sumika. Be as bright as you want today without dodging the sincere parts.'),
(149, 1, 5, 'birthday', 'playful', 'Happy birthday. Your gift is one day with no attendance lecture. Mostly.'),
(150, 1, 5, 'birthday', 'gentle', 'Happy birthday. I hope this year gives you more reasons to care openly.'),
(151, 1, 6, 'morning', 'curious', 'Morning, Hiro. Today contains several things you might be bad at. I assumed you would be interested.'),
(152, 1, 6, 'morning', 'firm', 'Challenge is welcome; collapse is not. We pace the difficult parts from the start.'),
(153, 1, 6, 'morning', 'warm', 'You chose the path least suited to you. Strange choice, brave choice. Let us continue.'),
(154, 1, 6, 'afternoon', 'curious', 'If the lesson hurts your pride or your stamina, record which one first. The distinction matters.'),
(155, 1, 6, 'afternoon', 'firm', 'Your stamina limit is data, not a dare. Do not turn the training room into a medical report.'),
(156, 1, 6, 'afternoon', 'warm', 'Failure is teaching you exactly as planned. Now let your body catch up to the theory.'),
(157, 1, 6, 'evening', 'gentle', 'Good work. Difficult things still require recovery afterward, even when you enjoy them.'),
(158, 1, 6, 'evening', 'curious', 'Your notes on failure were excellent. Your rest plan was suspiciously incomplete. Fix that tonight.'),
(159, 1, 6, 'evening', 'firm', 'No extra conditioning. The experiment resumes tomorrow, not after exhaustion.'),
(160, 1, 6, 'low_vocal', 'curious', 'The vocal issue was interesting: your logic arrived before the feeling. Reverse that next time.'),
(161, 1, 6, 'low_vocal', 'warm', 'Your voice is not a puzzle solved only by analysis. Let emotion enter the phrase.'),
(162, 1, 6, 'low_vocal', 'firm', 'Do not celebrate the low score too much. Growth is the goal, not collecting failures.'),
(163, 1, 6, 'low_dance', 'serious', 'Dance exposed the stamina gap again. We reduce the load and increase consistency.'),
(164, 1, 6, 'low_dance', 'curious', 'Your footwork broke at the same transition twice. Useful pattern. We correct that first.'),
(165, 1, 6, 'low_dance', 'gentle', 'Hard lessons can be satisfying, but your body still deserves patience.'),
(166, 1, 6, 'low_visual', 'warm', 'Visual work needs presence, not proof that you understand the concept. Be in the frame.'),
(167, 1, 6, 'low_visual', 'curious', 'Your expression looked like you were solving a math problem. Fascinating, but not the assignment.'),
(168, 1, 6, 'low_visual', 'firm', 'Tomorrow we practice simple emotional beats. No overcomplicating them into diagrams.'),
(169, 1, 6, 'audition_day', 'firm', 'Audition day. Treat the stage as the hardest problem yet, then solve it with your whole self.'),
(170, 1, 6, 'audition_day', 'warm', 'You picked idol work because it seemed unsuitable. Today, prove suitability can be built.'),
(171, 1, 6, 'audition_day', 'curious', 'Let us observe what happens when a genius walks willingly into uncertainty.'),
(172, 1, 6, 'rest_day', 'firm', 'Rest day. A recovery experiment requires actual recovery.'),
(173, 1, 6, 'rest_day', 'gentle', 'Do something easy today. Boredom will not defeat you in one afternoon.'),
(174, 1, 6, 'rest_day', 'curious', 'Observe rest as a challenge: uncomfortable, necessary, and surprisingly hard to optimize.'),
(175, 1, 6, 'good_progress', 'warm', 'Good progress. Your body is starting to answer the challenge instead of only enduring it.'),
(176, 1, 6, 'good_progress', 'curious', 'Interesting result: consistent practice produced improvement. We should repeat the experiment.'),
(177, 1, 6, 'good_progress', 'firm', 'You are improving, so protect the conditions that made improvement possible.'),
(178, 1, 6, 'birthday', 'warm', 'Happy birthday, Hiro. May this year bring difficult things that are also kind to you.'),
(179, 1, 6, 'birthday', 'curious', 'Happy birthday. I considered an easy day as a gift, but that may be your hardest challenge.'),
(180, 1, 6, 'birthday', 'gentle', 'Happy birthday. You do not need to earn celebration by suffering first.'),
(181, 1, 7, 'morning', 'warm', 'Good morning, China. The car brought you to school; your own effort carries you through the academy day.'),
(182, 1, 7, 'morning', 'gentle', 'Start with your cheerful greeting, then one careful basic at a time. That is enough for morning.'),
(183, 1, 7, 'morning', 'firm', 'A splendid idol is built from small correct steps. We take those seriously today.'),
(184, 1, 7, 'afternoon', 'warm', 'Your student council kindness helps the room. Now give your own lesson that same care.'),
(185, 1, 7, 'afternoon', 'gentle', 'You are behind in skill, not in heart. Keep letting the academy teach you.'),
(186, 1, 7, 'afternoon', 'firm', 'Do not rely on being protected. You came here to become an idol yourself.'),
(187, 1, 7, 'evening', 'gentle', 'Good work today. You can go home proud, even if some parts were clumsy.'),
(188, 1, 7, 'evening', 'warm', 'You smiled through hard practice. That is not naivete; that is resilience starting to form.'),
(189, 1, 7, 'evening', 'playful', 'Please tell your driver the academy is returning you tired, not defeated.'),
(190, 1, 7, 'low_vocal', 'gentle', 'Your voice shook today. We will practice simple phrases until confidence joins the sound.'),
(191, 1, 7, 'low_vocal', 'warm', 'The sincerity is already there. Now we give it posture, breath, and steadiness.'),
(192, 1, 7, 'low_vocal', 'firm', 'Do not announce defeat before the next note. Stand properly and try again.'),
(193, 1, 7, 'low_dance', 'warm', 'Dance was difficult, but your retry mattered. Basics first, elegance later.'),
(194, 1, 7, 'low_dance', 'firm', 'No skipping the foundation. Even an ojou-sama idol needs reliable footwork.'),
(195, 1, 7, 'low_dance', 'gentle', 'You stumbled, then continued. That is the part I want you to remember.'),
(196, 1, 7, 'low_visual', 'warm', 'Your innocence is charming, but the camera still needs you to face it directly.'),
(197, 1, 7, 'low_visual', 'gentle', 'Lift your chin slowly and let the expression settle. You do not need to rush elegance.'),
(198, 1, 7, 'low_visual', 'firm', 'A splendid idol cannot hide behind politeness. Hold the pose and own the frame.'),
(199, 1, 7, 'audition_day', 'warm', 'Audition day. Show them the student who started at the bottom and kept smiling upward.'),
(200, 1, 7, 'audition_day', 'firm', 'No apologizing for your level today. Perform the best version of what you can do now.'),
(201, 1, 7, 'audition_day', 'gentle', 'If you get nervous, breathe, greet the stage in your heart, and begin again.'),
(202, 1, 7, 'rest_day', 'gentle', 'Rest day. No academy duties today, China. Enjoy home without turning it into a lesson.'),
(203, 1, 7, 'rest_day', 'warm', 'Recharge at the villa. Tomorrow, the basics will still be waiting, and you will be ready.'),
(204, 1, 7, 'rest_day', 'playful', 'No secret student council errands by car today. I know helpfulness has wheels in your case.'),
(205, 1, 7, 'good_progress', 'warm', 'You improved because you kept trying after embarrassment. That is real strength.'),
(206, 1, 7, 'good_progress', 'gentle', 'Your basics are steadier. Be proud, China. Slowly is still forward.'),
(207, 1, 7, 'good_progress', 'firm', 'Good progress. Keep earning each step yourself; that is how confidence becomes yours.'),
(208, 1, 7, 'birthday', 'warm', 'Happy birthday, China. Today we celebrate the splendid idol you are working so hard to become.'),
(209, 1, 7, 'birthday', 'gentle', 'Happy birthday. May this year give you courage, kind lessons, and many reasons to smile.'),
(210, 1, 7, 'birthday', 'playful', 'Happy birthday. The cake may be elegant, but your effort is the real luxury.'),
(211, 1, 8, 'morning', 'warm', 'Morning, Ume. Chase Saki if you must, but make sure today grows your own talent too.'),
(212, 1, 8, 'morning', 'firm', 'Your energy is huge. Point it at the schedule first, your sister second.'),
(213, 1, 8, 'morning', 'playful', 'Good morning. Rival mode is awake; now let us wake up control mode too.'),
(214, 1, 8, 'afternoon', 'warm', 'Your council energy helps people, not just performances. Use it with care.'),
(215, 1, 8, 'afternoon', 'firm', 'Do not turn every drill into a Saki rematch. Some wins are quieter.'),
(216, 1, 8, 'afternoon', 'energetic', 'That stamina is a gift. Spend it on clean practice, not chaos.'),
(217, 1, 8, 'evening', 'gentle', 'Good work today. You can rest without falling behind your sister.'),
(218, 1, 8, 'evening', 'warm', 'You pushed hard and kept smiling. That combination will take you far.'),
(219, 1, 8, 'evening', 'playful', 'No challenging Saki to a bedtime race. That is not a recognized academy event.'),
(220, 1, 8, 'low_vocal', 'warm', 'Vocals were rough. The power is there; now we train control.'),
(221, 1, 8, 'low_vocal', 'firm', 'Do not shout your way through the phrase. Beat your old score with precision, not volume.'),
(222, 1, 8, 'low_vocal', 'gentle', 'Your voice can grow like stamina did: repetition, patience, and no panic.'),
(223, 1, 8, 'low_dance', 'firm', 'Dance got too explosive. The audience needs to see shape, not just power.'),
(224, 1, 8, 'low_dance', 'warm', 'Your movement has energy. We teach it timing so it lands like choreography.'),
(225, 1, 8, 'low_dance', 'playful', 'You attacked the routine like a sports festival. Impressive, but let us make it idol dance.'),
(226, 1, 8, 'low_visual', 'gentle', 'The visual score dipped when you started comparing yourself. The camera wants Ume.'),
(227, 1, 8, 'low_visual', 'warm', 'Your bright expression is strong. Hold it longer and trust it.'),
(228, 1, 8, 'low_visual', 'firm', 'Stop checking whether you look like Saki. Face forward as yourself.'),
(229, 1, 8, 'audition_day', 'warm', 'Audition day. Perform for the dream, not only the rivalry.'),
(230, 1, 8, 'audition_day', 'firm', 'You want to beat Saki. Good. Today, start by beating yesterday.'),
(231, 1, 8, 'audition_day', 'energetic', 'Bring the room your full energy, Ume. Just keep the steering wheel attached.'),
(232, 1, 8, 'rest_day', 'gentle', 'Rest day. Even athletes need recovery if they want to win tomorrow.'),
(233, 1, 8, 'rest_day', 'playful', 'No sister challenges today. The official sport is resting.'),
(234, 1, 8, 'rest_day', 'warm', 'Recharge that big energy. The academy will need it again soon.'),
(235, 1, 8, 'good_progress', 'warm', 'Good progress. You are not only chasing Saki; you are starting to bloom as Ume.'),
(236, 1, 8, 'good_progress', 'firm', 'Your control improved. That matters more than raw power.'),
(237, 1, 8, 'good_progress', 'energetic', 'That was a real step forward. Keep that fire aimed at growth.'),
(238, 1, 8, 'birthday', 'warm', 'Happy birthday, Ume. Celebrate yourself before challenging anyone else.'),
(239, 1, 8, 'birthday', 'playful', 'Happy birthday. I already declined three imaginary rematches with Saki for you.'),
(240, 1, 8, 'birthday', 'gentle', 'Happy birthday. Your admiration is beautiful, but your own dream deserves the spotlight too.'),
(241, 1, 9, 'morning', 'gentle', 'Good morning, Misuzu. Please stay awake through homeroom before taking care of everyone else.'),
(242, 1, 9, 'morning', 'warm', 'Your calm helps the class, but your own lesson comes first today.'),
(243, 1, 9, 'morning', 'playful', 'I trust your body clock. I do not trust your nap instincts. Let us begin.'),
(244, 1, 9, 'afternoon', 'warm', 'Council work suits your kindness. Just remember you are a student, not everyone’s pillow.'),
(245, 1, 9, 'afternoon', 'gentle', 'Your pace is slow, but steady. Keep it kind to yourself too.'),
(246, 1, 9, 'afternoon', 'firm', 'Do not spend all your energy caring for Temari or the others. Save some for your own idol work.'),
(247, 1, 9, 'evening', 'gentle', 'Good work today. Rest now, and no turning rest into worrying about everyone else.'),
(248, 1, 9, 'evening', 'warm', 'You supported the room and still practiced. That balance is progress.'),
(249, 1, 9, 'evening', 'playful', 'If you fall asleep reading this, I will count it as successful recovery.'),
(250, 1, 9, 'low_vocal', 'warm', 'Vocals were sleepy today. We will wake the breath gently, not force it.'),
(251, 1, 9, 'low_vocal', 'firm', 'Your voice needs more presence. Soft is fine; disappearing is not.'),
(252, 1, 9, 'low_vocal', 'gentle', 'One quiet vocal day is okay. Tomorrow, give the melody a little more light.'),
(253, 1, 9, 'low_dance', 'gentle', 'Dance was slow today. We keep the movement calm, but more intentional.'),
(254, 1, 9, 'low_dance', 'firm', 'You cannot drift through choreography. Choose each step, even the gentle ones.'),
(255, 1, 9, 'low_dance', 'warm', 'Your softness can be beautiful on stage. It just needs steadier timing.'),
(256, 1, 9, 'low_visual', 'warm', 'Visual work suits your calm, but today the camera needed more focus from you.'),
(257, 1, 9, 'low_visual', 'gentle', 'You looked kind, but distant. Let the audience feel invited in.'),
(258, 1, 9, 'low_visual', 'firm', 'No sleepy eyes unless the concept asks for them. Tomorrow we practice alert warmth.'),
(259, 1, 9, 'audition_day', 'gentle', 'Audition day. Let your kindness reach the audience without hiding behind it.'),
(260, 1, 9, 'audition_day', 'warm', 'You do not need to be loud to be memorable. Be present, Misuzu.'),
(261, 1, 9, 'audition_day', 'firm', 'Stay awake, stay centered, and perform for yourself too.'),
(262, 1, 9, 'rest_day', 'gentle', 'Rest day. This one is practically designed for you, but use it properly.'),
(263, 1, 9, 'rest_day', 'warm', 'Nap, walk, breathe. Let yourself be cared for today.'),
(264, 1, 9, 'rest_day', 'playful', 'Official rest day. For once, your favorite activity is curriculum-approved.'),
(265, 1, 9, 'good_progress', 'warm', 'Good progress. Your calm presence is becoming stage presence.'),
(266, 1, 9, 'good_progress', 'gentle', 'You balanced care for others with effort for yourself today. That is important.'),
(267, 1, 9, 'good_progress', 'firm', 'You improved because you stayed awake in the work, not only near it. Keep that.'),
(268, 1, 9, 'birthday', 'warm', 'Happy birthday, Misuzu. Today, let everyone spoil you for a change.'),
(269, 1, 9, 'birthday', 'gentle', 'Happy birthday. May this year be soft, bright, and full of peaceful growth.'),
(270, 1, 9, 'birthday', 'playful', 'Happy birthday. I scheduled no mandatory nap because you already handled that yourself.'),
(271, 1, 10, 'morning', 'warm', 'Good morning, Mao. The Little Prince has class first, then the stage can have you.'),
(272, 1, 10, 'morning', 'firm', 'Cool is not just an image today. It is posture, care, and follow-through.'),
(273, 1, 10, 'morning', 'playful', 'Dorm leader, please mentor yourself with the same patience you give the juniors.'),
(274, 1, 10, 'afternoon', 'warm', 'Your stagecraft is strongest when you stop fighting the cute parts of yourself.'),
(275, 1, 10, 'afternoon', 'firm', 'Do not reject a useful expression just because it is not cool enough in your head.'),
(276, 1, 10, 'afternoon', 'gentle', 'The juniors look up to you, but you are allowed to still be learning too.'),
(277, 1, 10, 'evening', 'warm', 'Good work today. You carried yourself well, and not only as dorm leader.'),
(278, 1, 10, 'evening', 'gentle', 'Rest now. Caring for everyone includes not wearing yourself down.'),
(279, 1, 10, 'evening', 'playful', 'Curtain down, prince. No encore for responsibilities tonight.'),
(280, 1, 10, 'low_vocal', 'warm', 'Vocals need more openness. Let the audience hear the person behind the cool silhouette.'),
(281, 1, 10, 'low_vocal', 'firm', 'Your voice tightened when you tried to look composed. Breathe before the line.'),
(282, 1, 10, 'low_vocal', 'gentle', 'You do not have to sound princely every second. Honest tone will carry farther.'),
(283, 1, 10, 'low_dance', 'firm', 'Dance lost sharpness. Martial movement helps only if the timing stays musical.'),
(284, 1, 10, 'low_dance', 'warm', 'Your body knows discipline. Now we make the movement flow instead of pose.'),
(285, 1, 10, 'low_dance', 'serious', 'Cool stage presence still needs clean footwork. We polish that tomorrow.'),
(286, 1, 10, 'low_visual', 'warm', 'Visual work is your stage language, but today it leaned too guarded. Let warmth through.'),
(287, 1, 10, 'low_visual', 'firm', 'You can be cool and cute. Stop treating that like a contradiction.'),
(288, 1, 10, 'low_visual', 'gentle', 'The camera caught your hesitation. We practice accepting the softer expression.'),
(289, 1, 10, 'audition_day', 'warm', 'Audition day. Walk on stage like the Little Prince, then perform like Mao.'),
(290, 1, 10, 'audition_day', 'firm', 'Do not run from cute, do not overplay cool. Balance is your weapon today.'),
(291, 1, 10, 'audition_day', 'serious', 'Your past brought technique. Your present brings heart. Use both.'),
(292, 1, 10, 'rest_day', 'gentle', 'Rest day. Dorm leader duties can wait unless there is an actual emergency.'),
(293, 1, 10, 'rest_day', 'warm', 'Take care of yourself today. The juniors need to see that example too.'),
(294, 1, 10, 'rest_day', 'playful', 'No heroic rounds today. Even princes have days off.'),
(295, 1, 10, 'good_progress', 'warm', 'Good progress. You looked cooler because you stopped rejecting your own softness.'),
(296, 1, 10, 'good_progress', 'firm', 'Your stagecraft sharpened today. Keep building the full range, not only the image.'),
(297, 1, 10, 'good_progress', 'gentle', 'You let more of yourself onto the stage. That is the right direction.'),
(298, 1, 10, 'birthday', 'warm', 'Happy birthday, Mao. Today, let the dorm celebrate its Little Prince properly.'),
(299, 1, 10, 'birthday', 'playful', 'Happy birthday. Your only duty today is accepting compliments with dignity.'),
(300, 1, 10, 'birthday', 'gentle', 'Happy birthday. May this year let you be cool, cute, and completely yourself.'),
(301, 1, 11, 'morning', 'warm', 'Good morning, Rinami. Council, class, and everyone else can wait until you choose yourself first.'),
(302, 1, 11, 'morning', 'gentle', 'Your kindness is steady. Today, your confidence gets the same care.'),
(303, 1, 11, 'morning', 'firm', 'You are not only support staff for other people’s dreams. Your idol work matters today.'),
(304, 1, 11, 'afternoon', 'warm', 'Your big-sister presence helps the council room, but the stage needs your own voice too.'),
(305, 1, 11, 'afternoon', 'gentle', 'Do not measure today by the old unit result. You are building something new.'),
(306, 1, 11, 'afternoon', 'firm', 'Care for the juniors after you finish your own assignment. That order matters.'),
(307, 1, 11, 'evening', 'gentle', 'Good work today. Let yourself receive support instead of always giving it.'),
(308, 1, 11, 'evening', 'warm', 'You balanced council duties and lessons well. I am proud of that.'),
(309, 1, 11, 'evening', 'playful', 'No fixing anyone’s tie after hours unless it is your own.'),
(310, 1, 11, 'low_vocal', 'gentle', 'Vocals were unsure. Sing like your place on stage is not temporary.'),
(311, 1, 11, 'low_vocal', 'warm', 'Your voice has warmth. We need to help it stand forward instead of behind others.'),
(312, 1, 11, 'low_vocal', 'firm', 'Do not apologize with your tone. Hold the line and let it belong to you.'),
(313, 1, 11, 'low_dance', 'warm', 'Dance needs more confidence. You are allowed to take up space.'),
(314, 1, 11, 'low_dance', 'firm', 'Your movement pulled back today. Stop making yourself smaller for everyone else.'),
(315, 1, 11, 'low_dance', 'gentle', 'The steps are there. Next time, trust them enough to be seen.'),
(316, 1, 11, 'low_visual', 'warm', 'Visual work suits your mature charm, but today you looked like you were checking on others.'),
(317, 1, 11, 'low_visual', 'firm', 'Face the camera for yourself. Not as secretary, not as dorm sister, as Rinami.'),
(318, 1, 11, 'low_visual', 'gentle', 'Your softness is strong. Let it become presence, not background.'),
(319, 1, 11, 'audition_day', 'warm', 'Audition day. This stage is not a repeat of the past. It is yours now.'),
(320, 1, 11, 'audition_day', 'firm', 'Do not support from the edge today. Step into the center and stay there.'),
(321, 1, 11, 'audition_day', 'gentle', 'You have cared for many people. Today, let the audience care about you.'),
(322, 1, 11, 'rest_day', 'gentle', 'Rest day. No council work, no dorm rounds, no quiet over-helping. Just rest.'),
(323, 1, 11, 'rest_day', 'warm', 'Let yourself be looked after today, Rinami. You have earned that.'),
(324, 1, 11, 'rest_day', 'playful', 'If you organize a rest-day checklist for everyone else, I am confiscating it.'),
(325, 1, 11, 'good_progress', 'warm', 'Good progress. You looked like you believed the spotlight could belong to you.'),
(326, 1, 11, 'good_progress', 'firm', 'You kept your own goal in view today. Keep doing that.'),
(327, 1, 11, 'good_progress', 'gentle', 'The old unit result is getting smaller behind you. Your current growth is louder.'),
(328, 1, 11, 'birthday', 'warm', 'Happy birthday, Rinami. Today, everyone gets to be the older sibling for you.'),
(329, 1, 11, 'birthday', 'gentle', 'Happy birthday. May this year give you a stage that feels fully yours.'),
(330, 1, 11, 'birthday', 'playful', 'Happy birthday. I checked your schedule: receiving appreciation is mandatory.'),
(331, 1, 12, 'morning', 'professional', 'Good morning, Sena. Academy top idol or not, the basics still deserve your full attention.'),
(332, 1, 12, 'morning', 'firm', 'Today we maintain the standard, not the image of the standard.'),
(333, 1, 12, 'morning', 'warm', 'Your leadership is strongest when students can see effort behind the excellence.'),
(334, 1, 12, 'afternoon', 'professional', 'Council work first, then talent review. Keep the academy moving without losing your own edge.'),
(335, 1, 12, 'afternoon', 'firm', 'Do not let admiration make you careless. The top position needs maintenance.'),
(336, 1, 12, 'afternoon', 'warm', 'Your eye for talent is sharp. Give your own growth the same scrutiny.'),
(337, 1, 12, 'evening', 'professional', 'Good work today. File the reports, close the room, and leave the academy on time.'),
(338, 1, 12, 'evening', 'warm', 'You carried the top-idol standard well today. Now let the day end.'),
(339, 1, 12, 'evening', 'firm', 'No late council revisions. Leaders need recovery to stay precise.'),
(340, 1, 12, 'low_vocal', 'serious', 'Your vocal standard dipped. We correct it before reputation tries to excuse it.'),
(341, 1, 12, 'low_vocal', 'firm', 'Elite training means responding quickly to weakness. Vocal drills tomorrow.'),
(342, 1, 12, 'low_vocal', 'warm', 'A rare slip is still a slip. I trust you to face it directly.'),
(343, 1, 12, 'low_dance', 'serious', 'Dance was not at your usual level. Precision review comes before rivalry rehearsal.'),
(344, 1, 12, 'low_dance', 'firm', 'Do not let confidence outrun clean movement. The top idol cannot skip fundamentals.'),
(345, 1, 12, 'low_dance', 'professional', 'We will audit the choreography counts and correct the weak transitions.'),
(346, 1, 12, 'low_visual', 'serious', 'Visual presence looked too controlled. The audience needs warmth, not only polish.'),
(347, 1, 12, 'low_visual', 'firm', 'You looked like the president more than the idol. Tomorrow, balance both.'),
(348, 1, 12, 'low_visual', 'warm', 'Your presence is powerful. Let it invite people in instead of placing you above them.'),
(349, 1, 12, 'audition_day', 'professional', 'Audition day. Perform like the academy standard is not a title, but a promise kept.'),
(350, 1, 12, 'audition_day', 'firm', 'Everyone expects excellence. Give them precision, then give them something human.'),
(351, 1, 12, 'audition_day', 'warm', 'You have carried expectations for years. Today, let the stage carry some of it back.'),
(352, 1, 12, 'rest_day', 'firm', 'Rest day. No student council tasks, no scouting notes, no quiet return to campus.'),
(353, 1, 12, 'rest_day', 'professional', 'The academy can wait today. Protecting performance quality includes scheduled rest.'),
(354, 1, 12, 'rest_day', 'warm', 'Step away from the top-idol role for a little while. You will return sharper.'),
(355, 1, 12, 'good_progress', 'professional', 'Good progress. Your standard rose because you reviewed yourself without vanity.'),
(356, 1, 12, 'good_progress', 'firm', 'Strong improvement. Keep leading from evidence, not reputation.'),
(357, 1, 12, 'good_progress', 'warm', 'You showed excellence and generosity today. That combination is why students follow you.'),
(358, 1, 12, 'birthday', 'warm', 'Happy birthday, Sena. Today, let the academy admire you without asking for more.'),
(359, 1, 12, 'birthday', 'professional', 'Happy birthday. Your schedule has one priority: receive celebration with grace.'),
(360, 1, 12, 'birthday', 'firm', 'Happy birthday. The council can survive one day without its president overworking.'),
(361, 1, 13, 'morning', 'serious', 'Good morning, Tsubame. Pride is useful only when it sharpens the work. Sharpen it today.'),
(362, 1, 13, 'morning', 'firm', 'Today is not about comparing yourself to Sena before first period. It is about execution.'),
(363, 1, 13, 'morning', 'warm', 'Your discipline sets the room straight. Let it support you too.'),
(364, 1, 13, 'afternoon', 'serious', 'Council duty, then rehearsal. Handle both with the precision expected of the academy No. 2.'),
(365, 1, 13, 'afternoon', 'firm', 'Do not waste energy proving you are strict. Put it into the performance.'),
(366, 1, 13, 'afternoon', 'warm', 'You are hard on yourself and others. Today, make room for improvement without contempt.'),
(367, 1, 13, 'evening', 'firm', 'Good work. The rivalry can sleep for tonight; your body should too.'),
(368, 1, 13, 'evening', 'serious', 'Review the lesson once, not ten times. Discipline includes knowing when to stop.'),
(369, 1, 13, 'evening', 'warm', 'You held the line today and still helped the room. That is real leadership.'),
(370, 1, 13, 'low_vocal', 'serious', 'Vocals lacked control. We correct the weak measures immediately.'),
(371, 1, 13, 'low_vocal', 'firm', 'Do not hide behind pride. The score dipped; the solution is practice.'),
(372, 1, 13, 'low_vocal', 'warm', 'A flaw does not insult your ability. It gives your discipline somewhere to work.'),
(373, 1, 13, 'low_dance', 'serious', 'Dance precision slipped. For someone chasing Sena, that detail matters.'),
(374, 1, 13, 'low_dance', 'firm', 'Your movement was forceful but not clean enough. Power must obey timing.'),
(375, 1, 13, 'low_dance', 'warm', 'You can demand better from yourself without turning cruel. Start there.'),
(376, 1, 13, 'low_visual', 'serious', 'Visual presence became too severe. Command the camera; do not threaten it.'),
(377, 1, 13, 'low_visual', 'firm', 'The audience needs to admire you, not brace for inspection. Adjust the expression.'),
(378, 1, 13, 'low_visual', 'warm', 'Your pride can be beautiful on stage when it carries care with it.'),
(379, 1, 13, 'audition_day', 'serious', 'Audition day. Do not perform against Sena in your head. Perform beyond yesterday’s Tsubame.'),
(380, 1, 13, 'audition_day', 'firm', 'Show them why No. 2 is not a comfort zone for you. Clean, proud, controlled.'),
(381, 1, 13, 'audition_day', 'warm', 'You have the discipline. Let the audience see the heart beneath it.'),
(382, 1, 13, 'rest_day', 'firm', 'Rest day. No rivalry rehearsal, no council cleanup, no secret practice.'),
(383, 1, 13, 'rest_day', 'serious', 'Recovery is not indulgence. It is preparation for surpassing your limits properly.'),
(384, 1, 13, 'rest_day', 'warm', 'Let yourself step back today. The ambition will still be there tomorrow.'),
(385, 1, 13, 'good_progress', 'serious', 'Good progress. Your precision improved because you listened before correcting.'),
(386, 1, 13, 'good_progress', 'firm', 'That was a sharper performance. Keep the pride, lose the unnecessary tension.'),
(387, 1, 13, 'good_progress', 'warm', 'You looked strong today without closing everyone out. That is the direction.'),
(388, 1, 13, 'birthday', 'warm', 'Happy birthday, Tsubame. Today, let pride accept celebration without turning it into a challenge.'),
(389, 1, 13, 'birthday', 'serious', 'Happy birthday. Your assignment is to let the day honor how far you have come.'),
(390, 1, 13, 'birthday', 'playful', 'Happy birthday. I am not ranking birthdays, so do not ask whether yours beat Sena’s.');

-- --------------------------------------------------------

--
-- Table structure for table `recurring_schedules`
--

CREATE TABLE `recurring_schedules` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `weekday` tinyint(4) NOT NULL,
  `activity_type` varchar(50) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `location` varchar(100) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recurring_schedules`
--

INSERT INTO `recurring_schedules` (`id`, `student_id`, `weekday`, `activity_type`, `title`, `description`, `start_time`, `end_time`, `location`, `created_by`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'class', 'Class 1-1 Homeroom', 'Weekly planning, announcements, and attendance with the same classmates.', '09:00:00', '09:40:00', 'Classroom 1-1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(2, 2, 1, 'class', 'Class 1-1 Homeroom', 'Weekly planning, announcements, and attendance with the same classmates.', '09:00:00', '09:40:00', 'Classroom 1-1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(3, 3, 1, 'class', 'Class 1-1 Homeroom', 'Weekly planning, announcements, and attendance with the same classmates.', '09:00:00', '09:40:00', 'Classroom 1-1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(4, 4, 1, 'class', 'Class 1-1 Homeroom', 'Weekly planning, announcements, and attendance with the same classmates.', '09:00:00', '09:40:00', 'Classroom 1-1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(5, 5, 1, 'class', 'Class 1-1 Homeroom', 'Weekly planning, announcements, and attendance with the same classmates.', '09:00:00', '09:40:00', 'Classroom 1-1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(6, 1, 1, 'class', 'Class 1-1 Academic Studies', 'General academy coursework shared by the full class.', '10:00:00', '11:30:00', 'Classroom 1-1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(7, 2, 1, 'class', 'Class 1-1 Academic Studies', 'General academy coursework shared by the full class.', '10:00:00', '11:30:00', 'Classroom 1-1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(8, 3, 1, 'class', 'Class 1-1 Academic Studies', 'General academy coursework shared by the full class.', '10:00:00', '11:30:00', 'Classroom 1-1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(9, 4, 1, 'class', 'Class 1-1 Academic Studies', 'General academy coursework shared by the full class.', '10:00:00', '11:30:00', 'Classroom 1-1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(10, 5, 1, 'class', 'Class 1-1 Academic Studies', 'General academy coursework shared by the full class.', '10:00:00', '11:30:00', 'Classroom 1-1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(11, 1, 2, 'class', 'Class 1-1 Idol Theory', 'Study of idol history, stage etiquette, and audience communication.', '09:00:00', '10:20:00', 'Classroom 1-1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(12, 2, 2, 'class', 'Class 1-1 Idol Theory', 'Study of idol history, stage etiquette, and audience communication.', '09:00:00', '10:20:00', 'Classroom 1-1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(13, 3, 2, 'class', 'Class 1-1 Idol Theory', 'Study of idol history, stage etiquette, and audience communication.', '09:00:00', '10:20:00', 'Classroom 1-1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(14, 4, 2, 'class', 'Class 1-1 Idol Theory', 'Study of idol history, stage etiquette, and audience communication.', '09:00:00', '10:20:00', 'Classroom 1-1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(15, 5, 2, 'class', 'Class 1-1 Idol Theory', 'Study of idol history, stage etiquette, and audience communication.', '09:00:00', '10:20:00', 'Classroom 1-1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(16, 1, 3, 'class', 'Class 1-1 Academic Studies', 'General academy coursework shared by the full class.', '09:00:00', '10:30:00', 'Classroom 1-1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(17, 2, 3, 'class', 'Class 1-1 Academic Studies', 'General academy coursework shared by the full class.', '09:00:00', '10:30:00', 'Classroom 1-1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(18, 3, 3, 'class', 'Class 1-1 Academic Studies', 'General academy coursework shared by the full class.', '09:00:00', '10:30:00', 'Classroom 1-1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(19, 4, 3, 'class', 'Class 1-1 Academic Studies', 'General academy coursework shared by the full class.', '09:00:00', '10:30:00', 'Classroom 1-1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(20, 5, 3, 'class', 'Class 1-1 Academic Studies', 'General academy coursework shared by the full class.', '09:00:00', '10:30:00', 'Classroom 1-1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(21, 1, 4, 'class', 'Class 1-1 Media Literacy', 'Interview manners, SNS safety, and public-facing conduct.', '09:00:00', '10:20:00', 'Classroom 1-1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(22, 2, 4, 'class', 'Class 1-1 Media Literacy', 'Interview manners, SNS safety, and public-facing conduct.', '09:00:00', '10:20:00', 'Classroom 1-1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(23, 3, 4, 'class', 'Class 1-1 Media Literacy', 'Interview manners, SNS safety, and public-facing conduct.', '09:00:00', '10:20:00', 'Classroom 1-1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(24, 4, 4, 'class', 'Class 1-1 Media Literacy', 'Interview manners, SNS safety, and public-facing conduct.', '09:00:00', '10:20:00', 'Classroom 1-1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(25, 5, 4, 'class', 'Class 1-1 Media Literacy', 'Interview manners, SNS safety, and public-facing conduct.', '09:00:00', '10:20:00', 'Classroom 1-1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(26, 1, 5, 'class', 'Class 1-1 Performance Review', 'Weekly class review and goal setting before weekend training.', '09:00:00', '10:00:00', 'Classroom 1-1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(27, 2, 5, 'class', 'Class 1-1 Performance Review', 'Weekly class review and goal setting before weekend training.', '09:00:00', '10:00:00', 'Classroom 1-1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(28, 3, 5, 'class', 'Class 1-1 Performance Review', 'Weekly class review and goal setting before weekend training.', '09:00:00', '10:00:00', 'Classroom 1-1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(29, 4, 5, 'class', 'Class 1-1 Performance Review', 'Weekly class review and goal setting before weekend training.', '09:00:00', '10:00:00', 'Classroom 1-1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(30, 5, 5, 'class', 'Class 1-1 Performance Review', 'Weekly class review and goal setting before weekend training.', '09:00:00', '10:00:00', 'Classroom 1-1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(31, 6, 1, 'class', 'Class 1-2 Homeroom', 'Weekly planning, announcements, and attendance with the same classmates.', '09:00:00', '09:40:00', 'Classroom 1-2', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(32, 7, 1, 'class', 'Class 1-2 Homeroom', 'Weekly planning, announcements, and attendance with the same classmates.', '09:00:00', '09:40:00', 'Classroom 1-2', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(33, 8, 1, 'class', 'Class 1-2 Homeroom', 'Weekly planning, announcements, and attendance with the same classmates.', '09:00:00', '09:40:00', 'Classroom 1-2', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(34, 9, 1, 'class', 'Class 1-2 Homeroom', 'Weekly planning, announcements, and attendance with the same classmates.', '09:00:00', '09:40:00', 'Classroom 1-2', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(35, 6, 1, 'class', 'Class 1-2 Academic Studies', 'General academy coursework shared by the full class.', '10:00:00', '11:30:00', 'Classroom 1-2', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(36, 7, 1, 'class', 'Class 1-2 Academic Studies', 'General academy coursework shared by the full class.', '10:00:00', '11:30:00', 'Classroom 1-2', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(37, 8, 1, 'class', 'Class 1-2 Academic Studies', 'General academy coursework shared by the full class.', '10:00:00', '11:30:00', 'Classroom 1-2', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(38, 9, 1, 'class', 'Class 1-2 Academic Studies', 'General academy coursework shared by the full class.', '10:00:00', '11:30:00', 'Classroom 1-2', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(39, 6, 2, 'class', 'Class 1-2 Idol Theory', 'Study of idol history, stage etiquette, and audience communication.', '09:00:00', '10:20:00', 'Classroom 1-2', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(40, 7, 2, 'class', 'Class 1-2 Idol Theory', 'Study of idol history, stage etiquette, and audience communication.', '09:00:00', '10:20:00', 'Classroom 1-2', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(41, 8, 2, 'class', 'Class 1-2 Idol Theory', 'Study of idol history, stage etiquette, and audience communication.', '09:00:00', '10:20:00', 'Classroom 1-2', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(42, 9, 2, 'class', 'Class 1-2 Idol Theory', 'Study of idol history, stage etiquette, and audience communication.', '09:00:00', '10:20:00', 'Classroom 1-2', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(43, 6, 3, 'class', 'Class 1-2 Academic Studies', 'General academy coursework shared by the full class.', '09:00:00', '10:30:00', 'Classroom 1-2', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(44, 7, 3, 'class', 'Class 1-2 Academic Studies', 'General academy coursework shared by the full class.', '09:00:00', '10:30:00', 'Classroom 1-2', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(45, 8, 3, 'class', 'Class 1-2 Academic Studies', 'General academy coursework shared by the full class.', '09:00:00', '10:30:00', 'Classroom 1-2', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(46, 9, 3, 'class', 'Class 1-2 Academic Studies', 'General academy coursework shared by the full class.', '09:00:00', '10:30:00', 'Classroom 1-2', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(47, 6, 4, 'class', 'Class 1-2 Media Literacy', 'Interview manners, SNS safety, and public-facing conduct.', '09:00:00', '10:20:00', 'Classroom 1-2', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(48, 7, 4, 'class', 'Class 1-2 Media Literacy', 'Interview manners, SNS safety, and public-facing conduct.', '09:00:00', '10:20:00', 'Classroom 1-2', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(49, 8, 4, 'class', 'Class 1-2 Media Literacy', 'Interview manners, SNS safety, and public-facing conduct.', '09:00:00', '10:20:00', 'Classroom 1-2', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(50, 9, 4, 'class', 'Class 1-2 Media Literacy', 'Interview manners, SNS safety, and public-facing conduct.', '09:00:00', '10:20:00', 'Classroom 1-2', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(51, 6, 5, 'class', 'Class 1-2 Performance Review', 'Weekly class review and goal setting before weekend training.', '09:00:00', '10:00:00', 'Classroom 1-2', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(52, 7, 5, 'class', 'Class 1-2 Performance Review', 'Weekly class review and goal setting before weekend training.', '09:00:00', '10:00:00', 'Classroom 1-2', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(53, 8, 5, 'class', 'Class 1-2 Performance Review', 'Weekly class review and goal setting before weekend training.', '09:00:00', '10:00:00', 'Classroom 1-2', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(54, 9, 5, 'class', 'Class 1-2 Performance Review', 'Weekly class review and goal setting before weekend training.', '09:00:00', '10:00:00', 'Classroom 1-2', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(55, 10, 1, 'class', 'Class 3-1 Homeroom', 'Weekly planning, announcements, and attendance with the same classmates.', '09:00:00', '09:40:00', 'Classroom 3-1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(56, 11, 1, 'class', 'Class 3-1 Homeroom', 'Weekly planning, announcements, and attendance with the same classmates.', '09:00:00', '09:40:00', 'Classroom 3-1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(57, 12, 1, 'class', 'Class 3-1 Homeroom', 'Weekly planning, announcements, and attendance with the same classmates.', '09:00:00', '09:40:00', 'Classroom 3-1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(58, 13, 1, 'class', 'Class 3-1 Homeroom', 'Weekly planning, announcements, and attendance with the same classmates.', '09:00:00', '09:40:00', 'Classroom 3-1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(59, 10, 1, 'class', 'Class 3-1 Academic Studies', 'General academy coursework shared by the full class.', '10:00:00', '11:30:00', 'Classroom 3-1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(60, 11, 1, 'class', 'Class 3-1 Academic Studies', 'General academy coursework shared by the full class.', '10:00:00', '11:30:00', 'Classroom 3-1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(61, 12, 1, 'class', 'Class 3-1 Academic Studies', 'General academy coursework shared by the full class.', '10:00:00', '11:30:00', 'Classroom 3-1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(62, 13, 1, 'class', 'Class 3-1 Academic Studies', 'General academy coursework shared by the full class.', '10:00:00', '11:30:00', 'Classroom 3-1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(63, 10, 2, 'class', 'Class 3-1 Idol Theory', 'Study of idol history, stage etiquette, and audience communication.', '09:00:00', '10:20:00', 'Classroom 3-1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(64, 11, 2, 'class', 'Class 3-1 Idol Theory', 'Study of idol history, stage etiquette, and audience communication.', '09:00:00', '10:20:00', 'Classroom 3-1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(65, 12, 2, 'class', 'Class 3-1 Idol Theory', 'Study of idol history, stage etiquette, and audience communication.', '09:00:00', '10:20:00', 'Classroom 3-1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(66, 13, 2, 'class', 'Class 3-1 Idol Theory', 'Study of idol history, stage etiquette, and audience communication.', '09:00:00', '10:20:00', 'Classroom 3-1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(67, 10, 3, 'class', 'Class 3-1 Academic Studies', 'General academy coursework shared by the full class.', '09:00:00', '10:30:00', 'Classroom 3-1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(68, 11, 3, 'class', 'Class 3-1 Academic Studies', 'General academy coursework shared by the full class.', '09:00:00', '10:30:00', 'Classroom 3-1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(69, 12, 3, 'class', 'Class 3-1 Academic Studies', 'General academy coursework shared by the full class.', '09:00:00', '10:30:00', 'Classroom 3-1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(70, 13, 3, 'class', 'Class 3-1 Academic Studies', 'General academy coursework shared by the full class.', '09:00:00', '10:30:00', 'Classroom 3-1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(71, 10, 4, 'class', 'Class 3-1 Media Literacy', 'Interview manners, SNS safety, and public-facing conduct.', '09:00:00', '10:20:00', 'Classroom 3-1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(72, 11, 4, 'class', 'Class 3-1 Media Literacy', 'Interview manners, SNS safety, and public-facing conduct.', '09:00:00', '10:20:00', 'Classroom 3-1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(73, 12, 4, 'class', 'Class 3-1 Media Literacy', 'Interview manners, SNS safety, and public-facing conduct.', '09:00:00', '10:20:00', 'Classroom 3-1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(74, 13, 4, 'class', 'Class 3-1 Media Literacy', 'Interview manners, SNS safety, and public-facing conduct.', '09:00:00', '10:20:00', 'Classroom 3-1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(75, 10, 5, 'class', 'Class 3-1 Performance Review', 'Weekly class review and goal setting before weekend training.', '09:00:00', '10:00:00', 'Classroom 3-1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(76, 11, 5, 'class', 'Class 3-1 Performance Review', 'Weekly class review and goal setting before weekend training.', '09:00:00', '10:00:00', 'Classroom 3-1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(77, 12, 5, 'class', 'Class 3-1 Performance Review', 'Weekly class review and goal setting before weekend training.', '09:00:00', '10:00:00', 'Classroom 3-1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(78, 13, 5, 'class', 'Class 3-1 Performance Review', 'Weekly class review and goal setting before weekend training.', '09:00:00', '10:00:00', 'Classroom 3-1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(79, 1, 1, 'dance', 'First-Year Dance Fundamentals', 'Shared fundamentals lesson for Class 1-1.', '13:00:00', '14:30:00', 'Dance Studio 1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(80, 2, 1, 'dance', 'First-Year Dance Fundamentals', 'Shared fundamentals lesson for Class 1-1.', '13:00:00', '14:30:00', 'Dance Studio 1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(81, 3, 1, 'dance', 'First-Year Dance Fundamentals', 'Shared fundamentals lesson for Class 1-1.', '13:00:00', '14:30:00', 'Dance Studio 1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(82, 4, 1, 'dance', 'First-Year Dance Fundamentals', 'Shared fundamentals lesson for Class 1-1.', '13:00:00', '14:30:00', 'Dance Studio 1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(83, 5, 1, 'dance', 'First-Year Dance Fundamentals', 'Shared fundamentals lesson for Class 1-1.', '13:00:00', '14:30:00', 'Dance Studio 1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(84, 6, 1, 'vocal', 'First-Year Vocal Fundamentals', 'Shared fundamentals lesson for Class 1-2.', '13:00:00', '14:30:00', 'Vocal Studio B', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(85, 7, 1, 'vocal', 'First-Year Vocal Fundamentals', 'Shared fundamentals lesson for Class 1-2.', '13:00:00', '14:30:00', 'Vocal Studio B', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(86, 8, 1, 'vocal', 'First-Year Vocal Fundamentals', 'Shared fundamentals lesson for Class 1-2.', '13:00:00', '14:30:00', 'Vocal Studio B', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(87, 9, 1, 'vocal', 'First-Year Vocal Fundamentals', 'Shared fundamentals lesson for Class 1-2.', '13:00:00', '14:30:00', 'Vocal Studio B', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(88, 10, 1, 'visual', 'Third-Year Branding Seminar', 'Advanced image-building and presence work for Class 3-1.', '13:00:00', '14:30:00', 'Visual Studio', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(89, 11, 1, 'visual', 'Third-Year Branding Seminar', 'Advanced image-building and presence work for Class 3-1.', '13:00:00', '14:30:00', 'Visual Studio', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(90, 12, 1, 'visual', 'Third-Year Branding Seminar', 'Advanced image-building and presence work for Class 3-1.', '13:00:00', '14:30:00', 'Visual Studio', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(91, 13, 1, 'visual', 'Third-Year Branding Seminar', 'Advanced image-building and presence work for Class 3-1.', '13:00:00', '14:30:00', 'Visual Studio', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(92, 1, 2, 'vocal', 'First-Year Vocal Fundamentals', 'Shared fundamentals lesson for Class 1-1.', '13:00:00', '14:30:00', 'Vocal Studio A', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(93, 2, 2, 'vocal', 'First-Year Vocal Fundamentals', 'Shared fundamentals lesson for Class 1-1.', '13:00:00', '14:30:00', 'Vocal Studio A', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(94, 3, 2, 'vocal', 'First-Year Vocal Fundamentals', 'Shared fundamentals lesson for Class 1-1.', '13:00:00', '14:30:00', 'Vocal Studio A', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(95, 4, 2, 'vocal', 'First-Year Vocal Fundamentals', 'Shared fundamentals lesson for Class 1-1.', '13:00:00', '14:30:00', 'Vocal Studio A', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(96, 5, 2, 'vocal', 'First-Year Vocal Fundamentals', 'Shared fundamentals lesson for Class 1-1.', '13:00:00', '14:30:00', 'Vocal Studio A', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(97, 6, 2, 'dance', 'First-Year Dance Fundamentals', 'Shared fundamentals lesson for Class 1-2.', '13:00:00', '14:30:00', 'Dance Studio 2', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(98, 7, 2, 'dance', 'First-Year Dance Fundamentals', 'Shared fundamentals lesson for Class 1-2.', '13:00:00', '14:30:00', 'Dance Studio 2', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(99, 8, 2, 'dance', 'First-Year Dance Fundamentals', 'Shared fundamentals lesson for Class 1-2.', '13:00:00', '14:30:00', 'Dance Studio 2', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(100, 9, 2, 'dance', 'First-Year Dance Fundamentals', 'Shared fundamentals lesson for Class 1-2.', '13:00:00', '14:30:00', 'Dance Studio 2', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(101, 10, 2, 'dance', 'Advanced Stage Movement', 'Upper-year lesson for clean, commanding movement.', '13:00:00', '14:30:00', 'Main Stage', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(102, 11, 2, 'dance', 'Advanced Stage Movement', 'Upper-year lesson for clean, commanding movement.', '13:00:00', '14:30:00', 'Main Stage', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(103, 12, 2, 'dance', 'Advanced Stage Movement', 'Upper-year lesson for clean, commanding movement.', '13:00:00', '14:30:00', 'Main Stage', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(104, 13, 2, 'dance', 'Advanced Stage Movement', 'Upper-year lesson for clean, commanding movement.', '13:00:00', '14:30:00', 'Main Stage', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(105, 1, 3, 'visual', 'First-Year Visual Fundamentals', 'Shared visual lesson for Class 1-1.', '13:00:00', '14:30:00', 'Visual Studio', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(106, 2, 3, 'visual', 'First-Year Visual Fundamentals', 'Shared visual lesson for Class 1-1.', '13:00:00', '14:30:00', 'Visual Studio', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(107, 3, 3, 'visual', 'First-Year Visual Fundamentals', 'Shared visual lesson for Class 1-1.', '13:00:00', '14:30:00', 'Visual Studio', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(108, 4, 3, 'visual', 'First-Year Visual Fundamentals', 'Shared visual lesson for Class 1-1.', '13:00:00', '14:30:00', 'Visual Studio', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(109, 5, 3, 'visual', 'First-Year Visual Fundamentals', 'Shared visual lesson for Class 1-1.', '13:00:00', '14:30:00', 'Visual Studio', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(110, 6, 3, 'class', 'Class 1-2 Remedial Skills Lab', 'Extra fundamentals for students who need careful support.', '13:00:00', '14:30:00', 'Classroom 1-2', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(111, 7, 3, 'class', 'Class 1-2 Remedial Skills Lab', 'Extra fundamentals for students who need careful support.', '13:00:00', '14:30:00', 'Classroom 1-2', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(112, 8, 3, 'class', 'Class 1-2 Remedial Skills Lab', 'Extra fundamentals for students who need careful support.', '13:00:00', '14:30:00', 'Classroom 1-2', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(113, 9, 3, 'class', 'Class 1-2 Remedial Skills Lab', 'Extra fundamentals for students who need careful support.', '13:00:00', '14:30:00', 'Classroom 1-2', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(114, 10, 3, 'vocal', 'Advanced Vocal Expression', 'Upper-year lesson for emotional vocal control.', '13:00:00', '14:30:00', 'Vocal Studio A', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(115, 11, 3, 'vocal', 'Advanced Vocal Expression', 'Upper-year lesson for emotional vocal control.', '13:00:00', '14:30:00', 'Vocal Studio A', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(116, 12, 3, 'vocal', 'Advanced Vocal Expression', 'Upper-year lesson for emotional vocal control.', '13:00:00', '14:30:00', 'Vocal Studio A', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(117, 13, 3, 'vocal', 'Advanced Vocal Expression', 'Upper-year lesson for emotional vocal control.', '13:00:00', '14:30:00', 'Vocal Studio A', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(118, 1, 4, 'dance', 'First-Year Choreography Lab', 'Group choreography and formation practice.', '13:00:00', '14:30:00', 'Dance Studio 1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(119, 2, 4, 'dance', 'First-Year Choreography Lab', 'Group choreography and formation practice.', '13:00:00', '14:30:00', 'Dance Studio 1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(120, 3, 4, 'dance', 'First-Year Choreography Lab', 'Group choreography and formation practice.', '13:00:00', '14:30:00', 'Dance Studio 1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(121, 4, 4, 'dance', 'First-Year Choreography Lab', 'Group choreography and formation practice.', '13:00:00', '14:30:00', 'Dance Studio 1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(122, 5, 4, 'dance', 'First-Year Choreography Lab', 'Group choreography and formation practice.', '13:00:00', '14:30:00', 'Dance Studio 1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(123, 6, 4, 'visual', 'First-Year Visual Fundamentals', 'Shared visual lesson for Class 1-2.', '13:00:00', '14:30:00', 'Visual Studio', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(124, 7, 4, 'visual', 'First-Year Visual Fundamentals', 'Shared visual lesson for Class 1-2.', '13:00:00', '14:30:00', 'Visual Studio', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(125, 8, 4, 'visual', 'First-Year Visual Fundamentals', 'Shared visual lesson for Class 1-2.', '13:00:00', '14:30:00', 'Visual Studio', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(126, 9, 4, 'visual', 'First-Year Visual Fundamentals', 'Shared visual lesson for Class 1-2.', '13:00:00', '14:30:00', 'Visual Studio', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(127, 10, 4, 'class', 'Third-Year Career Seminar', 'Career planning, leadership, and graduation preparation.', '13:00:00', '14:30:00', 'Classroom 3-1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(128, 11, 4, 'class', 'Third-Year Career Seminar', 'Career planning, leadership, and graduation preparation.', '13:00:00', '14:30:00', 'Classroom 3-1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(129, 12, 4, 'class', 'Third-Year Career Seminar', 'Career planning, leadership, and graduation preparation.', '13:00:00', '14:30:00', 'Classroom 3-1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(130, 13, 4, 'class', 'Third-Year Career Seminar', 'Career planning, leadership, and graduation preparation.', '13:00:00', '14:30:00', 'Classroom 3-1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(131, 1, 5, 'vocal', 'First-Year Ensemble Vocal', 'Harmonies and listening practice for Class 1-1.', '13:00:00', '14:30:00', 'Vocal Studio A', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(132, 2, 5, 'vocal', 'First-Year Ensemble Vocal', 'Harmonies and listening practice for Class 1-1.', '13:00:00', '14:30:00', 'Vocal Studio A', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(133, 3, 5, 'vocal', 'First-Year Ensemble Vocal', 'Harmonies and listening practice for Class 1-1.', '13:00:00', '14:30:00', 'Vocal Studio A', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(134, 4, 5, 'vocal', 'First-Year Ensemble Vocal', 'Harmonies and listening practice for Class 1-1.', '13:00:00', '14:30:00', 'Vocal Studio A', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(135, 5, 5, 'vocal', 'First-Year Ensemble Vocal', 'Harmonies and listening practice for Class 1-1.', '13:00:00', '14:30:00', 'Vocal Studio A', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(136, 6, 5, 'dance', 'First-Year Conditioning Dance', 'Stamina-friendly dance basics for Class 1-2.', '13:00:00', '14:30:00', 'Dance Studio 2', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(137, 7, 5, 'dance', 'First-Year Conditioning Dance', 'Stamina-friendly dance basics for Class 1-2.', '13:00:00', '14:30:00', 'Dance Studio 2', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(138, 8, 5, 'dance', 'First-Year Conditioning Dance', 'Stamina-friendly dance basics for Class 1-2.', '13:00:00', '14:30:00', 'Dance Studio 2', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(139, 9, 5, 'dance', 'First-Year Conditioning Dance', 'Stamina-friendly dance basics for Class 1-2.', '13:00:00', '14:30:00', 'Dance Studio 2', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(140, 10, 5, 'visual', 'Advanced Photo Presence', 'Camera-facing expression and poise.', '13:00:00', '14:30:00', 'Photo Studio', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(141, 11, 5, 'visual', 'Advanced Photo Presence', 'Camera-facing expression and poise.', '13:00:00', '14:30:00', 'Photo Studio', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(142, 12, 5, 'visual', 'Advanced Photo Presence', 'Camera-facing expression and poise.', '13:00:00', '14:30:00', 'Photo Studio', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(143, 13, 5, 'visual', 'Advanced Photo Presence', 'Camera-facing expression and poise.', '13:00:00', '14:30:00', 'Photo Studio', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(144, 1, 1, 'training', 'Pre-Dawn Competitive Run', 'Saki wakes around 4 AM, so her academy day starts early and ends early.', '04:30:00', '05:30:00', 'Academy Grounds', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(145, 1, 2, 'training', 'Pre-Dawn Competitive Run', 'Early solo conditioning before class.', '04:30:00', '05:30:00', 'Academy Grounds', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(146, 1, 3, 'training', 'Pre-Dawn Competitive Run', 'Early solo conditioning before class.', '04:30:00', '05:30:00', 'Academy Grounds', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(147, 1, 4, 'training', 'Hanami Rival Sprint', 'Saki and Ume turn sisterly rivalry into controlled sprint work.', '04:30:00', '05:30:00', 'Academy Track', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(148, 8, 4, 'training', 'Hanami Rival Sprint', 'Ume trains directly against the sister she wants to surpass.', '04:30:00', '05:30:00', 'Academy Track', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(149, 1, 5, 'training', 'Pre-Dawn Competitive Run', 'Final early run before the weekend; no late-night academy work.', '04:30:00', '05:30:00', 'Academy Grounds', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(150, 1, 6, 'training', 'Hanami Rival Sports Drill', 'Saki and Ume compete through athletic idol conditioning.', '08:00:00', '09:30:00', 'Academy Grounds', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(151, 8, 6, 'training', 'Hanami Rival Sports Drill', 'Saki and Ume compete through athletic idol conditioning.', '08:00:00', '09:30:00', 'Academy Grounds', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(152, 2, 1, 'vocal', 'Elite Vocal Reset', 'Temari rebuilds her former elite technique without overstraining.', '15:00:00', '16:00:00', 'Vocal Studio A', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(153, 2, 2, 'vocal', 'SyngUp Recovery Practice', 'Temari and Misuzu revisit old unit habits in a calmer setting.', '15:00:00', '16:00:00', 'Vocal Studio B', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(154, 9, 2, 'vocal', 'SyngUp Recovery Practice', 'Temari and Misuzu revisit old unit habits in a calmer setting.', '15:00:00', '16:00:00', 'Vocal Studio B', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(155, 2, 4, 'meeting', 'Producer Check-in: Self-Discipline', 'A short accountability meeting to keep her lazy side from taking over.', '15:00:00', '15:40:00', 'Producer Room', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(156, 3, 1, 'meeting', 'Fan Business Planning', 'Kotone studies idol income, fan trust, and realistic work planning.', '15:00:00', '16:00:00', 'Producer Room', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(157, 3, 3, 'dance', 'Cute Appeal Dance Practice', 'Dance practice built around the cute face and charm she trusts.', '15:00:00', '16:00:00', 'Dance Studio 1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(158, 3, 5, 'class', 'Academic Catch-Up', 'Remedial study because her grades need producer-backed structure.', '15:00:00', '16:00:00', 'Study Room A', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(159, 4, 1, 'vocal', 'Beginner Confidence Vocal', 'Lilja starts from basics with patient repetition.', '15:00:00', '16:00:00', 'Practice Room 1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(160, 4, 2, 'dance', 'Promise Practice', 'Lilja and Sumika practice together toward their shared stage promise.', '15:00:00', '16:00:00', 'Dance Studio 1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(161, 5, 2, 'dance', 'Promise Practice', 'Lilja and Sumika practice together toward their shared stage promise.', '15:00:00', '16:00:00', 'Dance Studio 1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(162, 4, 4, 'dance', 'Beginner Step Review', 'Slow technical review for a hardworking beginner.', '15:00:00', '16:00:00', 'Practice Room 1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(163, 5, 3, 'dance', 'Ballet-to-Idol Movement', 'Sumika converts former ballet skill into idol choreography.', '15:00:00', '16:00:00', 'Ballet Studio', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:21:46'),
(164, 5, 5, 'meeting', 'Attendance Motivation Check', 'A light check-in for a bright student who tends to skip lessons.', '15:00:00', '15:40:00', 'Counseling Room', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(165, 6, 1, 'training', 'Low-Stamina Conditioning', 'Hiro takes on the hard physical work she finds fascinating.', '15:00:00', '15:50:00', 'Training Room', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(166, 6, 2, 'vocal', 'Uncomfortable Vocal Challenge', 'A difficult lesson chosen because it does not come naturally.', '15:00:00', '16:00:00', 'Practice Room 2', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(167, 6, 4, 'dance', 'Failure-Friendly Dance Drill', 'Structured hard practice with breaks before exhaustion becomes unsafe.', '15:00:00', '16:00:00', 'Dance Studio 2', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(168, 6, 6, 'visual', 'Puzzle-Based Stage Blocking', 'Hiro treats stage movement like a spatial logic problem.', '10:00:00', '11:00:00', 'Stage Lab B', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:21:46'),
(169, 7, 1, 'vocal', 'Ojou-sama Basics', 'China patiently works from the bottom with cheerful greetings and fundamentals.', '15:00:00', '16:00:00', 'Practice Room 3', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(170, 7, 3, 'visual', 'Polite Stage Presence', 'Visual training using manners, posture, and innocent charm.', '15:00:00', '16:00:00', 'Photo Studio', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(171, 7, 5, 'class', 'Foundations Tutorial', 'Extra support for a sheltered student catching up to academy pace.', '15:00:00', '16:00:00', 'Study Room B', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(172, 8, 2, 'training', 'Explosive Athlete Conditioning', 'Ume turns raw physical ability into idol-ready stamina.', '15:00:00', '16:00:00', 'Athletics Gym', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:21:46'),
(173, 8, 3, 'dance', 'Power Dance Practice', 'Energetic dance work that channels her athletic background.', '15:00:00', '16:00:00', 'Dance Studio 2', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(174, 8, 5, 'training', 'Rival Goal Review', 'Ume reviews progress toward catching up with Saki.', '15:00:00', '15:40:00', 'Producer Room', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(175, 9, 1, 'visual', 'Gentle Presence Practice', 'Misuzu uses calm expression and careful timing instead of force.', '15:00:00', '16:00:00', 'Photo Studio', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(176, 9, 4, 'rest', 'Recovery Break', 'A sanctioned rest period inside the academy schedule for her sleepy rhythm.', '15:00:00', '15:40:00', 'Wellness Room', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(177, 9, 5, 'meeting', 'Care Team Check-in', 'Misuzu helps review support needs for classmates without turning it into private time.', '15:00:00', '15:40:00', 'Wellness Room', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(178, 10, 1, 'visual', 'Little Prince Stagecraft', 'Mao practices cool, theatrical presence rooted in her child-actor past.', '15:00:00', '16:00:00', 'Black Box Theater', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(179, 10, 2, 'training', 'Martial Arts Movement', 'Controlled combat movement for a cool stage silhouette.', '15:00:00', '16:00:00', 'Training Room', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(180, 10, 4, 'meeting', 'Dorm Support Rounds', 'Dorm residents only; Mao leads and Rinami supports juniors in the academy dorm common area.', '17:40:00', '18:20:00', 'Dorm Common Room', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(181, 11, 4, 'meeting', 'Dorm Support Rounds', 'Dorm residents only; Mao leads and Rinami supports juniors in the academy dorm common area.', '17:40:00', '18:20:00', 'Dorm Common Room', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(182, 10, 6, 'visual', 'Theater Workshop', 'Weekend acting and stage expression practice.', '10:00:00', '11:30:00', 'Black Box Theater', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(183, 11, 1, 'meeting', 'Student Council Session', 'Council work for president, vice president, and secretary.', '16:20:00', '17:20:00', 'Student Council Room', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(184, 12, 1, 'meeting', 'Student Council Session', 'Council work for president, vice president, and secretary.', '16:20:00', '17:20:00', 'Student Council Room', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(185, 13, 1, 'meeting', 'Student Council Session', 'Council work for president, vice president, and secretary.', '16:20:00', '17:20:00', 'Student Council Room', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(186, 11, 3, 'meeting', 'Student Council Session', 'Midweek council planning and academy requests.', '16:20:00', '17:20:00', 'Student Council Room', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(187, 12, 3, 'meeting', 'Student Council Session', 'Midweek council planning and academy requests.', '16:20:00', '17:20:00', 'Student Council Room', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(188, 13, 3, 'meeting', 'Student Council Session', 'Midweek council planning and academy requests.', '16:20:00', '17:20:00', 'Student Council Room', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(189, 11, 5, 'meeting', 'Student Council Session', 'Weekly closing reports and event approvals.', '16:20:00', '17:20:00', 'Student Council Room', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(190, 12, 5, 'meeting', 'Student Council Session', 'Weekly closing reports and event approvals.', '16:20:00', '17:20:00', 'Student Council Room', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(191, 13, 5, 'meeting', 'Student Council Session', 'Weekly closing reports and event approvals.', '16:20:00', '17:20:00', 'Student Council Room', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(192, 11, 2, 'visual', 'Big-Sister Styling Lab', 'Rinami practices cosmetics, care, and approachable stage warmth.', '15:00:00', '16:00:00', 'Dressing Room', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(193, 11, 4, 'vocal', 'Unit Confidence Rebuild', 'A gentle rebuild after her earlier unit did not succeed.', '15:00:00', '16:00:00', 'Vocal Studio A', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(194, 12, 2, 'vocal', 'Elite Idol Maintenance', 'Sena maintains the academy top-idol standard with precise training.', '15:00:00', '16:00:00', 'Vocal Studio A', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(195, 12, 4, 'meeting', 'Talent Scouting Review', 'Sena reviews promising students, including the talent she sees in Kotone.', '15:00:00', '15:50:00', 'Student Council Room', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(196, 12, 6, 'dance', 'Top-Two Rivalry Rehearsal', 'Sena and Tsubame sharpen each other without dorm scheduling.', '10:00:00', '11:30:00', 'Main Stage', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(197, 13, 6, 'dance', 'Top-Two Rivalry Rehearsal', 'Sena and Tsubame sharpen each other without dorm scheduling.', '10:00:00', '11:30:00', 'Main Stage', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(198, 13, 2, 'visual', 'Vice President Poise Drill', 'Tsubame practices strict presence and disciplined expression.', '15:00:00', '16:00:00', 'Photo Studio', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(199, 13, 4, 'dance', 'No. 2 Precision Rehearsal', 'Advanced rehearsal aimed at someday surpassing Sena.', '15:00:00', '16:00:00', 'Main Stage', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(200, 2, 6, 'rest', 'Quiet Recovery Block', 'Academy-sanctioned quiet recovery, not private off-campus time.', '09:30:00', '10:30:00', 'Wellness Room', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(201, 9, 6, 'rest', 'Quiet Recovery Block', 'Academy-sanctioned quiet recovery, not private off-campus time.', '09:30:00', '10:30:00', 'Wellness Room', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(202, 3, 6, 'meeting', 'Merchandise Idea Review', 'Kotone reviews fan-facing ideas with producer oversight.', '10:00:00', '11:00:00', 'Producer Room', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(203, 4, 6, 'dance', 'Promise Stage Rehearsal', 'Lilja and Sumika keep their shared-stage promise alive.', '10:00:00', '11:30:00', 'Dance Studio 1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(204, 5, 6, 'dance', 'Promise Stage Rehearsal', 'Lilja and Sumika keep their shared-stage promise alive.', '10:00:00', '11:30:00', 'Dance Studio 1', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(205, 7, 6, 'visual', 'Illustration-to-Stage Expression', 'China uses drawing and imagination to shape stage expression.', '10:00:00', '11:00:00', 'Visual Studio', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(206, 1, 7, 'rest', 'Academy Rest Day', 'No academy lessons or council duties are scheduled on Sunday.', '09:00:00', '17:00:00', 'No Scheduled Academy Location', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(207, 2, 7, 'rest', 'Academy Rest Day', 'No academy lessons or council duties are scheduled on Sunday.', '09:00:00', '17:00:00', 'No Scheduled Academy Location', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(208, 3, 7, 'rest', 'Academy Rest Day', 'No academy lessons or council duties are scheduled on Sunday.', '09:00:00', '17:00:00', 'No Scheduled Academy Location', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(209, 4, 7, 'rest', 'Academy Rest Day', 'No academy lessons or council duties are scheduled on Sunday.', '09:00:00', '17:00:00', 'No Scheduled Academy Location', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(210, 5, 7, 'rest', 'Academy Rest Day', 'No academy lessons or council duties are scheduled on Sunday.', '09:00:00', '17:00:00', 'No Scheduled Academy Location', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(211, 6, 7, 'rest', 'Academy Rest Day', 'No academy lessons or council duties are scheduled on Sunday.', '09:00:00', '17:00:00', 'No Scheduled Academy Location', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(212, 7, 7, 'rest', 'Academy Rest Day', 'No academy lessons or council duties are scheduled on Sunday.', '09:00:00', '17:00:00', 'No Scheduled Academy Location', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(213, 8, 7, 'rest', 'Academy Rest Day', 'No academy lessons or council duties are scheduled on Sunday.', '09:00:00', '17:00:00', 'No Scheduled Academy Location', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(214, 9, 7, 'rest', 'Academy Rest Day', 'No academy lessons or council duties are scheduled on Sunday.', '09:00:00', '17:00:00', 'No Scheduled Academy Location', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(215, 10, 7, 'rest', 'Academy Rest Day', 'No academy lessons or council duties are scheduled on Sunday.', '09:00:00', '17:00:00', 'No Scheduled Academy Location', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(216, 11, 7, 'rest', 'Academy Rest Day', 'No academy lessons or council duties are scheduled on Sunday.', '09:00:00', '17:00:00', 'No Scheduled Academy Location', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(217, 12, 7, 'rest', 'Academy Rest Day', 'No academy lessons or council duties are scheduled on Sunday.', '09:00:00', '17:00:00', 'No Scheduled Academy Location', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(218, 13, 7, 'rest', 'Academy Rest Day', 'No academy lessons or council duties are scheduled on Sunday.', '09:00:00', '17:00:00', 'No Scheduled Academy Location', 1, 1, '2026-06-08 11:20:32', '2026-06-08 11:20:32'),
(219, 7, 1, 'meeting', 'Student Council Session', 'Student council work with China handling cheerful support and committee errands.', '16:20:00', '17:20:00', 'Student Council Room', 1, 1, '2026-06-08 11:30:35', '2026-06-08 11:30:35'),
(220, 8, 1, 'meeting', 'Student Council Session', 'Student council work with Ume helping through energy, outreach, and practical tasks.', '16:20:00', '17:20:00', 'Student Council Room', 1, 1, '2026-06-08 11:30:35', '2026-06-08 11:30:35'),
(221, 9, 1, 'meeting', 'Student Council Session', 'Student council work with Misuzu supporting welfare and student care requests.', '16:20:00', '17:20:00', 'Student Council Room', 1, 1, '2026-06-08 11:30:35', '2026-06-08 11:30:35'),
(222, 7, 3, 'meeting', 'Student Council Session', 'Student council work with China handling cheerful support and committee errands.', '16:20:00', '17:20:00', 'Student Council Room', 1, 1, '2026-06-08 11:30:35', '2026-06-08 11:30:35'),
(223, 8, 3, 'meeting', 'Student Council Session', 'Student council work with Ume helping through energy, outreach, and practical tasks.', '16:20:00', '17:20:00', 'Student Council Room', 1, 1, '2026-06-08 11:30:35', '2026-06-08 11:30:35'),
(224, 9, 3, 'meeting', 'Student Council Session', 'Student council work with Misuzu supporting welfare and student care requests.', '16:20:00', '17:20:00', 'Student Council Room', 1, 1, '2026-06-08 11:30:35', '2026-06-08 11:30:35'),
(225, 7, 5, 'meeting', 'Student Council Session', 'Student council work with China handling cheerful support and committee errands.', '16:20:00', '17:20:00', 'Student Council Room', 1, 1, '2026-06-08 11:30:35', '2026-06-08 11:30:35'),
(226, 8, 5, 'meeting', 'Student Council Session', 'Student council work with Ume helping through energy, outreach, and practical tasks.', '16:20:00', '17:20:00', 'Student Council Room', 1, 1, '2026-06-08 11:30:35', '2026-06-08 11:30:35'),
(227, 9, 5, 'meeting', 'Student Council Session', 'Student council work with Misuzu supporting welfare and student care requests.', '16:20:00', '17:20:00', 'Student Council Room', 1, 1, '2026-06-08 11:30:35', '2026-06-08 11:30:35');

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

CREATE TABLE `schedules` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `activity_type` varchar(50) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `location` varchar(100) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `status` enum('scheduled','completed','cancelled') NOT NULL DEFAULT 'scheduled',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `songs`
--

CREATE TABLE `songs` (
  `id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `title_jp` varchar(150) DEFAULT NULL,
  `artist` varchar(150) DEFAULT NULL,
  `duration` time DEFAULT NULL,
  `release_date` date DEFAULT NULL,
  `song_type` enum('Solo','Group','Remix','Cover') NOT NULL DEFAULT 'Group',
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `songs`
--

INSERT INTO `songs` (`id`, `title`, `title_jp`, `artist`, `duration`, `release_date`, `song_type`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Fighting My Way', 'Fighting My Way', '花海咲季', '00:03:21', '2024-05-16', 'Solo', 'Fighting My Way. Official solo original for Saki Hanami, Hatsuboshi Academy\'s competitive first-year idol who hates losing and pushes herself straight toward the top. It reflects her high-energy rival image, athletic drive, and proud big-sister streak.', 1, '2026-06-08 13:53:09', '2026-06-08 16:17:58'),
(2, 'Luna say maybe', 'Luna say maybe', '月村手毬', '00:04:25', '2024-05-16', 'Solo', 'Luna say maybe. Official solo original for Temari Tsukimura, the cool former elite whose sharp singing talent sits beside a difficult, fragile personality. It reflects her image as a gifted vocalist learning how to face herself again.', 1, '2026-06-08 13:53:09', '2026-06-08 16:17:58'),
(3, 'Sekai Ichi Kawaii Watashi', '世界一可愛い私', '藤田ことね', '00:03:58', '2024-05-16', 'Solo', 'Sekai Ichi Kawaii Watashi (世界一可愛い私). Official solo original for Kotone Fujita, the cheerful self-producer who wants to become the cutest idol and change her life through success. It reflects her bright ambition, practical charm, and careful sense of presentation.', 1, '2026-06-08 13:53:09', '2026-06-08 16:17:58'),
(4, 'clumsy trick', 'clumsy trick', '姫崎莉波', '00:03:55', '2024-05-16', 'Solo', 'clumsy trick. Official solo original for Rinami Himesaki, the affectionate older-sister type whose warmth and maturity are central to her idol appeal. It reflects her caring, graceful, romantic, and emotionally expressive image.', 1, '2026-06-08 13:53:09', '2026-06-08 16:17:58'),
(5, 'Tame-Lie-One-Step', 'Tame-Lie-One-Step', '紫雲清夏', '00:04:04', '2024-05-16', 'Solo', 'Tame-Lie-One-Step. Official solo original for Sumika Shiun, the stylish gyaru idol whose easygoing surface comes with strong rhythm, taste, and stage instinct. It reflects her fashionable confidence and the seriousness behind her casual attitude.', 1, '2026-06-08 13:53:09', '2026-06-08 16:17:58'),
(6, 'Koukei', '光景', '篠澤広', '00:04:34', '2024-05-16', 'Solo', 'Koukei (光景). Official solo original for Hiro Shinosawa, the frail genius whose extreme talent and physical weakness shape her unusual idol path. It reflects her delicate, intellectual, unpredictable, and quietly intense presence.', 1, '2026-06-08 13:53:09', '2026-06-08 16:17:58'),
(7, 'Hakusen', '白線', '葛城リーリヤ', '00:03:40', '2024-05-16', 'Solo', 'Hakusen (白線). Official solo original for Lilja Katsuragi, the Swedish exchange student whose gentle manners hide a sincere wish to stand on stage. It reflects her earnest grace and her growing confidence as an idol.', 1, '2026-06-08 13:53:09', '2026-06-08 16:17:58'),
(8, 'Wonder Scale', 'Wonder Scale', '倉本千奈', '00:04:35', '2024-05-16', 'Solo', 'Wonder Scale. Official solo original for China Kuramoto, the small noble girl from a wealthy family who works hard to become worthy of admiration. It reflects her polite, sheltered, determined, and charmingly earnest personality.', 1, '2026-06-08 13:53:09', '2026-06-08 16:17:58'),
(9, 'Fluorite', 'Fluorite', '有村麻央', '00:04:15', '2024-05-16', 'Solo', 'Fluorite. Official solo original for Mao Arimura, the handsome prince-type third-year idol admired for charm, poise, and stage charisma. It reflects her elegant coolness and the sensitivity beneath the princely role.', 1, '2026-06-08 13:53:09', '2026-06-08 16:17:58'),
(10, 'Hajime', '初', '初星学園', '00:05:18', '2024-05-16', 'Group', 'Hajime (初). Official Hatsuboshi Academy ensemble song for Gakuen Idolmaster. It works as a shared academy number that gathers the idols under the school setting rather than focusing on one solo route.', 1, '2026-06-08 13:53:09', '2026-06-08 16:17:58'),
(11, 'Ivy', 'アイヴイ', '月村手毬', '00:03:15', '2024-05-23', 'Solo', 'Ivy (アイヴイ). Official solo original for Temari Tsukimura, the cool former elite whose sharp singing talent sits beside a difficult, fragile personality. It reflects her image as a gifted vocalist learning how to face herself again.', 1, '2026-06-08 13:53:09', '2026-06-08 16:17:58'),
(12, 'The Rolling Riceball', 'The Rolling Riceball', '花海佑芽', '00:03:12', '2024-06-01', 'Solo', 'The Rolling Riceball. Official solo original for Ume Hanami, Saki\'s younger sister and a naturally gifted newcomer whose talent blooms with support. It reflects her energetic, straightforward nature and her fast-growing potential.', 1, '2026-06-08 13:53:09', '2026-06-08 16:17:58'),
(13, 'Kanaetai, Koto Bakari', '叶えたい、ことばかり', '月村手毬', '00:04:07', '2024-06-03', 'Solo', 'Kanaetai, Koto Bakari (叶えたい、ことばかり). Official solo original for Temari Tsukimura, the cool former elite whose sharp singing talent sits beside a difficult, fragile personality. It reflects her image as a gifted vocalist learning how to face herself again.', 1, '2026-06-08 13:53:09', '2026-06-08 16:17:58'),
(14, 'Campus mode!!', 'Campus mode!!', '初星学園', '00:04:20', '2024-06-10', 'Group', 'Campus mode!!. Official 13-idol Hatsuboshi Academy group version for Gakuen Idolmaster. It is kept as the main group version in this database, not an instrumental or alternate solo version.', 1, '2026-06-08 13:53:09', '2026-06-08 16:17:58'),
(15, 'Yellow Big Bang!', 'Yellow Big Bang！', '藤田ことね', '00:04:08', '2024-06-11', 'Solo', 'Yellow Big Bang! (Yellow Big Bang！). Official solo original for Kotone Fujita, the cheerful self-producer who wants to become the cutest idol and change her life through success. It reflects her bright ambition, practical charm, and careful sense of presentation.', 1, '2026-06-08 13:53:09', '2026-06-08 16:17:58'),
(16, 'Boom Boom Pow', 'Boom Boom Pow', '花海咲季', '00:03:21', '2024-06-20', 'Solo', 'Boom Boom Pow. Official solo original for Saki Hanami, Hatsuboshi Academy\'s competitive first-year idol who hates losing and pushes herself straight toward the top. It reflects her high-energy rival image, athletic drive, and proud big-sister streak.', 1, '2026-06-08 13:53:09', '2026-06-08 16:17:58'),
(17, 'Kimi to Semi Blue', 'キミとセミブルー', '姫崎莉波・有村麻央・紫雲清夏', '00:03:56', '2024-07-02', 'Group', 'Kimi to Semi Blue (キミとセミブルー). Official original group version performed by 姫崎莉波・有村麻央・紫雲清夏. It is linked to the group-version singers; solo full versions are stored separately as remix rows.', 1, '2026-06-08 13:53:09', '2026-06-08 16:17:58'),
(18, 'Wake up!!', 'Wake up!!', '葛城リーリヤ', '00:04:22', '2024-07-24', 'Solo', 'Wake up!!. Official solo original for Lilja Katsuragi, the Swedish exchange student whose gentle manners hide a sincere wish to stand on stage. It reflects her earnest grace and her growing confidence as an idol.', 1, '2026-06-08 13:53:09', '2026-06-08 16:17:58'),
(19, 'Akogare wo Ippai', '憧れをいっぱい', '倉本千奈', '00:04:48', '2024-08-01', 'Solo', 'Akogare wo Ippai (憧れをいっぱい). Official solo original for China Kuramoto, the small noble girl from a wealthy family who works hard to become worthy of admiration. It reflects her polite, sheltered, determined, and charmingly earnest personality.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:58'),
(20, 'Kamurigiku', '冠菊', '花海咲季・藤田ことね・葛城リーリヤ', '00:04:00', '2024-08-02', 'Group', 'Kamurigiku (冠菊). Official original group version performed by 花海咲季・藤田ことね・葛城リーリヤ. It is linked to the group-version singers; solo full versions are stored separately as remix rows.', 1, '2026-06-08 13:53:09', '2026-06-08 16:17:58'),
(21, 'Hajime [Saki Hanami Solo ver.]', '初 [花海咲季 Solo Ver.]', '花海咲季', '00:05:18', '2024-08-07', 'Remix', 'Hajime [Saki Hanami Solo ver.]. Official solo vocal version included on 花海咲季 1st Single. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:00:57', '2026-06-08 16:17:58'),
(22, 'Hajime [Temari Tsukimura Solo ver.]', '初 [月村手毬 Solo Ver.]', '月村手毬', '00:05:18', '2024-08-07', 'Remix', 'Hajime [Temari Tsukimura Solo ver.]. Official solo vocal version included on 月村手毬 1st Single. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:00:57', '2026-06-08 16:17:58'),
(23, 'Hajime [Kotone Fujita Solo ver.]', '初 [藤田ことね Solo Ver.]', '藤田ことね', '00:05:18', '2024-08-07', 'Remix', 'Hajime [Kotone Fujita Solo ver.]. Official solo vocal version included on 藤田ことね 1st Single. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:00:57', '2026-06-08 16:17:58'),
(24, 'Gamushara ni Ikou!', 'がむしゃらに行こう！', '花海咲季・月村手毬・藤田ことね', '00:03:40', '2024-08-09', 'Group', 'Gamushara ni Ikou! (がむしゃらに行こう！). Official original group version performed by 花海咲季・月村手毬・藤田ことね. It is linked to the group-version singers; solo full versions are stored separately as remix rows.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:58'),
(25, 'Kimi to Semi Blue [Saki Hanami Solo ver.]', 'キミとセミブルー [花海咲季 Solo Ver.]', '花海咲季', '00:03:56', '2024-08-14', 'Remix', 'Kimi to Semi Blue [Saki Hanami Solo ver.]. Official solo full version from Season Solo Collection Vol.1. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:58'),
(26, 'Kimi to Semi Blue [Temari Tsukimura Solo ver.]', 'キミとセミブルー [月村手毬 Solo Ver.]', '月村手毬', '00:03:56', '2024-08-14', 'Remix', 'Kimi to Semi Blue [Temari Tsukimura Solo ver.]. Official solo full version from Season Solo Collection Vol.1. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:58'),
(27, 'Kimi to Semi Blue [Kotone Fujita Solo ver.]', 'キミとセミブルー [藤田ことね Solo Ver.]', '藤田ことね', '00:03:56', '2024-08-14', 'Remix', 'Kimi to Semi Blue [Kotone Fujita Solo ver.]. Official solo full version from Season Solo Collection Vol.1. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:58'),
(28, 'Kimi to Semi Blue [Mao Arimura Solo ver.]', 'キミとセミブルー [有村麻央 Solo Ver.]', '有村麻央', '00:03:56', '2024-08-14', 'Remix', 'Kimi to Semi Blue [Mao Arimura Solo ver.]. Official solo full version from Season Solo Collection Vol.1. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:58'),
(29, 'Kimi to Semi Blue [Lilja Katsuragi Solo ver.]', 'キミとセミブルー [葛城リーリヤ Solo Ver.]', '葛城リーリヤ', '00:03:56', '2024-08-14', 'Remix', 'Kimi to Semi Blue [Lilja Katsuragi Solo ver.]. Official solo full version from Season Solo Collection Vol.1. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:58'),
(30, 'Kimi to Semi Blue [China Kuramoto Solo ver.]', 'キミとセミブルー [倉本千奈 Solo Ver.]', '倉本千奈', '00:03:56', '2024-08-14', 'Remix', 'Kimi to Semi Blue [China Kuramoto Solo ver.]. Official solo full version from Season Solo Collection Vol.1. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:58'),
(31, 'Kimi to Semi Blue [Sumika Shiun Solo ver.]', 'キミとセミブルー [紫雲清夏 Solo Ver.]', '紫雲清夏', '00:03:56', '2024-08-14', 'Remix', 'Kimi to Semi Blue [Sumika Shiun Solo ver.]. Official solo full version from Season Solo Collection Vol.1. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:58'),
(32, 'Kimi to Semi Blue [Hiro Shinosawa Solo ver.]', 'キミとセミブルー [篠澤広 Solo Ver.]', '篠澤広', '00:03:56', '2024-08-14', 'Remix', 'Kimi to Semi Blue [Hiro Shinosawa Solo ver.]. Official solo full version from Season Solo Collection Vol.1. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:58'),
(33, 'Kimi to Semi Blue [Rinami Himesaki Solo ver.]', 'キミとセミブルー [姫崎莉波 Solo Ver.]', '姫崎莉波', '00:03:56', '2024-08-14', 'Remix', 'Kimi to Semi Blue [Rinami Himesaki Solo ver.]. Official solo full version from Season Solo Collection Vol.1. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:58'),
(34, 'Kamurigiku [Saki Hanami Solo ver.]', '冠菊 [花海咲季 Solo Ver.]', '花海咲季', '00:04:00', '2024-08-14', 'Remix', 'Kamurigiku [Saki Hanami Solo ver.]. Official solo full version from Season Solo Collection Vol.2. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:58'),
(35, 'Kamurigiku [Temari Tsukimura Solo ver.]', '冠菊 [月村手毬 Solo Ver.]', '月村手毬', '00:04:00', '2024-08-14', 'Remix', 'Kamurigiku [Temari Tsukimura Solo ver.]. Official solo full version from Season Solo Collection Vol.2. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:58'),
(36, 'Kamurigiku [Kotone Fujita Solo ver.]', '冠菊 [藤田ことね Solo Ver.]', '藤田ことね', '00:04:00', '2024-08-14', 'Remix', 'Kamurigiku [Kotone Fujita Solo ver.]. Official solo full version from Season Solo Collection Vol.2. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:58'),
(37, 'Kamurigiku [Mao Arimura Solo ver.]', '冠菊 [有村麻央 Solo Ver.]', '有村麻央', '00:04:00', '2024-08-14', 'Remix', 'Kamurigiku [Mao Arimura Solo ver.]. Official solo full version from Season Solo Collection Vol.2. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:58'),
(38, 'Kamurigiku [Lilja Katsuragi Solo ver.]', '冠菊 [葛城リーリヤ Solo Ver.]', '葛城リーリヤ', '00:04:00', '2024-08-14', 'Remix', 'Kamurigiku [Lilja Katsuragi Solo ver.]. Official solo full version from Season Solo Collection Vol.2. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:58'),
(39, 'Kamurigiku [China Kuramoto Solo ver.]', '冠菊 [倉本千奈 Solo Ver.]', '倉本千奈', '00:04:00', '2024-08-14', 'Remix', 'Kamurigiku [China Kuramoto Solo ver.]. Official solo full version from Season Solo Collection Vol.2. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:58'),
(40, 'Kamurigiku [Sumika Shiun Solo ver.]', '冠菊 [紫雲清夏 Solo Ver.]', '紫雲清夏', '00:04:00', '2024-08-14', 'Remix', 'Kamurigiku [Sumika Shiun Solo ver.]. Official solo full version from Season Solo Collection Vol.2. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:58'),
(41, 'Kamurigiku [Hiro Shinosawa Solo ver.]', '冠菊 [篠澤広 Solo Ver.]', '篠澤広', '00:04:00', '2024-08-14', 'Remix', 'Kamurigiku [Hiro Shinosawa Solo ver.]. Official solo full version from Season Solo Collection Vol.2. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:58'),
(42, 'Kamurigiku [Rinami Himesaki Solo ver.]', '冠菊 [姫崎莉波 Solo Ver.]', '姫崎莉波', '00:04:00', '2024-08-14', 'Remix', 'Kamurigiku [Rinami Himesaki Solo ver.]. Official solo full version from Season Solo Collection Vol.2. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:58'),
(43, 'Howling over the World (Saki Hanami, Temari Tsukimura, Kotone Fujita ver.)', 'Howling over the World (花海咲季・月村手毬・藤田ことね ver.)', '花海咲季・月村手毬・藤田ことね', '00:04:01', '2024-08-16', 'Group', 'Official 初 TOUR commemorative group version sung by 花海咲季, 月村手毬, and 藤田ことね. Released digitally as a two-track single with the instrumental version.', 1, '2026-06-08 16:15:20', '2026-06-08 16:17:58'),
(44, 'Hibi, Hakkenteki Step!', '日々、発見的ステップ！', '倉本千奈', '00:03:15', '2024-08-23', 'Solo', 'Hibi, Hakkenteki Step! (日々、発見的ステップ！). Official solo original for China Kuramoto, the small noble girl from a wealthy family who works hard to become worthy of admiration. It reflects her polite, sheltered, determined, and charmingly earnest personality.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:58'),
(45, 'Miracle Nanau! (Saki Hanami, Temari Tsukimura, Kotone Fujita ver.)', 'ミラクルナナウ(ﾟ∀ﾟ)！(花海咲季・月村手毬・藤田ことね ver.)', '花海咲季・月村手毬・藤田ことね', '00:03:43', '2024-08-24', 'Group', 'Official 初 TOUR commemorative group version sung by 花海咲季, 月村手毬, and 藤田ことね. Released digitally with the instrumental version.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:58'),
(46, 'Hajime [Lilja Katsuragi Solo ver.]', '初 [葛城リーリヤ Solo Ver.]', '葛城リーリヤ', '00:05:18', '2024-08-28', 'Remix', 'Hajime [Lilja Katsuragi Solo ver.]. Official solo vocal version included on 葛城リーリヤ 1st Single. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:00:57', '2026-06-08 16:17:58'),
(47, 'Hajime [China Kuramoto Solo ver.]', '初 [倉本千奈 Solo Ver.]', '倉本千奈', '00:05:18', '2024-08-28', 'Remix', 'Hajime [China Kuramoto Solo ver.]. Official solo vocal version included on 倉本千奈 1st Single. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:00:57', '2026-06-08 16:17:58'),
(48, 'Hajime [Rinami Himesaki Solo ver.]', '初 [姫崎莉波 Solo Ver.]', '姫崎莉波', '00:05:18', '2024-08-28', 'Remix', 'Hajime [Rinami Himesaki Solo ver.]. Official solo vocal version included on 姫崎莉波 1st Single. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:00:57', '2026-06-08 16:17:58'),
(49, 'Howling over the World (Lilja Katsuragi, China Kuramoto, Rinami Himesaki ver.)', 'Howling over the World (葛城リーリヤ・倉本千奈・姫崎莉波 ver.)', '葛城リーリヤ・倉本千奈・姫崎莉波', '00:04:01', '2024-09-06', 'Group', 'Official 初 TOUR commemorative group version sung by 葛城リーリヤ, 倉本千奈, and 姫崎莉波. Released digitally as a two-track single with the instrumental version.', 1, '2026-06-08 16:15:20', '2026-06-08 16:17:58'),
(50, 'Miracle Nanau! (Lilja Katsuragi, China Kuramoto, Rinami Himesaki ver.)', 'ミラクルナナウ(ﾟ∀ﾟ)！(葛城リーリヤ・倉本千奈・姫崎莉波 ver.)', '葛城リーリヤ・倉本千奈・姫崎莉波', '00:03:43', '2024-09-22', 'Group', 'Official 初 TOUR commemorative group version sung by 葛城リーリヤ, 倉本千奈, and 姫崎莉波. Released digitally with the instrumental version.', 1, '2026-06-08 16:17:58', '2026-06-08 16:17:58'),
(51, 'Hajime [Sumika Shiun Solo ver.]', '初 [紫雲清夏 Solo Ver.]', '紫雲清夏', '00:05:18', '2024-09-25', 'Remix', 'Hajime [Sumika Shiun Solo ver.]. Official solo vocal version included on 紫雲清夏 1st Single. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:00:57', '2026-06-08 16:17:58'),
(52, 'Hajime [Hiro Shinosawa Solo ver.]', '初 [篠澤広 Solo Ver.]', '篠澤広', '00:05:18', '2024-09-25', 'Remix', 'Hajime [Hiro Shinosawa Solo ver.]. Official solo vocal version included on 篠澤広 1st Single. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:00:57', '2026-06-08 16:17:58'),
(53, 'Hajime [Mao Arimura Solo ver.]', '初 [有村麻央 Solo Ver.]', '有村麻央', '00:05:18', '2024-09-25', 'Remix', 'Hajime [Mao Arimura Solo ver.]. Official solo vocal version included on 有村麻央 1st Single. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:00:57', '2026-06-08 16:17:58'),
(54, 'Kasou Kyousoukyoku', '仮装狂騒曲', '月村手毬・倉本千奈・篠澤広', '00:03:13', '2024-10-01', 'Group', 'Kasou Kyousoukyoku (仮装狂騒曲). Official original group version performed by 月村手毬・倉本千奈・篠澤広. It is linked to the group-version singers; solo full versions are stored separately as remix rows.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:58'),
(55, 'Howling over the World (Mao Arimura, Sumika Shiun, Hiro Shinosawa ver.)', 'Howling over the World (有村麻央・紫雲清夏・篠澤広 ver.)', '有村麻央・紫雲清夏・篠澤広', '00:04:01', '2024-10-18', 'Group', 'Official 初 TOUR commemorative group version sung by 有村麻央, 紫雲清夏, and 篠澤広. Released digitally as a two-track single with the instrumental version.', 1, '2026-06-08 16:15:20', '2026-06-08 16:17:58'),
(56, 'Miracle Nanau! (Mao Arimura, Sumika Shiun, Hiro Shinosawa ver.)', 'ミラクルナナウ(ﾟ∀ﾟ)！(有村麻央・紫雲清夏・篠澤広 ver.)', '有村麻央・紫雲清夏・篠澤広', '00:03:43', '2024-10-26', 'Group', 'Official 初 TOUR commemorative group version sung by 有村麻央, 紫雲清夏, and 篠澤広. Released digitally with the instrumental version.', 1, '2026-06-08 16:17:58', '2026-06-08 16:17:58'),
(57, 'Kokon Touzai Chochoino Choi', '古今東西ちょちょいのちょい', '花海咲季・月村手毬・藤田ことね', '00:03:50', '2024-10-30', 'Group', 'Kokon Touzai Chochoino Choi (古今東西ちょちょいのちょい). Official original group version performed by 花海咲季・月村手毬・藤田ことね. It is linked to the group-version singers; solo full versions are stored separately as remix rows.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:58'),
(58, 'Ride on Beat', 'Ride on Beat', '紫雲清夏', '00:04:29', '2024-11-11', 'Solo', 'Ride on Beat. Official solo original for Sumika Shiun, the stylish gyaru idol whose easygoing surface comes with strong rhythm, taste, and stage instinct. It reflects her fashionable confidence and the seriousness behind her casual attitude.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:58'),
(59, 'Kasou Kyousoukyoku [Saki Hanami Solo ver.]', '仮装狂騒曲 [花海咲季 Solo Ver.]', '花海咲季', '00:03:13', '2024-11-13', 'Remix', 'Kasou Kyousoukyoku [Saki Hanami Solo ver.]. Official solo full version from Season Solo Collection Vol.3. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:58'),
(60, 'Kasou Kyousoukyoku [Temari Tsukimura Solo ver.]', '仮装狂騒曲 [月村手毬 Solo Ver.]', '月村手毬', '00:03:13', '2024-11-13', 'Remix', 'Kasou Kyousoukyoku [Temari Tsukimura Solo ver.]. Official solo full version from Season Solo Collection Vol.3. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:58'),
(61, 'Kasou Kyousoukyoku [Kotone Fujita Solo ver.]', '仮装狂騒曲 [藤田ことね Solo Ver.]', '藤田ことね', '00:03:13', '2024-11-13', 'Remix', 'Kasou Kyousoukyoku [Kotone Fujita Solo ver.]. Official solo full version from Season Solo Collection Vol.3. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:58'),
(62, 'Kasou Kyousoukyoku [Mao Arimura Solo ver.]', '仮装狂騒曲 [有村麻央 Solo Ver.]', '有村麻央', '00:03:13', '2024-11-13', 'Remix', 'Kasou Kyousoukyoku [Mao Arimura Solo ver.]. Official solo full version from Season Solo Collection Vol.3. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:58'),
(63, 'Kasou Kyousoukyoku [Lilja Katsuragi Solo ver.]', '仮装狂騒曲 [葛城リーリヤ Solo Ver.]', '葛城リーリヤ', '00:03:13', '2024-11-13', 'Remix', 'Kasou Kyousoukyoku [Lilja Katsuragi Solo ver.]. Official solo full version from Season Solo Collection Vol.3. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:58'),
(64, 'Kasou Kyousoukyoku [China Kuramoto Solo ver.]', '仮装狂騒曲 [倉本千奈 Solo Ver.]', '倉本千奈', '00:03:13', '2024-11-13', 'Remix', 'Kasou Kyousoukyoku [China Kuramoto Solo ver.]. Official solo full version from Season Solo Collection Vol.3. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:58'),
(65, 'Kasou Kyousoukyoku [Sumika Shiun Solo ver.]', '仮装狂騒曲 [紫雲清夏 Solo Ver.]', '紫雲清夏', '00:03:13', '2024-11-13', 'Remix', 'Kasou Kyousoukyoku [Sumika Shiun Solo ver.]. Official solo full version from Season Solo Collection Vol.3. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:58'),
(66, 'Kasou Kyousoukyoku [Hiro Shinosawa Solo ver.]', '仮装狂騒曲 [篠澤広 Solo Ver.]', '篠澤広', '00:03:13', '2024-11-13', 'Remix', 'Kasou Kyousoukyoku [Hiro Shinosawa Solo ver.]. Official solo full version from Season Solo Collection Vol.3. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(67, 'Kasou Kyousoukyoku [Rinami Himesaki Solo ver.]', '仮装狂騒曲 [姫崎莉波 Solo Ver.]', '姫崎莉波', '00:03:13', '2024-11-13', 'Remix', 'Kasou Kyousoukyoku [Rinami Himesaki Solo ver.]. Official solo full version from Season Solo Collection Vol.3. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(68, 'White Night! White Wish!', 'White Night! White Wish!', '花海佑芽・藤田ことね・葛城リーリヤ', '00:03:56', '2024-11-29', 'Group', 'White Night! White Wish!. Official original group version performed by 花海佑芽・藤田ことね・葛城リーリヤ. It is linked to the group-version singers; solo full versions are stored separately as remix rows.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:59'),
(69, 'Choo Choo Choo', 'Choo Choo Choo', '十王星南', '00:03:11', '2024-12-07', 'Solo', 'Choo Choo Choo. Official solo original for Sena Juo, the student council president and top-class idol with a commanding public image. It reflects her polished pride, responsibility, and habit of carrying high expectations.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:59'),
(70, 'Cosmetic', 'Cosmetic', '十王星南', '00:04:17', '2024-12-07', 'Solo', 'Cosmetic. Official solo original for Sena Juo, the student council president and top-class idol with a commanding public image. It reflects her polished pride, responsibility, and habit of carrying high expectations.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:59'),
(71, 'White Night! White Wish! [Saki Hanami Solo ver.]', 'White Night! White Wish! [花海咲季 Solo Ver.]', '花海咲季', '00:03:56', '2024-12-11', 'Remix', 'White Night! White Wish! [Saki Hanami Solo ver.]. Official solo full version from Season Solo Collection Vol.4. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(72, 'White Night! White Wish! [Temari Tsukimura Solo ver.]', 'White Night! White Wish! [月村手毬 Solo Ver.]', '月村手毬', '00:03:56', '2024-12-11', 'Remix', 'White Night! White Wish! [Temari Tsukimura Solo ver.]. Official solo full version from Season Solo Collection Vol.4. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(73, 'White Night! White Wish! [Kotone Fujita Solo ver.]', 'White Night! White Wish! [藤田ことね Solo Ver.]', '藤田ことね', '00:03:56', '2024-12-11', 'Remix', 'White Night! White Wish! [Kotone Fujita Solo ver.]. Official solo full version from Season Solo Collection Vol.4. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(74, 'White Night! White Wish! [Mao Arimura Solo ver.]', 'White Night! White Wish! [有村麻央 Solo Ver.]', '有村麻央', '00:03:56', '2024-12-11', 'Remix', 'White Night! White Wish! [Mao Arimura Solo ver.]. Official solo full version from Season Solo Collection Vol.4. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(75, 'White Night! White Wish! [Lilja Katsuragi Solo ver.]', 'White Night! White Wish! [葛城リーリヤ Solo Ver.]', '葛城リーリヤ', '00:03:56', '2024-12-11', 'Remix', 'White Night! White Wish! [Lilja Katsuragi Solo ver.]. Official solo full version from Season Solo Collection Vol.4. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(76, 'White Night! White Wish! [China Kuramoto Solo ver.]', 'White Night! White Wish! [倉本千奈 Solo Ver.]', '倉本千奈', '00:03:56', '2024-12-11', 'Remix', 'White Night! White Wish! [China Kuramoto Solo ver.]. Official solo full version from Season Solo Collection Vol.4. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(77, 'White Night! White Wish! [Sumika Shiun Solo ver.]', 'White Night! White Wish! [紫雲清夏 Solo Ver.]', '紫雲清夏', '00:03:56', '2024-12-11', 'Remix', 'White Night! White Wish! [Sumika Shiun Solo ver.]. Official solo full version from Season Solo Collection Vol.4. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(78, 'White Night! White Wish! [Hiro Shinosawa Solo ver.]', 'White Night! White Wish! [篠澤広 Solo Ver.]', '篠澤広', '00:03:56', '2024-12-11', 'Remix', 'White Night! White Wish! [Hiro Shinosawa Solo ver.]. Official solo full version from Season Solo Collection Vol.4. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(79, 'White Night! White Wish! [Rinami Himesaki Solo ver.]', 'White Night! White Wish! [姫崎莉波 Solo Ver.]', '姫崎莉波', '00:03:56', '2024-12-11', 'Remix', 'White Night! White Wish! [Rinami Himesaki Solo ver.]. Official solo full version from Season Solo Collection Vol.4. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(80, 'White Night! White Wish! [Ume Hanami Solo ver.]', 'White Night! White Wish! [花海佑芽 Solo Ver.]', '花海佑芽', '00:03:56', '2024-12-11', 'Remix', 'White Night! White Wish! [Ume Hanami Solo ver.]. Official solo full version from Season Solo Collection Vol.4. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(81, 'Kakushita Watashi', 'カクシタワタシ', '紫雲清夏', '00:03:20', '2024-12-19', 'Solo', 'Kakushita Watashi (カクシタワタシ). Official solo original for Sumika Shiun, the stylish gyaru idol whose easygoing surface comes with strong rhythm, taste, and stage instinct. It reflects her fashionable confidence and the seriousness behind her casual attitude.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:59'),
(82, 'Mekurume', 'メクルメ', '篠澤広', '00:03:17', '2024-12-21', 'Solo', 'Mekurume (メクルメ). Official solo original for Hiro Shinosawa, the frail genius whose extreme talent and physical weakness shape her unusual idol path. It reflects her delicate, intellectual, unpredictable, and quietly intense presence.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:59'),
(83, 'Hajime [Ume Hanami Solo ver.]', '初 [花海佑芽 Solo Ver.]', '花海佑芽', '00:05:18', '2025-01-15', 'Remix', 'Hajime [Ume Hanami Solo ver.]. Official solo vocal version included on 花海佑芽 1st Single. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:00:57', '2026-06-08 16:17:59'),
(84, 'Sweet Magic', 'Sweet Magic', '有村麻央', '00:03:01', '2025-01-18', 'Solo', 'Sweet Magic. Official solo original for Mao Arimura, the handsome prince-type third-year idol admired for charm, poise, and stage charisma. It reflects her elegant coolness and the sensitivity beneath the princely role.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:59'),
(85, 'Happy Mille-Feuille', 'ハッピーミルフィーユ', '篠澤広・十王星南・姫崎莉波', '00:03:16', '2025-02-02', 'Group', 'Happy Mille-Feuille (ハッピーミルフィーユ). Official original group version performed by 篠澤広・十王星南・姫崎莉波. It is linked to the group-version singers; solo full versions are stored separately as remix rows.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:59'),
(86, 'Taisetsu na Mono', 'たいせつなもの', '秦谷美鈴', '00:05:16', '2025-02-06', 'Solo', 'Taisetsu na Mono (たいせつなもの). Official solo original for Misuzu Hataya, the sleepy, elegant first-year whose relaxed pace hides a powerful presence. It reflects her gentle mystery and the surprising force she can show when she chooses to move.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:59'),
(87, 'Tsuki no Kame', 'ツキノカメ', '秦谷美鈴', '00:04:44', '2025-02-07', 'Solo', 'Tsuki no Kame (ツキノカメ). Official solo original for Misuzu Hataya, the sleepy, elegant first-year whose relaxed pace hides a powerful presence. It reflects her gentle mystery and the surprising force she can show when she chooses to move.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:59'),
(88, 'Happy Mille-Feuille [Saki Hanami Solo ver.]', 'ハッピーミルフィーユ [花海咲季 Solo Ver.]', '花海咲季', '00:03:16', '2025-02-14', 'Remix', 'Happy Mille-Feuille [Saki Hanami Solo ver.]. Official solo full version from Season Solo Collection Vol.5. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(89, 'Happy Mille-Feuille [Temari Tsukimura Solo ver.]', 'ハッピーミルフィーユ [月村手毬 Solo Ver.]', '月村手毬', '00:03:16', '2025-02-14', 'Remix', 'Happy Mille-Feuille [Temari Tsukimura Solo ver.]. Official solo full version from Season Solo Collection Vol.5. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(90, 'Happy Mille-Feuille [Kotone Fujita Solo ver.]', 'ハッピーミルフィーユ [藤田ことね Solo Ver.]', '藤田ことね', '00:03:16', '2025-02-14', 'Remix', 'Happy Mille-Feuille [Kotone Fujita Solo ver.]. Official solo full version from Season Solo Collection Vol.5. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(91, 'Happy Mille-Feuille [Mao Arimura Solo ver.]', 'ハッピーミルフィーユ [有村麻央 Solo Ver.]', '有村麻央', '00:03:16', '2025-02-14', 'Remix', 'Happy Mille-Feuille [Mao Arimura Solo ver.]. Official solo full version from Season Solo Collection Vol.5. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(92, 'Happy Mille-Feuille [Lilja Katsuragi Solo ver.]', 'ハッピーミルフィーユ [葛城リーリヤ Solo Ver.]', '葛城リーリヤ', '00:03:16', '2025-02-14', 'Remix', 'Happy Mille-Feuille [Lilja Katsuragi Solo ver.]. Official solo full version from Season Solo Collection Vol.5. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(93, 'Happy Mille-Feuille [China Kuramoto Solo ver.]', 'ハッピーミルフィーユ [倉本千奈 Solo Ver.]', '倉本千奈', '00:03:16', '2025-02-14', 'Remix', 'Happy Mille-Feuille [China Kuramoto Solo ver.]. Official solo full version from Season Solo Collection Vol.5. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(94, 'Happy Mille-Feuille [Sumika Shiun Solo ver.]', 'ハッピーミルフィーユ [紫雲清夏 Solo Ver.]', '紫雲清夏', '00:03:16', '2025-02-14', 'Remix', 'Happy Mille-Feuille [Sumika Shiun Solo ver.]. Official solo full version from Season Solo Collection Vol.5. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(95, 'Happy Mille-Feuille [Hiro Shinosawa Solo ver.]', 'ハッピーミルフィーユ [篠澤広 Solo Ver.]', '篠澤広', '00:03:16', '2025-02-14', 'Remix', 'Happy Mille-Feuille [Hiro Shinosawa Solo ver.]. Official solo full version from Season Solo Collection Vol.5. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(96, 'Happy Mille-Feuille [Rinami Himesaki Solo ver.]', 'ハッピーミルフィーユ [姫崎莉波 Solo Ver.]', '姫崎莉波', '00:03:16', '2025-02-14', 'Remix', 'Happy Mille-Feuille [Rinami Himesaki Solo ver.]. Official solo full version from Season Solo Collection Vol.5. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(97, 'Happy Mille-Feuille [Ume Hanami Solo ver.]', 'ハッピーミルフィーユ [花海佑芽 Solo Ver.]', '花海佑芽', '00:03:16', '2025-02-14', 'Remix', 'Happy Mille-Feuille [Ume Hanami Solo ver.]. Official solo full version from Season Solo Collection Vol.5. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(98, 'Happy Mille-Feuille [Sena Juo Solo ver.]', 'ハッピーミルフィーユ [十王星南 Solo Ver.]', '十王星南', '00:03:16', '2025-02-14', 'Remix', 'Happy Mille-Feuille [Sena Juo Solo ver.]. Official solo full version from Season Solo Collection Vol.5. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(99, 'Chiisana Yabou', '小さな野望', '十王星南', '00:05:26', '2025-02-19', 'Solo', 'Chiisana Yabou (小さな野望). Official solo original for Sena Juo, the student council president and top-class idol with a commanding public image. It reflects her polished pride, responsibility, and habit of carrying high expectations.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:59'),
(100, 'Hajime [Sena Juo Solo ver.]', '初 [十王星南 Solo Ver.]', '十王星南', '00:05:18', '2025-02-19', 'Remix', 'Hajime [Sena Juo Solo ver.]. Official solo vocal version included on 十王星南 1st Single. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:00:57', '2026-06-08 16:17:59'),
(101, 'Yukidoke ni', '雪解けに', '倉本千奈・有村麻央・月村手毬', '00:04:02', '2025-03-01', 'Group', 'Yukidoke ni (雪解けに). Official original group version performed by 倉本千奈・有村麻央・月村手毬. It is linked to the group-version singers; solo full versions are stored separately as remix rows.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:59'),
(102, 'Yukidoke ni [Saki Hanami Solo ver.]', '雪解けに [花海咲季 Solo Ver.]', '花海咲季', '00:04:02', '2025-03-03', 'Remix', 'Yukidoke ni [Saki Hanami Solo ver.]. Official solo full version from Season Solo Collection Vol.6. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(103, 'Yukidoke ni [Temari Tsukimura Solo ver.]', '雪解けに [月村手毬 Solo Ver.]', '月村手毬', '00:04:02', '2025-03-03', 'Remix', 'Yukidoke ni [Temari Tsukimura Solo ver.]. Official solo full version from Season Solo Collection Vol.6. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(104, 'Yukidoke ni [Kotone Fujita Solo ver.]', '雪解けに [藤田ことね Solo Ver.]', '藤田ことね', '00:04:02', '2025-03-03', 'Remix', 'Yukidoke ni [Kotone Fujita Solo ver.]. Official solo full version from Season Solo Collection Vol.6. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(105, 'Yukidoke ni [Mao Arimura Solo ver.]', '雪解けに [有村麻央 Solo Ver.]', '有村麻央', '00:04:02', '2025-03-03', 'Remix', 'Yukidoke ni [Mao Arimura Solo ver.]. Official solo full version from Season Solo Collection Vol.6. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(106, 'Yukidoke ni [Lilja Katsuragi Solo ver.]', '雪解けに [葛城リーリヤ Solo Ver.]', '葛城リーリヤ', '00:04:02', '2025-03-03', 'Remix', 'Yukidoke ni [Lilja Katsuragi Solo ver.]. Official solo full version from Season Solo Collection Vol.6. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(107, 'Yukidoke ni [China Kuramoto Solo ver.]', '雪解けに [倉本千奈 Solo Ver.]', '倉本千奈', '00:04:02', '2025-03-03', 'Remix', 'Yukidoke ni [China Kuramoto Solo ver.]. Official solo full version from Season Solo Collection Vol.6. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(108, 'Yukidoke ni [Sumika Shiun Solo ver.]', '雪解けに [紫雲清夏 Solo Ver.]', '紫雲清夏', '00:04:02', '2025-03-03', 'Remix', 'Yukidoke ni [Sumika Shiun Solo ver.]. Official solo full version from Season Solo Collection Vol.6. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(109, 'Yukidoke ni [Hiro Shinosawa Solo ver.]', '雪解けに [篠澤広 Solo Ver.]', '篠澤広', '00:04:02', '2025-03-03', 'Remix', 'Yukidoke ni [Hiro Shinosawa Solo ver.]. Official solo full version from Season Solo Collection Vol.6. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(110, 'Yukidoke ni [Rinami Himesaki Solo ver.]', '雪解けに [姫崎莉波 Solo Ver.]', '姫崎莉波', '00:04:02', '2025-03-03', 'Remix', 'Yukidoke ni [Rinami Himesaki Solo ver.]. Official solo full version from Season Solo Collection Vol.6. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(111, 'Yukidoke ni [Ume Hanami Solo ver.]', '雪解けに [花海佑芽 Solo Ver.]', '花海佑芽', '00:04:02', '2025-03-03', 'Remix', 'Yukidoke ni [Ume Hanami Solo ver.]. Official solo full version from Season Solo Collection Vol.6. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(112, 'Yukidoke ni [Sena Juo Solo ver.]', '雪解けに [十王星南 Solo Ver.]', '十王星南', '00:04:02', '2025-03-03', 'Remix', 'Yukidoke ni [Sena Juo Solo ver.]. Official solo full version from Season Solo Collection Vol.6. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(113, 'marble heart', 'marble heart', '姫崎莉波', '00:03:39', '2025-03-05', 'Solo', 'marble heart. Official solo original for Rinami Himesaki, the affectionate older-sister type whose warmth and maturity are central to her idol appeal. It reflects her caring, graceful, romantic, and emotionally expressive image.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:59'),
(114, 'EGO', 'EGO', '花海咲季', '00:04:01', '2025-03-12', 'Solo', 'EGO. Official solo original for Saki Hanami, Hatsuboshi Academy\'s competitive first-year idol who hates losing and pushes herself straight toward the top. It reflects her high-energy rival image, athletic drive, and proud big-sister streak.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:59'),
(115, 'Unhappy Light', 'Unhappy Light', '月村手毬', '00:04:08', '2025-03-12', 'Solo', 'Unhappy Light. Official solo original for Temari Tsukimura, the cool former elite whose sharp singing talent sits beside a difficult, fragile personality. It reflects her image as a gifted vocalist learning how to face herself again.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:59'),
(116, 'Fuwafuwa', 'ふわふわ', '藤田ことね', '00:03:50', '2025-03-12', 'Solo', 'Fuwafuwa (ふわふわ). Official solo original for Kotone Fujita, the cheerful self-producer who wants to become the cutest idol and change her life through success. It reflects her bright ambition, practical charm, and careful sense of presentation.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:59'),
(117, 'Gamushara ni Ikou! [Saki Hanami Solo ver.]', 'がむしゃらに行こう！ [花海咲季 Solo Ver.]', '花海咲季', '00:03:40', '2025-03-12', 'Remix', 'Gamushara ni Ikou! [Saki Hanami Solo ver.]. Official solo full version from 2nd Single solo full version. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(118, 'Gamushara ni Ikou! [Temari Tsukimura Solo ver.]', 'がむしゃらに行こう！ [月村手毬 Solo Ver.]', '月村手毬', '00:03:40', '2025-03-12', 'Remix', 'Gamushara ni Ikou! [Temari Tsukimura Solo ver.]. Official solo full version from 2nd Single solo full version. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(119, 'Gamushara ni Ikou! [Kotone Fujita Solo ver.]', 'がむしゃらに行こう！ [藤田ことね Solo Ver.]', '藤田ことね', '00:03:40', '2025-03-12', 'Remix', 'Gamushara ni Ikou! [Kotone Fujita Solo ver.]. Official solo full version from 2nd Single solo full version. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(120, 'Howling over the World [Saki Hanami Solo ver.]', 'Howling over the World [花海咲季 Solo Ver.]', '花海咲季', '00:04:05', '2025-03-12', 'Remix', 'Howling over the World [Saki Hanami Solo ver.]. Official solo full version from 2nd Single solo full version. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(121, 'Howling over the World [Temari Tsukimura Solo ver.]', 'Howling over the World [月村手毬 Solo Ver.]', '月村手毬', '00:04:05', '2025-03-12', 'Remix', 'Howling over the World [Temari Tsukimura Solo ver.]. Official solo full version from 2nd Single solo full version. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(122, 'Howling over the World [Kotone Fujita Solo ver.]', 'Howling over the World [藤田ことね Solo Ver.]', '藤田ことね', '00:04:05', '2025-03-12', 'Remix', 'Howling over the World [Kotone Fujita Solo ver.]. Official solo full version from 2nd Single solo full version. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(123, 'Miracle Nanau! [Saki Hanami Solo ver.]', 'ミラクルナナウ(ﾟ∀ﾟ)！ [花海咲季 Solo Ver.]', '花海咲季', '00:03:43', '2025-03-12', 'Remix', 'Miracle Nanau! [Saki Hanami Solo ver.]. Official solo full version from 2nd Single solo full version. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(124, 'Miracle Nanau! [Temari Tsukimura Solo ver.]', 'ミラクルナナウ(ﾟ∀ﾟ)！ [月村手毬 Solo Ver.]', '月村手毬', '00:03:43', '2025-03-12', 'Remix', 'Miracle Nanau! [Temari Tsukimura Solo ver.]. Official solo full version from 2nd Single solo full version. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(125, 'Miracle Nanau! [Kotone Fujita Solo ver.]', 'ミラクルナナウ(ﾟ∀ﾟ)！ [藤田ことね Solo Ver.]', '藤田ことね', '00:03:43', '2025-03-12', 'Remix', 'Miracle Nanau! [Kotone Fujita Solo ver.]. Official solo full version from 2nd Single solo full version. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(126, 'Contrast', 'コントラスト', '篠澤広', '00:03:38', '2025-03-19', 'Solo', 'Contrast (コントラスト). Official solo original for Hiro Shinosawa, the frail genius whose extreme talent and physical weakness shape her unusual idol path. It reflects her delicate, intellectual, unpredictable, and quietly intense presence.', 1, '2026-06-08 13:53:09', '2026-06-08 16:17:59'),
(127, 'Tokimeki no Solfege', 'ときめきのソルフェージュ', '倉本千奈', '00:03:18', '2025-03-19', 'Solo', 'Tokimeki no Solfege (ときめきのソルフェージュ). Official solo original for China Kuramoto, the small noble girl from a wealthy family who works hard to become worthy of admiration. It reflects her polite, sheltered, determined, and charmingly earnest personality.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:59'),
(128, 'Feel Jewel Dream', 'Feel Jewel Dream', '有村麻央', '00:03:22', '2025-03-19', 'Solo', 'Feel Jewel Dream. Official solo original for Mao Arimura, the handsome prince-type third-year idol admired for charm, poise, and stage charisma. It reflects her elegant coolness and the sensitivity beneath the princely role.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:59'),
(129, 'Top Secret', 'Top Secret', '有村麻央', '00:02:50', '2025-03-19', 'Solo', 'Top Secret. Official solo original for Mao Arimura, the handsome prince-type third-year idol admired for charm, poise, and stage charisma. It reflects her elegant coolness and the sensitivity beneath the princely role.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:59');
INSERT INTO `songs` (`id`, `title`, `title_jp`, `artist`, `duration`, `release_date`, `song_type`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(130, 'Contemporary no Dance', 'コンテンポラリのダンス', '篠澤広', '00:03:25', '2025-03-19', 'Solo', 'Contemporary no Dance (コンテンポラリのダンス). Official solo original for Hiro Shinosawa, the frail genius whose extreme talent and physical weakness shape her unusual idol path. It reflects her delicate, intellectual, unpredictable, and quietly intense presence.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:59'),
(131, 'Gamushara ni Ikou! [Mao Arimura Solo ver.]', 'がむしゃらに行こう！ [有村麻央 Solo Ver.]', '有村麻央', '00:03:40', '2025-03-19', 'Remix', 'Gamushara ni Ikou! [Mao Arimura Solo ver.]. Official solo full version from 2nd Single solo full version. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(132, 'Gamushara ni Ikou! [China Kuramoto Solo ver.]', 'がむしゃらに行こう！ [倉本千奈 Solo Ver.]', '倉本千奈', '00:03:40', '2025-03-19', 'Remix', 'Gamushara ni Ikou! [China Kuramoto Solo ver.]. Official solo full version from 2nd Single solo full version. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(133, 'Gamushara ni Ikou! [Hiro Shinosawa Solo ver.]', 'がむしゃらに行こう！ [篠澤広 Solo Ver.]', '篠澤広', '00:03:40', '2025-03-19', 'Remix', 'Gamushara ni Ikou! [Hiro Shinosawa Solo ver.]. Official solo full version from 2nd Single solo full version. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(134, 'Howling over the World [Mao Arimura Solo ver.]', 'Howling over the World [有村麻央 Solo Ver.]', '有村麻央', '00:04:05', '2025-03-19', 'Remix', 'Howling over the World [Mao Arimura Solo ver.]. Official solo full version from 2nd Single solo full version. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(135, 'Howling over the World [China Kuramoto Solo ver.]', 'Howling over the World [倉本千奈 Solo Ver.]', '倉本千奈', '00:04:05', '2025-03-19', 'Remix', 'Howling over the World [China Kuramoto Solo ver.]. Official solo full version from 2nd Single solo full version. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(136, 'Howling over the World [Hiro Shinosawa Solo ver.]', 'Howling over the World [篠澤広 Solo Ver.]', '篠澤広', '00:04:05', '2025-03-19', 'Remix', 'Howling over the World [Hiro Shinosawa Solo ver.]. Official solo full version from 2nd Single solo full version. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(137, 'Miracle Nanau! [Mao Arimura Solo ver.]', 'ミラクルナナウ(ﾟ∀ﾟ)！ [有村麻央 Solo Ver.]', '有村麻央', '00:03:43', '2025-03-19', 'Remix', 'Miracle Nanau! [Mao Arimura Solo ver.]. Official solo full version from 2nd Single solo full version. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(138, 'Miracle Nanau! [China Kuramoto Solo ver.]', 'ミラクルナナウ(ﾟ∀ﾟ)！ [倉本千奈 Solo Ver.]', '倉本千奈', '00:03:43', '2025-03-19', 'Remix', 'Miracle Nanau! [China Kuramoto Solo ver.]. Official solo full version from 2nd Single solo full version. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(139, 'Miracle Nanau! [Hiro Shinosawa Solo ver.]', 'ミラクルナナウ(ﾟ∀ﾟ)！ [篠澤広 Solo Ver.]', '篠澤広', '00:03:43', '2025-03-19', 'Remix', 'Miracle Nanau! [Hiro Shinosawa Solo ver.]. Official solo full version from 2nd Single solo full version. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(140, 'Kyokkou', '極光', '葛城リーリヤ', '00:03:33', '2025-03-22', 'Solo', 'Kyokkou (極光). Official solo original for Lilja Katsuragi, the Swedish exchange student whose gentle manners hide a sincere wish to stand on stage. It reflects her earnest grace and her growing confidence as an idol.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:59'),
(141, 'L.U.V', 'L.U.V', '姫崎莉波', '00:04:11', '2025-03-26', 'Solo', 'L.U.V. Official solo original for Rinami Himesaki, the affectionate older-sister type whose warmth and maturity are central to her idol appeal. It reflects her caring, graceful, romantic, and emotionally expressive image.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:59'),
(142, 'Fragile Heart', 'Fragile Heart', '葛城リーリヤ', '00:04:04', '2025-03-26', 'Solo', 'Fragile Heart. Official solo original for Lilja Katsuragi, the Swedish exchange student whose gentle manners hide a sincere wish to stand on stage. It reflects her earnest grace and her growing confidence as an idol.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:59'),
(143, 'Utagoe wa Kimi Iro', '歌声は君いろ', '姫崎莉波', '00:04:38', '2025-03-26', 'Solo', 'Utagoe wa Kimi Iro (歌声は君いろ). Official solo original for Rinami Himesaki, the affectionate older-sister type whose warmth and maturity are central to her idol appeal. It reflects her caring, graceful, romantic, and emotionally expressive image.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:59'),
(144, 'Kira Kira', 'Kira Kira', '紫雲清夏', '00:03:06', '2025-03-26', 'Solo', 'Kira Kira. Official solo original for Sumika Shiun, the stylish gyaru idol whose easygoing surface comes with strong rhythm, taste, and stage instinct. It reflects her fashionable confidence and the seriousness behind her casual attitude.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:59'),
(145, 'Gamushara ni Ikou! [Lilja Katsuragi Solo ver.]', 'がむしゃらに行こう！ [葛城リーリヤ Solo Ver.]', '葛城リーリヤ', '00:03:40', '2025-03-26', 'Remix', 'Gamushara ni Ikou! [Lilja Katsuragi Solo ver.]. Official solo full version from 2nd Single solo full version. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(146, 'Gamushara ni Ikou! [Sumika Shiun Solo ver.]', 'がむしゃらに行こう！ [紫雲清夏 Solo Ver.]', '紫雲清夏', '00:03:40', '2025-03-26', 'Remix', 'Gamushara ni Ikou! [Sumika Shiun Solo ver.]. Official solo full version from 2nd Single solo full version. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(147, 'Gamushara ni Ikou! [Rinami Himesaki Solo ver.]', 'がむしゃらに行こう！ [姫崎莉波 Solo Ver.]', '姫崎莉波', '00:03:40', '2025-03-26', 'Remix', 'Gamushara ni Ikou! [Rinami Himesaki Solo ver.]. Official solo full version from 2nd Single solo full version. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(148, 'Howling over the World [Lilja Katsuragi Solo ver.]', 'Howling over the World [葛城リーリヤ Solo Ver.]', '葛城リーリヤ', '00:04:05', '2025-03-26', 'Remix', 'Howling over the World [Lilja Katsuragi Solo ver.]. Official solo full version from 2nd Single solo full version. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(149, 'Howling over the World [Sumika Shiun Solo ver.]', 'Howling over the World [紫雲清夏 Solo Ver.]', '紫雲清夏', '00:04:05', '2025-03-26', 'Remix', 'Howling over the World [Sumika Shiun Solo ver.]. Official solo full version from 2nd Single solo full version. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(150, 'Howling over the World [Rinami Himesaki Solo ver.]', 'Howling over the World [姫崎莉波 Solo Ver.]', '姫崎莉波', '00:04:05', '2025-03-26', 'Remix', 'Howling over the World [Rinami Himesaki Solo ver.]. Official solo full version from 2nd Single solo full version. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(151, 'Miracle Nanau! [Lilja Katsuragi Solo ver.]', 'ミラクルナナウ(ﾟ∀ﾟ)！ [葛城リーリヤ Solo Ver.]', '葛城リーリヤ', '00:03:43', '2025-03-26', 'Remix', 'Miracle Nanau! [Lilja Katsuragi Solo ver.]. Official solo full version from 2nd Single solo full version. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(152, 'Miracle Nanau! [Sumika Shiun Solo ver.]', 'ミラクルナナウ(ﾟ∀ﾟ)！ [紫雲清夏 Solo Ver.]', '紫雲清夏', '00:03:43', '2025-03-26', 'Remix', 'Miracle Nanau! [Sumika Shiun Solo ver.]. Official solo full version from 2nd Single solo full version. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(153, 'Miracle Nanau! [Rinami Himesaki Solo ver.]', 'ミラクルナナウ(ﾟ∀ﾟ)！ [姫崎莉波 Solo Ver.]', '姫崎莉波', '00:03:43', '2025-03-26', 'Remix', 'Miracle Nanau! [Rinami Himesaki Solo ver.]. Official solo full version from 2nd Single solo full version. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(154, 'Tsuyotsuyo Saikyou Exercise', 'つよつよ最強エクササイズ', '花海佑芽', '00:03:05', '2025-04-01', 'Solo', 'Tsuyotsuyo Saikyou Exercise (つよつよ最強エクササイズ). Official solo original for Ume Hanami, Saki\'s younger sister and a naturally gifted newcomer whose talent blooms with support. It reflects her energetic, straightforward nature and her fast-growing potential.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:59'),
(155, 'Try it now', 'Try it now', '花海咲季', '00:03:45', '2025-04-02', 'Solo', 'Try it now. Official solo original for Saki Hanami, Hatsuboshi Academy\'s competitive first-year idol who hates losing and pushes herself straight toward the top. It reflects her high-energy rival image, athletic drive, and proud big-sister streak.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:59'),
(156, 'Sakura Photograph', '桜フォトグラフ', '葛城リーリヤ・紫雲清夏・花海咲季', '00:04:42', '2025-04-02', 'Group', 'Sakura Photograph (桜フォトグラフ). Official original group version performed by 葛城リーリヤ・紫雲清夏・花海咲季. It is linked to the group-version singers; solo full versions are stored separately as remix rows.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:59'),
(157, 'Sakura Photograph [Saki Hanami Solo ver.]', '桜フォトグラフ [花海咲季 Solo Ver.]', '花海咲季', '00:04:42', '2025-04-09', 'Remix', 'Sakura Photograph [Saki Hanami Solo ver.]. Official solo full version from Season Solo Collection Vol.7. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(158, 'Sakura Photograph [Temari Tsukimura Solo ver.]', '桜フォトグラフ [月村手毬 Solo Ver.]', '月村手毬', '00:04:42', '2025-04-09', 'Remix', 'Sakura Photograph [Temari Tsukimura Solo ver.]. Official solo full version from Season Solo Collection Vol.7. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(159, 'Sakura Photograph [Kotone Fujita Solo ver.]', '桜フォトグラフ [藤田ことね Solo Ver.]', '藤田ことね', '00:04:42', '2025-04-09', 'Remix', 'Sakura Photograph [Kotone Fujita Solo ver.]. Official solo full version from Season Solo Collection Vol.7. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(160, 'Sakura Photograph [Mao Arimura Solo ver.]', '桜フォトグラフ [有村麻央 Solo Ver.]', '有村麻央', '00:04:42', '2025-04-09', 'Remix', 'Sakura Photograph [Mao Arimura Solo ver.]. Official solo full version from Season Solo Collection Vol.7. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(161, 'Sakura Photograph [Lilja Katsuragi Solo ver.]', '桜フォトグラフ [葛城リーリヤ Solo Ver.]', '葛城リーリヤ', '00:04:42', '2025-04-09', 'Remix', 'Sakura Photograph [Lilja Katsuragi Solo ver.]. Official solo full version from Season Solo Collection Vol.7. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(162, 'Sakura Photograph [China Kuramoto Solo ver.]', '桜フォトグラフ [倉本千奈 Solo Ver.]', '倉本千奈', '00:04:42', '2025-04-09', 'Remix', 'Sakura Photograph [China Kuramoto Solo ver.]. Official solo full version from Season Solo Collection Vol.7. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(163, 'Sakura Photograph [Sumika Shiun Solo ver.]', '桜フォトグラフ [紫雲清夏 Solo Ver.]', '紫雲清夏', '00:04:42', '2025-04-09', 'Remix', 'Sakura Photograph [Sumika Shiun Solo ver.]. Official solo full version from Season Solo Collection Vol.7. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(164, 'Sakura Photograph [Hiro Shinosawa Solo ver.]', '桜フォトグラフ [篠澤広 Solo Ver.]', '篠澤広', '00:04:42', '2025-04-09', 'Remix', 'Sakura Photograph [Hiro Shinosawa Solo ver.]. Official solo full version from Season Solo Collection Vol.7. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(165, 'Sakura Photograph [Rinami Himesaki Solo ver.]', '桜フォトグラフ [姫崎莉波 Solo Ver.]', '姫崎莉波', '00:04:42', '2025-04-09', 'Remix', 'Sakura Photograph [Rinami Himesaki Solo ver.]. Official solo full version from Season Solo Collection Vol.7. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(166, 'Sakura Photograph [Ume Hanami Solo ver.]', '桜フォトグラフ [花海佑芽 Solo Ver.]', '花海佑芽', '00:04:44', '2025-04-09', 'Remix', 'Sakura Photograph [Ume Hanami Solo ver.]. Official solo full version from Season Solo Collection Vol.7. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(167, 'Sakura Photograph [Sena Juo Solo ver.]', '桜フォトグラフ [十王星南 Solo Ver.]', '十王星南', '00:04:42', '2025-04-09', 'Remix', 'Sakura Photograph [Sena Juo Solo ver.]. Official solo full version from Season Solo Collection Vol.7. It is stored as remix because it is an alternate solo vocal version of an original group song, not the idol\'s main solo original.', 1, '2026-06-08 15:41:10', '2026-06-08 16:17:59'),
(168, 'Our Chant', 'Our Chant', '十王星南', '00:03:54', '2025-04-23', 'Solo', 'Our Chant. Official solo original for Sena Juo, the student council president and top-class idol with a commanding public image. It reflects her polished pride, responsibility, and habit of carrying high expectations.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:59'),
(169, 'The Cute!!!', 'The Cute!!!', '藤田ことね', '00:03:52', '2025-04-29', 'Solo', 'The Cute!!!. Official solo original for Kotone Fujita, the cheerful self-producer who wants to become the cutest idol and change her life through success. It reflects her bright ambition, practical charm, and careful sense of presentation.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:59'),
(170, 'Ameagari no Iris', '雨上がりのアイリス', 'Re;IRIS', '00:04:01', '2025-05-02', 'Group', 'Ameagari no Iris (雨上がりのアイリス). Official Re;IRIS unit song performed by Saki Hanami, Temari Tsukimura, and Kotone Fujita. It centers on three first-year rivals and their different approaches to becoming idols.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:59'),
(171, 'Shirushi', '標', '初星学園', '00:03:47', '2025-05-17', 'Group', 'Shirushi (標). Official Hatsuboshi Academy ensemble song for Gakuen Idolmaster. It works as a shared academy number that gathers the idols under the school setting rather than focusing on one solo route.', 1, '2026-06-08 13:53:09', '2026-06-08 16:17:59'),
(172, 'Hajime [Misuzu Hataya Solo ver.]', '初 [秦谷美鈴 Solo Ver.]', '秦谷美鈴', '00:05:18', '2025-05-21', 'Remix', 'Hajime [Misuzu Hataya Solo ver.]. Official solo vocal version included on 秦谷美鈴 1st Single. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:00:57', '2026-06-08 16:17:59'),
(173, 'Love & Joy', 'Love & Joy', '紫雲清夏', '00:03:55', '2025-06-20', 'Solo', 'Love & Joy. Official solo original for Sumika Shiun, the stylish gyaru idol whose easygoing surface comes with strong rhythm, taste, and stage instinct. It reflects her fashionable confidence and the seriousness behind her casual attitude.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:59'),
(174, 'Howling over the World (Ume Hanami, Misuzu Hataya, Sena Juo ver.)', 'Howling over the World (花海佑芽・秦谷美鈴・十王星南 ver.)', '花海佑芽・秦谷美鈴・十王星南', '00:04:01', '2025-07-12', 'Group', 'Official group version sung by 花海佑芽, 秦谷美鈴, and 十王星南. This fixes the previous row that had the correct 2025-07-12 release date but the wrong original artists.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:59'),
(175, 'Sunfaded', 'サンフェーデッド', '篠澤広', '00:03:32', '2025-07-18', 'Solo', 'Sunfaded (サンフェーデッド). Official solo original for Hiro Shinosawa, the frail genius whose extreme talent and physical weakness shape her unusual idol path. It reflects her delicate, intellectual, unpredictable, and quietly intense presence.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:59'),
(176, 'Miracle Nanau! (Ume Hanami, Misuzu Hataya, Sena Juo ver.)', 'ミラクルナナウ(ﾟ∀ﾟ)！(花海佑芽・秦谷美鈴・十王星南 ver.)', '花海佑芽・秦谷美鈴・十王星南', '00:03:43', '2025-07-19', 'Group', 'Official group version sung by 花海佑芽, 秦谷美鈴, and 十王星南. Released digitally with the instrumental version.', 1, '2026-06-08 16:17:58', '2026-06-08 16:17:59'),
(177, 'ENDLESS DANCE (Ume Hanami, Misuzu Hataya, Sena Juo ver.)', 'ENDLESS DANCE (花海佑芽・秦谷美鈴・十王星南 ver.)', '花海佑芽・秦谷美鈴・十王星南', '00:03:22', '2025-07-26', 'Group', 'ENDLESS DANCE. Official original group version performed by 花海佑芽・秦谷美鈴・十王星南. It is linked to the group-version singers; solo full versions are stored separately as remix rows.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:59'),
(178, 'ENDLESS DANCE (Saki Hanami, Temari Tsukimura, Kotone Fujita ver.)', 'ENDLESS DANCE (花海咲季・月村手毬・藤田ことね ver.)', '花海咲季・月村手毬・藤田ことね', '00:03:22', '2025-07-26', 'Group', 'ENDLESS DANCE (Saki Hanami, Temari Tsukimura, Kotone Fujita ver.). Official group version released digitally on 2025-07-26.', 1, '2026-06-08 16:17:59', '2026-06-08 16:17:59'),
(179, 'Star-mine', 'Star-mine', 'Begrazia', '00:03:59', '2025-08-01', 'Group', 'Star-mine. Official Begrazia unit song performed by Ume Hanami, Misuzu Hataya, and Sena Juo. It mixes Ume\'s energy, Misuzu\'s quiet depth, and Sena\'s commanding polish.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:59'),
(180, 'ENDLESS DANCE (Lilja Katsuragi, China Kuramoto, Rinami Himesaki ver.)', 'ENDLESS DANCE (葛城リーリヤ・倉本千奈・姫崎莉波 ver.)', '葛城リーリヤ・倉本千奈・姫崎莉波', '00:03:22', '2025-08-02', 'Group', 'ENDLESS DANCE (Lilja Katsuragi, China Kuramoto, Rinami Himesaki ver.). Official group version released digitally on 2025-08-02.', 1, '2026-06-08 16:17:59', '2026-06-08 16:17:59'),
(181, 'GO MY WAY!! -Kotone Fujita cover-', 'GO MY WAY!! -藤田ことね cover-', '藤田ことね', '00:04:50', '2025-08-07', 'Cover', 'GO MY WAY!! -藤田ことね cover-. Official Gakuen Idolmaster GOLD RUSH cover CD song for Kotone Fujita. It covers the classic THE IDOLM@STER song through Kotone\'s bright, ambitious idol image.', 1, '2026-06-08 15:30:10', '2026-06-08 16:17:59'),
(182, 'ENDLESS DANCE (Mao Arimura, Sumika Shiun, Hiro Shinosawa ver.)', 'ENDLESS DANCE (有村麻央・紫雲清夏・篠澤広 ver.)', '有村麻央・紫雲清夏・篠澤広', '00:03:22', '2025-08-09', 'Group', 'ENDLESS DANCE (Mao Arimura, Sumika Shiun, Hiro Shinosawa ver.). Official group version released digitally on 2025-08-09.', 1, '2026-06-08 16:17:59', '2026-06-08 16:17:59'),
(183, 'Jiko Kouteikan Bakuage Shuki Shuki Song', '自己肯定感爆上げ↑↑しゅきしゅきソング', '藤田ことね', '00:03:55', '2025-08-13', 'Solo', 'Jiko Kouteikan Bakuage Shuki Shuki Song (自己肯定感爆上げ↑↑しゅきしゅきソング). Official solo original for Kotone Fujita, the cheerful self-producer who wants to become the cutest idol and change her life through success. It reflects her bright ambition, practical charm, and careful sense of presentation.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:59'),
(184, 'SUPREMACY', 'SUPREMACY', '1年1組', '00:03:51', '2025-08-29', 'Group', 'SUPREMACY. Official class song for Class 1-1. It brings together Saki, Temari, Kotone, Lilja, and Sumika, connecting their daily academy life with their stage work.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:59'),
(185, 'Let’s GO!! ICHI-NO-NI!!', 'Let’s GO!! ICHI-NO-NI!!', '1年2組', '00:03:28', '2025-09-05', 'Group', 'Let’s GO!! ICHI-NO-NI!!. Official class song for Class 1-2. It brings together China, Hiro, Misuzu, and Ume, four very different first-year personalities.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:59'),
(186, 'Naiwa', 'ナイワ', '3年1組', '00:03:07', '2025-09-11', 'Group', 'Naiwa (ナイワ). Official class song for Class 3-1. It focuses on Mao, Rinami, Sena, and Tsubame, giving the senior class a more mature upperclassman presence.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:59'),
(187, '36℃ U･B･U', '36℃ U･B･U', '姫崎莉波', '00:04:12', '2025-09-18', 'Solo', '36℃ U･B･U. Official solo original for Rinami Himesaki, the affectionate older-sister type whose warmth and maturity are central to her idol appeal. It reflects her caring, graceful, romantic, and emotionally expressive image.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:59'),
(188, 'Sora to Yakusoku', '空と約束', '倉本千奈', '00:04:54', '2025-10-22', 'Solo', 'Sora to Yakusoku (空と約束). Official solo original for China Kuramoto, the small noble girl from a wealthy family who works hard to become worthy of admiration. It reflects her polite, sheltered, determined, and charmingly earnest personality.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:59'),
(189, 'Mite', '見て', '有村麻央', '00:05:03', '2025-11-01', 'Solo', 'Mite (見て). Official solo original for Mao Arimura, the handsome prince-type third-year idol admired for charm, poise, and stage charisma. It reflects her elegant coolness and the sensitivity beneath the princely role.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:59'),
(190, 'Katamari on the Doon', 'カタマリオンザドゥン', '花海咲季', '00:04:20', '2025-11-13', 'Solo', 'Katamari on the Doon (カタマリオンザドゥン). Official solo original for Saki Hanami, Hatsuboshi Academy\'s competitive first-year idol who hates losing and pushes herself straight toward the top. It reflects her high-energy rival image, athletic drive, and proud big-sister streak.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:59'),
(191, 'Riron Busou Shite', '理論武装して', '雨夜燕', '00:03:32', '2025-11-16', 'Solo', 'Riron Busou Shite (理論武装して). Official solo original for Tsubame Amaya, the poised senior idol connected to the student council circle and the academy\'s upperclassman presence. It reflects her composed, observant, and quietly distinctive stage image.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:59'),
(192, 'Ameagari no Iris [Saki Hanami Solo ver.]', '雨上がりのアイリス [花海咲季 Solo Ver.]', '花海咲季', '00:04:01', '2025-11-19', 'Remix', 'Ameagari no Iris [Saki Hanami Solo ver.]. Official solo vocal version included on Re;IRIS 1st Single「雨上がりのアイリス」. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:00:57', '2026-06-08 16:17:59'),
(193, 'Ameagari no Iris [Temari Tsukimura Solo ver.]', '雨上がりのアイリス [月村手毬 Solo Ver.]', '月村手毬', '00:04:01', '2025-11-19', 'Remix', 'Ameagari no Iris [Temari Tsukimura Solo ver.]. Official solo vocal version included on Re;IRIS 1st Single「雨上がりのアイリス」. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:00:57', '2026-06-08 16:17:59'),
(194, 'Ameagari no Iris [Kotone Fujita Solo ver.]', '雨上がりのアイリス [藤田ことね Solo Ver.]', '藤田ことね', '00:04:01', '2025-11-19', 'Remix', 'Ameagari no Iris [Kotone Fujita Solo ver.]. Official solo vocal version included on Re;IRIS 1st Single「雨上がりのアイリス」. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:00:57', '2026-06-08 16:17:59'),
(195, 'Star-mine [Ume Hanami Solo ver.]', 'Star-mine [花海佑芽 Solo Ver.]', '花海佑芽', '00:03:59', '2025-11-19', 'Remix', 'Star-mine [Ume Hanami Solo ver.]. Official solo vocal version included on Begrazia 1st Single「Star-mine」. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:00:57', '2026-06-08 16:17:59'),
(196, 'Star-mine [Misuzu Hataya Solo ver.]', 'Star-mine [秦谷美鈴 Solo Ver.]', '秦谷美鈴', '00:03:59', '2025-11-19', 'Remix', 'Star-mine [Misuzu Hataya Solo ver.]. Official solo vocal version included on Begrazia 1st Single「Star-mine」. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:00:57', '2026-06-08 16:17:59'),
(197, 'Star-mine [Sena Juo Solo ver.]', 'Star-mine [十王星南 Solo Ver.]', '十王星南', '00:03:59', '2025-11-19', 'Remix', 'Star-mine [Sena Juo Solo ver.]. Official solo vocal version included on Begrazia 1st Single「Star-mine」. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:00:57', '2026-06-08 16:17:59'),
(198, 'Guu Suu Pii', 'グースーピー', '花海佑芽', '00:03:02', '2025-11-29', 'Solo', 'Guu Suu Pii (グースーピー). Official solo original for Ume Hanami, Saki\'s younger sister and a naturally gifted newcomer whose talent blooms with support. It reflects her energetic, straightforward nature and her fast-growing potential.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:59'),
(199, 'Atmosphere', 'Atmosphere', '葛城リーリヤ', '00:04:31', '2025-12-09', 'Solo', 'Atmosphere. Official solo original for Lilja Katsuragi, the Swedish exchange student whose gentle manners hide a sincere wish to stand on stage. It reflects her earnest grace and her growing confidence as an idol.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:59'),
(200, 'Hatsuboshi Taisou Daiichi', '初星体操第一', 'Artist not listed', '00:02:02', '2025-12-17', 'Group', 'Hatsuboshi Taisou Daiichi (初星体操第一). Official Hatsuboshi Academy exercise-style song. It reads as a light academy routine number connected to school life rather than a private solo song.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:59'),
(201, 'Superlative', 'Superlative', '秦谷美鈴', '00:05:04', '2026-01-06', 'Solo', 'Superlative. Official solo original for Misuzu Hataya, the sleepy, elegant first-year whose relaxed pace hides a powerful presence. It reflects her gentle mystery and the surprising force she can show when she chooses to move.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:59'),
(202, 'Ittai Itsu Kara', '一体いつから', '月村手毬', '00:04:14', '2026-01-17', 'Solo', 'Ittai Itsu Kara (一体いつから). Official solo original for Temari Tsukimura, the cool former elite whose sharp singing talent sits beside a difficult, fragile personality. It reflects her image as a gifted vocalist learning how to face herself again.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:59'),
(203, 'Michi Naru Hirogaru', 'みちなるひろがる', 'ゆめぱしー (倉本千奈・篠澤広)', '00:03:25', '2026-01-28', 'Group', 'Michi Naru Hirogaru (みちなるひろがる). Official duet song for China Kuramoto and Hiro Shinosawa. It pairs China\'s earnest noble-girl determination with Hiro\'s fragile genius and unusual sensitivity.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:59'),
(204, 'Tokimeki Emotion', 'ときめきエモーション', 'REVERSI (紫雲清夏・葛城リーリヤ)', '00:03:27', '2026-01-28', 'Group', 'Tokimeki Emotion (ときめきエモーション). Official duet song for Sumika Shiun and Lilja Katsuragi. It pairs Sumika\'s stylish confidence with Lilja\'s gentle sincerity.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:59'),
(205, 'Shirushi [Sumika Shiun Solo Ver.]', '標 [紫雲清夏 Solo Ver.]', '紫雲清夏', '00:03:29', '2026-01-28', 'Remix', 'Shirushi (標) [Sumika Shiun Solo Ver.]. CD-exclusive solo vocal version of the Hatsuboshi Academy school anthem. Included on 紫雲清夏\'s 3rd Single. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:17:59', '2026-06-08 16:17:59'),
(206, 'Shirushi [Hiro Shinosawa Solo Ver.]', '標 [篠澤広 Solo Ver.]', '篠澤広', '00:03:29', '2026-01-28', 'Remix', 'Shirushi (標) [Hiro Shinosawa Solo Ver.]. CD-exclusive solo vocal version of the Hatsuboshi Academy school anthem. Included on 篠澤広\'s 3rd Single. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:17:59', '2026-06-08 16:17:59'),
(207, 'Shirushi [Kotone Fujita Solo Ver.]', '標 [藤田ことね Solo Ver.]', '藤田ことね', '00:03:29', '2026-01-28', 'Remix', 'Shirushi (標) [Kotone Fujita Solo Ver.]. CD-exclusive solo vocal version of the Hatsuboshi Academy school anthem. Included on 藤田ことね\'s 3rd Single. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:17:59', '2026-06-08 16:17:59'),
(208, 'Shirushi [Rinami Himesaki Solo Ver.]', '標 [姫崎莉波 Solo Ver.]', '姫崎莉波', '00:03:29', '2026-01-28', 'Remix', 'Shirushi (標) [Rinami Himesaki Solo Ver.]. CD-exclusive solo vocal version of the Hatsuboshi Academy school anthem. Included on 姫崎莉波\'s 3rd Single. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:17:59', '2026-06-08 16:17:59'),
(209, 'ENDLESS DANCE [Sumika Shiun Solo Ver.]', 'ENDLESS DANCE [紫雲清夏 Solo Ver.]', '紫雲清夏', '00:03:22', '2026-01-28', 'Remix', 'ENDLESS DANCE [Sumika Shiun Solo Ver.]. CD-exclusive solo vocal version included on 紫雲清夏\'s 3rd Single. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:17:59', '2026-06-08 16:17:59'),
(210, 'ENDLESS DANCE [Hiro Shinosawa Solo Ver.]', 'ENDLESS DANCE [篠澤広 Solo Ver.]', '篠澤広', '00:03:22', '2026-01-28', 'Remix', 'ENDLESS DANCE [Hiro Shinosawa Solo Ver.]. CD-exclusive solo vocal version included on 篠澤広\'s 3rd Single. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:17:59', '2026-06-08 16:17:59'),
(211, 'ENDLESS DANCE [Kotone Fujita Solo Ver.]', 'ENDLESS DANCE [藤田ことね Solo Ver.]', '藤田ことね', '00:03:22', '2026-01-28', 'Remix', 'ENDLESS DANCE [Kotone Fujita Solo Ver.]. CD-exclusive solo vocal version included on 藤田ことね\'s 3rd Single. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:17:59', '2026-06-08 16:17:59'),
(212, 'ENDLESS DANCE [Rinami Himesaki Solo Ver.]', 'ENDLESS DANCE [姫崎莉波 Solo Ver.]', '姫崎莉波', '00:03:22', '2026-01-28', 'Remix', 'ENDLESS DANCE [Rinami Himesaki Solo Ver.]. CD-exclusive solo vocal version included on 姫崎莉波\'s 3rd Single. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:17:59', '2026-06-08 16:17:59'),
(213, 'SEARCH RIGHT', 'SEARCH RIGHT', '初星学園生徒会', '00:03:24', '2026-02-08', 'Group', 'SEARCH RIGHT. Official student council song for the Hatsuboshi Academy student council members. It is tied to the academy leadership group and its polished public image.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:59'),
(214, 'Sekirara', '赤裸々', '十王星南', '00:04:25', '2026-02-10', 'Solo', 'Sekirara (赤裸々). Official solo original for Sena Juo, the student council president and top-class idol with a commanding public image. It reflects her polished pride, responsibility, and habit of carrying high expectations.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:59'),
(215, 'Wakashi, Sawagashi, Ska Punk', 'わかし・さわがし・スカパンク', '花海佑芽・秦谷美鈴・十王星南', '00:03:23', '2026-02-19', 'Group', 'Wakashi, Sawagashi, Ska Punk (わかし・さわがし・スカパンク). Official trio song for Ume Hanami, Misuzu Hataya, and Sena Juo. It balances Ume\'s momentum, Misuzu\'s sleepy elegance, and Sena\'s disciplined star presence.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:59'),
(216, 'Yorunite', 'ヨルニテ', '秦谷美鈴', '00:03:39', '2026-02-25', 'Solo', 'Yorunite (ヨルニテ). Official solo original for Misuzu Hataya, the sleepy, elegant first-year whose relaxed pace hides a powerful presence. It reflects her gentle mystery and the surprising force she can show when she chooses to move.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:59'),
(217, 'Kin no Ono, Gin no Ono, Emerald no Ono', '金の斧、銀の斧、エメラルドの斧', '花海佑芽', '00:03:17', '2026-02-25', 'Solo', 'Kin no Ono, Gin no Ono, Emerald no Ono (金の斧、銀の斧、エメラルドの斧). Official solo original for Ume Hanami, Saki\'s younger sister and a naturally gifted newcomer whose talent blooms with support. It reflects her energetic, straightforward nature and her fast-growing potential.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:59'),
(218, 'Gamushara ni Ikou! [Sena Juo Solo ver.]', 'がむしゃらに行こう！ [十王星南 Solo Ver.]', '十王星南', '00:04:24', '2026-02-25', 'Remix', 'Gamushara ni Ikou! [Sena Juo Solo ver.]. Official solo vocal version of the group song, included on 十王星南 2nd Single「Our Chant」. Stored as remix because it is an alternate solo full version rather than the original group recording.', 1, '2026-06-08 15:57:25', '2026-06-08 16:17:59'),
(219, 'Howling over the World [Sena Juo Solo ver.]', 'Howling over the World [十王星南 Solo Ver.]', '十王星南', '00:04:20', '2026-02-25', 'Remix', 'Howling over the World [Sena Juo Solo ver.]. Official solo vocal version of the group song, included on 十王星南 2nd Single「Our Chant」. Stored as remix because it is an alternate solo full version rather than the original group recording.', 1, '2026-06-08 15:57:25', '2026-06-08 16:17:59'),
(220, 'Miracle Nanau! [Sena Juo Solo ver.]', 'ミラクルナナウ(ﾟ∀ﾟ)！ [十王星南 Solo Ver.]', '十王星南', '00:03:43', '2026-02-25', 'Remix', 'Official solo vocal full version included on 十王星南 2nd Single「Our Chant」. Stored as remix because it is an alternate solo version of the group song.', 1, '2026-06-08 15:57:25', '2026-06-08 16:17:59'),
(221, 'Hajime [Tsubame Amaya Solo ver.]', '初 [雨夜燕 Solo Ver.]', '雨夜燕', '00:05:18', '2026-02-25', 'Remix', 'Hajime [Tsubame Amaya Solo ver.]. Official solo vocal version included on 雨夜燕 1st Single. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:00:57', '2026-06-08 16:17:59'),
(222, 'Kachi Doki', 'かちドキ', '藤田ことね', '00:04:01', '2026-03-01', 'Solo', 'Kachi Doki (かちドキ). Official solo original for Kotone Fujita, the cheerful self-producer who wants to become the cutest idol and change her life through success. It reflects her bright ambition, practical charm, and careful sense of presentation.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:59'),
(223, 'Search Right [Saki Hanami Solo ver.]', 'SEARCH RIGHT [花海咲季 Solo Ver.]', '花海咲季', '00:03:24', '2026-03-02', 'Remix', 'Search Right [Saki Hanami Solo ver.]. Official solo vocal version included on SEARCH RIGHT -Solo Version Album-. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:00:57', '2026-06-08 16:17:59'),
(224, 'Search Right [Temari Tsukimura Solo ver.]', 'SEARCH RIGHT [月村手毬 Solo Ver.]', '月村手毬', '00:03:24', '2026-03-02', 'Remix', 'Search Right [Temari Tsukimura Solo ver.]. Official solo vocal version included on SEARCH RIGHT -Solo Version Album-. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:00:57', '2026-06-08 16:17:59'),
(225, 'Search Right [Kotone Fujita Solo ver.]', 'SEARCH RIGHT [藤田ことね Solo Ver.]', '藤田ことね', '00:03:24', '2026-03-02', 'Remix', 'Search Right [Kotone Fujita Solo ver.]. Official solo vocal version included on SEARCH RIGHT -Solo Version Album-. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:00:57', '2026-06-08 16:17:59'),
(226, 'Search Right [Lilja Katsuragi Solo ver.]', 'SEARCH RIGHT [葛城リーリヤ Solo Ver.]', '葛城リーリヤ', '00:03:24', '2026-03-02', 'Remix', 'Search Right [Lilja Katsuragi Solo ver.]. Official solo vocal version included on SEARCH RIGHT -Solo Version Album-. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:00:57', '2026-06-08 16:17:59'),
(227, 'Search Right [Sumika Shiun Solo ver.]', 'SEARCH RIGHT [紫雲清夏 Solo Ver.]', '紫雲清夏', '00:03:24', '2026-03-02', 'Remix', 'Search Right [Sumika Shiun Solo ver.]. Official solo vocal version included on SEARCH RIGHT -Solo Version Album-. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:00:57', '2026-06-08 16:17:59'),
(228, 'Search Right [Hiro Shinosawa Solo ver.]', 'SEARCH RIGHT [篠澤広 Solo Ver.]', '篠澤広', '00:03:24', '2026-03-02', 'Remix', 'Search Right [Hiro Shinosawa Solo ver.]. Official solo vocal version included on SEARCH RIGHT -Solo Version Album-. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:00:57', '2026-06-08 16:17:59'),
(229, 'Search Right [China Kuramoto Solo ver.]', 'SEARCH RIGHT [倉本千奈 Solo Ver.]', '倉本千奈', '00:03:24', '2026-03-02', 'Remix', 'Search Right [China Kuramoto Solo ver.]. Official solo vocal version included on SEARCH RIGHT -Solo Version Album-. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:00:57', '2026-06-08 16:17:59'),
(230, 'Search Right [Ume Hanami Solo ver.]', 'SEARCH RIGHT [花海佑芽 Solo Ver.]', '花海佑芽', '00:03:24', '2026-03-02', 'Remix', 'Search Right [Ume Hanami Solo ver.]. Official solo vocal version included on SEARCH RIGHT -Solo Version Album-. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:00:57', '2026-06-08 16:17:59'),
(231, 'Search Right [Misuzu Hataya Solo ver.]', 'SEARCH RIGHT [秦谷美鈴 Solo Ver.]', '秦谷美鈴', '00:03:24', '2026-03-02', 'Remix', 'Search Right [Misuzu Hataya Solo ver.]. Official solo vocal version included on SEARCH RIGHT -Solo Version Album-. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:00:57', '2026-06-08 16:17:59'),
(232, 'Search Right [Mao Arimura Solo ver.]', 'SEARCH RIGHT [有村麻央 Solo Ver.]', '有村麻央', '00:03:24', '2026-03-02', 'Remix', 'Search Right [Mao Arimura Solo ver.]. Official solo vocal version included on SEARCH RIGHT -Solo Version Album-. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:00:57', '2026-06-08 16:17:59'),
(233, 'Search Right [Rinami Himesaki Solo ver.]', 'SEARCH RIGHT [姫崎莉波 Solo Ver.]', '姫崎莉波', '00:03:24', '2026-03-02', 'Remix', 'Search Right [Rinami Himesaki Solo ver.]. Official solo vocal version included on SEARCH RIGHT -Solo Version Album-. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:00:57', '2026-06-08 16:17:59'),
(234, 'Search Right [Sena Juo Solo ver.]', 'SEARCH RIGHT [十王星南 Solo Ver.]', '十王星南', '00:03:24', '2026-03-02', 'Remix', 'Search Right [Sena Juo Solo ver.]. Official solo vocal version included on SEARCH RIGHT -Solo Version Album-. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:00:57', '2026-06-08 16:17:59'),
(235, 'Search Right [Tsubame Amaya Solo ver.]', 'SEARCH RIGHT [雨夜燕 Solo Ver.]', '雨夜燕', '00:03:24', '2026-03-02', 'Remix', 'Search Right [Tsubame Amaya Solo ver.]. Official solo vocal version included on SEARCH RIGHT -Solo Version Album-. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:00:57', '2026-06-08 16:17:59'),
(236, 'Wakashi Sawagashi Ska Punk [Saki Hanami Solo ver.]', 'わかし・さわがし・スカパンク [花海咲季 Solo Ver.]', '花海咲季', '00:03:23', '2026-03-02', 'Remix', 'Wakashi Sawagashi Ska Punk [Saki Hanami Solo ver.]. Official solo vocal version included on わかし・さわがし・スカパンク -Solo Version Album-. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:00:57', '2026-06-08 16:17:59'),
(237, 'Wakashi Sawagashi Ska Punk [Temari Tsukimura Solo ver.]', 'わかし・さわがし・スカパンク [月村手毬 Solo Ver.]', '月村手毬', '00:03:23', '2026-03-02', 'Remix', 'Wakashi Sawagashi Ska Punk [Temari Tsukimura Solo ver.]. Official solo vocal version included on わかし・さわがし・スカパンク -Solo Version Album-. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:00:57', '2026-06-08 16:17:59'),
(238, 'Wakashi Sawagashi Ska Punk [Kotone Fujita Solo ver.]', 'わかし・さわがし・スカパンク [藤田ことね Solo Ver.]', '藤田ことね', '00:03:23', '2026-03-02', 'Remix', 'Wakashi Sawagashi Ska Punk [Kotone Fujita Solo ver.]. Official solo vocal version included on わかし・さわがし・スカパンク -Solo Version Album-. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:00:57', '2026-06-08 16:17:59'),
(239, 'Wakashi Sawagashi Ska Punk [Lilja Katsuragi Solo ver.]', 'わかし・さわがし・スカパンク [葛城リーリヤ Solo Ver.]', '葛城リーリヤ', '00:03:23', '2026-03-02', 'Remix', 'Wakashi Sawagashi Ska Punk [Lilja Katsuragi Solo ver.]. Official solo vocal version included on わかし・さわがし・スカパンク -Solo Version Album-. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:00:57', '2026-06-08 16:17:59'),
(240, 'Wakashi Sawagashi Ska Punk [Sumika Shiun Solo ver.]', 'わかし・さわがし・スカパンク [紫雲清夏 Solo Ver.]', '紫雲清夏', '00:03:23', '2026-03-02', 'Remix', 'Wakashi Sawagashi Ska Punk [Sumika Shiun Solo ver.]. Official solo vocal version included on わかし・さわがし・スカパンク -Solo Version Album-. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:00:57', '2026-06-08 16:17:59'),
(241, 'Wakashi Sawagashi Ska Punk [Hiro Shinosawa Solo ver.]', 'わかし・さわがし・スカパンク [篠澤広 Solo Ver.]', '篠澤広', '00:03:23', '2026-03-02', 'Remix', 'Wakashi Sawagashi Ska Punk [Hiro Shinosawa Solo ver.]. Official solo vocal version included on わかし・さわがし・スカパンク -Solo Version Album-. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:00:57', '2026-06-08 16:17:59'),
(242, 'Wakashi Sawagashi Ska Punk [China Kuramoto Solo ver.]', 'わかし・さわがし・スカパンク [倉本千奈 Solo Ver.]', '倉本千奈', '00:03:23', '2026-03-02', 'Remix', 'Wakashi Sawagashi Ska Punk [China Kuramoto Solo ver.]. Official solo vocal version included on わかし・さわがし・スカパンク -Solo Version Album-. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:00:57', '2026-06-08 16:17:59'),
(243, 'Wakashi Sawagashi Ska Punk [Ume Hanami Solo ver.]', 'わかし・さわがし・スカパンク [花海佑芽 Solo Ver.]', '花海佑芽', '00:03:23', '2026-03-02', 'Remix', 'Wakashi Sawagashi Ska Punk [Ume Hanami Solo ver.]. Official solo vocal version included on わかし・さわがし・スカパンク -Solo Version Album-. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:00:57', '2026-06-08 16:17:59'),
(244, 'Wakashi Sawagashi Ska Punk [Misuzu Hataya Solo ver.]', 'わかし・さわがし・スカパンク [秦谷美鈴 Solo Ver.]', '秦谷美鈴', '00:03:23', '2026-03-02', 'Remix', 'Wakashi Sawagashi Ska Punk [Misuzu Hataya Solo ver.]. Official solo vocal version included on わかし・さわがし・スカパンク -Solo Version Album-. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:00:57', '2026-06-08 16:17:59'),
(245, 'Wakashi Sawagashi Ska Punk [Mao Arimura Solo ver.]', 'わかし・さわがし・スカパンク [有村麻央 Solo Ver.]', '有村麻央', '00:03:23', '2026-03-02', 'Remix', 'Wakashi Sawagashi Ska Punk [Mao Arimura Solo ver.]. Official solo vocal version included on わかし・さわがし・スカパンク -Solo Version Album-. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:00:57', '2026-06-08 16:17:59'),
(246, 'Wakashi Sawagashi Ska Punk [Rinami Himesaki Solo ver.]', 'わかし・さわがし・スカパンク [姫崎莉波 Solo Ver.]', '姫崎莉波', '00:03:23', '2026-03-02', 'Remix', 'Wakashi Sawagashi Ska Punk [Rinami Himesaki Solo ver.]. Official solo vocal version included on わかし・さわがし・スカパンク -Solo Version Album-. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:00:57', '2026-06-08 16:17:59'),
(247, 'Wakashi Sawagashi Ska Punk [Sena Juo Solo ver.]', 'わかし・さわがし・スカパンク [十王星南 Solo Ver.]', '十王星南', '00:03:23', '2026-03-02', 'Remix', 'Wakashi Sawagashi Ska Punk [Sena Juo Solo ver.]. Official solo vocal version included on わかし・さわがし・スカパンク -Solo Version Album-. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:00:57', '2026-06-08 16:17:59'),
(248, 'Wakashi Sawagashi Ska Punk [Tsubame Amaya Solo ver.]', 'わかし・さわがし・スカパンク [雨夜燕 Solo Ver.]', '雨夜燕', '00:03:23', '2026-03-02', 'Remix', 'Wakashi Sawagashi Ska Punk [Tsubame Amaya Solo ver.]. Official solo vocal version included on わかし・さわがし・スカパンク -Solo Version Album-. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:00:57', '2026-06-08 16:17:59'),
(249, 'Agent Yoru wo Yuku -Mao Arimura cover-', 'エージェント夜を往く -有村麻央 cover-', '有村麻央', '00:04:14', '2026-03-06', 'Cover', 'Agent Yoru wo Yuku -Mao Arimura cover-. Official Gakuen Idolmaster GOLD RUSH cover CD song for Mao Arimura. It reinterprets a classic THE IDOLM@STER song through Mao\'s cool prince-like stage presence.', 1, '2026-06-08 15:30:10', '2026-06-08 16:17:59'),
(250, 'Wildest Flower', 'Wildest Flower', '花海咲季', '00:02:54', '2026-03-19', 'Solo', 'Wildest Flower. Official solo original for Saki Hanami, Hatsuboshi Academy\'s competitive first-year idol who hates losing and pushes herself straight toward the top. It reflects her high-energy rival image, athletic drive, and proud big-sister streak.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:59'),
(251, 'Masshiroi Page to Suisai no Shujinkou', '真っ白いページと水彩の主人公', '花海佑芽', '00:03:48', '2026-04-11', 'Solo', 'Masshiroi Page to Suisai no Shujinkou (真っ白いページと水彩の主人公). Official solo original for Ume Hanami, Saki\'s younger sister and a naturally gifted newcomer whose talent blooms with support. It reflects her energetic, straightforward nature and her fast-growing potential.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:59'),
(252, 'Shirushi [China Kuramoto Solo Ver.]', '標 [倉本千奈 Solo Ver.]', '倉本千奈', '00:03:29', '2026-04-15', 'Remix', 'Shirushi (標) [China Kuramoto Solo Ver.]. CD-exclusive solo vocal version of the Hatsuboshi Academy school anthem. Included on 倉本千奈\'s 3rd Single. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:17:59', '2026-06-08 16:17:59'),
(253, 'Shirushi [Mao Arimura Solo Ver.]', '標 [有村麻央 Solo Ver.]', '有村麻央', '00:03:29', '2026-04-15', 'Remix', 'Shirushi (標) [Mao Arimura Solo Ver.]. CD-exclusive solo vocal version of the Hatsuboshi Academy school anthem. Included on 有村麻央\'s 3rd Single. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:17:59', '2026-06-08 16:17:59'),
(254, 'Shirushi [Lilja Katsuragi Solo Ver.]', '標 [葛城リーリヤ Solo Ver.]', '葛城リーリヤ', '00:03:29', '2026-04-15', 'Remix', 'Shirushi (標) [Lilja Katsuragi Solo Ver.]. CD-exclusive solo vocal version of the Hatsuboshi Academy school anthem. Included on 葛城リーリヤ\'s 3rd Single. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:17:59', '2026-06-08 16:17:59'),
(255, 'Shirushi [Temari Tsukimura Solo Ver.]', '標 [月村手毬 Solo Ver.]', '月村手毬', '00:03:29', '2026-04-15', 'Remix', 'Shirushi (標) [Temari Tsukimura Solo Ver.]. CD-exclusive solo vocal version of the Hatsuboshi Academy school anthem. Included on 月村手毬\'s 3rd Single. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:17:59', '2026-06-08 16:17:59'),
(256, 'ENDLESS DANCE [China Kuramoto Solo Ver.]', 'ENDLESS DANCE [倉本千奈 Solo Ver.]', '倉本千奈', '00:03:22', '2026-04-15', 'Remix', 'ENDLESS DANCE [China Kuramoto Solo Ver.]. CD-exclusive solo vocal version included on 倉本千奈\'s 3rd Single. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:17:59', '2026-06-08 16:17:59'),
(257, 'ENDLESS DANCE [Mao Arimura Solo Ver.]', 'ENDLESS DANCE [有村麻央 Solo Ver.]', '有村麻央', '00:03:22', '2026-04-15', 'Remix', 'ENDLESS DANCE [Mao Arimura Solo Ver.]. CD-exclusive solo vocal version included on 有村麻央\'s 3rd Single. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:17:59', '2026-06-08 16:17:59'),
(258, 'ENDLESS DANCE [Lilja Katsuragi Solo Ver.]', 'ENDLESS DANCE [葛城リーリヤ Solo Ver.]', '葛城リーリヤ', '00:03:22', '2026-04-15', 'Remix', 'ENDLESS DANCE [Lilja Katsuragi Solo Ver.]. CD-exclusive solo vocal version included on 葛城リーリヤ\'s 3rd Single. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:17:59', '2026-06-08 16:17:59'),
(259, 'ENDLESS DANCE [Temari Tsukimura Solo Ver.]', 'ENDLESS DANCE [月村手毬 Solo Ver.]', '月村手毬', '00:03:22', '2026-04-15', 'Remix', 'ENDLESS DANCE [Temari Tsukimura Solo Ver.]. CD-exclusive solo vocal version included on 月村手毬\'s 3rd Single. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:17:59', '2026-06-08 16:17:59'),
(260, 'VEIL', 'VEIL', '秦谷美鈴', '00:03:37', '2026-04-30', 'Solo', 'VEIL. Official solo original for Misuzu Hataya, the sleepy, elegant first-year whose relaxed pace hides a powerful presence. It reflects her gentle mystery and the surprising force she can show when she chooses to move.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:59');
INSERT INTO `songs` (`id`, `title`, `title_jp`, `artist`, `duration`, `release_date`, `song_type`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(261, 'Yukidoke ni [Misuzu Hataya Solo ver.]', '雪解けに [秦谷美鈴 Solo Ver.]', '秦谷美鈴', '00:04:04', '2026-05-09', 'Remix', 'Yukidoke ni [Misuzu Hataya Solo ver.]. Official/current solo vocal version for Misuzu Hataya. It is stored as remix because it is an alternate solo version of the original group song; 2026 current idol song-list update; Tsubame/Misuzu solo additions confirmed in current web listings.', 1, '2026-06-08 15:53:44', '2026-06-08 16:17:59'),
(262, 'Yukidoke ni [Tsubame Amaya Solo ver.]', '雪解けに [雨夜燕 Solo Ver.]', '雨夜燕', '00:04:04', '2026-05-09', 'Remix', 'Yukidoke ni [Tsubame Amaya Solo ver.]. Official/current solo vocal version for Tsubame Amaya. It is stored as remix because it is an alternate solo version of the original group song; 2026 current idol song-list update; Tsubame/Misuzu solo additions confirmed in current web listings.', 1, '2026-06-08 15:53:44', '2026-06-08 16:17:59'),
(263, 'Sakura Photograph [Misuzu Hataya Solo ver.]', '桜フォトグラフ [秦谷美鈴 Solo Ver.]', '秦谷美鈴', '00:04:44', '2026-05-09', 'Remix', 'Sakura Photograph [Misuzu Hataya Solo ver.]. Official/current solo vocal version for Misuzu Hataya. It is stored as remix because it is an alternate solo version of the original group song; 2026 current idol song-list update; Ume/Misuzu/Tsubame solo additions checked against current web listings.', 1, '2026-06-08 15:53:44', '2026-06-08 16:17:59'),
(264, 'Sakura Photograph [Tsubame Amaya Solo ver.]', '桜フォトグラフ [雨夜燕 Solo Ver.]', '雨夜燕', '00:04:44', '2026-05-09', 'Remix', 'Sakura Photograph [Tsubame Amaya Solo ver.]. Official/current solo vocal version for Tsubame Amaya. It is stored as remix because it is an alternate solo version of the original group song; 2026 current idol song-list update; Ume/Misuzu/Tsubame solo additions checked against current web listings.', 1, '2026-06-08 15:53:44', '2026-06-08 16:17:59'),
(265, 'Garakuta Road', 'ガラクタロード', '初星学園', '00:03:43', '2026-05-16', 'Group', 'Garakuta Road (ガラクタロード). Official H.I.F Memorial Single song for Hatsuboshi Academy. It is a 13-idol theme for the H.I.F story arc, released with solo versions and an instrumental on the memorial CD.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:59'),
(266, 'Sanpunhan no Sousei', '三分半の創世', '雨夜燕', '00:03:30', '2026-05-18', 'Solo', 'Sanpunhan no Sousei (三分半の創世). Official solo original for Tsubame Amaya, the poised senior idol connected to the student council circle and the academy\'s upperclassman presence. It reflects her composed, observant, and quietly distinctive stage image.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:59'),
(267, 'MY STAGE', 'MY STAGE', '雨夜燕', '00:03:58', '2026-05-20', 'Solo', 'MY STAGE. Official solo original for Tsubame Amaya, the poised senior idol connected to the student council circle and the academy\'s upperclassman presence. It reflects her composed, observant, and quietly distinctive stage image.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:59'),
(268, 'Gamushara ni Ikou! [Misuzu Hataya Solo ver.]', 'がむしゃらに行こう！ [秦谷美鈴 Solo Ver.]', '秦谷美鈴', '00:04:24', '2026-05-20', 'Remix', 'Gamushara ni Ikou! [Misuzu Hataya Solo ver.]. Official solo vocal version of the group song, included on 秦谷美鈴 2nd Single「Superlative」. Stored as remix because it is an alternate solo full version rather than the original group recording.', 1, '2026-06-08 15:57:25', '2026-06-08 16:17:59'),
(269, 'Howling over the World [Misuzu Hataya Solo ver.]', 'Howling over the World [秦谷美鈴 Solo Ver.]', '秦谷美鈴', '00:04:20', '2026-05-20', 'Remix', 'Howling over the World [Misuzu Hataya Solo ver.]. Official solo vocal version of the group song, included on 秦谷美鈴 2nd Single「Superlative」. Stored as remix because it is an alternate solo full version rather than the original group recording.', 1, '2026-06-08 15:57:25', '2026-06-08 16:17:59'),
(270, 'Miracle Nanau! [Misuzu Hataya Solo ver.]', 'ミラクルナナウ(ﾟ∀ﾟ)！ [秦谷美鈴 Solo Ver.]', '秦谷美鈴', '00:03:43', '2026-05-20', 'Remix', 'Official solo vocal full version included on 秦谷美鈴 2nd Single「Superlative」. Stored as remix because it is an alternate solo version of the group song.', 1, '2026-06-08 15:57:25', '2026-06-08 16:17:59'),
(271, 'Howling over the World [Ume Hanami Solo ver.]', 'Howling over the World [花海佑芽 Solo Ver.]', '花海佑芽', '00:04:20', '2026-05-20', 'Remix', 'Official solo vocal full version included on 花海佑芽 2nd Single「グースーピー」. Stored as remix because it is an alternate solo version of the group song.', 1, '2026-06-08 16:15:20', '2026-06-08 16:17:59'),
(272, 'Miracle Nanau! [Ume Hanami Solo ver.]', 'ミラクルナナウ(ﾟ∀ﾟ)！ [花海佑芽 Solo Ver.]', '花海佑芽', '00:03:43', '2026-05-20', 'Remix', 'Official solo vocal full version included on 花海佑芽 2nd Single「グースーピー」. Stored as remix because it is an alternate solo version of the group song.', 1, '2026-06-08 16:17:58', '2026-06-08 16:17:59'),
(273, 'EGO (Nor Remix)', 'EGO (Nor Remix)', '花海咲季', '00:03:46', '2026-05-25', 'Remix', 'EGO (Nor Remix). Official remix track from 初星学園 Remix Album 「ReCollection」vol.1. Stored as remix because it is a new arranged remix recording, not an instrumental or original version.', 1, '2026-06-08 16:03:03', '2026-06-08 16:17:59'),
(274, 'Luna say maybe (gaburyu Remix)', 'Luna say maybe (gaburyu Remix)', '月村手毬', '00:04:10', '2026-05-25', 'Remix', 'Luna say maybe (gaburyu Remix). Official remix track from 初星学園 Remix Album 「ReCollection」vol.1. Stored as remix because it is a new arranged remix recording, not an instrumental or original version.', 1, '2026-06-08 16:03:03', '2026-06-08 16:17:59'),
(275, 'Yellow Big Bang! (cosMo@BousouP Remix)', 'Yellow Big Bang！ (cosMo＠暴走P Remix)', '藤田ことね', '00:03:04', '2026-05-25', 'Remix', 'Yellow Big Bang! (cosMo@BousouP Remix). Official remix track from 初星学園 Remix Album 「ReCollection」vol.1. Stored as remix because it is a new arranged remix recording, not an instrumental or original version.', 1, '2026-06-08 16:03:03', '2026-06-08 16:17:59'),
(276, 'Feel Jewel Dream (James Landino Remix)', 'Feel Jewel Dream (James Landino Remix)', '有村麻央', '00:03:15', '2026-05-25', 'Remix', 'Feel Jewel Dream (James Landino Remix). Official remix track from 初星学園 Remix Album 「ReCollection」vol.1. Stored as remix because it is a new arranged remix recording, not an instrumental or original version.', 1, '2026-06-08 16:03:03', '2026-06-08 16:17:59'),
(277, 'Haku sen (Yashikin Remix)', '白線 (やしきん Remix)', '葛城リーリヤ', '00:03:47', '2026-05-25', 'Remix', 'Haku sen (Yashikin Remix). Official remix track from 初星学園 Remix Album 「ReCollection」vol.1. Stored as remix because it is a new arranged remix recording, not an instrumental or original version.', 1, '2026-06-08 16:03:03', '2026-06-08 16:17:59'),
(278, 'Akogare wo Ippai (Tomggg Remix)', '憧れをいっぱい (Tomggg Remix)', '倉本千奈', '00:04:39', '2026-05-25', 'Remix', 'Akogare wo Ippai (Tomggg Remix). Official remix track from 初星学園 Remix Album 「ReCollection」vol.1. Stored as remix because it is a new arranged remix recording, not an instrumental or original version.', 1, '2026-06-08 16:03:03', '2026-06-08 16:17:59'),
(279, 'Kira Kira (r-906 Remix)', 'Kira Kira (r-906 Remix)', '紫雲清夏', '00:03:16', '2026-05-25', 'Remix', 'Kira Kira (r-906 Remix). Official remix track from 初星学園 Remix Album 「ReCollection」vol.1. Stored as remix because it is a new arranged remix recording, not an instrumental or original version.', 1, '2026-06-08 16:03:03', '2026-06-08 16:17:59'),
(280, 'Mekurume (PAS TASTA Remix)', 'メクルメ (PAS TASTA Remix)', '篠澤広', '00:03:41', '2026-05-25', 'Remix', 'Mekurume (PAS TASTA Remix). Official remix track from 初星学園 Remix Album 「ReCollection」vol.1. Stored as remix because it is a new arranged remix recording, not an instrumental or original version.', 1, '2026-06-08 16:03:03', '2026-06-08 16:17:59'),
(281, 'L.U.V (Sho Ishihama Remix)', 'L.U.V (石濱翔 Remix)', '姫崎莉波', '00:04:24', '2026-05-25', 'Remix', 'L.U.V (Sho Ishihama Remix). Official remix track from 初星学園 Remix Album 「ReCollection」vol.1. Stored as remix because it is a new arranged remix recording, not an instrumental or original version.', 1, '2026-06-08 16:03:03', '2026-06-08 16:17:59'),
(282, 'The Rolling Riceball (LindaAI-CUE Remix)', 'The Rolling Riceball (LindaAI-CUE Remix)', '花海佑芽', '00:04:07', '2026-05-25', 'Remix', 'The Rolling Riceball (LindaAI-CUE Remix). Official remix track from 初星学園 Remix Album 「ReCollection」vol.1. Stored as remix because it is a new arranged remix recording, not an instrumental or original version.', 1, '2026-06-08 16:03:03', '2026-06-08 16:17:59'),
(283, 'Cosmetic (REDALiCE Remix)', 'Cosmetic (REDALiCE Remix)', '十王星南', '00:03:13', '2026-05-25', 'Remix', 'Cosmetic (REDALiCE Remix). Official remix track from 初星学園 Remix Album 「ReCollection」vol.1. Stored as remix because it is a new arranged remix recording, not an instrumental or original version.', 1, '2026-06-08 16:03:03', '2026-06-08 16:17:59'),
(284, 'Tsuki no Kame (Shinpei Nasuno Remix)', 'ツキノカメ (Shinpei Nasuno Remix)', '秦谷美鈴', '00:03:53', '2026-05-25', 'Remix', 'Tsuki no Kame (Shinpei Nasuno Remix). Official remix track from 初星学園 Remix Album 「ReCollection」vol.1. Stored as remix because it is a new arranged remix recording, not an instrumental or original version.', 1, '2026-06-08 16:03:03', '2026-06-08 16:17:59'),
(285, 'Kasou Kyousoukyoku (Sasuke Haraguchi Remix)', '仮装狂騒曲 (原口沙輔 Remix)', '倉本千奈・篠澤広・月村手毬', '00:04:44', '2026-05-25', 'Remix', 'Kasou Kyousoukyoku (Sasuke Haraguchi Remix). Official remix track from 初星学園 Remix Album 「ReCollection」vol.1. Stored as remix because it is a new arranged remix recording, not an instrumental or original version.', 1, '2026-06-08 16:03:03', '2026-06-08 16:17:59'),
(286, 'Miracle Nanau! (Takafumi Sato Remix)', 'ミラクルナナウ(ﾟ∀ﾟ)！ (佐藤貴文 Remix)', '有村麻央・紫雲清夏・篠澤広', '00:03:25', '2026-05-25', 'Remix', 'Miracle Nanau! (Takafumi Sato Remix). Official remix track from 初星学園 Remix Album 「ReCollection」vol.1. Stored as remix because it is a new arranged remix recording, not an instrumental or original version.', 1, '2026-06-08 16:03:03', '2026-06-08 16:17:59'),
(287, 'Shirushi [Sena Juo Solo Ver.]', '標 [十王星南 Solo Ver.]', '十王星南', '00:03:29', '2026-08-26', 'Remix', 'Shirushi (標) [Sena Juo Solo Ver.]. CD-exclusive solo vocal version of the Hatsuboshi Academy school anthem. Included on 十王星南\'s 3rd Single. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:17:59', '2026-06-08 16:17:59'),
(288, 'Shirushi [Saki Hanami Solo Ver.]', '標 [花海咲季 Solo Ver.]', '花海咲季', '00:03:29', '2026-08-26', 'Remix', 'Shirushi (標) [Saki Hanami Solo Ver.]. CD-exclusive solo vocal version of the Hatsuboshi Academy school anthem. Included on 花海咲季\'s 3rd Single. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:17:59', '2026-06-08 16:17:59'),
(289, 'Shirushi [Ume Hanami Solo Ver.]', '標 [花海佑芽 Solo Ver.]', '花海佑芽', '00:03:29', '2026-08-26', 'Remix', 'Shirushi (標) [Ume Hanami Solo Ver.]. CD-exclusive solo vocal version of the Hatsuboshi Academy school anthem. Included on 花海佑芽\'s 3rd Single. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:17:59', '2026-06-08 16:17:59'),
(290, 'Shirushi [Misuzu Hataya Solo Ver.]', '標 [秦谷美鈴 Solo Ver.]', '秦谷美鈴', '00:03:29', '2026-08-26', 'Remix', 'Shirushi (標) [Misuzu Hataya Solo Ver.]. CD-exclusive solo vocal version of the Hatsuboshi Academy school anthem. Included on 秦谷美鈴\'s 3rd Single. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:17:59', '2026-06-08 16:17:59'),
(291, 'ENDLESS DANCE [Sena Juo Solo Ver.]', 'ENDLESS DANCE [十王星南 Solo Ver.]', '十王星南', '00:03:22', '2026-08-26', 'Remix', 'ENDLESS DANCE [Sena Juo Solo Ver.]. CD-exclusive solo vocal version included on 十王星南\'s 3rd Single. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:17:59', '2026-06-08 16:17:59'),
(292, 'ENDLESS DANCE [Saki Hanami Solo Ver.]', 'ENDLESS DANCE [花海咲季 Solo Ver.]', '花海咲季', '00:03:22', '2026-08-26', 'Remix', 'ENDLESS DANCE [Saki Hanami Solo Ver.]. CD-exclusive solo vocal version included on 花海咲季\'s 3rd Single. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:17:59', '2026-06-08 16:17:59'),
(293, 'ENDLESS DANCE [Ume Hanami Solo Ver.]', 'ENDLESS DANCE [花海佑芽 Solo Ver.]', '花海佑芽', '00:03:22', '2026-08-26', 'Remix', 'ENDLESS DANCE [Ume Hanami Solo Ver.]. CD-exclusive solo vocal version included on 花海佑芽\'s 3rd Single. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:17:59', '2026-06-08 16:17:59'),
(294, 'ENDLESS DANCE [Misuzu Hataya Solo Ver.]', 'ENDLESS DANCE [秦谷美鈴 Solo Ver.]', '秦谷美鈴', '00:03:22', '2026-08-26', 'Remix', 'ENDLESS DANCE [Misuzu Hataya Solo Ver.]. CD-exclusive solo vocal version included on 秦谷美鈴\'s 3rd Single. Stored as remix because it is an alternate solo version of the original group song.', 1, '2026-06-08 16:17:59', '2026-06-08 16:17:59'),
(295, 'SUGAR FLAVOR', 'SUGAR FLAVOR', 'RippleSign (有村麻央・姫崎莉波)', NULL, NULL, 'Group', 'SUGAR FLAVOR. Official RippleSign duet song for Mao Arimura and Rinami Himesaki. It presents a mature third-year pairing, with Mao\'s prince-like charm meeting Rinami\'s warm romantic style.', 1, '2026-06-08 13:53:10', '2026-06-08 16:17:59');

-- --------------------------------------------------------

--
-- Table structure for table `stat_history`
--

CREATE TABLE `stat_history` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `stat_type` enum('vocal','dance','visual') NOT NULL,
  `old_value` int(11) NOT NULL,
  `new_value` int(11) NOT NULL,
  `reason` varchar(200) DEFAULT NULL,
  `recorded_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `name_jp` varchar(100) DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `zodiac` varchar(30) DEFAULT NULL,
  `blood_type` char(2) DEFAULT NULL,
  `height` int(11) DEFAULT NULL,
  `weight` int(11) DEFAULT NULL,
  `three_size` varchar(20) DEFAULT NULL,
  `hometown` varchar(50) DEFAULT NULL,
  `hobbies` text DEFAULT NULL,
  `special_skill` text DEFAULT NULL,
  `school_year` varchar(20) DEFAULT NULL,
  `rank` varchar(10) NOT NULL DEFAULT 'Debut',
  `vocal` int(11) NOT NULL DEFAULT 0,
  `dance` int(11) NOT NULL DEFAULT 0,
  `visual` int(11) NOT NULL DEFAULT 0,
  `bio` text DEFAULT NULL,
  `theme_primary_color` varchar(20) DEFAULT NULL,
  `theme_secondary_color` varchar(20) NOT NULL,
  `producer_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `user_id`, `name`, `name_jp`, `birthday`, `zodiac`, `blood_type`, `height`, `weight`, `three_size`, `hometown`, `hobbies`, `special_skill`, `school_year`, `rank`, `vocal`, `dance`, `visual`, `bio`, `theme_primary_color`, `theme_secondary_color`, `producer_id`, `created_at`, `updated_at`) VALUES
(1, 5, 'Saki Hanami', '花海 咲季', '2000-04-02', 'Aries', 'A', 152, 45, '84/55/80', 'Aichi', 'Competitions in general', 'Exercise in general, chores in general, massages', 'Class 1-1', 'Debut', 75, 75, 80, 'A new student who topped the entrance exam and a fierce ex-athlete with an unshakeable hatred of losing. A child prodigy since youth—quick to learn, skilled at everything she tries. She\'s extremely close with her younger sister Ume Hanami, but they\'re also lifelong rivals who have competed in every sport imaginable. Saki rates Ume\'\'s talent higher than anyone else\'s and fears it.\r\n', '#E30F25', '#FAD0D4', 1, '2026-05-26 13:13:20', '2026-06-08 12:28:57'),
(2, 6, 'Temari Tsukimura', '月村 手毬', '2000-06-03', 'Gemini', 'AB', 162, 51, '82/58/86', 'Kyoto', 'Eating delicious food (currently sealed away)', 'Singing', 'Class 1-1', 'Debut', 75, 65, 55, 'A former elite middle-school idol, once called the #1 idol of her grade. Appears cool, stoic, and sarcastic on the surface but is secretly a lazy, spoiled troublemaker—a girl with two sides. She aims for the top to break away from the self she dislikes and learn to love herself. ', '#0C7BBB', '#CEE5F1', 1, '2026-05-26 13:13:20', '2026-06-08 12:28:57'),
(3, 7, 'Kotone Fujita', '藤田 ことね', '2000-04-29', 'Taurus', 'O', 156, 40, '75/55/75', 'Saitama', 'Making money', 'Dance, remembering people faces and names', 'Class 1-1', 'Debut', 65, 55, 75, 'A greedy girl who dreams of becoming \"an idol who can make money.\" She sees idol work as her one shot at turning her life around. Her grades are poor and her self-esteem is low overall, but she has full confidence in her cute face. She\'s a little uncomfortable around the student council president Sena, who keeps overestimating her for some reason.\r\n', '#F8C112', '#FEF3CF', 1, '2026-05-26 13:13:20', '2026-06-08 12:28:57'),
(4, 8, 'Lilja Katsuragi', '葛城 リーリヤ', '2000-07-24', 'Leo', 'B', 161, 43, '82/53/80', 'Sweden', 'Baking sweets, Japanese anime', 'Baking sweets, games', 'Class 1-1', 'Debut', 55, 55, 65, 'An exchange student from overseas with no prior singing or dancing experience, who is always shrinking back as if she had no confidence. She doesn\'t look talented at first glance, but her admiration for idols is genuine, and she is an incredibly hard worker. Her father is Japanese, so she speaks the language fluently. She has a promise with her best friend Sumika to stand on the same stage together one day—the reason she enrolled at Hatsuboshi Academy. ', '#7DC4D4', '#EAFDFF', 1, '2026-05-28 17:43:41', '2026-06-08 12:28:57'),
(5, 9, 'Sumika Shiun', '紫雲 清夏', '2000-11-11', 'Scorpio', 'O', 168, 54, '89/58/85', 'Hokkaido', 'Karaoke, social media', 'Flexibility', 'Class 1-1', 'Debut', 65, 55, 65, 'A laid-back gyaru who skips class and lessons—a bit of an unserious slacker, but genuinely bright, cheerful, and able to get along with anyone, which is her real charm. She once had a celebrated ballet career and was expected to go far on the world stage, but she has no motivation for it anymore. She wholeheartedly cheers on her best friend Lilja, who is earnestly chasing her idol dream. ', '#7CFC00', '#D6FFB3', 1, '2026-05-28 17:43:41', '2026-06-08 12:28:57'),
(6, 10, 'Hiro Shinosawa', '篠澤 広', '2000-12-21', 'Sagittarius', 'AB', 159, 41, '72/54/76', 'Akita', 'Challenging weak areas, growing houseplants', 'Academics in general, physics, mathematics, puzzles', 'Class 1-2', 'Debut', 55, 55, 60, 'A mysterious genius girl. Dissatisfied with days that are too easy and boring, she enrolled at Hatsuboshi Academy specifically to challenge her weakest areas. A true eccentric who finds joy in \"harsh, painful lessons\" and \"things that don\'t go well.\" Her reason for becoming an idol is \"because it seems like the thing I\'d be worst at.\" Despite her brilliant mind, she has extreme stamina and muscle deficits—even basic lessons leave her unsteady on her feet.\r\n', '#00AFCC', '#BFEAF2', 1, '2026-05-28 17:43:41', '2026-06-08 12:28:57'),
(7, 11, 'China Kuramoto', '倉本 千奈', '2000-08-01', 'Leo', 'O', 148, 43, '73/56/73', 'Kanagawa', 'Drawing', 'Cheerful greetings, being liked by animals', 'Class 1-2', 'Debut', 55, 55, 60, 'A genuine, spoiled-rotten young lady (お嬢様) raised with every privilege. A cheerful, innocent girl who enrolled at Hatsuboshi dreaming of becoming \"a proper, splendid idol.\" Her actual ability level, by her own admission, is \"definitely dead last among the entire student body!\" Her sheltered upbringing has left her sweet, polite, and earnest—but completely behind her classmates in raw skill, which she takes in stride with her trademark \"Well then, I\'ll just have to do my best, won\'t I!\" attitude.\r\n', '#F68B1F', '#FCE0C5', 1, '2026-05-28 17:43:41', '2026-06-08 12:28:57'),
(8, 12, 'Ume Hanami', '花海 佑芽', '2000-04-01', 'Aries', 'AB', 158, 50, '91/60/85', 'Aichi', 'Competing with her older sister', 'Sports in general, massages', 'Class 1-2', 'Debut', 50, 60, 60, '\'A supplementary-entry new student. Energetic to the core, with exceptional physical ability from her background as a former athlete. She absolutely adores her older sister Saki — she respects her with all her heart, treats her as her ultimate rival, and holds her up as the goal she\'s aiming for. Through Saki\'s devoted support, Ume gradually blossoms her own idol talent. \r\n', '#EA533A', '#FAD4CB', 1, '2026-05-28 17:43:41', '2026-06-08 12:28:57'),
(9, 13, 'Misuzu Hataya', '秦谷 美鈴', '2000-02-06', 'Aquarius', 'B', 160, 42, '73/54/74', 'Kyoto', 'Napping, taking walks', 'Cooking, ikebana, accurate body clock', 'Class 1-2', 'Debut', 65, 70, 80, 'A languid, easygoing girl who moves at her own pace. Sweet and indulgent to both herself and others, she loves taking care of people and spoiling them. Her childhood friend Temari Tsukimura — whom she calls \"Mari-chan\" — she treats like a little sister or daughter she needs to cherish and protect. A Kyoto native and former member of the elite middle-school unit \"SyngUp!\" alongside Temari. Sleeps constantly — at every opportunity.\r\n', '#7A99CF', '#D8E0EF', 1, '2026-05-28 17:43:41', '2026-06-08 12:28:57'),
(10, 14, 'Mao Arimura', '有村 麻央', '2000-01-18', 'Capricorn', 'A', 157, 46, '85/53/85', 'Hyogo', 'Taking care of others, watching theater', 'Martial arts, dexterous handwork', 'Class 3-1', 'Debut', 75, 65, 55, 'A 3rd-year girl aiming to be a cool idol. She serves as the dormitory leader of Hatsuboshi Academy\'s idol department and is very caring. She is adored by her juniors as the \"Little Prince.\" She has a past of aspiring to be a stage musical star since childhood and working as a child actress.', '#7F1184', '#DCC2DE', 1, '2026-06-03 13:03:10', '2026-06-08 12:28:57'),
(11, 15, 'Rinami Himesaki', '姫崎 莉波', '2000-03-05', 'Pisces', 'A', 166, 56, '90/59/93', 'Fukuoka', 'Cafe hopping, collecting cosmetics', 'Cooking, sewing and handicrafts', 'Class 3-1', 'Debut', 60, 70, 55, 'A mature-feeling 3rd-year student. Reliable, gentle, and caring—the big sister of everyone in the dorm. She was once part of a unit, but the results were not great. She is a member of the student council, serving as secretary.', '#F6ADC6', '#FCE6EE', 1, '2026-06-03 13:03:10', '2026-06-08 12:28:57'),
(12, 16, 'Sena Juo', '十王 星南', '2000-12-07', 'Sagittarius', 'A', 170, 54, '88/56/86', 'Tokyo', 'Lessons, finding talented idols', 'Recognizing idol talent', 'Class 3-1', 'Debut', 130, 100, 105, 'Student council president of Hatsuboshi Academy. Known as \"the academy\'s top idol,\" admired by many students. The granddaughter of the academy principal, she was raised with elite idol education from a young age. She has a special ability to spot idol talent and immediately takes a liking to Kotone—but for some reason, Kotone keeps her distance.', '#F6AE54', '#FCE6CC', 1, '2026-06-03 13:03:10', '2026-06-08 12:28:57'),
(13, 17, 'Tsubame Amaya', '雨夜 燕', '2000-05-20', 'Taurus', 'A', 169, 51, '80/55/82', 'Tokyo', 'Calligraphy, Japanese history, reading', 'Secretarial skills in general, mathematics, calligraphy', 'Class 3-1', 'Debut', 80, 90, 70, 'Vice president of the Hatsuboshi Academy student council. The academy\'s No. 2 idol. She carries pride befitting her ability and holds herself with an arrogant attitude. She is strict with both herself and others but is caring. She sees her childhood friend Sena as a rival and has publicly declared that she will one day surpass Sena to become the \"number one star.\"', '#7B68EE', '#D8D2FB', 1, '2026-06-03 13:03:10', '2026-06-08 12:28:57');

-- --------------------------------------------------------

--
-- Table structure for table `student_songs`
--

CREATE TABLE `student_songs` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `song_id` int(11) NOT NULL,
  `added_by` int(11) DEFAULT NULL,
  `added_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_songs`
--

INSERT INTO `student_songs` (`id`, `student_id`, `song_id`, `added_by`, `added_at`) VALUES
(1, 1, 1, 1, '2026-06-08 13:53:09'),
(2, 2, 2, 1, '2026-06-08 13:53:09'),
(3, 3, 3, 1, '2026-06-08 13:53:09'),
(4, 11, 4, 1, '2026-06-08 13:53:09'),
(5, 5, 5, 1, '2026-06-08 13:53:09'),
(6, 6, 6, 1, '2026-06-08 13:53:09'),
(7, 4, 7, 1, '2026-06-08 13:53:09'),
(8, 7, 8, 1, '2026-06-08 13:53:09'),
(9, 10, 9, 1, '2026-06-08 13:53:09'),
(10, 1, 10, 1, '2026-06-08 13:53:09'),
(11, 2, 10, 1, '2026-06-08 13:53:09'),
(12, 3, 10, 1, '2026-06-08 13:53:09'),
(13, 4, 10, 1, '2026-06-08 13:53:09'),
(14, 5, 10, 1, '2026-06-08 13:53:09'),
(15, 6, 10, 1, '2026-06-08 13:53:09'),
(16, 7, 10, 1, '2026-06-08 13:53:09'),
(17, 8, 10, 1, '2026-06-08 13:53:09'),
(18, 9, 10, 1, '2026-06-08 13:53:09'),
(19, 10, 10, 1, '2026-06-08 13:53:09'),
(20, 11, 10, 1, '2026-06-08 13:53:09'),
(21, 12, 10, 1, '2026-06-08 13:53:09'),
(22, 13, 10, 1, '2026-06-08 13:53:09'),
(23, 2, 11, 1, '2026-06-08 13:53:09'),
(24, 8, 12, 1, '2026-06-08 13:53:09'),
(25, 2, 13, 1, '2026-06-08 13:53:09'),
(26, 1, 14, 1, '2026-06-08 13:53:09'),
(27, 2, 14, 1, '2026-06-08 13:53:09'),
(28, 3, 14, 1, '2026-06-08 13:53:09'),
(29, 4, 14, 1, '2026-06-08 13:53:09'),
(30, 5, 14, 1, '2026-06-08 13:53:09'),
(31, 6, 14, 1, '2026-06-08 13:53:09'),
(32, 7, 14, 1, '2026-06-08 13:53:09'),
(33, 8, 14, 1, '2026-06-08 13:53:09'),
(34, 9, 14, 1, '2026-06-08 13:53:09'),
(35, 10, 14, 1, '2026-06-08 13:53:09'),
(36, 11, 14, 1, '2026-06-08 13:53:09'),
(37, 12, 14, 1, '2026-06-08 13:53:09'),
(38, 13, 14, 1, '2026-06-08 13:53:09'),
(39, 3, 15, 1, '2026-06-08 13:53:09'),
(40, 1, 16, 1, '2026-06-08 13:53:09'),
(41, 5, 17, 1, '2026-06-08 15:46:19'),
(42, 10, 17, 1, '2026-06-08 15:46:19'),
(43, 11, 17, 1, '2026-06-08 15:46:19'),
(44, 4, 18, 1, '2026-06-08 13:53:09'),
(45, 7, 19, 1, '2026-06-08 13:53:10'),
(46, 1, 20, 1, '2026-06-08 15:46:19'),
(47, 3, 20, 1, '2026-06-08 15:46:19'),
(48, 4, 20, 1, '2026-06-08 15:46:19'),
(49, 1, 21, 1, '2026-06-08 16:00:57'),
(50, 2, 22, 1, '2026-06-08 16:00:57'),
(51, 3, 23, 1, '2026-06-08 16:00:57'),
(52, 1, 24, 1, '2026-06-08 15:46:19'),
(53, 2, 24, 1, '2026-06-08 15:46:19'),
(54, 3, 24, 1, '2026-06-08 15:46:19'),
(55, 1, 25, 1, '2026-06-08 15:41:10'),
(56, 2, 26, 1, '2026-06-08 15:41:10'),
(57, 3, 27, 1, '2026-06-08 15:41:10'),
(58, 10, 28, 1, '2026-06-08 15:41:10'),
(59, 4, 29, 1, '2026-06-08 15:41:10'),
(60, 7, 30, 1, '2026-06-08 15:41:10'),
(61, 5, 31, 1, '2026-06-08 15:41:10'),
(62, 6, 32, 1, '2026-06-08 15:41:10'),
(63, 11, 33, 1, '2026-06-08 15:41:10'),
(64, 1, 34, 1, '2026-06-08 15:41:10'),
(65, 2, 35, 1, '2026-06-08 15:41:10'),
(66, 3, 36, 1, '2026-06-08 15:41:10'),
(67, 10, 37, 1, '2026-06-08 15:41:10'),
(68, 4, 38, 1, '2026-06-08 15:41:10'),
(69, 7, 39, 1, '2026-06-08 15:41:10'),
(70, 5, 40, 1, '2026-06-08 15:41:10'),
(71, 6, 41, 1, '2026-06-08 15:41:10'),
(72, 11, 42, 1, '2026-06-08 15:41:10'),
(73, 1, 43, 1, '2026-06-08 16:15:20'),
(74, 2, 43, 1, '2026-06-08 16:15:20'),
(75, 3, 43, 1, '2026-06-08 16:15:20'),
(76, 7, 44, 1, '2026-06-08 13:53:10'),
(77, 1, 45, 1, '2026-06-08 16:17:58'),
(78, 2, 45, 1, '2026-06-08 16:17:58'),
(79, 3, 45, 1, '2026-06-08 16:17:58'),
(80, 4, 46, 1, '2026-06-08 16:00:57'),
(81, 7, 47, 1, '2026-06-08 16:00:57'),
(82, 11, 48, 1, '2026-06-08 16:00:57'),
(83, 4, 49, 1, '2026-06-08 16:15:20'),
(84, 7, 49, 1, '2026-06-08 16:15:20'),
(85, 11, 49, 1, '2026-06-08 16:15:20'),
(86, 4, 50, 1, '2026-06-08 16:17:58'),
(87, 7, 50, 1, '2026-06-08 16:17:58'),
(88, 11, 50, 1, '2026-06-08 16:17:58'),
(89, 5, 51, 1, '2026-06-08 16:00:57'),
(90, 6, 52, 1, '2026-06-08 16:00:57'),
(91, 10, 53, 1, '2026-06-08 16:00:57'),
(92, 2, 54, 1, '2026-06-08 15:46:19'),
(93, 6, 54, 1, '2026-06-08 15:46:19'),
(94, 7, 54, 1, '2026-06-08 15:46:19'),
(95, 5, 55, 1, '2026-06-08 16:15:20'),
(96, 6, 55, 1, '2026-06-08 16:15:20'),
(97, 10, 55, 1, '2026-06-08 16:15:20'),
(98, 5, 56, 1, '2026-06-08 16:17:58'),
(99, 6, 56, 1, '2026-06-08 16:17:58'),
(100, 10, 56, 1, '2026-06-08 16:17:58'),
(101, 1, 57, 1, '2026-06-08 15:46:19'),
(102, 2, 57, 1, '2026-06-08 15:46:19'),
(103, 3, 57, 1, '2026-06-08 15:46:19'),
(104, 5, 58, 1, '2026-06-08 13:53:10'),
(105, 1, 59, 1, '2026-06-08 15:41:10'),
(106, 2, 60, 1, '2026-06-08 15:41:10'),
(107, 3, 61, 1, '2026-06-08 15:41:10'),
(108, 10, 62, 1, '2026-06-08 15:41:10'),
(109, 4, 63, 1, '2026-06-08 15:41:10'),
(110, 7, 64, 1, '2026-06-08 15:41:10'),
(111, 5, 65, 1, '2026-06-08 15:41:10'),
(112, 6, 66, 1, '2026-06-08 15:41:10'),
(113, 11, 67, 1, '2026-06-08 15:41:10'),
(114, 3, 68, 1, '2026-06-08 15:46:19'),
(115, 4, 68, 1, '2026-06-08 15:46:19'),
(116, 8, 68, 1, '2026-06-08 15:46:19'),
(117, 12, 69, 1, '2026-06-08 13:53:10'),
(118, 1, 70, 1, '2026-06-08 15:41:10'),
(119, 2, 71, 1, '2026-06-08 15:41:10'),
(120, 3, 72, 1, '2026-06-08 15:41:10'),
(121, 10, 73, 1, '2026-06-08 15:41:10'),
(122, 4, 74, 1, '2026-06-08 15:41:10'),
(123, 7, 75, 1, '2026-06-08 15:41:10'),
(124, 5, 76, 1, '2026-06-08 15:41:10'),
(125, 6, 77, 1, '2026-06-08 15:41:10'),
(126, 11, 78, 1, '2026-06-08 15:41:10'),
(127, 8, 79, 1, '2026-06-08 15:41:10'),
(128, 5, 80, 1, '2026-06-08 13:53:10'),
(129, 6, 81, 1, '2026-06-08 13:53:10'),
(130, 8, 82, 1, '2026-06-08 16:00:57'),
(131, 10, 83, 1, '2026-06-08 13:53:10'),
(132, 6, 84, 1, '2026-06-08 15:46:19'),
(133, 11, 84, 1, '2026-06-08 15:46:19'),
(134, 12, 84, 1, '2026-06-08 15:46:19'),
(135, 9, 85, 1, '2026-06-08 13:53:10'),
(136, 9, 86, 1, '2026-06-08 13:53:10'),
(137, 8, 87, 1, '2026-06-08 15:46:19'),
(138, 9, 87, 1, '2026-06-08 15:46:19'),
(139, 12, 87, 1, '2026-06-08 15:46:19'),
(140, 2, 88, 1, '2026-06-08 15:41:10'),
(141, 3, 89, 1, '2026-06-08 15:41:10'),
(142, 10, 90, 1, '2026-06-08 15:41:10'),
(143, 4, 91, 1, '2026-06-08 15:41:10'),
(144, 7, 92, 1, '2026-06-08 15:41:10'),
(145, 5, 93, 1, '2026-06-08 15:41:10'),
(146, 6, 94, 1, '2026-06-08 15:41:10'),
(147, 11, 95, 1, '2026-06-08 15:41:10'),
(148, 8, 96, 1, '2026-06-08 15:41:10'),
(149, 12, 97, 1, '2026-06-08 15:41:10'),
(150, 12, 98, 1, '2026-06-08 13:53:10'),
(151, 12, 99, 1, '2026-06-08 16:00:57'),
(152, 2, 100, 1, '2026-06-08 15:46:19'),
(153, 7, 100, 1, '2026-06-08 15:46:19'),
(154, 10, 100, 1, '2026-06-08 15:46:19'),
(155, 1, 101, 1, '2026-06-08 15:41:10'),
(156, 2, 102, 1, '2026-06-08 15:41:10'),
(157, 3, 103, 1, '2026-06-08 15:41:10'),
(158, 10, 104, 1, '2026-06-08 15:41:10'),
(159, 4, 105, 1, '2026-06-08 15:41:10'),
(160, 7, 106, 1, '2026-06-08 15:41:10'),
(161, 5, 107, 1, '2026-06-08 15:41:10'),
(162, 6, 108, 1, '2026-06-08 15:41:10'),
(163, 11, 109, 1, '2026-06-08 15:41:10'),
(164, 8, 110, 1, '2026-06-08 15:41:10'),
(165, 12, 111, 1, '2026-06-08 15:41:10'),
(166, 11, 112, 1, '2026-06-08 13:53:10'),
(167, 1, 113, 1, '2026-06-08 13:53:10'),
(168, 2, 114, 1, '2026-06-08 13:53:10'),
(169, 3, 115, 1, '2026-06-08 13:53:10'),
(170, 1, 116, 1, '2026-06-08 15:41:10'),
(171, 2, 117, 1, '2026-06-08 15:41:10'),
(172, 3, 118, 1, '2026-06-08 15:41:10'),
(173, 1, 119, 1, '2026-06-08 15:41:10'),
(174, 2, 120, 1, '2026-06-08 15:41:10'),
(175, 3, 121, 1, '2026-06-08 15:41:10'),
(176, 1, 122, 1, '2026-06-08 15:41:10'),
(177, 2, 123, 1, '2026-06-08 15:41:10'),
(178, 3, 124, 1, '2026-06-08 15:41:10'),
(179, 6, 125, 1, '2026-06-08 13:53:09'),
(180, 7, 126, 1, '2026-06-08 13:53:10'),
(181, 10, 127, 1, '2026-06-08 13:53:10'),
(182, 10, 128, 1, '2026-06-08 13:53:10'),
(183, 6, 129, 1, '2026-06-08 13:53:10'),
(184, 10, 130, 1, '2026-06-08 15:41:10'),
(185, 7, 131, 1, '2026-06-08 15:41:10'),
(186, 6, 132, 1, '2026-06-08 15:41:10'),
(187, 10, 133, 1, '2026-06-08 15:41:10'),
(188, 7, 134, 1, '2026-06-08 15:41:10'),
(189, 6, 135, 1, '2026-06-08 15:41:10'),
(190, 10, 136, 1, '2026-06-08 15:41:10'),
(191, 7, 137, 1, '2026-06-08 15:41:10'),
(192, 6, 138, 1, '2026-06-08 15:41:10'),
(193, 4, 139, 1, '2026-06-08 13:53:10'),
(194, 11, 140, 1, '2026-06-08 13:53:10'),
(195, 4, 141, 1, '2026-06-08 13:53:10'),
(196, 11, 142, 1, '2026-06-08 13:53:10'),
(197, 5, 143, 1, '2026-06-08 13:53:10'),
(198, 4, 144, 1, '2026-06-08 15:41:10'),
(199, 5, 145, 1, '2026-06-08 15:41:10'),
(200, 11, 146, 1, '2026-06-08 15:41:10'),
(201, 4, 147, 1, '2026-06-08 15:41:10'),
(202, 5, 148, 1, '2026-06-08 15:41:10'),
(203, 11, 149, 1, '2026-06-08 15:41:10'),
(204, 4, 150, 1, '2026-06-08 15:41:10'),
(205, 5, 151, 1, '2026-06-08 15:41:10'),
(206, 11, 152, 1, '2026-06-08 15:41:10'),
(207, 8, 153, 1, '2026-06-08 13:53:10'),
(208, 1, 154, 1, '2026-06-08 13:53:10'),
(209, 1, 155, 1, '2026-06-08 15:46:19'),
(210, 4, 155, 1, '2026-06-08 15:46:19'),
(211, 5, 155, 1, '2026-06-08 15:46:19'),
(212, 1, 156, 1, '2026-06-08 15:41:10'),
(213, 2, 157, 1, '2026-06-08 15:41:10'),
(214, 3, 158, 1, '2026-06-08 15:41:10'),
(215, 10, 159, 1, '2026-06-08 15:41:10'),
(216, 4, 160, 1, '2026-06-08 15:41:10'),
(217, 7, 161, 1, '2026-06-08 15:41:10'),
(218, 5, 162, 1, '2026-06-08 15:41:10'),
(219, 6, 163, 1, '2026-06-08 15:41:10'),
(220, 11, 164, 1, '2026-06-08 15:41:10'),
(221, 8, 165, 1, '2026-06-08 15:41:10'),
(222, 12, 166, 1, '2026-06-08 15:41:10'),
(223, 12, 167, 1, '2026-06-08 13:53:10'),
(224, 3, 168, 1, '2026-06-08 13:53:10'),
(225, 1, 169, 1, '2026-06-08 13:53:10'),
(226, 2, 169, 1, '2026-06-08 13:53:10'),
(227, 3, 169, 1, '2026-06-08 13:53:10'),
(228, 1, 170, 1, '2026-06-08 13:53:09'),
(229, 2, 170, 1, '2026-06-08 13:53:09'),
(230, 3, 170, 1, '2026-06-08 13:53:09'),
(231, 4, 170, 1, '2026-06-08 13:53:09'),
(232, 5, 170, 1, '2026-06-08 13:53:09'),
(233, 6, 170, 1, '2026-06-08 13:53:09'),
(234, 7, 170, 1, '2026-06-08 13:53:09'),
(235, 8, 170, 1, '2026-06-08 13:53:09'),
(236, 9, 170, 1, '2026-06-08 13:53:09'),
(237, 10, 170, 1, '2026-06-08 13:53:09'),
(238, 11, 170, 1, '2026-06-08 13:53:09'),
(239, 12, 170, 1, '2026-06-08 13:53:09'),
(240, 13, 170, 1, '2026-06-08 13:53:09'),
(241, 9, 171, 1, '2026-06-08 16:00:57'),
(242, 8, 172, 1, '2026-06-08 16:15:20'),
(243, 9, 172, 1, '2026-06-08 16:15:20'),
(244, 12, 172, 1, '2026-06-08 16:15:20'),
(245, 6, 173, 1, '2026-06-08 13:53:10'),
(246, 8, 174, 1, '2026-06-08 16:17:58'),
(247, 9, 174, 1, '2026-06-08 16:17:58'),
(248, 12, 174, 1, '2026-06-08 16:17:58'),
(249, 8, 175, 1, '2026-06-08 13:53:10'),
(250, 9, 175, 1, '2026-06-08 13:53:10'),
(251, 12, 175, 1, '2026-06-08 13:53:10'),
(252, 3, 176, 1, '2026-06-08 15:30:10'),
(253, 1, 177, 1, '2026-06-08 15:41:10'),
(254, 1, 178, 1, '2026-06-08 16:17:59'),
(255, 2, 178, 1, '2026-06-08 16:17:59'),
(256, 3, 178, 1, '2026-06-08 16:17:59'),
(257, 3, 179, 1, '2026-06-08 13:53:10'),
(258, 4, 180, 1, '2026-06-08 16:17:59'),
(259, 7, 180, 1, '2026-06-08 16:17:59'),
(260, 11, 180, 1, '2026-06-08 16:17:59'),
(261, 1, 181, 1, '2026-06-08 13:53:10'),
(262, 2, 181, 1, '2026-06-08 13:53:10'),
(263, 3, 181, 1, '2026-06-08 13:53:10'),
(264, 4, 181, 1, '2026-06-08 13:53:10'),
(265, 5, 181, 1, '2026-06-08 13:53:10'),
(266, 5, 182, 1, '2026-06-08 16:17:59'),
(267, 6, 182, 1, '2026-06-08 16:17:59'),
(268, 10, 182, 1, '2026-06-08 16:17:59'),
(269, 6, 183, 1, '2026-06-08 13:53:10'),
(270, 7, 183, 1, '2026-06-08 13:53:10'),
(271, 8, 183, 1, '2026-06-08 13:53:10'),
(272, 9, 183, 1, '2026-06-08 13:53:10'),
(273, 10, 184, 1, '2026-06-08 13:53:10'),
(274, 11, 184, 1, '2026-06-08 13:53:10'),
(275, 12, 184, 1, '2026-06-08 13:53:10'),
(276, 13, 184, 1, '2026-06-08 13:53:10'),
(277, 11, 185, 1, '2026-06-08 13:53:10'),
(278, 7, 186, 1, '2026-06-08 13:53:10'),
(279, 10, 187, 1, '2026-06-08 13:53:10'),
(280, 1, 188, 1, '2026-06-08 13:53:10'),
(281, 13, 189, 1, '2026-06-08 13:53:10'),
(282, 1, 190, 1, '2026-06-08 16:00:57'),
(283, 2, 191, 1, '2026-06-08 16:00:57'),
(284, 3, 192, 1, '2026-06-08 16:00:57'),
(285, 8, 193, 1, '2026-06-08 16:00:57'),
(286, 9, 194, 1, '2026-06-08 16:00:57'),
(287, 12, 195, 1, '2026-06-08 16:00:57'),
(288, 8, 196, 1, '2026-06-08 13:53:10'),
(289, 4, 197, 1, '2026-06-08 13:53:10'),
(290, 9, 199, 1, '2026-06-08 13:53:10'),
(291, 2, 200, 1, '2026-06-08 13:53:10'),
(292, 6, 201, 1, '2026-06-08 13:53:10'),
(293, 7, 201, 1, '2026-06-08 13:53:10'),
(294, 10, 202, 1, '2026-06-08 13:53:10'),
(295, 11, 202, 1, '2026-06-08 13:53:10'),
(296, 4, 203, 1, '2026-06-08 13:53:10'),
(297, 5, 203, 1, '2026-06-08 13:53:10'),
(298, 12, 204, 1, '2026-06-08 13:53:10'),
(299, 5, 205, 1, '2026-06-08 16:17:59'),
(300, 6, 206, 1, '2026-06-08 16:17:59'),
(301, 3, 207, 1, '2026-06-08 16:17:59'),
(302, 11, 208, 1, '2026-06-08 16:17:59'),
(303, 5, 209, 1, '2026-06-08 16:17:59'),
(304, 6, 210, 1, '2026-06-08 16:17:59'),
(305, 3, 211, 1, '2026-06-08 16:17:59'),
(306, 11, 212, 1, '2026-06-08 16:17:59'),
(307, 8, 213, 1, '2026-06-08 13:53:10'),
(308, 9, 213, 1, '2026-06-08 13:53:10'),
(309, 12, 213, 1, '2026-06-08 13:53:10'),
(310, 12, 214, 1, '2026-06-08 13:53:10'),
(311, 9, 215, 1, '2026-06-08 13:53:10'),
(312, 5, 216, 1, '2026-06-08 13:53:10'),
(313, 8, 217, 1, '2026-06-08 13:53:10'),
(314, 12, 218, 1, '2026-06-08 15:57:25'),
(315, 12, 219, 1, '2026-06-08 15:57:25'),
(316, 12, 220, 1, '2026-06-08 16:17:58'),
(317, 13, 221, 1, '2026-06-08 16:00:57'),
(318, 3, 222, 1, '2026-06-08 13:53:10'),
(319, 1, 223, 1, '2026-06-08 16:00:57'),
(320, 2, 224, 1, '2026-06-08 16:00:57'),
(321, 3, 225, 1, '2026-06-08 16:00:57'),
(322, 4, 226, 1, '2026-06-08 16:00:57'),
(323, 5, 227, 1, '2026-06-08 16:00:57'),
(324, 6, 228, 1, '2026-06-08 16:00:57'),
(325, 7, 229, 1, '2026-06-08 16:00:57'),
(326, 8, 230, 1, '2026-06-08 16:00:57'),
(327, 9, 231, 1, '2026-06-08 16:00:57'),
(328, 10, 232, 1, '2026-06-08 16:00:57'),
(329, 11, 233, 1, '2026-06-08 16:00:57'),
(330, 12, 234, 1, '2026-06-08 16:00:57'),
(331, 13, 235, 1, '2026-06-08 16:00:57'),
(332, 1, 236, 1, '2026-06-08 16:00:57'),
(333, 2, 237, 1, '2026-06-08 16:00:57'),
(334, 3, 238, 1, '2026-06-08 16:00:57'),
(335, 4, 239, 1, '2026-06-08 16:00:57'),
(336, 5, 240, 1, '2026-06-08 16:00:57'),
(337, 6, 241, 1, '2026-06-08 16:00:57'),
(338, 7, 242, 1, '2026-06-08 16:00:57'),
(339, 8, 243, 1, '2026-06-08 16:00:57'),
(340, 9, 244, 1, '2026-06-08 16:00:57'),
(341, 10, 245, 1, '2026-06-08 16:00:57'),
(342, 11, 246, 1, '2026-06-08 16:00:57'),
(343, 12, 247, 1, '2026-06-08 16:00:57'),
(344, 13, 248, 1, '2026-06-08 16:00:57'),
(345, 10, 249, 1, '2026-06-08 15:30:10'),
(346, 1, 250, 1, '2026-06-08 13:53:10'),
(347, 8, 251, 1, '2026-06-08 13:53:10'),
(348, 7, 252, 1, '2026-06-08 16:17:59'),
(349, 10, 253, 1, '2026-06-08 16:17:59'),
(350, 4, 254, 1, '2026-06-08 16:17:59'),
(351, 2, 255, 1, '2026-06-08 16:17:59'),
(352, 7, 256, 1, '2026-06-08 16:17:59'),
(353, 10, 257, 1, '2026-06-08 16:17:59'),
(354, 4, 258, 1, '2026-06-08 16:17:59'),
(355, 2, 259, 1, '2026-06-08 16:17:59'),
(356, 9, 260, 1, '2026-06-08 13:53:10'),
(357, 9, 261, 1, '2026-06-08 15:53:44'),
(358, 13, 262, 1, '2026-06-08 15:53:44'),
(359, 9, 263, 1, '2026-06-08 15:53:44'),
(360, 13, 264, 1, '2026-06-08 15:53:44'),
(361, 5, 265, 1, '2026-06-08 16:03:03'),
(362, 6, 265, 1, '2026-06-08 16:03:03'),
(363, 10, 265, 1, '2026-06-08 16:03:03'),
(364, 1, 266, 1, '2026-06-08 13:53:10'),
(365, 2, 266, 1, '2026-06-08 13:53:10'),
(366, 3, 266, 1, '2026-06-08 13:53:10'),
(367, 4, 266, 1, '2026-06-08 13:53:10'),
(368, 5, 266, 1, '2026-06-08 13:53:10'),
(369, 6, 266, 1, '2026-06-08 13:53:10'),
(370, 7, 266, 1, '2026-06-08 13:53:10'),
(371, 8, 266, 1, '2026-06-08 13:53:10'),
(372, 9, 266, 1, '2026-06-08 13:53:10'),
(373, 10, 266, 1, '2026-06-08 13:53:10'),
(374, 11, 266, 1, '2026-06-08 13:53:10'),
(375, 12, 266, 1, '2026-06-08 13:53:10'),
(376, 13, 266, 1, '2026-06-08 13:53:10'),
(377, 13, 267, 1, '2026-06-08 13:53:10'),
(378, 13, 268, 1, '2026-06-08 13:53:10'),
(379, 9, 269, 1, '2026-06-08 15:57:25'),
(380, 9, 270, 1, '2026-06-08 15:57:25'),
(381, 9, 271, 1, '2026-06-08 16:17:58'),
(382, 8, 272, 1, '2026-06-08 16:15:20'),
(383, 8, 273, 1, '2026-06-08 16:17:58'),
(384, 1, 274, 1, '2026-06-08 16:03:03'),
(385, 2, 275, 1, '2026-06-08 16:03:03'),
(386, 3, 276, 1, '2026-06-08 16:03:03'),
(387, 10, 277, 1, '2026-06-08 16:03:03'),
(388, 4, 278, 1, '2026-06-08 16:03:03'),
(389, 7, 279, 1, '2026-06-08 16:03:03'),
(390, 5, 280, 1, '2026-06-08 16:03:03'),
(391, 6, 281, 1, '2026-06-08 16:03:03'),
(392, 11, 282, 1, '2026-06-08 16:03:03'),
(393, 8, 283, 1, '2026-06-08 16:03:03'),
(394, 12, 284, 1, '2026-06-08 16:03:03'),
(395, 9, 285, 1, '2026-06-08 16:03:03'),
(396, 2, 286, 1, '2026-06-08 16:03:03'),
(397, 6, 286, 1, '2026-06-08 16:03:03'),
(398, 7, 286, 1, '2026-06-08 16:03:03'),
(399, 12, 287, 1, '2026-06-08 16:17:59'),
(400, 1, 288, 1, '2026-06-08 16:17:59'),
(401, 8, 289, 1, '2026-06-08 16:17:59'),
(402, 9, 290, 1, '2026-06-08 16:17:59'),
(403, 12, 291, 1, '2026-06-08 16:17:59'),
(404, 1, 292, 1, '2026-06-08 16:17:59'),
(405, 8, 293, 1, '2026-06-08 16:17:59'),
(406, 9, 294, 1, '2026-06-08 16:17:59'),
(407, 7, 295, 1, '2026-06-08 13:53:10'),
(408, 8, 295, 1, '2026-06-08 13:53:10'),
(409, 9, 295, 1, '2026-06-08 13:53:10'),
(410, 11, 295, 1, '2026-06-08 13:53:10'),
(411, 12, 295, 1, '2026-06-08 13:53:10'),
(412, 13, 295, 1, '2026-06-08 13:53:10');

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `specialty` enum('vocal','dance','visual') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `teachers`
--

INSERT INTO `teachers` (`id`, `user_id`, `name`, `specialty`) VALUES
(1, 2, 'Vocal Trainer', 'vocal'),
(2, 3, 'Dance Trainer', 'dance'),
(3, 4, 'Visual Trainer', 'visual');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('producer','teacher','student') NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `avatar`, `is_active`, `created_at`, `updated_at`, `last_login`) VALUES
(1, 'Producer', '$2y$10$7BFxM6RgeDvBv6kDTKovuuIdwTzQBM4l1sI2EcNoeHa94mU0y/OM2', 'producer', NULL, 1, '2026-05-26 13:10:43', '2026-05-28 16:41:57', '2026-05-28 16:41:57'),
(2, 'Vocal Trainer', '$2y$10$mfACa/WYxB5KtG.u/sz0NOsEavc0F8j7fR.Zf/PF9hcEl7gdLMNdK', 'teacher', NULL, 1, '2026-05-26 13:11:33', '2026-05-27 11:39:47', NULL),
(3, 'Dance Trainer', '$2y$10$t9sRByIWadz.sKik8/7BCeBHMW8S.QZjUqdFzjr2seuuDd3Qx7dMi', 'teacher', NULL, 1, '2026-05-26 13:11:56', '2026-05-28 16:42:24', '2026-05-28 16:42:24'),
(4, 'Visual Trainer', '$2y$10$bGNC.jMo6tcO1e0f6A0q5erVt6QZ2LbnR4kS1AD47.vwZW0J3GQV6', 'teacher', NULL, 1, '2026-05-26 13:12:30', '2026-05-27 11:40:41', NULL),
(5, 'Saki Hanami', '$2y$10$ff9abDr59dXAcmMgP6QO3uqs90jpoKPhk3xs8sS5zY3kISRRDVyV6', 'student', 'Saki Hanami.png', 1, '2026-05-26 13:13:13', '2026-06-08 10:44:22', '2026-06-08 10:44:22'),
(6, 'Temari Tsukimura', '$2y$10$iSoM6ouEBxTnTSw1CXHFKe89P9Lat.OcPxFayJwF8Y9xNkFjBLrlm', 'student', 'Temari Tsukimura.png', 1, '2026-05-26 13:13:13', '2026-06-08 11:58:12', '2026-06-08 11:58:12'),
(7, 'Kotone Fujita', '$2y$10$b0uAqh6CXeKP3rxgJbKpC.4W2fcJKUEO/qD19wI7P9zfkRx66NYRC', 'student', 'Kotone Fujita.png', 1, '2026-05-26 13:13:13', '2026-05-28 15:58:24', '2026-05-28 15:58:24'),
(8, 'Lilja Katsuragi', '$2y$10$U0wQB4kmtvPYWWQX.9oi0OJ60HDhnd/yf.tuf71exJ8MoG8xS0FVa', 'student', 'Lilja Katsuragi.png', 1, '2026-05-26 13:13:13', '2026-06-08 10:49:40', '2026-06-08 10:49:40'),
(9, 'Sumika Shiun', '$2y$10$XWAu/FVsR0m4XnA9srsx7ev50L3zAlz0qQViMwbhYzSDkRh0KF6cO', 'student', 'Sumika Shiun.png', 1, '2026-05-26 13:13:13', '2026-06-08 10:54:21', '2026-06-08 10:54:21'),
(10, 'Hiro Shinosawa', '$2y$10$yw50phXBAJaDziCBQu8ul.P/V5GmB5qKyObADsMOiw5nMBEfly4o2', 'student', 'Hiro Shinosawa.png', 1, '2026-05-26 13:13:13', '2026-06-08 10:54:06', '2026-06-08 10:54:06'),
(11, 'China Kuramoto', '$2y$10$kFyzwQwuqHfCPtRN3f4N/uIbqFxw9efSIbK3GEcHjpRK3Vw7QE2jy', 'student', 'China Kuramoto.png', 1, '2026-05-26 13:13:13', '2026-06-08 11:53:15', '2026-06-08 11:53:15'),
(12, 'Ume Hanami', '$2y$10$v48vl/ymkgldjh/sUGwPHusiC7iBAahdLG4ynPeWTGjI.MCfn2Wvq', 'student', 'Ume Hanami.png', 1, '2026-05-26 13:13:13', '2026-06-03 13:07:24', '2026-06-03 13:07:24'),
(13, 'Misuzu Hataya', '$2y$10$o5HsBq.D2KCvw7/nTI6j3.Oauif4UvvViRtc3ntYj7O9JHQ2OoWv6', 'student', 'Misuzu Hataya.png', 1, '2026-05-26 13:13:13', '2026-06-08 11:34:47', '2026-06-08 11:34:47'),
(14, 'Mao Arimura', '$2y$10$k5WzxPeI8a9aX45ap/4nZ.a5gWd9dqCUbxYQtQKdf5nOHOB57hMIO', 'student', 'Mao Arimura.png', 1, '2026-05-26 13:13:13', '2026-06-08 11:34:17', '2026-06-08 11:34:17'),
(15, 'Rinami Himesaki', '$2y$10$Mc/amUU4R6ORKAqyn6WMt.S3EcLbF5jxjIq8c83h6D0kElQpB/XZW', 'student', 'Rinami Himesaki.png', 1, '2026-05-26 13:13:13', '2026-06-08 16:37:19', '2026-06-08 16:37:19'),
(16, 'Sena Juo', '$2y$10$9w5AtEo39Z/d6j516qa7g.dFqvnHdZ9Dvgn9Qdlue.tlcT76MSMsm', 'student', 'Sena Juo.png', 1, '2026-05-26 13:13:13', '2026-06-08 14:46:20', '2026-06-08 14:46:20'),
(17, 'Tsubame Amaya', '$2y$10$UIL5Hsv5tvvU27ie5Fvu9.DGDlnO8OMMzoUQaJ7ivZESs0Hhn1yJi', 'student', 'Tsubame Amaya.png', 1, '2026-05-26 13:13:13', '2026-06-08 14:29:20', '2026-06-08 14:29:20');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `daily_student_stats`
--
ALTER TABLE `daily_student_stats`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_daily_student_stats_student_date` (`student_id`,`stat_date`),
  ADD KEY `idx_daily_student_stats_date` (`stat_date`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_events_date` (`date`),
  ADD KEY `idx_events_type` (`type`);

--
-- Indexes for table `event_participants`
--
ALTER TABLE `event_participants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_event_student` (`event_id`,`student_id`),
  ADD KEY `idx_event_participants_student_id` (`student_id`);

--
-- Indexes for table `lessons`
--
ALTER TABLE `lessons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_lessons_type` (`type`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_messages_sender_id` (`sender_id`),
  ADD KEY `idx_messages_receiver_id` (`receiver_id`),
  ADD KEY `idx_messages_receiver_unread` (`receiver_id`,`is_read`,`is_deleted_by_receiver`),
  ADD KEY `idx_messages_parent` (`parent_message_id`),
  ADD KEY `idx_messages_sent_at` (`sent_at`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notifications_user_id` (`user_id`),
  ADD KEY `idx_notifications_user_unread` (`user_id`,`is_read`),
  ADD KEY `idx_notifications_created_at` (`created_at`);

--
-- Indexes for table `producer_messages`
--
ALTER TABLE `producer_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_producer_messages_producer_id` (`producer_id`),
  ADD KEY `idx_producer_messages_student_id` (`student_id`),
  ADD KEY `idx_producer_messages_lookup` (`student_id`,`message_type`);

--
-- Indexes for table `recurring_schedules`
--
ALTER TABLE `recurring_schedules`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_schedules_student_id` (`student_id`),
  ADD KEY `idx_schedules_date` (`date`),
  ADD KEY `idx_schedules_student_date` (`student_id`,`date`),
  ADD KEY `idx_schedules_created_by` (`created_by`),
  ADD KEY `idx_schedules_status` (`status`);

--
-- Indexes for table `songs`
--
ALTER TABLE `songs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_songs_title` (`title`),
  ADD KEY `idx_songs_release_date` (`release_date`),
  ADD KEY `idx_songs_created_by` (`created_by`);

--
-- Indexes for table `stat_history`
--
ALTER TABLE `stat_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_stat_history_student_id` (`student_id`),
  ADD KEY `idx_stat_history_recorded_at` (`recorded_at`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_students_user_id` (`user_id`),
  ADD KEY `idx_students_producer_id` (`producer_id`),
  ADD KEY `idx_students_rank` (`rank`);

--
-- Indexes for table `student_songs`
--
ALTER TABLE `student_songs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_student_song` (`student_id`,`song_id`),
  ADD KEY `idx_student_songs_song_id` (`song_id`),
  ADD KEY `idx_student_songs_added_by` (`added_by`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_teachers_user_id` (`user_id`),
  ADD KEY `idx_teachers_specialty` (`specialty`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_users_username` (`username`),
  ADD KEY `idx_users_role` (`role`),
  ADD KEY `idx_users_is_active` (`is_active`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `daily_student_stats`
--
ALTER TABLE `daily_student_stats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=207;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_participants`
--
ALTER TABLE `event_participants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lessons`
--
ALTER TABLE `lessons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `producer_messages`
--
ALTER TABLE `producer_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=391;

--
-- AUTO_INCREMENT for table `recurring_schedules`
--
ALTER TABLE `recurring_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=228;

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `songs`
--
ALTER TABLE `songs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=296;

--
-- AUTO_INCREMENT for table `stat_history`
--
ALTER TABLE `stat_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `student_songs`
--
ALTER TABLE `student_songs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=413;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `event_participants`
--
ALTER TABLE `event_participants`
  ADD CONSTRAINT `fk_event_participants_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_event_participants_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `fk_messages_parent` FOREIGN KEY (`parent_message_id`) REFERENCES `messages` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_messages_receiver` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_messages_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `producer_messages`
--
ALTER TABLE `producer_messages`
  ADD CONSTRAINT `fk_producer_messages_producer` FOREIGN KEY (`producer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_producer_messages_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `schedules`
--
ALTER TABLE `schedules`
  ADD CONSTRAINT `fk_schedules_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_schedules_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `songs`
--
ALTER TABLE `songs`
  ADD CONSTRAINT `fk_songs_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `stat_history`
--
ALTER TABLE `stat_history`
  ADD CONSTRAINT `fk_stat_history_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `fk_students_producer` FOREIGN KEY (`producer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_students_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_songs`
--
ALTER TABLE `student_songs`
  ADD CONSTRAINT `fk_student_songs_added_by` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_student_songs_song` FOREIGN KEY (`song_id`) REFERENCES `songs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_student_songs_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `teachers`
--
ALTER TABLE `teachers`
  ADD CONSTRAINT `fk_teachers_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
