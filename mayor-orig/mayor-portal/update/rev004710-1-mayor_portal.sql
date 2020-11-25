DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4710 $$

CREATE PROCEDURE upgrade_database_4710()
BEGIN
SET NAMES utf8 COLLATE utf8_hungarian_ci;

ALTER TABLE `hirek` MODIFY `owner` varchar(255) DEFAULT NULL;

END $$
DELIMITER ;
CALL upgrade_database_4710();
