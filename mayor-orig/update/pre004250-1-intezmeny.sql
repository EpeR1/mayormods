DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4250 $$

CREATE PROCEDURE upgrade_database_4250()
BEGIN
SET NAMES utf8 COLLATE utf8_hungarian_ci;

IF NOT EXISTS (SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='hirnokFeliratkozas' AND COLUMN_NAME='hirnokFeliratkozasId') THEN
    ALTER TABLE hirnokFeliratkozas ADD COLUMN `hirnokFeliratkozasId` int(11) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
END IF;
END $$
DELIMITER ;
CALL upgrade_database_4250();
