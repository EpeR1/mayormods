DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4540 $$

CREATE PROCEDURE upgrade_database_4540()
BEGIN
SET NAMES utf8 COLLATE utf8_hungarian_ci;

IF NOT EXISTS (SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='targy' and COLUMN_NAME='kretaTargyNev') THEN
    ALTER TABLE targy ADD `kretaTargyNev` varchar(64) COLLATE utf8_hungarian_ci DEFAULT NULL;
    ALTER TABLE targy ADD UNIQUE INDEX (kretaTargyNev);
END IF;


END $$
DELIMITER ;
CALL upgrade_database_4540();
