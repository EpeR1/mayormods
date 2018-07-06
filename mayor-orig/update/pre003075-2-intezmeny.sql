CREATE TABLE IF NOT EXISTS `feladatTipus` (
  `feladatTipusId` tinyint(3) unsigned NOT NULL auto_increment,
  `feladatTipusLeiras` varchar(255) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `beszamithatoMaxOra` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`feladatTipusId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

-- egy később scriptben írjuk csak be, 3082
-- INSERT INTO feladatTipus (feladatTipusLeiras,beszamithatoMaxOra) VALUES ('felkészülés foglalkozásra, tanórára',10);
-- INSERT INTO feladatTipus (feladatTipusLeiras,beszamithatoMaxOra) VALUES ('diákok teljesítményének értékelése',10);
-- INSERT INTO feladatTipus (feladatTipusLeiras,beszamithatoMaxOra) VALUES ('diákönkormányzat segítése',10);
-- INSERT INTO feladatTipus (feladatTipusLeiras,beszamithatoMaxOra) VALUES ('sportélet és szabadidő szervezése',10);
-- INSERT INTO feladatTipus (feladatTipusLeiras,beszamithatoMaxOra) VALUES ('felügyelet',10);
