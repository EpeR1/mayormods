DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4125 $$

CREATE PROCEDURE upgrade_database_4125()
BEGIN
SET NAMES utf8 COLLATE utf8_hungarian_ci;

IF EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='csengetesiRend' AND COLUMN_NAME='csengetesiRendTipus'
) THEN
    ALTER TABLE csengetesiRend MODIFY csengetesiRendTipus ENUM ('normál','rövidített','speciális','rendhagyó','délutáni','délutáni rövidített','délutáni speciális','délutáni rendhagyó','nincs') DEFAULT 'normál';
END IF;

END $$
DELIMITER ;
CALL upgrade_database_4125();
