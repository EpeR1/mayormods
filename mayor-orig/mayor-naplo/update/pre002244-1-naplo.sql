alter table jegy DROP FOREIGN KEY `jegy_ibfk_3`;
alter table jegy ADD CONSTRAINT FOREIGN KEY `jegy_ibfk_3` (`dolgozatId`) REFERENCES `dolgozat` (`dolgozatId`) ON DELETE SET NULL ON UPDATE CASCADE;

