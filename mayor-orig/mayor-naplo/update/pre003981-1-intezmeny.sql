DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_3981 $$
CREATE PROCEDURE upgrade_database_3981()
BEGIN
IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='feladatTipus' AND COLUMN_NAME='feladatTipusId'
) THEN
    REPLACE INTO feladatTipus (feladatTipusId,feladatTipusLeiras,beszamithatoMaxOra) VALUES (230,'továbbképzés - felkészülés minősítésre, ellenőrzésre',10);
END IF;
END $$
DELIMITER ; $$
CALL upgrade_database_3981();
