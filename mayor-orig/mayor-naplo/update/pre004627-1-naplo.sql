DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4627 $$

CREATE PROCEDURE upgrade_database_4627()
BEGIN
SET NAMES utf8 COLLATE utf8_hungarian_ci;

IF NOT EXISTS (
    SELECT * FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='oraHazifeladatDiak'
) THEN

CREATE TABLE `oraHazifeladatDiak` (
  `hazifeladatId` int(10) unsigned DEFAULT NULL,
  `diakId` int(10) unsigned DEFAULT NULL,
  `diakLattamDt` datetime DEFAULT NULL,
  `tanarLattamDt` datetime DEFAULT NULL,
  `hazifeladatDiakStatus` ENUM('','kész') DEFAULT '',
  `hazifeladatDiakMegjegyzes` varchar(255) COLLATE utf8_hungarian_ci NOT NULL,
  PRIMARY KEY (`hazifeladatId`,`diakId`),
  UNIQUE KEY `oraHazifeladatDiak_UK` (`hazifeladatId`,`diakId`),
  CONSTRAINT `oraHazifeladatDiak_ibfk_1` FOREIGN KEY (`hazifeladatId`) REFERENCES `oraHazifeladat` (`hazifeladatId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `oraHazifeladatDiak_ibfk_2` FOREIGN KEY (`diakId`) REFERENCES %INTEZMENYDB%.`diak` (`diakId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

END IF;

END $$
DELIMITER ;
CALL upgrade_database_4627();
