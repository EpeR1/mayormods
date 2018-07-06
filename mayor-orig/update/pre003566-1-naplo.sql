
DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_3566 $$
CREATE PROCEDURE upgrade_database_3566()
BEGIN

    DROP TABLE IF EXISTS `bontasTankor`;

    DROP TABLE IF EXISTS `kepzesTargyBontas`;

    CREATE TABLE `kepzesTargyBontas` (
      `bontasId` int(10) unsigned NOT NULL AUTO_INCREMENT,
      `osztalyId` int(10) unsigned NOT NULL,
      `kepzesOratervId` int(10) unsigned NOT NULL,
      `targyId` int(10) unsigned NOT NULL,
      PRIMARY KEY (`bontasId`),
      CONSTRAINT `ktBontas_osztalyId` FOREIGN KEY (`osztalyId`) REFERENCES `%INTEZMENYDB%`.`osztaly` (`osztalyId`) ON DELETE CASCADE ON UPDATE CASCADE,
      CONSTRAINT `ktBontas_kepzesOratervId` FOREIGN KEY (`kepzesOratervId`) REFERENCES `%INTEZMENYDB%`.`kepzesOraterv` (`kepzesOratervId`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

    CREATE TABLE `bontasTankor` (
      `bontasId` int(10) unsigned NOT NULL,
      `tankorId` int(10) unsigned NOT NULL,
      `hetiOraszam` decimal(4,2) DEFAULT NULL,
      PRIMARY KEY (`bontasId`,`tankorId`),
      CONSTRAINT `bontasTankor_bontasId` FOREIGN KEY (`bontasId`) REFERENCES `kepzesTargyBontas` (`bontasId`) ON DELETE CASCADE ON UPDATE CASCADE,
      CONSTRAINT `bontasTankor_tankorId` FOREIGN KEY (`tankorId`) REFERENCES `%INTEZMENYDB%`.`tankor` (`tankorId`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

END $$
DELIMITER ;
CALL upgrade_database_3566();


