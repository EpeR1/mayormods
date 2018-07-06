DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_3580 $$
CREATE PROCEDURE upgrade_database_3580()
BEGIN

SET NAMES utf8 COLLATE utf8_hungarian_ci;

IF EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='osztaly' AND COLUMN_NAME='kezdoEvfolyam'
) THEN
    alter table osztaly change `kezdoEvfolyam` `_kezdoEvfolyam` tinyint(3) unsigned DEFAULT NULL;
END IF;
IF EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='szempontRendszer' AND COLUMN_NAME='evfolyam'
) THEN
    alter table szempontRendszer change `evfolyam` `_evfolyam` tinyint(5) unsigned NOT NULL;
END IF;
IF EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='tanmenet' AND COLUMN_NAME='evfolyam'
) THEN
    alter table tanmenet change `evfolyam` `_evfolyam` tinyint(5) unsigned NOT NULL;
END IF;


IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='osztaly' AND COLUMN_NAME='kezdoEvfolyamSorszam'
) THEN
    alter table osztaly add column kezdoEvfolyamSorszam tinyint unsigned default 1;
END IF;
IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='szempontRendszer' AND COLUMN_NAME='evfolyamJel'
) THEN
    alter table szempontRendszer add column evfolyamJel varchar(32) CHARACTER SET utf8 COLLATE utf8_hungarian_ci DEFAULT NULL;
    update szempontRendszer set evfolyamJel = _evfolyam;
ELSE
    alter table szempontRendszer modify evfolyamJel varchar(32) CHARACTER SET utf8 COLLATE utf8_hungarian_ci DEFAULT NULL;
    update szempontRendszer set evfolyamJel = _evfolyam where evfolyamJel is null;
END IF;
IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='tanmenet' AND COLUMN_NAME='evfolyamJel'
) THEN
    alter table tanmenet add column evfolyamJel varchar(32) CHARACTER SET utf8 COLLATE utf8_hungarian_ci DEFAULT NULL;
    update tanmenet set evfolyamJel = _evfolyam;
ELSE
    alter table tanmenet modify evfolyamJel varchar(32) CHARACTER SET utf8 COLLATE utf8_hungarian_ci DEFAULT NULL;
    update tanmenet set evfolyamJel = _evfolyam where evfolyamJel is null;
END IF;

END $$
DELIMITER ;
CALL upgrade_database_3580();
