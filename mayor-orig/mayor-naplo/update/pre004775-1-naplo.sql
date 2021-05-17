DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4775 $$

CREATE PROCEDURE upgrade_database_4775()
BEGIN
SET NAMES utf8 COLLATE utf8_hungarian_ci;

IF NOT EXISTS (
    SELECT * FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='felveteli_iskolak'
) THEN

CREATE TABLE `felveteli_iskolak` (
  `omkod` varchar(7) CHARACTER SET utf8 DEFAULT NULL,
  `iskolaNev` varchar(128) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `iskolaIrsz` varchar(10) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `iskolaCim` varchar(128) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `iskolaTelepules` varchar(64) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `iskolaTelefon` varchar(32) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `iskolaEmail` varchar(64) COLLATE utf8_hungarian_ci DEFAULT NULL,
  KEY `ID_felveteli_iskolak_omkod` (`omkod`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

END IF;

END $$
DELIMITER ;
CALL upgrade_database_4775();
