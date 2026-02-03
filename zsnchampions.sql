-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 51.83.175.128:3306
-- Generation Time: Feb 02, 2026 at 11:13 PM
-- Wersja serwera: 10.11.14-MariaDB-0+deb12u2
-- Wersja PHP: 8.4.17

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `s508_zsnchampions`
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

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `nazwa`, `type`, `started_at`, `ending_at`) VALUES
(12, 'ZAPISY', 'Zapisy', '2025-11-27 11:42:53', '2026-02-12 17:00:00');

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
  `server_ready_until` timestamp NOT NULL,
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

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `content`, `type`, `seen`, `created_at`) VALUES
(7, 31, 'Otrzymałeś zaproszenie do drużyny Koksy 3TR ', 'team-request', 0, '2025-11-28 10:45:55'),
(10, 29, 'Otrzymałeś zaproszenie do drużyny Koksy 3TR ', 'team-request', 0, '2025-12-09 10:24:59'),
(36, 53, 'Otrzymałeś zaproszenie do drużyny Koksy 3TR ', 'team-request', 0, '2026-01-26 19:53:20'),
(40, 70, 'Otrzymałeś zaproszenie do drużyny INTERKA ', 'team-request', 0, '2026-01-31 13:28:43');

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

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `title`, `content`, `posted_at`) VALUES
(1, 'Start zapisów do turnieju', 'Zapisy ruszyły. Stwórz drużynę <a href=\"team.php\">tutaj</a> i zaproś znajomych! Życzymy udanej gry!', '2025-11-24 14:00:00'),
(2, 'Weryfikacja graczy na turnieju', 'Podczas rozgrywek będzie przeprowadzana weryfikacja graczy (w celu sprawdzenia, czy nikt nie oddał konta do gry innemu graczu), która będzie polegać na połączeniu się na czat głosowy na <a href=\"https://discord.gg/VCdBre9fVv\" target=\"_blank\">Discordzie</a> oraz chwilowym udostępnieniu ekranu przez gracza.\nW przypadku wyjscia gracza z meczu (np. z błędu połączenia, crasha) będzie on musiał ponownie przejść weryfikację w trakcie przerwy technicznej.', '2025-12-09 16:15:00');

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
-- Struktura tabeli dla tabeli `teams`
--

