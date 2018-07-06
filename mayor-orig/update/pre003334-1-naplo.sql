CREATE TABLE `esemeny` (
  `esemenyId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `esemenyKategoria` enum('verseny','közösségi szolgálat','iskolai rendezvény'),
  `esemenyRovidnev` varchar(64)  COLLATE utf8_hungarian_ci DEFAULT NULL,
  `esemenyNev` varchar(255) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `esemenyLeiras` text COLLATE utf8_hungarian_ci DEFAULT NULL,
  `jelentkezesTolDt` datetime NOT NULL,
  `jelentkezesIgDt` datetime NOT NULL,
  `min` tinyint(3) unsigned NOT NULL,
  `max` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`esemenyId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci ;

CREATE TABLE `esemenyOsztaly` (
  `esemenyId` int(10) unsigned NOT NULL,
  `osztalyId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`esemenyId`,`osztalyId`),
  KEY `esemenyOsztaly_FKIndex1` (`esemenyId`),
  KEY `esemenyOsztaly_FKIndex2` (`osztalyId`),
  CONSTRAINT `esemenyOsztaly_ibfk_1` FOREIGN KEY (`esemenyId`) REFERENCES `esemeny` (`esemenyId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `esemenyOsztaly_ibfk_2` FOREIGN KEY (`osztalyId`) REFERENCES `%INTEZMENYDB%`.`osztaly` (`osztalyId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `esemenyDiak` (
  `esemenyId` int(10) unsigned NOT NULL,
  `diakId` int(10) unsigned NOT NULL,
  `jelentkezesDt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `jovahagyasDt` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`esemenyId`,`diakId`),
  KEY `esemenyDiak_esemenyId` (`esemenyId`),
  KEY `esemenyDiak_diakId` (`diakId`),
  CONSTRAINT `esemenyDiak_ibfk_1` FOREIGN KEY (`esemenyId`) REFERENCES `esemeny` (`esemenyId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `esemenyDiak_ibfk_2` FOREIGN KEY (`diakId`) REFERENCES `%INTEZMENYDB%`.`diak` (`diakId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci ;

CREATE TABLE `esemenyTanar` (
  `esemenyId` int(10) unsigned NOT NULL,
  `tanarId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`esemenyId`,`tanarId`),
  KEY `esemenyTanar_FKIndex1` (`esemenyId`),
  KEY `esemenyTanar_FKIndex2` (`tanarId`),
  CONSTRAINT `esemenyTanar_ibfk_1` FOREIGN KEY (`esemenyId`) REFERENCES `esemeny` (`esemenyId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `esemenyTanar_ibfk_2` FOREIGN KEY (`tanarId`) REFERENCES `%INTEZMENYDB%`.`tanar` (`tanarId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;
