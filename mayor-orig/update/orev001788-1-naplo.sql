
DROP TABLE IF EXISTS oraLatogatasTanar;
DROP TABLE IF EXISTS oraLatogatas;

CREATE TABLE `oraLatogatas` (
  `oraLatogatasId` int(10) unsigned NOT NULL auto_increment,
  `megjegyzes` text collate utf8_hungarian_ci NOT NULL,
  `oraId` int(10) unsigned default NULL,
  PRIMARY KEY  (`oraLatogatasId`),
  UNIQUE KEY `oraId` (`oraId`),
  CONSTRAINT `oraLatogatas_ibfk_1` FOREIGN KEY (`oraId`) REFERENCES `ora` (`oraId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `oraLatogatasTanar` (
  `oraLatogatasId` INTEGER UNSIGNED NOT NULL,
  `tanarId` INT UNSIGNED NULL,
  PRIMARY KEY(`oraLatogatasId`, `tanarId`),
  FOREIGN KEY(`tanarId`)
    REFERENCES %INTEZMENYDB%.tanar(`tanarId`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  FOREIGN KEY(`oraLatogatasId`)
    REFERENCES `oraLatogatas` (`oraLatogatasId`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;
