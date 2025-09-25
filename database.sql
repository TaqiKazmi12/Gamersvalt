-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 27, 2025 at 03:28 AM
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
-- Database: `gamersvalt`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_logs`
--

CREATE TABLE `admin_logs` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `log_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `career_postings`
--

CREATE TABLE `career_postings` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `location` varchar(100) NOT NULL,
  `type` enum('Full-Time','Part-Time','Contract','Internship') NOT NULL,
  `description` text NOT NULL,
  `responsibilities` text DEFAULT NULL,
  `qualifications` text DEFAULT NULL,
  `status` enum('open','closed') DEFAULT 'open',
  `posted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `posted_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `game_id` int(11) DEFAULT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart_items`
--

INSERT INTO `cart_items` (`id`, `user_id`, `game_id`, `added_at`) VALUES
(10, 2, 21, '2025-08-22 01:27:48'),
(11, 3, 22, '2025-08-23 01:27:48');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'Action'),
(3, 'Adventure'),
(8, 'Horror'),
(10, 'Indie'),
(6, 'Puzzle'),
(7, 'Racing'),
(2, 'RPG'),
(4, 'Shooter'),
(5, 'Simulation'),
(9, 'Strategy');

-- --------------------------------------------------------

--
-- Table structure for table `chat_groups`
--

CREATE TABLE `chat_groups` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `owner_id` int(11) NOT NULL,
  `group_avatar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chat_group_members`
--

CREATE TABLE `chat_group_members` (
  `id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role` enum('member','admin') DEFAULT 'member',
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chat_group_messages`
--

