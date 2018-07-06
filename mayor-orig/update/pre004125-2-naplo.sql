DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4125_2 $$

CREATE PROCEDURE upgrade_database_4125_2()
BEGIN
SET NAMES utf8 COLLATE utf8_hungarian_ci;

IF EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='nap' AND COLUMN_NAME='csengetesiRendTipus'
) THEN
    ALTER TABLE nap MODIFY csengetesiRendTipus ENUM ('normál','rövidített','speciális','rendhagyó','délutáni','délutáni rövidített','délutáni speciális','délutáni rendhagyó','nincs') DEFAULT 'normál';
END IF;

END $$
DELIMITER ;
CALL upgrade_database_4125_2();
