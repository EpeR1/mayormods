DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_3531c $$
CREATE PROCEDURE upgrade_database_3531c()
BEGIN
IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='kepzesOraszam' AND COLUMN_NAME='evfolyamJel'
) THEN
    alter table `kepzesOraszam` ADD `evfolyamJel` varchar(32) COLLATE utf8_hungarian_ci NOT NULL AFTER evfolyam;
    alter table `kepzesOraszam` MODIFY `evfolyam` tinyint(3) unsigned NULL;
    UPDATE kepzesOraszam SET evfolyamJel=evfolyam;
    alter table `kepzesOraszam` DROP PRIMARY KEY;
    alter table `kepzesOraszam` ADD PRIMARY KEY (`kepzesId`,`evfolyamJel`);
END IF;
END $$
DELIMITER ; $$
CALL upgrade_database_3531c();
