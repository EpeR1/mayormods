
CREATE TABLE `mayorUpdateLog` (
  `scriptFile` varchar(255) COLLATE utf8_hungarian_ci NOT NULL DEFAULT '',
  `dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`scriptFile`,`dt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `munkaterv` (
  `munkatervId` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `munkatervNev` varchar(255) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `tanitasiNap` smallint(6) DEFAULT NULL,
  `vegzosZarasDt` date DEFAULT NULL,
  `tanitasNelkuliMunkanap` smallint(6) DEFAULT NULL,
  PRIMARY KEY (`munkatervId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `munkatervOsztaly` (
  `munkatervId` tinyint(3) unsigned NOT NULL,
  `osztalyId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`munkatervId`,`osztalyId`),
  KEY `IBFK_osztalyId` (`osztalyId`),
  CONSTRAINT `munkatervOsztaly_ibfk_2` FOREIGN KEY (`munkatervId`) REFERENCES `munkaterv` (`munkatervId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `munkatervOsztaly_ibfk_1` FOREIGN KEY (`osztalyId`) REFERENCES `%DB%`.`osztaly` (`osztalyId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `nap` (
  `dt` date NOT NULL,
  `tipus` enum('tanítási nap','speciális tanítási nap','tanítás nélküli munkanap','tanítási szünet','szorgalmi időszakon kívüli munkanap') COLLATE utf8_hungarian_ci DEFAULT 'tanítási nap',
  `megjegyzes` varchar(64) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `orarendiHet` tinyint(3) unsigned DEFAULT NULL,
  `munkatervId` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `csengetesiRendTipus` enum('normál','rövidített','speciális','rendhagyó','délutáni','délutáni rövidített','délutáni speciális','délutáni rendhagyó','nincs') COLLATE utf8_hungarian_ci DEFAULT 'normál',
  PRIMARY KEY (`munkatervId`,`dt`),
  CONSTRAINT `nap_ibfk_1` FOREIGN KEY (`munkatervId`) REFERENCES `munkaterv` (`munkatervId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `logBejegyzes` (
  `logId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userAccount` varchar(32) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `dt` datetime DEFAULT NULL,
  `ip` varchar(15) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `tabla` varchar(16) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `action` varchar(32) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `szoveg` varchar(255) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `actionId` varchar(23) DEFAULT NULL,
  PRIMARY KEY (`logId`),
  KEY `IDX_a` (`actionId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `dolgozat` (
  `dolgozatId` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `bejelentesDt` date DEFAULT NULL,
  `tervezettDt` date DEFAULT NULL,
  `dolgozatNev` varchar(64) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `modositasDt` datetime NOT NULL,
  PRIMARY KEY (`dolgozatId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci ;

CREATE TABLE `csere` (
  `csereId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`csereId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `osztalyNaplo` (
  `osztalyId` int(10) unsigned NOT NULL,
  `osztalyJel` varchar(23) COLLATE utf8_hungarian_ci NOT NULL,
  `evfolyam` tinyint(3) unsigned DEFAULT NULL,
  `evfolyamJel` varchar(32) COLLATE utf8_hungarian_ci DEFAULT NULL,
  PRIMARY KEY (`osztalyId`),
  UNIQUE KEY `OsztalyNaplo_osztalyId` (`osztalyId`),
  CONSTRAINT `osztalyNaplo_ibfk_1` FOREIGN KEY (`osztalyId`) REFERENCES `%DB%`.`osztaly` (`osztalyId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `targySorszam` (
  `osztalyId` int(10) unsigned NOT NULL,
  `targyId` smallint(5) unsigned NOT NULL,
  `sorrendNev` enum('napló','anyakönyv','ellenőrző','bizonyítvány','egyedi') COLLATE utf8_hungarian_ci NOT NULL DEFAULT 'napló',
  `sorszam` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`osztalyId`,`targyId`,`sorrendNev`),
  KEY `targySorrend_FKIndex1` (`osztalyId`),
  CONSTRAINT `targySorszam_ibfk_1` FOREIGN KEY (`osztalyId`) REFERENCES `osztalyNaplo` (`osztalyId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `bejegyzes` (
  `bejegyzesId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tanarId` int(10) unsigned DEFAULT NULL,
  `diakId` int(10) unsigned NOT NULL,
  `bejegyzesTipusId` tinyint(3) unsigned NOT NULL,
  `hianyzasDb` smallint(6) unsigned DEFAULT NULL,
  `szoveg` text COLLATE utf8_hungarian_ci,
  `beirasDt` date NOT NULL,
  `referenciaDt` date DEFAULT NULL,
  PRIMARY KEY (`bejegyzesId`),
  KEY `diakId` (`diakId`),
  KEY `tanarId` (`tanarId`),
  CONSTRAINT `bejegyzes_ibfk_1` FOREIGN KEY (`diakId`) REFERENCES `%DB%`.`diak` (`diakId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `bejegyzes_ibfk_2` FOREIGN KEY (`tanarId`) REFERENCES `%DB%`.`tanar` (`tanarId`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `bejegyzes_ibfk_3` FOREIGN KEY (`bejegyzesTipusId`) REFERENCES `%DB%`.`bejegyzesTipus` (`bejegyzesTipusId`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci ;

CREATE TABLE `tankorDolgozat` (
  `tankorId` int(10) unsigned NOT NULL,
  `dolgozatId` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`tankorId`,`dolgozatId`),
  KEY `tankorDolgozat_FKIndex1` (`dolgozatId`),
  CONSTRAINT `tankorDolgozat_ibfk_1` FOREIGN KEY (`dolgozatId`) REFERENCES `dolgozat` (`dolgozatId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tankorDolgozat_ibfk_2` FOREIGN KEY (`tankorId`) REFERENCES `%DB%`.`tankor` (`tankorId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `tankorNaplo` (
  `tankorId` int(10) unsigned NOT NULL,
  `osztalyId` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`tankorId`,`osztalyId`),
  KEY `osztalyId` (`osztalyId`),
  CONSTRAINT `tankorNaplo_ibfk_1` FOREIGN KEY (`osztalyId`) REFERENCES `osztalyNaplo` (`osztalyId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tankorNaplo_ibfk_2` FOREIGN KEY (`tankorId`) REFERENCES `%DB%`.`tankor` (`tankorId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `ora` (
  `oraId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `dt` date NOT NULL,
  `ora` tinyint(3) unsigned NOT NULL,
  `ki` int(10) unsigned DEFAULT NULL,
  `kit` int(10) unsigned DEFAULT NULL,
  `tankorId` int(10) unsigned DEFAULT NULL,
  `teremId` smallint(5) unsigned DEFAULT NULL,
  `leiras` varchar(255) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `tipus` enum('normál','elmarad','helyettesítés','felügyelet','összevonás','normál máskor','elmarad máskor','egyéb') COLLATE utf8_hungarian_ci DEFAULT 'normál',
  `eredet` enum('órarend','plusz') COLLATE utf8_hungarian_ci DEFAULT 'órarend',
  `feladatTipusId` tinyint(3) unsigned DEFAULT NULL,
  `munkaido` enum('lekötött','fennmaradó','kötetlen') COLLATE utf8_hungarian_ci DEFAULT 'lekötött',
  `modositasDt` datetime DEFAULT NULL,
  PRIMARY KEY (`oraId`),
  KEY `ki` (`ki`),
  KEY `kit` (`kit`),
  KEY `tankorId` (`tankorId`),
  KEY `teremId` (`teremId`),
  KEY `dt` (`dt`),
  CONSTRAINT `ora_ibfk_1` FOREIGN KEY (`ki`) REFERENCES `%DB%`.`tanar` (`tanarId`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `ora_ibfk_2` FOREIGN KEY (`kit`) REFERENCES `%DB%`.`tanar` (`tanarId`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `ora_ibfk_3` FOREIGN KEY (`tankorId`) REFERENCES `%DB%`.`tankor` (`tankorId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `ora_ibfk_4` FOREIGN KEY (`teremId`) REFERENCES `%DB%`.`terem` (`teremId`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `ora_ibfk_5` FOREIGN KEY (`feladatTipusId`) REFERENCES `%DB%`.`feladatTipus` (`feladatTipusId`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci ;

CREATE TABLE `oraHazifeladat` (
  `hazifeladatId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `oraId` int(10) unsigned DEFAULT NULL,
  `hazifeladatLeiras` text COLLATE utf8_hungarian_ci NOT NULL,
  PRIMARY KEY (`hazifeladatId`),
  UNIQUE KEY `oraId` (`oraId`),
  CONSTRAINT `oraHazifeladat_ibfk_1` FOREIGN KEY (`oraId`) REFERENCES `ora` (`oraId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `oraHazifeladatDiak` (
  `hazifeladatId` int(10) unsigned NOT NULL DEFAULT '0',
  `diakId` int(10) unsigned NOT NULL DEFAULT '0',
  `diakLattamDt` datetime DEFAULT NULL,
  `tanarLattamDt` datetime DEFAULT NULL,
  `hazifeladatDiakStatus` enum('','kész') COLLATE utf8_hungarian_ci DEFAULT '',
  `hazifeladatDiakMegjegyzes` varchar(255) COLLATE utf8_hungarian_ci NOT NULL,
  PRIMARY KEY (`hazifeladatId`,`diakId`),
  UNIQUE KEY `oraHazifeladatDiak_UK` (`hazifeladatId`,`diakId`),
  KEY `oraHazifeladatDiak_ibfk_2` (`diakId`),
  CONSTRAINT `oraHazifeladatDiak_ibfk_1` FOREIGN KEY (`hazifeladatId`) REFERENCES `oraHazifeladat` (`hazifeladatId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `oraHazifeladatDiak_ibfk_2` FOREIGN KEY (`diakId`) REFERENCES `%DB%`.`diak` (`diakId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `orarendiOraTankor` (
  `tanarId` int(10) unsigned NOT NULL,
  `osztalyJel` varchar(7) COLLATE utf8_bin NOT NULL,
  `targyJel` varchar(32) COLLATE utf8_bin NOT NULL,
  `tankorId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`tanarId`,`osztalyJel`,`targyJel`),
  KEY `orarendiOraTankor_tankorId` (`tankorId`),
  CONSTRAINT `orarendiOraTankor_ibfk_1` FOREIGN KEY (`tankorId`) REFERENCES `%DB%`.`tankor` (`tankorId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ;

CREATE TABLE `hianyzas` (
  `hianyzasId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `diakId` int(10) unsigned NOT NULL,
  `oraId` int(10) unsigned NOT NULL,
  `dt` date DEFAULT NULL,
  `ora` tinyint(3) unsigned DEFAULT NULL,
  `perc` tinyint(3) unsigned DEFAULT NULL,
  `tipus` enum('hiányzás','késés','felszerelés hiány','felmentés','egyenruha hiány') COLLATE utf8_hungarian_ci DEFAULT NULL,
  `statusz` enum('igazolt','igazolatlan') COLLATE utf8_hungarian_ci DEFAULT NULL,
  `igazolas` enum('orvosi','szülői','osztályfőnöki','verseny','vizsga','igazgatói','hatósági','pályaválasztás','') COLLATE utf8_hungarian_ci DEFAULT NULL,
  `tankorTipus` enum('tanórai','tanórán kívüli','első nyelv','második nyelv','egyéni foglalkozás','délutáni') COLLATE utf8_hungarian_ci DEFAULT NULL,
  `tankorTipusId` int(10) unsigned DEFAULT NULL,
  `rogzitoTanarId` int(10) unsigned DEFAULT NULL,
  `rogzitesIdoben` tinyint(1) DEFAULT NULL,
  `modositasDt` datetime DEFAULT NULL,
  PRIMARY KEY (`hianyzasId`,`diakId`),
  UNIQUE KEY (`oraId`,`diakId`,`tipus`),
  KEY `hianyzas_FKIndex1` (`oraId`),
  KEY `diakId` (`diakId`),
  CONSTRAINT `hianyzas_ibfk_1` FOREIGN KEY (`oraId`) REFERENCES `ora` (`oraId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `hianyzas_ibfk_2` FOREIGN KEY (`diakId`) REFERENCES `%DB%`.`diak` (`diakId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;


CREATE TABLE `cserePluszOra` (
  `csereId` int(10) unsigned NOT NULL,
  `oraId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`csereId`,`oraId`),
  KEY `cserePluszOra_FKIndex1` (`csereId`),
  KEY `cserePluszOra_FKIndex2` (`oraId`),
  CONSTRAINT `cserePluszOra_ibfk_1` FOREIGN KEY (`csereId`) REFERENCES `csere` (`csereId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `cserePluszOra_ibfk_2` FOREIGN KEY (`oraId`) REFERENCES `ora` (`oraId`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;


CREATE TABLE `csereAlapOra` (
  `csereId` int(10) unsigned NOT NULL,
  `oraId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`csereId`,`oraId`),
  KEY `csereAlapOra_FKIndex1` (`csereId`),
  KEY `csereAlapOra_FKIndex2` (`oraId`),
  CONSTRAINT `csereAlapOra_ibfk_1` FOREIGN KEY (`csereId`) REFERENCES `csere` (`csereId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `csereAlapOra_ibfk_2` FOREIGN KEY (`oraId`) REFERENCES `ora` (`oraId`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `orarendiOra` (
  `het` tinyint(3) unsigned NOT NULL,
  `nap` tinyint(3) unsigned NOT NULL,
  `ora` tinyint(3) unsigned NOT NULL,
  `tanarId` int(10) unsigned NOT NULL,
  `osztalyJel` varchar(7) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `targyJel` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `teremId` smallint(5) unsigned DEFAULT NULL,
  `tolDt` date NOT NULL DEFAULT '0000-00-00',
  `igDt` date DEFAULT NULL,
  PRIMARY KEY (`het`,`nap`,`ora`,`tanarId`,`tolDt`),
  KEY `orarendiOra_FKIndex1` (`tanarId`,`osztalyJel`,`targyJel`),
  KEY `teremId` (`teremId`),
  CONSTRAINT `orarendiOra_ibfk_1` FOREIGN KEY (`tanarId`) REFERENCES `%DB%`.`tanar` (`tanarId`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `orarendiOra_ibfk_2` FOREIGN KEY (`teremId`) REFERENCES `%DB%`.`terem` (`teremId`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `jegy` (
  `jegyId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `diakId` int(10) unsigned NOT NULL,
  `jegy` decimal(4,1) NOT NULL,
  `jegyTipus` enum('jegy','magatartás','szorgalom','négyszintű (szöveges minősítés)','féljegy','százalékos','aláírás','háromszintű','egyedi felsorolás','szöveges szempontrendszer','teljesített óra') COLLATE utf8_hungarian_ci DEFAULT NULL,
  `tipus` tinyint(3) unsigned NOT NULL,
  `tankorId` int(10) unsigned NOT NULL,
  `dt` date DEFAULT NULL,
  `oraId` int(10) unsigned DEFAULT NULL,
  `dolgozatId` smallint(5) unsigned DEFAULT NULL,
  `megjegyzes` varchar(128) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `modositasDt` datetime NOT NULL,
  PRIMARY KEY (`jegyId`),
  KEY `tankorId` (`tankorId`),
  KEY `diakId` (`diakId`),
  KEY `dolgozatId` (`dolgozatId`),
  KEY `oraId` (`oraId`),
  CONSTRAINT `jegy_ibfk_1` FOREIGN KEY (`tankorId`) REFERENCES `%DB%`.`tankor` (`tankorId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `jegy_ibfk_2` FOREIGN KEY (`diakId`) REFERENCES `%DB%`.`diak` (`diakId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `jegy_ibfk_4` FOREIGN KEY (`oraId`) REFERENCES `ora` (`oraId`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `jegy_ibfk_5` FOREIGN KEY (`dolgozatId`) REFERENCES `dolgozat` (`dolgozatId`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci ;

CREATE TABLE `csoport` (
  `csoportId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `csoportNev` varchar(128) COLLATE utf8_hungarian_ci DEFAULT NULL,
  PRIMARY KEY (`csoportId`),
  UNIQUE KEY `IDX_U_csoport_csoportNev` (`csoportNev`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `tankorCsoport` (
  `tankorId` int(10) unsigned NOT NULL,
  `csoportId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`tankorId`,`csoportId`),
  KEY `csoportId` (`csoportId`),
  CONSTRAINT `tankorCsoport_ibfk_1` FOREIGN KEY (`tankorId`) REFERENCES `%DB%`.`tankor` (`tankorId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tankorCsoport_ibfk_2` FOREIGN KEY (`csoportId`) REFERENCES `csoport` (`csoportId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `fogadoOra` (
  `tanarId` int(10) unsigned NOT NULL,
  `tol` datetime NOT NULL,
  `ig` datetime NOT NULL,
  `teremId` smallint(5) unsigned DEFAULT NULL,
  PRIMARY KEY (`tanarId`,`tol`),
  KEY `teremId` (`teremId`),
  CONSTRAINT `fogadoOra_ibfk_1` FOREIGN KEY (`tanarId`) REFERENCES `%DB%`.`tanar` (`tanarId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fogadoOra_ibfk_2` FOREIGN KEY (`teremId`) REFERENCES `%DB%`.`terem` (`teremId`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci ;

CREATE TABLE `fogadoOraJelentkezes` (
  `szuloId` int(10) unsigned NOT NULL,
  `tanarId` int(10) unsigned NOT NULL,
  `tol` datetime NOT NULL,
  PRIMARY KEY (`tanarId`,`tol`),
  UNIQUE KEY `szuloId` (`szuloId`,`tol`),
  CONSTRAINT `fogadoOraJelentkezes_ibfk_1` FOREIGN KEY (`tanarId`) REFERENCES `%DB%`.`tanar` (`tanarId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fogadoOraJelentkezes_ibfk_2` FOREIGN KEY (`szuloId`) REFERENCES `%DB%`.`szulo` (`szuloId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `blokk` (
  `blokkId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `blokkNev` varchar(128) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `exportOraszam` decimal(2,1) unsigned DEFAULT NULL,
  PRIMARY KEY (`blokkId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;


CREATE TABLE `tankorBlokk` (
  `blokkId` int(10) unsigned NOT NULL,
  `tankorId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`blokkId`,`tankorId`),
  KEY `tankorBlokk_FKIndex1` (`tankorId`),
  CONSTRAINT `tankorBlokk_ibfk_1` FOREIGN KEY (`tankorId`) REFERENCES `%DB%`.`tankor` (`tankorId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tankorBlokk_ibfk_2` FOREIGN KEY (`blokkId`) REFERENCES `blokk` (`blokkId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `uzeno` (
  `mId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `dt` datetime NOT NULL,
  `txt` text COLLATE utf8_hungarian_ci NOT NULL,
  `olvasott` tinyint(1) NOT NULL DEFAULT '0',
  `feladoId` int(10) unsigned NOT NULL,
  `feladoTipus` enum('diak','szulo','tanar') COLLATE utf8_hungarian_ci DEFAULT NULL,
  `cimzettId` int(10) unsigned NOT NULL,
  `cimzettTipus` enum('diak','szulo','tanar','tankor','tankorSzulo','munkakozosseg','osztaly','osztalySzulo','osztalyTanar') COLLATE utf8_hungarian_ci DEFAULT NULL,
  PRIMARY KEY (`mId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci ;

CREATE TABLE `uzenoFlagek` (
  `mId` int(10) unsigned NOT NULL,
  `Id` int(10) unsigned NOT NULL,
  `Tipus` enum('diak','szulo','tanar') NOT NULL DEFAULT 'diak',
  `flag` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`mId`,`Id`,`Tipus`),
  CONSTRAINT `uzenoFlagek_ibfk_1` FOREIGN KEY (`mId`) REFERENCES `uzeno` (`mId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `kerdoiv` (
  `kerdoivId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cim` varchar(64) COLLATE utf8_hungarian_ci NOT NULL,
  `tolDt` datetime NOT NULL,
  `igDt` datetime NOT NULL,
  `megjegyzes` text COLLATE utf8_hungarian_ci,
  PRIMARY KEY (`kerdoivId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `kerdoivCimzett` (
  `kerdoivId` int(10) unsigned NOT NULL,
  `cimzettId` int(10) unsigned NOT NULL,
  `cimzettTipus` enum('diak','szulo','tanar','tankor','tankorSzulo','munkakozosseg','osztaly','osztalySzulo') COLLATE utf8_hungarian_ci NOT NULL DEFAULT 'diak',
  PRIMARY KEY (`kerdoivId`,`cimzettId`,`cimzettTipus`),
  KEY `kerdoivId` (`kerdoivId`),
  KEY `cimzettId` (`cimzettId`,`cimzettTipus`),
  CONSTRAINT `kerdoivCimzett_ibfk_1` FOREIGN KEY (`kerdoivId`) REFERENCES `kerdoiv` (`kerdoivId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `kerdoivKerdes` (
  `kerdesId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `kerdoivId` int(10) unsigned NOT NULL,
  `kerdes` varchar(255) COLLATE utf8_hungarian_ci NOT NULL,
  PRIMARY KEY (`kerdesId`),
  KEY `kerdoivId` (`kerdoivId`),
  CONSTRAINT `kerdoivKerdes_ibfk_1` FOREIGN KEY (`kerdoivId`) REFERENCES `kerdoiv` (`kerdoivId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci ;

CREATE TABLE `kerdoivValasz` (
  `valaszId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `kerdesId` int(10) unsigned NOT NULL,
  `valasz` varchar(255) COLLATE utf8_hungarian_ci NOT NULL,
  `pont` tinyint DEFAULT 0 NOT NULL,
  PRIMARY KEY (`valaszId`),
  KEY `kv_FKindex` (`kerdesId`),
  CONSTRAINT `kerdoivValasz_ibfk_1` FOREIGN KEY (`kerdesId`) REFERENCES `kerdoivKerdes` (`kerdesId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `kerdoivValaszSzam` (
  `valaszId` int(10) unsigned NOT NULL,
  `cimzettId` int(10) unsigned NOT NULL,
  `cimzettTipus` enum('diak','szulo','tanar','tankor','tankorSzulo','munkakozosseg','osztaly','osztalySzulo') COLLATE utf8_hungarian_ci NOT NULL DEFAULT 'diak',
  `szavazat` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`valaszId`,`cimzettId`,`cimzettTipus`),
  KEY `kv_FKindex` (`valaszId`),
  CONSTRAINT `kerdoivValaszSzam_ibfk_1` FOREIGN KEY (`valaszId`) REFERENCES `kerdoivValasz` (`valaszId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `kerdoivMegvalaszoltKerdes` (
  `feladoId` int(10) unsigned NOT NULL,
  `feladoTipus` enum('diak','szulo','tanar') COLLATE utf8_hungarian_ci NOT NULL DEFAULT 'diak',
  `kerdesId` int(10) unsigned NOT NULL,
  `cimzettId` int(10) unsigned NOT NULL,
  `cimzettTipus` enum('diak','szulo','tanar','tankor','tankorSzulo','munkakozosseg','osztaly','osztalySzulo') COLLATE utf8_hungarian_ci NOT NULL DEFAULT 'diak',
  PRIMARY KEY (`feladoTipus`,`feladoId`,`kerdesId`,`cimzettId`,`cimzettTipus`),
  KEY `kv_FKindex` (`kerdesId`),
  CONSTRAINT `kerdoivMegvalaszoltKerdes_ibfk_1` FOREIGN KEY (`kerdesId`) REFERENCES `kerdoivKerdes` (`kerdesId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci ;

CREATE TABLE `kerdoivSzabadValasz` (
  `szabadValaszId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `kerdesId` int(10) unsigned NOT NULL,
  `szoveg` varchar(255) COLLATE utf8_hungarian_ci DEFAULT NULL,
  PRIMARY KEY (`szabadValaszId`),
  FOREIGN KEY `fk1` (`kerdesId`) references `kerdoivKerdes`(`kerdesId`) on update cascade on delete cascade
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `szovegesErtekeles` (
  `szeId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `diakId` int(10) unsigned NOT NULL,
  `szrId` int(10) unsigned NOT NULL,
  `targyId` smallint(5) unsigned NOT NULL,
  `dt` date NOT NULL,
  PRIMARY KEY (`szeId`),
  UNIQUE KEY `sze_UKindex1` (`diakId`,`targyId`,`dt`),
  KEY `sze_FKindex1` (`diakId`),
  KEY `sze_FKindex2` (`szrId`),
  KEY `sze_FKindex3` (`targyId`),
  CONSTRAINT `szovegesErtekeles_ibfk_1` FOREIGN KEY (`diakId`) REFERENCES `%DB%`.`diak` (`diakId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `szovegesErtekeles_ibfk_2` FOREIGN KEY (`targyId`) REFERENCES `%DB%`.`targy` (`targyId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `szovegesErtekeles_ibfk_3` FOREIGN KEY (`szrId`) REFERENCES `%DB%`.`szempontRendszer` (`szrId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;


CREATE TABLE `szeEgyediMinosites` (
  `szeId` int(10) unsigned NOT NULL,
  `szempontId` int(10) unsigned NOT NULL,
  `egyediMinosites` varchar(255) COLLATE utf8_hungarian_ci DEFAULT NULL,
  PRIMARY KEY (`szeId`,`szempontId`),
  KEY `szeem_FKindex1` (`szempontId`),
  KEY `szeem_FKindex2` (`szeId`),
  CONSTRAINT `szeEgyediMinosites_ibfk_1` FOREIGN KEY (`szempontId`) REFERENCES `%DB%`.`szrSzempont` (`szempontId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `szeEgyediMinosites_ibfk_2` FOREIGN KEY (`szeId`) REFERENCES `szovegesErtekeles` (`szeId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `szeMinosites` (
  `szeId` int(10) unsigned NOT NULL,
  `minositesId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`szeId`,`minositesId`),
  KEY `szem_FKindex1` (`szeId`),
  KEY `minositesId` (`minositesId`),
  CONSTRAINT `szeMinosites_ibfk_1` FOREIGN KEY (`szeId`) REFERENCES `szovegesErtekeles` (`szeId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `szeMinosites_ibfk_2` FOREIGN KEY (`minositesId`) REFERENCES `%DB%`.`szrMinosites` (`minositesId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `hetes` (
  `osztalyId` int(10) unsigned NOT NULL,
  `dt` date NOT NULL DEFAULT '0000-00-00',
  `sorszam` smallint(5) unsigned NOT NULL DEFAULT '1',
  `diakId` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`osztalyId`,`dt`,`sorszam`),
  KEY `het_FKindex1` (`osztalyId`),
  KEY `het_FKindex2` (`diakId`),
  CONSTRAINT `hetes_ibfk_1` FOREIGN KEY (`osztalyId`) REFERENCES `%DB%`.`osztaly` (`osztalyId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `hetes_ibfk_2` FOREIGN KEY (`diakId`) REFERENCES `%DB%`.`diak` (`diakId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci ;

CREATE TABLE `oraLatogatas` (
  `oraLatogatasId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `megjegyzes` text COLLATE utf8_hungarian_ci NOT NULL,
  `oraId` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`oraLatogatasId`),
  UNIQUE KEY `oraId` (`oraId`),
  CONSTRAINT `oraLatogatas_ibfk_1` FOREIGN KEY (`oraId`) REFERENCES `ora` (`oraId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `oraLatogatasTanar` (
  `oraLatogatasId` int(10) unsigned NOT NULL,
  `tanarId` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`oraLatogatasId`,`tanarId`),
  KEY `tanarId` (`tanarId`),
  CONSTRAINT `oraLatogatasTanar_ibfk_1` FOREIGN KEY (`tanarId`) REFERENCES `%DB%`.`tanar` (`tanarId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `oraLatogatasTanar_ibfk_2` FOREIGN KEY (`oraLatogatasId`) REFERENCES `oraLatogatas` (`oraLatogatasId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci ;

CREATE TABLE `audit` (
  `auditId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `dt` datetime NOT NULL,
  `userAccount` varchar(32) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `psf` varchar(255) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `params` varchar(255) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `fejlec` varchar(255) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `szoveg` text COLLATE utf8_hungarian_ci,
  `felelosCsoport` varchar(64) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `felelos` varchar(64) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `lezarasDt` datetime DEFAULT NULL,
  PRIMARY KEY (`auditId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci ;

CREATE TABLE `hianyzasHozott` (                                                                                                                                                                            
  `diakId` int(10) unsigned NOT NULL,                                                                                                                                                                      
  `statusz` enum('igazolt','igazolatlan') COLLATE utf8_hungarian_ci DEFAULT NULL,                                                                                                                          
  `dbHianyzas` smallint(5) unsigned DEFAULT NULL,
  `dt` date DEFAULT NULL,                                                                                                                                                                                  
    CONSTRAINT `hianyzasHozott_IBFK1` FOREIGN KEY (`diakId`) REFERENCES `%DB%`.`diak` (`diakId`) ON DELETE CASCADE ON UPDATE CASCADE                                                                       
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;     

CREATE TABLE `hianyzasKreta` (
  `kretaHianyzasId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `diakId` int(10) unsigned DEFAULT NULL,
  `tankorId` int(10) unsigned DEFAULT NULL,
  `kretaDiakNev` varchar(255) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `oId` bigint(20) unsigned DEFAULT NULL,
  `dt` date DEFAULT NULL,
  `ora` tinyint(3) unsigned DEFAULT NULL,
  `kretaTankorNev` varchar(255) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `kretaTantargyNev` varchar(255) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `tipus` enum('hiányzás','késés','felszerelés hiány','felmentés','egyenruha hiány') COLLATE utf8_hungarian_ci DEFAULT NULL,
  `perc` tinyint(3) unsigned DEFAULT NULL,
  `kretaStatusz` enum('igen','nem') COLLATE utf8_hungarian_ci DEFAULT NULL,
  `statusz` enum('igazolt','igazolatlan') COLLATE utf8_hungarian_ci DEFAULT NULL,
  `kretaIgazolas` varchar(255) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `igazolas` enum('orvosi','szülői','osztályfőnöki','verseny','vizsga','igazgatói','hatósági','pályaválasztás','') COLLATE utf8_hungarian_ci DEFAULT NULL,
  PRIMARY KEY (`kretaHianyzasId`),
  KEY `hianyzasKreta_oId` (`oId`),
  KEY `hianyzasKreta_diakId` (`diakId`),
  KEY `hianyzasKreta_tankorId` (`tankorId`),
  CONSTRAINT `hk_ibfk_1` FOREIGN KEY (`oId`) REFERENCES `%DB%`.`diak` (`oId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `hk_ibfk_2` FOREIGN KEY (`diakId`) REFERENCES `%DB%`.`diak` (`diakId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `hk_ibfk_3` FOREIGN KEY (`tankorId`) REFERENCES `%DB%`.`tankor` (`tankorId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `koszi` (
  `kosziId` int(10) unsigned NOT NULL auto_increment,
  `kosziEsemenyId` int(10) unsigned NOT NULL,
  `dt` DATE NULL,
  `tanev` smallint(5) unsigned NULL,
  `felev` tinyint(3)  unsigned NULL,
  `igazolo` set('diák','tanár','osztályfőnök','dök') DEFAULT NULL,
  `tolDt` DATETIME DEFAULT NULL,
  `igDt`  DATETIME DEFAULT NULL,
  `targyId` smallint(5) unsigned DEFAULT NULL,
  PRIMARY KEY (`kosziId`),
  KEY `sze_FKindex1` (`targyId`),
  CONSTRAINT `koszi_ibfk_2` FOREIGN KEY (`targyId`) REFERENCES `%DB%`.`targy` (`targyId`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `koszi_ibfk_1` FOREIGN KEY (`kosziEsemenyId`) REFERENCES `%DB%`.`kosziEsemeny` (`kosziEsemenyId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;
CREATE TABLE `kosziIgazoloDiak` (
  `kosziId` int(10) unsigned NOT NULL,
  `diakId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`kosziId`,`diakId`),
  CONSTRAINT `kosziIgazoloDiak_ibfk_1` FOREIGN KEY (`kosziId`) REFERENCES `koszi` (`kosziId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `kosziIgazoloDiak_ibfk_2` FOREIGN KEY (`diakId`) REFERENCES `%DB%`.`diak` (`diakId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;
CREATE TABLE `kosziIgazoloTanar` (
  `kosziId` int(10) unsigned NOT NULL,
  `tanarId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`kosziId`,`tanarId`),
  CONSTRAINT `kosziIgazoloTanar_ibfk_1` FOREIGN KEY (`kosziId`) REFERENCES `koszi` (`kosziId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `kosziIgazoloTanar_ibfk_2` FOREIGN KEY (`tanarId`) REFERENCES `%DB%`.`tanar` (`tanarId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;
CREATE TABLE `kosziIgazoloOf` (
  `kosziId` int(10) unsigned NOT NULL,
  `tanarId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`kosziId`,`tanarId`),
  CONSTRAINT `kosziIgazoloOf_ibfk_1` FOREIGN KEY (`kosziId`) REFERENCES `koszi` (`kosziId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `kosziIgazoloT_ibfk_2` FOREIGN KEY (`tanarId`) REFERENCES `%DB%`.`tanar` (`tanarId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;
CREATE TABLE `kosziDiak` (
  `kosziId` int(10) unsigned NOT NULL,
  `diakId` int(10) unsigned NOT NULL,
  `rogzitesDt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `jovahagyasDt` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `kosziPontId` int(10) unsigned NOT NULL,
  `pont` int(10) unsigned NOT NULL,
  PRIMARY KEY (`kosziId`,`diakId`),
  KEY `kosziDiak_ibfk_2` (`diakId`),
  KEY `kosziDiak_ibfk_3` (`kosziPontId`),
  CONSTRAINT `kosziDiak_ibfk_1` FOREIGN KEY (`kosziId`) REFERENCES `koszi` (`kosziId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `kosziDiak_ibfk_2` FOREIGN KEY (`diakId`) REFERENCES `%DB%`.`diak` (`diakId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `kosziDiak_ibfk_3` FOREIGN KEY (`kosziPontId`) REFERENCES `%DB%`.`kosziPont` (`kosziPontId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `esemeny` (
  `esemenyId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `esemenyKategoria` enum('verseny','közösségi szolgálat','iskolai rendezvény'),
  `esemenyRovidnev` varchar(64)  COLLATE utf8_hungarian_ci DEFAULT NULL,
  `esemenyNev` varchar(255) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `esemenyLeiras` text COLLATE utf8_hungarian_ci DEFAULT NULL,
  `jelentkezesTolDt` datetime NOT NULL,
  `jelentkezesIgDt` datetime NOT NULL,
  `min` tinyint(3) unsigned NOT NULL,
  `max` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`esemenyId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci ;
CREATE TABLE `esemenyOsztaly` (
  `esemenyId` int(10) unsigned NOT NULL,
  `osztalyId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`esemenyId`,`osztalyId`),
  KEY `esemenyOsztaly_FKIndex1` (`esemenyId`),
  KEY `esemenyOsztaly_FKIndex2` (`osztalyId`),
  CONSTRAINT `esemenyOsztaly_ibfk_1` FOREIGN KEY (`esemenyId`) REFERENCES `esemeny` (`esemenyId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `esemenyOsztaly_ibfk_2` FOREIGN KEY (`osztalyId`) REFERENCES `%DB%`.`osztaly` (`osztalyId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;
CREATE TABLE `esemenyDiak` (
  `esemenyId` int(10) unsigned NOT NULL,
  `diakId` int(10) unsigned NOT NULL,
  `jelentkezesDt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `jovahagyasDt` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`esemenyId`,`diakId`),
  KEY `esemenyDiak_esemenyId` (`esemenyId`),
  KEY `esemenyDiak_diakId` (`diakId`),
  CONSTRAINT `esemenyDiak_ibfk_1` FOREIGN KEY (`esemenyId`) REFERENCES `esemeny` (`esemenyId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `esemenyDiak_ibfk_2` FOREIGN KEY (`diakId`) REFERENCES `%DB%`.`diak` (`diakId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci ;
CREATE TABLE `esemenyTanar` (
  `esemenyId` int(10) unsigned NOT NULL,
  `tanarId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`esemenyId`,`tanarId`),
  KEY `esemenyTanar_FKIndex1` (`esemenyId`),
  KEY `esemenyTanar_FKIndex2` (`tanarId`),
  CONSTRAINT `esemenyTanar_ibfk_1` FOREIGN KEY (`esemenyId`) REFERENCES `esemeny` (`esemenyId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `esemenyTanar_ibfk_2` FOREIGN KEY (`tanarId`) REFERENCES `%DB%`.`tanar` (`tanarId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `sniDiakAllapot` (
  `diakId` int(10) unsigned NOT NULL,
  `szemeszter` tinyint(3) unsigned NOT NULL,
  `olvasas` enum('betűző','szótagoló','folyamatos') DEFAULT NULL,
  `olvasasTempoja` enum('lassú','akadozó','megfelelő','gyors') DEFAULT NULL,
  `olvasasHibak` set('betűtévesztés','tipikus betűcsere','betűkihagyás','szótagkihagyás','szótagcsere') DEFAULT NULL,
  `iras` enum('csak másol','önállóan ír') DEFAULT NULL,
  `iraskepe` enum('kusza/olvashatatlan','rendezett') DEFAULT NULL,
  `irasHibak` set('betűtévesztés','tipikus betűcsere','betűkihagyás','szótagkihagyás','j-ly tévesztése','helyesírási hibák') DEFAULT NULL,
  `szovegertes` enum('gyenge/nem tudja értelmezni','kérdésekre válaszol','önállóan értelmez') DEFAULT NULL,
  `matematika` set('összeadást/kivonást elvégez','szorzási művelet technikáját ismeri','többtagú szorzást tud végezni',
'bennfoglalási művelet technikáját ismeri','többtagú bennfoglalást tud végezni','szöveges feladat matematikai műveleti leírására képes',
'mértani formákat/testeket ismeri','területszámítást tud végezni','felszínszámítást tud végezni','térfogatszámítást tud végezni',
'alapvető formák szerkesztésére képes') DEFAULT NULL,
  `szemelyesKompetenciak` varchar(700) DEFAULT NULL,
  `tarsasKompetenciak` varchar(700) DEFAULT NULL,
  `kognitivKepessegek` varchar(700) DEFAULT NULL,
  `vizsgalatDt` date DEFAULT NULL,
  `vizsgalatTanarId` int(10) unsigned DEFAULT NULL,
  `eljarasEszkozok` varchar(100) DEFAULT NULL,
  `vizsgaltTerulet` varchar(100) DEFAULT NULL,
  `problemaMegfogalmazasa` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`diakId`,`szemeszter`),
  CONSTRAINT `sniDiakAllapot_diakId` FOREIGN KEY (`diakId`) REFERENCES `%DB%`.`diak` (`diakId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `sniDiakAllapot_tanarId` FOREIGN KEY (`vizsgalatTanarId`) REFERENCES `%DB%`.`tanar` (`tanarId`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_hungarian_ci;

CREATE TABLE `sniDiakAdat` (
  `diakId` int(10) unsigned NOT NULL,
  `mentorTanarId` int(10) unsigned NOT NULL,
  `kulsoInfo` text,
  PRIMARY KEY (`diakId`),
  CONSTRAINT `sniDiakAdat_diakId` FOREIGN KEY (`diakId`) REFERENCES `%DB%`.`diak` (`diakId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `sniDiakAdat_tanarId` FOREIGN KEY (`mentorTanarId`) REFERENCES `%DB%`.`tanar` (`tanarId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_hungarian_ci;

CREATE TABLE `sniHaviOsszegzes` (
  `haviOsszegzesId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `diakId` int(10) unsigned NOT NULL,
  `dt` date DEFAULT NULL,
  `gyengesegek` varchar(300) DEFAULT NULL,
  `erossegek` varchar(300) DEFAULT NULL,
  `celok` varchar(200) DEFAULT NULL,
  `fejlesztesiFeladatok` varchar(200) DEFAULT NULL,
  `eszkozokModszerek` varchar(200) DEFAULT NULL,
  `utemezes` varchar(200) DEFAULT NULL,
  `ertekeles` varchar(200) DEFAULT NULL,
  `eredmeny` varchar(100) DEFAULT NULL,
  `valtozas` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`haviOsszegzesId`),
  CONSTRAINT `sniHaviOsszegzes_diakId` FOREIGN KEY (`diakId`) REFERENCES `%DB%`.`diak` (`diakId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_hungarian_ci;

CREATE TABLE `sniHaviOsszegzesFelelos` (
  `haviOsszegzesId` int(10) unsigned NOT NULL,
  `tanarId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`haviOsszegzesId`,`tanarId`),
  CONSTRAINT `sniHaviOsszegzesFelelos_haviOsszegzesId` FOREIGN KEY (`haviOsszegzesId`) REFERENCES `sniHaviOsszegzes` (`haviOsszegzesId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `sniHaviOsszegzesFelelos_tanarId` FOREIGN KEY (`tanarId`) REFERENCES `%DB%`.`tanar` (`tanarId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `sniTantargyiFeljegyzes` (
  `diakId` int(10) unsigned NOT NULL,
  `tankorId` int(10) unsigned NOT NULL,
  `dt` date NOT NULL DEFAULT '0000-00-00',
  `megjegyzes` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`diakId`,`tankorId`,`dt`),
  CONSTRAINT `sniTantargyiFeljegyzes_diakId` FOREIGN KEY (`diakId`) REFERENCES `%DB%`.`diak` (`diakId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `sniTantargyiFeljegyzes_tankorId` FOREIGN KEY (`tankorId`) REFERENCES `%DB%`.`tankor` (`tankorId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_hungarian_ci;

CREATE TABLE `sniDiakGyengesegErosseg` (
  `diakId` int(10) unsigned NOT NULL,
  `szemeszter` tinyint(3) unsigned NOT NULL,
  `gyengesegErosseg` enum('gyengeség','erősség') NOT NULL,
  `leiras` varchar(150) DEFAULT NULL,
  `prioritas` tinyint(5) unsigned DEFAULT NULL,
  KEY `sniDiakGyE_diakId` (`diakId`),
  CONSTRAINT `sniDiakGyE_diakId` FOREIGN KEY (`diakId`) REFERENCES `%DB%`.`diak` (`diakId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `kepzesTargyBontas` (
  `bontasId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `osztalyId` int(10) unsigned NOT NULL,
  `kepzesOratervId` int(10) unsigned NOT NULL,
  `targyId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`bontasId`),
  CONSTRAINT `ktBontas_osztalyId` FOREIGN KEY (`osztalyId`) REFERENCES `%DB%`.`osztaly` (`osztalyId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `ktBontas_kepzesOratervId` FOREIGN KEY (`kepzesOratervId`) REFERENCES `%DB%`.`kepzesOraterv` (`kepzesOratervId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `bontasTankor` (
  `bontasId` int(10) unsigned NOT NULL,
  `tankorId` int(10) unsigned NOT NULL,
  `hetiOraszam` decimal(4,2) DEFAULT NULL,
  PRIMARY KEY (`bontasId`,`tankorId`),
  CONSTRAINT `bontasTankor_bontasId` FOREIGN KEY (`bontasId`) REFERENCES `kepzesTargyBontas` (`bontasId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `bontasTankor_tankorId` FOREIGN KEY (`tankorId`) REFERENCES `%DB%`.`tankor` (`tankorId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `jegyzet` (
  `jegyzetId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(10) unsigned NOT NULL,
  `userTipus` enum('diak','tanar','szulo') COLLATE utf8_hungarian_ci DEFAULT NULL,
  `dt` date NOT NULL,
  `jegyzetLeiras` text COLLATE utf8_hungarian_ci,
  `publikus` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`jegyzetId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `jegyzetMunkakozosseg` (
  `jegyzetId` int(10) unsigned NOT NULL,
  `mkId` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`jegyzetId`,`mkId`),
  KEY `jegyzetMunkakozosseg_jegyzetId` (`jegyzetId`),
  KEY `jegyzetMunkakozosseg_mkId` (`mkId`),
  CONSTRAINT `jegyzetMunkakozosseg_ibfk_1` FOREIGN KEY (`jegyzetId`) REFERENCES `jegyzet` (`jegyzetId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `jegyzetMunkakozosseg_ibfk_2` FOREIGN KEY (`mkId`) REFERENCES `%DB%`.`munkakozosseg` (`mkId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `jegyzetOsztaly` (
  `jegyzetId` int(10) unsigned NOT NULL,
  `osztalyId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`jegyzetId`,`osztalyId`),
  KEY `jegyzetOsztaly_jegyzetId` (`jegyzetId`),
  KEY `jegyzetOsztaly_osztalyId` (`osztalyId`),
  CONSTRAINT `jegyzetOsztaly_ibfk_1` FOREIGN KEY (`jegyzetId`) REFERENCES `jegyzet` (`jegyzetId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `jegyzetOsztaly_ibfk_2` FOREIGN KEY (`osztalyId`) REFERENCES `%DB%`.`osztaly` (`osztalyId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `jegyzetTankor` (
  `jegyzetId` int(10) unsigned NOT NULL,
  `tankorId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`jegyzetId`,`tankorId`),
  KEY `jegyzetTankor_jegyzetId` (`jegyzetId`),
  KEY `jegyzetTankor_tankorId` (`tankorId`),
  CONSTRAINT `jegyzetTankor_ibfk_1` FOREIGN KEY (`jegyzetId`) REFERENCES `jegyzet` (`jegyzetId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `jegyzetTankor_ibfk_2` FOREIGN KEY (`tankorId`) REFERENCES `%DB%`.`tankor` (`tankorId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

 CREATE TABLE `oraCimke` (
  `oraId` int(10) unsigned NOT NULL,
  `cimkeId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`oraId`,`cimkeId`),
  CONSTRAINT `oraCimke_ibfk_1` FOREIGN KEY (`oraId`) REFERENCES `ora` (`oraId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `oraCimke_ibfk_2` FOREIGN KEY (`cimkeId`) REFERENCES `%DB%`.`cimke` (`cimkeId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

DROP FUNCTION IF EXISTS getNev;
DROP FUNCTION IF EXISTS getOraIgTime;
DROP FUNCTION IF EXISTS getOraTolTime;

DELIMITER //
 CREATE FUNCTION getNev(id int(10) unsigned, tipus varchar(20))
 RETURNS VARCHAR(60) DETERMINISTIC
 BEGIN
    DECLARE nev varchar(60) character set utf8;
    DECLARE tnv int(10);
    SELECT SUBSTRING(database(),-4) INTO tnv;

    IF tipus = 'diak' THEN
        SELECT TRIM(CONCAT_WS(' ',viseltNevElotag,viseltCsaladiNev,viseltUtonev)) FROM %DB%.diak WHERE diakId=id LIMIT 1 INTO nev;
    ELSEIF tipus = 'tanar' THEN
        SELECT TRIM(CONCAT_WS(' ',viseltNevElotag,viseltCsaladiNev,viseltUtonev)) FROM %DB%.tanar WHERE tanarId=id INTO nev;
    ELSEIF tipus = 'szulo' THEN
        SELECT TRIM(CONCAT_WS(' ',nevElotag,csaladinev,utonev)) FROM %DB%.szulo WHERE szuloId=id INTO nev;
    ELSEIF tipus = 'tankor' THEN
        SELECT tankorNev FROM %DB%.tankorSzemeszter WHERE tankorId=id AND tanev=tnv LIMIT 1 INTO nev;
    ELSEIF tipus = 'munkakozosseg' THEN
        SELECT leiras FROM %DB%.munkakozosseg WHERE mkId=id INTO nev;
    END IF;

    RETURN (nev);
 END
 //

 CREATE FUNCTION getOraTolTime(id int(10) unsigned)
 RETURNS TIME DETERMINISTIC
 BEGIN
    DECLARE oraTolTime TIME;

    SELECT DISTINCT tolTime FROM 
    (SELECT ora.*,osztalyDiak.osztalyId, osztalyDiak.diakId, %DB%.csengetesiRend.csengetesiRendTipus,
    tolTime, igTime FROM ora
    LEFT JOIN %DB%.tankorDiak ON (ora.tankorId = tankorDiak.tankorId AND tankorDiak.beDt<=ora.dt AND (tankorDiak.kiDt IS NULL or tankorDiak.kiDt>=ora.dt))
    LEFT JOIN %DB%.osztalyDiak ON (tankorDiak.diakId = osztalyDiak.diakId AND tankorDiak.beDt<=ora.dt AND (osztalyDiak.kiDt IS NULL or osztalyDiak.kiDt>=ora.dt))
    LEFT JOIN %DB%.osztaly ON (osztalyDiak.osztalyId = osztaly.osztalyId)
    LEFT JOIN %DB%.telephely USING (telephelyId)
    LEFT JOIN %DB%.csengetesiRend ON (telephely.telephelyId = csengetesiRend.telephelyId AND ora.ora=csengetesiRend.ora)
    WHERE oraId = id) AS a
    LEFT JOIN munkatervOsztaly USING (osztalyId)
    LEFT JOIN nap ON (nap.dt=a.dt AND nap.munkatervId=munkatervOsztaly.munkatervId)
    WHERE nap.csengetesiRendTipus = a.csengetesiRendTipus
    LIMIT 1
    INTO oraTolTime;

    RETURN (oraTolTime);
 END
 //

 CREATE FUNCTION getOraIgTime(id int(10) unsigned)
 RETURNS TIME DETERMINISTIC
 BEGIN
    DECLARE oraIgTime TIME;

    SELECT DISTINCT igTime FROM 
    (SELECT ora.*,osztalyDiak.osztalyId, osztalyDiak.diakId, %DB%.csengetesiRend.csengetesiRendTipus,
    tolTime, igTime FROM ora
    LEFT JOIN %DB%.tankorDiak ON (ora.tankorId = tankorDiak.tankorId AND tankorDiak.beDt<=ora.dt AND (tankorDiak.kiDt IS NULL or tankorDiak.kiDt>=ora.dt))
    LEFT JOIN %DB%.osztalyDiak ON (tankorDiak.diakId = osztalyDiak.diakId AND tankorDiak.beDt<=ora.dt AND (osztalyDiak.kiDt IS NULL or osztalyDiak.kiDt>=ora.dt))
    LEFT JOIN %DB%.osztaly ON (osztalyDiak.osztalyId = osztaly.osztalyId)
    LEFT JOIN %DB%.telephely USING (telephelyId)
    LEFT JOIN %DB%.csengetesiRend ON (telephely.telephelyId = csengetesiRend.telephelyId AND ora.ora=csengetesiRend.ora)
    WHERE oraId = id) AS a
    LEFT JOIN munkatervOsztaly USING (osztalyId)
    LEFT JOIN nap ON (nap.dt=a.dt AND nap.munkatervId=munkatervOsztaly.munkatervId)
    WHERE nap.csengetesiRendTipus = a.csengetesiRendTipus
    LIMIT 1
    INTO oraIgTime;

    RETURN (oraIgTime);
 END
 //
DELIMITER ; //
