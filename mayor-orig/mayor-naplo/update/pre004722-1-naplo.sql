DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4722 $$

CREATE PROCEDURE upgrade_database_4722()
BEGIN
SET NAMES utf8 COLLATE utf8_hungarian_ci;

IF NOT EXISTS (
    SELECT * FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='felveteli'
) THEN
CREATE TABLE `felveteli` (
  `oId` bigint(20) NOT NULL,
  `nev` varchar(50) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `jelige` varchar(30) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `omkod` varchar(7) CHARACTER SET utf8 DEFAULT NULL,
  `szuldt` date DEFAULT NULL,
  `fl` enum('fiú','lány') CHARACTER SET utf8 DEFAULT NULL,
  `an` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `lakcim_irsz` varchar(5) CHARACTER SET utf8 DEFAULT NULL,
  `lakcim_telepules` varchar(40) CHARACTER SET utf8 DEFAULT NULL,
  `lakcim_utcahazszam` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `tartozkodasi_irsz` varchar(5) CHARACTER SET utf8 DEFAULT NULL,
  `tartozkodasi_telepules` varchar(40) CHARACTER SET utf8 DEFAULT NULL,
  `tartozkodasi_utcahazszam` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `atlag` decimal(4,2) DEFAULT NULL,
  `jel_tagozat1` smallint(5) unsigned DEFAULT NULL,
  `jel_tagozat2` smallint(5) unsigned DEFAULT NULL,
  `jel_tagozat3` smallint(5) unsigned DEFAULT NULL,
  `jel_tagozat4` smallint(5) unsigned DEFAULT NULL,
  `jel_tagozat5` smallint(5) unsigned DEFAULT NULL,
  `jel_tagozat6` smallint(5) unsigned DEFAULT NULL,
  `magyar` smallint(5) unsigned NOT NULL DEFAULT '0',
  `matek` smallint(5) unsigned NOT NULL DEFAULT '0',
  `pont` decimal(5,2) DEFAULT NULL,
  `evfolyam` ENUM('4','5','6','8','') DEFAULT NULL,
  `rangsor` smallint(5) unsigned DEFAULT NULL,
  `jelenleg` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `joslat` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `vegeredmeny` varchar(40) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `level1` enum('nincs értesítve','értesítve') CHARACTER SET utf8 DEFAULT NULL,
  `level2` enum('nincs értesítve','nem kell értesíteni','értesítve') CHARACTER SET utf8 DEFAULT NULL,
  `extra` varchar(100) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `felveteliId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`felveteliId`),
  KEY `indx_fa_oId` (`oId`),
  KEY `indx_fa_nev` (`nev`,`oId`),
  KEY `indx_fa_jelige` (`jelige`,`oId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;
END IF;
IF NOT EXISTS (
    SELECT * FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='felveteli_tagozat'
) THEN
CREATE TABLE `felveteli_tagozat` (
  `tagozat` int(11) unsigned NOT NULL,
  `tagozatNev` varchar(64) NOT NULL,
  `szobeliMegjegyzes` varchar(255) DEFAULT NULL,
  `szobeliNelkulAjanlat` varchar(255) DEFAULT NULL,
  `szobeliElutasito` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`tagozat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;
END IF;

IF NOT EXISTS (
    SELECT * FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='felveteli_szobeli'
) THEN
CREATE TABLE `felveteli_szobeli` (
  `felveteliSzobeliId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `oId` bigint(20) NOT NULL,
  `szoveg` varchar(128) CHARACTER SET utf8 DEFAULT NULL,
  `bizottsag` varchar(10) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `nap` varchar(10) CHARACTER SET utf8 DEFAULT NULL,
  `napdt` date DEFAULT NULL,
  `ido` time DEFAULT NULL,
  `tagozat` int(11) unsigned DEFAULT NULL,
  `szobeliTipus` enum ('szóbeli','szóbeli nélküli ajánlat','elutasítás') DEFAULT 'szóbeli',
  PRIMARY KEY (`felveteliSzobeliId`),
  KEY `oId` (`oId`),
  CONSTRAINT `felveteli_szobeli_ibfk_1` FOREIGN KEY (`oId`) REFERENCES `felveteli` (`oId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `felveteli_szobeli_ibfk_2` FOREIGN KEY (`tagozat`) REFERENCES `felveteli_tagozat` (`tagozat`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;
END IF;

IF NOT EXISTS (
    SELECT * FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='felveteli_jelentkezes'
) THEN
CREATE TABLE `felveteli_jelentkezes` (
  `oId` bigint(20) NOT NULL,
  `tagozat` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`oId`,`tagozat`),
  CONSTRAINT `felveteli_jelentkezes_ibfk_1` FOREIGN KEY (`oId`) REFERENCES `felveteli` (`oId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `felveteli_jelentkezes_ibfk_2` FOREIGN KEY (`tagozat`) REFERENCES `felveteli_tagozat` (`tagozat`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;
END IF;


END $$
DELIMITER ;
CALL upgrade_database_4722();
