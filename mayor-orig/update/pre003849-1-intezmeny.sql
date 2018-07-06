DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_3849 $$
CREATE PROCEDURE upgrade_database_3849()
BEGIN

SET NAMES utf8 COLLATE utf8_hungarian_ci;

IF NOT EXISTS (
    SELECT * FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='targyTargy'
) THEN
CREATE TABLE `targyTargy` (
  `foTargyId` smallint(5) unsigned NOT NULL,
  `alTargyId` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`foTargyId`,`alTargyId`),
  KEY `targyTargy_K1` (`foTargyId`),
  KEY `targyTargy_K2` (`alTargyId`),
  CONSTRAINT `targyTargy_ibfk_1` FOREIGN KEY (`foTargyId`) REFERENCES `targy` (`targyId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `targyTargy_ibfk_2` FOREIGN KEY (`alTargyId`) REFERENCES `targy` (`targyId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;
END IF;
END $$
DELIMITER ;
CALL upgrade_database_3849();
