
CREATE TABLE `osztalyJelleg` (
  `osztalyJellegId` tinyint(3) unsigned NOT NULL auto_increment,
  `kirOsztalyJellegId` tinyint(3) unsigned NOT NULL,
  `osztalyJellegNev` varchar(255) NOT NULL,
  `erettsegizo` tinyint(3) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`osztalyJellegId`),
  CONSTRAINT `osztalyJelleg_ibfk_1` FOREIGN KEY (`kirOsztalyJellegId`) REFERENCES `kirOsztalyJelleg` (`kirOsztalyJellegId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

insert into osztalyJelleg 
    select null as osztalyJellegId, kirOsztalyJellegId, kirOsztalyJellegNev as osztalyJellegNev, 
    if(kirOsztalyJellegId<>1 and kirOsztalyJellegId<>6 and kirOsztalyJellegId<>7,1,0) as erettsegizo from kirOsztalyJelleg;

insert into osztalyJelleg values (10, 3, '5 évfolyamos gimnázium ny.ek. évfolyam',0);
