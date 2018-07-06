DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_3604 $$
CREATE PROCEDURE upgrade_database_3604()
BEGIN

SET NAMES utf8 COLLATE utf8_hungarian_ci;

IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='mayor_naplo' and TABLE_NAME='intezmeny' AND COLUMN_NAME='fenntarto'
) THEN
    alter table `intezmeny` add column `fenntarto` enum('állami','egyházi','alapítványi','magán','egyéb') CHARACTER SET utf8 COLLATE utf8_hungarian_ci DEFAULT 'állami';
ELSE
    alter table `intezmeny` modify `fenntarto` enum('állami','egyházi','alapítványi','magán','egyéb') CHARACTER SET utf8 COLLATE utf8_hungarian_ci DEFAULT 'állami';
END IF;

END $$
DELIMITER ;
CALL upgrade_database_3604();


