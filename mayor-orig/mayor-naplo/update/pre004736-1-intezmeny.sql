DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4736 $$

CREATE PROCEDURE upgrade_database_4736()
BEGIN
SET NAMES utf8 COLLATE utf8_hungarian_ci;

IF NOT EXISTS (SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='dokumentum' AND COLUMN_NAME='dokumentumPolicy') THEN
ALTER TABLE `dokumentum` ADD dokumentumPolicy ENUM('public','parent','private');
END IF;

IF NOT EXISTS (SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='dokumentum' AND COLUMN_NAME='dokumentumTipus') THEN
ALTER TABLE `dokumentum` ADD dokumentumTipus ENUM('general','tanev');
END IF;

END $$
DELIMITER ;
CALL upgrade_database_4736();
