
DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_3562 $$
CREATE PROCEDURE upgrade_database_3562()
BEGIN
    DROP TABLE IF EXISTS kepzesTargyOraszam;
END $$
DELIMITER ;

CALL upgrade_database_3562();
