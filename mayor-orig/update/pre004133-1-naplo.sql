DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4133 $$

CREATE PROCEDURE upgrade_database_4133()
BEGIN
SET NAMES utf8 COLLATE utf8_hungarian_ci;

IF NOT EXISTS (
    SELECT * FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='jegyzet'
) THEN
 CREATE TABLE `jegyzet` (
  `jegyzetId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(10) unsigned NOT NULL,
  `userTipus` enum('diak','tanar','szulo') COLLATE utf8_hungarian_ci DEFAULT NULL,
  `dt` DATE not null,
  `jegyzetLeiras` text COLLATE utf8_hungarian_ci,
  `publikus` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`jegyzetId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;
END IF;

IF NOT EXISTS (
    SELECT * FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='jegyzetTankor'
) THEN
 CREATE TABLE `jegyzetTankor` (
  `jegyzetId` int(10) unsigned NOT NULL,
  `tankorId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`jegyzetId`,`tankorId`),
  KEY `jegyzetTankor_jegyzetId` (`jegyzetId`),
  KEY `jegyzetTankor_tankorId` (`tankorId`),
  CONSTRAINT `jegyzetTankor_ibfk_1` FOREIGN KEY (`jegyzetId`) REFERENCES `jegyzet` (`jegyzetId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `jegyzetTankor_ibfk_2` FOREIGN KEY (`tankorId`) REFERENCES %INTEZMENYDB%.`tankor` (`tankorId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;
END IF;

IF NOT EXISTS (
    SELECT * FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='jegyzetOsztaly'
) THEN
 CREATE TABLE `jegyzetOsztaly` (
  `jegyzetId` int(10) unsigned NOT NULL,
  `osztalyId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`jegyzetId`,`osztalyId`),
  KEY `jegyzetOsztaly_jegyzetId` (`jegyzetId`),
  KEY `jegyzetOsztaly_osztalyId` (`osztalyId`),
  CONSTRAINT `jegyzetOsztaly_ibfk_1` FOREIGN KEY (`jegyzetId`) REFERENCES `jegyzet` (`jegyzetId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `jegyzetOsztaly_ibfk_2` FOREIGN KEY (`osztalyId`) REFERENCES %INTEZMENYDB%.`osztaly` (`osztalyId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;
END IF;

IF NOT EXISTS (
    SELECT * FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='jegyzetMunkakozosseg'
) THEN
 CREATE TABLE `jegyzetMunkakozosseg` (
  `jegyzetId` int(10) unsigned NOT NULL,
  `mkId` smallint(5) unsigned,
  PRIMARY KEY (`jegyzetId`,`mkId`),
  KEY `jegyzetMunkakozosseg_jegyzetId` (`jegyzetId`),
  KEY `jegyzetMunkakozosseg_mkId` (`mkId`),
  CONSTRAINT `jegyzetMunkakozosseg_ibfk_1` FOREIGN KEY (`jegyzetId`) REFERENCES `jegyzet` (`jegyzetId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `jegyzetMunkakozosseg_ibfk_2` FOREIGN KEY (`mkId`) REFERENCES %INTEZMENYDB%.`munkakozosseg` (`mkId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;
END IF;

END $$
DELIMITER ;
CALL upgrade_database_4133();
