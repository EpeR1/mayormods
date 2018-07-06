DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_3856 $$
CREATE PROCEDURE upgrade_database_3856()
BEGIN
    IF NOT EXISTS (
	SELECT * FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='facebookConnect'
    ) THEN
    CREATE TABLE `facebookConnect` (
  `fbUserId` bigint(20) NOT NULL,
  `userAccount` varchar(64) COLLATE utf8_hungarian_ci NOT NULL,
  `policy` enum('public','parent','private') COLLATE utf8_hungarian_ci DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;
END IF;
END $$
DELIMITER ; $$
CALL upgrade_database_3856();
