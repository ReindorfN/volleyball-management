-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Dec 17, 2024 at 09:27 PM
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
-- 
--

-- --------------------------------------------------------

--
-- Table structure for table `v_ball_activity_log`
--

CREATE TABLE `v_ball_activity_log` (
  `activity_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `activity_type` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `v_ball_activity_log`
--

INSERT INTO `v_ball_activity_log` (`activity_id`, `user_id`, `activity_type`, `description`, `timestamp`) VALUES
(1, 15, 'USER_CREATION', 'Added new coach: Hayford Bo', '2024-12-17 17:52:07'),
(2, 15, 'TEAM_CREATION', 'Added new team: Mighty Hitters', '2024-12-17 17:53:00'),
(3, 15, 'ANNOUNCEMENT_CREATED', 'Created general announcement: System update', '2024-12-17 19:40:44'),
(4, 15, 'ANNOUNCEMENT_CREATED', 'Created general announcement: System update', '2024-12-17 19:40:44');

-- --------------------------------------------------------

--
-- Table structure for table `v_ball_announcements`
--

CREATE TABLE `v_ball_announcements` (
  `announcement_id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL,
  `announcement_text` text NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `v_ball_fan_follows`
--

CREATE TABLE `v_ball_fan_follows` (
  `follow_id` int(11) NOT NULL,
  `fan_id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL,
  `followed_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `notification_preference` tinyint(1) DEFAULT 1,
  `interaction_count` int(11) DEFAULT 0,
  `last_interaction_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `favorite_status` tinyint(1) DEFAULT 0,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `v_ball_matches`
--

CREATE TABLE `v_ball_matches` (
  `match_id` int(11) NOT NULL,
  `match_date` date NOT NULL,
  `team1_id` int(11) NOT NULL,
  `team2_id` int(11) NOT NULL,
  `venue` varchar(100) NOT NULL,
  `score_team1` int(11) DEFAULT 0,
  `score_team2` int(11) DEFAULT 0,
  `match_status` enum('scheduled','ongoing','completed') DEFAULT 'scheduled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `v_ball_matches`
--

INSERT INTO `v_ball_matches` (`match_id`, `match_date`, `team1_id`, `team2_id`, `venue`, `score_team1`, `score_team2`, `match_status`, `created_at`) VALUES
(2, '2024-12-17', 1, 2, 'Ash Pitch', 0, 0, 'scheduled', '2024-12-16 16:20:41'),
(3, '2024-12-16', 2, 1, 'New Multi-purpose court', 0, 0, 'scheduled', '2024-12-16 17:45:01');

-- --------------------------------------------------------

--
-- Table structure for table `v_ball_match_strategies`
--

CREATE TABLE `v_ball_match_strategies` (
  `strategy_id` int(11) NOT NULL,
  `match_id` int(11) NOT NULL,
  `coach_id` int(11) NOT NULL,
  `strategy_text` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `v_ball_match_strategies`
--

INSERT INTO `v_ball_match_strategies` (`strategy_id`, `match_id`, `coach_id`, `strategy_text`, `created_at`) VALUES
(1, 2, 5, 'All Players must be ready and on the court not less than 30 minutes before the match starts. Also, we will likely use a 4-2 formation.', '2024-12-16 16:22:07');

-- --------------------------------------------------------

--
-- Table structure for table `v_ball_notifications`
--

CREATE TABLE `v_ball_notifications` (
  `notification_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `notification_type` enum('TEAM_SPECIFIC','GENERAL','MATCH_AVAILABILITY') NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `team_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `v_ball_notifications`
--

INSERT INTO `v_ball_notifications` (`notification_id`, `sender_id`, `notification_type`, `title`, `message`, `team_id`, `created_at`) VALUES
(1, 15, 'GENERAL', 'System update', 'There will be a system update tomorrow.', NULL, '2024-12-17 19:40:44'),
(2, 15, 'GENERAL', 'System update', 'There will be a system update tomorrow.', NULL, '2024-12-17 19:40:44');

-- --------------------------------------------------------

--
-- Table structure for table `v_ball_notification_recipients`
--

CREATE TABLE `v_ball_notification_recipients` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `read_status` tinyint(1) DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `v_ball_notification_recipients`
--

INSERT INTO `v_ball_notification_recipients` (`notification_id`, `user_id`, `read_status`, `read_at`) VALUES
(1, 4, 0, NULL),
(1, 5, 0, NULL),
(1, 6, 0, NULL),
(1, 7, 0, NULL),
(1, 8, 0, NULL),
(1, 13, 0, NULL),
(1, 14, 0, NULL),
(1, 15, 0, NULL),
(1, 16, 0, NULL),
(2, 4, 0, NULL),
(2, 5, 0, NULL),
(2, 6, 0, NULL),
(2, 7, 0, NULL),
(2, 8, 0, NULL),
(2, 13, 0, NULL),
(2, 14, 0, NULL),
(2, 15, 0, NULL),
(2, 16, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `v_ball_players`
--

CREATE TABLE `v_ball_players` (
  `player_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `team_id` int(11) DEFAULT NULL,
  `position` enum('spiker','blocker','setter','libero','server') NOT NULL,
  `jersey_number` int(11) NOT NULL,
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `v_ball_players`
--

INSERT INTO `v_ball_players` (`player_id`, `user_id`, `team_id`, `position`, `jersey_number`, `joined_at`) VALUES
(1, 13, 1, 'server', 20, '2024-12-12 21:33:26'),
(2, 14, 1, 'spiker', 7, '2024-12-12 21:41:12');

-- --------------------------------------------------------

--
-- Table structure for table `v_ball_statistics`
--

CREATE TABLE `v_ball_statistics` (
  `stat_id` int(11) NOT NULL,
  `match_id` int(11) NOT NULL,
  `player_id` int(11) NOT NULL,
  `spikes` int(11) DEFAULT 0,
  `blocks` int(11) DEFAULT 0,
  `serves` int(11) DEFAULT 0,
  `errors` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `v_ball_teams`
--

CREATE TABLE `v_ball_teams` (
  `team_id` int(11) NOT NULL,
  `team_name` varchar(100) NOT NULL,
  `coach_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `v_ball_teams`
--

INSERT INTO `v_ball_teams` (`team_id`, `team_name`, `coach_id`, `created_at`) VALUES
(1, 'Blazers', 5, '2024-12-10 19:38:01'),
(2, 'Nike Empire', 5, '2024-12-13 00:12:32'),
(3, 'Mighty Hitters', 16, '2024-12-17 17:53:00');

-- --------------------------------------------------------

--
-- Table structure for table `v_ball_tournaments`
--

CREATE TABLE `v_ball_tournaments` (
  `tournament_id` int(11) NOT NULL,
  `tournament_name` varchar(100) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `v_ball_tournaments`
--

INSERT INTO `v_ball_tournaments` (`tournament_id`, `tournament_name`, `start_date`, `end_date`) VALUES
(2, '2025 Semester 1', '2025-01-01', '2025-01-11');

-- --------------------------------------------------------

--
-- Table structure for table `v_ball_users`
--

CREATE TABLE `v_ball_users` (
  `user_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `username` varchar(101) GENERATED ALWAYS AS (lcase(concat(`first_name`,'.',`last_name`))) STORED,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('fan','player','coach','organizer','admin') DEFAULT 'fan',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_pic` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `v_ball_users`
--

INSERT INTO `v_ball_users` (`user_id`, `first_name`, `last_name`, `password_hash`, `email`, `role`, `created_by`, `created_at`, `profile_pic`) VALUES
(4, 'New', 'User', '$2y$10$9YdJUX3.q/UlZwzdR9ZbAeST21UTfzHGMo9tVV56UGnlXBmyWgaZy', 'user1@test.com', 'fan', NULL, '2024-12-10 02:28:29', NULL),
(5, 'Shadrack', 'Berdah', '$2y$10$.Xeirqg9QFYR3afcvsp2DuXX0pyC592tS5b7jIAa7rA7N7jyFcdD2', 'sheddy@gmail.com', 'coach', NULL, '2024-12-10 19:24:13', NULL),
(6, 'Ernest', 'Mensah', '$2y$10$RG2aRXO6vfNAFxciPZJZDuSiRXIaUFyEbLYaYecFFR3mMgt5fgINm', 'ernest@gmail.com', 'fan', NULL, '2024-12-10 19:32:20', NULL),
(7, 'Pinto', 'Ezra', '$2y$10$A629zdWDrEhW8psprC4lTuKBi68kwwFFhCkfobxD7IKZdbLvxWobW', 'pinto@gmail.com', 'organizer', NULL, '2024-12-10 19:33:18', NULL),
(8, 'Tawiah', 'Osei', '$2y$10$OjteVCN2qgbRlC5AT5tHge/HIrQZbB6NdQ1KrnM1j1IrFD7RdJ6BG', 'tawiahn@gmail.com', 'player', 5, '2024-12-11 19:48:18', NULL),
(13, 'Sam', 'Kay', '$2y$10$3TapP.wKAeod13deZX2iM.BoFp78jZPqCBehmFMiNv0B07jKdNfSW', 'samkay@gmail.com', 'player', 5, '2024-12-12 21:33:26', NULL),
(14, 'Ibrahim', 'Dasuki', '$2y$10$hD7zPhJu3Vf67wIfHhshoeN9JktokUpX1vham2I4IU.vO/oEsP1Ea', 'ibrahim@gmail.com', 'player', 5, '2024-12-12 21:41:12', NULL),
(15, 'admin', 'admin', '$2y$10$TFYU/sj83Kvt9lsRi94bQe/boy7wZZf9pq1FBCsyiRluARvPjX4Z6', 'admin@test.com', 'admin', NULL, '2024-12-16 20:04:44', NULL),
(16, 'Hayford', 'Bo', '$2y$10$/B9N3pUF7YeShh2tuAy4J.rxbV8R7rkCp9NPWPQTIa8fkhHr90Gde', 'hfd@mail.com', 'coach', 15, '2024-12-17 17:52:07', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `v_ball_activity_log`
--
ALTER TABLE `v_ball_activity_log`
  ADD PRIMARY KEY (`activity_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `v_ball_announcements`
--
ALTER TABLE `v_ball_announcements`
  ADD PRIMARY KEY (`announcement_id`),
  ADD KEY `fk_announcement_team` (`team_id`),
  ADD KEY `fk_announcement_creator` (`created_by`);

--
-- Indexes for table `v_ball_fan_follows`
--
ALTER TABLE `v_ball_fan_follows`
  ADD PRIMARY KEY (`follow_id`),
  ADD UNIQUE KEY `unique_fan_team` (`fan_id`,`team_id`),
  ADD KEY `team_id` (`team_id`);

--
-- Indexes for table `v_ball_matches`
--
ALTER TABLE `v_ball_matches`
  ADD PRIMARY KEY (`match_id`),
  ADD KEY `team1_id` (`team1_id`),
  ADD KEY `team2_id` (`team2_id`);

--
-- Indexes for table `v_ball_match_strategies`
--
ALTER TABLE `v_ball_match_strategies`
  ADD PRIMARY KEY (`strategy_id`),
  ADD KEY `match_id` (`match_id`),
  ADD KEY `coach_id` (`coach_id`);

--
-- Indexes for table `v_ball_notifications`
--
ALTER TABLE `v_ball_notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `team_id` (`team_id`);

--
-- Indexes for table `v_ball_notification_recipients`
--
ALTER TABLE `v_ball_notification_recipients`
  ADD PRIMARY KEY (`notification_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `v_ball_players`
--
ALTER TABLE `v_ball_players`
  ADD PRIMARY KEY (`player_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `team_id` (`team_id`);

--
-- Indexes for table `v_ball_statistics`
--
ALTER TABLE `v_ball_statistics`
  ADD PRIMARY KEY (`stat_id`),
  ADD KEY `match_id` (`match_id`),
  ADD KEY `player_id` (`player_id`);

--
-- Indexes for table `v_ball_teams`
--
ALTER TABLE `v_ball_teams`
  ADD PRIMARY KEY (`team_id`),
  ADD UNIQUE KEY `team_name` (`team_name`),
  ADD KEY `coach_id` (`coach_id`);

--
-- Indexes for table `v_ball_tournaments`
--
ALTER TABLE `v_ball_tournaments`
  ADD PRIMARY KEY (`tournament_id`);

--
-- Indexes for table `v_ball_users`
--
ALTER TABLE `v_ball_users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username_2` (`username`),
  ADD KEY `created_by` (`created_by`);
ALTER TABLE `v_ball_users` ADD FULLTEXT KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `v_ball_activity_log`
--
ALTER TABLE `v_ball_activity_log`
  MODIFY `activity_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `v_ball_announcements`
--
ALTER TABLE `v_ball_announcements`
  MODIFY `announcement_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `v_ball_fan_follows`
--
ALTER TABLE `v_ball_fan_follows`
  MODIFY `follow_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `v_ball_matches`
--
ALTER TABLE `v_ball_matches`
  MODIFY `match_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `v_ball_match_strategies`
--
ALTER TABLE `v_ball_match_strategies`
  MODIFY `strategy_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `v_ball_notifications`
--
ALTER TABLE `v_ball_notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `v_ball_players`
--
ALTER TABLE `v_ball_players`
  MODIFY `player_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `v_ball_statistics`
--
ALTER TABLE `v_ball_statistics`
  MODIFY `stat_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `v_ball_teams`
--
ALTER TABLE `v_ball_teams`
  MODIFY `team_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `v_ball_tournaments`
--
ALTER TABLE `v_ball_tournaments`
  MODIFY `tournament_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `v_ball_users`
--
ALTER TABLE `v_ball_users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `v_ball_activity_log`
--
ALTER TABLE `v_ball_activity_log`
  ADD CONSTRAINT `v_ball_activity_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `v_ball_users` (`user_id`);

--
-- Constraints for table `v_ball_announcements`
--
ALTER TABLE `v_ball_announcements`
  ADD CONSTRAINT `fk_announcement_creator` FOREIGN KEY (`created_by`) REFERENCES `v_ball_users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_announcement_team` FOREIGN KEY (`team_id`) REFERENCES `v_ball_teams` (`team_id`) ON DELETE CASCADE;

--
-- Constraints for table `v_ball_fan_follows`
--
ALTER TABLE `v_ball_fan_follows`
  ADD CONSTRAINT `v_ball_fan_follows_ibfk_1` FOREIGN KEY (`fan_id`) REFERENCES `v_ball_users` (`user_id`),
  ADD CONSTRAINT `v_ball_fan_follows_ibfk_2` FOREIGN KEY (`team_id`) REFERENCES `v_ball_teams` (`team_id`);

--
-- Constraints for table `v_ball_matches`
--
ALTER TABLE `v_ball_matches`
  ADD CONSTRAINT `v_ball_matches_ibfk_1` FOREIGN KEY (`team1_id`) REFERENCES `v_ball_teams` (`team_id`),
  ADD CONSTRAINT `v_ball_matches_ibfk_2` FOREIGN KEY (`team2_id`) REFERENCES `v_ball_teams` (`team_id`);

--
-- Constraints for table `v_ball_match_strategies`
--
ALTER TABLE `v_ball_match_strategies`
  ADD CONSTRAINT `v_ball_match_strategies_ibfk_1` FOREIGN KEY (`match_id`) REFERENCES `v_ball_matches` (`match_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `v_ball_match_strategies_ibfk_2` FOREIGN KEY (`coach_id`) REFERENCES `v_ball_users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `v_ball_notifications`
--
ALTER TABLE `v_ball_notifications`
  ADD CONSTRAINT `v_ball_notifications_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `v_ball_users` (`user_id`),
  ADD CONSTRAINT `v_ball_notifications_ibfk_2` FOREIGN KEY (`team_id`) REFERENCES `v_ball_teams` (`team_id`);

--
-- Constraints for table `v_ball_notification_recipients`
--
ALTER TABLE `v_ball_notification_recipients`
  ADD CONSTRAINT `v_ball_notification_recipients_ibfk_1` FOREIGN KEY (`notification_id`) REFERENCES `v_ball_notifications` (`notification_id`),
  ADD CONSTRAINT `v_ball_notification_recipients_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `v_ball_users` (`user_id`);

--
-- Constraints for table `v_ball_players`
--
ALTER TABLE `v_ball_players`
  ADD CONSTRAINT `v_ball_players_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `v_ball_users` (`user_id`),
  ADD CONSTRAINT `v_ball_players_ibfk_2` FOREIGN KEY (`team_id`) REFERENCES `v_ball_teams` (`team_id`);

--
-- Constraints for table `v_ball_statistics`
--
ALTER TABLE `v_ball_statistics`
  ADD CONSTRAINT `v_ball_statistics_ibfk_1` FOREIGN KEY (`match_id`) REFERENCES `v_ball_matches` (`match_id`),
  ADD CONSTRAINT `v_ball_statistics_ibfk_2` FOREIGN KEY (`player_id`) REFERENCES `v_ball_players` (`player_id`);

--
-- Constraints for table `v_ball_teams`
--
ALTER TABLE `v_ball_teams`
  ADD CONSTRAINT `v_ball_teams_ibfk_1` FOREIGN KEY (`coach_id`) REFERENCES `v_ball_users` (`user_id`);

--
-- Constraints for table `v_ball_users`
--
ALTER TABLE `v_ball_users`
  ADD CONSTRAINT `v_ball_users_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `v_ball_users` (`user_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
