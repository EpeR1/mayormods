
DROP TABLE IF EXISTS oraLatogatas;
CREATE TABLE oraLatogatas (
  oraLatogatasId INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  megjegyzes TEXT NOT NULL,
  oraId INT UNSIGNED NULL,
  PRIMARY KEY(oraLatogatasId),
  FOREIGN KEY(oraId)
    REFERENCES ora(oraId)
      ON DELETE CASCADE
      ON UPDATE CASCADE
)
ENGINE=InnoDB;

CREATE TABLE oraLatogatasTanar (
  oraLatogatasId INTEGER UNSIGNED NOT NULL,
  tanarId INT UNSIGNED NULL,
  PRIMARY KEY(oraLatogatasId, tanarId),
  FOREIGN KEY(tanarId)
    REFERENCES %INTEZMENYDB%.tanar(tanarId)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  FOREIGN KEY(oraLatogatasId)
    REFERENCES oraLatogatas(oraLatogatasId)
      ON DELETE CASCADE
      ON UPDATE CASCADE
)
ENGINE=InnoDB;
