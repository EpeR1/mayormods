DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_3531 $$
CREATE PROCEDURE upgrade_database_3531()
BEGIN
IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='kepzesOraterv' AND COLUMN_NAME='evfolyamJel'
) THEN
    alter table `kepzesOraterv` ADD `evfolyamJel` varchar(32) DEFAULT NULL AFTER evfolyam;
    update kepzesOraterv SET evfolyamJel=evfolyam;
    alter table `kepzesOraterv` ADD UNIQUE KEY `kot_kulcs2` (`kepzesId`,`targyId`,`evfolyamJel`,`szemeszter`);
END IF;
END $$
DELIMITER ; $$
CALL upgrade_database_3531();
