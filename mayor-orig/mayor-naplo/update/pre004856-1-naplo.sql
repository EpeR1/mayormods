
DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4856 $$

CREATE PROCEDURE upgrade_database_4856()
BEGIN
SET NAMES utf8 COLLATE utf8_hungarian_ci;

DROP TABLE IF EXISTS felveteli_eredmeny;
CREATE TABLE `felveteli_eredmeny` (
  `nev` varchar(50) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `oId` bigint(20) NOT NULL,
  `evfolyam` enum('4','5','6','8','') COLLATE utf8_hungarian_ci DEFAULT NULL,
  `szulhely` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `szuldt` date DEFAULT NULL,
  `an` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `lakcim_irsz` varchar(5) CHARACTER SET utf8 DEFAULT NULL,
  `lakcim_telepules` varchar(40) CHARACTER SET utf8 DEFAULT NULL,
  `lakcim_utcahazszam` varchar(128) CHARACTER SET utf8 DEFAULT NULL,
  `ert_nev` varchar(50) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `ert_irsz` varchar(5) CHARACTER SET utf8 DEFAULT NULL,
  `ert_telepules` varchar(40) CHARACTER SET utf8 DEFAULT NULL,
  `ert_utcahazszam` varchar(128) CHARACTER SET utf8 DEFAULT NULL,
  `allampolgarsag` varchar(40) CHARACTER SET utf8 DEFAULT 'magyar',
  `email` varchar(128) CHARACTER SET utf8 DEFAULT NULL,
  `omkod` varchar(6) CHARACTER SET utf8 DEFAULT NULL,
  `isk_nev` varchar(128) CHARACTER SET utf8 DEFAULT NULL,
  `isk_irsz` varchar(5) CHARACTER SET utf8 DEFAULT NULL,
  `isk_telepules` varchar(40) CHARACTER SET utf8 DEFAULT NULL,
  `isk_utcahazszam` varchar(128) CHARACTER SET utf8 DEFAULT NULL,
  `isk_tel` varchar(32) CHARACTER SET utf8 DEFAULT NULL,
  `isk_email` varchar(128) CHARACTER SET utf8 DEFAULT NULL,
  `felvett` varchar(5) CHARACTER SET utf8 DEFAULT NULL,
  `mashova` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `helyhiany` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `elutasitott` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `eredmenyId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  UNIQUE KEY `idx_eredmenyId` (`eredmenyId`),
  PRIMARY KEY (`oId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

ALTER TABLE `felveteli` ADD COLUMN `szulhely` VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL AFTER `omkod`;


END $$
DELIMITER ;
CALL upgrade_database_4856();

