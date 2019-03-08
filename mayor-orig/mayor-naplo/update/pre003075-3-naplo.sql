alter table ora add feladatTipusId tinyint(3) unsigned default null ;
alter table ora ADD CONSTRAINT `ora_ibfk_5` FOREIGN KEY (`feladatTipusId`) REFERENCES
`%INTEZMENYDB%`.`feladatTipus` (`feladatTipusId`) ON DELETE SET NULL ON UPDATE CASCADE;
