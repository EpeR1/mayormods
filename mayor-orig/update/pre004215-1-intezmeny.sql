DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4215 $$

CREATE PROCEDURE upgrade_database_4215()
BEGIN
SET NAMES utf8 COLLATE utf8_hungarian_ci;

IF NOT EXISTS (SELECT * FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='hirnokFeliratkozas') THEN
CREATE TABLE `hirnokFeliratkozas` (
  `naploId` int(10) unsigned NOT NULL,
  `naploTipus` enum('diak','tanar','szulo') COLLATE utf8_hungarian_ci DEFAULT NULL,
  `userAccount` varchar(32) COLLATE utf8_hungarian_ci NOT NULL,
  `policy` enum('private','parent','public') COLLATE utf8_hungarian_ci DEFAULT 'private',
  `email` varchar(64) COLLATE utf8_hungarian_ci NOT NULL,
  `feliratkozasDt` datetime DEFAULT NULL,
  `utolsoEmailDt` datetime DEFAULT NULL,
  `megtekintesDt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;
END IF;

END $$
DELIMITER ;
CALL upgrade_database_4215();
