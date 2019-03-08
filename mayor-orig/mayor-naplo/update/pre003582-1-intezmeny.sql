DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_3582 $$
CREATE PROCEDURE upgrade_database_3582()
BEGIN

SET NAMES utf8 COLLATE utf8_hungarian_ci;

IF EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='kepzes' AND COLUMN_NAME='kezdoEvfolyam'
) THEN
    alter table kepzes change `kezdoEvfolyam` `_kezdoEvfolyam` tinyint(3) unsigned DEFAULT NULL;
END IF;
IF EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='kepzes' AND COLUMN_NAME='zaroEvfolyam'
) THEN
    alter table kepzes change `zaroEvfolyam` `_zaroEvfolyam` tinyint(3) unsigned DEFAULT NULL;
END IF;

END $$
DELIMITER ;
CALL upgrade_database_3582();

