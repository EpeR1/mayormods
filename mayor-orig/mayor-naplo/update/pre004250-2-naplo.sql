DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4250c $$

CREATE PROCEDURE upgrade_database_4250c()
BEGIN
SET NAMES utf8 COLLATE utf8_hungarian_ci;

IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='ora' AND COLUMN_NAME='modositasDt'
) THEN
    ALTER TABLE `ora` ADD `modositasDt` datetime DEFAULT NULL;
END IF;

END $$
DELIMITER ;
CALL upgrade_database_4250c();
