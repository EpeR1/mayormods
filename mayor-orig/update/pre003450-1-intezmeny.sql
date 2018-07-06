
DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_3450 $$
CREATE PROCEDURE upgrade_database_3450()
BEGIN
IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='diak' AND COLUMN_NAME='elozoIskolaOMKod'
) THEN
    alter table diak add column `elozoIskolaOMKod` mediumint(8) unsigned zerofill DEFAULT NULL;
ELSE
    alter table diak modify `elozoIskolaOMKod` mediumint(8) unsigned zerofill DEFAULT NULL;
END IF;
END $$
DELIMITER ; $$
CALL upgrade_database_3450();



