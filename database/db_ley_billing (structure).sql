-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Feb 11, 2026 at 04:08 PM
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
-- Database: `db_ley_billing`
--

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
(1, 'Ley Billing and Collection', 'Municipality of Toboso', '1', 'tobosoley@gmail.com', 'Ley Toboso', '2025-12-07 10:03:21', 'default.png');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_banks`
--

CREATE TABLE `tbl_banks` (
  `id` int(11) NOT NULL,
  `bank_name` varchar(255) NOT NULL,
  `branch` varchar(255) DEFAULT NULL,
  `bank_code` varchar(50) DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_charge_invoices`
--

CREATE TABLE `tbl_charge_invoices` (
  `id` int(11) NOT NULL,
  `invoice_no` varchar(50) NOT NULL,
  `invoice_date` date NOT NULL,
  `customer_id` int(11) NOT NULL,
  `address` text DEFAULT NULL,
  `total_amount` decimal(15,2) DEFAULT 0.00,
  `payment_status` enum('Unpaid','Partially Paid','Paid') DEFAULT 'Unpaid',
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `withholding_tax_5` decimal(15,2) DEFAULT 0.00 COMMENT 'Government withholding tax 5%',
  `withholding_tax_1` decimal(15,2) DEFAULT 0.00 COMMENT 'Government withholding tax 1%',
  `net_amount` decimal(15,2) DEFAULT 0.00 COMMENT 'Amount after withholding tax deductions',
  `is_government` tinyint(1) DEFAULT 0 COMMENT 'Flag for government transactions'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_customers`
--

CREATE TABLE `tbl_customers` (
  `id` int(11) NOT NULL,
  `customer_type` enum('Private','Business','Government') NOT NULL DEFAULT 'Private',
  `name` varchar(255) NOT NULL,
  `business_name` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `contact_number` varchar(50) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_deliveries`
--

CREATE TABLE `tbl_deliveries` (
  `id` int(11) NOT NULL,
  `delivery_no` varchar(50) NOT NULL,
  `delivery_date` date NOT NULL,
  `customer_id` int(11) NOT NULL,
  `delivery_type` enum('DR V','DR Government') DEFAULT 'DR V' COMMENT 'Delivery receipt category',
  `address` text DEFAULT NULL,
  `total_amount` decimal(15,2) DEFAULT 0.00,
  `payment_status` enum('Unpaid','Partially Paid','Paid') DEFAULT 'Unpaid',
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `checker` varchar(255) DEFAULT NULL,
  `driver` varchar(255) DEFAULT NULL,
  `plate_number` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_invoice_deliveries`
--

CREATE TABLE `tbl_invoice_deliveries` (
  `id` int(11) NOT NULL,
  `charge_invoice_id` int(11) NOT NULL,
  `delivery_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_official_receipts`
--

CREATE TABLE `tbl_official_receipts` (
  `id` int(11) NOT NULL,
  `or_no` varchar(50) NOT NULL,
  `or_date` date NOT NULL,
  `customer_id` int(11) NOT NULL,
  `amount_received` decimal(15,2) NOT NULL DEFAULT 0.00,
  `payment_type` enum('Cash','Check','Bank Transfer','GCash') DEFAULT 'Cash',
  `bank_id` int(11) DEFAULT NULL,
  `cheque_no` varchar(100) DEFAULT NULL,
  `cheque_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `cheque_name` varchar(255) DEFAULT NULL COMMENT 'Check name for government transactions',
  `check_amount` decimal(15,2) DEFAULT NULL COMMENT 'Check amount verification',
  `payment_details` text DEFAULT NULL COMMENT 'Additional payment details'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_or_delivery`
--

CREATE TABLE `tbl_or_delivery` (
  `id` int(11) NOT NULL,
  `or_id` int(11) NOT NULL,
  `delivery_id` int(11) NOT NULL,
  `applied_amount` decimal(15,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_or_invoice`
--

CREATE TABLE `tbl_or_invoice` (
  `id` int(11) NOT NULL,
  `or_id` int(11) NOT NULL,
  `charge_invoice_id` int(11) NOT NULL,
  `applied_amount` decimal(15,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_payment_history`
--

CREATE TABLE `tbl_payment_history` (
  `id` int(11) NOT NULL,
  `or_id` int(11) NOT NULL,
  `charge_invoice_id` int(11) NOT NULL,
  `amount_received` decimal(15,2) NOT NULL,
  `net_value` decimal(15,2) DEFAULT 0.00,
  `percent_one` decimal(15,2) DEFAULT 0.00,
  `percent_five` decimal(15,2) DEFAULT 0.00,
  `status` enum('Active','Cancelled') DEFAULT 'Active',
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `is_government_payment` tinyint(1) DEFAULT 0 COMMENT 'Flag for government payment processing',
  `payment_reference` varchar(100) DEFAULT NULL COMMENT 'Reference number for tracking'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_users`
--

CREATE TABLE `tbl_users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('Admin','Encoder','Viewer','Faculty','Staff','Dean','Director') DEFAULT 'Encoder',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_users`
--

INSERT INTO `tbl_users` (`id`, `first_name`, `middle_name`, `last_name`, `position`, `username`, `password_hash`, `role`, `created_at`) VALUES
(1, 'Admin', '', 'System', 'Administrator', 'super', '$2y$10$VvC9sWd7qymmX5gPrU1NBu4AY/kcsmFmUd3fxEGwlbvVWGJxW.pzq', 'Admin', '2025-12-07 17:44:26'),
(3, 'encoder', '', '1', 'encoder', 'encoder', '$2y$10$gIsqnNOG6ykQWX3xGERfiewchH/mzTPI/2qV55qYP/He5MN6x8iNm', 'Encoder', '2025-12-07 20:34:24');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user_logs`
--

CREATE TABLE `tbl_user_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `login_time` datetime DEFAULT NULL,
  `logout_time` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

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
-- Indexes for table `tbl_banks`
--
ALTER TABLE `tbl_banks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_charge_invoices`
--
ALTER TABLE `tbl_charge_invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_no` (`invoice_no`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `tbl_charge_invoices_ibfk_2` (`created_by`),
  ADD KEY `idx_charge_invoices_is_government` (`is_government`);

--
-- Indexes for table `tbl_customers`
--
ALTER TABLE `tbl_customers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_deliveries`
--
ALTER TABLE `tbl_deliveries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `delivery_no` (`delivery_no`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `tbl_invoice_deliveries`
--
ALTER TABLE `tbl_invoice_deliveries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `charge_invoice_id` (`charge_invoice_id`),
  ADD KEY `delivery_id` (`delivery_id`);

--
-- Indexes for table `tbl_official_receipts`
--
ALTER TABLE `tbl_official_receipts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `or_no` (`or_no`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `bank_id` (`bank_id`),
  ADD KEY `tbl_official_receipts_ibfk_3` (`created_by`);

--
-- Indexes for table `tbl_or_delivery`
--
ALTER TABLE `tbl_or_delivery`
  ADD PRIMARY KEY (`id`),
  ADD KEY `or_id` (`or_id`),
  ADD KEY `delivery_id` (`delivery_id`);

--
-- Indexes for table `tbl_or_invoice`
--
ALTER TABLE `tbl_or_invoice`
  ADD PRIMARY KEY (`id`),
  ADD KEY `or_id` (`or_id`),
  ADD KEY `charge_invoice_id` (`charge_invoice_id`);

--
-- Indexes for table `tbl_payment_history`
--
ALTER TABLE `tbl_payment_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `or_id` (`or_id`),
  ADD KEY `charge_invoice_id` (`charge_invoice_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_payment_history_is_government` (`is_government_payment`);

--
-- Indexes for table `tbl_users`
--
ALTER TABLE `tbl_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `tbl_user_logs`
--
ALTER TABLE `tbl_user_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

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
-- AUTO_INCREMENT for table `tbl_banks`
--
ALTER TABLE `tbl_banks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_charge_invoices`
--
ALTER TABLE `tbl_charge_invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_customers`
--
ALTER TABLE `tbl_customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_deliveries`
--
ALTER TABLE `tbl_deliveries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_invoice_deliveries`
--
ALTER TABLE `tbl_invoice_deliveries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_official_receipts`
--
ALTER TABLE `tbl_official_receipts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_or_delivery`
--
ALTER TABLE `tbl_or_delivery`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_or_invoice`
--
ALTER TABLE `tbl_or_invoice`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_payment_history`
--
ALTER TABLE `tbl_payment_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_users`
--
ALTER TABLE `tbl_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tbl_user_logs`
--
ALTER TABLE `tbl_user_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tbl_activity_logs`
--
ALTER TABLE `tbl_activity_logs`
  ADD CONSTRAINT `tbl_activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tbl_or_delivery`
--
ALTER TABLE `tbl_or_delivery`
  ADD CONSTRAINT `tbl_or_delivery_ibfk_1` FOREIGN KEY (`or_id`) REFERENCES `tbl_official_receipts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tbl_or_delivery_ibfk_2` FOREIGN KEY (`delivery_id`) REFERENCES `tbl_deliveries` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
