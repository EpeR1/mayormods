
CREATE TABLE `mayorUpdateLog` (
  `scriptFile` varchar(255) COLLATE utf8_hungarian_ci NOT NULL DEFAULT '',
  `dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`scriptFile`,`dt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `szemeszter` (
  `tanev` smallint(5) unsigned NOT NULL,
  `szemeszter` tinyint(3) unsigned NOT NULL,
  `szemeszterId` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `statusz` enum('aktív','lezárt','archivált','tervezett') COLLATE utf8_hungarian_ci DEFAULT 'tervezett',
  `kezdesDt` date DEFAULT NULL,
  `zarasDt` date DEFAULT NULL,
  PRIMARY KEY (`tanev`,`szemeszter`),
  UNIQUE KEY `szemeszter_uniq` (`szemeszterId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

LOCK TABLES `szemeszter` WRITE;
INSERT INTO `szemeszter` (`tanev`,`szemeszter`,`szemeszterId`,`statusz`,`kezdesDt`,`zarasDt`) VALUES 
(1997,1,1,'tervezett','1997-09-01','1998-01-15'),(1997,2,2,'tervezett','1998-01-16','1998-06-15'),
(1998,1,3,'tervezett','1998-09-01','1999-01-15'),(1998,2,4,'tervezett','1999-01-16','1999-06-15'),
(1999,1,5,'tervezett','1999-09-01','2000-01-15'),(1999,2,6,'tervezett','2000-01-16','2000-06-15'),
(2000,1,7,'tervezett','2000-09-01','2001-01-15'),(2000,2,8,'tervezett','2001-01-16','2001-06-15'),
(2001,1,9,'tervezett','2001-09-01','2002-01-15'),(2001,2,10,'tervezett','2002-01-16','2002-06-15'),
(2002,1,11,'tervezett','2002-09-01','2003-01-15'),(2002,2,12,'tervezett','2003-01-16','2003-06-15'),
(2003,1,13,'tervezett','2003-09-01','2004-01-15'),(2003,2,14,'tervezett','2004-01-16','2004-06-15'),
(2004,1,15,'tervezett','2004-09-01','2005-01-15'),(2004,2,16,'tervezett','2005-01-16','2005-06-15'),
(2005,1,17,'tervezett','2005-09-01','2006-01-15'),(2005,2,18,'tervezett','2006-01-16','2006-06-15'),
(2006,1,19,'tervezett','2006-09-01','2007-01-19'),(2006,2,20,'tervezett','2007-01-20','2007-06-15'),
(2007,1,21,'tervezett','2007-09-03','2008-01-18'),(2007,2,22,'tervezett','2008-01-19','2008-06-13'),
(2008,1,23,'tervezett','2008-09-01','2009-01-16'),(2008,2,24,'tervezett','2009-01-17','2009-06-15'),
(2009,1,25,'tervezett','2009-09-01','2010-01-15'),(2009,2,26,'tervezett','2010-01-16','2010-06-15'),
(2010,1,27,'tervezett','2010-09-01','2011-01-14'),(2010,2,28,'tervezett','2011-01-15','2011-06-15'),
(2011,1,29,'tervezett','2011-09-01','2012-01-13'),(2011,2,30,'tervezett','2012-01-14','2012-06-15'),
(2012,1,31,'tervezett','2012-09-01','2013-01-18'),(2012,2,32,'tervezett','2013-01-19','2013-06-15'),
(2013,1,33,'tervezett','2013-09-01','2014-01-15'),(2013,2,34,'tervezett','2014-01-16','2014-06-15'),
(2014,1,35,'tervezett','2014-09-01','2015-01-15'),(2014,2,36,'tervezett','2015-01-16','2015-06-15'),
(2015,1,37,'tervezett','2015-09-01','2016-01-15'),(2015,2,38,'tervezett','2016-01-16','2016-06-15'),
(2016,1,39,'tervezett','2016-09-01','2017-01-15'),(2016,2,40,'tervezett','2017-01-16','2017-06-15'),
(2017,1,41,'tervezett','2017-09-01','2018-01-15'),(2017,2,42,'tervezett','2018-01-16','2018-06-15'),
(2018,1,43,'tervezett','2018-09-01','2019-01-15'),(2018,2,44,'tervezett','2019-01-16','2019-06-15'),
(2019,1,45,'tervezett','2019-09-01','2020-01-15'),(2019,2,46,'tervezett','2020-01-16','2020-06-15'),
(2020,1,47,'tervezett','2020-09-01','2021-01-15'),(2020,2,48,'tervezett','2021-01-16','2021-06-15'),
(2021,1,49,'tervezett','2021-09-01','2022-01-15'),(2021,2,50,'tervezett','2022-01-16','2022-06-15'),
(2022,1,51,'tervezett','2022-09-01','2023-01-15'),(2022,2,52,'tervezett','2023-01-16','2023-06-15'),
(2023,1,53,'tervezett','2023-09-01','2024-01-15'),(2023,2,54,'tervezett','2024-01-16','2024-06-15'),
(2024,1,55,'tervezett','2024-09-01','2025-01-15'),(2024,2,56,'tervezett','2025-01-16','2025-06-15'),
(2025,1,57,'tervezett','2025-09-01','2026-01-15'),(2025,2,58,'tervezett','2026-01-16','2026-06-15');
UNLOCK TABLES;

CREATE TABLE `telephely` (
  `telephelyId` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `telephelyRovidNev` varchar(16) NOT NULL,
  `telephelyNev` varchar(128) NOT NULL,
  `alapertelmezett` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `cimHelyseg` varchar(32) CHARACTER SET utf8 COLLATE utf8_hungarian_ci DEFAULT NULL,
  `cimIrsz` varchar(8) DEFAULT NULL,
  `cimKozteruletNev` varchar(32) DEFAULT NULL,
  `cimKozteruletJelleg` enum('út','utca','útja','körút','tér','tere','körtér','köz','fasor','árok','lejtő','lakótelep','sétány','dűlő','átjáró','bástya','bástyája','domb','dűlőút','egyéb','elágazás','erdősor','fasora','forduló','főút','gát','hajóállomás','határsor','határút','hegy','helyrajzi szám','hídfő','játszótér','kapu','kert','kikötő','kilátó','körönd','körvasútsor','külterület','lakónegyed','lakópark','lépcső','liget','major','mélykút','ösvény','park','parkja','part','pavilon','piac','pihenő','puszta','rakpart','repülőtér','rét','sétaút','sor','sugárút','sziget','tanya','telep','udvar','üdülőpart','várkerület','vasútállomás','völgy','zug') COLLATE utf8_hungarian_ci DEFAULT NULL,
  `cimHazszam` varchar(20) DEFAULT NULL,
  `telefon` varchar(64) DEFAULT NULL,
  `fax` varchar(64) DEFAULT NULL,
  `email` varchar(96) DEFAULT NULL,
  `honlap` varchar(96) DEFAULT NULL,
  PRIMARY KEY (`telephelyId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `szulo` (
  `szuloId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nevElotag` varchar(8) COLLATE utf8_hungarian_ci NOT NULL DEFAULT '',
  `csaladinev` varchar(64) COLLATE utf8_hungarian_ci NOT NULL DEFAULT '',
  `utonev` varchar(64) COLLATE utf8_hungarian_ci NOT NULL DEFAULT '',
  `szuleteskoriNevElotag` varchar(8) COLLATE utf8_hungarian_ci NOT NULL DEFAULT '',
  `szuleteskoriCsaladinev` varchar(32) COLLATE utf8_hungarian_ci NOT NULL DEFAULT '',
  `szuleteskoriUtonev` varchar(32) COLLATE utf8_hungarian_ci NOT NULL DEFAULT '',
  `nem` enum('fiú','lány') COLLATE utf8_hungarian_ci DEFAULT NULL,
  `szuletesiEv` year(4) DEFAULT NULL,
  `cimOrszag` varchar(16) COLLATE utf8_hungarian_ci DEFAULT 'Magyarország',
  `cimHelyseg` varchar(32) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `cimIrsz` varchar(8) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `cimKozteruletNev` varchar(32) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `cimKozteruletJelleg` enum('út','utca','útja','körút','tér','tere','körtér','köz','fasor','árok','lejtő','lakótelep','sétány','dűlő','átjáró','bástya','bástyája','domb','dűlőút','egyéb','elágazás','erdősor','fasora','forduló','főút','gát','hajóállomás','határsor','határút','hegy','helyrajzi szám','hídfő','játszótér','kapu','kert','kikötő','kilátó','körönd','körvasútsor','külterület','lakónegyed','lakópark','lépcső','liget','major','mélykút','ösvény','park','parkja','part','pavilon','piac','pihenő','puszta','rakpart','repülőtér','rét','sétaút','sor','sugárút','sziget','tanya','telep','udvar','üdülőpart','várkerület','vasútállomás','völgy','zug') COLLATE utf8_hungarian_ci DEFAULT NULL,
  `cimHazszam` varchar(20) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `cimEmelet` varchar(5) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `cimAjto` varchar(5) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `mobil` varchar(64) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `telefon` varchar(64) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `email` varchar(96) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `foglalkozas` varchar(128) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `munkahely` varchar(128) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `userAccount` varchar(32) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `statusz` enum('elhunyt','házas','egyedülálló','hajadon / nőtlen','elvált','özvegy','élettársi kapcsolatban él') COLLATE utf8_hungarian_ci DEFAULT NULL,
  PRIMARY KEY (`szuloId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `diak` (
  `diakId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `oId` bigint(20) unsigned DEFAULT NULL,
  `diakigazolvanySzam` bigint(11) unsigned DEFAULT NULL,
  `tajSzam` int(9) unsigned zerofill DEFAULT NULL,
  `adoazonosito` bigint(10) unsigned zerofill DEFAULT NULL,
  `szemelyiIgazolvanySzam` varchar(16) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `tartozkodasiOkiratSzam` varchar(16) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `viseltNevElotag` varchar(8) COLLATE utf8_hungarian_ci NOT NULL DEFAULT '',
  `viseltCsaladinev` varchar(64) COLLATE utf8_hungarian_ci NOT NULL DEFAULT '',
  `viseltUtonev` varchar(64) COLLATE utf8_hungarian_ci NOT NULL DEFAULT '',
  `szuleteskoriNevElotag` varchar(8) COLLATE utf8_hungarian_ci NOT NULL DEFAULT '',
  `szuleteskoriCsaladinev` varchar(64) COLLATE utf8_hungarian_ci NOT NULL DEFAULT '',
  `szuleteskoriUtonev` varchar(64) COLLATE utf8_hungarian_ci NOT NULL DEFAULT '',
  `szuletesiHely` varchar(32) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `szuletesiIdo` date DEFAULT NULL,
  `nem` enum('fiú','lány') COLLATE utf8_hungarian_ci DEFAULT NULL,
  `apaId` int(10) unsigned DEFAULT NULL,
  `gondviseloId` int(10) unsigned DEFAULT NULL,
  `neveloId` int(10) unsigned DEFAULT NULL,
  `anyaId` int(10) unsigned DEFAULT NULL,
  `beiratoId` int(10) unsigned DEFAULT NULL,
  `allampolgarsag` varchar(16) COLLATE utf8_hungarian_ci DEFAULT 'magyar',
  `lakhelyOrszag` varchar(16) COLLATE utf8_hungarian_ci DEFAULT 'Magyarország',
  `lakhelyHelyseg` varchar(32) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `lakhelyIrsz` varchar(8) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `lakhelyKozteruletNev` varchar(32) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `lakhelyKozteruletJelleg` enum('út','utca','útja','körút','tér','tere','körtér','köz','fasor','árok','lejtő','lakótelep','sétány','dűlő','átjáró','bástya','bástyája','domb','dűlőút','egyéb','elágazás','erdősor','fasora','forduló','főút','gát','hajóállomás','határsor','határút','hegy','helyrajzi szám','hídfő','játszótér','kapu','kert','kikötő','kilátó','körönd','körvasútsor','külterület','lakónegyed','lakópark','lépcső','liget','major','mélykút','ösvény','park','parkja','part','pavilon','piac','pihenő','puszta','rakpart','repülőtér','rét','sétaút','sor','sugárút','sziget','tanya','telep','udvar','üdülőpart','várkerület','vasútállomás','völgy','zug') COLLATE utf8_hungarian_ci DEFAULT NULL,
  `lakhelyHazszam` varchar(20) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `lakhelyEmelet` varchar(5) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `lakhelyAjto` varchar(5) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `tartOrszag` varchar(16) COLLATE utf8_hungarian_ci DEFAULT 'Magyarország',
  `tartHelyseg` varchar(32) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `tartIrsz` varchar(8) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `tartKozteruletNev` varchar(32) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `tartKozteruletJelleg` enum('út','utca','útja','körút','tér','tere','körtér','köz','fasor','árok','lejtő','lakótelep','sétány','dűlő','átjáró','bástya','bástyája','domb','dűlőút','egyéb','elágazás','erdősor','fasora','forduló','főút','gát','hajóállomás','határsor','határút','hegy','helyrajzi szám','hídfő','játszótér','kapu','kert','kikötő','kilátó','körönd','körvasútsor','külterület','lakónegyed','lakópark','lépcső','liget','major','mélykút','ösvény','park','parkja','part','pavilon','piac','pihenő','puszta','rakpart','repülőtér','rét','sétaút','sor','sugárút','sziget','tanya','telep','udvar','üdülőpart','várkerület','vasútállomás','völgy','zug') COLLATE utf8_hungarian_ci DEFAULT NULL,
  `tartHazszam` varchar(20) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `tartEmelet` varchar(5) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `tartAjto` varchar(5) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `jogviszonyKezdete` date DEFAULT NULL,
  `kezdoTanev` smallint(5) unsigned NOT NULL,
  `kezdoSzemeszter` tinyint(3) unsigned NOT NULL,
  `jogviszonyVege` date DEFAULT NULL,
  `telefon` varchar(64) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `mobil` varchar(64) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `email` varchar(96) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `statusz` enum('felvételt nyert','jogviszonyban van','magántanuló','vendégtanuló','jogviszonya felfüggesztve','jogviszonya lezárva') COLLATE utf8_hungarian_ci NOT NULL,
  `penzugyiStatusz` enum('állami finanszírozás','térítési díj','tandíj') COLLATE utf8_hungarian_ci DEFAULT 'állami finanszírozás',
  `szocialisHelyzet` set('szülei elváltak','három vagy több gyerekes család','rendszeres gyermekvédelmi támogatást kap','állami gondozott','veszélyeztetett','hátrányos helyzetű','halmozottan hátrányos helyzetű','sajátos nevelési igényű') COLLATE utf8_hungarian_ci DEFAULT NULL,
  `fogyatekossag` set('tartósan beteg',
'mozgássérült','beszédfogyatékos','hallássérült','látássérült','autista','enyhén értelmi fogyatékos','középsúlyos értelmi fogyatékos',
'halmozottan fogyatékos',
'diszlexia','diszgráfia','diszkalkulia','iskolai készségek kevert zavarával küzdő','tanulási nehézség','tanulási zavar',
'kevert specifikus fejlődési zavarok','elektív mutista','hiperaktív','magatartászavar') COLLATE utf8_hungarian_ci DEFAULT NULL,
  `gondozasiSzam` varchar(128) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `elozoIskolaOMKod` mediumint(8) unsigned zerofill DEFAULT NULL,
  `lakohelyiJellemzo` enum('körzetes','kerületi','helybéli','bejáró','kollégista') COLLATE utf8_hungarian_ci DEFAULT NULL,
  `torvenyesKepviselo` set('anya','apa','gyám','gondnok') COLLATE utf8_hungarian_ci DEFAULT NULL,
  `megjegyzes` varchar(255) collate utf8_hungarian_ci default null,
  `NEKAzonosito` varchar(16) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `torzslapszam` varchar(32) COLLATE utf8_hungarian_ci DEFAULT NULL,
  PRIMARY KEY (`diakId`),
  UNIQUE KEY `diakOid` (`oId`),
  KEY `kezdoTanev` (`kezdoTanev`,`kezdoSzemeszter`),
  KEY `anyaId` (`anyaId`),
  KEY `gondviseloId` (`gondviseloId`),
  KEY `apaId` (`apaId`),
  CONSTRAINT `diak_ibfk_2` FOREIGN KEY (`kezdoTanev`, `kezdoSzemeszter`) REFERENCES `szemeszter` (`tanev`, `szemeszter`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `diak_ibfk_3` FOREIGN KEY (`anyaId`)       REFERENCES `szulo` (`szuloId`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `diak_ibfk_4` FOREIGN KEY (`gondviseloId`) REFERENCES `szulo` (`szuloId`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `diak_ibfk_5` FOREIGN KEY (`apaId`)        REFERENCES `szulo` (`szuloId`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `diak_ibfk_6` FOREIGN KEY (`neveloId`)     REFERENCES `szulo` (`szuloId`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `diak_ibfk_7` FOREIGN KEY (`beiratoId`)    REFERENCES `szulo` (`szuloId`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `diakJogviszony` (
  `diakId` int(10) unsigned NOT NULL,
  `statusz` enum('felvételt nyert','jogviszonyban van','magántanuló','vendégtanuló','jogviszonya felfüggesztve','jogviszonya lezárva') COLLATE utf8_hungarian_ci NOT NULL,
  `dt` date NOT NULL,
  PRIMARY KEY (`diakId`,`dt`),
  KEY `diakJogviszony_FKIndex1` (`diakId`),
  CONSTRAINT `diakJogviszony_ibfk_1` FOREIGN KEY (`diakId`) REFERENCES `diak` (`diakId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `diakHianyzas` (
  `diakHianyzasId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `diakId` int(10) unsigned NOT NULL,
  `dt` date NOT NULL,
  `igazolt` tinyint(3) unsigned DEFAULT NULL,
  `igazolatlan` tinyint(3) unsigned DEFAULT NULL,
  `beszamit` tinyint(1) unsigned DEFAULT NULL,
  `megjegyzes` tinytext COLLATE utf8_hungarian_ci,
  PRIMARY KEY (`diakHianyzasId`),
  KEY `diakHianyzas_FKIndex1` (`diakId`),
  CONSTRAINT `diakHianyzas_ibfk_1` FOREIGN KEY (`diakId`) REFERENCES `diak` (`diakId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `hianyzasOsszesites` (
  `diakId` int(10) unsigned NOT NULL,
  `tanev` smallint(5) unsigned NOT NULL,
  `szemeszter` tinyint(3) unsigned NOT NULL,
  `igazolt` smallint(5) unsigned DEFAULT NULL,
  `igazolatlan` smallint(5) unsigned DEFAULT NULL,
  `kesesPercOsszeg` smallint(5) unsigned DEFAULT NULL,
  `gyakorlatIgazolt` smallint(5) unsigned DEFAULT NULL,
  `gyakorlatIgazolatlan` smallint(5) unsigned DEFAULT NULL,
  `gyakorlatKesesPercOsszeg` smallint(5) unsigned DEFAULT NULL,
  `elmeletIgazolt` smallint(5) unsigned DEFAULT NULL,
  `elmeletIgazolatlan` smallint(5) unsigned DEFAULT NULL,
  `elmeletKesesPercOsszeg` smallint(5) unsigned DEFAULT NULL,
  PRIMARY KEY (`diakId`,`tanev`,`szemeszter`),
  KEY `hianyzasOsszesites_FKIndex1` (`tanev`,`szemeszter`),
  KEY `hianyzasOsszesites_FKIndex2` (`diakId`),
  CONSTRAINT `hianyzasOsszesites_ibfk_1` FOREIGN KEY (`tanev`, `szemeszter`) REFERENCES `szemeszter` (`tanev`, `szemeszter`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `hianyzasOsszesites_ibfk_2` FOREIGN KEY (`diakId`) REFERENCES `diak` (`diakId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `idoszak` (
  `idoszakId` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `tanev` smallint(5) unsigned NOT NULL,
  `szemeszter` tinyint(3) unsigned NOT NULL,
  `tipus` enum('zárás','bizonyítvány írás','vizsga','előzetes tárgyválasztás','tárgyválasztás','tankörnévsor módosítás','fogadóóra jelentkezés','tanmenet leadás') COLLATE utf8_hungarian_ci DEFAULT NULL,
  `tolDt` datetime NOT NULL,
  `igDt` datetime NOT NULL,
  PRIMARY KEY (`idoszakId`),
  KEY `szemeszterIdoszak_FKIndex1` (`tanev`,`szemeszter`),
  CONSTRAINT `idoszak_ibfk_1` FOREIGN KEY (`tanev`, `szemeszter`) REFERENCES `szemeszter` (`tanev`, `szemeszter`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `munkakozosseg` (
  `mkId` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `mkVezId` int(10) unsigned DEFAULT NULL,
  `leiras` varchar(32) COLLATE utf8_hungarian_ci DEFAULT NULL,
  PRIMARY KEY (`mkId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `targy` (
  `targyId` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `targyNev` varchar(128) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `mkId` smallint(5) unsigned NOT NULL,
  `targyJelleg` enum('nyelv','szakmai','magatartás','szorgalom','alsó tagozatos','osztályfőnöki','készség','közösségi szolgálat') COLLATE utf8_hungarian_ci DEFAULT NULL,
  `evkoziKovetelmeny` enum('jegy','magatartás','szorgalom','négyszintű (szöveges minősítés)','féljegy','százalékos','aláírás','háromszintű','egyedi felsorolás','szöveges szempontrendszer','teljesített óra') collate utf8_hungarian_ci DEFAULT 'jegy',
  `zaroKovetelmeny` enum('jegy','magatartás','szorgalom','négyszintű (szöveges minősítés)','féljegy','százalékos','aláírás','háromszintű','egyedi felsorolás','szöveges szempontrendszer','teljesített óra') COLLATE utf8_hungarian_ci DEFAULT 'jegy',
  `targyRovidNev` varchar(64) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `kirTargyId` smallint(5) unsigned DEFAULT NULL,
  PRIMARY KEY (`targyId`),
  KEY `targy_FKIndex1` (`mkId`),
  KEY `targy_ibfk_2` (`kirTargyId`),
  CONSTRAINT `targy_ibfk_1` FOREIGN KEY (`mkId`) REFERENCES `munkakozosseg` (`mkId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `targy_ibfk_2` FOREIGN KEY (`kirTargyId`) REFERENCES `mayor_naplo`.`kirTargy` (`kirTargyId`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `targyTargy` (
  `foTargyId` smallint(5) unsigned NOT NULL,
  `alTargyId` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`foTargyId`,`alTargyId`),
  KEY `targyTargy_K1` (`foTargyId`),
  KEY `targyTargy_K2` (`alTargyId`),
  CONSTRAINT `targyTargy_ibfk_1` FOREIGN KEY (`foTargyId`) REFERENCES `targy` (`targyId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `targyTargy_ibfk_2` FOREIGN KEY (`alTargyId`) REFERENCES `targy` (`targyId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `tanar` (
  `tanarId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `oId` bigint(20) unsigned DEFAULT NULL,
  `beDt` date DEFAULT NULL,
  `kiDt` date DEFAULT NULL,
  `viseltNevElotag` varchar(8) COLLATE utf8_hungarian_ci NOT NULL DEFAULT '',
  `viseltCsaladinev` varchar(64) COLLATE utf8_hungarian_ci NOT NULL DEFAULT '',
  `viseltUtonev` varchar(64) COLLATE utf8_hungarian_ci NOT NULL DEFAULT '',
  `szuletesiHely` varchar(16) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `szuletesiIdo` date DEFAULT NULL,
  `dn` varchar(128) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `szuleteskoriUtonev` varchar(64) COLLATE utf8_hungarian_ci NOT NULL DEFAULT '',
  `szuleteskoriCsaladinev` varchar(64) COLLATE utf8_hungarian_ci NOT NULL DEFAULT '',
  `szuleteskoriNevElotag` varchar(8) COLLATE utf8_hungarian_ci NOT NULL DEFAULT '',
  `hetiMunkaora` decimal(3,1) DEFAULT '0.0',
  `NEKAzonosito` varchar(16) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `statusz` enum('határozatlan idejű','határozott idejű','tartósan távol','jogviszonya lezárva','külső óraadó') COLLATE utf8_hungarian_ci DEFAULT 'határozatlan idejű',
  `hetiKotelezoOraszam` decimal(3,1) DEFAULT '0.0',
  `megjegyzes` varchar(255) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `besorolas` enum('Gyakornok','Pedagógus I.','Pedagógus II.','Mesterpedagógus','Kutatótanár') COLLATE utf8_hungarian_ci DEFAULT 'Pedagógus I.',
  `hetiLekotottMinOraszam` decimal(3,1) DEFAULT '0.0',
  `hetiLekotottMaxOraszam` decimal(3,1) DEFAULT '0.0',
  `hetiKotottMaxOraszam` decimal(3,1) DEFAULT '0.0',
  `tovabbkepzesForduloDt` date DEFAULT NULL,
  `titulus` varchar(32) COLLATE utf8_hungarian_ci DEFAULT '',
  `titulusRovid` varchar(10) COLLATE utf8_hungarian_ci DEFAULT '',
  `email` varchar(64) COLLATE utf8_hungarian_ci DEFAULT '',
  PRIMARY KEY (`tanarId`),
  UNIQUE KEY `tanarOid` (`oId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE IF NOT EXISTS `tanarTelephely` (
  `tanarId` int(10) unsigned NOT NULL,
  `telephelyId` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`tanarId`,`telephelyId`),
  KEY `tanarTelephely_ibfk_1` (`tanarId`),
  CONSTRAINT `tanarTelephely_ibfk_1` FOREIGN KEY (`tanarId`) REFERENCES `tanar` (`tanarId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `tankor` (
  `tankorId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `targyId` smallint(5) unsigned NOT NULL,
  `kovetelmeny` enum('jegy','magatartás','szorgalom','négyszintű (szöveges minősítés)','féljegy','százalékos','aláírás','háromszintű','egyedi felsorolás','szöveges szempontrendszer','teljesített óra','nincs') COLLATE utf8_hungarian_ci DEFAULT NULL,
  `_jelenlet` enum('kötelező','nem kötelező') COLLATE utf8_hungarian_ci DEFAULT NULL,
  `felveheto` tinyint(3) unsigned DEFAULT NULL,
  `cn` varchar(64) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `min` tinyint(3) unsigned NOT NULL,
  `max` tinyint(3) unsigned NOT NULL,
  `_tankorTipus` enum('tanórai','tanórán kívüli','első nyelv','második nyelv','egyéni foglalkozás','délutáni') COLLATE utf8_hungarian_ci DEFAULT 'tanórai',
  `tankorTipusId` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`tankorId`),
  KEY `tankor_FKIndex1` (`targyId`),
  CONSTRAINT `tankor_ibfk_1` FOREIGN KEY (`targyId`) REFERENCES `targy` (`targyId`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci ;

CREATE TABLE `tankorTipus` (
  `tankorTipusId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `oratervi` enum('óratervi','tanórán kívüli') COLLATE utf8_hungarian_ci DEFAULT 'óratervi',
  `rovidNev` varchar(30) COLLATE utf8_hungarian_ci NOT NULL,
  `leiras` varchar(255) COLLATE utf8_hungarian_ci NOT NULL,
  `jelenlet` enum('kötelező','nem kötelező') COLLATE utf8_hungarian_ci NOT NULL,
  `regisztralando` enum('igen','nem') COLLATE utf8_hungarian_ci DEFAULT NULL,
  `hianyzasBeleszamit` enum('igen','nem') COLLATE utf8_hungarian_ci DEFAULT NULL,
  `jelleg` enum('elmélet','gyakorlat') COLLATE utf8_hungarian_ci DEFAULT 'elmélet',
  `nevsor` enum('állandó','változtatható') COLLATE utf8_hungarian_ci DEFAULT NULL,
  `tankorJel` varchar(3) COLLATE utf8_hungarian_ci DEFAULT NULL,
  PRIMARY KEY (`tankorTipusId`),
  KEY `rovidNev` (`rovidNev`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

INSERT INTO `tankorTipus` VALUES (1,'óratervi','óratervi','Óratervi (képzési hálóban kötelező) tanóra','kötelező','igen','igen','elmélet','állandó',''),
(2,'óratervi','első nyelv','Óratervi (képzési hálóban kötelező) tanóra - első nyelv','kötelező','igen','igen','elmélet','állandó','I'),
(3,'óratervi','második nyelv','Óratervi (képzési hálóban kötelező) tanóra - második nyelv','kötelező','igen','igen','elmélet','állandó','II'),
(4,'tanórán kívüli','tanórán kívüli','Nem óratervi (képzési hálóban nem szerepló) óra jellegű foglalkozás','nem kötelező','igen','nem','elmélet','állandó',''),
(5,'tanórán kívüli','szakkör','Nem óratervi (képzési hálóban nem szerepló) óra jellegű foglalkozás - szakkör','nem kötelező','igen','nem','elmélet','állandó',''),
(6,'tanórán kívüli','edzés','Nem óratervi (képzési hálóban nem szerepló) óra jellegű foglalkozás - edzés','nem kötelező','igen','nem','elmélet','állandó',''),
(7,'tanórán kívüli','kórus','Nem óratervi (képzési hálóban nem szerepló) óra jellegű foglalkozás - kórus','nem kötelező','igen','nem','elmélet','állandó',''),
(8,'tanórán kívüli','tanulószoba','Nem óratervi (képzési hálóban nem szerepló) óra jellegű foglalkozás - tanulószoba','nem kötelező','igen','nem','elmélet','változtatható',''),
(9,'tanórán kívüli','napközi','Nem óratervi (képzési hálóban nem szerepló) óra jellegű foglalkozás - napközi','nem kötelező','igen','nem','elmélet','változtatható',''),
(10,'tanórán kívüli','egyéni foglalkozás','Nem óratervi (képzési hálóban nem szerepló) óra jellegű foglalkozás - egyéni foglalkozás','nem kötelező','igen','nem','elmélet','változtatható',''),
(11,'óratervi','gyakorlat','Óratervi (képzési hálóban kötelező) gyakorlat','kötelező','igen','igen','gyakorlat','változtatható',''),
(12,'tanórán kívüli','közösségi szolgálat','Közösségi szolgálat','nem kötelező','igen','nem','gyakorlat','állandó',''),
(13,'óratervi','könyvtár','Könyvtári osztályfüggetlen elfoglaltság (nyitva tartás)', 'nem kötelező','nem','nem','osztályfüggetlen','állandó',''),
(14,'óratervi','gyakorlat állandó tagokkal','Óratervi (képzési hálóban kötelező) gyakorlat állandó tagokkal', 'kötelező','igen','igen','gyakorlat','állandó','')
;

CREATE TABLE `feladatTipus` (
  `feladatTipusId` tinyint(3) unsigned NOT NULL,
  `feladatTipusLeiras` varchar(255) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `beszamithatoMaxOra` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`feladatTipusId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

INSERT INTO feladatTipus (feladatTipusId,feladatTipusLeiras,beszamithatoMaxOra) VALUES (1,'felkészülés foglalkozásra, tanórára',10);
INSERT INTO feladatTipus (feladatTipusId,feladatTipusLeiras,beszamithatoMaxOra) VALUES (2,'diákok teljesítményének értékelése',10);
INSERT INTO feladatTipus (feladatTipusId,feladatTipusLeiras,beszamithatoMaxOra) VALUES (31,'kulturális és sportélet, valamint szabadidő szervezése',10);
INSERT INTO feladatTipus (feladatTipusId,feladatTipusLeiras,beszamithatoMaxOra) VALUES (32,'versenyeztetés, versenyfelkészítés',10);
INSERT INTO feladatTipus (feladatTipusId,feladatTipusLeiras,beszamithatoMaxOra) VALUES (4,'diákönkormányzat segítése',10);
INSERT INTO feladatTipus (feladatTipusId,feladatTipusLeiras,beszamithatoMaxOra) VALUES (51,'felügyelet (pl. versenyeken, rendezvényeken)',10);
INSERT INTO feladatTipus (feladatTipusId,feladatTipusLeiras,beszamithatoMaxOra) VALUES (6,'balesetmegelőzés',10);
INSERT INTO feladatTipus (feladatTipusId,feladatTipusLeiras,beszamithatoMaxOra) VALUES (7,'gyermek- és ifjúságvédelmi feladat',10);
INSERT INTO feladatTipus (feladatTipusId,feladatTipusLeiras,beszamithatoMaxOra) VALUES (9,'adminisztráció, dokumentumkészítés',10);
INSERT INTO feladatTipus (feladatTipusId,feladatTipusLeiras,beszamithatoMaxOra) VALUES (11,'szülői értekezlet, fogadóóra (kapcsolattartás)',10);
INSERT INTO feladatTipus (feladatTipusId,feladatTipusLeiras,beszamithatoMaxOra) VALUES (12,'osztályfőnöki feladat',10);
INSERT INTO feladatTipus (feladatTipusId,feladatTipusLeiras,beszamithatoMaxOra) VALUES (13,'mentorálás',10);
INSERT INTO feladatTipus (feladatTipusId,feladatTipusLeiras,beszamithatoMaxOra) VALUES (14,'nevelőtestületi, munkaközösségi feladat',10);
INSERT INTO feladatTipus (feladatTipusId,feladatTipusLeiras,beszamithatoMaxOra) VALUES (16,'intézményfejlesztés, -karbantartás',10);
INSERT INTO feladatTipus (feladatTipusId,feladatTipusLeiras,beszamithatoMaxOra) VALUES (17,'környezeti nevelés',10);
INSERT INTO feladatTipus (feladatTipusId,feladatTipusLeiras,beszamithatoMaxOra) VALUES (20,'telephelyközi utazás',10);
INSERT INTO feladatTipus (feladatTipusId,feladatTipusLeiras,beszamithatoMaxOra) VALUES (22,'iskolai rendezvény',10);
INSERT INTO feladatTipus (feladatTipusId,feladatTipusLeiras,beszamithatoMaxOra) VALUES (23,'nem rendszeres foglalkozás (korrepetálás, tehetséggondozás)',10);
INSERT INTO feladatTipus (feladatTipusId,feladatTipusLeiras,beszamithatoMaxOra) VALUES (230,'továbbképzés - felkészülés minősítésre, ellenőrzésre',10);

CREATE TABLE `osztaly` (
  `osztalyId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `leiras` varchar(64) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `kezdoTanev` smallint(5) unsigned DEFAULT NULL,
  `vegzoTanev` smallint(5) unsigned DEFAULT NULL,
  `jel` varchar(20) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `_kezdoEvfolyam` tinyint(3) unsigned DEFAULT NULL,
  `kezdoEvfolyamSorszam` tinyint(3) unsigned DEFAULT NULL,
  `telephelyId` tinyint(3) unsigned DEFAULT NULL,
  `osztalyJellegId` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`osztalyId`),
  KEY `osztaly_telephely` (`telephelyId`),
  KEY `osztaly_ibfk_1` (`osztalyJellegId`),
  CONSTRAINT `osztaly_ibfk_1` FOREIGN KEY (`osztalyJellegId`) REFERENCES `mayor_naplo`.`osztalyJelleg` (`osztalyJellegId`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `osztaly_telephely` FOREIGN KEY (`telephelyId`) REFERENCES `telephely` (`telephelyId`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `osztalyDiak` (
  `osztalyId` int(10) unsigned NOT NULL,
  `diakId` int(10) unsigned NOT NULL,
  `beDt` date NOT NULL DEFAULT '0000-00-00',
  `kiDt` date DEFAULT NULL,
  PRIMARY KEY (`osztalyId`,`diakId`,`beDt`),
  KEY `osztalyTag_FKIndex1` (`osztalyId`),
  KEY `osztalyDiak_FKIndex2` (`diakId`),
  CONSTRAINT `osztalyDiak_ibfk_1` FOREIGN KEY (`osztalyId`) REFERENCES `osztaly` (`osztalyId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `osztalyDiak_ibfk_2` FOREIGN KEY (`diakId`) REFERENCES `diak` (`diakId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `mkTanar` (
  `mkId` smallint(5) unsigned NOT NULL,
  `tanarId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`mkId`,`tanarId`),
  KEY `mkTag_FKIndex1` (`mkId`),
  KEY `mkTag_FKIndex2` (`tanarId`),
  CONSTRAINT `mkTanar_ibfk_1` FOREIGN KEY (`mkId`) REFERENCES `munkakozosseg` (`mkId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `mkTanar_ibfk_2` FOREIGN KEY (`tanarId`) REFERENCES `tanar` (`tanarId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `osztalyTanar` (
  `osztalyId` int(10) unsigned NOT NULL,
  `tanarId` int(10) unsigned NOT NULL,
  `beDt` date NOT NULL DEFAULT '0000-00-00',
  `kiDt` date DEFAULT NULL,
  PRIMARY KEY (`osztalyId`,`tanarId`,`beDt`),
  KEY `osztalyTanar_FKIndex1` (`osztalyId`),
  KEY `osztalyTanar_FKIndex2` (`tanarId`),
  CONSTRAINT `osztalyTanar_ibfk_1` FOREIGN KEY (`osztalyId`) REFERENCES `osztaly` (`osztalyId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `osztalyTanar_ibfk_2` FOREIGN KEY (`tanarId`) REFERENCES `tanar` (`tanarId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `kepzes` (
  `kepzesId` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `kepzesNev` varchar(255) NOT NULL,
  `tanev` smallint(5) unsigned DEFAULT NULL,
  `_kezdoEvfolyam` tinyint(3) unsigned DEFAULT NULL,
  `_zaroEvfolyam` tinyint(3) unsigned DEFAULT NULL,
  `kepzesEles` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `osztalyJellegId` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`kepzesId`),
  UNIQUE KEY `kepzesNevTanev` (`kepzesNev`,`tanev`),
  KEY `kepzes_ibfk_1` (`osztalyJellegId`),
  CONSTRAINT `kepzes_ibfk_1` FOREIGN KEY (`osztalyJellegId`) REFERENCES `mayor_naplo`.`osztalyJelleg` (`osztalyJellegId`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `szempontRendszer` (
  `szrId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `kepzesId` smallint(5) unsigned DEFAULT NULL,
  `_evfolyam` tinyint(5) unsigned NOT NULL,
  `evfolyamJel` varchar(32) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `targyId` smallint(5) unsigned DEFAULT NULL,
  `targyTipus` enum('első nyelv','második nyelv','választható','kötelezően választható') COLLATE utf8_hungarian_ci DEFAULT NULL,
  `tanev` smallint(5) unsigned NOT NULL,
  `szemeszter` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`szrId`),
  KEY `szr_FKindex1` (`kepzesId`),
  KEY `szr_FKindex2` (`targyId`),
  KEY `szr_FKIndex3` (`tanev`,`szemeszter`),
  CONSTRAINT `szempontRendszer_ibfk_1` FOREIGN KEY (`kepzesId`) REFERENCES `kepzes` (`kepzesId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `szempontRendszer_ibfk_2` FOREIGN KEY (`targyId`) REFERENCES `targy` (`targyId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `szempontRendszer_ibfk_3` FOREIGN KEY (`tanev`, `szemeszter`) REFERENCES `szemeszter` (`tanev`, `szemeszter`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `szrSzempont` (
  `szempontId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `szrId` int(10) unsigned NOT NULL,
  `szempont` varchar(128) COLLATE utf8_hungarian_ci DEFAULT NULL,
  PRIMARY KEY (`szempontId`),
  KEY `szrsz_FKindex1` (`szrId`),
  CONSTRAINT `szrSzempont_ibfk_1` FOREIGN KEY (`szrId`) REFERENCES `szempontRendszer` (`szrId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `szrMinosites` (
  `minositesId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `szempontId` int(10) unsigned NOT NULL,
  `minosites` varchar(255) COLLATE utf8_hungarian_ci DEFAULT NULL,
  PRIMARY KEY (`minositesId`),
  KEY `szrm_FKindex1` (`szempontId`),
  CONSTRAINT `szrMinosites_ibfk_1` FOREIGN KEY (`szempontId`) REFERENCES `szrSzempont` (`szempontId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `szovegesErtekeles` (
  `szeId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `diakId` int(10) unsigned NOT NULL,
  `szrId` int(10) unsigned NOT NULL,
  `targyId` smallint(5) unsigned NOT NULL,
  `dt` date NOT NULL,
  `tanev` smallint(5) unsigned NOT NULL,
  `szemeszter` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`szeId`),
  UNIQUE KEY `sze_UKindex1` (`diakId`,`targyId`,`tanev`,`szemeszter`),
  KEY `sze_FKindex1` (`diakId`),
  KEY `sze_FKindex2` (`szrId`),
  KEY `sze_FKindex3` (`targyId`),
  KEY `sze_FKIndex4` (`tanev`,`szemeszter`),
  CONSTRAINT `szovegesErtekeles_ibfk_1` FOREIGN KEY (`diakId`) REFERENCES `diak` (`diakId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `szovegesErtekeles_ibfk_2` FOREIGN KEY (`targyId`) REFERENCES `targy` (`targyId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `szovegesErtekeles_ibfk_3` FOREIGN KEY (`szrId`) REFERENCES `szempontRendszer` (`szrId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `szovegesErtekeles_ibfk_4` FOREIGN KEY (`tanev`, `szemeszter`) REFERENCES `szemeszter` (`tanev`, `szemeszter`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `szeEgyediMinosites` (
  `szeId` int(10) unsigned NOT NULL,
  `szempontId` int(10) unsigned NOT NULL,
  `egyediMinosites` varchar(255) COLLATE utf8_hungarian_ci DEFAULT NULL,
  PRIMARY KEY (`szeId`,`szempontId`),
  KEY `szeem_FKindex1` (`szempontId`),
  KEY `szeem_FKindex2` (`szeId`),
  CONSTRAINT `szeEgyediMinosites_ibfk_1` FOREIGN KEY (`szempontId`) REFERENCES `szrSzempont` (`szempontId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `szeEgyediMinosites_ibfk_2` FOREIGN KEY (`szeId`) REFERENCES `szovegesErtekeles` (`szeId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `szeMinosites` (
  `szeId` int(10) unsigned NOT NULL,
  `minositesId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`szeId`,`minositesId`),
  KEY `szem_FKindex1` (`szeId`),
  KEY `minositesId` (`minositesId`),
  CONSTRAINT `szeMinosites_ibfk_1` FOREIGN KEY (`szeId`) REFERENCES `szovegesErtekeles` (`szeId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `szeMinosites_ibfk_2` FOREIGN KEY (`minositesId`) REFERENCES `szrMinosites` (`minositesId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `tankorDiak` (
  `tankorId` int(10) unsigned NOT NULL,
  `diakId` int(10) unsigned NOT NULL,
  `beDt` date NOT NULL DEFAULT '0000-00-00',
  `kiDt` date DEFAULT NULL,
  `_jelenlet` enum('kötelező','nem kötelező') COLLATE utf8_hungarian_ci DEFAULT 'kötelező',
  `_kovetelmeny` enum('aláírás','vizsga','jegy') COLLATE utf8_hungarian_ci DEFAULT 'jegy',
  `jovahagyva` tinyint(3) unsigned DEFAULT '1',
  PRIMARY KEY (`tankorId`,`diakId`,`beDt`),
  KEY `tankorTag_FKIndex1` (`tankorId`),
  KEY `diakId` (`diakId`),
  CONSTRAINT `tankorDiak_ibfk_1` FOREIGN KEY (`tankorId`) REFERENCES `tankor` (`tankorId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tankorDiak_ibfk_2` FOREIGN KEY (`diakId`) REFERENCES `diak` (`diakId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci ;

CREATE TABLE `tankorDiakFelmentes` (
  `tankorId` int(10) unsigned NOT NULL,
  `diakId` int(10) unsigned NOT NULL,
  `beDt` date NOT NULL DEFAULT '0000-00-00',
  `kiDt` date DEFAULT NULL,
  `felmentesTipus` enum('óralátogatás alól','értékelés alól') COLLATE utf8_hungarian_ci NOT NULL DEFAULT 'óralátogatás alól',
  `nap` tinyint(3) unsigned DEFAULT NULL,
  `ora` tinyint(3) unsigned DEFAULT NULL,
  `tankorDiakFelmentesId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `iktatoszam` varchar(60) COLLATE utf8_hungarian_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`tankorDiakFelmentesId`),
  KEY `tankorDiakFM_FKIndex1` (`tankorId`),
  KEY `diakId` (`diakId`),
  CONSTRAINT `tankorDiakFM_ibfk_1` FOREIGN KEY (`tankorId`) REFERENCES `tankor` (`tankorId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tankorDiakFM_ibfk_2` FOREIGN KEY (`diakId`) REFERENCES `diak` (`diakId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `tankorOsztaly` (
  `tankorId` int(10) unsigned NOT NULL,
  `osztalyId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`tankorId`,`osztalyId`),
  KEY `tankorOsztaly_FKIndex1` (`tankorId`),
  KEY `tankorOsztaly_FKIndex2` (`osztalyId`),
  CONSTRAINT `tankorOsztaly_ibfk_1` FOREIGN KEY (`tankorId`) REFERENCES `tankor` (`tankorId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tankorOsztaly_ibfk_2` FOREIGN KEY (`osztalyId`) REFERENCES `osztaly` (`osztalyId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `tankorSzemeszter` (
  `tankorId` int(10) unsigned NOT NULL,
  `tanev` smallint(5) unsigned NOT NULL,
  `szemeszter` tinyint(3) unsigned NOT NULL,
  `oraszam` decimal(4,2) DEFAULT NULL,
  `tankorNev` varchar(128) COLLATE utf8_hungarian_ci DEFAULT NULL,
  PRIMARY KEY (`tankorId`,`tanev`,`szemeszter`),
  KEY `tankorTanev_FKIndex1` (`tankorId`),
  KEY `tankorTanev_FKIndex2` (`tanev`,`szemeszter`),
  CONSTRAINT `tankorSzemeszter_ibfk_1` FOREIGN KEY (`tankorId`) REFERENCES `tankor` (`tankorId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tankorSzemeszter_ibfk_2` FOREIGN KEY (`tanev`, `szemeszter`) REFERENCES `szemeszter` (`tanev`, `szemeszter`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `tankorTanar` (
  `tankorId` int(10) unsigned NOT NULL,
  `tanarId` int(10) unsigned NOT NULL,
  `beDt` date NOT NULL DEFAULT '0000-00-00',
  `kiDt` date DEFAULT NULL,
  PRIMARY KEY (`tankorId`,`tanarId`,`beDt`),
  KEY `tankorTanar_FKIndex1` (`tankorId`),
  KEY `tankorTanar_FKIndex2` (`tanarId`),
  CONSTRAINT `tankorTanar_ibfk_1` FOREIGN KEY (`tankorId`) REFERENCES `tankor` (`tankorId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tankorTanar_ibfk_2` FOREIGN KEY (`tanarId`) REFERENCES `tanar` (`tanarId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `terem` (
  `teremId` smallint(5) unsigned NOT NULL,
  `leiras` varchar(64) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `ferohely` tinyint(3) unsigned DEFAULT NULL,
  `tipus` set('tanterem','szaktanterem','osztályterem','labor','gépterem','tornaterem','tornaszoba','fejlesztőszoba','tanműhely','előadó','könyvtár','díszterem','tanári','templom','egyéb','megszűnt') COLLATE utf8_hungarian_ci DEFAULT NULL,
  `telephelyId` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`teremId`),
  KEY `terem_telephely` (`telephelyId`),
  CONSTRAINT `terem_telephely` FOREIGN KEY (`telephelyId`) REFERENCES `telephely` (`telephelyId`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `teremPreferencia` (
  `teremPreferenciaId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tanarId` int(10) unsigned NOT NULL,
  `targyId` smallint(5) unsigned DEFAULT NULL,
  `teremStr` varchar(255) COLLATE utf8_hungarian_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`teremPreferenciaId`),
  KEY `teremPref_FKIndex1` (`tanarId`),
  KEY `teremPref_FKIndex2` (`targyId`),
  CONSTRAINT `teremPref_ibfk_1` FOREIGN KEY (`tanarId`) REFERENCES `tanar` (`tanarId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `teremPref_ibfk_2` FOREIGN KEY (`targyId`) REFERENCES `targy` (`targyId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `zaradek` (
  `zaradekId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `diakId` int(10) unsigned NOT NULL,
  `dt` date DEFAULT NULL,
  `sorszam` varchar(5) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `dokumentum` set('beírási napló','osztálynapló','törzslap','bizonyítvány') COLLATE utf8_hungarian_ci DEFAULT NULL,
  `szoveg` varchar(255) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `zaradekIndex` tinyint(3) unsigned DEFAULT NULL,
  `iktatoszam` varchar(60) COLLATE utf8_hungarian_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`zaradekId`),
  KEY `zaradek_FKIndex1` (`diakId`),
  CONSTRAINT `zaradek_ibfk_1` FOREIGN KEY (`diakId`) REFERENCES `diak` (`diakId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `zaroJegy` (
  `zaroJegyId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `diakId` int(10) unsigned NOT NULL,
  `targyId` smallint(5) unsigned NOT NULL,
  `evfolyam` tinyint(3) unsigned DEFAULT NULL,
  `evfolyamJel` varchar(32) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `felev` tinyint(3) unsigned DEFAULT NULL,
  `jegy` decimal(4,1) NOT NULL,
  `jegyTipus` enum('jegy','magatartás','szorgalom','négyszintű (szöveges minősítés)','százalékos','aláírás','háromszintű','egyedi felsorolás','nem értékelhető','teljesített óra') COLLATE utf8_hungarian_ci NOT NULL DEFAULT 'jegy',
  `megjegyzes` enum('dicséret','figyelmeztető') COLLATE utf8_hungarian_ci DEFAULT NULL,
  `modositasDt` datetime NOT NULL,
  `hivatalosDt` date NOT NULL,
  PRIMARY KEY (`zaroJegyId`),
  KEY `zaroJegy_FKIndex2` (`diakId`),
  KEY `zaroJegy_FKIndex3` (`targyId`),
  CONSTRAINT `zaroJegy_ibfk_2` FOREIGN KEY (`diakId`) REFERENCES `diak` (`diakId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `zaroJegy_ibfk_3` FOREIGN KEY (`targyId`) REFERENCES `targy` (`targyId`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `zaroJegyZaradek` (
  `zaroJegyId` int(10) unsigned NOT NULL,
  `zaradekId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`zaradekId`,`zaroJegyId`),
  KEY `zaroJegyId` (`zaroJegyId`),
  CONSTRAINT `zaroJegyZaradek_ibfk_1` FOREIGN KEY (`zaradekId`) REFERENCES `zaradek` (`zaradekId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `zaroJegyZaradek_ibfk_2` FOREIGN KEY (`zaroJegyId`) REFERENCES `zaroJegy` (`zaroJegyId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `vizsga` (
  `vizsgaId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `diakId` int(10) unsigned NOT NULL,
  `targyId` smallint(5) unsigned NOT NULL,
  `evfolyam` tinyint(3) unsigned NOT NULL,
  `evfolyamJel` varchar(32) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `felev` tinyint(3) unsigned DEFAULT NULL,
  `tipus` enum('osztályozó vizsga','beszámoltatóvizsga','különbözetivizsga','javítóvizsga') COLLATE utf8_hungarian_ci DEFAULT NULL,
  `jelentkezesDt` date NOT NULL,
  `vizsgaDt` date DEFAULT NULL,
  `zaradekId` int(10) unsigned DEFAULT NULL,
  `zaroJegyId` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`vizsgaId`),
  KEY `vizsga_FKIndex1` (`diakId`),
  KEY `vizsga_FKIndex2` (`targyId`),
  KEY `zaradekId` (`zaradekId`),
  KEY `zaroJegyId` (`zaroJegyId`),
  CONSTRAINT `vizsga_ibfk_1` FOREIGN KEY (`diakId`) REFERENCES `diak` (`diakId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `vizsga_ibfk_2` FOREIGN KEY (`targyId`) REFERENCES `targy` (`targyId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `vizsga_ibfk_3` FOREIGN KEY (`zaradekId`) REFERENCES `zaradek` (`zaradekId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `vizsga_ibfk_4` FOREIGN KEY (`zaroJegyId`) REFERENCES `zaroJegy` (`zaroJegyId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `kepzesDiak` (
  `kepzesId` smallint(5) unsigned NOT NULL,
  `diakId` int(10) unsigned NOT NULL,
  `tolDt` date default NULL,
  `igDt` date default NULL,
  PRIMARY KEY (`kepzesId`,`diakId`,`tolDt`),
  KEY `kepzesDiak_FKIndex1` (`kepzesId`),
  KEY `kepzesDiak_FKIndex2` (`diakId`),
  CONSTRAINT `kepzesDiak_ibfk_1` FOREIGN KEY (`kepzesId`) REFERENCES `kepzes` (`kepzesId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `kepzesDiak_ibfk_2` FOREIGN KEY (`diakId`) REFERENCES `diak` (`diakId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `kepzesOraszam` (
  `kepzesId` smallint(5) unsigned NOT NULL,
  `evfolyamJel` varchar(32) CHARACTER SET utf8 COLLATE utf8_hungarian_ci NOT NULL,
  `kotelezoOraszam` decimal(4,2) unsigned DEFAULT NULL,
  `maximalisOraszam` decimal(4,2) unsigned DEFAULT NULL,
  `tanitasiHetekSzama` smallint(5) unsigned DEFAULT NULL,
  PRIMARY KEY (`kepzesId`,`evfolyamJel`),
  KEY `kepzesOraszam_FKIndex1` (`kepzesId`),
  CONSTRAINT `kepzesOraszam_ibfk_1` FOREIGN KEY (`kepzesId`) REFERENCES `kepzes` (`kepzesId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `kepzesOraterv` (
  `kepzesOratervId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `kepzesId` smallint(5) unsigned NOT NULL,
  `targyId` smallint(5) unsigned DEFAULT NULL,
  `evfolyamJel` varchar(32) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `szemeszter` tinyint(3) unsigned NOT NULL,
  `hetiOraszam` decimal(4,2) DEFAULT NULL,
  `kovetelmeny` enum('jegy','négyszintű (szöveges minősítés)','százalékos','aláírás','háromszintű','egyedi felsorolás','szöveges szempontrendszer','teljesített óra','nincs') COLLATE utf8_hungarian_ci DEFAULT NULL,
  `tipus` enum('első nyelv','második nyelv','mintatantervi','kötelezően választható 1.','kötelezően választható 2.','szabadon választható 1.','szabadon választható 2.','művészetek') COLLATE utf8_hungarian_ci DEFAULT 'mintatantervi',
  PRIMARY KEY (`kepzesOratervId`),
  UNIQUE KEY `kot_kulcs2` (`kepzesId`,`targyId`,`evfolyamJel`,`szemeszter`),
  KEY `kepzesOraterv_FKIndex1` (`targyId`),
  CONSTRAINT `kepzesOraterv_ibfk_1` FOREIGN KEY (`targyId`) REFERENCES `targy` (`targyId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `kepzesOraterv_ibfk_2` FOREIGN KEY (`kepzesId`) REFERENCES `kepzes` (`kepzesId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `kepzesOsztaly` (
  `kepzesId` smallint(5) unsigned NOT NULL,
  `osztalyId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`kepzesId`,`osztalyId`),
  KEY `kepzesOsztaly_FKIndex1` (`kepzesId`),
  KEY `kepzesOsztaly_FKIndex2` (`osztalyId`),
  CONSTRAINT `kepzesOsztaly_ibfk_1` FOREIGN KEY (`kepzesId`) REFERENCES `kepzes` (`kepzesId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `kepzesOsztaly_ibfk_2` FOREIGN KEY (`osztalyId`) REFERENCES `osztaly` (`osztalyId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- DROP in rev. 3562
-- CREATE TABLE `kepzesTargyOraszam` (
--   `kepzesId` smallint(5) unsigned NOT NULL,
--   `evfolyam` tinyint(3) unsigned NOT NULL,
--   `targyId` smallint(5) unsigned NOT NULL,
--   `oraszam` decimal(4,2) unsigned DEFAULT NULL,
--   `kovetelmeny` enum('aláírás','vizsga','jegy') DEFAULT NULL,
--   `jelenlet` enum('kötelező','nem kötelező') DEFAULT NULL,
--   PRIMARY KEY (`kepzesId`,`evfolyam`,`targyId`),
--   KEY `kepzesTargyOraszam_FKIndex1` (`kepzesId`),
--   KEY `kepzesTargyOraszam_FKIndex2` (`targyId`),
--   CONSTRAINT `kepzesTargyOraszam_ibfk_1` FOREIGN KEY (`kepzesId`) REFERENCES `kepzes` (`kepzesId`) ON DELETE CASCADE ON UPDATE CASCADE,
--   CONSTRAINT `kepzesTargyOraszam_ibfk_2` FOREIGN KEY (`targyId`) REFERENCES `targy` (`targyId`) ON DELETE NO ACTION ON UPDATE NO ACTION
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tanmenet` (
  `tanmenetId` int(10) unsigned NOT NULL auto_increment,
  `targyId` smallint(5) unsigned NOT NULL,
  `_evfolyam` tinyint(3) unsigned NOT NULL,
  `evfolyamJel` varchar(32) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `tanmenetNev` varchar(128),
  `oraszam` smallint unsigned,
  `dt` DATE NOT NULL,
  `tanarId` int(10) unsigned NOT NULL,
  `statusz` enum('új','kész','jóváhagyott','publikus','elavult') default 'új',
  PRIMARY KEY (`tanmenetId`),
  CONSTRAINT `tanmenet_ibfk_1` FOREIGN KEY (`targyId`) REFERENCES `targy` (`targyId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tanmenet_ibfk_2` FOREIGN KEY (`tanarId`) REFERENCES `tanar` (`tanarId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `tanmenetTemakor` (
  `tanmenetId` int(10) unsigned NOT NULL,
  `sorszam` tinyint unsigned NOT NULL,
  `oraszam` tinyint unsigned NOT NULL,
  `temakorMegnevezes` text,
  PRIMARY KEY (`tanmenetId`, `sorszam` ),
  CONSTRAINT `tanmenetTemakor_ibfk_1` FOREIGN KEY (`tanmenetId`) REFERENCES `tanmenet` (`tanmenetId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `tanmenetTankor` (
  `tankorId` int(10) unsigned NOT NULL,
  `tanev` smallint(5) unsigned NOT NULL,
  `tanmenetId` int(10) unsigned NOT NULL,
  KEY `tanmenetTankor_FKIndex1` (`tankorId`),
  CONSTRAINT `tanmenetTankor_ibfk_1` FOREIGN KEY (`tankorId`) REFERENCES `tankor` (`tankorId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tanmenetTankor_ibfk_2` FOREIGN KEY (`tanmenetId`) REFERENCES `tanmenet` (`tanmenetId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tanmenetTankor_ibfk_3` FOREIGN KEY (`tanev`) REFERENCES `szemeszter` (`tanev`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `bejegyzesTipus` (
  `bejegyzesTipusId` tinyint unsigned NOT NULL auto_increment,
  `tipus` enum('dicséret','fegyelmi','üzenet') COLLATE utf8_hungarian_ci NOT NULL,
  `fokozat` tinyint(3) unsigned NOT NULL,
  `bejegyzesTipusNev` varchar(128),
  `hianyzasDb` tinyint(3) unsigned DEFAULT NULL,
  `jogosult` SET('szaktanár','osztályfőnök','vezetőség','admin') COLLATE utf8_hungarian_ci DEFAULT NULL,
  `tolDt` date DEFAULT NULL,
  `igDt` date DEFAULT NULL,
  PRIMARY KEY (`bejegyzesTipusId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

INSERT INTO `bejegyzesTipus` VALUES 
(1,'fegyelmi',1,'szaktanári figyelmeztetés',NULL,'szaktanár,admin','2003-01-01',NULL),
(2,'fegyelmi',2,'szóbeli osztályfőnöki figyelmeztetés',1,'osztályfőnök,admin','2003-01-01',NULL),
(3,'fegyelmi',3,'osztályfőnöki figyelmeztetés',2,'osztályfőnök,admin','2003-01-01',NULL),
(4,'fegyelmi',4,'osztályfőnöki intő',3,'osztályfőnök,admin','2003-01-01',NULL),
(5,'fegyelmi',5,'osztályfőnöki rovó',4,'osztályfőnök,admin','2003-01-01',NULL),
(6,'fegyelmi',6,'igazgatói figyelmeztető',5,'vezetőség,admin','2003-01-01',NULL),
(7,'fegyelmi',7,'igazgatói intő',7,'vezetőség,admin','2003-01-01',NULL),
(8,'fegyelmi',8,'igazgatói rovó',9,'vezetőség,admin','2003-01-01',NULL),
(9,'fegyelmi',9,'nevelőtestületi figyelmeztetés',11,'vezetőség,admin','2003-01-01',NULL),
(10,'fegyelmi',10,'nevelőtestületi intés',NULL,'vezetőség,admin','2003-01-01',NULL),
(11,'fegyelmi',11,'nevelőtestületi megrovás',NULL,'vezetőség,admin','2003-01-01',NULL),
(12,'dicséret',1,'szaktanári dicséret',0,'szaktanár,admin','2003-01-01',NULL),
(13,'dicséret',2,'osztályfőnöki dicséret',NULL,'osztályfőnök,admin','2003-01-01',NULL),
(14,'dicséret',3,'igazgatói dicséret',NULL,'vezetőség,admin','2003-01-01',NULL),
(15,'dicséret',4,'nevelőtestületi dicséret',NULL,'vezetőség,admin','2003-01-01',NULL),
(16,'üzenet',0,'üzenet',NULL,'szaktanár,osztályfőnök,vezetőség,admin','2003-01-01',NULL);

CREATE TABLE `csengetesiRend` (
  `nap` tinyint(3) unsigned DEFAULT NULL,
  `ora` tinyint(3) unsigned NOT NULL,
  `tolTime` time DEFAULT NULL,
  `igTime` time DEFAULT NULL,
  `telephelyId` tinyint(3) unsigned DEFAULT NULL,
  `csengetesiRendTipus` enum('normál','rövidített','speciális','rendhagyó','délutáni','délutáni rövidített','délutáni speciális','délutáni rendhagyó','nincs') COLLATE utf8_hungarian_ci DEFAULT 'normál',
  KEY `csengetesiRend_telephely` (`telephelyId`),
  CONSTRAINT `csengetesiRend_telephely` FOREIGN KEY (`telephelyId`) REFERENCES `telephely` (`telephelyId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `kosziEsemeny` (                                                                                                                                                                            
  `kosziEsemenyId` int(10) unsigned NOT NULL auto_increment,
  `kosziEsemenyNev` varchar(50) NOT NULL,
  `kosziEsemenyLeiras` varchar(255) NOT NULL,
  `kosziEsemenyTipus` enum('iskolai rendezvény','DÖK rendezvény','tanulmányi verseny','sportverseny','foglalkozás','tevékenység','hiányzás') COLLATE utf8_hungarian_ci NOT NULL,
  `kosziEsemenyIntervallum`  tinyint(1) UNSIGNED NULL DEFAULT 0,
  PRIMARY KEY (`kosziEsemenyId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;
CREATE TABLE `kosziPont` (                                                                                                                                                                            
  `kosziPontId` int(10) unsigned NOT NULL auto_increment,
  `kosziEsemenyId` int(10) unsigned NOT NULL,
  `kosziPontTipus` enum('résztvevő','segítő','szervező','fellépő (egyéni)','fellépő (osztály)','fellépő (csoport)','eredmény') NOT NULL DEFAULT 'résztvevő',
  `kosziPont` int(10) unsigned NOT NULL DEFAULT 0,
  `kosziHelyezes` int(10) unsigned NULL DEFAULT NULL,
  UNIQUE KEY (`kosziEsemenyId`,`kosziPontTipus`,`kosziHelyezes`),
  PRIMARY KEY (`kosziPontId`),
  CONSTRAINT `kosziEsemeny_ibfk_1` FOREIGN KEY (`kosziEsemenyId`) REFERENCES `kosziEsemeny` (`kosziEsemenyId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `diakAdatkezeles` (
  `diakId` int(10) unsigned NOT NULL,
  `kulcs` varchar(30) NOT NULL,
  `ertek` varchar(30) NOT NULL,
  `lastModified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY (`diakId`,`kulcs`),
  KEY `diakAdatkezeles_FKIndex1` (`diakId`),
  CONSTRAINT `diakAdatkezeles_ibfk_1` FOREIGN KEY (`diakId`) REFERENCES `diak` (`diakId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `diakTorzslapszam` (
  `osztalyId` int(10) unsigned NOT NULL,
  `diakId` int(10) unsigned NOT NULL,
  `torzslapszam` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`osztalyId`,`diakId`),
  KEY `diakTorzslapszam_ibfk_2` (`diakId`),
  CONSTRAINT `diakTorzslapszam_ibfk_1` FOREIGN KEY (`osztalyId`) REFERENCES `osztaly` (`osztalyId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `diakTorzslapszam_ibfk_2` FOREIGN KEY (`diakId`) REFERENCES `diak` (`diakId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `kepesites` (
  `kepesitesId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `vegzettseg` enum('','alapfokú','középfokú','felsőfokú') COLLATE utf8_hungarian_ci DEFAULT NULL,
  `fokozat` enum('','főiskolai','egyetemi','alapfokozat','mesterfokozat','tudományos fokozat') COLLATE utf8_hungarian_ci DEFAULT NULL,
  `specializacio` enum('','alapfokú szakképesítés','középfokú szakképesítés','emelt szintű szakképesítés','felsőfokú szakképesítés','szakképzettség') COLLATE utf8_hungarian_ci DEFAULT NULL,
  `kepesitesNev` varchar(255) COLLATE utf8_hungarian_ci DEFAULT NULL,
  PRIMARY KEY (`kepesitesId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `kepesitesTargy` (
  `kepesitesId` int(10) unsigned NOT NULL,
  `targyId` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`kepesitesId`,`targyId`),
  CONSTRAINT `kepesitesTargy_ibfk_1` FOREIGN KEY (`kepesitesId`) REFERENCES `kepesites` (`kepesitesId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `kepesitesTargy_ibfk_2` FOREIGN KEY (`targyId`) REFERENCES `targy` (`targyId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `tanarKepesites` (
  `tanarId` int(10) unsigned NOT NULL,
  `kepesitesId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`tanarId`,`kepesitesId`),
  KEY `tanarKepesites_FKIndex1` (`tanarId`),                                                                                                                                                               
  KEY `tanarKepesites_FKIndex2` (`kepesitesId`),                                                                                                                                                               
  CONSTRAINT `tanarKepesites_ibfk_1` FOREIGN KEY (`tanarId`) REFERENCES `tanar` (`tanarId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tanarKepesites_ibfk_2` FOREIGN KEY (`kepesitesId`) REFERENCES `kepesites` (`kepesitesId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;
  
CREATE TABLE IF NOT EXISTS `mkVezeto` (
  `mkId` smallint(5) unsigned NOT NULL,
  `tanarId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`mkId`,`tanarId`),
  KEY `mkTag_FKIndex1` (`mkId`),
  KEY `mkTag_FKIndex2` (`tanarId`),
  CONSTRAINT `mkVezeto_ibfk_1` FOREIGN KEY (`mkId`) REFERENCES `munkakozosseg` (`mkId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `mkVezeto_ibfk_2` FOREIGN KEY (`tanarId`) REFERENCES `tanar` (`tanarId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `tovabbkepzoIntezmeny` (
  `tovabbkepzoIntezmenyId` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `intezmenyRovidNev` varchar(32) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `intezmenyNev` varchar(255) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `intezmenyCim` varchar(255) COLLATE utf8_hungarian_ci DEFAULT NULL,
  PRIMARY KEY (`tovabbkepzoIntezmenyId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;
CREATE TABLE `tovabbkepzes` (
  `tovabbkepzesId` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `tovabbkepzesNev` varchar(255) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `tovabbkepzoIntezmenyId` smallint(5) unsigned NOT NULL,
  `oraszam` smallint(5) unsigned NOT NULL,
  `akkreditalt` tinyint unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`tovabbkepzesId`),
  KEY `tovabbkepzes_FK_1` (`tovabbkepzoIntezmenyId`),
  CONSTRAINT `tovabbkepzes_FK_1` FOREIGN KEY (`tovabbkepzoIntezmenyId`) REFERENCES `tovabbkepzoIntezmeny` (`tovabbkepzoIntezmenyId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;
CREATE TABLE `tovabbkepzesTanar` (
  `tovabbkepzesId` smallint(5) unsigned NOT NULL,
  `tanarId` int(10) unsigned not null,
  `tolDt` date NOT NULL,
  `igDt` date DEFAULT NULL,
  `tanusitvanyDt` date DEFAULT NULL,
  `tanusitvanySzam` varchar(255) COLLATE utf8_hungarian_ci DEFAULT NULL,
  PRIMARY KEY (`tovabbkepzesId`,`tanarId`),
  CONSTRAINT `tovabbkepzesTanar_ibfk_1` FOREIGN KEY (`tanarId`) REFERENCES `tanar` (`tanarId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tovabbkepzesTanar_ibfk_2` FOREIGN KEY (`tovabbkepzesId`) REFERENCES `tovabbkepzes` (`tovabbkepzesId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;
CREATE TABLE `tovabbkepzesTanulmanyiEgyseg` (
  `tovabbkepzesId` smallint(5) unsigned NOT NULL,
  `tanarId` int(10) unsigned NOT NULL,
  `tanev` year(4) NOT NULL,
  `reszosszeg` int(10) unsigned NOT NULL DEFAULT '0',
  `tamogatas` int(10) unsigned NOT NULL DEFAULT '0',
  `tovabbkepzesStatusz` enum('terv','jóváhagyott','elutasított','megszűnt','megszakadt','teljesített') COLLATE utf8_hungarian_ci DEFAULT 'terv',
  `tavollet` varchar(255) COLLATE utf8_hungarian_ci NOT NULL DEFAULT '',
  `helyettesitesRendje` varchar(255) COLLATE utf8_hungarian_ci NOT NULL DEFAULT '',
  `prioritas` varchar(255) COLLATE utf8_hungarian_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`tovabbkepzesId`,`tanarId`,`tanev`),
  KEY `tovabbkepzesTE_ibfk_1` (`tanarId`),
  CONSTRAINT `tovabbkepzesTE_ibfk_1` FOREIGN KEY (`tanarId`) REFERENCES `tanar` (`tanarId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tovabbkepzesTE_ibfk_2` FOREIGN KEY (`tovabbkepzesId`) REFERENCES `tovabbkepzes` (`tovabbkepzesId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tovabbkepzesTE_ibfk_main` FOREIGN KEY (`tovabbkepzesId`, `tanarId`) REFERENCES `tovabbkepzesTanar` (`tovabbkepzesId`, `tanarId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;
CREATE TABLE `tovabbkepzesKeret` (
  `tanev` year(4) NOT NULL,
  `keretOsszeg` int(10) unsigned NOT NULL,
  PRIMARY KEY (`tanev`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `hirnokFeliratkozas` (
  `hirnokFeliratkozasId` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `naploId` int(10) unsigned NOT NULL,
  `naploTipus` enum('diak','tanar','szulo') COLLATE utf8_hungarian_ci DEFAULT NULL,
  `userAccount` varchar(32) COLLATE utf8_hungarian_ci NOT NULL,
  `policy` enum('private','parent','public') COLLATE utf8_hungarian_ci DEFAULT 'private',
  `email` varchar(64) COLLATE utf8_hungarian_ci NOT NULL,
  `feliratkozasDt` datetime DEFAULT NULL,
  `utolsoEmailDt` datetime DEFAULT NULL,
  `megtekintesDt` datetime DEFAULT NULL,
  PRIMARY KEY (`hirnokFeliratkozasId`),
  UNIQUE KEY `K_hf_mix` (`userAccount`,`policy`,`naploId`,`naploTipus`,`email`),
  KEY `K_hf_up` (`userAccount`,`policy`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `diakNyelvvizsga` (
  `nyelvvizsgaId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `diakId` int(10) unsigned NOT NULL,
  `idegenNyelv` varchar(64) COLLATE utf8_hungarian_ci NOT NULL,
  `targyId` smallint(5) unsigned NOT NULL,
  `vizsgaSzint` enum('A2 szint (belépő)','B1 szint (alapfok)','B2 szint (középfok)','C1 szint (felsőfok)') COLLATE utf8_hungarian_ci DEFAULT NULL,
  `vizsgaTipus` enum('szóbeli','írásbeli','komplex') COLLATE utf8_hungarian_ci DEFAULT 'komplex',
  `vizsgaDt` date DEFAULT NULL,
  `vizsgaIntezmeny` varchar(64) COLLATE utf8_hungarian_ci NOT NULL,
  `vizsgaBizonyitvanySzam` varchar(32) COLLATE utf8_hungarian_ci NOT NULL,
  PRIMARY KEY (`nyelvvizsgaId`),
  KEY `diakNyelvvizsga_ibfk_1` (`diakId`),
  KEY `diakNyelvvizsga_ibfk_2` (`targyId`),
  CONSTRAINT `diakNyelvvizsga_ibfk_1` FOREIGN KEY (`diakId`) REFERENCES `diak` (`diakId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `diakNyelvvizsga_ibfk_2` FOREIGN KEY (`targyId`) REFERENCES `targy` (`targyId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

 DELIMITER //  
 DROP FUNCTION IF EXISTS diakNaploSorszam //                                                                                                                                                                                            
 CREATE function diakNaploSorszam ( thisDiakId INT, thisTanev INT, thisOsztalyId INT ) returns INT                                                                                                         
 READS SQL DATA                                                                                                                                                                                            
 BEGIN                                                                                                                                                                                                     
    DECLARE inKezdesDt,inZarasDt DATE;                                                                                                                                                                     
    DECLARE a,i INT; -- for loop                                                                                                                                                                           
    DECLARE b DATE; -- for loop                                                                                                                                                                            
    DECLARE c DATE;
    DECLARE d INT;
    DECLARE e VARCHAR(255);

    DECLARE cur1                                                                                                                                                                                           
        CURSOR FOR
-- Ez volt eredetileg:
-- ------------------
--        SELECT diakId,IF(beDt<inKezdesDt,inKezdesDt,beDt) AS d FROM osztalyDiak LEFT JOIN diak USING (diakId) 
--	WHERE osztalyId=thisOsztalyId AND beDt<=inZarasDt AND (kiDt IS NULL OR kiDt>=inKezdesDt) 
--	-- AND statusz!='felvételt nyert'
--	ORDER BY d, CONCAT_WS(' ',viseltCsaladinev,viseltUtonev) COLLATE utf8_hungarian_ci;
--
-- Első módosítás: az adottt tanévben nézi a jogviszonyt
-- ---------------
--	-- SELECT diakId,IF(beDt<inKezdesDt,inKezdesDt,beDt) AS d,
--	--     (SELECT COUNT(*) FROM diakJogviszony AS ds 
--	-- 	WHERE ds.diakId=diak.diakId AND inKezdesDt<dt AND dt<=inZarasDt AND ds.statusz IN ('jogviszonyban van','magántanuló')
--	--     ) AS aktJogviszonyDb,
--	--     (SELECT statusz FROM diakJogviszony AS ds 
--	-- 	WHERE ds.diakId=diak.diakId AND dt<=inKezdesDt ORDER BY dt DESC LIMIT 1
--	--     ) AS elozoStatusz 
--	-- FROM osztalyDiak LEFT JOIN diak USING (diakId) 
--	-- WHERE osztalyId=thisOsztalyId AND beDt<=inZarasDt AND (kiDt IS NULL OR kiDt>=inKezdesDt) 
--	-- HAVING (aktJogviszonyDb>0 or elozoStatusz in ('magántanuló','jogviszonyban van')) 
--	-- ORDER BY d, CONCAT_WS(' ',viseltCsaladinev,viseltUtonev) COLLATE utf8_hungarian_ci;
--
-- Második módosítás: Az adott tanév azon szakaszában nézi a jogviszonyt, amikor az osztálynak tagja...
-- ------------------
	SELECT diakId,IF(beDt<inKezdesDt,inKezdesDt,beDt) AS tolDt,IF(ifnull(kiDt,inZarasDt)<inZarasDt,kiDt,inZarasDt) AS igDt,
	    (SELECT COUNT(*) FROM diakJogviszony AS ds 
		WHERE ds.diakId=diak.diakId AND tolDt<dt AND dt<=igDt AND ds.statusz IN ('jogviszonyban van','magántanuló')
	    ) AS aktJogviszonyDb,
	    (SELECT statusz FROM diakJogviszony AS ds 
		WHERE ds.diakId=diak.diakId AND dt<=tolDt ORDER BY dt DESC LIMIT 1
	    ) AS elozoStatusz 
	FROM osztalyDiak LEFT JOIN diak USING (diakId) 
	WHERE osztalyId=thisOsztalyId AND beDt<=inZarasDt AND (kiDt IS NULL OR kiDt>=inKezdesDt) 
	HAVING (aktJogviszonyDb>0 or elozoStatusz in ('magántanuló','jogviszonyban van')) 
	ORDER BY tolDt, CONCAT_WS(' ',viseltCsaladinev,viseltUtonev) COLLATE utf8_hungarian_ci;

-- Ha RETURN, akkor az EXIT HANDLER úgy is, nem?
    -- DECLARE CONTINUE HANDLER FOR NOT FOUND RETURN NULL;
    DECLARE EXIT HANDLER FOR NOT FOUND RETURN NULL;
    SELECT kezdesDt FROM szemeszter WHERE tanev=thisTanev AND szemeszter=1 INTO inKezdesDt;
    SELECT MAX(zarasDt) FROM szemeszter WHERE tanev=thisTanev INTO inZarasDt;

    SET i := 1;                                                                                                                                                                                            
    OPEN cur1;                                                                                                                                                                                             
    lo: LOOP                                                                                                                                                                                               
--        FETCH cur1 INTO a,b;                                                                                                                                                                               
        FETCH cur1 INTO a,b,c,d,e;                                                                                                                                                                               
        IF a = thisDiakId THEN                                                                                                                                                                             
            LEAVE lo;                                                                                                                                                                                      
        END IF;                                                                                                                                                                                            
        SET i := i+1;                                                                                                                                                                                      
    END LOOP;                                                                                                                                                                                              
    CLOSE cur1;                                                                                                                                                                                            
    return i;                                                                                                                                                                                              
 END; //                                                                                                                                                                                                   
 DELIMITER ; //                                                                                                                                                                                            

-- DELIMITER //                                    
-- DROP FUNCTION IF EXISTS diakTorzslapszam //
-- CREATE function diakTorzslapszam ( thisDiakId INT, thisOsztalyId INT ) returns INT                                                                                                         
-- READS SQL DATA                                                                                                                                                                                            
-- BEGIN                                                                                                                                                                                                     
--    DECLARE i,d,n01,n02,n03,n04,n05,n06,n07,n08,n09,n10,n11,n12,n13 INT; -- for loop                                                                                                                                                                           
--    DECLARE error,inKezdoTanev,inVegzoTanev INT;                                                                                                                                                       
--    DECLARE cur1                                                                                                                                                                                           
--        CURSOR FOR
--	SELECT diakId, 
--		ifnull(diakNaploSorszam(diakId,inKezdoTanev,thisOsztalyId),99) as ns01, 
--		ifnull(diakNaploSorszam(diakId,inKezdoTanev+1,thisOsztalyId),99) as ns02, 
--		ifnull(diakNaploSorszam(diakId,inKezdoTanev+2,thisOsztalyId),99) as ns03, 
--		ifnull(diakNaploSorszam(diakId,inKezdoTanev+3,thisOsztalyId),99) as ns04, 
--		ifnull(diakNaploSorszam(diakId,inKezdoTanev+4,thisOsztalyId),99) as ns05, 
--		ifnull(diakNaploSorszam(diakId,inKezdoTanev+5,thisOsztalyId),99) as ns06, 
--		ifnull(diakNaploSorszam(diakId,inKezdoTanev+6,thisOsztalyId),99) as ns07, 
--		ifnull(diakNaploSorszam(diakId,inKezdoTanev+7,thisOsztalyId),99) as ns08, 
--		ifnull(diakNaploSorszam(diakId,inKezdoTanev+8,thisOsztalyId),99) as ns09, 
--		ifnull(diakNaploSorszam(diakId,inKezdoTanev+9,thisOsztalyId),99) as ns10, 
--		ifnull(diakNaploSorszam(diakId,inKezdoTanev+10,thisOsztalyId),99) as ns11, 
--		ifnull(diakNaploSorszam(diakId,inKezdoTanev+11,thisOsztalyId),99) as ns12, 
--		ifnull(diakNaploSorszam(diakId,inKezdoTanev+12,thisOsztalyId),99) as ns13
--	FROM osztalyDiak 
--	WHERE osztalyId=thisOsztalyId
--	ORDER BY ns01, ns02, ns03, ns04, ns05, ns06, ns07, ns08, ns09, ns10, ns11, ns12, ns13;
--    DECLARE CONTINUE HANDLER FOR NOT FOUND SET error := 1; -- Ne csináljon semmit, menjen tovább...
--    SELECT kezdoTanev FROM osztaly WHERE osztalyId=thisOsztalyId INTO inKezdoTanev;
--    SET i := 1;
--    OPEN cur1;
--    lo: LOOP
--        FETCH cur1 INTO d, n01, n02, n03, n04, n05, n06, n07, n08, n09, n10, n11, n12, n13;
--        IF d = thisDiakId THEN
--            LEAVE lo;
--        END IF;
--        SET i := i+1;
--    END LOOP;
--    CLOSE cur1;
--
--    return i;
-- END; //
-- DELIMITER ; //                                                                                                                                                                                            

-- -- Egy újabb próbálkozás...
-- DELIMITER //                                    
-- DROP FUNCTION IF EXISTS diakTorzslapszam //
-- CREATE function diakTorzslapszam ( thisDiakId INT, thisOsztalyId INT ) returns INT                                                                                                         
-- READS SQL DATA                                                                                                                                                                                            
-- BEGIN                                                                                                                                                                                                     
-- 
--     DECLARE ret INT;
--     set @oszt=0; 
--     set @sz=0; 
-- --    set @ret = (
-- select sorsz from (
-- select 
--     @sz:=if(@osz=osztalyId,@sz:=@sz+1,1) as sorsz,
--     @oszt:=osztalyId as o, 
--     osztalyId, diakId, sort, diakNev 
--     from (
--         select 
--             osztalyId, diakId,
--             if (month(min(beDt))>8 or month(min(beDt))<6 or (month(min(beDt))=6 and day(min(beDt))<16), min(beDt), date_format(min(beDt),'%Y-09-01')) as sort, 
--             concat_ws(' ',viseltNevElotag, viseltCsaladinev, viseltUtonev) as diakNev 
--         from osztalyDiak left join diak using (diakId) 
--         group by osztalyId, diakId 
--         order by osztalyId, sort, diakNev
-- ) as t
-- ) as k
-- where osztalyId=thisOsztalyId and diakId=thisDiakId into ret;
-- 
--     return ret;
-- END; //
-- DELIMITER ; //                                                                                                                                                                                            

