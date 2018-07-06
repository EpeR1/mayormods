ALTER TABLE jegy ADD jegyTipus ENUM('jegy','aláírás','százalékos','három szintű','egyedi felsorolás') NOT NULL DEFAULT 'jegy' AFTER jegy;
ALTER TABLE jegy MODIFY jegy DECIMAL(4,1);

DROP TABLE IF EXISTS oraLatogatas;
CREATE TABLE oraLatogatas (
  oraLatogatasId INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  megjegyzes TEXT NOT NULL,
  tanarId INT UNSIGNED NULL,
  PRIMARY KEY(oraLatogatasId),
  FOREIGN KEY(tanarId)
    REFERENCES %INTEZMENYDB%.tanar(tanarId)
      ON DELETE CASCADE
      ON UPDATE CASCADE
)
ENGINE=InnoDB;
