-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 11, 2025 at 12:40 PM
-- Server version: 8.4.3
-- PHP Version: 8.3.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `company_dam`
--

-- --------------------------------------------------------

--
-- Table structure for table `assets`
--

CREATE TABLE `assets` (
  `asset_id` int NOT NULL,
  `user_id` int NOT NULL,
  `name` varchar(150) NOT NULL,
  `department` varchar(100) DEFAULT NULL,
  `title` varchar(150) NOT NULL,
  `type` enum('PDF','JPG','PNG','EXCEL','DOCS','OTHER') NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `description` text,
  `file_path` varchar(255) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `reject_reason` text,
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `assets`
--

INSERT INTO `assets` (`asset_id`, `user_id`, `name`, `department`, `title`, `type`, `category`, `description`, `file_path`, `status`, `reject_reason`, `uploaded_at`) VALUES
(1, 1, 'Nik Shalihah Hanaan', 'Marketing', 'AHLI JAWATANKUASA MAJLIS ANUGERAH PERSATUAN FSM', 'PDF', 'Award Ceremony', 'Ubah suai bismillah', 'uploads/1765451849_poster_dday.png', 'pending', 'irrelevant', '2025-11-20 07:00:46'),
(3, 3, 'Balqis Nabila', NULL, 'Cute Game', 'PNG', 'Game', 'A game that has questions about Canva', 'uploads/1764026141_canva_comel.png', 'approved', NULL, '2025-11-24 23:15:41'),
(4, 4, 'Nur Anis Shazleen', NULL, 'Car sales', 'EXCEL', 'Sales', 'Car sales in November', 'uploads/1764033726_car_sales.csv', 'approved', NULL, '2025-11-25 01:22:06'),
(6, 4, 'shazleen', 'Finance', 'Web archiving', 'PDF', 'report', 'progress', 'uploads/1765443232_ICM561_-_CASE_STUDY_-_FARAH_AQILAH_BINTI_BADRUL_HISHAM__2025197245_.pdf', 'approved', NULL, '2025-12-11 08:53:52'),
(7, 2, 'Shalihah', 'IT', 'presentation', 'PDF', 'slides', 'rujukan', 'uploads/1765443860_Topik_7_TEKNOLOGI_MAKLUMAT_DAN_KOMUNIKASI_PENGGERAK_KESEPADUAN_NASIONAL_DI_MALAYSIA1__1_.pdf', 'approved', NULL, '2025-12-11 09:04:20'),
(8, 2, 'Shalihah', 'IT', 'LOGO', 'PNG', '-', 'LGOGOGOGO', 'uploads/1765444185_logodigikeep.png', 'approved', NULL, '2025-12-11 09:09:45'),
(9, 1, 'farah aqilah', 'Marketing', 'gerak kerja', 'PDF', NULL, 'entah aaa', 'uploads/1765451815_AAGRBT0C7xk_1764972604551.png', 'pending', 'irrelevant', '2025-12-11 09:34:37'),
(10, 2, 'Shalihah', 'IT', 'poster', 'PNG', NULL, 'boleh tengok tak', 'uploads/1765445753_AAGRBT0C7xk_1765164530663.png', 'approved', NULL, '2025-12-11 09:35:53'),
(12, 10, 'wonyoung', 'Executive', 'gambar', 'PNG', NULL, 'ttt', 'uploads/1765451329_banner_dday.png', 'approved', NULL, '2025-12-11 10:21:48'),
(13, 10, 'wonyoung', 'Executive', 'gerak kerja', 'PDF', NULL, 'progress and tentative', 'uploads/1765449137_Gerak_Kerja_Program_Walk_with_Art_-_Steps_of_Inspiration.pdf', 'approved', NULL, '2025-12-11 10:32:17'),
(14, 4, 'shazleen', 'Finance', 'jadual', 'EXCEL', NULL, 'timetable dodgeball', 'uploads/1765449260_JADUAL_BOLA_BALING_LELAKI_15_PASUKAN_INTERFAC_25.xlsx', 'rejected', 'IRRELEVANT', '2025-12-11 10:34:20'),
(15, 2, 'Shalihah', 'IT', 'student', 'EXCEL', NULL, 'perform', 'uploads/1765449496_student_habits_performance__1_.csv', 'approved', NULL, '2025-12-11 10:38:16'),
(16, 11, 'Ali', 'Operations', 'CHATGPT', 'PNG', NULL, 'MEME', 'uploads/1765452771_ChatGPT_Image_Dec_1__2025__07_38_57_PM.png', 'pending', NULL, '2025-12-11 11:32:51'),
(17, 14, 'Afiqah', 'Sales', 'Jadual perlawanan', 'PDF', NULL, 'You may refer this', 'uploads/1765454939_Topik_7_TEKNOLOGI_MAKLUMAT_DAN_KOMUNIKASI_PENGGERAK_KESEPADUAN_NASIONAL_DI_MALAYSIA1__1_.pdf', 'pending', NULL, '2025-12-11 11:42:16'),
(18, 14, 'afiqah', 'Sales', 'case study', 'PDF', NULL, 'assignment...', 'uploads/1765455009_ICM561_-_CASE_STUDY_-_FARAH_AQILAH_BINTI_BADRUL_HISHAM__2025197245_.pdf', 'pending', NULL, '2025-12-11 12:10:09'),
(19, 10, 'wonyoung', 'Executive', 'AGM', 'PNG', NULL, 'Gambar', 'uploads/1765455177_WhatsApp_Image_2025-12-04_at_7.04.24_PM.jpeg', 'approved', NULL, '2025-12-11 12:12:36');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int NOT NULL,
  `username` varchar(80) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') NOT NULL,
  `department` varchar(80) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `role`, `department`, `created_at`) VALUES