CREATE TABLE `teams` (
  `id` int(11) NOT NULL,
  `nazwa` varchar(255) NOT NULL,
  `skrot` varchar(10) NOT NULL,
  `leader_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teams`
--

INSERT INTO `teams` (`id`, `nazwa`, `skrot`, `leader_id`) VALUES
(2, 'NIEPEŁNOSPRYTNE MANDARYNKI', 'NSMD', 1),
(3, 'Koksy 3TR ', 'JD63', 28),
(7, 'Emeritos Boomeros', 'BOOM', 38),
(8, 'Romperek', 'ROMP', 61),
(10, 'Dobre chopoki pacano', 'DCP', 34),
(11, 'INTERKA ', 'INT', 68);

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

--
-- Dumping data for table `team_chat_messages`
--

INSERT INTO `team_chat_messages` (`id`, `message`, `user_id`, `team_id`, `created_at`) VALUES
(1, 'xddd', 1, 2, '2025-12-06 13:47:39'),
(2, 'cvvele', 1, 2, '2025-12-16 10:18:00'),
(3, 'l', 34, 10, '2026-01-14 22:05:37');

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
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `team_id`, `steam_id`, `avatar_url`, `email`, `imie`, `klasa`, `plec`, `updated_at`, `isAdmin`, `isSpectator`, `rezerwa`) VALUES
(1, 'ksencior', '$2y$10$Mr.eKrY.0DKw.ubBgXP8vOc2Fg160gX/TOuCfsiIXgF4iswAb1H4e', 2, '76561198255264853', 'https://avatars.steamstatic.com/bb7eb78ec189a036245ae3a498d603d9d5d393c7_full.jpg', 'szymonmazur@zsngasawa.pl', 'Szymon Mazur', '5TI', 'm', '2025-11-10 15:56:11', 1, 1, NULL),
(5, 'dc', '$2y$10$JgKoTT3cQ7psloGlTruMluKfoTybeiNW4BPaEPJ3ZfYx7g1E9JzCO', 2, '76561198829290451', 'https://avatars.steamstatic.com/451f43df8596e1d22e9a7555ff755912311d322f_full.jpg', 'jakubmaciejewski@zsngasawa.pl', 'Jakub Maciejewski', '5TI', 'm', '2025-12-01 09:42:28', 0, 0, NULL),
(11, 'SyloX5', '$2y$10$fsGJaym8Trk1BHTo06seuuxRVd/n21B6q3.EwBn8324p98.bkQCji', 2, '76561199520285359', 'https://avatars.steamstatic.com/59087c71286c9cac8e086cb774cb028a0f9420a7_full.jpg', 'bartoszszymaniak@zsngasawa.pl', 'Bartosz Szymaniak', '4TI', 'm', '2025-11-10 09:20:19', 1, 0, NULL),
(12, 'Gejmer', '$2y$10$Wjhkd86T33CYsLkWqwT.bOPi8iD.tZUFNiVFYRdUqNi0gt7XL6Xc6', NULL, '76561198450250220', 'https://avatars.steamstatic.com/148ff422f2245ab66abfeabf3f7506861d6b703b_full.jpg', 'wiktorkrupinski@zsngasawa.pl', NULL, NULL, NULL, '2025-11-13 10:03:23', 0, 0, NULL),
(17, 'ZuTy', '$2y$10$CprKYZAHG.FhyVrMD/wwPuRMzgIA9.o1Tvg.96BwYGueglXOKgW1q', 7, '76561198309689581', 'https://avatars.steamstatic.com/31736056320707cb9196de253307bf3a8a845e30_full.jpg', 'mateuszmeszynski@zsngasawa.pl', NULL, NULL, NULL, '2025-11-15 12:12:03', 0, 0, NULL),
(26, 'themandi', '$2y$10$X2T9w219h8MKEvP948EdjuI8TEJVWbMLb2aHgV6TPFKcVlq47NAwa', NULL, NULL, NULL, 'anowaczyk@zsngasawa.pl', NULL, NULL, NULL, '2025-11-24 11:53:02', 1, 0, NULL),
(27, 'olko', '$2y$10$dvbu8QsGtFFw1DKIdA67zeE57il5VOFR5/eeIZadk3SYT6g0SfM1m', NULL, NULL, NULL, 'oliwierkubiak@zsngasawa.pl', NULL, NULL, NULL, '2025-11-27 12:53:10', 0, 0, NULL),
(28, 'pietrass_ku', '$2y$10$MW2aUNOF9MLQmzSKnKIjnej41fQTeJOqsf99v2iHYBkjLaeJv1d4C', 3, '76561199683687427', 'https://avatars.steamstatic.com/34ec3c0753a1cf0848d4ba637256e47710209349_full.jpg', 'jakubpietras@zsngasawa.pl', NULL, NULL, NULL, '2025-11-28 09:46:08', 0, 0, NULL),
(29, 'Pawelzduniak', '$2y$10$dH66nHfhsoCEOpWmCBI9K.WFiCBYlXwM/h7PyuOrjeLKOrbFQKKHW', NULL, '76561199138676455', 'https://avatars.steamstatic.com/0155f2326c651ee3faf766233bcf2449a871b83f_full.jpg', 'jangoc@zsngasawa.pl', 'Jan goc', '3TR', NULL, '2025-11-28 10:43:15', 0, 0, NULL),
(30, 'Tomciu12', '$2y$10$LU8Diy.OwPpApMggvRuFDOWPSoAM6SXCPHtY89FSN6CGP5nXEDQvi', 3, '76561199389242228', 'https://avatars.steamstatic.com/a7ba570c79c4ac424740398d09a1dcebf92302cb_full.jpg', 'tomaszbrej@zsngasawa.pl', NULL, NULL, NULL, '2025-11-28 10:26:10', 0, 0, NULL),
(31, 'Wiciu124', '$2y$10$Wp3FWVG2M7xmdSpBfGPxDuQMGQMLKFQKXh4VtYdA1G11hOMN5lMZ2', NULL, '76561199300705992', 'https://avatars.steamstatic.com/dcb7144e2c8e70f02032b6b6de345805016846e7_full.jpg', 'wojciechrobaczewski@zsngasawa.pl', NULL, NULL, NULL, '2025-11-28 10:41:26', 0, 0, NULL),
(32, 'Padorax', '$2y$10$jYQUeDdpmqXOshJJNdpyCO9Se5YAsEkKrPV75jPlVYriLDRc/Rh32', 2, '76561199101559928', 'https://avatars.steamstatic.com/e02097075c06d783a61f38d3ff7e0deb632230b0_full.jpg', 'karolkitaszewski@zsngasawa.pl', NULL, NULL, NULL, '2025-12-11 09:52:21', 0, 0, NULL),
(33, 'grk17', '$2y$10$3wUI1JjEXxyxNxE7Qw5jGeCjI./z97quKpvTGbxjWUXtLIFVm/sLW', 2, '76561199107089642', 'https://avatars.steamstatic.com/b7992841847b5ec0b8ee8f210eeb0c4279b56e80_full.jpg', 'nikodemmodelski@zsngasawa.pl', NULL, NULL, NULL, '2025-11-28 16:11:07', 0, 0, NULL),
(34, 'BORAZO', '$2y$10$IVGRj4A1n41K3I16aMc7pedKmXRGk6ZmXhxrV744ITdeWRKoRYcDy', 10, '76561199443261678', 'https://avatars.steamstatic.com/cbb44941be7ca567c3352fb4d62fe3ee072f8d85_full.jpg', 'michalchrosniak@zsngasawa.pl', NULL, NULL, NULL, '2025-11-30 15:38:59', 0, 0, NULL),
(35, 'Kamyszek [PL]', '$2y$10$JHaQGm9RmrYS0XgAVvKXmuqv3MaEOafGTylh/Knr6ch5lE1RYX3GK', 10, '76561199237132313', 'https://avatars.steamstatic.com/d7b60383eab95687610f1bdd604d79feeb532f25_full.jpg', 'michalgawron@zsngasawa.pl', NULL, '2TI', 'm', '2026-01-18 19:23:27', 0, 0, NULL),
(36, 'Kuberowski', '$2y$10$DyLzmpTVDP1TWs6gQq4aaeQL4JoxTltF.ZJqDVxJjgOBIj.GUicI6', NULL, NULL, NULL, 'wojciechkubera2010@zsngasawa.pl', NULL, NULL, NULL, '2025-12-01 08:57:15', 0, 0, NULL),
(37, 'kayen', '$2y$10$DHaG9J6w9sfGU4KN28x39uTLpW8DD8iHyAXns9dyeGWCGY/PYcqwy', NULL, NULL, NULL, 'wiktorstrykowski@zsngasawa.pl', NULL, NULL, NULL, '2025-12-01 09:08:57', 0, 0, NULL),
(38, 'Gh0st1095', '$2y$10$rPAAsFtA9gcDl7rb0sKD0OStCc1vMUhcPIz9SxGxYJuC58FVM/Oq.', 7, '76561198140930078', 'https://avatars.steamstatic.com/938e6f25e06bedb5d512d39414b4ee4a8f59ad24_full.jpg', 'kacperhutek@zsngasawa.pl', 'Kacper Hutek', '5TI', 'm', '2025-12-13 20:57:34', 0, 0, NULL),
(39, 'max0lino', '$2y$10$UuOlMIWSxqzSuqfKzVIS5ejhNbPvxHki6d8sYQlBG4vhQrA/w25Gi', NULL, NULL, NULL, 'maksymilianstryganek@zsngasawa.pl', NULL, NULL, NULL, '2025-12-01 10:10:08', 0, 0, NULL),
(40, '_kuberowski_', '$2y$10$tr.wXIhAKWnb7lUK.urWvONRub6seTwVJU7GMLT/sJwFJG3fWrVUK', NULL, NULL, NULL, 'wojciechkubera@zsngasawa.pl', NULL, NULL, NULL, '2025-12-01 14:10:08', 0, 0, NULL),
(41, 'miki', '$2y$10$66HUDSa3inhS4yKEzN/Xpuh5AOTmhrz9K80gWSsu/sQHaZV6vvLvS', NULL, NULL, NULL, 'mikolajchrosniak@zsngasawa.pl', NULL, NULL, NULL, '2025-12-03 19:34:08', 0, 0, NULL),
(42, 'Komentator_1', '$2y$10$WbHBMoKkgO7BS1fLzH17EOZ7LwDDYKelFBSukxNfrwIp/78w19YrG', NULL, NULL, NULL, 'komentator1@zsngasawa.pl', NULL, NULL, NULL, '2025-12-08 08:00:49', 0, 1, NULL),
(43, 'Komentator_2', '$2y$10$2y8tCuykv7qFOT0LARrGM.MF9mg8/Gv8/G/ULSWSKNYZcz8SGjNsu', NULL, NULL, NULL, 'komentator2@zsngasawa.pl', NULL, NULL, NULL, '2025-12-08 08:02:32', 0, 1, NULL),
(44, 'senioR', '$2y$10$KTggCUn7MN68ubO1TGMzNeF5y9rOCZTxT/rTE478IGaxl9QBCBfme', NULL, '76561199244850082', 'https://avatars.steamstatic.com/f7d391ca832a04579d362450b115b4c790d117f8_full.jpg', 'bartoszblazejczak@zsngasawa.pl', 'Bartosz Błażejczak', '4TI', 'm', '2025-12-09 10:19:42', 0, 0, NULL),
(45, 'Mariusz24', '$2y$10$cGio6Ykd7fJVZK/aykm1quZS0vrjXaZGbdWsh9LuEnykhzSO2YJKa', NULL, NULL, NULL, 'mariuszkasprzycki@zsngasawa.pl', NULL, NULL, NULL, '2025-12-09 12:14:06', 0, 0, NULL),
(46, 'Marcel', '$2y$10$VrhQqfF8bo8iaHmrciHzrOQXbYze1RAQ3aOkrecwuh.3e/mW.Rroe', NULL, NULL, NULL, 'marcelmaciejewski@zsngasawa.pl', NULL, NULL, NULL, '2025-12-09 14:16:06', 0, 0, NULL),
(47, 'cvvel', '$2y$10$ij7Yf/5kLgm4LRIFr2sMaODF/h0IrhbcTwF6UtA34v4k6N1S.ySem', 11, '76561199446842097', 'https://avatars.steamstatic.com/bada17ecc6999ed4767de72de1944b7204ef3f73_full.jpg', 'filipmurawski@zsngasawa.pl', NULL, '5TI', 'k', '2026-01-29 14:13:39', 0, 0, NULL),
(48, 'Kotlet', '$2y$10$iEYR1ghjLFyJuIO0EHyYU.klFYkbvbcn7DsqYrGo9QUMhSaCBhp.a', 11, '76561198370964421', 'https://avatars.steamstatic.com/04a2e1557851740534d56b5eb25553ed8ee87883_full.jpg', 'kamilstrzemkowski@zsngasawa.pl', 'Cwel Chuj', '5D', 'k', '2026-01-29 14:13:51', 0, 0, NULL),
(50, 'tescior', '$2y$10$CuI00ujCfTUDjRCqy18rTOC49LMeJCKrey3J0DVC2UjfWVS4b.Anq', NULL, NULL, NULL, 'test@zsngasawa.pl', NULL, NULL, NULL, '2025-12-09 20:02:34', 0, 0, NULL),
(51, 'tesciak2', '$2y$10$7DMyhBaaPUgjbMekCLrbNe6HtjgY2bE9W0MVpbvo/My2vuH9nbnCG', NULL, NULL, NULL, 'test2@zsngasawa.pl', NULL, NULL, NULL, '2025-12-09 20:29:53', 0, 0, NULL),
(52, 'Sala', '$2y$10$K/qVlyQfT9eTvi9z/pU9/OHEoEuEGal1CrTnr8kgv/kd2hAm5mPvC', 7, '76561198981141643', 'https://avatars.steamstatic.com/ceaccde9b493884cba3f223a8af83e9db77d6ab2_full.jpg', 'jakubsala@zsngasawa.pl', NULL, NULL, NULL, '2025-12-10 08:28:38', 0, 0, NULL),
(53, 'Michal Zablocki', '$2y$10$xEwpZVCAObri5WhVZJPDc.e64F9/8h5D3dhQZvd48q6w0Bn1.fK3C', NULL, '76561199878654645', 'https://avatars.steamstatic.com/379edf8b55c7fd2f16eb3500637521af13baeeb8_full.jpg', 'michalzablocki@zsngasawa.pl', NULL, NULL, NULL, '2025-12-11 21:38:56', 0, 0, NULL),
(54, 'Roltroy', '$2y$10$2zmchVQhi32jSec/Gl9zQuIceDKXD/sYU1YqR5IFv277YjNBkWezu', 8, '76561199041280181', 'https://avatars.steamstatic.com/2314a5a8bc0131f69905e3d3461146d0d4dec1a2_full.jpg', 'krzysztofbiskup@zsngasawa.pl', NULL, NULL, NULL, '2025-12-12 21:12:30', 0, 0, NULL),
(55, 'MściwójBiałowąsy', '$2y$10$r3Sb00iImf78.p6xm5DCFOHBTAvvh3c94jqYBPZ7phlslcvqzIpNW', NULL, NULL, NULL, 'mikolajszkulimowski@zsngasawa.pl', NULL, NULL, 'm', '2025-12-13 21:12:43', 0, 0, NULL),
(56, 'nex', '$2y$10$qY9DuqlkXoj7VTtdXTj.yOVm3yz06KiC.9SOBUk18SEM5oa2TFc1C', 7, '76561199205582129', 'https://avatars.steamstatic.com/056fd9e34f1eec7c55792dd3cabf60e7daf1f09d_full.jpg', 'arturwitt@zsngasawa.pl', NULL, NULL, NULL, '2025-12-13 21:03:55', 0, 0, NULL),
(57, 'Funnyplaja', '$2y$10$pVwdqPQZneZQghoyO8wYyurY7SLVu9xyPa6RqDXKH69OtZ.HPgSqG', 7, '76561198868008306', 'https://avatars.steamstatic.com/368b01215e5d4086e46c0f0f93f5837d739ade6f_full.jpg', 'mikolajzalinski@zsngasawa.pl', NULL, NULL, NULL, '2025-12-14 10:23:35', 0, 0, NULL),
(58, 'NITEK', '$2y$10$IIC2xTmm/fq5n.s9O3M0I.OTu/R0J5BMlIaC1SNI9RHj/QdjXVFYK', NULL, NULL, NULL, 'jakubnitka@zsngasawa.pl', NULL, NULL, NULL, '2025-12-14 16:25:00', 0, 0, NULL),
(59, 'qukar', '$2y$10$DhR2eN77kt48lxzyVDj54ODA/bCWHfAGmNoTgdAKgE5CPOb7/DQq.', NULL, NULL, NULL, 'kkaczmarek@zsngasawa.pl', NULL, NULL, NULL, '2025-12-15 14:29:15', 1, 0, NULL),
(60, 'cuttieturtle', '$2y$10$EyLf2OkkFJGj8ZOZvGSjGOk6KQ7TdqjSPOANk4bu2m57CxYpQTXeS', NULL, NULL, NULL, 'oleksiihordiiuk@zsngasawa.pl', NULL, NULL, NULL, '2025-12-16 07:12:29', 0, 0, NULL),
(61, 'Sklamarr', '$2y$10$GnoJwv8eJBwVNCRedX4ADuS1SybV2JrpuAowj0RJVjZI05i/x1QUe', 8, '76561199118747281', 'https://avatars.steamstatic.com/38d366482898d53541ccfd7773af98adc23a083c_full.jpg', 'marcelnowakowski@zsngasawa.pl', NULL, NULL, NULL, '2026-01-07 20:28:28', 0, 0, NULL),
(62, 'Kolbus', '$2y$10$7rGLNaEmhjVeDhI41g5Js.wZ/FzfzLGglmury9Q0FRM8iP8aUQpqi', 8, '76561199113043511', 'https://avatars.steamstatic.com/331c33c07ff3163df10adca31ab65eee303556dc_full.jpg', 'wiktorkorga@zsngasawa.pl', NULL, NULL, NULL, '2026-01-08 20:29:13', 0, 0, NULL),
(63, 'IMPERATOR', '$2y$10$oWhAECVIDK4Z1.0N.1R0eeL06r0qRIne6/Wy9dD1Ug5H7IO0uqz1e', 8, '76561199142126804', 'https://avatars.steamstatic.com/19d92997835889f524cb35f1a9f30cab40b93ced_full.jpg', 'szymonmaciejewski@zsngasawa.pl', NULL, NULL, NULL, '2026-01-08 20:31:05', 0, 0, NULL),
(64, 'Smvrfikk', '$2y$10$.bBThjBNmn9AUF2tO24aueI8YM9UsBZllmtsxkI0aDXEqjVq9GQOK', 8, '76561199246607222', 'https://avatars.steamstatic.com/543bb32f9249e6738288c0d3ac6987ee25983c41_full.jpg', 'wiktorkarmowski@zsngasawa.pl', NULL, NULL, NULL, '2026-01-13 19:22:00', 0, 0, NULL),
(65, 'Mariusz', '$2y$10$0k7QwasdGwK5nHb0DBizZeU5qypIcZP4f8VcN8y781ghm7CG4Btym', 10, '76561199066869951', 'https://avatars.steamstatic.com/7e90bff10859719a0d33287f23e6e37938caff83_full.jpg', 'mariuszkasprzyki@zsngasawa.pl', NULL, NULL, NULL, '2026-01-13 19:28:53', 0, 0, NULL),
(66, 'wedemboyzz', '$2y$10$UaJKD8X.JkN0HUynPbzjB.prHvw46SGPGM7dsUWUQu4ZL8b.qqGr6', 10, '76561199383966220', 'https://avatars.steamstatic.com/09c66275bccbc2ab9fcaea34d7ba804d8f1c120c_full.jpg', 'damiankilian@zsngasawa.pl', 'Damian Kilian', '2LO', 'm', '2026-01-14 10:51:00', 0, 0, NULL),
(67, 'KoKo Czambo', '$2y$10$VssAeEQuM9nDHS48UOevB.bDLuDIqBIE/aEpQhc1YSE8trfkuOriK', 10, '76561199405498834', 'https://avatars.steamstatic.com/f398804ece1c5f7f239f4b145e171fa177a127ac_full.jpg', 'hubertstarczewski@zsngasawa.pl', NULL, NULL, NULL, '2026-01-16 22:47:57', 0, 0, NULL),
(68, 'charlesdubronxoliviera', '$2y$10$FAjbKv17qjfyAKozyB/cUOTimD3pJSuv.aqOPPCtASlwp4o2wBH1y', 11, '76561199023123052', 'https://avatars.steamstatic.com/8092acbad3462f7690caa2dfba216d252b1e7717_full.jpg', 'michalsubstyk@zsngasawa.pl', NULL, NULL, NULL, '2026-01-29 14:02:22', 0, 0, NULL),
(69, 'kiąże', '$2y$10$zHbSN1WbpUkEulLX8sl3OeNMxTMivEquW20fz9ToE.CSt4L.gRRrW', 11, '76561198891160578', 'https://avatars.steamstatic.com/0f6ef8d8acad795128272be77bba897f169b7f47_full.jpg', 'marcinksiazkiewicz@zsngasawa.pl', NULL, NULL, NULL, '2026-01-29 16:50:32', 0, 0, NULL),
(70, 'masku.', '$2y$10$Afd3ADdK4QWoaU0/FTFPwO1F8iec945zyD2h6GSPSIlXn3QGJH6Ty', NULL, '76561199429951048', 'https://avatars.steamstatic.com/63d5e7b0a8e99b9d90865dee53440e3fdca537f9_full.jpg', 'maksymilianszary1@zsngasawa.pl', NULL, NULL, NULL, '2026-01-31 13:20:11', 0, 0, NULL);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(32) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `ready_players`
--
ALTER TABLE `ready_players`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=118;

--
-- AUTO_INCREMENT for table `teams`
--
ALTER TABLE `teams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- AUTO_INCREMENT for table `team_chat_messages`
--
ALTER TABLE `team_chat_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=509;

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
