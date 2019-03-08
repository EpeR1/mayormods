-- MySQL dump 10.13  Distrib 5.1.31, for debian-linux-gnu (i486)
--
-- Host: localhost    Database: mayor_felveteli
-- ------------------------------------------------------
-- Server version	5.1.31-1ubuntu2

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `adatok_2010`
--

DROP TABLE IF EXISTS `adatok_2010`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `adatok_2010` (
  `id` int(10) unsigned NOT NULL,
  `nev` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `jelige` varchar(30) CHARACTER SET utf8 DEFAULT NULL,
  `OM` varchar(7) CHARACTER SET utf8 DEFAULT NULL,
  `diak` varchar(10) CHARACTER SET utf8 DEFAULT NULL,
  `oktid` varchar(11) COLLATE utf8_hungarian_ci NOT NULL,
  `szuldt` date DEFAULT NULL,
  `fl` enum('fiú','lány') CHARACTER SET utf8 DEFAULT NULL,
  `an` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `irsz` varchar(5) CHARACTER SET utf8 DEFAULT NULL,
  `varos` varchar(40) CHARACTER SET utf8 DEFAULT NULL,
  `utca` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `lirsz` varchar(5) CHARACTER SET utf8 DEFAULT NULL,
  `lvaros` varchar(40) CHARACTER SET utf8 DEFAULT NULL,
  `lutca` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `atlag` decimal(4,2) DEFAULT NULL,
  `magyar` smallint(5) unsigned NOT NULL DEFAULT '0',
  `matek` smallint(5) unsigned NOT NULL DEFAULT '0',
  `matek2` smallint(5) unsigned NOT NULL DEFAULT '0',
  `pontosatlag` decimal(6,4) NOT NULL DEFAULT '0.0000',
  `pont` decimal(4,2) DEFAULT NULL,
  `pontsum` decimal(4,2) DEFAULT NULL,
  `evfolyam` enum('hat','négy') CHARACTER SET utf8 DEFAULT NULL,
  `jelenleg` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `joslat` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `vegeredmeny` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `level1` enum('nincs értesítve','értesítve') CHARACTER SET utf8 DEFAULT NULL,
  `level2` enum('nincs értesítve','nem kell értesíteni','értesítve') CHARACTER SET utf8 DEFAULT NULL,
  `extra` varchar(100) COLLATE utf8_hungarian_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `oktid` (`oktid`,`nev`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;
SET character_set_client = @saved_cs_client;


--
-- Table structure for table `eredmenyek_tagozatonkent_2010`
--

DROP TABLE IF EXISTS `eredmenyek_tagozatonkent_2010`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `eredmenyek_tagozatonkent_2010` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `jid` int(10) unsigned NOT NULL DEFAULT '0',
  `tagozat` tinyint(3) unsigned DEFAULT NULL,
  `rangsor` int(10) unsigned DEFAULT NULL,
  `pont` decimal(4,2) DEFAULT NULL,
  `szobeli` decimal(4,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `jid` (`jid`),
  CONSTRAINT `eredmenyek_tagozatonkent_2010_ibfk_1` FOREIGN KEY (`jid`) REFERENCES `adatok_2010` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1637 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;


--
-- Table structure for table `irasbeli_eredmenyek_2010`
--

DROP TABLE IF EXISTS `irasbeli_eredmenyek_2010`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `irasbeli_eredmenyek_2010` (
  `kod` smallint(5) unsigned NOT NULL DEFAULT '0',
  `nev` varchar(80) COLLATE utf8_unicode_ci NOT NULL,
  `evfolyam` varchar(4) CHARACTER SET utf8 DEFAULT NULL,
  `ni1` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `ni2` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `flap1` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `magyar` char(3) CHARACTER SET utf8 DEFAULT NULL,
  `flap2` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `matek` char(3) CHARACTER SET utf8 DEFAULT NULL,
  `matek2` smallint(5) unsigned DEFAULT NULL,
  `ossz` int(11) DEFAULT NULL,
  `szuldt` date DEFAULT NULL,
  `szulvaros` varchar(30) CHARACTER SET utf8 DEFAULT NULL,
  `an` varchar(60) CHARACTER SET utf8 DEFAULT NULL,
  `oId` varchar(11) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`oId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;


--
-- Table structure for table `szobeli_2010`
--

DROP TABLE IF EXISTS `szobeli_2010`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `szobeli_2010` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `jid` int(10) unsigned DEFAULT '0',
  `szoveg` varchar(40) CHARACTER SET utf8 DEFAULT NULL,
  `bizottsag` tinyint(3) unsigned DEFAULT '0',
  `nap` varchar(10) CHARACTER SET utf8 DEFAULT NULL,
  `napdt` date DEFAULT NULL,
  `ido` time DEFAULT '00:00:00',
  `tagozat` tinyint(3) unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `jid` (`jid`),
  CONSTRAINT `szobeli_2010_ibfk_1` FOREIGN KEY (`jid`) REFERENCES `adatok_2010` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=470 DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;
SET character_set_client = @saved_cs_client;

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2010-02-05  9:17:57