(1, 'farahaqilah21', '$2y$10$p1f18VcAd07mQtLwuEyOO./dz3z04Hv0/R1UMI1FFnunueCmlUyWW', 'user', 'Marketing', '2025-11-20 06:13:54'),
(2, 'shalihah21', '$2y$10$O.6hLZS0l/SPzkdF46N0iuBbKlH/DgK.QXrdRxFtbT4VVvm.UAfcS', 'admin', 'IT', '2025-11-20 06:16:50'),
(3, 'balqis21', '$2y$10$50DHOWywXfEaJDonj7MA5umoxm93PKwPjitdHq8zA1Wdc6T0pXTom', 'user', 'HR', '2025-11-24 23:13:27'),
(4, 'shazleen21', '$2y$10$TjUM/3ciPI3V6dLqhtYileXTLs0h12BX4YW/BgdStTI1wmV/FQx82', 'user', 'Finance', '2025-11-25 01:20:52'),
(5, 'nuaim21', '$2y$10$dYypwPp2PkOg/7CpPKIl5ulJSDt2HcG4215DW.KAbiSpoqVtOh3oW', 'user', 'HR', '2025-12-11 03:48:58'),
(6, 'admin', '$2y$10$wS1.8.1.8.1.8.1.8.1.8.1.8.1.8.1.8.1.8.1.8.1.8.1.8.1.8', 'admin', NULL, '2025-12-11 08:31:21'),
(8, 'shazleenuar', '$2y$10$JNrqQuuBskTJU0b9.6wpW.LADxrEMF7NSe6.L3Zw7aAQaT7QXBoU6', 'user', 'Marketing', '2025-12-11 08:35:05'),
(9, 'nabila21', '$2y$10$FglGSdJm6aSFAvCoF5/Gd.9pyN4E9M.cffYOJlx3IY0N0ry0n5B0W', 'admin', 'HR', '2025-12-11 08:56:41'),
(10, 'jangwonyoung', '$2y$10$wzIuYwJn4kD7SQ7RX0DTzeX/eHeBZt9OyUVbyKLtyl4qM1peDNmN6', 'admin', 'Executive', '2025-12-11 09:38:38'),
(11, 'ali21', '$2y$10$6QD..XDP5NqqbJJX0LWaWOXe8SCRjpDCBurYPDMBUXJV/RzoIVa0m', 'user', 'Operations', '2025-12-11 11:32:03'),
(12, 'Mimi21', '$2y$10$40kbv87E3/llXhKJFT4bougzfV1xLQa/T.ju7xMy2b6/zpSt0lWhS', 'user', 'IT', '2025-12-11 11:36:14'),
(13, 'Alyssa21', '$2y$10$ZzQyuJbjQ9L.pfxMy4rzR.B2/izDavRgcIUSnWEkkI7JVJ7G37pju', 'user', 'HR', '2025-12-11 11:37:44'),
(14, 'Afiqah21', '$2y$10$ykMZOxu37Zri.d4St8qfHOkSw3mVKl/5kTWZ2MmL0.ROtHo/8oWau', 'user', 'Sales', '2025-12-11 11:38:30'),
(15, 'Aisyah21', '$2y$10$FC8lKRgsYLM11NVvco1DwumcSTqWhC2XRSuiiwugxBtfmq0Ii4Op2', 'user', 'R&D', '2025-12-11 11:38:51'),
(16, 'Jenolee21', '$2y$10$RM7j3.aepdFlbEEXYIvg6.uBpu1l1Ojm6YhLY54wkvhmtWGtWLW36', 'user', 'Executive', '2025-12-11 11:39:12');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `assets`
--
ALTER TABLE `assets`
  ADD PRIMARY KEY (`asset_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `assets`
--
ALTER TABLE `assets`
  MODIFY `asset_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assets`
--
ALTER TABLE `assets`
  ADD CONSTRAINT `assets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
