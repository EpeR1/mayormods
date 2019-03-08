DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_3703 $$
CREATE PROCEDURE upgrade_database_3703()
BEGIN

SET NAMES utf8 COLLATE utf8_hungarian_ci;

IF EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='tanar' AND COLUMN_NAME='viseltNevElotag'
) THEN
    update tanar set viseltNevElotag='' where viseltNevElotag is null;
    alter table tanar modify `viseltNevElotag` varchar(8) CHARACTER SET utf8 COLLATE utf8_hungarian_ci NOT NULL DEFAULT '';
END IF;
IF EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='tanar' AND COLUMN_NAME='szuleteskoriNevElotag'
) THEN
    update tanar set szuleteskoriNevElotag='' where szuleteskoriNevElotag is null;
    alter table tanar modify `szuleteskoriNevElotag` varchar(8) CHARACTER SET utf8 COLLATE utf8_hungarian_ci NOT NULL DEFAULT '';
END IF;

IF EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='diak' AND COLUMN_NAME='viseltNevElotag'
) THEN
    update diak set viseltNevElotag='' where viseltNevElotag is null;
    alter table diak modify `viseltNevElotag` varchar(8) CHARACTER SET utf8 COLLATE utf8_hungarian_ci NOT NULL DEFAULT '';
END IF;
IF EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='diak' AND COLUMN_NAME='szuleteskoriNevElotag'
) THEN
    update diak set szuleteskoriNevElotag='' where szuleteskoriNevElotag is null;
    alter table diak modify `szuleteskoriNevElotag` varchar(8) CHARACTER SET utf8 COLLATE utf8_hungarian_ci NOT NULL DEFAULT '';
END IF;

IF EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='szulo' AND COLUMN_NAME='nevElotag'
) THEN
    update szulo set nevElotag='' where nevElotag is null;
    alter table szulo modify `nevElotag` varchar(8) CHARACTER SET utf8 COLLATE utf8_hungarian_ci NOT NULL DEFAULT '';
END IF;
IF EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='szulo' AND COLUMN_NAME='szuleteskoriNevElotag'
) THEN
    update szulo set szuleteskoriNevElotag='' where szuleteskoriNevElotag is null;
    alter table szulo modify `szuleteskoriNevElotag` varchar(8) CHARACTER SET utf8 COLLATE utf8_hungarian_ci NOT NULL DEFAULT '';
END IF;


END $$
DELIMITER ;
CALL upgrade_database_3703();

