DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4066 $$

CREATE PROCEDURE upgrade_database_4066()
BEGIN
SET NAMES utf8 COLLATE utf8_hungarian_ci;

IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='jegy' AND COLUMN_NAME='modositasDt'
) THEN
    ALTER TABLE jegy ADD modositasDt DATETIME NOT NULL;
    UPDATE jegy set modositasDt = dt WHERE modositasDt = '';
END IF;

END $$
DELIMITER ;
CALL upgrade_database_4066();
