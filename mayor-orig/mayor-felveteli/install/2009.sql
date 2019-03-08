-- MySQL dump 10.11
--
-- Host: localhost    Database: mayor_felveteli
-- ------------------------------------------------------
-- Server version	5.0.67-0ubuntu6

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
-- Table structure for table `irasbeli_eredmenyek_2009`
--

DROP TABLE IF EXISTS `irasbeli_eredmenyek_2009`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `irasbeli_eredmenyek_2009` (
  `kod` smallint(5) unsigned NOT NULL default '0',
  `nev` varchar(80) collate utf8_unicode_ci NOT NULL,
  `evfolyam` varchar(4) character set utf8 default NULL,
  `ni1` char(1) character set utf8 default NULL,
  `ni2` char(1) character set utf8 default NULL,
  `flap1` varchar(20) collate utf8_unicode_ci default NULL,
  `magyar` char(3) character set utf8 default NULL,
  `flap2` varchar(20) collate utf8_unicode_ci default NULL,
  `matek` char(3) character set utf8 default NULL,
  `matek2` smallint(5) unsigned default NULL,
  `ossz` int(11) default NULL,
  `szuldt` date default NULL,
  `szulvaros` varchar(30) character set utf8 default NULL,
  `an` varchar(60) character set utf8 default NULL,
  `oId` int(11) NOT NULL,
  PRIMARY KEY  (`oId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `irasbeli_eredmenyek_2009`
--

LOCK TABLES `irasbeli_eredmenyek_2009` WRITE;
/*!40000 ALTER TABLE `irasbeli_eredmenyek_2009` DISABLE KEYS */;
/*!40000 ALTER TABLE `irasbeli_eredmenyek_2009` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2009-02-06  8:43:49
