DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4225 $$

CREATE PROCEDURE upgrade_database_4225()
BEGIN
SET NAMES utf8 COLLATE utf8_hungarian_ci;

IF EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='hianyzas' AND COLUMN_NAME='igazolas'
) THEN
    ALTER TABLE `hianyzas` MODIFY `igazolas` enum('orvosi','szülői','osztályfőnöki','tanulmányi verseny','nyelvvizsga','igazgatói','hatósági','pályaválasztás','') COLLATE utf8_hungarian_ci DEFAULT NULL;
END IF;

END $$
DELIMITER ;
CALL upgrade_database_4225();
