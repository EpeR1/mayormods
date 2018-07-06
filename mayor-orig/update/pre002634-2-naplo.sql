CREATE TABLE `kosziDiak` (
  `kosziId` int(10) unsigned NOT NULL,
  `diakId` int(10) unsigned NOT NULL,
  `rogzitesDt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `jovahagyasDt` TIMESTAMP,
  `kosziPontId` int(10) unsigned not null,
  `pont` int(10) unsigned not null,
  PRIMARY KEY (`kosziId`,`diakId`),
  CONSTRAINT `kosziDiak_ibfk_1` FOREIGN KEY (`kosziId`) REFERENCES `koszi` (`kosziId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `kosziDiak_ibfk_2` FOREIGN KEY (`diakId`)  REFERENCES `%INTEZMENYDB%`.`diak` (`diakId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `kosziDiak_ibfk_3` FOREIGN KEY (`kosziPontId`) REFERENCES `%INTEZMENYDB%`.`kosziPont` (`kosziPontId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;
