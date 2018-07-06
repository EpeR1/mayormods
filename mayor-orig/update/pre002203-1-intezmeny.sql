ALTER TABLE tankor CHANGE tankorTipus _tankorTipus enum('tanórai','tanórán kívüli','első nyelv','második nyelv','egyéni foglalkozás','délutáni') DEFAULT 'tanórai';
ALTER TABLE tankor CHANGE jelenlet _jelenlet enum('kötelező','nem kötelező');
ALTER TABLE tankorDiak CHANGE jelenlet _jelenlet enum('kötelező','nem kötelező') DEFAULT 'kötelező';
ALTER TABLE tankorDiak CHANGE kovetelmeny _kovetelmeny enum('aláírás','vizsga','jegy') DEFAULT 'jegy';
ALTER TABLE tankorDiak ADD oralatogatasAlol enum('felmentve','nincs felmentve') DEFAULT 'nincs felmentve';
UPDATE tankorDiak SET oralatogatasAlol='nincs felmentve' WHERE _jelenlet='kötelező';
UPDATE tankorDiak SET oralatogatasAlol='felmentve' WHERE _jelenlet='nem kötelező';
ALTER TABLE tankorDiak DROP oralatogatasAlol;
ALTER TABLE tankorDiak DROP erdemjegyet ;
CREATE TABLE `tankorDiakFelmentes` (
  `tankorId` int(10) unsigned NOT NULL,
  `diakId` int(10) unsigned NOT NULL,
  `beDt` date NOT NULL DEFAULT '0000-00-00',
  `kiDt` date DEFAULT NULL,
  `felmentesTipus` enum('óralátogatás alól','értékelés alól') COLLATE utf8_hungarian_ci DEFAULT NULL,
  PRIMARY KEY (`tankorId`,`diakId`,`beDt`),
  KEY `tankorDiakFM_FKIndex1` (`tankorId`),
  KEY `diakId` (`diakId`),
  CONSTRAINT `tankorDiakFM_ibfk_1` FOREIGN KEY (`tankorId`) REFERENCES `tankor` (`tankorId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tankorDiakFM_ibfk_2` FOREIGN KEY (`diakId`) REFERENCES `diak` (`diakId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci  ;
INSERT INTO tankorDiakFelmentes SELECT tankorId,diakId,beDt,kiDt,'óralátogatás alól' AS felmentesTipus FROM tankorDiak WHERE _jelenlet='nem kötelező';
