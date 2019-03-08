
-- vizsga tábla 

CREATE TABLE vizsga (
  vizsgaId INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  diakId INTEGER UNSIGNED NOT NULL,
  targyId SMALLINT UNSIGNED NOT NULL,
  evfolyam TINYINT UNSIGNED NOT NULL,
  tipus ENUM('osztalyozó vizsga','javítóvizsga') NOT NULL,
  jelentkezesDt DATE NOT NULL,
  vizsgaDt DATE NULL,
  zaradekId INTEGER UNSIGNED NULL,
  zaroJegyId INTEGER UNSIGNED NULL,
  PRIMARY KEY(vizsgaId),
  INDEX vizsga_FKIndex1(diakId),
  INDEX vizsga_FKIndex2(targyId),
  FOREIGN KEY(diakId)
    REFERENCES diak(diakId)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  FOREIGN KEY(targyId)
    REFERENCES targy(targyId)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  FOREIGN KEY(zaradekId)
    REFERENCES zaradek(zaradekId)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  FOREIGN KEY(zaroJegyId)
    REFERENCES zaroJegy(zaroJegyId)
      ON DELETE CASCADE
      ON UPDATE CASCADE
)
ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_hungarian_ci;