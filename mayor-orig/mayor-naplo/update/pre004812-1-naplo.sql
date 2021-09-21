DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4812 $$

CREATE PROCEDURE upgrade_database_4812()
BEGIN
SET NAMES utf8 COLLATE utf8_hungarian_ci;

ALTER TABLE `orarendiOra` MODIFY `targyJel` varchar(96) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL;
ALTER TABLE `orarendiOraTankor` MODIFY `targyJel` varchar(96) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;

END $$
DELIMITER ;
CALL upgrade_database_4812();
