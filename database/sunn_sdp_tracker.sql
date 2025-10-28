-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Oct 20, 2025 at 01:05 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sunn_sdp_tracker`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_accomplishment`
--

CREATE TABLE `tbl_accomplishment` (
  `accomplishment_id` int(11) NOT NULL,
  `sdp_id` int(11) NOT NULL,
  `accomplishment` varchar(255) DEFAULT NULL COMMENT 'Actual value achieved',
  `r_matrix_id` int(11) DEFAULT NULL,
  `evidence` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_activity_logs`
--

CREATE TABLE `tbl_activity_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `module` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_app_setting`
--

CREATE TABLE `tbl_app_setting` (
  `setting_id` int(11) NOT NULL,
  `app_name` varchar(100) NOT NULL,
  `address` text DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `about` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `logo` varchar(255) NOT NULL DEFAULT 'default.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_app_setting`
--

INSERT INTO `tbl_app_setting` (`setting_id`, `app_name`, `address`, `contact_number`, `email`, `about`, `updated_at`, `logo`) VALUES
(1, 'SUNN SDP TRACKER', 'SUNN', '0934569876', 'sunn_eat@sunn.edu.ph', 'SUNN SDP TRACKER', '2025-10-14 06:04:44', 'logo_68edb6df0b78a4.59004219.png');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_objectives`
--

CREATE TABLE `tbl_objectives` (
  `objectives_id` int(11) NOT NULL,
  `objectives_details` text NOT NULL,
  `focus_area` varchar(255) DEFAULT NULL COMMENT 'RISENUP areas'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_responsibility_matrix`
--

CREATE TABLE `tbl_responsibility_matrix` (
  `r_matrix_id` int(11) NOT NULL,
  `office_unit` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_sdp`
--

CREATE TABLE `tbl_sdp` (
  `sdp_id` int(11) NOT NULL,
  `objectives_id` int(11) DEFAULT NULL,
  `kpi` varchar(255) DEFAULT NULL,
  `initiatives` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_sdp_responsibility`
--

CREATE TABLE `tbl_sdp_responsibility` (
  `s_responsibility_id` int(11) NOT NULL,
  `sdp_id` int(11) NOT NULL,
  `r_matrix_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_target`
--

CREATE TABLE `tbl_target` (
  `target_id` int(11) NOT NULL,
  `sdp_id` int(11) NOT NULL,
  `target_value` decimal(12,2) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `value_type` enum('Percentage','Count','Index','Other') DEFAULT 'Other',
  `quarter` enum('Q1','Q2','Q3','Q4') DEFAULT NULL,
  `year` year(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user`
--

CREATE TABLE `tbl_user` (
  `user_id` int(11) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Admin','Faculty','Staff','Dean','Director') DEFAULT 'Staff',
  `created_at` datetime DEFAULT current_timestamp(),
  `r_matrix_id` int(11) DEFAULT NULL COMMENT 'office'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_user`
--

INSERT INTO `tbl_user` (`user_id`, `first_name`, `middle_name`, `last_name`, `position`, `username`, `password`, `role`, `created_at`, `r_matrix_id`) VALUES
(3, 'Sys', NULL, 'John', 'System Admin', 'admin', '$2y$10$ofq8sNB07Buq9CMWszNBoukK0EEGeeT99HTxZkddR1fGE48JHGIOG', 'Admin', '2025-08-05 08:30:59', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_accomplishment`
--
ALTER TABLE `tbl_accomplishment`
  ADD PRIMARY KEY (`accomplishment_id`),
  ADD KEY `sdp_id` (`sdp_id`),
  ADD KEY `r_matrix_id` (`r_matrix_id`);

--
-- Indexes for table `tbl_activity_logs`
--
ALTER TABLE `tbl_activity_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tbl_app_setting`
--
ALTER TABLE `tbl_app_setting`
  ADD PRIMARY KEY (`setting_id`);

--
-- Indexes for table `tbl_objectives`
--
ALTER TABLE `tbl_objectives`
  ADD PRIMARY KEY (`objectives_id`);

--
-- Indexes for table `tbl_responsibility_matrix`
--
ALTER TABLE `tbl_responsibility_matrix`
  ADD PRIMARY KEY (`r_matrix_id`);

--
-- Indexes for table `tbl_sdp`
--
ALTER TABLE `tbl_sdp`
  ADD PRIMARY KEY (`sdp_id`),
  ADD KEY `objectives_id` (`objectives_id`);

--
-- Indexes for table `tbl_sdp_responsibility`
--
ALTER TABLE `tbl_sdp_responsibility`
  ADD PRIMARY KEY (`s_responsibility_id`),
  ADD KEY `sdp_id` (`sdp_id`),
  ADD KEY `r_matrix_id` (`r_matrix_id`);

--
-- Indexes for table `tbl_target`
--
ALTER TABLE `tbl_target`
  ADD PRIMARY KEY (`target_id`),
  ADD KEY `sdp_id` (`sdp_id`);

--
-- Indexes for table `tbl_user`
--
ALTER TABLE `tbl_user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `r_matrix_id` (`r_matrix_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_accomplishment`
--
ALTER TABLE `tbl_accomplishment`
  MODIFY `accomplishment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_activity_logs`
--
ALTER TABLE `tbl_activity_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_app_setting`
--
ALTER TABLE `tbl_app_setting`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbl_objectives`
--
ALTER TABLE `tbl_objectives`
  MODIFY `objectives_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_responsibility_matrix`
--
ALTER TABLE `tbl_responsibility_matrix`
  MODIFY `r_matrix_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_sdp`
--
ALTER TABLE `tbl_sdp`
  MODIFY `sdp_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_sdp_responsibility`
--
ALTER TABLE `tbl_sdp_responsibility`
  MODIFY `s_responsibility_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_target`
--
ALTER TABLE `tbl_target`
  MODIFY `target_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_user`
--
ALTER TABLE `tbl_user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tbl_accomplishment`
--
ALTER TABLE `tbl_accomplishment`
  ADD CONSTRAINT `tbl_accomplishment_ibfk_1` FOREIGN KEY (`sdp_id`) REFERENCES `tbl_sdp` (`sdp_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tbl_accomplishment_ibfk_2` FOREIGN KEY (`r_matrix_id`) REFERENCES `tbl_responsibility_matrix` (`r_matrix_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `tbl_activity_logs`
--
ALTER TABLE `tbl_activity_logs`
  ADD CONSTRAINT `tbl_activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tbl_user` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `tbl_sdp`
--
ALTER TABLE `tbl_sdp`
  ADD CONSTRAINT `tbl_sdp_ibfk_1` FOREIGN KEY (`objectives_id`) REFERENCES `tbl_objectives` (`objectives_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `tbl_sdp_responsibility`
--
ALTER TABLE `tbl_sdp_responsibility`
  ADD CONSTRAINT `tbl_sdp_responsibility_ibfk_1` FOREIGN KEY (`sdp_id`) REFERENCES `tbl_sdp` (`sdp_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tbl_sdp_responsibility_ibfk_2` FOREIGN KEY (`r_matrix_id`) REFERENCES `tbl_responsibility_matrix` (`r_matrix_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tbl_target`
--
ALTER TABLE `tbl_target`
  ADD CONSTRAINT `tbl_target_ibfk_1` FOREIGN KEY (`sdp_id`) REFERENCES `tbl_sdp` (`sdp_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tbl_user`
--
ALTER TABLE `tbl_user`
  ADD CONSTRAINT `fk_user_rmatrix_1` FOREIGN KEY (`r_matrix_id`) REFERENCES `tbl_responsibility_matrix` (`r_matrix_id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
