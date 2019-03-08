DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_3812 $$
CREATE PROCEDURE upgrade_database_3812()
BEGIN

SET NAMES utf8 COLLATE utf8_hungarian_ci;

IF EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='tanar' AND COLUMN_NAME='statusz'
) THEN
    alter table tanar modify `statusz` enum('határozatlan idejű','határozott idejű','tartósan távol','jogviszonya lezárva','külső óraadó') COLLATE utf8_hungarian_ci DEFAULT 'határozatlan idejű';
END IF;

END $$
DELIMITER ;
CALL upgrade_database_3812();
