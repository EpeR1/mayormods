DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4179 $$
CREATE PROCEDURE upgrade_database_4179()
BEGIN
    IF NOT EXISTS (
        SELECT * FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='googleConnect'
    ) THEN

CREATE TABLE `googleConnect` (
  `googleSub` varchar(32) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `userAccount` varchar(64) COLLATE utf8_hungarian_ci NOT NULL,
  `policy` enum('public','parent','private') COLLATE utf8_hungarian_ci DEFAULT NULL,
  `googleUserCn` varchar(64) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `googleUserEmail` varchar(64) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `studyId` varchar(12) COLLATE utf8_hungarian_ci DEFAULT NULL,
  UNIQUE KEY `googleSub` (`googleSub`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

END IF;
END $$
DELIMITER ; $$
CALL upgrade_database_4179();
