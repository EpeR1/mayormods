
alter table `osztaly` drop foreign key `osztaly_ibfk_1`;
alter table `osztaly` change `kirOsztalyJellegId` `osztalyJellegId` tinyint(3) unsigned DEFAULT NULL;
alter table `osztaly` add 
CONSTRAINT `osztaly_ibfk_1` FOREIGN KEY (`osztalyJellegId`) REFERENCES `mayor_naplo`.`osztalyJelleg` (`osztalyJellegId`) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE osztaly MODIFY vegzoTanev smallint(5) unsigned DEFAULT NULL;