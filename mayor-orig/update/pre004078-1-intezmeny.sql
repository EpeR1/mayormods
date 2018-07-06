DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4078 $$

CREATE PROCEDURE upgrade_database_4078()
BEGIN
SET NAMES utf8 COLLATE utf8_hungarian_ci;

IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='csengetesiRend' AND COLUMN_NAME='csengetesiRendTipus'
) THEN
    ALTER TABLE csengetesiRend ADD csengetesiRendTipus ENUM ('normál','rövidített','speciális','rendhagyó','nincs') DEFAULT 'normál';
END IF;
UPDATE csengetesiRend SET csengetesiRendTipus = 'normál' WHERE csengetesiRendTipus='' OR csengetesiRendTipus IS NULL;

END $$
DELIMITER ;
CALL upgrade_database_4078();
