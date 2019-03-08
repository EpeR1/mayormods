CREATE TABLE `csengetesiRend` (
  `nap` tinyint(3) unsigned DEFAULT NULL,
  `ora` tinyint(3) unsigned NOT NULL,
  `tolTime` time DEFAULT NULL,
  `igTime` time DEFAULT NULL,
  `telephelyId` tinyint(3) unsigned DEFAULT NULL,
  KEY `csengetesiRend_telephely` (`telephelyId`),
  CONSTRAINT `csengetesiRend_telephely` FOREIGN KEY (`telephelyId`) REFERENCES `telephely` (`telephelyId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;
