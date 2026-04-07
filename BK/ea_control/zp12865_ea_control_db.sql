-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 10, 2026 at 09:09 PM
-- Server version: 10.6.16-MariaDB-log
-- PHP Version: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `zp12865_ea_control_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `mt_accounts`
--

CREATE TABLE `mt_accounts` (
  `account_number` bigint(20) NOT NULL COMMENT 'เลขบัญชี MT4/MT5',
  `account_name` varchar(100) DEFAULT NULL COMMENT 'ชื่อเจ้าของบัญชี',
  `broker` varchar(100) DEFAULT NULL COMMENT 'ชื่อโบรกเกอร์',
  `platform` varchar(10) DEFAULT 'MT4' COMMENT 'MT4 หรือ MT5',
  `balance` decimal(15,2) DEFAULT 0.00 COMMENT 'ยอด Balance',
  `equity` decimal(15,2) DEFAULT 0.00 COMMENT 'ยอด Equity',
  `margin` decimal(15,2) DEFAULT 0.00 COMMENT 'Margin ที่ใช้ไป',
  `free_margin` decimal(15,2) DEFAULT 0.00 COMMENT 'Margin คงเหลือ',
  `profit` decimal(15,2) DEFAULT 0.00 COMMENT 'กำไร/ขาดทุนรวม (Floating)',
  `daily_profit` decimal(15,2) DEFAULT 0.00,
  `orders_json` longtext DEFAULT NULL COMMENT 'เก็บรายการออเดอร์ทั้งหมดเป็น JSON String',
  `last_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'เวลาล่าสุดที่ EA ติดต่อเข้ามา',
  `vps_id` int(11) DEFAULT NULL,
  `ea_name` varchar(100) DEFAULT NULL,
  `mt_type` enum('MT4','MT5') DEFAULT NULL,
  `account_mode` enum('Demo','Real') DEFAULT NULL,
  `initial_capital` decimal(15,2) DEFAULT NULL,
  `trading_enabled` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `mt_accounts`
--

