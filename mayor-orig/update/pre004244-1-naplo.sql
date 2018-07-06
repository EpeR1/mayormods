DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4244 $$

CREATE PROCEDURE upgrade_database_4244()
BEGIN
SET NAMES utf8 COLLATE utf8_hungarian_ci;

IF EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='hianyzas' AND COLUMN_NAME='tipus'
) THEN
    ALTER TABLE `hianyzas` MODIFY `tipus` enum('hiányzás','késés','felszerelés hiány','felmentés','egyenruha hiány') COLLATE utf8_hungarian_ci DEFAULT NULL;
END IF;

END $$
DELIMITER ;
CALL upgrade_database_4244();
