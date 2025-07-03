-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 29, 2025 at 03:12 PM
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
-- Database: `guntur_properties`
--

-- --------------------------------------------------------

--
-- Table structure for table `agents`
--

CREATE TABLE `agents` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `position` varchar(100) DEFAULT 'Real Estate Agent',
  `description` text DEFAULT NULL,
  `experience` int(11) DEFAULT 0,
  `specialization` varchar(255) DEFAULT NULL,
  `properties_sold` int(11) DEFAULT 0,
  `rating` decimal(3,2) DEFAULT 0.00,
  `facebook_url` varchar(255) DEFAULT NULL,
  `twitter_url` varchar(255) DEFAULT NULL,
  `instagram_url` varchar(255) DEFAULT NULL,
  `linkedin_url` varchar(255) DEFAULT NULL,
  `youtube_url` varchar(255) DEFAULT NULL,
  `website_url` varchar(255) DEFAULT NULL,
  `office_address` text DEFAULT NULL,
  `office_hours` varchar(255) DEFAULT NULL,
  `featured` tinyint(1) DEFAULT 0,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `agents`
--

INSERT INTO `agents` (`id`, `user_id`, `position`, `description`, `experience`, `specialization`, `properties_sold`, `rating`, `facebook_url`, `twitter_url`, `instagram_url`, `linkedin_url`, `youtube_url`, `website_url`, `office_address`, `office_hours`, `featured`, `display_order`, `created_at`, `updated_at`) VALUES
(1, 15, 'Real Estate Agent', 'Highly Talented', 1, NULL, 0, 5.00, '', '', '', '', '', NULL, '', '', 0, 0, '2025-05-27 12:40:55', '2025-05-27 13:55:42'),
(3, 17, 'Real Estate Agent', '', 1, NULL, 0, 0.00, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, 0, 0, '2025-05-27 13:19:50', '2025-05-27 13:19:50');

-- --------------------------------------------------------

--
-- Table structure for table `agent_awards`
--

