DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_3422 $$
CREATE PROCEDURE upgrade_database_3422()
BEGIN
IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='mayor_naplo' and TABLE_NAME='osztalyJelleg' AND COLUMN_NAME='osztalyJellegEles'
) THEN
    alter table osztalyJelleg add column osztalyJellegEles tinyint unsigned default 1;
END IF;
END $$
CALL upgrade_database_3422();
UPDATE osztalyJelleg SET osztalyJellegEles=1 WHERE osztalyJellegId>=21;
UPDATE osztalyJelleg SET osztalyJellegEles=0 WHERE osztalyJellegId<21;
