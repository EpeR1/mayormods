DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_3391 $$
CREATE PROCEDURE upgrade_database_3391()
BEGIN
IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='tankorDiakFelmentes' AND COLUMN_NAME='iktatoszam'
) THEN
    ALTER TABLE tankorDiakFelmentes ADD iktatoszam VARCHAR(60) not null default '';
END IF;
END $$
CALL upgrade_database_3391();
