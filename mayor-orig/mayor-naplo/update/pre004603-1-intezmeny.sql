DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4603 $$

CREATE PROCEDURE upgrade_database_4603()
BEGIN
SET NAMES utf8 COLLATE utf8_hungarian_ci;

IF EXISTS (SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='tankor' and COLUMN_NAME='tankorNevExtra') THEN
    ALTER TABLE `tankor` MODIFY `tankorNevExtra` varchar(64) COLLATE utf8_hungarian_ci DEFAULT NULL;
END IF;

END $$
DELIMITER ;
CALL upgrade_database_4603();
