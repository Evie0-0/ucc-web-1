-- phpMyAdmin SQL Dump
-- version 5.2.3deb1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 06, 2026 at 04:07 AM
-- Server version: 11.8.6-MariaDB-5ubuntu0.1 from Ubuntu
-- PHP Version: 8.5.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ucc`
--

-- --------------------------------------------------------

--
-- Table structure for table `bor`
--

CREATE TABLE `bor` (
  `id` int(11) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `middle_initial` varchar(5) DEFAULT NULL,
  `bio` longtext DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('active','hidden') NOT NULL DEFAULT 'active',
  `author_id` int(11) NOT NULL,
  `editor_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `active`) VALUES
(1, 'University News', 'university-news', 1),
(2, 'Student Life', 'student-life', 1),
(3, 'Community and Extension Services', 'community-and-extension-services', 1),
(4, 'Admission', 'admission', 1),
(5, 'Alumni', 'alumni', 1),
(6, 'Careers', 'careers', 1),
(7, 'E-Services', 'e-services', 0);

-- --------------------------------------------------------

--
-- Table structure for table `college`
--

CREATE TABLE `college` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `banner_image` varchar(255) NOT NULL,
  `dean` varchar(255) NOT NULL,
  `dean_image` varchar(255) NOT NULL,
  `vision` longtext NOT NULL,
  `mission` longtext NOT NULL,
  `status` enum('active','hidden') NOT NULL DEFAULT 'active',
  `author_id` int(11) NOT NULL,
  `editor_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `eserv`
--

CREATE TABLE `eserv` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL,
  `description` longtext NOT NULL,
  `url` varchar(2048) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `author_id` int(11) NOT NULL,
  `editor_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `eserv`
--

INSERT INTO `eserv` (`id`, `name`, `category`, `description`, `url`, `logo`, `author_id`, `editor_id`, `created_at`, `updated_at`, `active`) VALUES
(1, 'hello world', 'helloi', 'hello', 'https://www.youtube.com/watch?v=spRerxsOLXk&list=RDspRerxsOLXk&start_radio=1', '15966e7ce67b1339bd4b7087cbd1182a.webp', 1, 1, '2026-10-06 01:18:30', '2026-10-06 02:56:46', 1),
(2, 'hello world', 'hello world', 'hello world', 'https://www.youtube.com/watch?v=NzNQR0930V8&list=RDspRerxsOLXk&index=5', NULL, 1, 1, '2026-10-06 02:57:38', '2026-10-06 03:07:22', 0),
(3, 'hello world 2', 'hello world 2', 'hello world 2', 'https://www.youtube.com/watch?v=GNGyvXwMq_A&list=RDspRerxsOLXk&index=12', NULL, 1, 1, '2026-10-06 03:20:54', '2026-10-06 03:21:16', 0),
(4, 'Hello World 3', 'Hello World 3', 'Hello World 3', 'https://www.youtube.com/watch?v=GNGyvXwMq_A&list=RDspRerxsOLXk&index=12', '12c4bfa88c5bd00a77493c51945604dc.webp', 1, 1, '2026-10-06 03:21:38', '2026-10-06 03:21:38', 1);

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `excerpt` text DEFAULT NULL,
  `content` longtext NOT NULL,
  `featured_image` varchar(255) DEFAULT NULL,
  `author_id` int(11) NOT NULL,
  `editor_id` int(11) NOT NULL,
  `status` enum('draft','published') NOT NULL DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `published_at` datetime DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `archived_at` timestamp NULL DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `category_id`, `title`, `slug`, `excerpt`, `content`, `featured_image`, `author_id`, `editor_id`, `status`, `created_at`, `published_at`, `updated_at`, `archived_at`, `remarks`, `active`) VALUES
