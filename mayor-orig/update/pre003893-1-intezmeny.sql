DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_3893 $$
CREATE PROCEDURE upgrade_database_3893()
BEGIN
IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='tovabbkepzesTanulmanyiEgyseg' AND COLUMN_NAME='tavollet'
) THEN
ALTER TABLE tovabbkepzesTanulmanyiEgyseg ADD tavollet varchar(255) NOT NULL DEFAULT '';
END IF;
IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='tovabbkepzesTanulmanyiEgyseg' AND COLUMN_NAME='helyettesitesRendje'
) THEN
ALTER TABLE tovabbkepzesTanulmanyiEgyseg ADD helyettesitesRendje varchar(255) NOT NULL DEFAULT '';
END IF;
IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='tovabbkepzesTanulmanyiEgyseg' AND COLUMN_NAME='prioritas'
) THEN
ALTER TABLE tovabbkepzesTanulmanyiEgyseg ADD prioritas varchar(255) NOT NULL DEFAULT '';
END IF;
IF EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='tovabbkepzesTanulmanyiEgyseg' AND COLUMN_NAME='megjegyzes'
) THEN
IF EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='tovabbkepzesTanulmanyiEgyseg' AND COLUMN_NAME='tavollet'
) THEN
    UPDATE tovabbkepzesTanulmanyiEgyseg SET tavollet=megjegyzes;
    ALTER TABLE  tovabbkepzesTanulmanyiEgyseg  DROP megjegyzes;
END IF;
END IF;
END $$
DELIMITER ; $$
CALL upgrade_database_3893();

