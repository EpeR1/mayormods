DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4149 $$

CREATE PROCEDURE upgrade_database_4149()
BEGIN
SET NAMES utf8 COLLATE utf8_hungarian_ci;

IF EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='hianyzasHozott' AND COLUMN_NAME='dbHianyzas'
) THEN
    ALTER TABLE hianyzasHozott MODIFY dbHianyzas smallint unsigned DEFAULT NULL;
END IF;

END $$
DELIMITER ;
CALL upgrade_database_4149();
