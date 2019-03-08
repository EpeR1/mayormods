-- MySQL dump 10.11
--
-- Host: localhost    Database: mayor_felv_newonly
-- ------------------------------------------------------
-- Server version	5.0.38-Ubuntu_0ubuntu1.1-log

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
-- Table structure for table `adatok_2008`
--

DROP TABLE IF EXISTS `adatok_2008`;
CREATE TABLE `adatok_2008` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `nev` varchar(50) character set utf8 default NULL,
  `jelige` varchar(30) character set utf8 collate utf8_bin default NULL,
  `OM` varchar(7) character set utf8 default NULL,
  `diak` varchar(10) character set utf8 default NULL,
  `oktid` varchar(11) character set utf8 default NULL,
  `szuldt` date default NULL,
  `fl` enum('fiú','lány') character set utf8 default NULL,
  `an` varchar(50) character set utf8 default NULL,
  `irsz` varchar(5) character set utf8 default NULL,
  `varos` varchar(40) character set utf8 default NULL,
  `utca` varchar(50) character set utf8 default NULL,
  `lirsz` varchar(5) character set utf8 default NULL,
  `lvaros` varchar(40) character set utf8 default NULL,
  `lutca` varchar(50) character set utf8 default NULL,
  `atlag` decimal(4,2) default NULL,
  `pontosatlag` decimal(6,4) NOT NULL default '0.0000',
  `pont` decimal(4,2) default NULL,
  `pontsum` decimal(4,2) default NULL,
  `evfolyam` enum('hat','négy') character set utf8 default NULL,
  `jelenleg` varchar(50) character set utf8 default NULL,
  `joslat` varchar(50) character set utf8 default NULL,
  `vegeredmeny` varchar(20) character set utf8 default NULL,
  `level1` enum('nincs értesítve','értesítve') character set utf8 default NULL,
  `level2` enum('nincs értesítve','nem kell értesíteni','értesítve') character set utf8 default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE INDEX oktid (`oktid`)
) ENGINE=InnoDb DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `adatok_2008`
--

LOCK TABLES `adatok_2008` WRITE;
/*!40000 ALTER TABLE `adatok_2008` DISABLE KEYS */;
/*!40000 ALTER TABLE `adatok_2008` ENABLE KEYS */;
UNLOCK TABLES;


DROP TABLE IF EXISTS `szobeli_2008`;
CREATE TABLE `szobeli_2008` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `jid` int(10) unsigned default '0',
  `szoveg` varchar(40) default NULL,
  `bizottsag` tinyint(3) unsigned default '0',
  `nap` varchar(10) default NULL,
  `napdt` date default NULL,
  `ido` time default '00:00:00',
  `tagozat` tinyint(3) unsigned default '0',
  PRIMARY KEY  (`id`),
  FOREIGN KEY(jid)
    REFERENCES adatok_2008(id)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  UNIQUE INDEX id (id)
) ENGINE=InnoDb DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `eredmenyek_tagozatonkent_2008`;
CREATE TABLE `eredmenyek_tagozatonkent_2008` (
  `id` int(10) unsigned NOT NULL default '0',
  `jid` int(10) unsigned NOT NULL default '0',
  `kod` tinyint(3) unsigned default NULL,
  `rangsor` int(10) unsigned default NULL,
  `pont` decimal(4,2) default NULL,
  `szobeli` decimal(4,2) default NULL,
  PRIMARY KEY  (`id`),
  FOREIGN KEY(jid)
    REFERENCES adatok_2008(id)
      ON DELETE CASCADE
      ON UPDATE CASCADE

) ENGINE=innoDb DEFAULT CHARSET=utf8;

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

