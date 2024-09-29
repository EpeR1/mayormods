DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4857 $$
CREATE PROCEDURE upgrade_database_4857()
BEGIN

SET NAMES utf8 COLLATE utf8_hungarian_ci;

IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='diak' AND COLUMN_NAME='kretaFelhasznaloId'
) THEN

    alter table `diak` ADD `kretaFelhasznaloId` int(11) DEFAULT NULL AFTER `torzslapszam`;
END IF;

END $$
DELIMITER ;
CALL upgrade_database_4857();
