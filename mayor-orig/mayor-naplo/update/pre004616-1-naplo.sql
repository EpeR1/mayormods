DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4616 $$

CREATE PROCEDURE upgrade_database_4616()
BEGIN
SET NAMES utf8 COLLATE utf8_hungarian_ci;

IF NOT EXISTS (
    SELECT * FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='oraHazifeladat'
) THEN

CREATE TABLE `oraHazifeladat` (
  `hazifeladatId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `oraId` int(10) unsigned DEFAULT NULL,
  `hazifeladatLeiras` text COLLATE utf8_hungarian_ci NOT NULL,
  PRIMARY KEY (`hazifeladatId`),
  UNIQUE KEY `oraId` (`oraId`),
  CONSTRAINT `oraHazifeladat_ibfk_1` FOREIGN KEY (`oraId`) REFERENCES `ora` (`oraId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

END IF;

END $$
DELIMITER ;
CALL upgrade_database_4616();
