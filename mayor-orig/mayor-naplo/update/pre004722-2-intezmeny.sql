DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4722b $$

CREATE PROCEDURE upgrade_database_4722b()
BEGIN
SET NAMES utf8 COLLATE utf8_hungarian_ci;

ALTER TABLE `idoszak` MODIFY `tipus` enum('zárás','bizonyítvány írás','vizsga','előzetes tárgyválasztás','tárgyválasztás','tankörnévsor módosítás','fogadóóra jelentkezés','tanmenet leadás','felvételi szóbeli lekérdezés','felvételi ideiglenes rangsor lekérdezés','felvételi végeredmény lekérdezés') COLLATE utf8_hungarian_ci DEFAULT NULL;

END $$
DELIMITER ;
CALL upgrade_database_4722b();
