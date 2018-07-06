ALTER TABLE zaroJegy ADD evfolyam tinyint unsigned AFTER targyId;
ALTER TABLE zaroJegy ADD jegyTipus ENUM('jegy','aláírás','százalékos','három szintű','egyedi felsorolás','felmentett') NOT NULL DEFAULT 'jegy' AFTER jegy;
ALTER TABLE zaroJegy ADD dt DATE ;
ALTER TABLE zaroJegy DROP PRIMARY KEY;
ALTER TABLE zaroJegy ADD zaroJegyId int unsigned NOT NULL auto_increment PRIMARY KEY FIRST;

CREATE TABLE zaradek (
  zaradekId INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  diakId INTEGER UNSIGNED NOT NULL,
  dt DATE NULL,
  sorszam varchar(5) NULL,
  dokumentum SET('beírási napló','osztálynapló','törzslap','bizonyítvány') NULL,
  szoveg VARCHAR(255),
  PRIMARY KEY(zaradekId),
  INDEX zaradek_FKIndex1(diakId),
  FOREIGN KEY(diakId)
    REFERENCES diak(diakId)
      ON DELETE CASCADE
      ON UPDATE CASCADE
)
ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_hungarian_ci;

DROP TABLE IF EXISTS  zaroJegyZaradek;
CREATE TABLE zaroJegyZaradek (
  zaroJegyId INTEGER UNSIGNED NOT NULL,
  zaradekId INTEGER UNSIGNED NOT NULL,
  PRIMARY KEY(zaradekId, zaroJegyId),
  FOREIGN KEY(zaradekId)
    REFERENCES zaradek(zaradekId)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  FOREIGN KEY(zaroJegyId)
    REFERENCES zaroJegy(zaroJegyId)
      ON DELETE CASCADE
      ON UPDATE CASCADE
)
ENGINE=InnoDB;

-- KÉPZÉS
DROP TABLE IF EXISTS kepzesOraterv;
CREATE TABLE kepzesOraterv (
  kepzesOratervId INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  targyId SMALLINT UNSIGNED NOT NULL,
  evfolyam TINYINT UNSIGNED NOT NULL,
  szemeszter TINYINT UNSIGNED NOT NULL,
  hetiOraszam DECIMAL(2,2) NULL,
  kovetelmeny ENUM('jegy','aláírás','százalékos','három szintű','egyedi felsorolás','szöveges'),
  tipus ENUM('első nyelv','második nyelv','kötelezően választható','szabadon választható','mintatantervi') DEFAULT 'mintatantervi',
  PRIMARY KEY(kepzesOratervId),
  INDEX kepzesOraterv_FKIndex1(targyId),
  FOREIGN KEY(targyId)
    REFERENCES targy(targyId)
      ON DELETE CASCADE
      ON UPDATE CASCADE
)
ENGINE=InnoDB;

-- targy
ALTER TABLE targy ADD targyJelleg ENUM ('nyelv','szakmai','magatartás','szorgalom') DEFAULT NULL;
