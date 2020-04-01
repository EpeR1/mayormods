DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4644 $$

CREATE PROCEDURE upgrade_database_4644()
BEGIN
SET NAMES utf8 COLLATE utf8_hungarian_ci;

IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='oraHazifeladatDiak' and COLUMN_NAME='hazifeladatDiakFileDt'
) THEN
ALTER TABLE `oraHazifeladatDiak` ADD `hazifeladatDiakFileDt` datetime DEFAULT NULL;
UPDATE `oraHazifeladatDiak` SET `hazifeladatDiakFileDt`=`diakLattamDt` WHERE `hazifeladatDiakFilename`!='';
END IF;

END $$
DELIMITER ;
CALL upgrade_database_4644();
