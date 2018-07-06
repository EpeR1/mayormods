DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_3409 $$
CREATE PROCEDURE upgrade_database_3409()
BEGIN
IF NOT EXISTS (
    SELECT * FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='sniDiakAllapot'
) THEN
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
  CONSTRAINT `sniDiakAllapot_diakId` FOREIGN KEY (`diakId`) REFERENCES `%INTEZMENYDB%`.`diak` (`diakId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `sniDiakAllapot_tanarId` FOREIGN KEY (`vizsgalatTanarId`) REFERENCES `%INTEZMENYDB%`.`tanar` (`tanarId`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_hungarian_ci;
END IF;
IF NOT EXISTS (
    SELECT * FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='sniDiakAdat'
) THEN
CREATE TABLE `sniDiakAdat` (
  `diakId` int(10) unsigned NOT NULL,
  `mentorTanarId` int(10) unsigned NOT NULL,
  `kulsoInfo` text,
  PRIMARY KEY (`diakId`),
  CONSTRAINT `sniDiakAdat_diakId` FOREIGN KEY (`diakId`) REFERENCES `%INTEZMENYDB%`.`diak` (`diakId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `sniDiakAdat_tanarId` FOREIGN KEY (`mentorTanarId`) REFERENCES `%INTEZMENYDB%`.`tanar` (`tanarId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_hungarian_ci;
END IF;
IF NOT EXISTS (
    SELECT * FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='sniHaviOsszegzes'
) THEN
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
  CONSTRAINT `sniHaviOsszegzes_diakId` FOREIGN KEY (`diakId`) REFERENCES `%INTEZMENYDB%`.`diak` (`diakId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_hungarian_ci;
END IF;
IF NOT EXISTS (
    SELECT * FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='sniHaviOsszegzesFelelos'
) THEN
CREATE TABLE `sniHaviOsszegzesFelelos` (
  `haviOsszegzesId` int(10) unsigned NOT NULL,
  `tanarId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`haviOsszegzesId`,`tanarId`),
  CONSTRAINT `sniHaviOsszegzesFelelos_haviOsszegzesId` FOREIGN KEY (`haviOsszegzesId`) REFERENCES `sniHaviOsszegzes` (`haviOsszegzesId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `sniHaviOsszegzesFelelos_tanarId` FOREIGN KEY (`tanarId`) REFERENCES `%INTEZMENYDB%`.`tanar` (`tanarId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
END IF;
IF NOT EXISTS (
    SELECT * FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='sniTantargyiFeljegyzes'
) THEN
CREATE TABLE `sniTantargyiFeljegyzes` (
  `diakId` int(10) unsigned NOT NULL,
  `tankorId` int(10) unsigned NOT NULL,
  `dt` date NOT NULL DEFAULT '0000-00-00',
  `megjegyzes` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`diakId`,`tankorId`,`dt`),
  CONSTRAINT `sniTantargyiFeljegyzes_diakId` FOREIGN KEY (`diakId`) REFERENCES `%INTEZMENYDB%`.`diak` (`diakId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `sniTantargyiFeljegyzes_tankorId` FOREIGN KEY (`tankorId`) REFERENCES `%INTEZMENYDB%`.`tankor` (`tankorId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_hungarian_ci;
END IF;
IF NOT EXISTS (
    SELECT * FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='sniDiakGyengesegErosseg'
) THEN
CREATE TABLE `sniDiakGyengesegErosseg` (
  `diakId` int(10) unsigned NOT NULL,
  `szemeszter` tinyint(3) unsigned NOT NULL,
  `gyengesegErosseg` enum('gyengeség','erősség') NOT NULL,
  `leiras` varchar(150) DEFAULT NULL,
  `prioritas` tinyint(5) unsigned DEFAULT NULL,
  KEY `sniDiakGyE_diakId` (`diakId`),
  CONSTRAINT `sniDiakGyE_diakId` FOREIGN KEY (`diakId`) REFERENCES `%INTEZMENYDB%`.`diak` (`diakId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
END IF;
END $$
CALL upgrade_database_3409();
