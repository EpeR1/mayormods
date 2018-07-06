
alter table osztaly add column telephelyId tinyint(3) unsigned DEFAULT NULL;
alter table osztaly add CONSTRAINT `osztaly_telephely` FOREIGN KEY (`telephelyId`) REFERENCES `telephely` (`telephelyId`) ON DELETE SET NULL ON UPDATE SET NULL;