CREATE TABLE `chat_group_messages` (
  `id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `message_text` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `shared_game_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message_text` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `shared_game_id` int(11) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `content_pages`
--

CREATE TABLE `content_pages` (
  `id` int(11) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `last_updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `content_pages`
--

INSERT INTO `content_pages` (`id`, `slug`, `title`, `content`, `last_updated_by`, `updated_at`) VALUES
(1, 'about-us', 'About Gamer\'s Valt', '<h1>Our Mission</h1><p>Welcome to Gamer\'s Valt. Our mission is to build the ultimate platform for gamers and developers to connect, create, and share amazing experiences...</p>', NULL, '2025-08-05 11:31:41'),
(2, 'privacy-policy', 'Privacy Policy', '<h1>Privacy Policy for Gamer\'s Valt</h1><p>Your privacy is important to us. This policy outlines how we collect, use, and protect your data...</p>', NULL, '2025-08-05 11:31:41'),
(3, 'terms-of-service', 'Terms of Service', '<h1>Terms of Service</h1><p>By using Gamer\'s Valt, you agree to the following terms and conditions...</p>', NULL, '2025-08-05 11:31:41'),
(4, 'documentation', 'Developer Documentation', '<h1>Welcome, Developers!</h1><p>This documentation will guide you through the process of uploading and managing your games on our platform...</p>', NULL, '2025-08-05 11:31:41'),
(5, 'payout-information', 'Payout Information', '<h1>Developer Payouts</h1><p>Here is how you get paid for the games you sell on Gamer\'s Valt...</p>', NULL, '2025-08-05 11:31:41');

-- --------------------------------------------------------

--
-- Table structure for table `developers`
--

CREATE TABLE `developers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `portfolio_link` varchar(255) DEFAULT NULL,
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `developers`
--

INSERT INTO `developers` (`id`, `user_id`, `bio`, `portfolio_link`, `joined_at`) VALUES
(1, 5, 'CD Projekt Red is a Polish game development studio founded in 2002, known for The Witcher series and Cyberpunk 2077.', 'https://en.cdprojektred.com/', '2025-08-27 01:19:14'),
(2, 6, 'Mojang Studios is a Swedish developer best known for Minecraft.', 'https://www.minecraft.net/', '2025-08-27 01:19:14'),
(3, NULL, 'Rockstar North is the UK studio behind Grand Theft Auto.', 'https://www.rockstargames.com/', '2025-08-27 01:19:14'),
(4, NULL, 'Valve Corporation — developers of Portal 2 and creators of Steam.', 'https://www.valvesoftware.com/', '2025-08-27 01:19:14'),
(5, NULL, 'Supergiant Games — indie studio behind Hades, Bastion and Transistor.', 'https://www.supergiantgames.com/', '2025-08-27 01:19:14'),
(6, NULL, 'Colossal Order — Finnish studio behind Cities: Skylines.', 'https://colossalorder.fi/', '2025-08-27 01:19:14'),
(8, NULL, 'Blizzard Entertainment is an American developer and publisher, known for Warcraft, StarCraft, Diablo, and Overwatch.', 'https://www.blizzard.com/', '2025-08-27 01:24:43'),
(9, NULL, 'Innersloth LLC is a small indie studio based in Washington, USA, creators of Among Us and The Henry Stickmin Collection.', 'https://www.innersloth.com/', '2025-08-27 01:24:43'),
(11, NULL, 'Bethesda Game Studios is an American developer based in Maryland, creators of The Elder Scrolls and Fallout franchises.', 'https://bethesda.net/', '2025-08-27 01:24:43'),
(100, NULL, 'Valve Corporation is an American game developer and digital distribution company, creators of Half-Life, CS:GO, and Steam.', 'https://www.valvesoftware.com/', '2025-08-27 01:25:49'),
(101, NULL, 'Blizzard Entertainment is an American developer and publisher, known for Warcraft, StarCraft, Diablo, and Overwatch.', 'https://www.blizzard.com/', '2025-08-27 01:25:49'),
(102, NULL, 'Innersloth LLC is a small indie studio based in Washington, USA, creators of Among Us and The Henry Stickmin Collection.', 'https://www.innersloth.com/', '2025-08-27 01:25:49'),
(103, NULL, 'Rockstar Games is a major American video game publisher and developer, best known for Grand Theft Auto and Red Dead Redemption series.', 'https://www.rockstargames.com/', '2025-08-27 01:25:49'),
(104, NULL, 'Bethesda Game Studios is an American developer based in Maryland, creators of The Elder Scrolls and Fallout franchises.', 'https://bethesda.net/', '2025-08-27 01:25:49'),
(1000, NULL, 'FromSoftware — Japanese studio, creators of Dark Souls and Elden Ring.', 'https://www.fromsoftware.jp/ww/', '2025-08-27 01:27:47'),
(1001, NULL, 'Larian Studios — Belgian studio, creators of Divinity and Baldur\'s Gate 3.', 'https://larian.com/', '2025-08-27 01:27:47'),
(1002, NULL, 'CD PROJEKT RED — Polish studio, creators of The Witcher series and Cyberpunk 2077.', 'https://en.cdprojektred.com/', '2025-08-27 01:27:47'),
(1003, NULL, 'Rockstar Games — American studio/publisher, creators of Grand Theft Auto and Red Dead Redemption.', 'https://www.rockstargames.com/', '2025-08-27 01:27:47'),
(1004, NULL, 'id Software — American studio, creators of DOOM and Quake.', 'https://www.idsoftware.com/', '2025-08-27 01:27:47'),
(1005, NULL, 'Valve Corporation — American studio and digital platform (Steam); creators of Half-Life and Portal.', 'https://www.valvesoftware.com/', '2025-08-27 01:27:47'),
(1006, NULL, 'Respawn Entertainment — developer of Titanfall and Apex Legends.', 'https://www.respawn.com/', '2025-08-27 01:27:47'),
(1007, NULL, 'Supergiant Games — indie studio, creators of Hades, Bastion and Transistor.', 'https://www.supergiantgames.com/', '2025-08-27 01:27:47'),
(1008, NULL, 'ConcernedApe — solo developer of Stardew Valley (Eric Barone).', 'https://www.stardewvalley.net/', '2025-08-27 01:27:47'),
(1009, NULL, 'Re-Logic — indie studio, creators of Terraria.', 'https://re-logic.com/', '2025-08-27 01:27:47'),
(1010, NULL, 'Team Cherry — indie studio behind Hollow Knight.', 'https://www.teamcherry.com.au/', '2025-08-27 01:27:47'),
(1011, NULL, 'Colossal Order — creators of Cities: Skylines.', 'https://colossalorder.fi/', '2025-08-27 01:27:47'),
(1012, NULL, 'Firaxis Games — creators of Civilization series.', 'https://firaxis.com/', '2025-08-27 01:27:47');

-- --------------------------------------------------------

--
-- Table structure for table `discounts`
--

CREATE TABLE `discounts` (
  `id` int(11) NOT NULL,
  `code` varchar(50) DEFAULT NULL,
  `type` enum('percentage','fixed') DEFAULT NULL,
  `value` decimal(10,2) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `discounts`
--

INSERT INTO `discounts` (`id`, `code`, `type`, `value`, `start_date`, `end_date`, `active`) VALUES
(1, '123', 'percentage', 123.00, '2025-08-15', '2025-08-19', 1);

-- --------------------------------------------------------

--
-- Table structure for table `downloads`
--

CREATE TABLE `downloads` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `game_id` int(11) DEFAULT NULL,
  `download_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `featured_content`
--

CREATE TABLE `featured_content` (
  `id` int(11) NOT NULL,
  `content_type` enum('game','news','developer','category') NOT NULL,
  `content_id` int(11) NOT NULL,
  `custom_title` varchar(255) DEFAULT NULL,
  `custom_description` text DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `friendships`
--

CREATE TABLE `friendships` (
  `id` int(11) NOT NULL,
  `user_one_id` int(11) NOT NULL,
  `user_two_id` int(11) NOT NULL,
  `status` enum('pending','accepted','blocked') NOT NULL,
  `action_user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `games`
--

CREATE TABLE `games` (
  `id` int(11) NOT NULL,
  `title` varchar(150) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `developer_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `status` enum('published','draft','banned') DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `discount_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `games`
--

INSERT INTO `games` (`id`, `title`, `description`, `price`, `developer_id`, `category_id`, `thumbnail`, `file_path`, `status`, `created_at`, `discount_id`) VALUES
(21, 'ELDEN RING', 'Open-world action RPG from FromSoftware with deep combat and vast exploration.', 59.99, 1000, 2, 'https://cdn.cloudflare.steamstatic.com/steam/apps/1245620/header.jpg', NULL, 'published', '2025-08-27 01:27:47', NULL),
(22, 'Baldur\'s Gate 3', 'Narrative heavy-party RPG from Larian Studios based on D&D rules with turn-based combat.', 59.99, 1001, 2, 'https://cdn.cloudflare.steamstatic.com/steam/apps/1086940/header.jpg', NULL, 'published', '2025-08-27 01:27:47', NULL),
(23, 'The Witcher 3: Wild Hunt', 'Open-world action RPG following Geralt of Rivia with a massive story-driven world.', 39.99, 1002, 2, 'https://cdn.cloudflare.steamstatic.com/steam/apps/292030/header.jpg', NULL, 'published', '2025-08-27 01:27:47', NULL),
(24, 'Cyberpunk 2077', 'Open-world futuristic RPG in Night City with branching quests and cyberware.', 59.99, 1002, 2, 'https://cdn.cloudflare.steamstatic.com/steam/apps/1091500/header.jpg', NULL, 'published', '2025-08-27 01:27:47', NULL),
(25, 'Grand Theft Auto V', 'Open-world action-adventure set in Los Santos with three playable protagonists.', 29.99, 1003, 1, 'https://cdn.cloudflare.steamstatic.com/steam/apps/271590/header.jpg', NULL, 'published', '2025-08-27 01:27:47', NULL),
(26, 'Red Dead Redemption 2', 'Epic Western action-adventure with a cinematic story and open frontier.', 49.99, 1003, 1, 'https://cdn.cloudflare.steamstatic.com/steam/apps/1174180/header.jpg', NULL, 'published', '2025-08-27 01:27:47', NULL),
(27, 'DOOM Eternal', 'Fast, aggressive FPS; rip and tear through demons with high-speed combat.', 39.99, 1004, 4, 'https://cdn.cloudflare.steamstatic.com/steam/apps/782330/header.jpg', NULL, 'published', '2025-08-27 01:27:47', NULL),
(28, 'Counter-Strike: Global Offensive', 'Competitive team-based FPS and cornerstone of esports (bomb/defuse matches).', 0.00, 1005, 4, 'https://cdn.cloudflare.steamstatic.com/steam/apps/730/header.jpg', NULL, 'published', '2025-08-27 01:27:47', NULL),
(29, 'Apex Legends', 'Free-to-play hero battle royale with squads and unique abilities.', 0.00, 1006, 4, 'https://cdn.cloudflare.steamstatic.com/steam/apps/1172470/header.jpg', NULL, 'published', '2025-08-27 01:27:47', NULL),
(30, 'Hades', 'Action roguelike about Zagreus escaping the Underworld with mythic boons.', 24.99, 1007, 10, 'https://cdn.cloudflare.steamstatic.com/steam/apps/1145360/header.jpg', NULL, 'published', '2025-08-27 01:27:47', NULL),
(31, 'Stardew Valley', 'Cozy farming-life sim: grow crops, raise animals, mine and build relationships.', 14.99, 1008, 10, 'https://cdn.cloudflare.steamstatic.com/steam/apps/413150/header.jpg', NULL, 'published', '2025-08-27 01:27:47', NULL),
(32, 'Terraria', '2D sandbox adventure with crafting, bosses, and exploration.', 9.99, 1009, 10, 'https://cdn.cloudflare.steamstatic.com/steam/apps/105600/header.jpg', NULL, 'published', '2025-08-27 01:27:47', NULL),
(33, 'Hollow Knight', 'Atmospheric metroidvania with tight combat and hand-drawn art.', 14.99, 1010, 10, 'https://cdn.cloudflare.steamstatic.com/steam/apps/367520/header.jpg', NULL, 'published', '2025-08-27 01:27:47', NULL),
(34, 'Cities: Skylines', 'Deep city-building sim focusing on traffic, zoning and services.', 39.99, 1011, 5, 'https://cdn.cloudflare.steamstatic.com/steam/apps/255710/header.jpg', NULL, 'published', '2025-08-27 01:27:47', NULL),
(35, 'Portal 2', 'First-person puzzle-platformer with portals, puzzles and sharp humor.', 9.99, 1005, 6, 'https://cdn.cloudflare.steamstatic.com/steam/apps/620/header.jpg', NULL, 'published', '2025-08-27 01:27:47', NULL),
(36, 'Sid Meier\'s Civilization VI', 'Turn-based 4X strategy guiding a civilization from ancient to modern times.', 59.99, 1012, 9, 'https://cdn.cloudflare.steamstatic.com/steam/apps/289070/header.jpg', NULL, 'published', '2025-08-27 01:27:47', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `game_images`
--

CREATE TABLE `game_images` (
  `id` int(11) NOT NULL,
  `game_id` int(11) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `game_images`
--

INSERT INTO `game_images` (`id`, `game_id`, `image_url`) VALUES
(46, 21, 'https://cdn.cloudflare.steamstatic.com/steam/apps/1245620/header.jpg'),
(47, 21, 'https://cdn.cloudflare.steamstatic.com/steam/apps/1245620/capsule_616x353.jpg'),
(48, 22, 'https://cdn.cloudflare.steamstatic.com/steam/apps/1086940/header.jpg'),
(49, 22, 'https://cdn.cloudflare.steamstatic.com/steam/apps/1086940/capsule_616x353.jpg'),
(50, 23, 'https://cdn.cloudflare.steamstatic.com/steam/apps/292030/header.jpg'),
(51, 23, 'https://cdn.cloudflare.steamstatic.com/steam/apps/292030/capsule_616x353.jpg'),
(52, 24, 'https://cdn.cloudflare.steamstatic.com/steam/apps/1091500/header.jpg'),
(53, 24, 'https://cdn.cloudflare.steamstatic.com/steam/apps/1091500/capsule_616x353.jpg'),
(54, 25, 'https://cdn.cloudflare.steamstatic.com/steam/apps/271590/header.jpg'),
(55, 25, 'https://cdn.cloudflare.steamstatic.com/steam/apps/271590/capsule_616x353.jpg'),
(56, 26, 'https://cdn.cloudflare.steamstatic.com/steam/apps/1174180/header.jpg'),
(57, 26, 'https://cdn.cloudflare.steamstatic.com/steam/apps/1174180/capsule_616x353.jpg'),
(58, 27, 'https://cdn.cloudflare.steamstatic.com/steam/apps/782330/header.jpg'),
(59, 27, 'https://cdn.cloudflare.steamstatic.com/steam/apps/782330/capsule_616x353.jpg'),
(60, 28, 'https://cdn.cloudflare.steamstatic.com/steam/apps/730/header.jpg'),
(61, 28, 'https://cdn.cloudflare.steamstatic.com/steam/apps/730/capsule_616x353.jpg'),
(62, 29, 'https://cdn.cloudflare.steamstatic.com/steam/apps/1172470/header.jpg'),
(63, 29, 'https://cdn.cloudflare.steamstatic.com/steam/apps/1172470/capsule_616x353.jpg'),
(64, 30, 'https://cdn.cloudflare.steamstatic.com/steam/apps/1145360/header.jpg'),
(65, 30, 'https://cdn.cloudflare.steamstatic.com/steam/apps/1145360/capsule_616x353.jpg'),
(66, 31, 'https://cdn.cloudflare.steamstatic.com/steam/apps/413150/header.jpg'),
(67, 31, 'https://cdn.cloudflare.steamstatic.com/steam/apps/413150/capsule_616x353.jpg'),
(68, 32, 'https://cdn.cloudflare.steamstatic.com/steam/apps/105600/header.jpg'),
(69, 32, 'https://cdn.cloudflare.steamstatic.com/steam/apps/105600/capsule_616x353.jpg'),
(70, 33, 'https://cdn.cloudflare.steamstatic.com/steam/apps/367520/header.jpg'),
(71, 33, 'https://cdn.cloudflare.steamstatic.com/steam/apps/367520/capsule_616x353.jpg'),
(72, 34, 'https://cdn.cloudflare.steamstatic.com/steam/apps/255710/header.jpg'),
(73, 34, 'https://cdn.cloudflare.steamstatic.com/steam/apps/255710/capsule_616x353.jpg'),
(74, 35, 'https://cdn.cloudflare.steamstatic.com/steam/apps/620/header.jpg'),
(75, 35, 'https://cdn.cloudflare.steamstatic.com/steam/apps/620/capsule_616x353.jpg'),
(76, 36, 'https://cdn.cloudflare.steamstatic.com/steam/apps/289070/header.jpg'),
(77, 36, 'https://cdn.cloudflare.steamstatic.com/steam/apps/289070/capsule_616x353.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_subscriptions`
--

CREATE TABLE `newsletter_subscriptions` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subscribed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `news_articles`
--

CREATE TABLE `news_articles` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `excerpt` text DEFAULT NULL,
  `content` longtext NOT NULL,
  `thumbnail_image` varchar(255) DEFAULT NULL,
  `author_id` int(11) NOT NULL,
  `status` enum('draft','published') DEFAULT 'draft',
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `related_id` int(11) DEFAULT NULL,
  `message` varchar(255) NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payouts`
--

CREATE TABLE `payouts` (
  `id` int(11) NOT NULL,
  `developer_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payout_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchases`
--

CREATE TABLE `purchases` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `game_id` int(11) DEFAULT NULL,
  `price_paid` decimal(10,2) DEFAULT NULL,
  `purchase_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `payout_id` int(11) DEFAULT NULL,
  `playtime_hours` int(11) NOT NULL DEFAULT 0,
  `last_played` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchases`
--

INSERT INTO `purchases` (`id`, `user_id`, `game_id`, `price_paid`, `purchase_date`, `payout_id`, `playtime_hours`, `last_played`) VALUES
(10, 2, 25, NULL, '2025-08-26 01:27:48', NULL, 0, NULL),
(11, 3, 26, NULL, '2025-08-27 01:27:48', NULL, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL,
  `report_type` varchar(100) NOT NULL,
  `comments` text DEFAULT NULL,
  `is_resolved` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `game_id` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL,
  `review_text` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `user_id`, `game_id`, `rating`, `review_text`, `created_at`) VALUES
(16, 2, 21, 5, 'Brutal, beautiful and enormous — FromSoftware at its best.', '2025-08-07 01:27:47'),
(17, 3, 22, 5, 'Masterclass RPG with superb writing and tactical combat.', '2025-08-09 01:27:47'),
(18, 4, 23, 5, 'A sprawling epic with memorable quests and characters.', '2025-08-11 01:27:47'),
(19, 5, 24, 4, 'Night City is gorgeous; many bugs were fixed by recent patches.', '2025-08-13 01:27:47'),
(20, 2, 25, 5, 'Still a fantastic open-world playground.', '2025-08-15 01:27:47'),
(21, 3, 26, 5, 'Stunning worldbuilding and narrative.', '2025-08-17 01:27:47'),
(22, 4, 27, 5, 'Fast, fluid combat — great sequel.', '2025-08-18 01:27:47'),
(23, 5, 28, 4, 'Competitive and precise; great esports scene.', '2025-08-19 01:27:47'),
(24, 2, 30, 5, 'Perfect roguelike loop and storytelling.', '2025-08-20 01:27:47'),
(25, 3, 31, 5, 'Cozy and endlessly replayable.', '2025-08-21 01:27:47');

-- --------------------------------------------------------

--
-- Table structure for table `support_tickets`
--

CREATE TABLE `support_tickets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `category` enum('Billing','Technical','Account','Gameplay','General') NOT NULL,
  `status` enum('Open','Answered','In-Progress','Closed') DEFAULT 'Open',
  `priority` enum('Low','Medium','High','Urgent') DEFAULT 'Medium',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `support_ticket_replies`
--

CREATE TABLE `support_ticket_replies` (
  `id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('user','developer','admin') DEFAULT 'user',
  `is_online` tinyint(1) NOT NULL DEFAULT 0,
  `last_seen` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `is_online`, `last_seen`, `created_at`) VALUES
(1, 'Admin User', 'admin@example.com', '$2y$10$E.MvqdJ2x8V4f9i/Y.5ePu...', 'admin', 0, NULL, '2025-08-27 01:15:11'),
(2, 'Alice Gamer', 'alice@example.com', '$2y$10$E.MvqdJ2x8V4f9i/Y.5ePu...', 'user', 0, NULL, '2025-08-27 01:15:11'),
(3, 'Bob Builder', 'bob@example.com', '$2y$10$E.MvqdJ2x8V4f9i/Y.5ePu...', 'user', 0, NULL, '2025-08-27 01:15:11'),
(4, 'Carol Creator', 'carol@example.com', '$2y$10$E.MvqdJ2x8V4f9i/Y.5ePu...', 'user', 0, NULL, '2025-08-27 01:15:11'),
(5, 'Dave Developer', 'dave@devstudio.com', '$2y$10$E.MvqdJ2x8V4f9i/Y.5ePu...', 'user', 0, NULL, '2025-08-27 01:15:11'),
(6, 'Eve Engineer', 'eve@devstudio.com', '$2y$10$E.MvqdJ2x8V4f9i/Y.5ePu...', 'user', 0, NULL, '2025-08-27 01:15:11');

-- --------------------------------------------------------

--
-- Table structure for table `user_payment_methods`
--

CREATE TABLE `user_payment_methods` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `card_type` enum('visa','mastercard','amex') NOT NULL,
  `cardholder_name` varchar(255) NOT NULL,
  `card_number_last4` varchar(4) NOT NULL,
  `expiry_month` varchar(2) NOT NULL,
  `expiry_year` varchar(4) NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wishlists`
--

CREATE TABLE `wishlists` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `game_id` int(11) DEFAULT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wishlists`
--

INSERT INTO `wishlists` (`id`, `user_id`, `game_id`, `added_at`) VALUES
(9, 2, 23, '2025-08-24 01:27:48'),
(10, 4, 24, '2025-08-25 01:27:48');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `career_postings`
--
ALTER TABLE `career_postings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `posted_by` (`posted_by`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `game_id` (`game_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `chat_groups`
--
ALTER TABLE `chat_groups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `owner_id` (`owner_id`);

--
-- Indexes for table `chat_group_members`
--
ALTER TABLE `chat_group_members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_member` (`group_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `chat_group_messages`
--
ALTER TABLE `chat_group_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `group_id` (`group_id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `shared_game_id` (`shared_game_id`);

--
-- Indexes for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`),
  ADD KEY `shared_game_id` (`shared_game_id`);

--
-- Indexes for table `content_pages`
--
ALTER TABLE `content_pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `last_updated_by` (`last_updated_by`);

--
-- Indexes for table `developers`
--
ALTER TABLE `developers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `discounts`
--
ALTER TABLE `discounts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `downloads`
--
ALTER TABLE `downloads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `game_id` (`game_id`);

--
-- Indexes for table `featured_content`
--
ALTER TABLE `featured_content`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `friendships`
--
ALTER TABLE `friendships`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_friendship` (`user_one_id`,`user_two_id`),
  ADD KEY `user_two_id` (`user_two_id`),
  ADD KEY `action_user_id` (`action_user_id`);

--
-- Indexes for table `games`
--
ALTER TABLE `games`
  ADD PRIMARY KEY (`id`),
  ADD KEY `developer_id` (`developer_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `discount_id` (`discount_id`);

--
-- Indexes for table `game_images`
--
ALTER TABLE `game_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `game_id` (`game_id`);

--
-- Indexes for table `newsletter_subscriptions`
--
ALTER TABLE `newsletter_subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `news_articles`
--
ALTER TABLE `news_articles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `author_id` (`author_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `payouts`
--
ALTER TABLE `payouts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `developer_id` (`developer_id`);

--
-- Indexes for table `purchases`
--
ALTER TABLE `purchases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `game_id` (`game_id`),
  ADD KEY `payout_id` (`payout_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `game_id` (`game_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `game_id` (`game_id`);

--
-- Indexes for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `support_ticket_replies`
--
ALTER TABLE `support_ticket_replies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_id` (`ticket_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_payment_methods`
--
ALTER TABLE `user_payment_methods`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `wishlists`
--
ALTER TABLE `wishlists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `game_id` (`game_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_logs`
--
ALTER TABLE `admin_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `career_postings`
--
ALTER TABLE `career_postings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `chat_groups`
--
ALTER TABLE `chat_groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chat_group_members`
--
ALTER TABLE `chat_group_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chat_group_messages`
--
ALTER TABLE `chat_group_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `content_pages`
--
ALTER TABLE `content_pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `developers`
--
ALTER TABLE `developers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1013;

--
-- AUTO_INCREMENT for table `discounts`
--
ALTER TABLE `discounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `downloads`
--
ALTER TABLE `downloads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `featured_content`
--
ALTER TABLE `featured_content`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `friendships`
--
ALTER TABLE `friendships`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `games`
--
ALTER TABLE `games`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `game_images`
--
ALTER TABLE `game_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `newsletter_subscriptions`
--
ALTER TABLE `newsletter_subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `news_articles`
--
ALTER TABLE `news_articles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `payouts`
--
ALTER TABLE `payouts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `purchases`
--
ALTER TABLE `purchases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `support_tickets`
--
ALTER TABLE `support_tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `support_ticket_replies`
--
ALTER TABLE `support_ticket_replies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `user_payment_methods`
--
ALTER TABLE `user_payment_methods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `wishlists`
--
ALTER TABLE `wishlists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD CONSTRAINT `admin_logs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `career_postings`
--
ALTER TABLE `career_postings`
  ADD CONSTRAINT `career_postings_ibfk_1` FOREIGN KEY (`posted_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `chat_groups`
--
ALTER TABLE `chat_groups`
  ADD CONSTRAINT `chat_groups_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `chat_group_members`
--
ALTER TABLE `chat_group_members`
  ADD CONSTRAINT `chat_group_members_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `chat_groups` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chat_group_members_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `chat_group_messages`
--
ALTER TABLE `chat_group_messages`
  ADD CONSTRAINT `chat_group_messages_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `chat_groups` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chat_group_messages_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chat_group_messages_ibfk_3` FOREIGN KEY (`shared_game_id`) REFERENCES `games` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD CONSTRAINT `chat_messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chat_messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chat_messages_ibfk_3` FOREIGN KEY (`shared_game_id`) REFERENCES `games` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `content_pages`
--
ALTER TABLE `content_pages`
  ADD CONSTRAINT `content_pages_ibfk_1` FOREIGN KEY (`last_updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `developers`
--
ALTER TABLE `developers`
  ADD CONSTRAINT `developers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `downloads`
--
ALTER TABLE `downloads`
  ADD CONSTRAINT `downloads_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `downloads_ibfk_2` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `friendships`
--
ALTER TABLE `friendships`
  ADD CONSTRAINT `friendships_ibfk_1` FOREIGN KEY (`user_one_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `friendships_ibfk_2` FOREIGN KEY (`user_two_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `friendships_ibfk_3` FOREIGN KEY (`action_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `games`
--
ALTER TABLE `games`
  ADD CONSTRAINT `games_ibfk_1` FOREIGN KEY (`developer_id`) REFERENCES `developers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `games_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `games_ibfk_3` FOREIGN KEY (`discount_id`) REFERENCES `discounts` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `game_images`
--
ALTER TABLE `game_images`
  ADD CONSTRAINT `game_images_ibfk_1` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `news_articles`
--
ALTER TABLE `news_articles`
  ADD CONSTRAINT `news_articles_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payouts`
--
ALTER TABLE `payouts`
  ADD CONSTRAINT `payouts_ibfk_1` FOREIGN KEY (`developer_id`) REFERENCES `developers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `purchases`
--
ALTER TABLE `purchases`
  ADD CONSTRAINT `purchases_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchases_ibfk_2` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchases_ibfk_3` FOREIGN KEY (`payout_id`) REFERENCES `payouts` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reports_ibfk_2` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD CONSTRAINT `support_tickets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `support_ticket_replies`
--
ALTER TABLE `support_ticket_replies`
  ADD CONSTRAINT `support_ticket_replies_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `support_tickets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `support_ticket_replies_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_payment_methods`
--
ALTER TABLE `user_payment_methods`
  ADD CONSTRAINT `user_payment_methods_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wishlists`
--
ALTER TABLE `wishlists`
  ADD CONSTRAINT `wishlists_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlists_ibfk_2` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
