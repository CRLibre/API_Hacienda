-- MySQL dump 10.16  Distrib 10.1.21-MariaDB, for Win32 (AMD64)
--
-- Host: localhost    Database: localhost
-- ------------------------------------------------------
-- Server version	10.1.21-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `conversations`
--

DROP TABLE IF EXISTS `conversations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `conversations` (
  `idConversation` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `idUser` int(11) unsigned NOT NULL,
  `idRecipient` int(11) unsigned NOT NULL,
  `timestamp` int(11) unsigned NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`idConversation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `conversations`
--

LOCK TABLES `conversations` WRITE;
/*!40000 ALTER TABLE `conversations` DISABLE KEYS */;
/*!40000 ALTER TABLE `conversations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `files`
--

DROP TABLE IF EXISTS `files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `files` (
  `idFile` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `md5` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `timestamp` int(11) unsigned NOT NULL,
  `size` int(11) unsigned NOT NULL,
  `idUser` int(11) unsigned NOT NULL,
  `downloadCode` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fileType` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`idFile`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `files`
--

LOCK TABLES `files` WRITE;
/*!40000 ALTER TABLE `files` DISABLE KEYS */;
/*!40000 ALTER TABLE `files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lestatz_domains`
--

DROP TABLE IF EXISTS `lestatz_domains`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lestatz_domains` (
  `idDomain` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idUser` int(10) unsigned NOT NULL,
  `domain` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total` int(10) unsigned NOT NULL,
  PRIMARY KEY (`idDomain`),
  UNIQUE KEY `idDomain` (`idDomain`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='A list of domains per user';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lestatz_domains`
--

LOCK TABLES `lestatz_domains` WRITE;
/*!40000 ALTER TABLE `lestatz_domains` DISABLE KEYS */;
/*!40000 ALTER TABLE `lestatz_domains` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lestatz_goals`
--

DROP TABLE IF EXISTS `lestatz_goals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lestatz_goals` (
  `idGoal` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idUser` int(10) unsigned NOT NULL,
  `goal` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total` int(10) unsigned NOT NULL,
  `idDomain` int(10) unsigned NOT NULL,
  PRIMARY KEY (`idGoal`),
  UNIQUE KEY `idGoal` (`idGoal`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='A list of goals per user';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lestatz_goals`
--

LOCK TABLES `lestatz_goals` WRITE;
/*!40000 ALTER TABLE `lestatz_goals` DISABLE KEYS */;
/*!40000 ALTER TABLE `lestatz_goals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lestatz_refs`
--

DROP TABLE IF EXISTS `lestatz_refs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lestatz_refs` (
  `idRef` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idUser` int(10) unsigned NOT NULL,
  `ref` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total` int(10) unsigned NOT NULL,
  `idDomain` int(10) unsigned NOT NULL,
  PRIMARY KEY (`idRef`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='A list of refs per user';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lestatz_refs`
--

LOCK TABLES `lestatz_refs` WRITE;
/*!40000 ALTER TABLE `lestatz_refs` DISABLE KEYS */;
/*!40000 ALTER TABLE `lestatz_refs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lestatz_visit_log`
--

DROP TABLE IF EXISTS `lestatz_visit_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lestatz_visit_log` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lestatz_visit_log`
--

LOCK TABLES `lestatz_visit_log` WRITE;
/*!40000 ALTER TABLE `lestatz_visit_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `lestatz_visit_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `marter_logs`
--

DROP TABLE IF EXISTS `marter_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `marter_logs` (
  `idLog` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`idLog`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `marter_logs`
--

LOCK TABLES `marter_logs` WRITE;
/*!40000 ALTER TABLE `marter_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `marter_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `master_certi`
--

DROP TABLE IF EXISTS `master_certi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `master_certi` (
  `idCerti` int(11) NOT NULL AUTO_INCREMENT,
  `hambiente` int(11) NOT NULL,
  `usuario` int(11) NOT NULL,
  `pass` int(11) NOT NULL,
  `pinCerti` int(11) NOT NULL,
  PRIMARY KEY (`idCerti`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `master_certi`
--

LOCK TABLES `master_certi` WRITE;
/*!40000 ALTER TABLE `master_certi` DISABLE KEYS */;
/*!40000 ALTER TABLE `master_certi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `master_claves`
--

DROP TABLE IF EXISTS `master_claves`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `master_claves` (
  `idClave` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`idClave`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `master_claves`
--

LOCK TABLES `master_claves` WRITE;
/*!40000 ALTER TABLE `master_claves` DISABLE KEYS */;
/*!40000 ALTER TABLE `master_claves` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `master_companyusers`
--

DROP TABLE IF EXISTS `master_companyusers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `master_companyusers` (
  `idUser` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`idUser`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `master_companyusers`
--

LOCK TABLES `master_companyusers` WRITE;
/*!40000 ALTER TABLE `master_companyusers` DISABLE KEYS */;
/*!40000 ALTER TABLE `master_companyusers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `master_comprobantes`
--

DROP TABLE IF EXISTS `master_comprobantes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `master_comprobantes` (
  `idComprobante` int(11) NOT NULL AUTO_INCREMENT,
  `idComprobanteReferencia` int(11) NOT NULL,
  `idUser` int(11) NOT NULL,
  `tipoDocumento` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `xmlEnviadoBase64` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `respuestaMHBase64` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `fechaCreacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `idReceptor` int(11) NOT NULL,
  PRIMARY KEY (`idComprobante`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabala de los comprobantes electronicos';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `master_comprobantes`
--

LOCK TABLES `master_comprobantes` WRITE;
/*!40000 ALTER TABLE `master_comprobantes` DISABLE KEYS */;
/*!40000 ALTER TABLE `master_comprobantes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `master_emisores`
--

DROP TABLE IF EXISTS `master_emisores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `master_emisores` (
  `idUser` int(11) NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cedula` int(15) NOT NULL,
  `tipoCedula` int(10) NOT NULL,
  `razonSocial` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `direccion` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
  `correo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` int(15) NOT NULL,
  `provincia` int(11) NOT NULL,
  `canton` int(11) NOT NULL,
  `distrito` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `master_emisores`
--

LOCK TABLES `master_emisores` WRITE;
/*!40000 ALTER TABLE `master_emisores` DISABLE KEYS */;
/*!40000 ALTER TABLE `master_emisores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `master_inventario`
--

DROP TABLE IF EXISTS `master_inventario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `master_inventario` (
  `idProducto` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`idProducto`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `master_inventario`
--

LOCK TABLES `master_inventario` WRITE;
/*!40000 ALTER TABLE `master_inventario` DISABLE KEYS */;
/*!40000 ALTER TABLE `master_inventario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `master_permission`
--

DROP TABLE IF EXISTS `master_permission`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `master_permission` (
  `idCompanyUser` int(11) NOT NULL,
  `idRol` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `master_permission`
--

LOCK TABLES `master_permission` WRITE;
/*!40000 ALTER TABLE `master_permission` DISABLE KEYS */;
/*!40000 ALTER TABLE `master_permission` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `master_receptores`
--

DROP TABLE IF EXISTS `master_receptores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `master_receptores` (
  `idReceptor` int(11) NOT NULL AUTO_INCREMENT,
  `idUser` int(11) NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cedula` int(15) NOT NULL,
  `razonSocial` int(50) NOT NULL,
  `tipoCedula` int(10) NOT NULL,
  `direccion` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
  `correo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`idReceptor`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `master_receptores`
--

LOCK TABLES `master_receptores` WRITE;
/*!40000 ALTER TABLE `master_receptores` DISABLE KEYS */;
/*!40000 ALTER TABLE `master_receptores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `master_rol`
--

DROP TABLE IF EXISTS `master_rol`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `master_rol` (
  `idRol` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`idRol`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `master_rol`
--

LOCK TABLES `master_rol` WRITE;
/*!40000 ALTER TABLE `master_rol` DISABLE KEYS */;
/*!40000 ALTER TABLE `master_rol` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `master_sqlupgrade`
--

DROP TABLE IF EXISTS `master_sqlupgrade`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `master_sqlupgrade` (
  `idSQL` int(11) NOT NULL AUTO_INCREMENT,
  `versionAPI` double NOT NULL,
  `SQL` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`idSQL`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `master_sqlupgrade`
--

LOCK TABLES `master_sqlupgrade` WRITE;
/*!40000 ALTER TABLE `master_sqlupgrade` DISABLE KEYS */;
/*!40000 ALTER TABLE `master_sqlupgrade` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `msgs`
--

DROP TABLE IF EXISTS `msgs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `msgs` (
  `idMsg` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `timestamp` int(11) unsigned NOT NULL,
  `ip` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `idSender` int(11) unsigned NOT NULL,
  `idRecipient` int(11) unsigned NOT NULL,
  `text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `channel` int(11) NOT NULL,
  `attachments` int(11) unsigned NOT NULL,
  `idConversation` int(11) unsigned NOT NULL,
  PRIMARY KEY (`idMsg`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `msgs`
--

LOCK TABLES `msgs` WRITE;
/*!40000 ALTER TABLE `msgs` DISABLE KEYS */;
/*!40000 ALTER TABLE `msgs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `idSession` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `idUser` int(11) unsigned NOT NULL,
  `sessionKey` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lastAccess` int(10) unsigned NOT NULL,
  PRIMARY KEY (`idSession`),
  KEY `key` (`sessionKey`(191)),
  KEY `sessionKey` (`sessionKey`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `idUser` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `fullName` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `userName` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `about` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `country` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(1) COLLATE utf8mb4_unicode_ci NOT NULL,
  `timestamp` int(11) unsigned NOT NULL,
  `lastAccess` int(11) unsigned NOT NULL,
  `pwd` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatar` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `settings` text COLLATE utf8mb4_unicode_ci COMMENT 'Any and all settings you would like to set',
  PRIMARY KEY (`idUser`),
  UNIQUE KEY `wire_2` (`userName`),
  UNIQUE KEY `email` (`email`),
  KEY `idUser` (`idUser`),
  KEY `wire` (`userName`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-10-23  9:07:04
