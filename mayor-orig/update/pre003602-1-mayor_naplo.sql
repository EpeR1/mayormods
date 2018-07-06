DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_3602 $$
CREATE PROCEDURE upgrade_database_3602()
BEGIN

SET NAMES utf8 COLLATE utf8_hungarian_ci;

update osztalyJelleg set osztalyJellegNev='általános iskola felső tagozat (5-8)' where osztalyJellegId = 23;

END $$
DELIMITER ;
CALL upgrade_database_3602();