CREATE TABLE `agent_awards` (
  `id` int(11) NOT NULL,
  `agent_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `issuing_organization` varchar(255) DEFAULT NULL,
  `year` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `agent_certifications`
--

CREATE TABLE `agent_certifications` (
  `id` int(11) NOT NULL,
  `agent_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `issuing_organization` varchar(255) NOT NULL,
  `issue_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `agent_gallery`
--

CREATE TABLE `agent_gallery` (
  `id` int(11) NOT NULL,
  `agent_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `agent_reviews`
--

CREATE TABLE `agent_reviews` (
  `id` int(11) NOT NULL,
  `agent_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` between 1 and 5),
  `title` varchar(255) DEFAULT NULL,
  `review` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `agent_specializations`
--

CREATE TABLE `agent_specializations` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `agent_specializations`
--

INSERT INTO `agent_specializations` (`id`, `name`, `created_at`) VALUES
(2, 'Commercial', '2025-04-30 04:50:50'),
(4, 'Villa', '2025-04-30 04:50:50'),
(5, 'Apartment', '2025-04-30 04:50:50'),
(6, 'Land', '2025-04-30 04:50:50'),
(7, 'Office Space', '2025-04-30 04:50:50');

-- --------------------------------------------------------

--
-- Table structure for table `agent_specialization_mapping`
--

CREATE TABLE `agent_specialization_mapping` (
  `id` int(11) NOT NULL,
  `agent_id` int(11) NOT NULL,
  `specialization_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `agent_specialization_mapping`
--

INSERT INTO `agent_specialization_mapping` (`id`, `agent_id`, `specialization_id`, `created_at`) VALUES
(59, 3, 5, '2025-05-27 13:19:50'),
(60, 3, 6, '2025-05-27 13:19:50'),
(61, 3, 7, '2025-05-27 13:19:50'),
(67, 1, 5, '2025-05-27 13:48:12'),
(68, 1, 2, '2025-05-27 13:48:12'),
(69, 1, 6, '2025-05-27 13:48:12'),
(70, 1, 7, '2025-05-27 13:48:12'),
(71, 1, 4, '2025-05-27 13:48:12');

-- --------------------------------------------------------

--
-- Table structure for table `enquiries`
--

CREATE TABLE `enquiries` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `property_id` int(11) DEFAULT NULL,
  `agent_id` int(11) DEFAULT NULL,
  `status` enum('new','in_progress','closed') NOT NULL DEFAULT 'new',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enquiries`
--

INSERT INTO `enquiries` (`id`, `name`, `email`, `phone`, `subject`, `message`, `property_id`, `agent_id`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Siva Penmetsa', '21jr1a43c3@gmail.com', '07382927666', 'Agent Contact Form', 'I&#039;m interested in your properties. Please contact me.', NULL, NULL, 'new', '2025-04-30 05:14:19', '2025-04-30 05:14:19'),
(2, 'AS', '21jr1a43c3@gmail.com', '07382927666', 'Agent Contact Form', 'I&#039;m interested in your properties. Please contact me.', NULL, NULL, 'new', '2025-04-30 05:32:47', '2025-04-30 05:32:47');

-- --------------------------------------------------------

--
-- Table structure for table `properties`
--

CREATE TABLE `properties` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `address` varchar(255) NOT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL DEFAULT 'Andhra Pradesh',
  `zip_code` varchar(20) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `instagram_url` varchar(255) DEFAULT NULL,
  `bedrooms` int(11) DEFAULT NULL,
  `bathrooms` int(11) DEFAULT NULL,
  `facing` varchar(100) DEFAULT NULL,
  `area` decimal(10,2) DEFAULT NULL,
  `area_unit` varchar(10) DEFAULT 'sq ft',
  `type_id` int(11) NOT NULL,
  `agent_id` int(11) DEFAULT NULL,
  `featured` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('buy','rent','pending','sold','rented') NOT NULL DEFAULT 'buy',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `properties`
--

INSERT INTO `properties` (`id`, `title`, `description`, `price`, `address`, `city`, `state`, `zip_code`, `phone_number`, `instagram_url`, `bedrooms`, `bathrooms`, `facing`, `area`, `area_unit`, `type_id`, `agent_id`, `featured`, `status`, `created_at`, `updated_at`) VALUES
(13, 'qq', 'qqqqqqqqq', 2200000.00, '123ed', 'guntur', 'Andhra Pradesh', '522007', '7382927666', 'https://www.instagram.com/_.mani.varma._/', 2, 2, 'west', 100.00, 'sq ft', 1, NULL, 1, 'buy', '2025-05-10 04:31:28', '2025-05-22 11:40:46'),
(16, 'assdasd', 'sda22', 2200000.00, '2-101 Ramalayamstreet', 'muramalla', 'Andhra Pradesh', '522001', '+917382927666', 'https://www.instagram.com/p/DIWvSxsBRy9/', 2, 2, 'west', 10000.00, 'sq ft', 2, 3, 1, 'rent', '2025-05-19 13:07:14', '2025-05-27 13:21:48');

-- --------------------------------------------------------

--
-- Table structure for table `property_features`
--

CREATE TABLE `property_features` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `property_features`
--

INSERT INTO `property_features` (`id`, `name`, `icon`, `created_at`) VALUES
(1, 'Parking', 'fas fa-car', '2025-04-22 12:38:18'),
(2, 'Swimming Pool', 'fas fa-swimming-pool', '2025-04-22 12:38:18'),
(3, 'Garden', 'fas fa-leaf', '2025-04-22 12:38:18'),
(4, 'Gym', 'fas fa-dumbbell', '2025-04-22 12:38:18'),
(5, 'Security', 'fas fa-shield-alt', '2025-04-22 12:38:18'),
(6, 'Elevator', 'fas fa-arrow-up', '2025-04-22 12:38:18'),
(7, 'Air Conditioning', 'fas fa-snowflake', '2025-04-22 12:38:18'),
(8, 'Balcony', 'fas fa-border-none', '2025-04-22 12:38:18'),
(9, 'CCTV', 'fas fa-video', '2025-04-22 12:38:18'),
(10, 'Generator Backup', 'fas fa-bolt', '2025-04-22 12:38:18');

-- --------------------------------------------------------

--
-- Table structure for table `property_feature_mapping`
--

CREATE TABLE `property_feature_mapping` (
  `property_id` int(11) NOT NULL,
  `feature_id` int(11) NOT NULL,
  `value` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `property_feature_mapping`
--

INSERT INTO `property_feature_mapping` (`property_id`, `feature_id`, `value`) VALUES
(1, 9, 'yes'),
(16, 7, 'Yes');

-- --------------------------------------------------------

--
-- Table structure for table `property_images`
--

CREATE TABLE `property_images` (
  `id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `property_images`
--

INSERT INTO `property_images` (`id`, `property_id`, `image_path`, `is_primary`, `sort_order`, `created_at`) VALUES
(10, 13, 'assets/images/properties/681ed6a00956c.png', 1, 0, '2025-05-10 04:31:28'),
(11, 13, 'assets/images/properties/681ed6a00a507.jpg', 0, 1, '2025-05-10 04:31:28'),
(12, 13, 'assets/images/properties/681ee2b9c1b37.jpg', 0, 2, '2025-05-10 05:23:05'),
(13, 13, 'assets/images/properties/681ee2b9c237d.webp', 0, 3, '2025-05-10 05:23:05'),
(14, 13, 'assets/images/properties/681ee2c6a6478.jpg', 0, 4, '2025-05-10 05:23:18'),
(15, 13, 'assets/images/properties/681ee2c6a6b3c.jpg', 0, 5, '2025-05-10 05:23:18'),
(16, 13, 'assets/images/properties/681ee2d9e0cb4.png', 0, 6, '2025-05-10 05:23:37'),
(17, 13, 'assets/images/properties/681ee2d9e13f2.png', 0, 7, '2025-05-10 05:23:37'),
(18, 13, 'assets/images/properties/681ee2ece09f1.png', 0, 8, '2025-05-10 05:23:56'),
(19, 13, 'assets/images/properties/681ee2ece1569.png', 0, 9, '2025-05-10 05:23:56'),
(22, 16, 'assets/images/properties/682b2d0241a50.jpg', 1, 0, '2025-05-19 13:07:14');

-- --------------------------------------------------------

--
-- Table structure for table `property_types`
--

CREATE TABLE `property_types` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `property_types`
--

INSERT INTO `property_types` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Apartment', 'Residential unit within a building with multiple similar units', '2025-04-22 12:38:18', '2025-04-22 12:38:18'),
(2, 'House', 'Standalone residential building', '2025-04-22 12:38:18', '2025-04-22 12:38:18'),
(3, 'Villa', 'Luxury standalone house usually with garden or yard', '2025-04-22 12:38:18', '2025-04-22 12:38:18'),
(4, 'Land', 'Vacant land for building or investment', '2025-04-22 12:38:18', '2025-05-07 05:52:44'),
(5, 'Commercial', 'Properties for business purposes', '2025-04-22 12:38:18', '2025-04-22 12:38:18'),
(6, 'Office Space', 'Dedicated space for business operations', '2025-04-22 12:38:18', '2025-04-22 12:38:18');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES
(1, 'site_name', 'Guntur Properties', '2025-04-22 12:38:18'),
(2, 'site_email', 'info@gunturproperties.com', '2025-04-22 12:38:18'),
(3, 'site_phone', '+91 123 456 7890', '2025-04-22 12:38:18'),
(4, 'site_address', '123 Real Estate Avenue, Guntur City, 522002', '2025-04-22 12:38:18'),
(5, 'footer_text', '©2025 Guntur Properties. All Rights Reserved.', '2025-04-22 12:38:18');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `profile_pic` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `role` enum('admin','agent','manager','user') NOT NULL DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `profile_pic`, `created_at`, `updated_at`, `status`, `role`) VALUES
(6, 'varma', 'varma@gmail.com', '$2y$10$0vDMjezyvo5wTh9y/e/gleBLgDFXlPH3jGeBIs7xppg.OcVsTN4kK', '1111111111', 'assets/images/users/681b12a5789f0.jpg', '2025-05-04 16:39:51', '2025-05-27 06:00:18', 1, 'admin'),
(7, 'DInesh', 'kolagani.dinesh@gmail.com', '$2y$10$nT6FUhwSGpwqfyVpM08Fne22/bf6oYD6nL9S71OkvRtrKdYrc2Odq', '85007 21069', 'assets/images/users/68198b825dcfc.png', '2025-05-05 14:00:43', '2025-05-27 06:00:18', 1, 'admin'),
(11, 'Siva Penmetsa', '21jr1a43c3@gmail.com', '$2y$10$ZFZCqH71arVzYwpv/Z2qAOTrUYM4bErUnxZDcgowiTUUYaJtrH7N.', '07382927666', NULL, '2025-05-07 06:04:29', '2025-05-27 08:12:47', 1, 'agent'),
(12, 'murari', 'murari@gmail.com', '$2y$10$Ctr8I5UzqdZyjZsyNH.GROP52iXSe458KSUBe1.37uF7cgifHDare', '07382927666', 'assets/images/users/681afb46cd705.jpg', '2025-05-07 06:18:31', '2025-05-27 06:00:18', 1, 'admin'),
(15, 'Siva', '21j1a43c3@gmail.com', '$2y$10$I8i7Llom0Jr7Hq7XX4lGc.JuS69G/w4mZRJsZlIeb3nKMSgnijK.2', '7382927666', 'assets/images/agents/agent_6835c285bc00d.jpg', '2025-05-27 12:40:55', '2025-05-27 13:47:49', 1, 'agent'),
(17, 'krishna', 'krishna@gmail.com', '$2y$10$snVp6xo7ptL5wrCuVUzzF.zLc4AisLW5H8ILgaOoaXYRTCkAfmtbC', '73892927666', 'assets/images/agents/agent_6835bbf606aca.jpg', '2025-05-27 13:19:50', '2025-05-27 13:19:50', 1, 'agent');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `agents`
--
ALTER TABLE `agents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `agent_awards`
--
ALTER TABLE `agent_awards`
  ADD PRIMARY KEY (`id`),
  ADD KEY `agent_id` (`agent_id`);

--
-- Indexes for table `agent_certifications`
--
ALTER TABLE `agent_certifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `agent_id` (`agent_id`);

--
-- Indexes for table `agent_gallery`
--
ALTER TABLE `agent_gallery`
  ADD PRIMARY KEY (`id`),
  ADD KEY `agent_id` (`agent_id`);

--
-- Indexes for table `agent_reviews`
--
ALTER TABLE `agent_reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `agent_id` (`agent_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `agent_specializations`
--
ALTER TABLE `agent_specializations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `agent_specialization_mapping`
--
ALTER TABLE `agent_specialization_mapping`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `agent_id` (`agent_id`,`specialization_id`),
  ADD KEY `specialization_id` (`specialization_id`);

--
-- Indexes for table `enquiries`
--
ALTER TABLE `enquiries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `property_id` (`property_id`),
  ADD KEY `agent_id` (`agent_id`);

--
-- Indexes for table `properties`
--
ALTER TABLE `properties`
  ADD PRIMARY KEY (`id`),
  ADD KEY `type_id` (`type_id`),
  ADD KEY `agent_id` (`agent_id`),
  ADD KEY `idx_properties_status` (`status`),
  ADD KEY `idx_properties_city` (`city`),
  ADD KEY `idx_properties_price` (`price`),
  ADD KEY `idx_properties_type_id` (`type_id`),
  ADD KEY `idx_properties_featured` (`featured`);
ALTER TABLE `properties` ADD FULLTEXT KEY `title` (`title`,`description`,`city`);

--
-- Indexes for table `property_features`
--
ALTER TABLE `property_features`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `property_feature_mapping`
--
ALTER TABLE `property_feature_mapping`
  ADD PRIMARY KEY (`property_id`,`feature_id`),
  ADD KEY `feature_id` (`feature_id`);

--
-- Indexes for table `property_images`
--
ALTER TABLE `property_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `property_id` (`property_id`);

--
-- Indexes for table `property_types`
--
ALTER TABLE `property_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `agents`
--
ALTER TABLE `agents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `agent_awards`
--
ALTER TABLE `agent_awards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `agent_certifications`
--
ALTER TABLE `agent_certifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `agent_gallery`
--
ALTER TABLE `agent_gallery`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `agent_reviews`
--
ALTER TABLE `agent_reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `agent_specializations`
--
ALTER TABLE `agent_specializations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `agent_specialization_mapping`
--
ALTER TABLE `agent_specialization_mapping`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT for table `enquiries`
--
ALTER TABLE `enquiries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `properties`
--
ALTER TABLE `properties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `property_features`
--
ALTER TABLE `property_features`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `property_images`
--
ALTER TABLE `property_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `property_types`
--
ALTER TABLE `property_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `agents`
--
ALTER TABLE `agents`
  ADD CONSTRAINT `agents_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `agent_awards`
--
ALTER TABLE `agent_awards`
  ADD CONSTRAINT `agent_awards_ibfk_1` FOREIGN KEY (`agent_id`) REFERENCES `agents` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `agent_certifications`
--
ALTER TABLE `agent_certifications`
  ADD CONSTRAINT `agent_certifications_ibfk_1` FOREIGN KEY (`agent_id`) REFERENCES `agents` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `agent_gallery`
--
ALTER TABLE `agent_gallery`
  ADD CONSTRAINT `agent_gallery_ibfk_1` FOREIGN KEY (`agent_id`) REFERENCES `agents` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `agent_reviews`
--
ALTER TABLE `agent_reviews`
  ADD CONSTRAINT `agent_reviews_ibfk_1` FOREIGN KEY (`agent_id`) REFERENCES `agents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `agent_reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `agent_specialization_mapping`
--
ALTER TABLE `agent_specialization_mapping`
  ADD CONSTRAINT `agent_specialization_mapping_ibfk_1` FOREIGN KEY (`agent_id`) REFERENCES `agents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `agent_specialization_mapping_ibfk_2` FOREIGN KEY (`specialization_id`) REFERENCES `agent_specializations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `enquiries`
--
ALTER TABLE `enquiries`
  ADD CONSTRAINT `enquiries_agent_fk` FOREIGN KEY (`agent_id`) REFERENCES `agents` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `enquiries_property_fk` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `properties`
--
ALTER TABLE `properties`
  ADD CONSTRAINT `properties_agent_fk` FOREIGN KEY (`agent_id`) REFERENCES `agents` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `properties_ibfk_1` FOREIGN KEY (`type_id`) REFERENCES `property_types` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `property_feature_mapping`
--
ALTER TABLE `property_feature_mapping`
  ADD CONSTRAINT `property_feature_mapping_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `property_feature_mapping_ibfk_2` FOREIGN KEY (`feature_id`) REFERENCES `property_features` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `property_images`
--
ALTER TABLE `property_images`
  ADD CONSTRAINT `property_images_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
