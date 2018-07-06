DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_3860 $$
CREATE PROCEDURE upgrade_database_3860()
BEGIN
    IF NOT EXISTS (
	SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='facebookConnect' AND COLUMN_NAME='fbUserCn'
    ) THEN
    ALTER TABLE facebookConnect ADD fbUserCn varchar(64), ADD fbUserEmail varchar(64), ADD studyId varchar(12);
END IF;
END $$
DELIMITER ; $$
CALL upgrade_database_3860();
