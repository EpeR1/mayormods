DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4364 $$

CREATE PROCEDURE upgrade_database_4364()
BEGIN
SET NAMES utf8 COLLATE utf8_hungarian_ci;

IF NOT EXISTS (SELECT * FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='diakNyelvvizsga') THEN
CREATE TABLE `diakNyelvvizsga` (
  `nyelvvizsgaId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `diakId` int(10) unsigned NOT NULL,
  `idegenNyelv` varchar(64) COLLATE utf8_hungarian_ci NOT NULL,
  `targyId` smallint(5) unsigned NOT NULL,
  `vizsgaSzint` enum('A2 szint (belépő)','B1 szint (alapfok)','B2 szint (középfok)','C1 szint (felsőfok)') COLLATE utf8_hungarian_ci DEFAULT NULL,
  `vizsgaTipus` enum('szóbeli','írásbeli','komplex') COLLATE utf8_hungarian_ci DEFAULT 'komplex',
  `vizsgaDt` date DEFAULT NULL,
  `vizsgaIntezmeny` varchar(64) COLLATE utf8_hungarian_ci NOT NULL,
  `vizsgaBizonyitvanySzam` varchar(32) COLLATE utf8_hungarian_ci NOT NULL,
  PRIMARY KEY (`nyelvvizsgaId`),
  KEY `diakNyelvvizsga_ibfk_1` (`diakId`),
  KEY `diakNyelvvizsga_ibfk_2` (`targyId`),
  CONSTRAINT `diakNyelvvizsga_ibfk_1` FOREIGN KEY (`diakId`) REFERENCES `diak` (`diakId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `diakNyelvvizsga_ibfk_2` FOREIGN KEY (`targyId`) REFERENCES `targy` (`targyId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

END IF;

END $$
DELIMITER ;
CALL upgrade_database_4364();
