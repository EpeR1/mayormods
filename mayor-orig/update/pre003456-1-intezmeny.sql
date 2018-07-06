
DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_3456 $$
CREATE PROCEDURE upgrade_database_3456()
BEGIN

IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='zaroJegy' AND COLUMN_NAME='evfolyamJel'
) THEN
    alter table zaroJegy add column `evfolyamJel` varchar(32) COLLATE utf8_hungarian_ci DEFAULT NULL AFTER `evfolyam`;
ELSE
    alter table zaroJegy modify `evfolyamJel` varchar(32) COLLATE utf8_hungarian_ci DEFAULT NULL;
END IF;

IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='vizsga' AND COLUMN_NAME='evfolyamJel'
) THEN
    alter table vizsga add column `evfolyamJel` varchar(32) COLLATE utf8_hungarian_ci DEFAULT NULL AFTER `evfolyam`;
ELSE
    alter table vizsga modify `evfolyamJel` varchar(32) COLLATE utf8_hungarian_ci DEFAULT NULL;
END IF;

END $$
DELIMITER ; $$
CALL upgrade_database_3456();



