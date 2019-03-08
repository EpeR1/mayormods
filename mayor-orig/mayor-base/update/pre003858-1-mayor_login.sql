DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_3858 $$
CREATE PROCEDURE upgrade_database_3858()
BEGIN
    IF EXISTS (
	SELECT * FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='facebookConnect'
    ) THEN
    ALTER TABLE facebookConnect ADD UNIQUE INDEX (fbUserId);
END IF;
END $$
DELIMITER ; $$
CALL upgrade_database_3858();
