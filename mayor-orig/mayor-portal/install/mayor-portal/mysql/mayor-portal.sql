-- MySQL dump 10.11
--
-- Host: localhost    Database: hirek
-- ------------------------------------------------------
-- Server version	5.0.32-Debian_7etch1-log

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

CREATE DATABASE IF NOT EXISTS %MYSQL_PORTAL_DB% CHARACTER SET utf8 COLLATE utf8_hungarian_ci;
GRANT ALL ON `%MYSQL_PORTAL_DB%`.* TO '%MYSQL_PORTAL_USER%'@'localhost' IDENTIFIED BY '%MYSQL_PORTAL_PW%';

USE %MYSQL_PORTAL_DB%;

CREATE TABLE `mayorUpdateLog` (
  `scriptFile` varchar(255) COLLATE utf8_hungarian_ci NOT NULL DEFAULT '',
  `dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`scriptFile`,`dt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

--
-- Table structure for table `hirek`
--

DROP TABLE IF EXISTS `hirek`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `hirek` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `kdt` datetime default NULL,
  `vdt` datetime default NULL,
  `class` tinyint(3) unsigned NOT NULL default '0',
  `lang` varchar(5) default 'hu_HU',
  `cim` text,
  `txt` text,
  `owner` varchar(10) default NULL,
  `flag` tinyint(3) unsigned NOT NULL default '0',
  `cid` tinyint(3) unsigned NOT NULL default '0',
  `pic` varchar(20) default NULL,
  `friss` enum('','on') default '',
  `fontos` enum('','on') default '',
  `split` tinyint(3) unsigned NOT NULL default '0',
  `csoport` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `dtindex` (`kdt`,`vdt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 PACK_KEYS=1;
SET character_set_client = @saved_cs_client;

--
--
-- Table structure for table `kategoriak`
--

DROP TABLE IF EXISTS `kategoriak`;
CREATE TABLE `kategoriak` (
  `id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `leiras` varchar(70) NOT NULL DEFAULT '',
  `precode` text,
  `postcode` text,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 PACK_KEYS=1;

DROP TABLE IF EXISTS `hirKategoria`;
CREATE TABLE `hirKategoria` (
  `hirId` int(10) unsigned NOT NULL,
  `kategoriaId` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`hirId`,`kategoriaId`),
  KEY `hirKategoria_FKIndex1` (`hirId`),
  KEY `hirKategoria_FKIndex2` (`kategoriaId`),
  CONSTRAINT `hirKategoria_ibfk_1` FOREIGN KEY (`hirId`) REFERENCES `hirek` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `hirKategoria_ibfk_2` FOREIGN KEY (`kategoriaId`) REFERENCES `kategoriak` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;
--
-- Table structure for table `linkek`
--

DROP TABLE IF EXISTS `linkek`;
CREATE TABLE `linkek` (
  `kulcs` int(10) unsigned NOT NULL auto_increment,
  `hid` int(10) unsigned NOT NULL default '0',
  `uri` varchar(255) default NULL,
  `linktipus` enum('belso','kulso') default NULL,
  `szoveg` text,
  PRIMARY KEY  (`kulcs`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 PACK_KEYS=1;


--
-- Table structure for table `kerdesek`
--

DROP TABLE IF EXISTS `kerdesek`;
CREATE TABLE `kerdesek` (
  `sorszam` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `vszam` tinyint(3) unsigned DEFAULT NULL,
  `kerdes` varchar(255) DEFAULT NULL,
  `policy` set('private','parent','public') DEFAULT 'public',
  PRIMARY KEY (`sorszam`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 PACK_KEYS=1;


--
-- Table structure for table `nevnap`
--

DROP TABLE IF EXISTS `nevnap`;
CREATE TABLE `nevnap` (
  `honap` tinyint(4) default NULL,
  `nap` tinyint(4) default NULL,
  `nevnap` tinytext
) ENGINE=InnoDB DEFAULT CHARSET=utf8 PACK_KEYS=1;

--
-- Dumping data for table `nevnap`
--

/*!40000 ALTER TABLE `nevnap` DISABLE KEYS */;
INSERT INTO `nevnap` VALUES (1,1,'Fruzsina'),(1,2,'Ábel'),(1,3,'Genovéva, Benjámin'),(1,4,'Titusz, Leona'),(1,5,'Simon'),(1,6,'Boldizsár'),(1,7,'Attila, Ramóna'),(1,8,'Gyöngyvér'),(1,9,'Marcell'),(1,10,'Melánia'),(1,11,'Ágota'),(1,12,'Ernő'),(1,13,'Veronika'),(1,14,'Bódog'),(1,15,'Lóránt, Loránd'),(1,16,'Gusztáv'),(1,17,'Antal, Antónia'),(1,18,'Piroska'),(1,19,'Sára, Márió'),(1,20,'Fábián, Sebestyén'),(1,21,'Ágnes'),(1,22,'Vince, Artúr'),(1,23,'Zelma, Rajmund'),(1,24,'Timót'),(1,25,'Pál'),(1,26,'Vanda, Paula'),(1,27,'Angelika'),(1,28,'Károly, Karola'),(1,29,'Adél'),(1,30,'Martina, Gerda'),(1,31,'Marcella'),(2,1,'Ignác'),(2,2,'Karolina, Aida'),(2,3,'Balázs'),(2,4,'Ráhel, Csenge'),(2,5,'Ágota, Ingrid'),(2,6,'Dorottya, Dóra'),(2,7,'Tódor, Rómeó'),(2,8,'Aranka'),(2,9,'Abigél, Alex'),(2,10,'Elvira'),(2,11,'Bertold, Marietta'),(2,12,'Lívia, Lídia'),(2,13,'Ella, Linda'),(2,14,'Bálint, Valentin'),(2,15,'Kolos, Georgina'),(2,16,'Julianna, Lilla'),(2,17,'Donát'),(2,18,'Bernadett'),(2,19,'Zsuzsanna'),(2,20,'Aladár, Álmos'),(2,21,'Eleonóra'),(2,22,'Gerzson'),(2,23,'Alfréd'),(2,24,'Szőkőnap'),(2,25,'Mátyás'),(2,26,'Géza'),(2,27,'Edina'),(2,28,'Ákos, Bátor'),(2,29,'Elemér'),(3,1,'Albin'),(3,2,'Lujza'),(3,3,'Kornélia'),(3,4,'Kázmér'),(3,5,'Adorján, Adrián'),(3,6,'Leonóra, Inez'),(3,7,'Tamás'),(3,8,'Zoltán'),(3,9,'Franciska, Fanni'),(3,10,'Ildikó'),(3,11,'Szilárd'),(3,12,'Gergely'),(3,13,'Krisztián, Ajtony'),(3,14,'Matild'),(3,15,'Kristóf'),(3,16,'Henrietta'),(3,17,'Gertrúd, Patrik'),(3,18,'Sándor, Ede'),(3,19,'József, Bánk'),(3,20,'Klaudia'),(3,21,'Benedek'),(3,22,'Beáta, Izolda'),(3,23,'Emőke'),(3,24,'Gábor, Karina'),(3,25,'Irén, Irisz'),(3,26,'Emánuel'),(3,27,'Hajnalka'),(3,28,'Gedeon, Johanna'),(3,29,'Auguszta'),(3,30,'Zalán'),(3,31,'Árpád'),(4,1,'Hugó'),(4,2,'Áron'),(4,3,'Buda, Richárd'),(4,4,'Izidor'),(4,5,'Vince'),(4,6,'Vilmos, Bíborka'),(4,7,'Herman'),(4,8,'Dénes'),(4,9,'Erhard'),(4,10,'Zsolt'),(4,11,'Leó, Szaniszló'),(4,12,'Gyula'),(4,13,'Ida'),(4,14,'Tibor, Nyúl:)'),(4,15,'Anasztázia, Tas'),(4,16,'Csongor'),(4,17,'Rudolf'),(4,18,'Andrea, Ilma'),(4,19,'Emma, :)'),(4,20,'Tivadar'),(4,21,'Konrád'),(4,22,'Csilla, Noémi'),(4,23,'Béla'),(4,24,'György'),(4,25,'Márk'),(4,26,'Ervin'),(4,27,'Zita'),(4,28,'Valéria'),(4,29,'Péter'),(4,30,'Katalin, Kitti'),(5,1,'Fülöp, Jakab'),(5,2,'Zsigmond'),(5,3,'Tímea, Irma'),(5,4,'Mónika, Flórián'),(5,5,'Györgyi'),(5,6,'Ivett, Frida'),(5,7,'Gizella'),(5,8,'Mihály'),(5,9,'Gergely'),(5,10,'Ármin, Pálma'),(5,11,'Ferenc'),(5,12,'Pongrác'),(5,13,'Szervác, Imola'),(5,14,'Bonifác'),(5,15,'Zsófia, Szonja'),(5,16,'Mózes, Botond'),(5,17,'Paszkál'),(5,18,'Erik, Alexandra'),(5,19,'Ivó, Milán'),(5,20,'Bernát, Felícia'),(5,21,'Konstantin'),(5,22,'Júlia, Rita'),(5,23,'Dezső'),(5,24,'Eszter, Eliza'),(5,25,'Orbán'),(5,26,'Fülöp, Evelin'),(5,27,'Hella'),(5,28,'Emil, Csanád'),(5,29,'Magdolna'),(5,30,'Janka, Zsanett'),(5,31,'Angéla, Petronella'),(6,1,'Tünde'),(6,2,'Kármen, Anita'),(6,3,'Klotild'),(6,4,'Bulcsú'),(6,5,'Fatime'),(6,6,'Norbert, Cintia'),(6,7,'Róbert'),(6,8,'Medárd'),(6,9,'Félix'),(6,10,'Margit, Gréta'),(6,11,'Barnabás'),(6,12,'Villő'),(6,13,'Antal, Anett'),(6,14,'Vazul'),(6,15,'Jolán, Vid'),(6,16,'Jusztin'),(6,17,'Laura, Alida'),(6,18,'Arnold, Levente'),(6,19,'Gyárfás'),(6,20,'Rafael'),(6,21,'Alajos, Leila'),(6,22,'Paulina'),(6,23,'Zoltán'),(6,24,'Iván'),(6,25,'Vilmos'),(6,26,'János, Pál'),(6,27,'László'),(6,28,'Levente, Irén'),(6,29,'Péter, Pál'),(6,30,'Pál'),(7,1,'Tihamér, Annamária'),(7,2,'Ottó'),(7,3,'Kornél, Soma'),(7,4,'Ulrik'),(7,5,'Emese, Sarolta'),(7,6,'Csaba'),(7,7,'Appolónia'),(7,8,'Ellák'),(7,9,'Lukrécia'),(7,10,'Amália'),(7,11,'Nóra, Lili'),(7,12,'Izabella, Dalma'),(7,13,'Jenő'),(7,14,'őrs, Stella'),(7,15,'Henrik, Roland'),(7,16,'Valter'),(7,17,'Endre, Elek'),(7,18,'Frigyes'),(7,19,'Emília'),(7,20,'Illés'),(7,21,'Dániel, Daniella'),(7,22,'Magdolna'),(7,23,'Lenke'),(7,24,'Kinga, Kincső'),(7,25,'Kristóf, Jakab'),(7,26,'Anna, Anikó'),(7,27,'Olga, Liliána'),(7,28,'Szabolcs'),(7,29,'Márta, Flóra'),(7,30,'Judit, Xénia'),(7,31,'Oszkár'),(8,1,'Boglárka'),(8,2,'Lehel'),(8,3,'Hermina'),(8,4,'Domonkos, Dominika'),(8,5,'Krisztina'),(8,6,'Berta, Bettina'),(8,7,'Ibolya'),(8,8,'László'),(8,9,'Emőd'),(8,10,'Lörinc'),(8,11,'Zsuzsanna, Tiborc'),(8,12,'Klára'),(8,13,'Ipoly'),(8,14,'Marcell'),(8,15,'Mária'),(8,16,'Ábrahám'),(8,17,'Jácint'),(8,18,'Ilona'),(8,19,'Huba'),(8,20,'István'),(8,21,'Sámuel, Hajna'),(8,22,'Menyhért, Mirjam'),(8,23,'Bence'),(8,24,'Bertalan'),(8,25,'Lajos, Patrícia'),(8,26,'Izsó'),(8,27,'Gáspár'),(8,28,'Ágoston'),(8,29,'Beatrix, Erna'),(8,30,'Rózsa'),(8,31,'Erika, Bella'),(9,1,'Egyed, Egon'),(9,2,'Rebeka, Dorina'),(9,3,'Hilda'),(9,4,'Rozália'),(9,5,'Viktor, Lőrinc'),(9,6,'Zakariás'),(9,7,'Regina'),(9,8,'Mária, Adrienn'),(9,9,'Ádám'),(9,10,'Nikolett, Hunor'),(9,11,'Teodóra'),(9,12,'Mária'),(9,13,'Kornél'),(9,14,'Szeréna, Roxána'),(9,15,'Enikő, Melitta'),(9,16,'Edit'),(9,17,'Zsófia'),(9,18,'Diána'),(9,19,'Vilhelmina'),(9,20,'Friderika'),(9,21,'Máté, Mirella'),(9,22,'Móric'),(9,23,'Tekla'),(9,24,'Gellért, Mercédesz'),(9,25,'Eufrozina, Kende'),(9,26,'Jusztina'),(9,27,'Adalbert'),(9,28,'Vencel'),(9,29,'Mihály'),(9,30,'Jeromos'),(10,1,'Malvin'),(10,2,'Petra'),(10,3,'Helga'),(10,4,'Ferenc'),(10,5,'Aurél'),(10,6,'Brúnó, Renáta'),(10,7,'Amália'),(10,8,'Koppány'),(10,9,'Dénes'),(10,10,'Gedeon'),(10,11,'Brigitta'),(10,12,'Miksa'),(10,13,'Kálmán, Ede'),(10,14,'Helén'),(10,15,'Teréz'),(10,16,'Gál'),(10,17,'Hedvig'),(10,18,'Lukács'),(10,19,'Nándor'),(10,20,'Vendel, :)'),(10,21,'Orsolya'),(10,22,'Előd'),(10,23,'Gyöngyi'),(10,24,'Salamon'),(10,25,'Blanka, Bianka'),(10,26,'Dömötör'),(10,27,'Szabina'),(10,28,'Simon, Szimonetta'),(10,29,'Nárcisz'),(10,30,'Alfonz'),(10,31,'Farkas'),(11,1,'Marianna'),(11,2,'Achilles'),(11,3,'Győző'),(11,4,'Károly'),(11,5,'Imre'),(11,6,'Lénárd'),(11,7,'Rezső'),(11,8,'Zsombor'),(11,9,'Tivadar'),(11,10,'Réka'),(11,11,'Márton'),(11,12,'Jónás, Renátó'),(11,13,'Szilvia'),(11,14,'Aliz'),(11,15,'Albert, Lipót'),(11,16,'Ödön'),(11,17,'Hortenzia, Gergő'),(11,18,'Jenő'),(11,19,'Erzsébet'),(11,20,'Jolán'),(11,21,'Olivér'),(11,22,'Cecília'),(11,23,'Kelemen, Klementina'),(11,24,'Emma'),(11,25,'Katalin'),(11,26,'Virág'),(11,27,'Virgil'),(11,28,'Stefánia'),(11,29,'Taksony'),(11,30,'András, Andor'),(12,1,'Elza'),(12,2,'Melinda, Vivien'),(12,3,'Ferenc, Olívia'),(12,4,'Borbála, Barbara'),(12,5,'Vilma'),(12,6,'Mikulás, Miklós'),(12,7,'Ambrus'),(12,8,'Mária'),(12,9,'Natália'),(12,10,'Judit'),(12,11,'Árpád'),(12,12,'Gabriella'),(12,13,'Luca, Otília'),(12,14,'Szilárda'),(12,15,'Valér'),(12,16,'Etelka, Aletta'),(12,17,'Lázár, Olimpia'),(12,18,'Auguszta'),(12,19,'Viola'),(12,20,'Teofil'),(12,21,'Tamás'),(12,22,'Zéno'),(12,23,'Viktória'),(12,24,'Ádám, Éva'),(12,25,'KARÁCSONY, Eugénia'),(12,26,'KARÁCSONY, István'),(12,27,'János'),(12,28,'Kamilla'),(12,29,'Tamás, Tamara'),(12,30,'Dávid'),(12,31,'Szilveszter');
/*!40000 ALTER TABLE `nevnap` ENABLE KEYS */;

-- Table structure for table `valaszok`
--

DROP TABLE IF EXISTS `valaszok`;
CREATE TABLE `valaszok` (
  `sorszam` int(10) unsigned NOT NULL auto_increment,
  `kszam` int(10) unsigned default NULL,
  `pontszam` int(10) unsigned default '0',
  `valasz` varchar(255) default NULL,
  PRIMARY KEY  (`sorszam`)
) ENGINE=InnoDb DEFAULT CHARSET=utf8;

-- Table structure for table `kerdoivSzavazott`
--

DROP TABLE IF EXISTS `kerdoivSzavazott`;
CREATE TABLE `kerdoivSzavazott` (
  `kerdoivId` int(10) unsigned NOT NULL,
  `policy` enum('private','parent') COLLATE utf8_hungarian_ci DEFAULT NULL,
  `userAccount` varchar(60) COLLATE utf8_hungarian_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

