CREATE TABLE IF NOT EXISTS `teremPreferencia` (
  `teremPreferenciaId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tanarId` int(10) unsigned NOT NULL,
  `targyId` smallint(5) unsigned DEFAULT NULL,
  `teremStr` varchar(255) COLLATE utf8_hungarian_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`teremPreferenciaId`),
  KEY `teremPref_FKIndex1` (`tanarId`),
  KEY `teremPref_FKIndex2` (`targyId`),
  CONSTRAINT `teremPref_ibfk_1` FOREIGN KEY (`tanarId`) REFERENCES `tanar` (`tanarId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `teremPref_ibfk_2` FOREIGN KEY (`targyId`) REFERENCES `targy` (`targyId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;
