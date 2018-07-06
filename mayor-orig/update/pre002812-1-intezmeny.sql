CREATE TABLE IF NOT EXISTS `diakAdatkezeles` (
  `diakId` int(10) unsigned NOT NULL,
  `kulcs` varchar(30) NOT NULL,
  `ertek` varchar(30) NOT NULL,
  `lastModified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY (`diakId`,`kulcs`),
  KEY `diakAdatkezeles_FKIndex1` (`diakId`),
  CONSTRAINT `diakAdatkezeles_ibfk_1` FOREIGN KEY (`diakId`) REFERENCES `diak` (`diakId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;
