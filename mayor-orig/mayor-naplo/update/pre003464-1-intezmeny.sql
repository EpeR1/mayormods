DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_3464 $$
CREATE PROCEDURE upgrade_database_3464()
BEGIN
IF EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='zaroJegy' AND COLUMN_NAME='_tanev'
) THEN
    alter table zaroJegy DROP `_tanev`;
END IF;
IF EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='zaroJegy' AND COLUMN_NAME='_szemeszter'
) THEN
    alter table zaroJegy DROP `_szemeszter`;
END IF;

END $$
DELIMITER ; $$
CALL upgrade_database_3464();
