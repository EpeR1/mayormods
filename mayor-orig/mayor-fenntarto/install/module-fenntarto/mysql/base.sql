

CREATE DATABASE `%MYSQL_FENNTARTO_DB%` CHARACTER SET utf8 COLLATE utf8_hungarian_ci;
GRANT ALL ON `%MYSQL_FENNTARTO_DB%`.* TO '%MYSQL_FENNTARTO_USER%'@'localhost' IDENTIFIED BY '%MYSQL_FENNTARTO_PW%';

USE `%MYSQL_FENNTARTO_DB%`;

CREATE TABLE `rpcKerelem` (
  `userAccount` varchar(50) NOT NULL default '',
  `nodeId` mediumint(8) unsigned zerofill NOT NULL DEFAULT '00000000',
  `OMKod` mediumint(8) unsigned zerofill NOT NULL DEFAULT '00000000',
  `requ` set('OMKod','Jogosultság','Tantárgyfelosztás') COLLATE utf8_hungarian_ci DEFAULT 'OMKod,Jogosultság',
  PRIMARY KEY (`nodeId`, `userAccount`,`OMKod`),
  CONSTRAINT `nodeId_login` FOREIGN KEY (`nodeId`) REFERENCES `mayor_login`.`mayorKeychain` (`nodeId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

