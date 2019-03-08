DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4412 $$
CREATE PROCEDURE upgrade_database_4412()
BEGIN
    IF NOT EXISTS (
        SELECT * FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='authToken' 
    ) THEN

CREATE TABLE `authToken` (
  `tokenId` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `policy` enum('private','parent','public') COLLATE utf8_hungarian_ci NOT NULL,
  `userAccount` varchar(32) COLLATE utf8_hungarian_ci NOT NULL,
  `userCn` varchar(64) COLLATE utf8_hungarian_ci NOT NULL,
  `studyId` bigint(20) unsigned DEFAULT NULL,
  `selector` char(16) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `token` char(64) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `expires` datetime DEFAULT NULL,
  `ipAddress` varchar(64) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `activity` datetime DEFAULT NULL,
  PRIMARY KEY (`tokenId`),
  UNIQUE KEY `selector` (`selector`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

END IF;
END $$
DELIMITER ; $$
CALL upgrade_database_4412();
