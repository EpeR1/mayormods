DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_3396b $$
CREATE PROCEDURE upgrade_database_3396b()
BEGIN

IF NOT EXISTS (
    SELECT * FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='mayorUpdateLog'
) THEN
    CREATE TABLE `mayorUpdateLog` (
      `scriptFile` varchar(255) COLLATE utf8_hungarian_ci NOT NULL DEFAULT '',
      `dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`scriptFile`,`dt`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;
END IF;

END $$
CALL upgrade_database_3396b();
