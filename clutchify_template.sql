-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 05, 2026 at 07:08 PM
-- Wersja serwera: 10.4.32-MariaDB
-- Wersja PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tournament_app_template`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `demos`
--

CREATE TABLE `demos` (
  `match_id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL,
  `path` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `nazwa` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `started_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `ending_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `finished_games`
--

CREATE TABLE `finished_games` (
  `match_id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL,
  `team1_id` int(11) NOT NULL,
  `team2_id` int(11) NOT NULL,
  `map_name` varchar(255) NOT NULL,
  `team1_score` int(11) NOT NULL,
  `team2_score` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `finished_stats`
--

CREATE TABLE `finished_stats` (
  `match_id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL,
  `kills` int(11) NOT NULL,
  `deaths` int(11) NOT NULL,
  `assists` int(11) NOT NULL,
  `headshots` int(11) NOT NULL,
  `mvps` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `games`
--

CREATE TABLE `games` (
  `id` int(11) NOT NULL,
  `lobby_id` int(11) NOT NULL,
  `match_id` int(11) NOT NULL,
  `team1` int(11) NOT NULL,
  `team2` int(11) NOT NULL,
  `team1_score` int(11) NOT NULL DEFAULT 0,
  `team2_score` int(11) NOT NULL DEFAULT 0,
  `server_ip` varchar(255) NOT NULL,
  `current_map` varchar(255) NOT NULL,
  `current_round` int(11) NOT NULL DEFAULT 0,
  `server_ready_until` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('waiting','playing','finished','walkover') NOT NULL DEFAULT 'waiting'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `games_maps`
--

CREATE TABLE `games_maps` (
  `lobby_id` int(11) NOT NULL,
  `order_num` int(11) NOT NULL,
  `map` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `game_players`
--

CREATE TABLE `game_players` (
  `game_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL,
  `kills` int(11) NOT NULL DEFAULT 0,
  `deaths` int(11) NOT NULL DEFAULT 0,
  `assists` int(11) NOT NULL DEFAULT 0,
  `headshots` int(11) NOT NULL DEFAULT 0,
  `mvps` int(11) NOT NULL DEFAULT 0,
  `joined_server` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `lobbies`
--

CREATE TABLE `lobbies` (
  `id` int(11) NOT NULL,
  `mecz_id` int(11) NOT NULL,
  `team1_id` int(11) NOT NULL,
  `team2_id` int(11) NOT NULL,
  `current_stage` int(11) DEFAULT NULL,
  `last_action_time` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `map_veto`
--

CREATE TABLE `map_veto` (
  `id` int(11) NOT NULL,
  `lobby_id` int(11) NOT NULL,
  `stage` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `team` varchar(255) NOT NULL,
  `map_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `matchzy_stats_maps`
--

CREATE TABLE `matchzy_stats_maps` (
  `matchid` int(11) NOT NULL,
  `mapnumber` tinyint(3) UNSIGNED NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime DEFAULT NULL,
  `winner` varchar(16) NOT NULL DEFAULT '',
  `mapname` varchar(64) NOT NULL DEFAULT '',
  `team1_score` int(11) NOT NULL DEFAULT 0,
  `team2_score` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `matchzy_stats_matches`
--

CREATE TABLE `matchzy_stats_matches` (
  `matchid` int(11) NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime DEFAULT NULL,
  `winner` varchar(255) NOT NULL DEFAULT '',
  `series_type` varchar(255) NOT NULL DEFAULT '',
  `team1_name` varchar(255) NOT NULL DEFAULT '',
  `team1_score` int(11) NOT NULL DEFAULT 0,
  `team2_name` varchar(255) NOT NULL DEFAULT '',
  `team2_score` int(11) NOT NULL DEFAULT 0,
  `server_ip` varchar(255) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `matchzy_stats_players`
--

CREATE TABLE `matchzy_stats_players` (
  `matchid` int(11) NOT NULL,
  `mapnumber` tinyint(3) UNSIGNED NOT NULL,
  `steamid64` bigint(20) NOT NULL,
  `team` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL,
  `kills` int(11) NOT NULL,
  `deaths` int(11) NOT NULL,
  `damage` int(11) NOT NULL,
  `assists` int(11) NOT NULL,
  `enemy5ks` int(11) NOT NULL,
  `enemy4ks` int(11) NOT NULL,
  `enemy3ks` int(11) NOT NULL,
  `enemy2ks` int(11) NOT NULL,
  `utility_count` int(11) NOT NULL,
  `utility_damage` int(11) NOT NULL,
  `utility_successes` int(11) NOT NULL,
  `utility_enemies` int(11) NOT NULL,
  `flash_count` int(11) NOT NULL,
  `flash_successes` int(11) NOT NULL,
  `health_points_removed_total` int(11) NOT NULL,
  `health_points_dealt_total` int(11) NOT NULL,
  `shots_fired_total` int(11) NOT NULL,
  `shots_on_target_total` int(11) NOT NULL,
  `v1_count` int(11) NOT NULL,
  `v1_wins` int(11) NOT NULL,
  `v2_count` int(11) NOT NULL,
  `v2_wins` int(11) NOT NULL,
  `entry_count` int(11) NOT NULL,
  `entry_wins` int(11) NOT NULL,
  `equipment_value` int(11) NOT NULL,
  `money_saved` int(11) NOT NULL,
  `kill_reward` int(11) NOT NULL,
  `live_time` int(11) NOT NULL,
  `head_shot_kills` int(11) NOT NULL,
  `cash_earned` int(11) NOT NULL,
  `enemies_flashed` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `mecze`
--

CREATE TABLE `mecze` (
  `id` int(11) NOT NULL,
  `team1` int(11) DEFAULT NULL,
  `team2` int(11) DEFAULT NULL,
  `team1_wins` int(11) NOT NULL DEFAULT 0,
  `team2_wins` int(11) NOT NULL DEFAULT 0,
  `termin` datetime NOT NULL,
  `round` int(11) NOT NULL,
  `match_number` int(11) NOT NULL,
  `winner_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `type` varchar(50) NOT NULL,
  `seen` tinyint(4) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `posts`
--

CREATE TABLE `posts` (
  `id` int(32) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `posted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `ranking`
--

CREATE TABLE `ranking` (
  `user_id` int(11) NOT NULL,
  `global_kills` int(11) NOT NULL,
  `global_deaths` int(11) NOT NULL,
  `global_assists` int(11) NOT NULL,
  `global_headshots` int(11) NOT NULL,
  `global_mvps` int(11) NOT NULL,
  `global_kd` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `ready_players`
--

CREATE TABLE `ready_players` (
  `id` int(11) NOT NULL,
  `mecz_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `category` enum('general','features','branding','limits') DEFAULT 'general'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `teams`
--

CREATE TABLE `teams` (
  `id` int(11) NOT NULL,
  `nazwa` varchar(255) NOT NULL,
  `skrot` varchar(10) NOT NULL,
  `leader_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `team_chat_messages`
--

CREATE TABLE `team_chat_messages` (
  `id` int(11) NOT NULL,
  `message` text NOT NULL,
  `user_id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password_hash` text NOT NULL,
  `team_id` int(11) DEFAULT NULL,
  `steam_id` varchar(255) DEFAULT NULL,
  `avatar_url` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `imie` varchar(255) DEFAULT NULL,
  `klasa` varchar(3) DEFAULT NULL,
  `plec` varchar(1) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `isAdmin` tinyint(1) NOT NULL DEFAULT 0,
  `isSpectator` tinyint(1) NOT NULL DEFAULT 0,
  `rezerwa` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indeksy dla zrzutów tabel
--

--
-- Indeksy dla tabeli `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `games`
--
ALTER TABLE `games`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `lobbies`
--
ALTER TABLE `lobbies`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `map_veto`
--
ALTER TABLE `map_veto`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `matchzy_stats_maps`
--
ALTER TABLE `matchzy_stats_maps`
  ADD PRIMARY KEY (`matchid`,`mapnumber`),
  ADD KEY `mapnumber_index` (`mapnumber`);

--
-- Indeksy dla tabeli `matchzy_stats_matches`
--
ALTER TABLE `matchzy_stats_matches`
  ADD PRIMARY KEY (`matchid`);

--
-- Indeksy dla tabeli `matchzy_stats_players`
--
ALTER TABLE `matchzy_stats_players`
  ADD PRIMARY KEY (`matchid`,`mapnumber`,`steamid64`),
  ADD KEY `mapnumber` (`mapnumber`);

--
-- Indeksy dla tabeli `mecze`
--
ALTER TABLE `mecze`
  ADD PRIMARY KEY (`id`),
  ADD KEY `team1` (`team1`),
  ADD KEY `team2` (`team2`);

--
-- Indeksy dla tabeli `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `ranking`
--
ALTER TABLE `ranking`
  ADD PRIMARY KEY (`user_id`);

--
-- Indeksy dla tabeli `ready_players`
--
ALTER TABLE `ready_players`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indeksy dla tabeli `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `team_chat_messages`
--
ALTER TABLE `team_chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `team_id` (`team_id`);

--
-- Indeksy dla tabeli `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `games`
--
ALTER TABLE `games`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `map_veto`
--
ALTER TABLE `map_veto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `matchzy_stats_matches`
--
ALTER TABLE `matchzy_stats_matches`
  MODIFY `matchid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mecze`
--
ALTER TABLE `mecze`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(32) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ready_players`
--
ALTER TABLE `ready_players`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=129;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `teams`
--
ALTER TABLE `teams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `team_chat_messages`
--
ALTER TABLE `team_chat_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `matchzy_stats_maps`
--
ALTER TABLE `matchzy_stats_maps`
  ADD CONSTRAINT `matchzy_stats_maps_matchid` FOREIGN KEY (`matchid`) REFERENCES `matchzy_stats_matches` (`matchid`);

--
-- Constraints for table `matchzy_stats_players`
--
ALTER TABLE `matchzy_stats_players`
  ADD CONSTRAINT `matchzy_stats_players_ibfk_1` FOREIGN KEY (`matchid`) REFERENCES `matchzy_stats_matches` (`matchid`),
  ADD CONSTRAINT `matchzy_stats_players_ibfk_2` FOREIGN KEY (`mapnumber`) REFERENCES `matchzy_stats_maps` (`mapnumber`);

--
-- Constraints for table `mecze`
--
ALTER TABLE `mecze`
  ADD CONSTRAINT `mecze_ibfk_1` FOREIGN KEY (`team1`) REFERENCES `teams` (`id`),
  ADD CONSTRAINT `mecze_ibfk_2` FOREIGN KEY (`team2`) REFERENCES `teams` (`id`);

--
-- Constraints for table `team_chat_messages`
--
ALTER TABLE `team_chat_messages`
  ADD CONSTRAINT `team_chat_messages_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `team_chat_messages_ibfk_2` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
