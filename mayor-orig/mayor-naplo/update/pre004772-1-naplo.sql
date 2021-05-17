DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4772 $$

CREATE PROCEDURE upgrade_database_4772()
BEGIN
SET NAMES utf8 COLLATE utf8_hungarian_ci;

IF NOT EXISTS (
    SELECT * FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='felveteli_levelLog'
) THEN

CREATE TABLE `felveteli_levelLog` (
  `oId` bigint(20) NOT NULL,
  `generalasDt` datetime DEFAULT NULL,
  `ip` varchar(32) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `token` varchar(64) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `letoltesDt` datetime DEFAULT NULL,
  KEY `IDX_levelLog_oId` (`oId`),
  CONSTRAINT `felveteli_levelLog_ibfk_1` FOREIGN KEY (`oId`) REFERENCES `felveteli` (`oId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

END IF;

alter table felveteli modify `vegeredmeny` varchar(60)  COLLATE utf8_hungarian_ci DEFAULT NULL;


END $$
DELIMITER ;
CALL upgrade_database_4772();
