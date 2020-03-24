DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4632 $$

CREATE PROCEDURE upgrade_database_4632()
BEGIN
SET NAMES utf8 COLLATE utf8_hungarian_ci;

IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='oraHazifeladat' and COLUMN_NAME='hazifeladatFeltoltesEngedely'
) THEN

ALTER TABLE `oraHazifeladat` ADD `hazifeladatFeltoltesEngedely` tinyint unsigned DEFAULT 0;

END IF;

END $$
DELIMITER ;
CALL upgrade_database_4632();
