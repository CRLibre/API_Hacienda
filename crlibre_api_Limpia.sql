-- phpMyAdmin SQL Dump
-- version 4.7.7
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 08-04-2018 a las 17:32:50
-- Versión del servidor: 10.2.14-MariaDB-log
-- Versión de PHP: 5.6.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `crlibre_api_demo`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `conversations`
--

CREATE TABLE `conversations` (
  `idConversation` int(11) UNSIGNED NOT NULL,
  `idUser` int(11) UNSIGNED NOT NULL,
  `idRecipient` int(11) UNSIGNED NOT NULL,
  `timestamp` int(11) UNSIGNED NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `files`
--

CREATE TABLE `files` (
  `idFile` int(10) UNSIGNED NOT NULL,
  `md5` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `timestamp` int(11) UNSIGNED NOT NULL,
  `size` int(11) UNSIGNED NOT NULL,
  `idUser` int(11) UNSIGNED NOT NULL,
  `downloadCode` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fileType` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lestatz_domains`
--

CREATE TABLE `lestatz_domains` (
  `idDomain` int(10) UNSIGNED NOT NULL,
  `idUser` int(10) UNSIGNED NOT NULL,
  `domain` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total` int(10) UNSIGNED NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='A list of domains per user';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lestatz_goals`
--

CREATE TABLE `lestatz_goals` (
  `idGoal` int(10) UNSIGNED NOT NULL,
  `idUser` int(10) UNSIGNED NOT NULL,
  `goal` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total` int(10) UNSIGNED NOT NULL,
  `idDomain` int(10) UNSIGNED NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='A list of goals per user';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lestatz_refs`
--

CREATE TABLE `lestatz_refs` (
  `idRef` int(10) UNSIGNED NOT NULL,
  `idUser` int(10) UNSIGNED NOT NULL,
  `ref` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total` int(10) UNSIGNED NOT NULL,
  `idDomain` int(10) UNSIGNED NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='A list of refs per user';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lestatz_visit_log`
--

CREATE TABLE `lestatz_visit_log` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `timestamp` int(10) UNSIGNED NOT NULL,
  `ip` varchar(50) NOT NULL,
  `browser` varchar(15) NOT NULL,
  `lang` varchar(10) NOT NULL,
  `os` varchar(10) NOT NULL,
  `url` varchar(250) NOT NULL,
  `args` varchar(250) NOT NULL,
  `page_name` varchar(250) NOT NULL,
  `referrer` varchar(250) NOT NULL,
  `country` varchar(3) NOT NULL,
  `idRef` int(10) UNSIGNED NOT NULL COMMENT 'The refering page according to a user setting',
  `idGoal` int(10) UNSIGNED NOT NULL COMMENT 'The goal set by the user',
  `coordinates` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='The main registry of visits, this can become quite big';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `msgs`
--

CREATE TABLE `msgs` (
  `idMsg` int(11) UNSIGNED NOT NULL,
  `timestamp` int(11) UNSIGNED NOT NULL,
  `ip` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `idSender` int(11) UNSIGNED NOT NULL,
  `idRecipient` int(11) UNSIGNED NOT NULL,
  `text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `channel` int(11) NOT NULL,
  `attachments` int(11) UNSIGNED NOT NULL,
  `idConversation` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `msgs`
--

INSERT INTO `msgs` (`idMsg`, `timestamp`, `ip`, `idSender`, `idRecipient`, `text`, `channel`, `attachments`, `idConversation`) VALUES
(1, 1429227759, '192.168.43.164', 1, 2, 'va uno nuevo', 0, 0, 0),
(2, 1429227772, '192.168.43.164', 2, 1, 'nicer dicer!', 0, 0, 0),
(3, 1429233896, '192.168.43.164', 1, 2, 'y el ultimo', 0, 0, 0),
(4, 1429234012, '192.168.43.164', 1, 2, 'id', 0, 0, 0),
(5, 1429274092, '192.168.43.164', 1, 2, 'aeoahu', 0, 0, 0),
(6, 1429274093, '192.168.43.164', 1, 2, 'aeou', 0, 0, 0),
(7, 1429274093, '192.168.43.164', 1, 2, 'oeu', 0, 0, 0),
(8, 1429274093, '192.168.43.164', 1, 2, 'u', 0, 0, 0),
(9, 1429274094, '192.168.43.164', 1, 2, 'au', 0, 0, 0),
(10, 1429274094, '192.168.43.164', 1, 2, 'u', 0, 0, 0),
(11, 1429274094, '192.168.43.164', 1, 2, 'eu', 0, 0, 0),
(12, 1429274095, '192.168.43.164', 1, 2, 'ue', 0, 0, 0),
(13, 1429274095, '192.168.43.164', 1, 2, 'eu', 0, 0, 0),
(14, 1429274095, '192.168.43.164', 1, 2, 'u', 0, 0, 0),
(15, 1429274095, '192.168.43.164', 1, 2, 'eu', 0, 0, 0),
(16, 1429274096, '192.168.43.164', 1, 2, '', 0, 0, 0),
(17, 1429274096, '192.168.43.164', 1, 2, 'u', 0, 0, 0),
(18, 1429274096, '192.168.43.164', 1, 2, 'ao', 0, 0, 0),
(19, 1429274097, '192.168.43.164', 1, 2, 'aouaoeu', 0, 0, 0),
(20, 1429274218, '192.168.43.164', 1, 2, 'b', 0, 0, 0),
(21, 1429274219, '192.168.43.164', 1, 2, 'c', 0, 0, 0),
(22, 1429274220, '192.168.43.164', 1, 2, 'd', 0, 0, 0),
(23, 1429274223, '192.168.43.164', 1, 2, 'a si, comprendo', 0, 0, 0),
(24, 1429274226, '192.168.43.164', 1, 2, 'bien bien bien', 0, 0, 0),
(25, 1429274555, '192.168.43.164', 1, 2, 'este deberÃ­a tener titulo', 0, 0, 1),
(26, 1429275906, '192.168.43.164', 1, 2, 'uno', 0, 0, 1),
(27, 1429275907, '192.168.43.164', 1, 2, 'dos', 0, 0, 1),
(28, 1429275908, '192.168.43.164', 1, 2, 'tres', 0, 0, 1),
(29, 1429414895, '192.168.43.164', 2, 0, 'nthunaho', 0, 0, 1),
(30, 1429414914, '192.168.43.164', 2, 1, 'nthunaho', 0, 0, 1),
(31, 1429414926, '192.168.43.164', 2, 1, 'nanu nanu', 0, 0, 1),
(32, 1429415017, '192.168.43.164', 2, 1, 'del mar', 0, 0, 2),
(33, 1429415302, '192.168.43.164', 2, 1, 'aeua', 0, 0, 2),
(34, 1429415337, '192.168.43.164', 2, 1, 'nthnth', 0, 0, 2),
(35, 1429415351, '192.168.43.164', 2, 1, 'nhstnh', 0, 0, 2),
(36, 1429415368, '192.168.43.164', 2, 1, 'sth', 0, 0, 2),
(37, 1429415368, '192.168.43.164', 2, 1, '', 0, 0, 2),
(38, 1429415372, '192.168.43.164', 2, 1, 'tttt', 0, 0, 2),
(39, 1429415435, '192.168.43.164', 2, 1, 'snnn\\', 0, 0, 2),
(40, 1429415509, '192.168.43.164', 2, 1, 'aonuth', 0, 0, 2),
(41, 1429415516, '192.168.43.164', 2, 1, 'aoeu', 0, 0, 2),
(42, 1429415532, '192.168.43.164', 2, 1, 'uaeou', 0, 0, 2),
(43, 1429415560, '192.168.43.164', 2, 1, 'naeohu', 0, 0, 2),
(44, 1429415563, '192.168.43.164', 2, 1, 'uno', 0, 0, 2),
(45, 1429415576, '192.168.43.164', 2, 1, 'mas', 0, 0, 2),
(46, 1429416009, '192.168.43.164', 2, 1, 'tons', 0, 0, 2),
(47, 1429416013, '192.168.43.164', 2, 1, 'que bien :)', 0, 0, 2),
(48, 1429416019, '192.168.43.164', 2, 1, 'vea bb', 0, 0, 2),
(49, 1429420265, '192.168.43.164', 2, 1, 'hola ?', 0, 0, 2),
(50, 1429420268, '192.168.43.164', 2, 1, 'tnahseosutah', 0, 0, 2),
(51, 1429420269, '192.168.43.164', 2, 1, 'aoeu', 0, 0, 2),
(52, 1429420269, '192.168.43.164', 2, 1, 'oaeu', 0, 0, 2),
(53, 1429420269, '192.168.43.164', 2, 1, 'oaeu', 0, 0, 2),
(54, 1429420270, '192.168.43.164', 2, 1, 'uaou', 0, 0, 2),
(55, 1429420270, '192.168.43.164', 2, 1, 'u', 0, 0, 2),
(56, 1429420270, '192.168.43.164', 2, 1, 'ae', 0, 0, 2),
(57, 1429420379, '192.168.43.164', 2, 1, 'nicer dicer!', 0, 0, 2),
(58, 1429448743, '192.168.43.164', 2, 1, 'aeonuh', 0, 0, 2),
(59, 1429456372, '192.168.43.164', 2, 1, 'funciona?', 0, 0, 2),
(60, 1429456377, '192.168.43.164', 2, 1, 'si seÃ±or!!!', 0, 0, 2),
(61, 1429536336, '192.168.43.164', 2, 1, 'hello', 0, 0, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sessions`
--

CREATE TABLE `sessions` (
  `idSession` int(11) UNSIGNED NOT NULL,
  `idUser` int(11) UNSIGNED NOT NULL,
  `sessionKey` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lastAccess` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `idUser` int(11) UNSIGNED NOT NULL,
  `fullName` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `userName` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `about` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `country` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(1) COLLATE utf8mb4_unicode_ci NOT NULL,
  `timestamp` int(11) UNSIGNED NOT NULL,
  `lastAccess` int(11) UNSIGNED NOT NULL,
  `pwd` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatar` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `settings` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Any and all settings you would like to set'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`idUser`, `fullName`, `userName`, `email`, `about`, `country`, `status`, `timestamp`, `lastAccess`, `pwd`, `avatar`, `settings`) VALUES
(1, 'root', 'root', 'a', 'I am me', 'crc', '1', 1429235768, 1523223130, '202cb962ac59075b964b07152d234b70', '', '');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `conversations`
--
ALTER TABLE `conversations`
  ADD PRIMARY KEY (`idConversation`);

--
-- Indices de la tabla `files`
--
ALTER TABLE `files`
  ADD PRIMARY KEY (`idFile`);

--
-- Indices de la tabla `lestatz_domains`
--
ALTER TABLE `lestatz_domains`
  ADD PRIMARY KEY (`idDomain`),
  ADD UNIQUE KEY `idDomain` (`idDomain`);

--
-- Indices de la tabla `lestatz_goals`
--
ALTER TABLE `lestatz_goals`
  ADD PRIMARY KEY (`idGoal`),
  ADD UNIQUE KEY `idGoal` (`idGoal`);

--
-- Indices de la tabla `lestatz_refs`
--
ALTER TABLE `lestatz_refs`
  ADD PRIMARY KEY (`idRef`);

--
-- Indices de la tabla `msgs`
--
ALTER TABLE `msgs`
  ADD PRIMARY KEY (`idMsg`);

--
-- Indices de la tabla `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`idSession`),
  ADD KEY `key` (`sessionKey`),
  ADD KEY `sessionKey` (`sessionKey`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`idUser`),
  ADD UNIQUE KEY `wire_2` (`userName`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idUser` (`idUser`),
  ADD KEY `wire` (`userName`),
  ADD KEY `status` (`status`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `conversations`
--
ALTER TABLE `conversations`
  MODIFY `idConversation` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `files`
--
ALTER TABLE `files`
  MODIFY `idFile` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `lestatz_domains`
--
ALTER TABLE `lestatz_domains`
  MODIFY `idDomain` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `lestatz_goals`
--
ALTER TABLE `lestatz_goals`
  MODIFY `idGoal` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `lestatz_refs`
--
ALTER TABLE `lestatz_refs`
  MODIFY `idRef` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `msgs`
--
ALTER TABLE `msgs`
  MODIFY `idMsg` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT de la tabla `sessions`
--
ALTER TABLE `sessions`
  MODIFY `idSession` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `idUser` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
