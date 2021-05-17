DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4737 $$

CREATE PROCEDURE upgrade_database_4737()
BEGIN
SET NAMES utf8 COLLATE utf8_hungarian_ci;

IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='felveteli_jelentkezes' and COLUMN_NAME='ideiglenesRangsor'
) THEN
    ALTER TABLE felveteli_jelentkezes ADD ideiglenesRangsor mediumint unsigned not null;
END IF;
ALTER TABLE felveteli MODIFY joslat varchar(250);


END $$
DELIMITER ;
CALL upgrade_database_4737();
