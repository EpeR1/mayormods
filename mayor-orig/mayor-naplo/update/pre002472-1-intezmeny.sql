-- jelenleg 2472
CREATE TABLE `tanmenet` (
  `tanmenetId` int(10) unsigned NOT NULL auto_increment,
  `targyId` smallint(5) unsigned NOT NULL,
  `evfolyam` tinyint(3) unsigned NOT NULL,
  `tanmenetNev` varchar(128),
  `oraszam` smallint unsigned,
  `dt` DATE NOT NULL,
  `tanarId` int(10) unsigned NOT NULL,
  `jovahagyva` tinyint(3) unsigned DEFAULT '0',
  PRIMARY KEY (`tanmenetId`),
  CONSTRAINT `tanmenet_ibfk_1` FOREIGN KEY (`targyId`) REFERENCES `targy` (`targyId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tanmenet_ibfk_2` FOREIGN KEY (`tanarId`) REFERENCES `tanar` (`tanarId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `tanmenetTemakor` (
  `tanmenetId` int(10) unsigned NOT NULL,
  `sorszam` tinyint unsigned NOT NULL,
  `oraszam` tinyint unsigned NOT NULL,
  `temakorMegnevezes` varchar(255),
  PRIMARY KEY (`tanmenetId`, `sorszam` ),
  CONSTRAINT `tanmenetTemakor_ibfk_1` FOREIGN KEY (`tanmenetId`) REFERENCES `tanmenet` (`tanmenetId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `tanmenetTankor` (
  `tankorId` int(10) unsigned NOT NULL,
  `tanev` smallint(5) unsigned NOT NULL,
  `tanmenetId` int(10) unsigned NOT NULL,
  KEY `tanmenetTankor_FKIndex1` (`tankorId`),
  CONSTRAINT `tanmenetTankor_ibfk_1` FOREIGN KEY (`tankorId`) REFERENCES `tankor` (`tankorId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tanmenetTankor_ibfk_2` FOREIGN KEY (`tanmenetId`) REFERENCES `tanmenet` (`tanmenetId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tanmenetTankor_ibfk_3` FOREIGN KEY (`tanev`) REFERENCES `szemeszter` (`tanev`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

alter table idoszak modify 
    `tipus` enum('zárás','bizonyítvány írás','vizsga','előzetes tárgyválasztás','tárgyválasztás','tankörnévsor módosítás','fogadóóra jelentkezés','tanmenet leadás') 
    COLLATE utf8_hungarian_ci DEFAULT NULL;