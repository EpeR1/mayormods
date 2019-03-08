ALTER TABLE `targy` ADD kirTargyId smallint(5) unsigned DEFAULT NULL;
ALTER TABLE `targy` ADD CONSTRAINT FOREIGN KEY `targy_ibfk_2` (`kirTargyId`) REFERENCES `mayor_naplo`.`kirTargy` (`kirTargyId`) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE `osztaly` ADD kirOsztalyJellegId tinyint(3) UNSIGNED DEFAULT NULL;
ALTER TABLE `osztaly` ADD CONSTRAINT FOREIGN KEY `osztaly_ibfk_1` (`kirOsztalyJellegId`) REFERENCES `mayor_naplo`.`kirOsztalyJelleg` (`kirOsztalyJellegId`) ON DELETE SET NULL ON UPDATE CASCADE;
