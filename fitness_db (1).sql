-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 24, 2026 at 02:34 PM
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
-- Database: `fitness_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `daily_logs`
--

CREATE TABLE `daily_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `date` date DEFAULT curdate(),
  `calories_in` int(11) DEFAULT 0,
  `calories_out` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `diet_logs`
--

CREATE TABLE `diet_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `food_name` varchar(100) DEFAULT NULL,
  `calories` int(11) DEFAULT NULL,
  `protein` float DEFAULT NULL,
  `carbs` float DEFAULT NULL,
  `entry_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `diet_logs`
--

INSERT INTO `diet_logs` (`id`, `user_id`, `food_name`, `calories`, `protein`, `carbs`, `entry_date`) VALUES
(1, 1, 'Almonds', 290, 10.5, 11, '2026-03-24'),
(2, 1, 'Rice', 130, 2.7, 28, '2026-03-24'),
(3, 1, 'Chicken Breast', 825, 155, 0, '2026-03-24'),
(4, 7, 'Almonds', 290, 10.5, 11, '2026-03-24'),
(5, 7, 'Chicken Breast', 495, 93, 0, '2026-03-24'),
(6, 7, 'Oats', 389, 16.9, 66, '2026-03-24'),
(7, 7, 'Paneer', 159, 10.8, 0.7, '2026-03-24');

-- --------------------------------------------------------

--
-- Table structure for table `food_data`
--

CREATE TABLE `food_data` (
  `id` int(11) NOT NULL,
  `item_name` varchar(50) DEFAULT NULL,
  `cal_per_100g` int(11) DEFAULT NULL,
  `pro_per_100g` float DEFAULT NULL,
  `fat_per_100g` float DEFAULT NULL,
  `carb_per_100g` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `food_data`
--

INSERT INTO `food_data` (`id`, `item_name`, `cal_per_100g`, `pro_per_100g`, `fat_per_100g`, `carb_per_100g`) VALUES
(1, 'Chicken Breast', 165, 31, 3.6, 0),
(2, 'Rice', 130, 2.7, 0.3, 28),
(3, 'Egg', 155, 13, 11, 1.1),
(4, 'Oats', 389, 16.9, 6.9, 66),
(5, 'Chapati', 264, 8, 3, 50),
(6, 'Broccoli', 34, 2.8, 0.4, 7),
(7, 'Spinach (Palak)', 23, 2.9, 0.4, 3.6),
(8, 'Paneer', 265, 18, 20, 1.2),
(9, 'Dal (Cooked)', 116, 9, 0.4, 20),
(10, 'Soya Chunks', 345, 52, 0.5, 33),
(11, 'Greek Yogurt', 59, 10, 0.4, 3.6),
(12, 'Peanut Butter', 588, 25, 50, 20),
(13, 'Almonds', 579, 21, 49, 22),
(14, 'Tuna Fish', 132, 28, 1.3, 0),
(15, 'Salmon', 208, 20, 13, 0),
(16, 'Chickpeas (Chana)', 164, 8.9, 2.6, 27),
(17, 'Whey Protein', 390, 80, 3, 5),
(18, 'Beef (Lean)', 250, 26, 15, 0),
(19, 'Mutton', 294, 25, 21, 0),
(20, 'Tofu', 76, 8, 4.8, 1.9),
(21, 'Sweet Potato', 86, 1.6, 0.1, 20),
(22, 'Quinoa', 120, 4.4, 1.9, 21),
(23, 'Banana', 89, 1.1, 0.3, 23),
(24, 'Cottage Cheese', 98, 11, 4.3, 3.4);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `starting_weight` float DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `age` int(11) DEFAULT 20,
  `weight` float DEFAULT 70,
  `height` float DEFAULT 170,
  `theme` varchar(10) DEFAULT 'light',
  `profile_pic` varchar(255) DEFAULT 'default.png',
  `goal` enum('Bulk','Cut') DEFAULT 'Bulk',
  `old_password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `starting_weight`, `password`, `age`, `weight`, `height`, `theme`, `profile_pic`, `goal`, `old_password`) VALUES
(7, 'Anaghkrishna', 'anaghkrishna@gmail.com', 62, '$2y$10$30tkPivaIOernM4bl6Tdmuccwddm5cS3DswX0/fdQZEHWf8N3mWVu', 19, 72, 180, 'light', 'default.png', 'Bulk', '$2y$10$8L1KtilV6SqXTKJJHy7HcuIYUf0JdXefF0cEOsMXwdl/cHlaLkU1.');

-- --------------------------------------------------------

--
-- Table structure for table `weight_log`
--

CREATE TABLE `weight_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `weight` float DEFAULT NULL,
  `log_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `weight_log`
--

INSERT INTO `weight_log` (`id`, `user_id`, `weight`, `log_date`) VALUES
(1, 3, 48, '2026-03-24'),
(2, 4, 80, '2026-03-24'),
(3, 4, 80, '2026-03-24'),
(4, 5, 62, '2026-03-24'),
(5, 5, 80, '2026-03-24'),
(6, 6, 52, '2026-03-24'),
(7, 6, 50, '2026-03-24'),
(8, 6, 40, '2026-03-24'),
(9, 7, 62, '2026-03-24'),
(10, 7, 72, '2026-03-24');

-- --------------------------------------------------------

--
-- Table structure for table `workout_logs`
--

CREATE TABLE `workout_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `exercise_name` varchar(100) NOT NULL,
  `sets` int(11) NOT NULL,
  `reps` int(11) NOT NULL,
  `weight_kg` float NOT NULL,
  `date_logged` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `workout_logs`
--

INSERT INTO `workout_logs` (`id`, `user_id`, `exercise_name`, `sets`, `reps`, `weight_kg`, `date_logged`) VALUES
(1, 1, 'push-ups', 3, 15, 8, '2026-03-24 10:09:30'),
(2, 1, 'bench-press', 3, 18, 60, '2026-03-24 10:30:08'),
(3, 1, 'pull-ups', 2, 4, 0, '2026-03-24 11:20:59'),
(4, 5, 'crunches', 4, 10, 0, '2026-03-24 12:29:52');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `daily_logs`
--
ALTER TABLE `daily_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `diet_logs`
--
ALTER TABLE `diet_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `food_data`
--
ALTER TABLE `food_data`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `weight_log`
--
ALTER TABLE `weight_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `workout_logs`
--
ALTER TABLE `workout_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `daily_logs`
--
ALTER TABLE `daily_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `diet_logs`
--
ALTER TABLE `diet_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `food_data`
--
ALTER TABLE `food_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `weight_log`
--
ALTER TABLE `weight_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `workout_logs`
--
ALTER TABLE `workout_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `daily_logs`
--
ALTER TABLE `daily_logs`
  ADD CONSTRAINT `daily_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `diet_logs`
--
ALTER TABLE `diet_logs`
  ADD CONSTRAINT `diet_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `weight_log`
--
ALTER TABLE `weight_log`
  ADD CONSTRAINT `weight_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `workout_logs`
--
ALTER TABLE `workout_logs`
  ADD CONSTRAINT `workout_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
