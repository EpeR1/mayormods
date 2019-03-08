DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_3597 $$
CREATE PROCEDURE upgrade_database_3597()
BEGIN

SET NAMES utf8 COLLATE utf8_hungarian_ci;

IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='tanar' AND COLUMN_NAME='besorolas'
) THEN
    alter table tanar add column besorolas enum('Gyakornok','Pedagógus I.','Pedagógus II.','Mesterpedagógus','Kutatótanár') CHARACTER SET utf8 COLLATE utf8_hungarian_ci;
ELSE
    alter table tanar modify column besorolas enum('Gyakornok','Pedagógus I.','Pedagógus II.','Mesterpedagógus','Kutatótanár') CHARACTER SET utf8 COLLATE utf8_hungarian_ci;
END IF;

END $$
DELIMITER ;
CALL upgrade_database_3597();

