DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4531_2 $$

CREATE PROCEDURE upgrade_database_4531_2()
BEGIN
SET NAMES utf8 COLLATE utf8_hungarian_ci;

IF NOT EXISTS (SELECT * FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='oraCimke') THEN
 CREATE TABLE `oraCimke` (
  `oraId` int(10) unsigned NOT NULL,
  `cimkeId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`oraId`,`cimkeId`),
  CONSTRAINT `oraCimke_ibfk_1` FOREIGN KEY (`oraId`) REFERENCES `ora` (`oraId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `oraCimke_ibfk_2` FOREIGN KEY (`cimkeId`) REFERENCES %INTEZMENYDB%.`cimke` (`cimkeId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;
END IF;

END $$
DELIMITER ;
CALL upgrade_database_4531_2();
