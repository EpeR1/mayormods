DROP DATABASE IF EXISTS mayor_jatek;
CREATE DATABASE mayor_jatek CHARACTER SET utf8;
GRANT ALL ON mayor_jatek.* TO 'mayor_jatek'@'localhost' IDENTIFIED BY '1212';
GRANT SELECT ON mayor_jatek.* TO 'mayor_jatek_read'@'localhost' IDENTIFIED BY '1234';
USE mayor_jatek;

CREATE TABLE egyszam (
  userAccount VARCHAR(32) NULL,
  ev YEAR NULL,
  het TINYINT UNSIGNED NULL,
  szam TINYINT UNSIGNED NULL,
  PRIMARY KEY(userAccount,ev,het,szam)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

