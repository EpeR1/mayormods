DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4715 $$

CREATE PROCEDURE upgrade_database_4715()
BEGIN
SET NAMES utf8 COLLATE utf8_hungarian_ci;

ALTER TABLE zaroJegy MODIFY `megjegyzes` enum('dicséret','figyelmeztető','') COLLATE utf8_hungarian_ci DEFAULT NULL;

END $$
DELIMITER ;
CALL upgrade_database_4715();
