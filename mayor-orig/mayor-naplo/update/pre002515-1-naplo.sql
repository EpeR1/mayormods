DROP TABLE IF EXISTS  `hianyzasHozott`;
CREATE TABLE `hianyzasHozott` (
  `diakId` int(10) unsigned NOT NULL,
  `statusz` enum('igazolt','igazolatlan') COLLATE utf8_hungarian_ci DEFAULT NULL,
  `dbHianyzas` tinyint(3) unsigned DEFAULT NULL,
  `dt` date DEFAULT NULL,
    CONSTRAINT `hianyzasHozott_IBFK1` FOREIGN KEY (`diakId`) REFERENCES `%INTEZMENYDB%`.`diak` (`diakId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;
