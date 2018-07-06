CREATE TABLE `koszi` (
  `kosziId` int(10) unsigned NOT NULL auto_increment,
  `kosziEsemenyId` int(10) unsigned NOT NULL,
  `dt` DATE NULL,
  `tanev` smallint(5) unsigned NULL,
  `felev` tinyint(3)  unsigned NULL,
  `igazolo` set('diák','tanár','osztályfőnök') DEFAULT NULL,
  PRIMARY KEY (`kosziId`),
  CONSTRAINT `koszi_ibfk_1` FOREIGN KEY (`kosziEsemenyId`) REFERENCES `%INTEZMENYDB%`.`kosziEsemeny` (`kosziEsemenyId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;
CREATE TABLE `kosziIgazoloDiak` (
  `kosziId` int(10) unsigned NOT NULL,
  `diakId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`kosziId`,`diakId`),
  CONSTRAINT `kosziIgazoloDiak_ibfk_1` FOREIGN KEY (`kosziId`) REFERENCES `koszi` (`kosziId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `kosziIgazoloDiak_ibfk_2` FOREIGN KEY (`diakId`) REFERENCES `%INTEZMENYDB%`.`diak` (`diakId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;
CREATE TABLE `kosziIgazoloTanar` (
  `kosziId` int(10) unsigned NOT NULL,
  `tanarId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`kosziId`,`tanarId`),
  CONSTRAINT `kosziIgazoloTanar_ibfk_1` FOREIGN KEY (`kosziId`) REFERENCES `koszi` (`kosziId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `kosziIgazoloTanar_ibfk_2` FOREIGN KEY (`tanarId`) REFERENCES `%INTEZMENYDB%`.`tanar` (`tanarId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;
CREATE TABLE `kosziIgazoloOf` (
  `kosziId` int(10) unsigned NOT NULL,
  `tanarId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`kosziId`,`tanarId`),
  CONSTRAINT `kosziIgazoloOf_ibfk_1` FOREIGN KEY (`kosziId`) REFERENCES `koszi` (`kosziId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `kosziIgazoloT_ibfk_2` FOREIGN KEY (`tanarId`) REFERENCES `%INTEZMENYDB%`.`tanar` (`tanarId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;
