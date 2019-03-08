DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4247 $$

CREATE PROCEDURE upgrade_database_4247()
BEGIN
SET NAMES utf8 COLLATE utf8_hungarian_ci;

IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='dolgozat' AND COLUMN_NAME='modositasDt'
) THEN
    ALTER TABLE `dolgozat` ADD `modositasDt` datetime NOT NULL;
    UPDATE `dolgozat` SET `modositasDt` = `bejelentesDt` + INTERVAL 12 HOUR;
END IF;

END $$
DELIMITER ;
CALL upgrade_database_4247();
