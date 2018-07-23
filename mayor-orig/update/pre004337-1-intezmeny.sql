CREATE TABLE IF NOT EXISTS `tanarTelephely` (
  `tanarId` int(10) unsigned NOT NULL,
  `telephelyId` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`tanarId`,`telephelyId`),
  KEY `tanarTelephely_ibfk_1` (`tanarId`),
  CONSTRAINT `tanarTelephely_ibfk_1` FOREIGN KEY (`tanarId`) REFERENCES `tanar` (`tanarId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;
