DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4800 $$
CREATE PROCEDURE upgrade_database_4800()
BEGIN

SET NAMES utf8 COLLATE utf8_hungarian_ci;

IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='diak' AND COLUMN_NAME='emailMagan'
) THEN

    alter table `diak` ADD `emailMagan` varchar(255) AFTER `email`;
END IF;

END $$
DELIMITER ;
CALL upgrade_database_4800();
