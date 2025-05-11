-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 01, 2025 at 01:29 AM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `elitefit`
--

-- --------------------------------------------------------

--
-- Table structure for table `assigned_workouts`
--

DROP TABLE IF EXISTS `assigned_workouts`;
CREATE TABLE IF NOT EXISTS `assigned_workouts` (
  `assignment_id` int NOT NULL AUTO_INCREMENT,
  `trainer_id` int DEFAULT NULL,
  `member_id` int DEFAULT NULL,
  `plan_id` int DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `notes` text,
  `status` enum('active','completed','paused') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`assignment_id`),
  KEY `trainer_id` (`trainer_id`),
  KEY `member_id` (`member_id`),
  KEY `plan_id` (`plan_id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `assigned_workouts`
--

INSERT INTO `assigned_workouts` (`assignment_id`, `trainer_id`, `member_id`, `plan_id`, `start_date`, `end_date`, `notes`, `status`, `created_at`) VALUES
(1, 15, 16, 1, '2025-05-22', '2025-12-18', 'Do the work well', 'active', '2025-04-02 19:28:21'),
(2, 15, 16, 4, '2025-04-17', '2025-05-01', 'doo well', 'active', '2025-04-02 20:43:43'),
(3, 15, 16, 4, '2025-04-17', '2025-05-01', 'doo well', 'active', '2025-04-02 21:30:16'),
(4, 15, 16, 3, '2025-04-03', '2025-04-30', 'Goodies', 'active', '2025-04-02 21:31:06'),
(5, 15, 18, 2, '2025-04-10', '2025-05-09', 'We begin early so be fast okayyy', 'active', '2025-04-02 23:00:22'),
(6, 15, 19, 2, '2025-04-08', '2025-04-26', 'You have to be early for work', 'active', '2025-04-06 21:58:06'),
(7, 15, 26, 2, '2025-04-14', '2025-04-23', 'Duration: 20-25 minutes\r\nEquipment: None (optional: mat, water bottle)\r\nGoal: Boost heart rate, improve endurance, and burn calories.', 'active', '2025-04-13 21:40:33'),
(8, 15, 28, 2, '2025-04-15', '2025-04-25', 'rggt bb rbtr', 'active', '2025-04-14 13:32:14');

-- --------------------------------------------------------

--
-- Table structure for table `booked_sessions`
--

DROP TABLE IF EXISTS `booked_sessions`;
CREATE TABLE IF NOT EXISTS `booked_sessions` (
  `booking_id` int NOT NULL AUTO_INCREMENT,
  `trainer_id` int DEFAULT NULL,
  `member_id` int DEFAULT NULL,
  `session_date` date DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `status` enum('scheduled','completed','cancelled') DEFAULT 'scheduled',
  `notes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`booking_id`),
  KEY `trainer_id` (`trainer_id`),
  KEY `member_id` (`member_id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `booked_sessions`
--

INSERT INTO `booked_sessions` (`booking_id`, `trainer_id`, `member_id`, `session_date`, `start_time`, `end_time`, `status`, `notes`, `created_at`) VALUES
(1, 15, 26, '2025-04-14', '09:00:00', '10:00:00', 'completed', NULL, '2025-04-13 23:07:42'),
(2, 15, 28, '2025-04-17', '13:00:00', '14:00:00', 'scheduled', NULL, '2025-04-14 13:25:36'),
(3, 15, 15, '2025-04-24', '13:15:00', '14:15:00', 'completed', NULL, '2025-04-21 22:25:31'),
(4, 15, 26, '2025-04-24', '13:50:00', '14:50:00', 'completed', NULL, '2025-04-22 00:52:31'),
(5, 15, 26, '2025-04-24', '13:50:00', '14:50:00', 'completed', NULL, '2025-04-22 12:28:35'),
(6, 15, 26, '2025-05-01', '12:39:00', '13:39:00', 'completed', NULL, '2025-04-27 23:39:00'),
(7, 15, 26, '2025-05-01', '13:55:00', '14:55:00', 'completed', NULL, '2025-04-27 23:53:50');

-- --------------------------------------------------------

--
-- Table structure for table `equipment`
--

DROP TABLE IF EXISTS `equipment`;
CREATE TABLE IF NOT EXISTS `equipment` (
  `equipment_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `status` enum('available','maintenance','out_of_service') DEFAULT 'available',
  `last_maintenance_date` date DEFAULT NULL,
  `next_maintenance_date` date DEFAULT NULL,
  `is_archived` tinyint(1) DEFAULT '0',
  `archived_at` datetime DEFAULT NULL,
  PRIMARY KEY (`equipment_id`)
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `equipment`
--

INSERT INTO `equipment` (`equipment_id`, `name`, `status`, `last_maintenance_date`, `next_maintenance_date`, `is_archived`, `archived_at`) VALUES
(3, 'Treadmill', 'available', '2025-04-07', NULL, 0, NULL),
(18, 'New and trancate', 'out_of_service', '2025-04-26', '2025-05-09', 0, NULL),
(4, 'Elliptical Trainer', 'available', '2025-04-07', NULL, 0, NULL),
(5, 'Stationary Bike', 'maintenance', '2025-02-05', NULL, 0, NULL),
(6, 'Rowing Machine', 'available', '2025-04-07', NULL, 0, NULL),
(7, 'Dumbbells', 'available', '2025-04-07', NULL, 0, NULL),
(8, 'Barbells', 'maintenance', '2025-04-07', NULL, 0, NULL),
(9, 'Kettlebells', 'maintenance', '2025-02-05', NULL, 0, NULL),
(10, 'Leg Press Machine', 'available', '2025-04-07', NULL, 0, NULL),
(11, 'Chest Press Machine', 'out_of_service', '2024-12-17', NULL, 0, NULL),
(12, 'Lat Pulldown Machine', 'available', '2025-04-07', NULL, 0, NULL),
(13, 'Cable Crossover Machine', 'available', '2025-04-07', NULL, 0, NULL),
(14, 'Smith Machine', 'available', '2025-04-07', NULL, 0, NULL),
(15, 'Pull-Up Bar', 'available', '2025-04-07', NULL, 0, NULL),
(16, 'Dip Station', 'available', '2025-04-07', NULL, 0, NULL),
(17, 'Power Rack', 'available', '2025-04-07', NULL, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `fitness_goals`
--

DROP TABLE IF EXISTS `fitness_goals`;
CREATE TABLE IF NOT EXISTS `fitness_goals` (
  `goal_id` int NOT NULL AUTO_INCREMENT,
  `member_id` int DEFAULT NULL,
  `goal_text` text NOT NULL,
  `target_date` date DEFAULT NULL,
  `is_completed` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`goal_id`),
  KEY `member_id` (`member_id`)
) ENGINE=MyISAM AUTO_INCREMENT=63 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `fitness_goals`
--

INSERT INTO `fitness_goals` (`goal_id`, `member_id`, `goal_text`, `target_date`, `is_completed`) VALUES
(13, 17, 'jog', NULL, 0),
(12, 16, 'Be fat', NULL, 0),
(11, 13, 'dfgtsg', NULL, 0),
(10, 11, 'goattto', NULL, 0),
(9, 10, 'sgrgws', NULL, 0),
(8, 9, 'Abs', NULL, 0),
(14, 18, 'Want to be strong and healthy', NULL, 0),
(15, 19, 'Want more than everything as expected', NULL, 0),
(16, 26, 'I want to gain weight', NULL, 0),
(17, 26, 'Want to reduce fat', NULL, 0),
(18, 26, 'Want to be fit', NULL, 0),
(19, 27, 'be good', NULL, 0),
(20, 28, 'Want to get a healthy body', NULL, 0),
(21, 28, 'Want to be strong', NULL, 0),
(22, 29, 'Saw man', NULL, 0),
(23, 30, 'Want to be fat', NULL, 0),
(24, 30, 'health', NULL, 0),
(25, 30, 'stay strong', NULL, 0),
(26, 30, 'get longer legs', NULL, 0),
(27, 31, 'Want to be fat', NULL, 0),
(28, 31, 'grow tall', NULL, 0),
(29, 31, 'become muscular', NULL, 0),
(30, 32, 'Big', NULL, 0),
(31, 32, 'Fat', NULL, 0),
(32, 32, 'Strong', NULL, 0),
(33, 33, 'GOal', NULL, 0),
(34, 33, 'Ball', NULL, 0),
(35, 33, 'soar', NULL, 0),
(36, 34, 'Horn', NULL, 0),
(37, 34, 'Car', NULL, 0),
(38, 34, 'Gold', NULL, 0),
(39, 35, 'Hose', NULL, 0),
(40, 36, 'Horn', NULL, 0),
(41, 37, 'Hornrn', NULL, 0),
(42, 37, 'Hornn', NULL, 0),
(43, 38, 'tg r vdv', NULL, 0),
(44, 40, 'hhh', NULL, 0),
(45, 41, 'Healthy lifeset', NULL, 0),
(46, 42, 'Healthy boy', NULL, 0),
(47, 43, 'Stay healthy', NULL, 0),
(48, 43, 'Be strong', NULL, 0),
(49, 44, 'Healthy Body', NULL, 0),
(50, 44, 'strenght', NULL, 0),
(51, 44, 'Weight', NULL, 0),
(52, 44, 'Bookings', NULL, 0),
(53, 46, 'strong', NULL, 0),
(54, 47, 'hot boy', NULL, 0),
(55, 48, 'Tall', NULL, 0),
(56, 49, 'long', NULL, 0),
(57, 50, 'Be strong', NULL, 0),
(58, 51, 'Big head', NULL, 0),
(59, 52, 'Hot sun', NULL, 0),
(60, 53, 'Good thing', NULL, 0),
(61, 54, 'Goall', NULL, 0),
(62, 55, 'Holo', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `member_fitness`
--

DROP TABLE IF EXISTS `member_fitness`;
CREATE TABLE IF NOT EXISTS `member_fitness` (
  `member_id` int NOT NULL,
  `height` decimal(5,2) DEFAULT NULL COMMENT 'in cm',
  `weight` decimal(5,2) DEFAULT NULL COMMENT 'in kg',
  `body_type` enum('ectomorph','mesomorph','endomorph') DEFAULT NULL,
  `experience_level` enum('beginner','intermediate','advanced') DEFAULT NULL,
  `health_conditions` text,
  PRIMARY KEY (`member_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `member_fitness`
--

INSERT INTO `member_fitness` (`member_id`, `height`, `weight`, `body_type`, `experience_level`, `health_conditions`) VALUES
(33, 189.00, 56.00, 'ectomorph', 'intermediate', 'none'),
(32, 189.00, 75.00, 'mesomorph', 'beginner', 'none'),
(30, 158.00, 67.00, 'endomorph', 'beginner', 'none'),
(31, 159.00, 68.00, 'mesomorph', 'advanced', 'none'),
(29, 150.00, 28.00, 'ectomorph', 'intermediate', 'None'),
(28, 157.00, 67.00, 'mesomorph', 'beginner', 'None'),
(27, 159.00, 67.00, 'mesomorph', 'beginner', 'none'),
(18, 45.00, 32.00, 'mesomorph', 'intermediate', 'None'),
(19, 120.00, 78.00, 'endomorph', 'advanced', 'No health condition now'),
(26, 156.00, 67.00, 'ectomorph', 'beginner', 'none'),
(34, 186.00, 65.00, 'mesomorph', 'intermediate', 'none'),
(35, 189.00, 65.00, 'ectomorph', 'intermediate', 'none'),
(36, 198.00, 65.00, 'mesomorph', 'intermediate', 'none'),
(37, 198.00, 56.00, 'ectomorph', 'intermediate', 'none'),
(38, 198.00, 65.00, 'ectomorph', 'intermediate', 'none'),
(39, 198.00, 89.00, 'mesomorph', 'beginner', NULL),
(40, 189.00, 65.00, 'mesomorph', 'intermediate', 'none'),
(41, 189.00, 69.00, 'mesomorph', 'intermediate', 'none'),
(42, 189.00, 70.00, 'ectomorph', 'beginner', 'None'),
(43, 189.00, 65.00, 'mesomorph', 'beginner', 'none'),
(44, 189.00, 98.00, 'mesomorph', 'intermediate', 'none'),
(46, 189.00, 67.00, 'ectomorph', 'intermediate', 'none'),
(47, 134.00, 69.00, 'mesomorph', 'intermediate', 'none'),
(48, 178.00, 67.00, 'mesomorph', 'intermediate', 'none'),
(49, 189.00, 78.00, 'ectomorph', 'intermediate', 'none'),
(50, 198.00, 96.00, 'mesomorph', 'intermediate', 'none'),
(51, 168.00, 55.00, 'ectomorph', 'beginner', 'none'),
(52, 190.00, 67.00, 'mesomorph', 'intermediate', 'none'),
(53, 198.00, 89.00, 'mesomorph', 'intermediate', 'none'),
(54, 189.00, 89.00, 'ectomorph', 'intermediate', 'none'),
(55, 196.00, 89.00, 'mesomorph', 'beginner', 'none'),
(56, 198.00, 65.00, 'mesomorph', 'intermediate', NULL),
(57, NULL, NULL, NULL, NULL, NULL),
(58, NULL, NULL, NULL, NULL, NULL),
(59, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `member_workout_preferences`
--

DROP TABLE IF EXISTS `member_workout_preferences`;
CREATE TABLE IF NOT EXISTS `member_workout_preferences` (
  `member_id` int NOT NULL,
  `plan_id` int NOT NULL,
  `preference_order` int DEFAULT NULL,
  PRIMARY KEY (`member_id`,`plan_id`),
  KEY `plan_id` (`plan_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `member_workout_preferences`
--

INSERT INTO `member_workout_preferences` (`member_id`, `plan_id`, `preference_order`) VALUES
(2, 1, 1),
(2, 3, 2),
(3, 1, 1),
(3, 3, 2),
(4, 2, 1),
(4, 4, 2),
(5, 3, 1),
(5, 4, 2),
(6, 1, 1),
(6, 3, 2),
(7, 1, 1),
(7, 3, 2),
(8, 1, 1),
(8, 2, 2),
(9, 1, 1),
(9, 3, 2),
(10, 1, 1),
(10, 3, 2),
(11, 1, 1),
(11, 2, 2),
(11, 4, 3),
(13, 1, 1),
(13, 2, 2),
(16, 1, 1),
(16, 2, 2),
(17, 4, 1),
(18, 1, 1),
(18, 2, 2),
(18, 3, 3),
(19, 1, 1),
(19, 2, 2),
(19, 3, 3),
(26, 3, 1),
(26, 5, 2),
(27, 1, 1),
(27, 3, 2),
(28, 1, 1),
(28, 2, 2),
(28, 4, 3),
(29, 1, 1),
(29, 2, 2),
(29, 5, 3),
(30, 1, 1),
(30, 4, 2),
(30, 5, 3),
(31, 1, 1),
(31, 2, 2),
(31, 3, 3),
(32, 1, 1),
(32, 3, 2),
(32, 5, 3),
(33, 1, 1),
(33, 2, 2),
(33, 5, 3),
(34, 1, 1),
(34, 2, 2),
(34, 3, 3),
(35, 1, 1),
(35, 2, 2),
(35, 3, 3),
(36, 3, 1),
(36, 4, 2),
(36, 5, 3),
(37, 1, 1),
(37, 2, 2),
(37, 4, 3),
(38, 2, 1),
(38, 3, 2),
(38, 4, 3),
(39, 1, 1),
(39, 2, 2),
(39, 3, 3),
(40, 1, 1),
(40, 2, 2),
(40, 3, 3),
(41, 1, 1),
(41, 2, 2),
(41, 3, 3),
(42, 2, 1),
(42, 3, 2),
(42, 4, 3),
(43, 1, 1),
(43, 3, 2),
(43, 5, 3),
(44, 1, 1),
(44, 3, 2),
(44, 4, 3),
(46, 1, 1),
(46, 2, 2),
(46, 3, 3),
(47, 1, 1),
(47, 2, 2),
(47, 3, 3),
(48, 1, 1),
(48, 2, 2),
(48, 3, 3),
(49, 1, 1),
(49, 2, 2),
(49, 3, 3),
(50, 1, 1),
(50, 2, 2),
(50, 3, 3),
(51, 1, 1),
(51, 2, 2),
(51, 4, 3),
(52, 1, 1),
(52, 2, 2),
(52, 3, 3),
(53, 1, 1),
(53, 2, 2),
(53, 3, 3),
(54, 1, 1),
(54, 2, 2),
(54, 3, 3),
(55, 1, 1),
(55, 2, 2),
(55, 3, 3),
(56, 1, 1),
(56, 2, 2),
(56, 3, 3);

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
CREATE TABLE IF NOT EXISTS `messages` (
  `message_id` int NOT NULL AUTO_INCREMENT,
  `sender_id` int NOT NULL,
  `receiver_id` int NOT NULL,
  `message_text` text NOT NULL,
  `sent_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_read` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`message_id`),
  KEY `sender_id` (`sender_id`),
  KEY `receiver_id` (`receiver_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`message_id`, `sender_id`, `receiver_id`, `message_text`, `sent_at`, `is_read`) VALUES
(1, 15, 16, 'Yoo the time is due come tomorrow', '2025-04-02 22:20:00', 0),
(2, 15, 18, 'Dora Tomorow be arund okay', '2025-04-02 23:00:53', 1),
(3, 15, 18, 'We have more work to do ooo', '2025-04-02 23:36:17', 1),
(5, 15, 19, 'Make sure you are early to work tomorrow okayyy', '2025-04-06 21:58:38', 0),
(6, 15, 26, 'Lets meet early for tomorrow\'s training', '2025-04-13 21:58:43', 1);

-- --------------------------------------------------------

--
-- Table structure for table `progress_tracking`
--

DROP TABLE IF EXISTS `progress_tracking`;
CREATE TABLE IF NOT EXISTS `progress_tracking` (
  `track_id` int NOT NULL AUTO_INCREMENT,
  `member_id` int DEFAULT NULL,
  `trainer_id` int DEFAULT NULL,
  `measurement_date` date NOT NULL,
  `weight` decimal(5,2) DEFAULT NULL,
  `body_fat` decimal(5,2) DEFAULT NULL,
  `muscle_mass` decimal(5,2) DEFAULT NULL,
  `notes` text,
  PRIMARY KEY (`track_id`),
  KEY `member_id` (`member_id`),
  KEY `trainer_id` (`trainer_id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `progress_tracking`
--

INSERT INTO `progress_tracking` (`track_id`, `member_id`, `trainer_id`, `measurement_date`, `weight`, `body_fat`, `muscle_mass`, `notes`) VALUES
(1, 18, 15, '2025-04-04', 67.00, 30.00, 45.00, 'Good'),
(2, 18, 15, '2025-04-12', 64.00, 34.00, 50.00, 'Doing great lately'),
(3, 26, 15, '2025-04-13', 64.00, 25.00, 47.00, 'More room for improvement'),
(4, 26, 15, '2025-04-02', 68.00, 14.00, 32.00, 'More room for improvement'),
(5, 28, 15, '2025-04-14', 65.00, 30.00, 10.00, 'tgtrgtgrb'),
(6, 28, 15, '2025-04-21', 60.00, 5.00, 22.00, 'Well Done'),
(7, 26, 15, '2025-04-20', 62.00, 18.00, 44.00, 'Good Work done');

-- --------------------------------------------------------

--
-- Table structure for table `trainers`
--

DROP TABLE IF EXISTS `trainers`;
CREATE TABLE IF NOT EXISTS `trainers` (
  `trainer_id` int NOT NULL,
  `specialization` varchar(100) DEFAULT NULL,
  `certification` varchar(100) DEFAULT NULL,
  `years_experience` int DEFAULT NULL,
  `bio` text,
  `is_archived` tinyint(1) DEFAULT '0',
  `archived_at` datetime DEFAULT NULL,
  PRIMARY KEY (`trainer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `trainers`
--

INSERT INTO `trainers` (`trainer_id`, `specialization`, `certification`, `years_experience`, `bio`, `is_archived`, `archived_at`) VALUES
(12, 'Strength Training', 'NASM Certified', 5, 'Specialized in strength training and bodybuilding', 0, NULL),
(15, 'Corrective Exercise Specialist', 'NASM-CES (Corrective Exercise Specialist)', 5, 'Helping clients improve posture, reduce pain, and recover from injuries through tailored movement and mobility programs. Ideal for working with clients who have muscular imbalances or past injuries.', 0, NULL),
(25, ' Strength Training & Weight Loss', 'NASM Certified Personal Trainer (CPT)', 7, 'Dedicated and results-driven personal trainer with over 6 years of experience helping clients transform their lives through personalized workout plans, nutritional guidance, and motivational coaching. Specializes in strength training, weight loss, and functional fitness. Passionate about empowering individuals to reach their full potential, both physically and mentally', 1, '2025-04-28 03:57:58'),
(60, 'dfrtghj', 'dfghj', 45678, 'fghj', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `trainer_availability`
--

DROP TABLE IF EXISTS `trainer_availability`;
CREATE TABLE IF NOT EXISTS `trainer_availability` (
  `availability_id` int NOT NULL AUTO_INCREMENT,
  `trainer_id` int DEFAULT NULL,
  `day_of_week` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') DEFAULT NULL,
  `availability_type` enum('morning','afternoon','full') DEFAULT NULL,
  PRIMARY KEY (`availability_id`),
  UNIQUE KEY `unique_trainer_day` (`trainer_id`,`day_of_week`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `trainer_availability`
--

INSERT INTO `trainer_availability` (`availability_id`, `trainer_id`, `day_of_week`, `availability_type`) VALUES
(2, 15, 'Monday', 'morning'),
(3, 15, 'Thursday', 'afternoon');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `contact_number` varchar(15) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('member','trainer','admin','equipment_manager') NOT NULL DEFAULT 'member',
  `location` varchar(50) DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `date_registered` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_active` tinyint(1) DEFAULT '1',
  `is_verified` tinyint(1) DEFAULT '0',
  `verify_token` varchar(64) DEFAULT NULL,
  `token_expires` datetime DEFAULT NULL,
  `is_archived` tinyint(1) DEFAULT '0',
  `archived_at` datetime DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  `password_otp_hash` varchar(255) DEFAULT NULL,
  `password_otp_expires` datetime DEFAULT NULL,
  `password_otp_attempts` int DEFAULT '0',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `contact_number` (`contact_number`)
) ENGINE=MyISAM AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `first_name`, `last_name`, `email`, `contact_number`, `password_hash`, `role`, `location`, `gender`, `date_of_birth`, `profile_picture`, `date_registered`, `is_active`, `is_verified`, `verify_token`, `token_expires`, `is_archived`, `archived_at`, `reset_token`, `reset_expires`, `password_otp_hash`, `password_otp_expires`, `password_otp_attempts`) VALUES
(25, 'Kwame', 'Mensah', 'kwame@gmail.com', '054698655', '$2y$10$lCYU9u9c4wPTJ82hmSoWs.uv1dlErYCuSn/ozqXYEoYr2dVw9gqkO', 'trainer', NULL, NULL, NULL, NULL, '2025-04-07 10:03:36', 1, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 0),
(17, 'samson', 'Paul', 'marylyngrip@gmail.com', '025852002585', '$2y$10$IIk/WPgoBcpUz1jaEDOHm.7xRSTxrRGyyHbhpryArY8ADqOJwm7iW', 'member', NULL, NULL, NULL, 'uploads/profile_pictures/67edadd326b8d.png', '2025-04-02 21:36:19', 1, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 0),
(28, 'Harry', 'Johnson', 'Harry-johnson.agyemang@rmu.edu.gh', '05040888510', '$2y$10$eNYku6dSWosMjBhoB1H0putLUqolP82wYWkOQUJ06WXitHecJr2N.', 'member', NULL, NULL, NULL, 'uploads/profile_pictures/67fd0c1aa827c.png', '2025-04-14 13:22:34', 1, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 0),
(15, 'Junior', 'Owusu', 'junior@gmail.com', '0344355353', '$2y$10$IHs7FAnammqH8Tbjj3DJPOqxHU4Jr/pbXJdjHYiKmCBO6qgZ6Ooqa', 'trainer', 'Ghana', 'male', '1991-09-13', 'uploads/profile_pictures/trainer_15_1744421292.png', '2025-04-02 18:30:51', 1, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 0),
(27, 'Johnson', 'Osei', 'johnson@gmail.com', 'abffgfgdf', '$2y$10$kuQXzB4UnTM3kaHzFcTOs.VdAp5C7gquieZHph3nEFylrntTcp.8.', 'member', NULL, NULL, NULL, 'uploads/profile_pictures/67fcffa418da5.png', '2025-04-14 12:29:24', 1, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 0),
(21, 'Michael', 'Asante', 'asantemichael044@gmail.com', '0255478555', '$2y$10$gC4PH4Y7LtM7p./PXiJRgOPoIj7Oox62FalmGCuev0den.qqHTyjq', 'equipment_manager', NULL, NULL, NULL, NULL, '2025-04-06 22:25:42', 1, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 0),
(24, '', '', 'admin@elitefit.com', '', '$2y$10$UwBPeSz7vQh8SAVavjtzN.nwUjmddqVM5HZjZ0s3z1Nm69xA7izZm', 'admin', NULL, NULL, NULL, NULL, '2025-04-07 01:06:24', 1, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 0),
(26, 'Dora', 'Owusu', 'wemoye@gmail.com', '05698753', '$2y$10$TDJgFr2dAJ6bMW6SlhKThOHmMAcI4QeivuYEKIlg34x843AQvYitS', 'member', NULL, NULL, NULL, 'uploads/profile_pictures/67fc2e14a86f4.png', '2025-04-13 20:59:44', 1, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 0),
(29, 'sow', 'max', 'marylyngripo@gmail.com', '0203567826', '$2y$10$8jbFMhLPLixIfkJnBWOIeu4N.PZh5mcC3ZwE0FF/H/AHQp5L/rL7.', 'member', NULL, NULL, NULL, 'uploads/profile_pictures/67fda5b039293.png', '2025-04-15 00:17:52', 1, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 0),
(44, 'Nana', 'Yaw', 'biggie@gmail.com', '02525282028', '$2y$10$koUhCNeC1tPfEbUFROVbIOLILODD51.O567lo0FyAc/f2WkghLswe', 'member', 'Ghana', 'male', '2000-12-14', 'uploads/profile_pictures/6804195b9ac7d.png', '2025-04-19 21:44:59', 1, 0, NULL, NULL, 0, NULL, '52f16c77b971c26cb02a56228a3182bd3e4de2ea3a27b0bb7aae24a64ad5c61c', '2025-04-28 11:38:53', NULL, NULL, 0),
(45, 'Jayda', 'mensah', 'jayda@gmail.com', '05855280885', '$2y$10$Psc7/aPjMrQ9t0UVuLrrJuy/5byrs4jwGjptCX0VXxZaNf/lu8206', 'member', NULL, NULL, NULL, NULL, '2025-04-24 23:55:54', 1, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 0),
(47, 'Max', 'Grip', 'maxgrip500@gmail.com', '0203567829', '$2y$10$7VHgnL3km4pjmNuPsZ7RueYllxylPdSJbi63AY4TmhPA.PyHTv66O', 'member', 'Ghana', 'female', '1995-08-16', 'uploads/profile_pictures/680c6de4dabfa.png', '2025-04-26 05:24:48', 1, 0, NULL, NULL, 0, NULL, 'f7c753c1e14e76c0158c9e7db64bc9d1df78c21ca3c4631e2c5b8212ef6d99f1', '2025-04-28 11:48:41', NULL, NULL, 0),
(48, 'Jefferson', 'Forson', 'jeffersonforson24@gmail.com', '0597685562', '$2y$10$iSIqRk2aQpH2YmxSNGNum.L8k9EJgMyanhR3Snl.ArtHa.lVR21m2', 'member', 'Takoradi', 'male', '1997-10-12', 'uploads/profile_pictures/680cb0a69b79d.png', '2025-04-26 10:10:12', 1, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 0),
(51, 'Joyce', 'Attawah Elli', 'elliattawah23@gmail.com', '0205895657', '$2y$10$tS9BnJ5B2L7OQFLIxbf4QektNO9ndqjXiQ4r9bOfmkoRTyJ/OWUp2', 'member', 'Ghana', 'female', '1998-05-15', 'uploads/profile_pictures/680f9841ab79f.png', '2025-04-28 15:02:41', 1, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `workout_categories`
--

DROP TABLE IF EXISTS `workout_categories`;
CREATE TABLE IF NOT EXISTS `workout_categories` (
  `category_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `default_duration` decimal(5,2) NOT NULL DEFAULT '30.00',
  PRIMARY KEY (`category_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `workout_categories`
--

INSERT INTO `workout_categories` (`category_id`, `name`, `default_duration`) VALUES
(1, 'Cardio', 30.00),
(2, 'Strength', 45.00),
(3, 'Yoga', 40.00),
(4, 'Quick HIIT', 1.00);

-- --------------------------------------------------------

--
-- Table structure for table `workout_plans`
--

DROP TABLE IF EXISTS `workout_plans`;
CREATE TABLE IF NOT EXISTS `workout_plans` (
  `plan_id` int NOT NULL AUTO_INCREMENT,
  `plan_name` varchar(100) NOT NULL,
  `description` text,
  `difficulty` enum('beginner','intermediate','advanced') DEFAULT NULL,
  `duration_weeks` int DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT '1',
  `focus_area` varchar(100) DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`plan_id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `workout_plans`
--

INSERT INTO `workout_plans` (`plan_id`, `plan_name`, `description`, `difficulty`, `duration_weeks`, `is_public`, `focus_area`, `created_by`, `created_at`) VALUES
(1, 'Strength Training', 'Focus on building muscle mass and strength', 'intermediate', 8, 1, NULL, NULL, '2025-04-12 00:59:48'),
(2, 'Cardio Blast', 'High-intensity cardiovascular training', 'beginner', 4, 1, NULL, NULL, '2025-04-12 00:59:48'),
(3, 'Yoga Fundamentals', 'Basic yoga for flexibility and relaxation', 'beginner', 6, 1, NULL, NULL, '2025-04-12 00:59:48'),
(4, 'HIIT Program', 'High-intensity interval training', 'advanced', 4, 1, NULL, NULL, '2025-04-12 00:59:48'),
(5, 'Total Body Reboot', 'A beginner-friendly full-body workout plan designed to build strength, improve endurance, and ease into a consistent gym routine.', 'beginner', 4, 1, 'Full body (strength + cardio)', 15, '2025-04-12 01:10:44');

-- --------------------------------------------------------

--
-- Table structure for table `workout_sessions`
--

DROP TABLE IF EXISTS `workout_sessions`;
CREATE TABLE IF NOT EXISTS `workout_sessions` (
  `session_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `date` date NOT NULL,
  `workout_type` varchar(50) NOT NULL,
  `duration` decimal(5,2) DEFAULT NULL COMMENT 'duration in minutes (supports fractions)',
  `completed_status` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`session_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `workout_sessions`
--

INSERT INTO `workout_sessions` (`session_id`, `user_id`, `date`, `workout_type`, `duration`, `completed_status`) VALUES
(1, 26, '2025-04-27', 'Cardio', 30.00, 1);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
