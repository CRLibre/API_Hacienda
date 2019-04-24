-- phpMyAdmin SQL Dump
-- version 4.3.11.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Apr 26, 2015 at 10:30 AM
-- Server version: 5.5.43-0ubuntu0.14.04.1
-- PHP Version: 5.5.9-1ubuntu4.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cala_dev_01`
--

-- --------------------------------------------------------

--
-- Table structure for table `conversations`
--

CREATE TABLE IF NOT EXISTS `conversations` (
  `idConversation` int(11) unsigned NOT NULL,
  `idUser` int(11) unsigned NOT NULL,
  `idRecipient` int(11) unsigned NOT NULL,
  `timestamp` int(11) unsigned NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `files`
--

CREATE TABLE IF NOT EXISTS `files` (
  `idFile` int(10) unsigned NOT NULL,
  `md5` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `timestamp` int(11) unsigned NOT NULL,
  `size` int(11) unsigned NOT NULL,
  `idUser` int(11) unsigned NOT NULL,
  `downloadCode` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fileType` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lestatz_domains`
--

CREATE TABLE IF NOT EXISTS `lestatz_domains` (
  `idDomain` int(10) unsigned NOT NULL,
  `idUser` int(10) unsigned NOT NULL,
  `domain` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total` int(10) unsigned NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='A list of domains per user';

-- --------------------------------------------------------

--
-- Table structure for table `lestatz_goals`
--

CREATE TABLE IF NOT EXISTS `lestatz_goals` (
  `idGoal` int(10) unsigned NOT NULL,
  `idUser` int(10) unsigned NOT NULL,
  `goal` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total` int(10) unsigned NOT NULL,
  `idDomain` int(10) unsigned NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='A list of goals per user';

-- --------------------------------------------------------

--
-- Table structure for table `lestatz_refs`
--

CREATE TABLE IF NOT EXISTS `lestatz_refs` (
  `idRef` int(10) unsigned NOT NULL,
  `idUser` int(10) unsigned NOT NULL,
  `ref` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total` int(10) unsigned NOT NULL,
  `idDomain` int(10) unsigned NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='A list of refs per user';

-- --------------------------------------------------------

--
-- Table structure for table `lestatz_visit_log`
--

CREATE TABLE IF NOT EXISTS `lestatz_visit_log` (
  `user_id` int(10) unsigned NOT NULL,
  `timestamp` int(10) unsigned NOT NULL,
  `ip` varchar(50) NOT NULL,
  `browser` varchar(15) NOT NULL,
  `lang` varchar(10) NOT NULL,
  `os` varchar(10) NOT NULL,
  `url` varchar(250) NOT NULL,
  `args` varchar(250) NOT NULL,
  `page_name` varchar(250) NOT NULL,
  `referrer` varchar(250) NOT NULL,
  `country` varchar(3) NOT NULL,
  `idRef` int(10) unsigned NOT NULL COMMENT 'The refering page according to a user setting',
  `idGoal` int(10) unsigned NOT NULL COMMENT 'The goal set by the user',
  `coordinates` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='The main registry of visits, this can become quite big';

-- --------------------------------------------------------

--
-- Table structure for table `msgs`
--

CREATE TABLE IF NOT EXISTS `msgs` (
  `idMsg` int(11) unsigned NOT NULL,
  `timestamp` int(11) unsigned NOT NULL,
  `ip` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `idSender` int(11) unsigned NOT NULL,
  `idRecipient` int(11) unsigned NOT NULL,
  `text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `channel` int(11) NOT NULL,
  `attachments` int(11) unsigned NOT NULL,
  `idConversation` int(11) unsigned NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=76 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE IF NOT EXISTS `sessions` (
  `idSession` int(11) unsigned NOT NULL,
  `idUser` int(11) unsigned NOT NULL,
  `sessionKey` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lastAccess` int(10) unsigned NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=70 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `idUser` int(11) unsigned NOT NULL,
  `fullName` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `userName` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `about` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `country` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(1) COLLATE utf8mb4_unicode_ci NOT NULL,
  `timestamp` int(11) unsigned NOT NULL,
  `lastAccess` int(11) unsigned NOT NULL,
  `pwd` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatar` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `settings` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Any and all settings you would like to set'
) ENGINE=MyISAM AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `conversations`
--
ALTER TABLE `conversations`
  ADD PRIMARY KEY (`idConversation`);

--
-- Indexes for table `files`
--
ALTER TABLE `files`
  ADD PRIMARY KEY (`idFile`);

--
-- Indexes for table `lestatz_domains`
--
ALTER TABLE `lestatz_domains`
  ADD PRIMARY KEY (`idDomain`), ADD UNIQUE KEY `idDomain` (`idDomain`);

--
-- Indexes for table `lestatz_goals`
--
ALTER TABLE `lestatz_goals`
  ADD PRIMARY KEY (`idGoal`), ADD UNIQUE KEY `idGoal` (`idGoal`);

--
-- Indexes for table `lestatz_refs`
--
ALTER TABLE `lestatz_refs`
  ADD PRIMARY KEY (`idRef`);

--
-- Indexes for table `msgs`
--
ALTER TABLE `msgs`
  ADD PRIMARY KEY (`idMsg`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`idSession`), ADD KEY `key` (`sessionKey`), ADD KEY `sessionKey` (`sessionKey`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`idUser`), ADD UNIQUE KEY `wire_2` (`userName`), ADD UNIQUE KEY `email` (`email`), ADD KEY `idUser` (`idUser`), ADD KEY `wire` (`userName`), ADD KEY `status` (`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `conversations`
--
ALTER TABLE `conversations`
  MODIFY `idConversation` int(11) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `files`
--
ALTER TABLE `files`
  MODIFY `idFile` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=15;
--
-- AUTO_INCREMENT for table `lestatz_domains`
--
ALTER TABLE `lestatz_domains`
  MODIFY `idDomain` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `lestatz_goals`
--
ALTER TABLE `lestatz_goals`
  MODIFY `idGoal` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=8;
--
-- AUTO_INCREMENT for table `lestatz_refs`
--
ALTER TABLE `lestatz_refs`
  MODIFY `idRef` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT for table `msgs`
--
ALTER TABLE `msgs`
  MODIFY `idMsg` int(11) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=76;
--
-- AUTO_INCREMENT for table `sessions`
--
ALTER TABLE `sessions`
  MODIFY `idSession` int(11) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=70;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `idUser` int(11) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=36;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
