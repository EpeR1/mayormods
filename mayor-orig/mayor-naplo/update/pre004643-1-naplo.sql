DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4643 $$

CREATE PROCEDURE upgrade_database_4643()
BEGIN
SET NAMES utf8 COLLATE utf8_hungarian_ci;

IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='oraHazifeladat' and COLUMN_NAME='hazifeladatHataridoDt'
) THEN

ALTER TABLE `oraHazifeladat` ADD `hazifeladatHataridoDt` datetime DEFAULT NULL;

END IF;


IF NOT EXISTS (
    SELECT * FROM information_schema.statistics WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='oraHazifeladat' and INDEX_NAME = 'hazifeladatHataridoDt'
) THEN

ALTER TABLE `oraHazifeladat` ADD INDEX `hazifeladatHataridoDt` (`hazifeladatHataridoDt`);

END IF;

END $$
DELIMITER ;
CALL upgrade_database_4643();
