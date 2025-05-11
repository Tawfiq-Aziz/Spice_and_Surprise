-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 11, 2025 at 04:23 PM
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
-- Database: `spice_and_surprise`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `u_id` int(11) NOT NULL,
  `access_lvl` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`u_id`, `access_lvl`) VALUES
(25, 'full');

-- --------------------------------------------------------

--
-- Table structure for table `bingo_challenge`
--

CREATE TABLE `bingo_challenge` (
  `challenge_id` int(11) NOT NULL,
  `theme` varchar(100) DEFAULT NULL,
  `required_match_wins` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `challenge`
--

CREATE TABLE `challenge` (
  `challenge_id` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `difficulty_level` varchar(50) DEFAULT NULL,
  `time_limit` int(11) DEFAULT NULL,
  `reward_pts` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `challenge`
--

INSERT INTO `challenge` (`challenge_id`, `description`, `type`, `difficulty_level`, `time_limit`, `reward_pts`, `is_active`) VALUES
(1, 'Try 5 spicy dishes', 'Bingo', 'Medium', 30, 50, 1),
(2, 'try fuchka 3 different fuchka under 30mins from different vendors', 'Spin', 'Hard', 30, 50, 1);

-- --------------------------------------------------------

--
-- Table structure for table `completes`
--

CREATE TABLE `completes` (
  `user_id` int(11) NOT NULL,
  `challenge_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contains`
--

CREATE TABLE `contains` (
  `challenge_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dietary_category`
--

CREATE TABLE `dietary_category` (
  `item_id` int(11) NOT NULL,
  `type` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `food_explorer`
--

CREATE TABLE `food_explorer` (
  `user_id` int(11) NOT NULL,
  `spice_tolerance` varchar(50) DEFAULT NULL,
  `fav_food_type` varchar(50) DEFAULT NULL,
  `challengeCompletion_count` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `food_explorer`
--

INSERT INTO `food_explorer` (`user_id`, `spice_tolerance`, `fav_food_type`, `challengeCompletion_count`) VALUES
(26, NULL, NULL, NULL),
(27, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `food_item`
--

CREATE TABLE `food_item` (
  `item_id` int(11) NOT NULL,
  `review_id` int(11) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `spice_level` varchar(50) DEFAULT NULL,
  `hygine_rating` float DEFAULT NULL,
  `price_range` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `food_item`
--

INSERT INTO `food_item` (`item_id`, `review_id`, `name`, `spice_level`, `hygine_rating`, `price_range`, `description`) VALUES
(1, 1, 'Spicy Ramen', 'High', 4.5, '$$', 'Delicious spicy ramen with extra chili.');

-- --------------------------------------------------------

--
-- Table structure for table `food_item_location`
--

CREATE TABLE `food_item_location` (
  `item_id` int(11) NOT NULL,
  `location` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leaderboard`
--

CREATE TABLE `leaderboard` (
  `leaderboard_id` int(11) NOT NULL,
  `challenge_id` int(11) DEFAULT NULL,
  `ranking` text DEFAULT NULL,
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `meal_category`
--

CREATE TABLE `meal_category` (
  `item_id` int(11) NOT NULL,
  `meal_type` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mod_permission`
--

CREATE TABLE `mod_permission` (
  `u_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ranks`
--

CREATE TABLE `ranks` (
  `user_id` int(11) NOT NULL,
  `leaderboard_id` int(11) NOT NULL,
  `score` int(11) DEFAULT NULL,
  `current_rank` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `region_specialty`
--

CREATE TABLE `region_specialty` (
  `item_id` int(11) NOT NULL,
  `origin` varchar(100) DEFAULT NULL,
  `signature_dish` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `review`
--

CREATE TABLE `review` (
  `review_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `vendor_id` int(11) DEFAULT NULL,
  `hygine_rating` float DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `photo_url` varchar(255) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `spice_rating` float DEFAULT NULL,
  `taste_rating` float DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `shop_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `review`
--

INSERT INTO `review` (`review_id`, `user_id`, `vendor_id`, `hygine_rating`, `comments`, `photo_url`, `date`, `spice_rating`, `taste_rating`, `item_id`, `shop_id`) VALUES
(1, 1, 1, 4.5, 'Great food!', NULL, '2024-01-02', 3.5, 5, NULL, NULL),
(2, 2, 3, 5, 'good', NULL, '2025-05-10', 1.5, 5, NULL, NULL),
(3, 2, 3, 5, 'good', NULL, '2025-05-10', 1.5, 5, NULL, NULL),
(4, 2, 3, 3.5, 'not bad', NULL, '2025-05-10', 3.5, 4, NULL, NULL),
(5, 2, NULL, 4, 'testing 1 2 3', NULL, '2025-05-10', 1, 3, NULL, NULL),
(6, 2, NULL, 4, 'testing 1 2 3', NULL, '2025-05-10', 1, 3, NULL, NULL),
(7, 2, 5, 5, 'crispy and spicy', NULL, '2025-05-11', 3.5, 4.5, NULL, NULL),
(8, 2, 5, 5, 'crispy and spicy', NULL, '2025-05-11', 3.5, 4.5, NULL, NULL),
(9, 26, 3, 3.3, 'service is excellent ', NULL, '2025-05-11', 1.6, 4.2, NULL, NULL),
(10, 26, 3, 5, '', NULL, '2025-05-11', 5, 5, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `reward`
--

CREATE TABLE `reward` (
  `reward_id` int(11) NOT NULL,
  `challenge_id` int(11) DEFAULT NULL,
  `item` varchar(100) DEFAULT NULL,
  `unlockable_content` text DEFAULT NULL,
  `points` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sells`
--

CREATE TABLE `sells` (
  `item_id` int(11) NOT NULL,
  `vendor_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shop`
--

CREATE TABLE `shop` (
  `id` int(11) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `shop_name` varchar(100) DEFAULT NULL,
  `license_no` varchar(50) DEFAULT NULL,
  `menu` text DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shop`
--

INSERT INTO `shop` (`id`, `vendor_id`, `shop_name`, `license_no`, `menu`, `location`, `image`) VALUES
(1, 3, 'taste the test', '545047', 'fuchka with extra spice', 'Banasree', 'Screenshot 2025-04-19 235655.png'),
(2, 4, 'test 1', '107662', 'fuchka and chatpati', 'Badda', 'Screenshot 2025-04-27 231614.png'),
(3, 3, 'burger king', '545047', 'Burger', 'Mohakhali', 'Screenshot 2025-05-04 230701.png'),
(4, 5, 'chillox', '098765', 'potato wedges', 'Badda', 'potato-wedges-recipe-500x500.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `spin_challenge`
--

CREATE TABLE `spin_challenge` (
  `challenge_id` int(11) NOT NULL,
  `max_tries` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `spin_challenge`
--

INSERT INTO `spin_challenge` (`challenge_id`, `max_tries`) VALUES
(2, 3);

-- --------------------------------------------------------

--
-- Table structure for table `spin_challenge_option`
--

CREATE TABLE `spin_challenge_option` (
  `sp_id` int(11) NOT NULL,
  `option_text` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `spin_challenge_option`
--

INSERT INTO `spin_challenge_option` (`sp_id`, `option_text`) VALUES
(2, 'try fuchka 3 different fuchka under 30mins from different vendors');

-- --------------------------------------------------------

--
-- Table structure for table `timed_event_challenge`
--

CREATE TABLE `timed_event_challenge` (
  `challenge_id` int(11) NOT NULL,
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `max_participants` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `join_date` date DEFAULT NULL,
  `points_earned` int(11) DEFAULT NULL,
  `achievement_lvl` varchar(50) DEFAULT NULL,
  `user_type` enum('admin','vendor','food_explorer') NOT NULL DEFAULT 'food_explorer'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `name`, `email`, `password`, `join_date`, `points_earned`, `achievement_lvl`, `user_type`) VALUES
(1, 'John Doe', 'john@example.com', 'hashed_password', '2024-01-01', 100, 'Beginner', ''),
(2, 'Tawfiq Aziz Khan Rafi ', 'rafitawfiq6@gmail.com', '$2y$10$7WiUzTqdMcitS7c9Ax0PsuarQWj8qmGzL5J7gvLAVrRdD6/NX2L3W', '2025-05-04', 0, 'Beginner', ''),
(3, 'a', 'ehe@g', '$2y$10$jpsIEblXEftq2K2EPbxBEOL7aDJJlNUzvHWr.i24O59hpzFIH1Q3.', '2025-05-04', 0, 'Beginner', ''),
(5, 'Tawfiq Aziz Khan Rafi ', 'tawfiqaziz.khan.rafi@g.bracu.ac.bd', '$2y$10$9OKYOfFDLJkgcu23J72VROvEtyV5u2cGecS91e91v4jzzxou6TVrK', '2025-05-04', 0, 'Beginner', ''),
(6, 'Admin User', 'admin@example.com', 'hashed_admin_pass', '2024-01-01', 1500, 'Gold', ''),
(7, 'Vendor One', 'vendor1@example.com', 'hashed_vendor_pass', '2024-02-01', 800, 'Silver', ''),
(8, 'Explorer Joe', 'explorer1@example.com', 'hashed_explorer_pass', '2024-03-01', 400, 'Bronze', ''),
(9, 'Alice', 'alice@example.com', 'hashed_password', '2024-08-07', 334, 'Silver', ''),
(10, 'Bob', 'bob@example.com', 'hashed_password', '2023-09-21', 1315, 'Platinum', ''),
(11, 'Charlie', 'charlie@example.com', 'hashed_password', '2024-01-11', 391, 'Bronze', ''),
(12, 'David', 'david@example.com', 'hashed_password', '2024-05-30', 1589, 'Gold', ''),
(13, 'Eva', 'eva@example.com', 'hashed_password', '2023-01-30', 1535, 'Silver', ''),
(14, 'Product', '123@abc', '$2y$10$c5bOnKzdkjjWPZ1MxwyArOhnCMn5I678d2TyXiSkp8I7Dld2DqS9q', '2025-05-07', 0, 'Beginner', ''),
(15, 'ab', 'a@b', '$2y$10$gCl9JvkfPL/WHsUh7KdiRO1l9K6wiIZIMtHrZ71OJQ3qdVLVQXhny', '2025-05-08', 0, 'Beginner', ''),
(16, 'Tawfiw Rafi', '1234@1234', '$2y$10$T7H3o.pARSS1/g1KRdj0Eueac5DmxAx5YGHKU4hoSXuGroPVeBuLy', '2025-05-08', 0, 'Beginner', ''),
(17, 'Tawfiq Aziz Khan Rafi', '1234@abcd', '$2y$10$LWcF4x59CnvJOCUJziFghuf5qeJx61E00iy57HYMfug9JWY7vUbFu', '2025-05-08', 0, 'Beginner', ''),
(18, 'Tawfiq Aziz Khan Rafi', '1@2', '$2y$10$Oy9VbNS7TgFYa96gy5iRLu0OKceQCbiS3uT459UH4NpagJEN7obq6', '2025-05-08', 0, 'Beginner', ''),
(19, 'iuewf', 'wliuf@fouer', '$2y$10$17GAJ14ROJm1STdfMsFblOowKq82FzDKKEDusOVtQuFKiE1NLdW42', '2025-05-08', 0, 'Beginner', ''),
(20, 'oqwyfuh', 'qewfulf@oq', '$2y$10$z66x0wBfe/Kg6q/.SidHwuwm5/g8As6vuYEMSg2POGJvK5SrfS2pK', '2025-05-08', 0, 'Beginner', ''),
(21, 'wfhuwh', 'kjwehgui@iwef', '$2y$10$FM/tGVWWZRzQOTCkWkMbKOCPcCauuma8aJSZihd1awzkg0I08Rn4C', '2025-05-08', 0, 'Beginner', ''),
(22, 'max', 'max1@max', '$2y$10$JuD9iMLUlsRiB3MRKuI8wO0mwkJl9L/uDqAQk9zuX3hKJuX2/i1eG', '2025-05-10', 0, 'Beginner', 'vendor'),
(23, 'uwewu', 'uwewu@uwewu', '$2y$10$ZeLkTJgNbsLpNqpVs0vzqeo94Ut9uwSOsEGyXAe.2AfaBZjIq9VUW', '2025-05-10', 0, 'Beginner', 'vendor'),
(24, 'new vendor', 'new@vendor', '$2y$10$DfxLp7cyBuJ/LDppO.Sx.eoUVaQmA0FUc8VH.0a2syJmydwju9gju', '2025-05-11', 0, 'Beginner', 'vendor'),
(25, 'admin', 'admin@admin', '$2y$10$dZryOOdF1puh/BZmQUWObuPHcUFC5TKa2RV1k5FQgmcI.nbVCwmH6', '2025-05-11', 0, 'Beginner', 'admin'),
(26, 'Tawfiq Aziz', 'incursio069@gmail.com', '$2y$10$5Uk0EDnGHfPmuJKS.js99O3tf5Dge899g6mc0ILv90MJOfbCgllOK', '2025-05-11', 0, 'Beginner', 'food_explorer'),
(27, 'test', 'test@test', '$2y$10$.9NsLx1bYTPMxmTGrIIWVOdmG40IWamcGn3LDE8tVOxQdsuWKgx2G', '2025-05-11', 0, 'Beginner', 'food_explorer');

-- --------------------------------------------------------

--
-- Table structure for table `vendor`
--

CREATE TABLE `vendor` (
  `vendor_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `license_num` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vendor`
--

INSERT INTO `vendor` (`vendor_id`, `user_id`, `license_num`) VALUES
(1, 1, 'VENDOR123'),
(2, 21, '116510'),
(3, 22, '545047'),
(4, 23, '107662'),
(5, 24, '182436');

-- --------------------------------------------------------

--
-- Table structure for table `vendor_menu`
--

CREATE TABLE `vendor_menu` (
  `menu_id` int(11) NOT NULL,
  `u_id` int(11) DEFAULT NULL,
  `vendor_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`u_id`);

--
-- Indexes for table `bingo_challenge`
--
ALTER TABLE `bingo_challenge`
  ADD PRIMARY KEY (`challenge_id`);

--
-- Indexes for table `challenge`
--
ALTER TABLE `challenge`
  ADD PRIMARY KEY (`challenge_id`);

--
-- Indexes for table `completes`
--
ALTER TABLE `completes`
  ADD PRIMARY KEY (`user_id`,`challenge_id`),
  ADD KEY `challenge_id` (`challenge_id`);

--
-- Indexes for table `contains`
--
ALTER TABLE `contains`
  ADD PRIMARY KEY (`challenge_id`,`item_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `dietary_category`
--
ALTER TABLE `dietary_category`
  ADD PRIMARY KEY (`item_id`);

--
-- Indexes for table `food_explorer`
--
ALTER TABLE `food_explorer`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `food_item`
--
ALTER TABLE `food_item`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `review_id` (`review_id`);

--
-- Indexes for table `food_item_location`
--
ALTER TABLE `food_item_location`
  ADD PRIMARY KEY (`item_id`,`location`);

--
-- Indexes for table `leaderboard`
--
ALTER TABLE `leaderboard`
  ADD PRIMARY KEY (`leaderboard_id`),
  ADD KEY `challenge_id` (`challenge_id`);

--
-- Indexes for table `meal_category`
--
ALTER TABLE `meal_category`
  ADD PRIMARY KEY (`item_id`);

--
-- Indexes for table `mod_permission`
--
ALTER TABLE `mod_permission`
  ADD PRIMARY KEY (`u_id`);

--
-- Indexes for table `ranks`
--
ALTER TABLE `ranks`
  ADD PRIMARY KEY (`user_id`,`leaderboard_id`),
  ADD KEY `leaderboard_id` (`leaderboard_id`);

--
-- Indexes for table `region_specialty`
--
ALTER TABLE `region_specialty`
  ADD PRIMARY KEY (`item_id`);

--
-- Indexes for table `review`
--
ALTER TABLE `review`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `vendor_id` (`vendor_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `reward`
--
ALTER TABLE `reward`
  ADD PRIMARY KEY (`reward_id`),
  ADD KEY `challenge_id` (`challenge_id`);

--
-- Indexes for table `sells`
--
ALTER TABLE `sells`
  ADD PRIMARY KEY (`item_id`,`vendor_id`),
  ADD KEY `vendor_id` (`vendor_id`);

--
-- Indexes for table `shop`
--
ALTER TABLE `shop`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vendor_id` (`vendor_id`);

--
-- Indexes for table `spin_challenge`
--
ALTER TABLE `spin_challenge`
  ADD PRIMARY KEY (`challenge_id`);

--
-- Indexes for table `spin_challenge_option`
--
ALTER TABLE `spin_challenge_option`
  ADD PRIMARY KEY (`sp_id`,`option_text`);

--
-- Indexes for table `timed_event_challenge`
--
ALTER TABLE `timed_event_challenge`
  ADD PRIMARY KEY (`challenge_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `vendor`
--
ALTER TABLE `vendor`
  ADD PRIMARY KEY (`vendor_id`),
  ADD KEY `u_id` (`user_id`);

--
-- Indexes for table `vendor_menu`
--
ALTER TABLE `vendor_menu`
  ADD PRIMARY KEY (`menu_id`),
  ADD KEY `u_id` (`u_id`),
  ADD KEY `vendor_id` (`vendor_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `challenge`
--
ALTER TABLE `challenge`
  MODIFY `challenge_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `food_item`
--
ALTER TABLE `food_item`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `leaderboard`
--
ALTER TABLE `leaderboard`
  MODIFY `leaderboard_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `review`
--
ALTER TABLE `review`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `reward`
--
ALTER TABLE `reward`
  MODIFY `reward_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shop`
--
ALTER TABLE `shop`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `vendor`
--
ALTER TABLE `vendor`
  MODIFY `vendor_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `vendor_menu`
--
ALTER TABLE `vendor_menu`
  MODIFY `menu_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin`
--
ALTER TABLE `admin`
  ADD CONSTRAINT `admin_ibfk_1` FOREIGN KEY (`u_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `bingo_challenge`
--
ALTER TABLE `bingo_challenge`
  ADD CONSTRAINT `bingo_challenge_ibfk_1` FOREIGN KEY (`challenge_id`) REFERENCES `challenge` (`challenge_id`);

--
-- Constraints for table `completes`
--
ALTER TABLE `completes`
  ADD CONSTRAINT `completes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`),
  ADD CONSTRAINT `completes_ibfk_2` FOREIGN KEY (`challenge_id`) REFERENCES `challenge` (`challenge_id`);

--
-- Constraints for table `contains`
--
ALTER TABLE `contains`
  ADD CONSTRAINT `contains_ibfk_1` FOREIGN KEY (`challenge_id`) REFERENCES `challenge` (`challenge_id`),
  ADD CONSTRAINT `contains_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `food_item` (`item_id`);

--
-- Constraints for table `dietary_category`
--
ALTER TABLE `dietary_category`
  ADD CONSTRAINT `dietary_category_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `food_item` (`item_id`);

--
-- Constraints for table `food_explorer`
--
ALTER TABLE `food_explorer`
  ADD CONSTRAINT `food_explorer_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `food_item`
--
ALTER TABLE `food_item`
  ADD CONSTRAINT `food_item_ibfk_1` FOREIGN KEY (`review_id`) REFERENCES `review` (`review_id`) ON DELETE SET NULL;

--
-- Constraints for table `food_item_location`
--
ALTER TABLE `food_item_location`
  ADD CONSTRAINT `food_item_location_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `food_item` (`item_id`);

--
-- Constraints for table `leaderboard`
--
ALTER TABLE `leaderboard`
  ADD CONSTRAINT `leaderboard_ibfk_1` FOREIGN KEY (`challenge_id`) REFERENCES `challenge` (`challenge_id`);

--
-- Constraints for table `meal_category`
--
ALTER TABLE `meal_category`
  ADD CONSTRAINT `meal_category_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `food_item` (`item_id`);

--
-- Constraints for table `mod_permission`
--
ALTER TABLE `mod_permission`
  ADD CONSTRAINT `mod_permission_ibfk_1` FOREIGN KEY (`u_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `ranks`
--
ALTER TABLE `ranks`
  ADD CONSTRAINT `ranks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`),
  ADD CONSTRAINT `ranks_ibfk_2` FOREIGN KEY (`leaderboard_id`) REFERENCES `leaderboard` (`leaderboard_id`);

--
-- Constraints for table `region_specialty`
--
ALTER TABLE `region_specialty`
  ADD CONSTRAINT `region_specialty_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `food_item` (`item_id`);

--
-- Constraints for table `review`
--
ALTER TABLE `review`
  ADD CONSTRAINT `review_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`),
  ADD CONSTRAINT `review_ibfk_2` FOREIGN KEY (`vendor_id`) REFERENCES `vendor` (`vendor_id`),
  ADD CONSTRAINT `review_ibfk_3` FOREIGN KEY (`item_id`) REFERENCES `food_item` (`item_id`) ON DELETE SET NULL;

--
-- Constraints for table `reward`
--
ALTER TABLE `reward`
  ADD CONSTRAINT `reward_ibfk_1` FOREIGN KEY (`challenge_id`) REFERENCES `challenge` (`challenge_id`);

--
-- Constraints for table `sells`
--
ALTER TABLE `sells`
  ADD CONSTRAINT `sells_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `food_item` (`item_id`),
  ADD CONSTRAINT `sells_ibfk_2` FOREIGN KEY (`vendor_id`) REFERENCES `vendor` (`vendor_id`);

--
-- Constraints for table `shop`
--
ALTER TABLE `shop`
  ADD CONSTRAINT `shop_ibfk_1` FOREIGN KEY (`vendor_id`) REFERENCES `vendor` (`vendor_id`) ON DELETE CASCADE;

--
-- Constraints for table `spin_challenge`
--
ALTER TABLE `spin_challenge`
  ADD CONSTRAINT `spin_challenge_ibfk_1` FOREIGN KEY (`challenge_id`) REFERENCES `challenge` (`challenge_id`);

--
-- Constraints for table `spin_challenge_option`
--
ALTER TABLE `spin_challenge_option`
  ADD CONSTRAINT `spin_challenge_option_ibfk_1` FOREIGN KEY (`sp_id`) REFERENCES `spin_challenge` (`challenge_id`);

--
-- Constraints for table `timed_event_challenge`
--
ALTER TABLE `timed_event_challenge`
  ADD CONSTRAINT `timed_event_challenge_ibfk_1` FOREIGN KEY (`challenge_id`) REFERENCES `challenge` (`challenge_id`);

--
-- Constraints for table `vendor`
--
ALTER TABLE `vendor`
  ADD CONSTRAINT `vendor_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `vendor_menu`
--
ALTER TABLE `vendor_menu`
  ADD CONSTRAINT `vendor_menu_ibfk_1` FOREIGN KEY (`u_id`) REFERENCES `user` (`user_id`),
  ADD CONSTRAINT `vendor_menu_ibfk_2` FOREIGN KEY (`vendor_id`) REFERENCES `vendor` (`vendor_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
