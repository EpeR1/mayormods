DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_3744 $$
CREATE PROCEDURE upgrade_database_3744()
BEGIN
IF NOT EXISTS (
    SELECT * FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='tovabbkepzoIntezmeny'
) THEN
CREATE TABLE `tovabbkepzoIntezmeny` (
  `tovabbkepzoIntezmenyId` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `intezmenyRovidNev` varchar(32) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `intezmenyNev` varchar(255) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `intezmenyCim` varchar(255) COLLATE utf8_hungarian_ci DEFAULT NULL,
  PRIMARY KEY (`tovabbkepzoIntezmenyId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;
END IF;
IF NOT EXISTS (
    SELECT * FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='tovabbkepzes'
) THEN
CREATE TABLE `tovabbkepzes` (
  `tovabbkepzesId` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `tovabbkepzesNev` varchar(255) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `tovabbkepzoIntezmenyId` smallint(5) unsigned NOT NULL,
  `oraszam` smallint(5) unsigned NOT NULL,
  `akkreditalt` tinyint unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`tovabbkepzesId`),
  KEY `tovabbkepzes_FK_1` (`tovabbkepzoIntezmenyId`),
  CONSTRAINT `tovabbkepzes_FK_1` FOREIGN KEY (`tovabbkepzoIntezmenyId`) REFERENCES `tovabbkepzoIntezmeny` (`tovabbkepzoIntezmenyId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;
END IF;

IF NOT EXISTS (
    SELECT * FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='tovabbkepzesTanar'
) THEN
CREATE TABLE `tovabbkepzesTanar` (
  `tovabbkepzesId` smallint(5) unsigned NOT NULL,
  `tanarId` int(10) unsigned not null,
  `tolDt` date NOT NULL,
  `igDt` date DEFAULT NULL,
  `tanusitvanyDt` date DEFAULT NULL,
  `tanusitvanySzam` varchar(255) COLLATE utf8_hungarian_ci DEFAULT NULL,
  PRIMARY KEY (`tovabbkepzesId`,`tanarId`),
  CONSTRAINT `tovabbkepzesTanar_ibfk_1` FOREIGN KEY (`tanarId`) REFERENCES `tanar` (`tanarId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tovabbkepzesTanar_ibfk_2` FOREIGN KEY (`tovabbkepzesId`) REFERENCES `tovabbkepzes` (`tovabbkepzesId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;
END IF;

IF NOT EXISTS (
    SELECT * FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='tovabbkepzesTanulmanyiEgyseg'
) THEN
CREATE TABLE `tovabbkepzesTanulmanyiEgyseg` (
  `tovabbkepzesId` smallint(5) unsigned NOT NULL,
  `tanarId` int(10) unsigned not null,
  `naptariEv` YEAR NOT NULL,
  `reszosszeg` int unsigned not null default 0,
  `tamogatas` int unsigned not null default 0,
  `tovabbkepzesStatusz` ENUM('terv','jóváhagyott','elutasított','megszűnt','teljesített') COLLATE utf8_hungarian_ci DEFAULT 'terv',
  `megjegyzes` varchar(255) COLLATE utf8_hungarian_ci DEFAULT NULL,
  PRIMARY KEY (`tovabbkepzesId`,`tanarId`,`naptariEv`),
  CONSTRAINT `tovabbkepzesTE_ibfk_1` FOREIGN KEY (`tanarId`) REFERENCES `tanar` (`tanarId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tovabbkepzesTE_ibfk_2` FOREIGN KEY (`tovabbkepzesId`) REFERENCES `tovabbkepzes` (`tovabbkepzesId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tovabbkepzesTE_ibfk_main` FOREIGN KEY (`tovabbkepzesId`, `tanarId`) REFERENCES `tovabbkepzesTanar` (`tovabbkepzesId`, `tanarId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;
END IF;

END $$
DELIMITER ; $$
CALL upgrade_database_3744();
