-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 25, 2025 at 02:53 PM
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
(1, '1', '2', '3', '4', 0.00, '2025-10-21 11:00:42');

-- --------------------------------------------------------

--
-- Table structure for table `glass_powers`
--

CREATE TABLE `glass_powers` (
  `id` int(11) NOT NULL,
  `variety_name` varchar(100) NOT NULL,
  `spherical` decimal(4,2) NOT NULL,
  `cylinder` decimal(4,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `glass_powers`
--

INSERT INTO `glass_powers` (`id`, `variety_name`, `spherical`, `cylinder`) VALUES
(11832, 'v=cr', -8.00, -8.00),
(11833, 'v=cr', -7.75, -7.75),
(11834, 'v=cr', -7.50, -7.50),
(11835, 'v=cr', -7.25, -7.25),
(11836, 'v=cr', -7.00, -7.00),
(11837, 'v=cr', -6.75, -6.75),
(11838, 'v=cr', -6.50, -6.50),
(11839, 'v=cr', -6.25, -6.25),
(11840, 'v=cr', -6.00, -6.00),
(11841, 'v=cr', -5.75, -5.75),
(11842, 'v=cr', -5.50, -5.50),
(11843, 'v=cr', -5.25, -5.25),
(11844, 'v=cr', -5.00, -5.00),
(11845, 'v=cr', -4.75, -4.75),
(11846, 'v=cr', -4.50, -4.50),
(11847, 'v=cr', -4.25, -4.25),
(11848, 'v=cr', -4.00, -4.00),
(11849, 'v=cr', -3.75, -3.75),
(11850, 'v=cr', -3.50, -3.50),
(11851, 'v=cr', -3.25, -3.25),
(11852, 'v=cr', -3.00, -3.00),
(11853, 'v=cr', -2.75, -2.75),
(11854, 'v=cr', -2.50, -2.50),
(11855, 'v=cr', -2.25, -2.25),
(11856, 'v=cr', -2.00, -2.00),
(11857, 'v=cr', -1.75, -1.75),
(11858, 'v=cr', -1.50, -1.50),
(11859, 'v=cr', -1.25, -1.25),
(11860, 'v=cr', -1.00, -1.00),
(11861, 'v=cr', -0.75, -0.75),
(11862, 'v=cr', -0.50, -0.50),
(11863, 'v=cr', -0.25, -0.25),
(11864, 'v=cr', 0.00, 0.00),
(11865, 'v=cr', 0.25, 0.25),
(11866, 'v=cr', 0.50, 0.50),
(11867, 'v=cr', 0.75, 0.75),
(11868, 'v=cr', 1.00, 1.00),
(11869, 'v=cr', 1.25, 1.25),
(11870, 'v=cr', 1.50, 1.50),
(11871, 'v=cr', 1.75, 1.75),
(11872, 'v=cr', 2.00, 2.00),
(11873, 'v=cr', 2.25, 2.25),
(11874, 'v=cr', 2.50, 2.50),
(11875, 'v=cr', 2.75, 2.75),
(11876, 'v=cr', 3.00, 3.00),
(11877, 'v=cr', 3.25, 3.25),
(11878, 'v=cr', 3.50, 3.50),
(11879, 'v=cr', 3.75, 3.75),
(11880, 'v=cr', 4.00, 4.00),
(11881, 'v=cr', 4.25, 4.25),
(11882, 'v=cr', 4.50, 4.50),
(11883, 'v=cr', 4.75, 4.75),
(11884, 'v=cr', 5.00, 5.00),
(11885, 'v=cr', 5.25, 5.25),
(11886, 'v=cr', 5.50, 5.50),
(11887, 'v=cr', 5.75, 5.75),
(11888, 'v=cr', 6.00, 6.00),
(11889, 'v=cr', 6.25, 6.25),
(11890, 'v=cr', 6.50, 6.50),
(11891, 'v=cr', 6.75, 6.75),
(11892, 'v=cr', 7.00, 7.00),
(11893, 'v=cr', 7.25, 7.25),
(11894, 'v=cr', 7.50, 7.50),
(11895, 'v=cr', 7.75, 7.75),
(11896, 'v=cr', 8.00, 8.00);

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
  `party_type` enum('Supplier','Customer') NOT NULL,
  `party_id` int(11) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `mode` enum('Cash','Bank') DEFAULT 'Cash',
  `note` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase`
--

CREATE TABLE `purchase` (
  `id` int(11) NOT NULL,
  `purchase_no` varchar(50) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `item_type` enum('Glass','Other') NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `spherical` decimal(4,2) DEFAULT NULL,
  `cylinder` decimal(4,2) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `rate` decimal(10,2) NOT NULL,
  `total_amount` decimal(12,2) NOT NULL,
  `payment` decimal(12,2) DEFAULT 0.00,
  `paid_amount` decimal(12,2) DEFAULT 0.00,
  `remaining_amount` decimal(12,2) GENERATED ALWAYS AS (`total_amount` - `paid_amount`) STORED,
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase`
--

INSERT INTO `purchase` (`id`, `purchase_no`, `supplier_id`, `item_type`, `item_name`, `spherical`, `cylinder`, `quantity`, `rate`, `total_amount`, `payment`, `paid_amount`, `note`, `created_at`) VALUES
(1, 'PUR2025102411380747', 1, 'Glass', 'bluecut', 0.25, 1.00, 100, 10.00, 1000.00, 500.00, 500.00, '', '2025-10-24 09:38:07');

-- --------------------------------------------------------

--
-- Table structure for table `purchases`
--

CREATE TABLE `purchases` (
  `id` int(11) NOT NULL,
  `purchase_no` varchar(50) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `item_type` enum('Glass','Other') NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `spherical` decimal(4,2) DEFAULT NULL,
  `cylinder` decimal(4,2) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `rate` decimal(10,2) NOT NULL,
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

INSERT INTO `purchases` (`id`, `purchase_no`, `supplier_id`, `item_type`, `item_name`, `spherical`, `cylinder`, `quantity`, `rate`, `total_amount`, `payment`, `paid_amount`, `note`, `created_at`) VALUES
(30, 'PUR2025102508373887', 2, 'Glass', 'v=cr', -7.75, -5.00, 100, 100.00, 10000.00, 5000.00, 5000.00, '', '2025-10-25 06:37:38');

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `sale_no` varchar(50) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `item_type` enum('Glass','Other') NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `spherical` decimal(4,2) DEFAULT NULL,
  `cylinder` decimal(4,2) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `rate` decimal(10,2) NOT NULL,
  `total_amount` decimal(12,2) NOT NULL,
  `payment` decimal(12,2) DEFAULT 0.00,
  `paid_amount` decimal(12,2) DEFAULT 0.00,
  `remaining_amount` decimal(12,2) GENERATED ALWAYS AS (`total_amount` - `paid_amount`) STORED,
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(1, '1', 'sdasd', '1232432', 'asd', -100.00, '2025-10-21 10:48:39'),
(2, '5', 'asd', '123', 'as', 0.00, '2025-10-21 10:59:04');

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

--
-- Dumping data for table `supplier_ledger`
--

INSERT INTO `supplier_ledger` (`id`, `supplier_id`, `type`, `amount`, `note`, `created_at`) VALUES
(1, 1, 'Payment', 100.00, 'Manual Payment', '2025-10-21 10:49:15'),
(2, 1, 'Payment', 200.00, 'Manual Payment', '2025-10-21 10:49:43'),
(3, 1, 'Payment', -300.00, 'Manual Payment', '2025-10-21 10:49:48'),
(4, 1, 'Payment', 100.00, 'Manual Payment', '2025-10-21 10:54:40');

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
-- Indexes for table `purchase`
--
ALTER TABLE `purchase`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `purchase_no` (`purchase_no`);

--
-- Indexes for table `purchases`
--
ALTER TABLE `purchases`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `purchase_no` (`purchase_no`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sale_no` (`sale_no`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `glass_powers`
--
ALTER TABLE `glass_powers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11897;

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
-- AUTO_INCREMENT for table `purchase`
--
ALTER TABLE `purchase`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `purchases`
--
ALTER TABLE `purchases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock`
--
ALTER TABLE `stock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `supplier_ledger`
--
ALTER TABLE `supplier_ledger`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
