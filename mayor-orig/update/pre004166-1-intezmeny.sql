DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4166 $$

CREATE PROCEDURE upgrade_database_4166()
BEGIN
SET NAMES utf8 COLLATE utf8_hungarian_ci;

IF NOT EXISTS (SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='hianyzasOsszesites' AND COLUMN_NAME='gyakorlatIgazolt') THEN
    ALTER TABLE hianyzasOsszesites ADD COLUMN `gyakorlatIgazolt` smallint(5) unsigned DEFAULT NULL;
END IF;
IF NOT EXISTS (SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='hianyzasOsszesites' AND COLUMN_NAME='gyakorlatIgazolatlan') THEN
    ALTER TABLE hianyzasOsszesites ADD COLUMN  `gyakorlatIgazolatlan` smallint(5) unsigned DEFAULT NULL;
END IF;
IF NOT EXISTS (SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='hianyzasOsszesites' AND COLUMN_NAME='gyakorlatKesesPercOsszeg') THEN
    ALTER TABLE hianyzasOsszesites ADD COLUMN  `gyakorlatKesesPercOsszeg` smallint(5) unsigned DEFAULT NULL;
END IF;
IF NOT EXISTS (SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='hianyzasOsszesites' AND COLUMN_NAME='elmeletIgazolt') THEN
    ALTER TABLE hianyzasOsszesites ADD COLUMN  `elmeletIgazolt` smallint(5) unsigned DEFAULT NULL;
END IF;
IF NOT EXISTS (SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='hianyzasOsszesites' AND COLUMN_NAME='elmeletIgazolatlan') THEN
    ALTER TABLE hianyzasOsszesites ADD COLUMN  `elmeletIgazolatlan` smallint(5) unsigned DEFAULT NULL;
END IF;
IF NOT EXISTS (SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='hianyzasOsszesites' AND COLUMN_NAME='elmeletKesesPercOsszeg') THEN
    ALTER TABLE hianyzasOsszesites ADD COLUMN  `elmeletKesesPercOsszeg` smallint(5) unsigned DEFAULT NULL;
END IF;

END $$
DELIMITER ;
CALL upgrade_database_4166();
