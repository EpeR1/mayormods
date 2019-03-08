
CREATE TABLE IF NOT EXISTS `hetes` (
    `osztalyId` INT(10) UNSIGNED NOT NULL,
    `dt` DATE,
    `sorszam` SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    `diakId` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY(`osztalyId`,`dt`,`sorszam`),
  INDEX het_FKindex1(`osztalyId`),
  INDEX het_FKindex2(`diakId`),
  FOREIGN KEY(`osztalyId`)
    REFERENCES %INTEZMENYDB%.`osztaly`(`osztalyId`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  FOREIGN KEY(`diakId`)
    REFERENCES %INTEZMENYDB%.`diak`(`diakId`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
)
ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_hungarian_ci;
