DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4441 $$

CREATE PROCEDURE upgrade_database_4441()
BEGIN
SET NAMES utf8 COLLATE utf8_hungarian_ci;

IF NOT EXISTS (
    SELECT * FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='hianyzasKreta'
) THEN
 CREATE TABLE `hianyzasKreta` (
  `kretaHianyzasId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `diakId` int(10) unsigned DEFAULT NULL,
  `tankorId` int(10) unsigned DEFAULT NULL,
  `kretaDiakNev` varchar(255) DEFAULT NULL,
  `oId` bigint(20) unsigned DEFAULT NULL,
  `dt` date DEFAULT NULL,
  `ora` tinyint(3) unsigned DEFAULT NULL,
  `kretaTankorNev` varchar(255) DEFAULT NULL,
  `kretaTantargyNev` varchar(255) DEFAULT NULL,
  `tipus` enum('hiányzás','késés','felszerelés hiány','felmentés','egyenruha hiány') COLLATE utf8_hungarian_ci DEFAULT NULL,
  `perc` tinyint(3) unsigned DEFAULT NULL,
  `kretaStatusz` enum('igen','nem') COLLATE utf8_hungarian_ci DEFAULT NULL,
  `statusz` enum('igazolt','igazolatlan') COLLATE utf8_hungarian_ci DEFAULT NULL,
  `kretaIgazolas` varchar(255) DEFAULT NULL,
  `igazolas` enum('orvosi','szülői','osztályfőnöki','verseny','vizsga','igazgatói','hatósági','pályaválasztás','') COLLATE utf8_hungarian_ci DEFAULT NULL,
  PRIMARY KEY (`kretaHianyzasId`),
  KEY `hianyzasKreta_oId` (`oId`),
  KEY `hianyzasKreta_diakId` (`diakId`),
  KEY `hianyzasKreta_tankorId` (`tankorId`),
  CONSTRAINT `hk_ibfk_1` FOREIGN KEY (`oId`) REFERENCES %INTEZMENYDB%.`diak` (`oId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `hk_ibfk_2` FOREIGN KEY (`diakId`) REFERENCES %INTEZMENYDB%.`diak` (`diakId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `hk_ibfk_3` FOREIGN KEY (`tankorId`) REFERENCES %INTEZMENYDB%.`tankor` (`tankorId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;
END IF;

END $$
DELIMITER ;
CALL upgrade_database_4441();
