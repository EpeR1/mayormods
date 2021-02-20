DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4693 $$

CREATE PROCEDURE upgrade_database_4693()
BEGIN
SET NAMES utf8 COLLATE utf8_hungarian_ci;

IF NOT EXISTS (SELECT * FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='kategoriak' AND COLUMN_NAME='id') THEN
    ALTER TABLE `kategoriak` ADD UNIQUE INDEX (id);
END IF;

ALTER TABLE `hirek` ENGINE = InnoDB;
ALTER TABLE `kategoriak` ENGINE = InnoDB;

IF NOT EXISTS (SELECT * FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='hirKategoria') THEN
CREATE TABLE `hirKategoria` (
 `hirId` int(10) unsigned NOT NULL,
 `kategoriaId` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`hirId`,`kategoriaId`),
  KEY `hirKategoria_FKIndex1` (`hirId`),
  KEY `hirKategoria_FKIndex2` (`kategoriaId`),
  CONSTRAINT `hirKategoria_ibfk_1` FOREIGN KEY (`hirId`) REFERENCES `hirek` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `hirKategoria_ibfk_2` FOREIGN KEY (`kategoriaId`) REFERENCES `kategoriak` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci ;
END IF;

-- hirek.cid --> hirKategoria kapcsolo
INSERT IGNORE INTO hirKategoria SELECT hirek.id as hirId,hirek.cid AS kategoriaId from hirek where hirek.cid!=0;

END $$
DELIMITER ;
CALL upgrade_database_4693();
