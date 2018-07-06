DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_3924 $$
CREATE PROCEDURE upgrade_database_3924()
BEGIN
IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='tanar' AND COLUMN_NAME='tovabbkepzesForduloDt'
) THEN
    alter table tanar ADD tovabbkepzesForduloDt DATE;
END IF;
-- ALTER TABLE tovabbkepzesTanulmanyiEgyseg MODIFY `tovabbkepzesStatusz` enum('terv','jóváhagyott','elutasított','megszűnt','megszakadt','teljesített') COLLATE utf8_hungarian_ci DEFAULT 'terv';
END $$
DELIMITER ; $$
CALL upgrade_database_3924();
