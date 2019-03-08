DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_3531d $$
CREATE PROCEDURE upgrade_database_3531d()
BEGIN
IF EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='kepzesOraszam' AND COLUMN_NAME='evfolyam'
) THEN
    alter table `kepzesOraszam` DROP `evfolyam`;
END IF;
IF EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='kepzesOraterv' AND COLUMN_NAME='evfolyam'
) THEN
    alter table `kepzesOraterv` drop key `kot_kulcs`;
    alter table `kepzesOraterv` DROP `evfolyam`;
END IF;
END $$
DELIMITER ;
CALL upgrade_database_3531d();
