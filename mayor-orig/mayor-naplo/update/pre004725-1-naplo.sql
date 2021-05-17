DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4725 $$

CREATE PROCEDURE upgrade_database_4725()
BEGIN
SET NAMES utf8 COLLATE utf8_hungarian_ci;

IF NOT EXISTS (
    SELECT * FROM information_schema.statistics WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='felveteli' and INDEX_NAME = 'indx_fa_oId2'
) THEN
    ALTER TABLE felveteli ADD UNIQUE KEY indx_fa_oId2 (oId);
END IF;

END $$
DELIMITER ;
CALL upgrade_database_4725();
