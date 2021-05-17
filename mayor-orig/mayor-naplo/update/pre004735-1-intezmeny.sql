DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4735 $$

CREATE PROCEDURE upgrade_database_4735()
BEGIN
SET NAMES utf8 COLLATE utf8_hungarian_ci;

IF NOT EXISTS (SELECT * FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='dokumentum') THEN
CREATE TABLE `dokumentum` (
  `dokumentumId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `dokumentumLeiras` varchar(511) NOT NULL,
  `dokumentumRovidLeiras` varchar(255) NOT NULL,
  `dokumentumUrl` varchar(1023) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `dokumentumMegjegyzes` varchar(63) NOT NULL,
  `dokumentumSorrend` SMALLINT DEFAULT 1,
  `dokumentumDt` datetime DEFAULT NULL,
  PRIMARY KEY (`dokumentumId`),
  INDEX (`dokumentumSorrend`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;
END IF;

END $$
DELIMITER ;
CALL upgrade_database_4735();
