
DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_3591 $$
DROP PROCEDURE IF EXISTS upgrade_database_3592 $$

CREATE PROCEDURE upgrade_database_3592()
BEGIN

SET NAMES utf8 COLLATE utf8_hungarian_ci;

IF EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='nap' AND COLUMN_NAME='tipus'
) THEN
    alter table nap modify tipus enum('tanítási nap','speciális tanítási nap','tanítás nélküli munkanap','tanítási szünet','szorgalmi időszakon kívüli munkanap') 
	CHARACTER SET utf8 COLLATE utf8_hungarian_ci DEFAULT 'tanítási nap';
END IF;

END $$
DELIMITER ;
CALL upgrade_database_3592();


