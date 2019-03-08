-- SQL utasítások a intezmeny adatbázisban --

ALTER TABLE osztaly CHANGE kepzes leiras VARCHAR(64);

CREATE TABLE kepzes (
  kepzesId SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  kepzesNev VARCHAR(255) NOT NULL,
  tanev SMALLINT UNSIGNED NULL,
  UNIQUE INDEX kepzesNevTanev (kepzesNev, tanev),
  PRIMARY KEY (kepzesId)
)
ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE kepzesDiak (
  kepzesId SMALLINT UNSIGNED NOT NULL,
  diakId INTEGER UNSIGNED NOT NULL,
  PRIMARY KEY(kepzesId, diakId),
  INDEX kepzesDiak_FKIndex1(kepzesId),
  INDEX kepzesDiak_FKIndex2(diakId),
  FOREIGN KEY(kepzesId)
    REFERENCES kepzes(kepzesId)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  FOREIGN KEY(diakId)
    REFERENCES diak(diakId)
      ON DELETE CASCADE
      ON UPDATE CASCADE
)
ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE kepzesOsztaly (
  kepzesId SMALLINT UNSIGNED NOT NULL,
  osztalyId INTEGER UNSIGNED NOT NULL,
  PRIMARY KEY(kepzesId, osztalyId),
  INDEX kepzesOsztaly_FKIndex1(kepzesId),
  INDEX kepzesOsztaly_FKIndex2(osztalyId),
  FOREIGN KEY(kepzesId)
    REFERENCES kepzes(kepzesId)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  FOREIGN KEY(osztalyId)
    REFERENCES osztaly(osztalyId)
      ON DELETE CASCADE
      ON UPDATE CASCADE
)
ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE kepzesTargyOraszam (
  kepzesId SMALLINT UNSIGNED NOT NULL,
  evfolyam TINYINT UNSIGNED NOT NULL,
  targyId SMALLINT UNSIGNED NOT NULL,
  oraszam DECIMAL(4,2) UNSIGNED NULL,
  kovetelmeny ENUM('aláírás','vizsga','jegy') NULL,
  jelenlet ENUM('kötelező','nem kötelező') NULL,
  PRIMARY KEY(kepzesId, evfolyam, targyId),
  INDEX kepzesTargyOraszam_FKIndex1(kepzesId),
  INDEX kepzesTargyOraszam_FKIndex2(targyId),
  FOREIGN KEY(kepzesId)
    REFERENCES kepzes(kepzesId)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  FOREIGN KEY(targyId)
    REFERENCES targy(targyId)
      ON DELETE NO ACTION
      ON UPDATE NO ACTION
)
ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE kepzesOraszam (
  kepzesId SMALLINT UNSIGNED NOT NULL,
  evfolyam TINYINT UNSIGNED NOT NULL,
  kotelezoOraszam DECIMAL(4,2) UNSIGNED NULL,
  maximalisOraszam DECIMAL(4,2) UNSIGNED NULL,
  PRIMARY KEY(kepzesId, evfolyam),
  INDEX kepzesOraszam_FKIndex1(kepzesId),
  FOREIGN KEY(kepzesId)
    REFERENCES kepzes(kepzesId)
      ON DELETE CASCADE
      ON UPDATE CASCADE
)
ENGINE=InnoDB DEFAULT CHARSET=utf8;

