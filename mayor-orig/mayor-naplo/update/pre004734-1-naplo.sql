DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4734 $$

CREATE PROCEDURE upgrade_database_4734()
BEGIN
SET NAMES utf8 COLLATE utf8_hungarian_ci;

IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='felveteli_szobeli' and COLUMN_NAME='szobelipont'
) THEN
    ALTER TABLE felveteli_szobeli ADD szobelipont tinyint UNSIGNED DEFAULT NULL;
END IF;

END $$
DELIMITER ;
CALL upgrade_database_4734();
