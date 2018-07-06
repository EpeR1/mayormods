DELIMITER $$

DROP PROCEDURE IF EXISTS upgrade_database_3282 $$
CREATE PROCEDURE upgrade_database_3282()
BEGIN

IF NOT EXISTS ( 
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='mayor_naplo' and TABLE_NAME='osztalyJelleg' AND COLUMN_NAME='kovOsztalyJellegId'
) THEN
    alter table osztalyJelleg add column `kovOsztalyJellegId` tinyint(3) unsigned DEFAULT NULL;
END IF;

update osztalyJelleg set kovOsztalyJellegId=3 where osztalyJellegId=10;
END $$

CALL upgrade_database_3282();
