CREATE TABLE IF NOT EXISTS `szempontRendszer` (
    `szrId` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    `kepzesId` SMALLINT UNSIGNED,
    `evfolyam` SMALLINT UNSIGNED NOT NULL,
    `targyId` SMALLINT UNSIGNED,
    `targyTipus` ENUM('első nyelv','második nyelv','választható','kötelezően választható'),
    `tanev` SMALLINT UNSIGNED NOT NULL,
    `szemeszter` TINYINT UNSIGNED NOT NULL,
  PRIMARY KEY(`szrId`),
  INDEX szr_FKindex1(`kepzesId`),
  INDEX szr_FKindex2(`targyId`),
  INDEX szr_FKIndex3(tanev,szemeszter),
  FOREIGN KEY(`kepzesId`)
    REFERENCES `kepzes`(`kepzesId`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  FOREIGN KEY(`targyId`)
    REFERENCES `targy`(`targyId`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  FOREIGN KEY(`tanev`,`szemeszter`)
    REFERENCES `szemeszter`(`tanev`,`szemeszter`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
)
ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_hungarian_ci;

CREATE TABLE IF NOT EXISTS `szrSzempont` (
    `szempontId` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    `szrId` INTEGER UNSIGNED NOT NULL,
    `szempont` VARCHAR(128),
  PRIMARY KEY(`szempontId`),
  INDEX szrsz_FKindex1(`szrId`),
  FOREIGN KEY(`szrId`)
    REFERENCES `szempontRendszer`(`szrId`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
)
ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_hungarian_ci;

CREATE TABLE IF NOT EXISTS `szrMinosites` (
    `minositesId` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    `szempontId` INTEGER UNSIGNED NOT NULL,
    `minosites` VARCHAR(128),
  PRIMARY KEY(`minositesId`),
  INDEX szrm_FKindex1(`szempontId`),
  FOREIGN KEY(`szempontId`)
    REFERENCES `szrSzempont`(`szempontId`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
)
ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_hungarian_ci;

CREATE TABLE IF NOT EXISTS `szovegesErtekeles` (
    `szeId` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    `diakId` INTEGER UNSIGNED NOT NULL,
    `szrId` INTEGER UNSIGNED NOT NULL,
    `targyId` SMALLINT UNSIGNED NOT NULL,
    `dt` DATE NOT NULL,
    `tanev` SMALLINT UNSIGNED NOT NULL,
    `szemeszter` TINYINT UNSIGNED NOT NULL,
  PRIMARY KEY(`szeId`),
  INDEX sze_FKindex1(`diakId`),
  INDEX sze_FKindex2(`szrId`),
  INDEX sze_FKindex3(`targyId`),
  INDEX sze_FKIndex4(`tanev`,`szemeszter`),
  UNIQUE KEY sze_UKindex1(`diakId`,`targyId`,`dt`),
  FOREIGN KEY(diakId)
    REFERENCES `diak`(`diakId`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  FOREIGN KEY(`targyId`)
    REFERENCES `targy`(`targyId`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  FOREIGN KEY(`szrId`)
    REFERENCES `szempontRendszer`(`szrId`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  FOREIGN KEY(`tanev`,`szemeszter`)
    REFERENCES `szemeszter`(`tanev`,`szemeszter`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
)
ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_hungarian_ci;

CREATE TABLE IF NOT EXISTS `szeEgyediMinosites` (
    `szeId` INTEGER UNSIGNED NOT NULL,
    `szempontId` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    `egyediMinosites` VARCHAR(255),
  PRIMARY KEY(`szeId`,`szempontId`),
  INDEX szeem_FKindex1(`szempontId`),
  INDEX szeem_FKindex2(`szeId`),
  FOREIGN KEY(`szempontId`)
    REFERENCES `szrSzempont`(`szempontId`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  FOREIGN KEY(`szeId`)
    REFERENCES `szovegesErtekeles`(`szeId`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
)
ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_hungarian_ci;

CREATE TABLE IF NOT EXISTS `szeMinosites` (
    `szeId` INTEGER UNSIGNED NOT NULL,
    `minositesId` INTEGER UNSIGNED NOT NULL,
  PRIMARY KEY(`szeId`,`minositesId`),
  INDEX szem_FKindex1(`szeId`),
  FOREIGN KEY(`szeId`)
    REFERENCES `szovegesErtekeles`(`szeId`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  FOREIGN KEY(`minositesId`)
    REFERENCES `szrMinosites`(`minositesId`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
)
ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_hungarian_ci;

