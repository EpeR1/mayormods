DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4263 $$

CREATE PROCEDURE upgrade_database_4263()
BEGIN
SET NAMES utf8 COLLATE utf8_hungarian_ci;

IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='hianyzas' AND COLUMN_NAME='modositasDt'
) THEN
    ALTER TABLE `hianyzas` ADD `modositasDt` datetime DEFAULT NULL;
    UPDATE `hianyzas` SET `modositasDt` = `dt` WHERE `modositasDt` is NULL;
END IF;

END $$
DELIMITER ;
CALL upgrade_database_4263();
