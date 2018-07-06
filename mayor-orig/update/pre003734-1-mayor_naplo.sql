DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_3734 $$
CREATE PROCEDURE upgrade_database_3734()
BEGIN

SET NAMES utf8 COLLATE utf8_hungarian_ci;

IF EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='intezmeny' AND BINARY(COLUMN_NAME)='fenntartó'
) THEN
    alter table intezmeny drop column `fenntartó`;
END IF;
IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='intezmeny' AND BINARY(COLUMN_NAME)='fenntarto'
) THEN
    alter table intezmeny add column `fenntarto` enum('állami','egyházi','alapítványi','magán','egyéb') COLLATE utf8_hungarian_ci DEFAULT 'állami';
END IF;

END $$
DELIMITER ;
CALL upgrade_database_3734();