(1, 1, 'hello po', 'hello-world', '', '<ol><li><span></span>hello</li></ol>', '9377f81e204737dd483b12218c12af63.png', 1, 1, 'draft', '2026-10-04 03:35:43', '2026-10-04 00:00:00', '2026-10-05 03:59:15', '2026-10-05 03:59:15', NULL, 1),
(2, 4, 'hello', 'helloo', '', '<p>hello</p>', NULL, 1, 1, 'published', '2026-10-04 03:36:57', '2026-10-04 00:00:00', '2026-10-05 06:00:33', '2026-10-05 06:00:33', NULL, 1),
(3, 5, 'hello world', 'helloiii', '', '<ol><li><span></span><strong><em>here and there</em></strong></li></ol>', '83b455261623b72811a0460f6b99930a.webp', 1, 1, 'draft', '2026-10-04 15:52:07', '2026-10-04 00:00:00', '2026-10-05 03:47:59', '2026-10-05 03:47:59', NULL, 1),
(4, 1, 'hello world 2', 'hello world', '', '<p>iii</p>', '0dbfee59e3d26e581b96deecf4303da7.webp', 1, 1, 'published', '2026-10-05 01:14:01', '2026-10-07 00:00:00', '2026-10-05 03:27:56', '2026-10-05 03:22:01', NULL, 0),
(5, 5, 'hello world 3', 'iiiiiiii', '', '<p>kkkkkkkk</p>', 'c970b59ba77f6a6de525f2652f6601bb.webp', 1, 1, 'published', '2026-10-05 03:56:32', '2026-10-09 00:00:00', '2026-10-05 04:14:24', NULL, NULL, 0),
(6, 4, 'Ito p', 'oitis', 'eeeeee', '<p>dddd</p>', '8411e6160ff0747f5e0d2bc1e9be339d.webp', 1, 1, 'published', '2026-10-05 05:59:55', '2026-10-23 00:00:00', '2026-10-05 06:01:02', NULL, NULL, 0),
(7, 1, 'ddddd', 'ddddddd', 'ddddddd', '<ol><li><span></span><strong>dddddd</strong></li></ol>', NULL, 1, 1, 'draft', '2026-10-05 06:02:05', '2026-10-10 00:00:00', '2026-10-05 06:02:05', NULL, NULL, 1),
(8, 1, 'dddddddddd', 'hello', '', '<p>hhhhhhhhhh</p>', NULL, 1, 1, 'draft', '2026-10-05 07:30:08', '2026-10-05 00:00:00', '2026-10-05 07:30:43', NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(150) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('editor','admin') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_at`, `active`) VALUES
(1, 'admin123', 'admin123@gmail.com', '$2y$12$g6LV1KdcYK7Wc7IwdxoODuo4/VhkZyHeLSEN1M9xx7tIvSNh5Qnj.', 'admin', '2026-09-26 02:38:20', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bor`
--
ALTER TABLE `bor`
  ADD PRIMARY KEY (`id`),
  ADD KEY `author_id` (`author_id`),
  ADD KEY `editor_id` (`editor_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `college`
--
ALTER TABLE `college`
  ADD PRIMARY KEY (`id`),
  ADD KEY `author_id` (`author_id`),
  ADD KEY `editor_id` (`editor_id`);

--
-- Indexes for table `eserv`
--
ALTER TABLE `eserv`
  ADD PRIMARY KEY (`id`),
  ADD KEY `author_id` (`author_id`),
  ADD KEY `editor_id` (`editor_id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `author_id` (`author_id`),
  ADD KEY `editor_id` (`editor_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bor`
--
ALTER TABLE `bor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `college`
--
ALTER TABLE `college`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `eserv`
--
ALTER TABLE `eserv`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bor`
--
ALTER TABLE `bor`
  ADD CONSTRAINT `bor_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `bor_ibfk_2` FOREIGN KEY (`editor_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `college`
--
ALTER TABLE `college`
  ADD CONSTRAINT `college_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `college_ibfk_2` FOREIGN KEY (`editor_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `eserv`
--
ALTER TABLE `eserv`
  ADD CONSTRAINT `eserv_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `eserv_ibfk_2` FOREIGN KEY (`editor_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `posts_ibfk_2` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `posts_ibfk_3` FOREIGN KEY (`editor_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
