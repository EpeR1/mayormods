DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4671 $$

CREATE PROCEDURE upgrade_database_4671()
BEGIN
SET NAMES utf8 COLLATE utf8_hungarian_ci;

IF EXISTS (SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='tanar' and COLUMN_NAME='viseltNevElotag') THEN
    ALTER TABLE `tanar` MODIFY `viseltNevElotag` varchar(8) COLLATE utf8_hungarian_ci DEFAULT NULL;
END IF;

END $$
DELIMITER ;
CALL upgrade_database_4671();
