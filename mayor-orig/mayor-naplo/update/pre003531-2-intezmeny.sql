DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_3531b $$
CREATE PROCEDURE upgrade_database_3531b()
BEGIN
IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='kepzes' AND COLUMN_NAME='osztalyJellegId'
) THEN
    alter table `kepzes` ADD `osztalyJellegId` tinyint(3) unsigned DEFAULT NULL;
    alter table `kepzes` ADD KEY `kepzes_ibfk_1` (`osztalyJellegId`);
    alter table `kepzes` ADD CONSTRAINT `kepzes_ibfk_1` FOREIGN KEY (`osztalyJellegId`) REFERENCES `mayor_naplo`.`osztalyJelleg` (`osztalyJellegId`)
    ON DELETE SET NULL ON UPDATE CASCADE;
END IF;
END $$
DELIMITER ; $$
CALL upgrade_database_3531b();
