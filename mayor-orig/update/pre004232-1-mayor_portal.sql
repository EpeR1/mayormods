DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4232 $$
CREATE PROCEDURE upgrade_database_4232()
BEGIN

SET NAMES utf8 COLLATE utf8_hungarian_ci;

IF EXISTS (
    SELECT * FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='nevnap'
) THEN
    update nevnap set nevnap='Gyöngyi' where honap=10 and nap=23;
END IF;

END $$
DELIMITER ;
CALL upgrade_database_4232();
