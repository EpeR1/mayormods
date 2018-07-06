DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_3799 $$
CREATE PROCEDURE upgrade_database_3799()
BEGIN
IF EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='tovabbkepzesKeret' AND COLUMN_NAME='naptariEv'
) THEN
    DROP TABLE tovabbkepzesKeret;
    CREATE TABLE `tovabbkepzesKeret` (
    `tanev` year(4) NOT NULL,
    `keretOsszeg` int(10) unsigned NOT NULL,
    PRIMARY KEY (`tanev`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;
END IF;
IF EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='tovabbkepzesTanulmanyiEgyseg' AND COLUMN_NAME='naptariEv'
) THEN
    DROP TABLE tovabbkepzesTanulmanyiEgyseg;
    CREATE TABLE `tovabbkepzesTanulmanyiEgyseg` (
  `tovabbkepzesId` smallint(5) unsigned NOT NULL,
  `tanarId` int(10) unsigned not null,
  `tanev` year NOT NULL,
  `reszosszeg` int unsigned not null default 0,
  `tamogatas` int unsigned not null default 0,
  `tovabbkepzesStatusz` enum('terv','jóváhagyott','elutasított','megszűnt','megszakadt','teljesített') COLLATE utf8_hungarian_ci DEFAULT 'terv',
  `megjegyzes` varchar(255) COLLATE utf8_hungarian_ci DEFAULT NULL,
  PRIMARY KEY (`tovabbkepzesId`,`tanarId`,`tanev`),
  CONSTRAINT `tovabbkepzesTE_ibfk_1` FOREIGN KEY (`tanarId`) REFERENCES `tanar` (`tanarId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tovabbkepzesTE_ibfk_2` FOREIGN KEY (`tovabbkepzesId`) REFERENCES `tovabbkepzes` (`tovabbkepzesId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tovabbkepzesTE_ibfk_main` FOREIGN KEY (`tovabbkepzesId`, `tanarId`) REFERENCES `tovabbkepzesTanar` (`tovabbkepzesId`, `tanarId`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;
END IF;

END $$
DELIMITER ; $$
CALL upgrade_database_3799();

