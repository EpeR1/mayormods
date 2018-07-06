DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_3690 $$
CREATE PROCEDURE upgrade_database_3690()
BEGIN

SET NAMES utf8 COLLATE utf8_hungarian_ci;

CREATE TABLE IF NOT EXISTS `mkVezeto` (
  `mkId` smallint(5) unsigned NOT NULL,
  `tanarId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`mkId`,`tanarId`),
  KEY `mkTag_FKIndex1` (`mkId`),
  KEY `mkTag_FKIndex2` (`tanarId`),
  CONSTRAINT `mkVezeto_ibfk_1` FOREIGN KEY (`mkId`) REFERENCES `munkakozosseg` (`mkId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `mkVezeto_ibfk_2` FOREIGN KEY (`tanarId`) REFERENCES `tanar` (`tanarId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;


END $$
DELIMITER ;
CALL upgrade_database_3690();

