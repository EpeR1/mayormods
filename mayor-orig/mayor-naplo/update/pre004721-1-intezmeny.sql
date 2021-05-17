DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4721 $$

CREATE PROCEDURE upgrade_database_4721()
BEGIN
SET NAMES utf8 COLLATE utf8_hungarian_ci;

IF NOT EXISTS (SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='tanar' and COLUMN_NAME='elerhetoseg') THEN
    ALTER TABLE `tanar` ADD `elerhetoseg` varchar(64) COLLATE utf8_hungarian_ci DEFAULT NULL;
END IF;
END $$
DELIMITER ;
CALL upgrade_database_4721();
