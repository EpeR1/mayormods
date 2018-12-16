DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4388 $$
CREATE PROCEDURE upgrade_database_4388()
BEGIN
    IF NOT EXISTS (
        SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='eduroam' AND COLUMN_NAME='eduroamDOMAIN'
    ) THEN
ALTER TABLE `eduroam` ADD `eduroamDOMAIN` varchar(128) COLLATE utf8_hungarian_ci NOT NULL;
END IF;
END $$
DELIMITER ; $$
CALL upgrade_database_4388();
