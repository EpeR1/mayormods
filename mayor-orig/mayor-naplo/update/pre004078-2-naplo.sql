DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4078_2 $$

CREATE PROCEDURE upgrade_database_4078_2()
BEGIN
SET NAMES utf8 COLLATE utf8_hungarian_ci;

IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='nap' AND COLUMN_NAME='csengetesiRendTipus'
) THEN
    ALTER TABLE nap ADD csengetesiRendTipus ENUM ('normál','rövidített','speciális','rendhagyó','nincs') DEFAULT NULL;
END IF;
UPDATE nap SET csengetesiRendTipus = 'normál' WHERE (csengetesiRendTipus='' OR csengetesiRendTipus IS NULL) AND tipus = 'tanítási nap';
UPDATE nap SET csengetesiRendTipus = 'nincs' WHERE (csengetesiRendTipus='' OR csengetesiRendTipus IS NULL);

END $$
DELIMITER ;
CALL upgrade_database_4078_2();
