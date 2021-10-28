DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4819 $$

CREATE PROCEDURE upgrade_database_4819()
BEGIN
SET NAMES utf8 COLLATE utf8_hungarian_ci;

ALTER TABLE `szeEgyediMinosites` MODIFY `egyediMinosites` text CHARACTER SET utf8 COLLATE utf8_hungarian_ci DEFAULT NULL;

END $$
DELIMITER ;
CALL upgrade_database_4819();
