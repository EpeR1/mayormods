DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4246 $$

CREATE PROCEDURE upgrade_database_4246()
BEGIN
SET NAMES utf8 COLLATE utf8_hungarian_ci;

ALTER TABLE `zaroJegy` MODIFY `modositasDt` datetime NOT NULL;

END $$
DELIMITER ;
CALL upgrade_database_4246();
