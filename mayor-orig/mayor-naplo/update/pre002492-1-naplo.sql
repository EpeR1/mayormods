alter table `nap` add munkatervId tinyint unsigned default null;
CREATE TABLE `munkaterv` (
  `munkatervId` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `munkatervNev` varchar(255) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `tanitasiNap` smallint(6) DEFAULT NULL,
  `vegzosZarasDt` date DEFAULT NULL,
  `tanitasNelkuliMunkanap` smallint(6) DEFAULT NULL,
  PRIMARY KEY (`munkatervId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;
alter table nap add foreign key IBFK_munkatervId (munkatervId) REFERENCES munkaterv (munkatervId) ON DELETE CASCADE ON UPDATE CASCADE ;
create table munkatervOsztaly (munkatervId tinyint not null, osztalyId int(10) unsigned not null, PRIMARY KEY (munkatervId,osztalyId)) ENGINE=InnoDB default CHARSET=utf8 COLLATE=utf8_hungarian_ci;
alter table munkatervOsztaly add foreign key IBFK_osztalyId (osztalyId) REFERENCES %INTEZMENYDB%.osztaly (osztalyId) ON DELETE CASCADE ON UPDATE CASCADE ;
-- alter table munkatervOsztaly add foreign key IBFK_munkatervId (munkatervId) REFERENCES munkaterv (munkatervId) ON DELETE CASCADE ON UPDATE CASCADE ;
