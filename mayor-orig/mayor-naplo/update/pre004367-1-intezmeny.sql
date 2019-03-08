DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4367 $$

CREATE PROCEDURE upgrade_database_4367()
BEGIN
SET NAMES utf8 COLLATE utf8_hungarian_ci;


IF NOT EXISTS (SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='tankor' and COLUMN_NAME='tankorCn') THEN
    ALTER TABLE `tankor` ADD `tankorCn` varchar(64) COLLATE utf8_hungarian_ci DEFAULT NULL;
END IF;
IF EXISTS (SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='tankor' and COLUMN_NAME='cn') THEN
    UPDATE `tankor` SET `tankorCn` = `cn`;
    ALTER TABLE `tankor` DROP `cn`;
END IF;

END $$
DELIMITER ;
CALL upgrade_database_4367();
