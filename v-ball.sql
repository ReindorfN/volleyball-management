-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Dec 16, 2024 at 04:59 PM
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
-- Database: `v-ball`
--

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
(1, '2024-12-15', 2, 1, 'Old Multi-purpose court', 0, 0, 'scheduled', '2024-12-13 00:38:22');

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

-- --------------------------------------------------------

--
-- Table structure for table `v_ball_notifications`
--

CREATE TABLE `v_ball_notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(2, 'Nike Empire', 5, '2024-12-13 00:12:32');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `v_ball_users`
--

INSERT INTO `v_ball_users` (`user_id`, `first_name`, `last_name`, `password_hash`, `email`, `role`, `created_by`, `created_at`) VALUES
(1, 'admin', 'admin', 'Admin512.', 'admin@test.com', 'admin', NULL, '2024-12-10 00:58:34'),
(4, 'New', 'User', '$2y$10$9YdJUX3.q/UlZwzdR9ZbAeST21UTfzHGMo9tVV56UGnlXBmyWgaZy', 'user1@test.com', 'fan', NULL, '2024-12-10 02:28:29'),
(5, 'Shadrack', 'Berdah', '$2y$10$.Xeirqg9QFYR3afcvsp2DuXX0pyC592tS5b7jIAa7rA7N7jyFcdD2', 'sheddy@gmail.com', 'coach', NULL, '2024-12-10 19:24:13'),
(6, 'Ernest', 'Mensah', '$2y$10$RG2aRXO6vfNAFxciPZJZDuSiRXIaUFyEbLYaYecFFR3mMgt5fgINm', 'ernest@gmail.com', 'fan', NULL, '2024-12-10 19:32:20'),
(7, 'Pinto', 'Ezra', '$2y$10$A629zdWDrEhW8psprC4lTuKBi68kwwFFhCkfobxD7IKZdbLvxWobW', 'pinto@gmail.com', 'organizer', NULL, '2024-12-10 19:33:18'),
(8, 'Tawiah', 'Osei', '$2y$10$OjteVCN2qgbRlC5AT5tHge/HIrQZbB6NdQ1KrnM1j1IrFD7RdJ6BG', 'tawiahn@gmail.com', 'player', 5, '2024-12-11 19:48:18'),
(13, 'Sam', 'Kay', '$2y$10$3TapP.wKAeod13deZX2iM.BoFp78jZPqCBehmFMiNv0B07jKdNfSW', 'samkay@gmail.com', 'player', 5, '2024-12-12 21:33:26'),
(14, 'Ibrahim', 'Dasuki', '$2y$10$hD7zPhJu3Vf67wIfHhshoeN9JktokUpX1vham2I4IU.vO/oEsP1Ea', 'ibrahim@gmail.com', 'player', 5, '2024-12-12 21:41:12');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `v_ball_announcements`
--
ALTER TABLE `v_ball_announcements`
  ADD PRIMARY KEY (`announcement_id`),
  ADD KEY `fk_announcement_team` (`team_id`),
  ADD KEY `fk_announcement_creator` (`created_by`);

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
-- AUTO_INCREMENT for table `v_ball_announcements`
--
ALTER TABLE `v_ball_announcements`
  MODIFY `announcement_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `v_ball_matches`
--
ALTER TABLE `v_ball_matches`
  MODIFY `match_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `v_ball_match_strategies`
--
ALTER TABLE `v_ball_match_strategies`
  MODIFY `strategy_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `v_ball_notifications`
--
ALTER TABLE `v_ball_notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT;

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
  MODIFY `team_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `v_ball_tournaments`
--
ALTER TABLE `v_ball_tournaments`
  MODIFY `tournament_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `v_ball_users`
--
ALTER TABLE `v_ball_users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `v_ball_announcements`
--
ALTER TABLE `v_ball_announcements`
  ADD CONSTRAINT `fk_announcement_creator` FOREIGN KEY (`created_by`) REFERENCES `v_ball_users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_announcement_team` FOREIGN KEY (`team_id`) REFERENCES `v_ball_teams` (`team_id`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `v_ball_notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `v_ball_users` (`user_id`);

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
