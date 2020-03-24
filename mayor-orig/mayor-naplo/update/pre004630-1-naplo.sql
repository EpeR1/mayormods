DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4630 $$

CREATE PROCEDURE upgrade_database_4630()
BEGIN
SET NAMES utf8 COLLATE utf8_hungarian_ci;

IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='oraHazifeladatDiak' and COLUMN_NAME='hazifeladatDiakFilename'
) THEN

ALTER TABLE `oraHazifeladatDiak` ADD `hazifeladatDiakFilename` varchar(255) COLLATE utf8_hungarian_ci DEFAULT NULL;

END IF;

IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='oraHazifeladatDiak' and COLUMN_NAME='hazifeladatDiakOrigFilename'
) THEN

ALTER TABLE `oraHazifeladatDiak` ADD `hazifeladatDiakOrigFilename` varchar(255) COLLATE utf8_hungarian_ci DEFAULT NULL;

END IF;

END $$
DELIMITER ;
CALL upgrade_database_4630();
