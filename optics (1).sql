-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 31, 2025 at 02:37 PM
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
-- Database: `optics`
--

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `balance` decimal(12,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `code`, `name`, `phone`, `address`, `balance`, `created_at`) VALUES
(4, '1001', 'Hamid', '033585648646', 'djshfsjdhfds', 0.00, '2025-10-28 12:08:41');

-- --------------------------------------------------------

--
-- Table structure for table `glass_powers`
--

CREATE TABLE `glass_powers` (
  `id` int(11) NOT NULL,
  `variety_name` varchar(100) NOT NULL,
  `spherical` decimal(4,2) NOT NULL,
  `cylinder` decimal(4,2) NOT NULL,
  `addition` decimal(4,2) DEFAULT NULL,
  `axis` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `glass_powers`
--

INSERT INTO `glass_powers` (`id`, `variety_name`, `spherical`, `cylinder`, `addition`, `axis`) VALUES
(27269, 'cr', -8.00, -8.00, -8.00, 105),
(27270, 'cr', -7.75, -7.75, -7.75, 85),
(27271, 'cr', -7.50, -7.50, -7.50, 65),
(27272, 'cr', -7.25, -7.25, -7.25, 165),
(27273, 'cr', -7.00, -7.00, -7.00, 95),
(27274, 'cr', -6.75, -6.75, -6.75, 70),
(27275, 'cr', -6.50, -6.50, -6.50, 95),
(27276, 'cr', -6.25, -6.25, -6.25, 35),
(27277, 'cr', -6.00, -6.00, -6.00, 120),
(27278, 'cr', -5.75, -5.75, -5.75, 180),
(27279, 'cr', -5.50, -5.50, -5.50, 40),
(27280, 'cr', -5.25, -5.25, -5.25, 95),
(27281, 'cr', -5.00, -5.00, -5.00, 180),
(27282, 'cr', -4.75, -4.75, -4.75, 5),
(27283, 'cr', -4.50, -4.50, -4.50, 135),
(27284, 'cr', -4.25, -4.25, -4.25, 0),
(27285, 'cr', -4.00, -4.00, -4.00, 70),
(27286, 'cr', -3.75, -3.75, -3.75, 55),
(27287, 'cr', -3.50, -3.50, -3.50, 130),
(27288, 'cr', -3.25, -3.25, -3.25, 95),
(27289, 'cr', -3.00, -3.00, -3.00, 150),
(27290, 'cr', -2.75, -2.75, -2.75, 110),
(27291, 'cr', -2.50, -2.50, -2.50, 180),
(27292, 'cr', -2.25, -2.25, -2.25, 130),
(27293, 'cr', -2.00, -2.00, -2.00, 180),
(27294, 'cr', -1.75, -1.75, -1.75, 25),
(27295, 'cr', -1.50, -1.50, -1.50, 30),
(27296, 'cr', -1.25, -1.25, -1.25, 30),
(27297, 'cr', -1.00, -1.00, -1.00, 165),
(27298, 'cr', -0.75, -0.75, -0.75, 45),
(27299, 'cr', -0.50, -0.50, -0.50, 140),
(27300, 'cr', -0.25, -0.25, -0.25, 70),
(27301, 'cr', 0.00, 0.00, 0.00, 165),
(27302, 'cr', 0.25, 0.25, 0.25, 145),
(27303, 'cr', 0.50, 0.50, 0.50, 160),
(27304, 'cr', 0.75, 0.75, 0.75, 150),
(27305, 'cr', 1.00, 1.00, 1.00, 100),
(27306, 'cr', 1.25, 1.25, 1.25, 10),
(27307, 'cr', 1.50, 1.50, 1.50, 120),
(27308, 'cr', 1.75, 1.75, 1.75, 60),
(27309, 'cr', 2.00, 2.00, 2.00, 0),
(27310, 'cr', 2.25, 2.25, 2.25, 35),
(27311, 'cr', 2.50, 2.50, 2.50, 35),
(27312, 'cr', 2.75, 2.75, 2.75, 170),
(27313, 'cr', 3.00, 3.00, 3.00, 175),
(27314, 'cr', 3.25, 3.25, 3.25, 55),
(27315, 'cr', 3.50, 3.50, 3.50, 55),
(27316, 'cr', 3.75, 3.75, 3.75, 50),
(27317, 'cr', 4.00, 4.00, 4.00, 165),
(27318, 'cr', 4.25, 4.25, 4.25, 65),
(27319, 'cr', 4.50, 4.50, 4.50, 155),
(27320, 'cr', 4.75, 4.75, 4.75, 145),
(27321, 'cr', 5.00, 5.00, 5.00, 120),
(27322, 'cr', 5.25, 5.25, 5.25, 55),
(27323, 'cr', 5.50, 5.50, 5.50, 0),
(27324, 'cr', 5.75, 5.75, 5.75, 70),
(27325, 'cr', 6.00, 6.00, 6.00, 75),
(27326, 'cr', 6.25, 6.25, 6.25, 150),
(27327, 'cr', 6.50, 6.50, 6.50, 140),
(27328, 'cr', 6.75, 6.75, 6.75, 160),
(27329, 'cr', 7.00, 7.00, 7.00, 180),
(27330, 'cr', 7.25, 7.25, 7.25, 115),
(27331, 'cr', 7.50, 7.50, 7.50, 60),
(27332, 'cr', 7.75, 7.75, 7.75, 175),
(27333, 'cr', 8.00, 8.00, 8.00, 115);

-- --------------------------------------------------------

--
-- Table structure for table `glass_varieties`
--

CREATE TABLE `glass_varieties` (
  `id` int(11) NOT NULL,
  `variety_name` varchar(100) NOT NULL,
  `plus_spherical` text DEFAULT NULL,
  `minus_spherical` text DEFAULT NULL,
  `plus_cylinder` text DEFAULT NULL,
  `minus_cylinder` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `category` enum('Glass','Lens','Other') DEFAULT 'Other',
  `brand` varchar(100) DEFAULT NULL,
  `cost_price` decimal(10,2) DEFAULT 0.00,
  `sale_price` decimal(10,2) DEFAULT 0.00,
  `stock` int(11) DEFAULT 0,
  `variety` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`id`, `code`, `item_name`, `category`, `brand`, `cost_price`, `sale_price`, `stock`, `variety`, `description`, `created_at`) VALUES
(3, 'GLS-001', 'Glass', 'Glass', NULL, 0.00, 0.00, 0, NULL, NULL, '2025-10-21 10:22:24'),
(4, 'LNS-001', 'Lens', 'Lens', NULL, 0.00, 0.00, 0, NULL, NULL, '2025-10-21 10:22:24');

-- --------------------------------------------------------

--
-- Table structure for table `item_variants`
--

CREATE TABLE `item_variants` (
  `id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `type` enum('Spherical','Cylindrical') NOT NULL,
  `sign` enum('+','-') NOT NULL,
  `value` decimal(4,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ledger`
--

CREATE TABLE `ledger` (
  `id` int(11) NOT NULL,
  `ref_type` enum('Purchase','Sale','Payment','Receipt') NOT NULL,
  `ref_id` int(11) DEFAULT NULL,
  `party_type` enum('Supplier','Customer') NOT NULL,
  `party_id` int(11) NOT NULL,
  `debit` decimal(12,2) DEFAULT 0.00,
  `credit` decimal(12,2) DEFAULT 0.00,
  `balance_after` decimal(12,2) DEFAULT 0.00,
  `remarks` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `person_id` int(11) NOT NULL,
  `type` enum('customer','supplier') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchases`
--

CREATE TABLE `purchases` (
  `id` int(11) NOT NULL,
  `purchase_no` varchar(50) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `item_type` enum('Glass','Other') NOT NULL,
  `total_amount` decimal(12,2) NOT NULL,
  `payment` decimal(12,2) DEFAULT 0.00,
  `paid_amount` decimal(12,2) DEFAULT 0.00,
  `remaining_amount` decimal(12,2) GENERATED ALWAYS AS (`total_amount` - `paid_amount`) STORED,
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `purchases`
--

INSERT INTO `purchases` (`id`, `purchase_no`, `supplier_id`, `item_type`, `total_amount`, `payment`, `paid_amount`, `note`, `created_at`) VALUES
(44, 'PUR2025103111522486', 3, 'Glass', 10000.00, 0.00, 5000.00, '', '2025-10-31 10:52:24'),
(45, 'PUR2025103113043540', 3, 'Glass', 404800.00, 0.00, 200000.00, '', '2025-10-31 12:04:35');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_items`
--

CREATE TABLE `purchase_items` (
  `id` int(11) NOT NULL,
  `purchase_id` int(11) DEFAULT NULL,
  `item_type` varchar(50) DEFAULT NULL,
  `item_name` varchar(100) DEFAULT NULL,
  `spherical` decimal(5,2) DEFAULT NULL,
  `cylinder` decimal(5,2) DEFAULT NULL,
  `addition` decimal(5,2) DEFAULT NULL,
  `axis` int(11) DEFAULT NULL,
  `quantity` decimal(10,2) DEFAULT NULL,
  `rate` decimal(10,2) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `purchase_items`
--

INSERT INTO `purchase_items` (`id`, `purchase_id`, `item_type`, `item_name`, `spherical`, `cylinder`, `addition`, `axis`, `quantity`, `rate`, `amount`) VALUES
(41, 44, 'Glass', 'cr', -8.00, -7.75, -8.00, 105, 100.00, 100.00, 10000.00),
(42, 45, 'Glass', 'cr', -8.00, 0.00, -8.00, 0, 100.00, 150.00, 15000.00),
(43, 45, 'Glass', 'cr', -7.75, -7.75, 0.00, 0, 1000.00, 150.00, 150000.00),
(44, 45, 'Glass', 'cr', -7.50, -7.50, -4.50, 85, 150.00, 180.00, 27000.00),
(45, 45, 'Glass', 'cr', -4.00, -3.50, 0.00, 0, 950.00, 180.00, 171000.00),
(46, 45, 'Glass', 'cr', -1.75, -3.75, -3.50, 130, 190.00, 220.00, 41800.00);

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `sale_no` varchar(50) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `item_type` enum('Glass','Other') NOT NULL,
  `total_amount` decimal(12,2) NOT NULL,
  `payment` decimal(12,2) DEFAULT 0.00,
  `paid_amount` decimal(12,2) DEFAULT 0.00,
  `remaining_amount` decimal(12,2) GENERATED ALWAYS AS (`total_amount` - `paid_amount`) STORED,
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `sale_no`, `customer_id`, `item_type`, `total_amount`, `payment`, `paid_amount`, `note`, `created_at`) VALUES
(14, 'SAL2025103010021577', 4, 'Glass', 22500.00, 0.00, 22500.00, '', '2025-10-30 09:02:15'),
(17, 'SAL2025103014542896', 4, 'Glass', 22000.00, 0.00, 22000.00, '', '2025-10-30 13:54:28'),
(18, 'SAL2025103015425559', 4, 'Glass', 10000.00, 0.00, 10000.00, '', '2025-10-30 14:42:55'),
(19, 'SAL2025103015440564', 4, 'Glass', 25000.00, 0.00, 18500.00, '', '2025-10-30 14:44:05'),
(20, 'SAL2025103015461771', 4, 'Glass', 10000.00, 0.00, 1000.00, '', '2025-10-30 14:46:17');

-- --------------------------------------------------------

--
-- Table structure for table `sale_items`
--

CREATE TABLE `sale_items` (
  `id` int(11) NOT NULL,
  `sale_id` int(11) NOT NULL,
  `item_type` varchar(20) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `spherical` decimal(5,2) DEFAULT NULL,
  `cylinder` decimal(5,2) DEFAULT NULL,
  `addition` decimal(5,2) DEFAULT NULL,
  `axis` int(11) DEFAULT NULL,
  `quantity` decimal(10,2) NOT NULL DEFAULT 1.00,
  `rate` decimal(10,2) NOT NULL DEFAULT 0.00,
  `amount` decimal(12,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sale_items`
--

INSERT INTO `sale_items` (`id`, `sale_id`, `item_type`, `item_name`, `spherical`, `cylinder`, `addition`, `axis`, `quantity`, `rate`, `amount`) VALUES
(9, 14, 'Glass', '', 0.50, 0.50, NULL, NULL, 50.00, 150.00, 7500.00),
(10, 14, 'Glass', '', 0.25, 0.25, NULL, NULL, 50.00, 300.00, 15000.00),
(21, 17, 'Glass', 'CRMLKT', -7.75, -0.50, NULL, NULL, 100.00, 100.00, 10000.00),
(22, 17, 'Glass', 'CRMLKT', -7.25, -4.25, NULL, NULL, 120.00, 100.00, 12000.00),
(24, 18, 'Glass', 'CRMLKT', -8.00, -5.00, NULL, NULL, 100.00, 100.00, 10000.00),
(27, 19, 'Glass', 'CRMLKT', -7.75, -4.25, NULL, NULL, 100.00, 100.00, 10000.00),
(28, 19, 'Glass', 'CRMLKT', -5.25, -5.50, NULL, NULL, 150.00, 100.00, 15000.00),
(30, 20, 'Glass', 'CRMLKT', -8.00, -3.25, -3.75, 115, 100.00, 100.00, 10000.00);

-- --------------------------------------------------------

--
-- Table structure for table `stock`
--

CREATE TABLE `stock` (
  `id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `balance` decimal(12,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`id`, `code`, `name`, `phone`, `address`, `balance`, `created_at`) VALUES
(3, '1001', 'Farhan', '01145415', 'gfhfdh', 0.00, '2025-10-28 12:08:08');

-- --------------------------------------------------------

--
-- Table structure for table `supplier_ledger`
--

CREATE TABLE `supplier_ledger` (
  `id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `type` enum('Purchase','Payment') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `supplier_rates`
--

CREATE TABLE `supplier_rates` (
  `id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `variety_name` varchar(100) NOT NULL,
  `spherical` decimal(5,2) NOT NULL,
  `cylinder` decimal(5,2) NOT NULL,
  `rate` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `supplier_rates`
--

INSERT INTO `supplier_rates` (`id`, `supplier_id`, `variety_name`, `spherical`, `cylinder`, `rate`, `created_at`) VALUES
(1, 3, 'blue', -8.00, -8.00, 100.00, '2025-10-30 09:42:58');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','staff') DEFAULT 'admin',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `created_at`) VALUES
(1, 'admin', 'admin123', 'admin', '2025-10-21 10:03:26');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `glass_powers`
--
ALTER TABLE `glass_powers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `glass_varieties`
--
ALTER TABLE `glass_varieties`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `item_variants`
--
ALTER TABLE `item_variants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `item_id` (`item_id`,`type`,`sign`,`value`);

--
-- Indexes for table `ledger`
--
ALTER TABLE `ledger`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `purchases`
--
ALTER TABLE `purchases`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `purchase_no` (`purchase_no`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `purchase_items`
--
ALTER TABLE `purchase_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sale_no` (`sale_no`);

--
-- Indexes for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sale_id_idx` (`sale_id`);

--
-- Indexes for table `stock`
--
ALTER TABLE `stock`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `item_id` (`item_id`,`variant_id`),
  ADD KEY `variant_id` (`variant_id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `supplier_ledger`
--
ALTER TABLE `supplier_ledger`
  ADD PRIMARY KEY (`id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `supplier_rates`
--
ALTER TABLE `supplier_rates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_rate` (`supplier_id`,`variety_name`,`spherical`,`cylinder`);

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
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `glass_powers`
--
ALTER TABLE `glass_powers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27334;

--
-- AUTO_INCREMENT for table `glass_varieties`
--
ALTER TABLE `glass_varieties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `item_variants`
--
ALTER TABLE `item_variants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ledger`
--
ALTER TABLE `ledger`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchases`
--
ALTER TABLE `purchases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `purchase_items`
--
ALTER TABLE `purchase_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `sale_items`
--
ALTER TABLE `sale_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `stock`
--
ALTER TABLE `stock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `supplier_ledger`
--
ALTER TABLE `supplier_ledger`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `supplier_rates`
--
ALTER TABLE `supplier_rates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `item_variants`
--
ALTER TABLE `item_variants`
  ADD CONSTRAINT `item_variants_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `purchases`
--
ALTER TABLE `purchases`
  ADD CONSTRAINT `purchases_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD CONSTRAINT `fk_sale_items_sale` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stock`
--
ALTER TABLE `stock`
  ADD CONSTRAINT `stock_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_ibfk_2` FOREIGN KEY (`variant_id`) REFERENCES `item_variants` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `supplier_ledger`
--
ALTER TABLE `supplier_ledger`
  ADD CONSTRAINT `supplier_ledger_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
