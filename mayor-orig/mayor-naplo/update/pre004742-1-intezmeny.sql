DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4742 $$

CREATE PROCEDURE upgrade_database_4742()
BEGIN
SET NAMES utf8 COLLATE utf8_hungarian_ci;

ALTER TABLE `dokumentum` MODIFY `dokumentumDt` datetime DEFAULT NULL;

END $$
DELIMITER ;
CALL upgrade_database_4742();