INSERT INTO `mt_accounts` (`account_number`, `account_name`, `broker`, `platform`, `balance`, `equity`, `margin`, `free_margin`, `profit`, `daily_profit`, `orders_json`, `last_update`, `vps_id`, `ea_name`, `mt_type`, `account_mode`, `initial_capital`, `trading_enabled`) VALUES
(216465, 'Chalantorn Nipittanakul', 'CXM Direct LLC', 'MT5', 10076.87, 10076.87, 0.00, 10076.87, 0.00, 76.21, '[]', '2026-02-10 14:09:00', 7, 'Monster_Killer_Animation', 'MT5', 'Demo', 10000.66, 1),
(624432, 'Chalantorn Nipittanakul', 'CXM Direct LLC', 'MT5', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, '[]', '2026-02-10 14:09:00', 1, 'Copy_VIGRO PRO V5 EA', 'MT5', 'Real', 1496.05, 1),
(7012021, 'Chalantorn Nipittanakul', 'CXM Direct LLC', 'MT4', 164318.19, 164318.19, 0.00, 164318.19, 0.00, 9754.00, '[]', '2026-02-10 14:09:00', 6, 'Gold Crazy Scalping', 'MT4', 'Demo', 10305.59, 1),
(7012025, 'Chalantorn Nipittanakul', 'CXM Direct LLC', 'MT4', 7932.07, 7932.07, 0.00, 7932.07, 0.00, 0.00, '[]', '2026-02-10 14:09:00', 1, 'VIGRO-PRO V6 EA', 'MT4', 'Demo', 7951.29, 1),
(7012749, 'Chalantorn Nipittanakul', 'CXM Direct LLC', 'MT4', 18671.38, 18671.38, 0.00, 18671.38, 0.00, 362.00, '[]', '2026-02-10 14:09:00', 7, 'VIGRO PRO V5 EA', 'MT4', 'Demo', 1027.74, 1),
(7012836, 'Chalantorn Nipittanakul', 'CXM Direct LLC', 'MT4', 15633.14, 15633.14, 0.00, 15633.14, 0.00, 0.00, '[]', '2026-02-10 14:09:00', 6, 'Copy_Gold Crazy Scalping', 'MT4', 'Demo', 1522.24, 1),
(20265204, 'Chalantorn Niplttanaku', 'Tickmill Ltd', 'MT4', 10436.30, 10436.30, 0.00, 10436.30, 0.00, 0.00, '[]', '2026-02-10 14:09:00', 4, 'Aurum Prime V2.65', 'MT4', 'Demo', 10000.00, 1),
(20266648, 'Chalantorn Niplttanaku', 'Tickmill Ltd', 'MT4', 583.06, 583.06, 0.00, 583.06, 0.00, 38.15, '[]', '2026-02-10 14:09:00', 4, 'MASHALLAH PRO V2', 'MT4', 'Demo', 523.06, 1),
(20268058, 'Chalantorn Niplttanaku', 'Tickmill Ltd', 'MT4', 58270.04, 58270.04, 0.00, 58270.04, 0.00, 0.00, '[]', '2026-02-10 02:24:07', 4, 'ICT_Venom_XAUUSD_EA_Final 2', 'MT4', 'Demo', 58270.04, 1),
(20270423, 'Chal Natto', 'Tickmill Ltd', 'MT4', 599.18, 390.45, 249.69, 140.76, -208.73, 15.60, '[{\"ticket\":235953276,\"symbol\":\"EURUSD\",\"type\":\"SELL\",\"lots\":0.05,\"open_price\":1.18317,\"open_time\":\"2026.02.09 07:12:04\",\"sl\":1.20652,\"tp\":1.17652,\"swap\":0.17,\"profit\":-38.85},{\"ticket\":235978232,\"symbol\":\"EURUSD\",\"type\":\"SELL\",\"lots\":0.1,\"open_price\":1.18545,\"open_time\":\"2026.02.09 08:42:41\",\"sl\":1.20652,\"tp\":1.17652,\"swap\":0.34,\"profit\":-54.9},{\"ticket\":236063591,\"symbol\":\"EURUSD\",\"type\":\"SELL\",\"lots\":0.15,\"open_price\":1.18763,\"open_time\":\"2026.02.09 14:18:52\",\"sl\":1.20652,\"tp\":1.17652,\"swap\":0.5,\"profit\":-49.65},{\"ticket\":236074571,\"symbol\":\"EURUSD\",\"type\":\"SELL\",\"lots\":0.2,\"open_price\":1.18863,\"open_time\":\"2026.02.09 15:06:11\",\"sl\":1.20652,\"tp\":1.17652,\"swap\":0.67,\"profit\":-46.2},{\"ticket\":236078463,\"symbol\":\"EURUSD\",\"type\":\"SELL\",\"lots\":0.25,\"open_price\":1.18963,\"open_time\":\"2026.02.09 15:24:27\",\"sl\":1.20652,\"tp\":1.17652,\"swap\":0.84,\"profit\":-32.75},{\"ticket\":236241790,\"symbol\":\"EURUSD\",\"type\":\"SELL\",\"lots\":0.3,\"open_price\":1.19152,\"open_time\":\"2026.02.10 05:01:30\",\"sl\":1.20652,\"tp\":1.17652,\"swap\":0,\"profit\":17.4}]', '2026-02-10 14:09:00', 4, 'Pips Sure EA V2.1', 'MT4', 'Demo', 350.28, 1),
(25259199, 'Chalantorn Niplttanaku', 'Tickmill Ltd', 'MT5', 20310.03, 20310.03, 0.00, 20310.03, 0.00, -20.00, '[]', '2026-02-10 14:09:00', 4, 'euro ai ea', 'MT5', 'Demo', 14941.18, 1),
(25270364, 'Chalantorn Niplttanaku', 'Tickmill Ltd', 'MT5', 26350.66, 26350.66, 0.00, 26350.66, 0.00, 4.18, '[]', '2026-02-10 14:09:00', 4, 'Monster_Killer_Animation', 'MT5', 'Demo', 26343.82, 1),
(44923783, 'Chalantorn Nipittanakul', 'Raw Trading Ltd', 'MT4', 10075.53, 10027.85, 9.44, 10018.41, -47.68, 0.00, '[{\"ticket\":348117847,\"symbol\":\"EURUSD\",\"type\":\"SELL\",\"lots\":0.01,\"open_price\":1.17735,\"open_time\":\"2026.02.06 02:30:01\",\"sl\":0,\"tp\":0,\"swap\":0.06,\"profit\":-13.61},{\"ticket\":348156425,\"symbol\":\"EURUSD\",\"type\":\"SELL\",\"lots\":0.02,\"open_price\":1.17936,\"open_time\":\"2026.02.06 06:38:59\",\"sl\":0,\"tp\":0,\"swap\":0.1,\"profit\":-23.2},{\"ticket\":348254428,\"symbol\":\"EURUSD\",\"type\":\"SELL\",\"lots\":0.02,\"open_price\":1.18139,\"open_time\":\"2026.02.06 16:03:58\",\"sl\":0,\"tp\":0,\"swap\":0.1,\"profit\":-19.14},{\"ticket\":348264505,\"symbol\":\"EURUSD\",\"type\":\"BUY\",\"lots\":0.01,\"open_price\":1.18236,\"open_time\":\"2026.02.06 16:45:00\",\"sl\":0,\"tp\":0,\"swap\":-0.16,\"profit\":8.59}]', '2026-02-10 14:09:00', 2, 'EA Game Changer V2.0', 'MT4', 'Demo', 10005.24, 1),
(50013711, 'Chalantorn Nipittanakul', 'CXM Direct LLC', 'MT4', 1535.77, 1535.77, 0.00, 1535.77, 0.00, 33.91, '[]', '2026-02-10 14:09:00', 6, 'AiXTradeX EA\'s HFT v12.7', 'MT4', 'Real', 1522.62, 1),
(52236435, 'Chalantorn Nipittanakul', 'Raw Trading Ltd', 'MT5', 256.96, 256.96, 0.00, 256.96, 0.00, 0.00, '[]', '2026-02-10 14:09:00', 2, 'Gold Trend Scalping v5.5', 'MT5', 'Demo', 179.42, 1),
(52567965, 'Chalantorn Nipittanakul', 'Raw Trading Ltd', 'MT5', 6214.43, 6214.43, 0.00, 6214.43, 0.00, 120.88, '[]', '2026-02-10 14:09:00', 2, 'Hero 5.3', 'MT5', 'Demo', 5481.86, 1),
(52657061, 'Chalantorn Nipittanakul', 'Raw Trading Ltd', 'MT5', 50540.96, 50540.96, 0.00, 50540.96, 0.00, 22.10, '[]', '2026-02-10 14:09:00', 2, 'Veerox Algo', 'MT5', 'Demo', 49865.97, 1),
(61345538, 'Chalantorn Nipittanakul', 'RoboForex Ltd', 'MT4', 31984.44, 31984.44, 0.00, 31984.44, 0.00, 218.50, '[]', '2026-02-10 14:09:00', 7, 'EA Game Changer V2.0', 'MT4', 'Demo', 30000.00, 1),
(67172124, 'Chalantorn Nipittanakul', 'RoboForex Ltd', 'MT5', 16613.63, 16613.63, 0.00, 16613.63, 0.00, 65.58, '[]', '2026-02-10 14:09:00', 7, 'CCBSN v2.4 HappyFarm', 'MT5', 'Demo', 11015.42, 1),
(67172599, 'Chalantorn Nipittanakul', 'RoboForex Ltd', 'MT5', 53735.97, 53735.45, 30.33, 53705.12, -0.52, 3863.05, '[{\"ticket\":616060396,\"symbol\":\"XAUUSD\",\"type\":\"BUY\",\"lots\":0.01,\"open_price\":5054.73,\"open_time\":\"2026.02.10 16:08:49\",\"sl\":0,\"tp\":0,\"swap\":0,\"profit\":-0.7},{\"ticket\":616060437,\"symbol\":\"XAUUSD\",\"type\":\"BUY\",\"lots\":0.01,\"open_price\":5054.09,\"open_time\":\"2026.02.10 16:08:53\",\"sl\":0,\"tp\":0,\"swap\":0,\"profit\":-0.06},{\"ticket\":616060451,\"symbol\":\"XAUUSD\",\"type\":\"BUY\",\"lots\":0.01,\"open_price\":5053.79,\"open_time\":\"2026.02.10 16:08:55\",\"sl\":0,\"tp\":0,\"swap\":0,\"profit\":0.24}]', '2026-02-10 14:09:00', 3, 'BTX-SCLAPER PRO', 'MT5', 'Demo', 40891.56, 1),
(67174810, 'Chalantorn Nipittanakul', 'RoboForex Ltd', 'MT5', 7573.70, 7573.70, 0.00, 7573.70, 0.00, 0.00, '[]', '2026-02-10 14:09:00', 1, 'Aurum Nexus AI v2.1', 'MT5', 'Demo', 6180.45, 1),
(68278766, 'Chalantorn Nipittanakul', 'RoboForex Ltd', 'MT5', 19418.72, 19418.72, 0.00, 19418.72, 0.00, -4313.45, '[]', '2026-02-10 14:09:00', 1, 'RTX_TuTienGioi_Eng_v3.18', 'MT5', 'Demo', 10014.36, 1),
(70499897, 'Raw Spread', 'Exness Technologies Ltd', 'MT4', 2211.08, 2211.08, 0.00, 2211.08, 0.00, 0.00, '[]', '2026-02-10 14:09:00', 3, 'ST Breakout EA v2', 'MT4', 'Demo', 2211.08, 1),
(70499899, 'XAUUSD M1 Sniper', 'Exness Technologies Ltd', 'MT4', 11306.38, 11306.38, 0.00, 11306.38, 0.00, 8.35, '[]', '2026-02-10 14:09:00', 3, 'Ultra Hedge Scalper EA v1.6b', 'MT4', 'Demo', 11119.25, 1),
(70655258, 'Volume Scalper V4.0', 'Exness Technologies Ltd', 'MT4', 1038.78, 1038.78, 0.00, 1038.78, 0.00, 0.00, '[]', '2026-02-10 14:09:00', 6, 'Volume Scalper V4.0', 'MT4', 'Demo', 1038.78, 1),
(87206744, 'Gold Hitter V4.0', 'Exness Technologies Ltd', 'MT4', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, '[]', '2026-02-10 14:09:00', 3, 'TOK_M5_BOT', 'MT4', 'Real', 970.08, 1),
(105411992, 'Chalantorn Nipittanakul', 'FBS Markets Inc.', 'MT5', 1285.50, 1285.50, 0.00, 1285.50, 0.00, 108.00, '[]', '2026-02-10 14:09:00', 7, 'The Breaker V4.0', 'MT5', 'Demo', 200.00, 1),
(105412371, 'Chalantorn Nipittanakul', 'FBS Markets Inc.', 'MT5', 10741.99, 10741.99, 0.00, 10741.99, 0.00, 0.00, '[]', '2026-02-10 14:09:00', 2, 'XAU_Sovereign_Hybrid v1.14', 'MT5', 'Demo', 10209.50, 1),
(105540353, 'Chalantorn Nipittanakul', 'FBS Markets Inc.', 'MT5', 52563.79, 52563.79, 0.00, 52563.79, 0.00, 1736.79, '[]', '2026-02-10 14:09:00', 2, 'Monster_Killer_Animation', 'MT5', 'Demo', 50000.00, 1),
(105586859, 'Chalantorn Nipittanakul', 'FBS Markets Inc.', 'MT5', 1000.00, 1000.00, 0.00, 1000.00, 0.00, 0.00, '[]', '2026-02-10 14:09:00', 7, 'Project Evolution Breakout Scalper', 'MT5', 'Demo', 1000.00, 1),
(206649821, 'Raw Spread', 'Exness Technologies Ltd', 'MT5', 36124.56, 36124.56, 0.00, 36124.56, 0.00, 0.00, '[]', '2026-02-10 14:09:00', 6, 'The Breaker V4.0', 'MT5', 'Demo', 30070.56, 1),
(206829966, 'Raw Spread', 'Exness Technologies Ltd', 'MT5', 9495.18, 9495.18, 0.00, 9495.18, 0.00, 0.00, '[]', '2026-02-10 14:09:00', 1, 'XAU_Sovereign_Hybrid v1.14', 'MT5', 'Demo', 10000.00, 1),
(263292686, 'Standard Cent', 'Exness Technologies Ltd', 'MT5', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, '[]', '2026-02-10 01:48:12', 3, 'Gold M1 Scalper', 'MT5', 'Real', 0.00, 1),
(279343838, 'Zero', 'Exness Technologies Ltd', 'MT5', 24151.89, 24151.89, 0.00, 24151.89, 0.00, 10.21, '[]', '2026-02-10 14:09:00', 3, 'Long Run Single Entry', 'MT5', 'Demo', 23895.77, 1),
(1100221203, 'Nattima BOOTSRI', 'Just Global Markets Ltd.', 'MT5', 67490.44, 67490.79, 10.11, 67480.68, 0.35, 59.56, '[{\"ticket\":1581209526,\"symbol\":\"XAUUSD.ecn\",\"type\":\"BUY\",\"lots\":0.01,\"open_price\":5054.14,\"open_time\":\"2026.02.10 16:08:55\",\"sl\":5053.44,\"tp\":0,\"swap\":0,\"profit\":0.35}]', '2026-02-10 14:09:00', 1, 'GOLD FOUNDRY AG', 'MT5', 'Demo', 10080.84, 1);

