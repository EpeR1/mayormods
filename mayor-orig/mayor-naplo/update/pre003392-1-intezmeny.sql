DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_3392 $$
CREATE PROCEDURE upgrade_database_3392()
BEGIN
IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='zaradek' AND COLUMN_NAME='iktatoszam'
) THEN
    ALTER TABLE zaradek ADD iktatoszam VARCHAR(60) not null default '';
END IF;
END $$
CALL upgrade_database_3392();
