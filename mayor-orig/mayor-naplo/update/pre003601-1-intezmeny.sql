DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_3601 $$
CREATE PROCEDURE upgrade_database_3601()
BEGIN

SET NAMES utf8 COLLATE utf8_hungarian_ci;

IF EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='tanar' AND COLUMN_NAME='besorolas'
) THEN
    alter table tanar modify besorolas enum('Gyakornok','Pedagógus I.','Pedagógus II.','Mesterpedagógus','Kutatótanár') CHARACTER SET utf8 COLLATE utf8_hungarian_ci DEFAULT 'Pedagógus I.';
END IF;
IF EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='tanar' AND COLUMN_NAME='statusz'
) THEN
    alter table tanar modify `statusz` enum('határozatlan idejű','határozott idejű','tartósan távol','jogviszonya lezárva') CHARACTER SET utf8 COLLATE utf8_hungarian_ci DEFAULT 'határozatlan idejű';
END IF;

UPDATE tanar SET besorolas='Pedagógus I.' WHERE besorolas IS NULL;
UPDATE tanar SET statusz='határozatlan idejű' WHERE kiDt IS NULL AND statusz IS NULL;
UPDATE tanar SET statusz='jogviszonya lezárva' WHERE kiDt IS NOT NULL AND statusz IS NULL;

END $$
DELIMITER ;
CALL upgrade_database_3601();

