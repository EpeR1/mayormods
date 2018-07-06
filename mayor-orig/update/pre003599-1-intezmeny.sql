DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_3599 $$
CREATE PROCEDURE upgrade_database_3599()
BEGIN

SET NAMES utf8 COLLATE utf8_hungarian_ci;

DROP TABLE IF EXISTS `tanarKepesites`;
DROP TABLE IF EXISTS `kepesitesTargy`;
DROP TABLE IF EXISTS `kepesites`;

CREATE TABLE `kepesites` (
  `kepesitesId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `vegzettseg` enum('','alapfokú','középfokú','felsőfokú') CHARACTER SET utf8 COLLATE utf8_hungarian_ci DEFAULT NULL,
  `fokozat` enum('','főiskolai','egyetemi','alapfokozat','mesterfokozat','tudományos fokozat') CHARACTER SET utf8 COLLATE utf8_hungarian_ci DEFAULT NULL,
  `specializacio` enum('','alapfokú szakképesítés','középfokú szakképesítés','emelt szintű szakképesítés','felsőfokú szakképesítés','szakképzettség') CHARACTER SET utf8 COLLATE utf8_hungarian_ci DEFAULT NULL,
  `kepesitesNev` varchar(255) CHARACTER SET utf8 COLLATE utf8_hungarian_ci DEFAULT NULL,
  PRIMARY KEY (`kepesitesId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

INSERT INTO `kepesites` (`vegzettseg`, `fokozat`, `specializacio`, `kepesitesNev`) VALUES
('alapfokú','','','általános iskola'),
('középfokú','','','érettségi');

CREATE TABLE `kepesitesTargy` (
  `kepesitesId` int(10) unsigned NOT NULL,
  `targyId` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`kepesitesId`,`targyId`),
  CONSTRAINT `kepesitesTargy_ibfk_1` FOREIGN KEY (`kepesitesId`) REFERENCES `kepesites` (`kepesitesId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `kepesitesTargy_ibfk_2` FOREIGN KEY (`targyId`) REFERENCES `targy` (`targyId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `tanarKepesites` (
  `tanarId` int(10) unsigned NOT NULL,
  `kepesitesId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`tanarId`,`kepesitesId`),
  CONSTRAINT `tanarKepesites_ibfk_1` FOREIGN KEY (`tanarId`) REFERENCES `tanar` (`tanarId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tanarKepesites_ibfk_2` FOREIGN KEY (`kepesitesId`) REFERENCES `kepesites` (`kepesitesId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;


END $$
DELIMITER ;
CALL upgrade_database_3599();

