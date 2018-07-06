DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4329 $$

CREATE PROCEDURE upgrade_database_4329()
BEGIN
SET NAMES utf8 COLLATE utf8_hungarian_ci;

ALTER TABLE `hianyzas` MODIFY `igazolas` enum('orvosi','szülői','osztályfőnöki','verseny','tanulmányi verseny','vizsga','nyelvvizsga','igazgatói','hatósági','pályaválasztás','') COLLATE utf8_hungarian_ci DEFAULT NULL;
UPDATE hianyzas SET igazolas='verseny' WHERE igazolas='tanulmányi verseny';
UPDATE hianyzas SET igazolas='vizsga' WHERE igazolas='nyelvvizsga';
ALTER TABLE hianyzas MODIFY `igazolas` enum('orvosi','szülői','osztályfőnöki','verseny','vizsga','igazgatói','hatósági','pályaválasztás','') COLLATE utf8_hungarian_ci DEFAULT NULL;

END $$
DELIMITER ;
CALL upgrade_database_4329();