-- --------------------------------------------------------

--
-- Table structure for table `mt_commands`
--

CREATE TABLE `mt_commands` (
  `id` int(11) NOT NULL,
  `account_number` bigint(20) NOT NULL DEFAULT 0 COMMENT 'เลขบัญชีเป้าหมาย (ใส่ 0 ถ้าสั่งทุกบัญชี)',
  `command_type` varchar(50) NOT NULL COMMENT 'ประเภทคำสั่ง เช่น CLOSE_ALL, CLOSE_TICKET',
  `params` text DEFAULT NULL COMMENT 'พารามิเตอร์เสริม เช่น Ticket ID (JSON หรือ String)',
  `status` enum('PENDING','PICKED','COMPLETED','FAILED') NOT NULL DEFAULT 'PENDING' COMMENT 'สถานะคำสั่ง',
  `result_message` text DEFAULT NULL COMMENT 'ข้อความตอบกลับจาก EA หลังทำคำสั่งเสร็จ',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'เวลาที่สั่ง',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'เวลาที่สถานะเปลี่ยน'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `mt_commands`
--

INSERT INTO `mt_commands` (`id`, `account_number`, `command_type`, `params`, `status`, `result_message`, `created_at`, `updated_at`) VALUES
(1, 52657061, 'CLOSE_ALL', NULL, 'PICKED', NULL, '2026-01-16 17:24:46', '2026-01-16 17:24:46'),
(2, 20268058, 'CLOSE_ALL', NULL, 'PICKED', NULL, '2026-01-16 18:46:32', '2026-01-16 18:46:35'),
(3, 7012021, 'CLOSE_ALL', NULL, 'PICKED', NULL, '2026-01-19 02:56:30', '2026-01-19 02:56:31'),
(5, 25270364, 'CLOSE_ALL', NULL, 'PICKED', NULL, '2026-01-19 16:40:24', '2026-01-19 16:40:24'),
(6, 67174810, 'CLOSE_ALL', NULL, 'PICKED', NULL, '2026-01-20 05:43:54', '2026-01-20 05:43:55'),
(8, 44923783, 'CLOSE_ALL', NULL, 'PICKED', NULL, '2026-01-20 05:48:01', '2026-01-20 05:48:04'),
(9, 20268058, 'CLOSE_ALL', NULL, 'PICKED', NULL, '2026-01-20 05:48:31', '2026-01-20 05:48:33'),
(10, 20268058, 'CLOSE_ALL', NULL, 'PICKED', NULL, '2026-01-20 05:48:41', '2026-01-20 05:48:45'),
(11, 25270364, 'CLOSE_ALL', NULL, 'PICKED', NULL, '2026-01-20 07:24:40', '2026-01-20 07:24:41'),
(12, 7012836, 'CLOSE_ALL', NULL, 'PICKED', NULL, '2026-01-20 07:27:02', '2026-01-20 07:27:03'),
(13, 67174810, 'CLOSE_ALL', NULL, 'PICKED', NULL, '2026-01-20 09:37:24', '2026-01-20 09:37:26'),
(14, 20270423, 'CLOSE_ALL', NULL, 'PICKED', NULL, '2026-01-20 09:39:39', '2026-01-20 09:39:40'),
(15, 105540353, 'CLOSE_ALL', NULL, 'PICKED', NULL, '2026-01-20 09:40:17', '2026-01-20 09:40:18'),
(18, 20268058, 'CLOSE_ALL', NULL, 'PICKED', NULL, '2026-01-21 03:35:02', '2026-01-21 03:35:03'),
(19, 52567965, 'CLOSE_ALL', NULL, 'PICKED', NULL, '2026-01-26 12:52:41', '2026-01-26 12:52:41'),
(20, 68278766, 'CLOSE_ALL', NULL, 'PICKED', NULL, '2026-01-26 17:14:04', '2026-01-26 17:14:07'),
(21, 68278766, 'CLOSE_ALL', NULL, 'PICKED', NULL, '2026-01-26 23:21:28', '2026-01-26 23:21:30'),
(22, 68278766, 'CLOSE_ALL', NULL, 'PICKED', NULL, '2026-01-27 06:26:35', '2026-01-27 06:26:36'),
(23, 68278766, 'CLOSE_ALL', NULL, 'PICKED', NULL, '2026-01-27 06:27:00', '2026-01-27 06:27:00'),
(24, 68278766, 'CLOSE_ALL', NULL, 'PICKED', NULL, '2026-01-27 06:38:58', '2026-01-27 06:39:01'),
(25, 105540353, 'CLOSE_ALL', NULL, 'PICKED', NULL, '2026-01-29 14:57:56', '2026-01-29 14:57:58'),
(26, 25270364, 'CLOSE_ALL', NULL, 'PICKED', NULL, '2026-01-30 10:03:36', '2026-01-30 10:03:38'),
(27, 25270364, 'CLOSE_ALL', NULL, 'PICKED', NULL, '2026-02-01 22:52:38', '2026-02-01 22:52:40'),
(28, 44923783, 'CLOSE_ALL', NULL, 'PICKED', NULL, '2026-02-01 22:53:02', '2026-02-01 22:53:05'),
(29, 206829966, 'CLOSE_ALL', NULL, 'PICKED', NULL, '2026-02-01 22:53:13', '2026-02-01 22:53:14'),
(30, 70499899, 'CLOSE_ALL', NULL, 'PICKED', NULL, '2026-02-01 22:53:25', '2026-02-01 22:53:26'),
(31, 7012836, 'CLOSE_ALL', NULL, 'PICKED', NULL, '2026-02-01 22:53:34', '2026-02-01 22:53:35'),
(32, 25270364, 'CLOSE_ALL', NULL, 'PICKED', NULL, '2026-02-01 23:54:28', '2026-02-01 23:54:31'),
(33, 25270364, 'CLOSE_ALL', NULL, 'PICKED', NULL, '2026-02-02 10:29:42', '2026-02-02 10:29:43'),
(34, 87206744, 'CLOSE_ALL', NULL, 'PICKED', NULL, '2026-02-02 15:54:40', '2026-02-02 15:54:40'),
(35, 206829966, 'CLOSE_ALL', NULL, 'PICKED', NULL, '2026-02-04 05:47:05', '2026-02-04 05:47:06'),
(36, 624432, 'CLOSE_ALL', NULL, 'PICKED', NULL, '2026-02-04 14:10:17', '2026-02-04 14:10:19'),
(37, 25270364, 'CLOSE_ALL', NULL, 'PICKED', NULL, '2026-02-06 00:00:11', '2026-02-06 00:00:11'),
(38, 68278766, 'CLOSE_ALL', NULL, 'PICKED', NULL, '2026-02-10 01:04:04', '2026-02-10 01:04:06');

-- --------------------------------------------------------

--
-- Table structure for table `vps_list`
--

CREATE TABLE `vps_list` (
  `id` int(11) NOT NULL,
  `vps_name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vps_list`
--

INSERT INTO `vps_list` (`id`, `vps_name`, `created_at`) VALUES
(1, 'Hyonix London  |  188.253.106.175', '2026-01-16 17:00:01'),
(2, 'Hyonix New York  |  38.240.39.34', '2026-01-16 17:00:24'),
(3, 'MT4Cloud Singapore  |  139.180.142.177:12308', '2026-01-16 17:00:46'),
(4, 'MT4Cloud Falkenstein EUROPE  |  168.119.245.173:12308', '2026-01-16 17:01:09'),
(5, 'MT4Cloud London  |  ip18.mt4.cloud:5274', '2026-01-16 17:01:27'),
(6, 'MT4Cloud Singapore  |  45.77.253.207:12308', '2026-01-16 17:01:40'),
(7, 'MT4Cloud London | 78.141.195.173', '2026-01-18 15:34:14');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `mt_accounts`
--
ALTER TABLE `mt_accounts`
  ADD PRIMARY KEY (`account_number`),
  ADD KEY `idx_last_update` (`last_update`);

--
-- Indexes for table `mt_commands`
--
ALTER TABLE `mt_commands`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status_acc` (`status`,`account_number`);

--
-- Indexes for table `vps_list`
--
ALTER TABLE `vps_list`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `mt_commands`
--
ALTER TABLE `mt_commands`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `vps_list`
--
ALTER TABLE `vps_list`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
