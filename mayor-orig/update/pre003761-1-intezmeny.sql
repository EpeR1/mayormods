DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_3761 $$
CREATE PROCEDURE upgrade_database_3761()
BEGIN
IF NOT EXISTS (
    SELECT * FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='tovabbkepzesKeret'
) THEN
    CREATE TABLE `tovabbkepzesKeret` (naptariEv YEAR NOT NULL PRIMARY KEY, keretOsszeg int unsigned not null);
END IF;

END $$
DELIMITER ; $$
CALL upgrade_database_3761();
