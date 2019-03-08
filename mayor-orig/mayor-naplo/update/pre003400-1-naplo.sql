DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_3400 $$
CREATE PROCEDURE upgrade_database_3400()
BEGIN
IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='osztalyNaplo' AND COLUMN_NAME='evfolyam'
) THEN
    alter table osztalyNaplo add column evfolyam tinyint unsigned;
END IF;
IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='osztalyNaplo' AND COLUMN_NAME='evfolyamJel'
) THEN
    alter table osztalyNaplo add column evfolyamJel varchar(32) collate utf8_hungarian_ci;
END IF;
END $$
CALL upgrade_database_3400();
update osztalyNaplo left join %INTEZMENYDB%.osztaly using (osztalyId) left join mayor_naplo.osztalyJelleg using (osztalyJellegId)
set evfolyam=if (
              (osztaly.vegzoTanev>=convert(SUBSTRING_INDEX(DATABASE(),'_',-1),unsigned) AND osztaly.kezdoTanev<=convert(SUBSTRING_INDEX(DATABASE(),'_',-1),unsigned)),
              if(
                  ((elokeszitoEvfolyam IS NOT NULL AND elokeszitoEvfolyam<>'') OR osztaly.osztalyJellegId=92) AND osztaly.kezdoTanev <> convert(SUBSTRING_INDEX(DATABASE(),'_',-1),unsigned),
                  (convert(SUBSTRING_INDEX(DATABASE(),'_',-1),unsigned)+osztaly.kezdoEvfolyam-osztaly.kezdoTanev-1),
                  (convert(SUBSTRING_INDEX(DATABASE(),'_',-1),unsigned)+osztaly.kezdoEvfolyam-osztaly.kezdoTanev)
              ),
              NULL
          );
update osztalyNaplo left join %INTEZMENYDB%.osztaly using (osztalyId) left join mayor_naplo.osztalyJelleg using (osztalyJellegId)
set evfolyamJel=if (
                 (osztaly.vegzoTanev>=convert(SUBSTRING_INDEX(DATABASE(),'_',-1),unsigned) AND osztaly.kezdoTanev<=convert(SUBSTRING_INDEX(DATABASE(),'_',-1),unsigned)),
                 if(
                     (elokeszitoEvfolyam IS NOT NULL AND elokeszitoEvfolyam<>''),
                     if (
                         osztaly.kezdoTanev = convert(SUBSTRING_INDEX(DATABASE(),'_',-1),unsigned),
                         CONCAT((convert(SUBSTRING_INDEX(DATABASE(),'_',-1),unsigned)+osztaly.kezdoEvfolyam-osztaly.kezdoTanev),elokeszitoEvfolyam),
                         (convert(SUBSTRING_INDEX(DATABASE(),'_',-1),unsigned)+osztaly.kezdoEvfolyam-osztaly.kezdoTanev-1)
                     ),
                     (convert(SUBSTRING_INDEX(DATABASE(),'_',-1),unsigned)+osztaly.kezdoEvfolyam-osztaly.kezdoTanev)
                 ),
                 NULL
             );
update osztalyNaplo left join %INTEZMENYDB%.osztaly using (osztalyId) left join mayor_naplo.osztalyJelleg using (osztalyJellegId)
set osztalyJel=if (
                 (osztaly.vegzoTanev>=convert(SUBSTRING_INDEX(DATABASE(),'_',-1),unsigned) AND osztaly.kezdoTanev<=convert(SUBSTRING_INDEX(DATABASE(),'_',-1),unsigned)),
                 if(
                     (elokeszitoEvfolyam IS NOT NULL AND elokeszitoEvfolyam<>''),
                     if (
                         osztaly.kezdoTanev = convert(SUBSTRING_INDEX(DATABASE(),'_',-1),unsigned),
                         CONCAT((convert(SUBSTRING_INDEX(DATABASE(),'_',-1),unsigned)+osztaly.kezdoEvfolyam-osztaly.kezdoTanev),elokeszitoEvfolyam,'.',osztaly.jel),
                         CONCAT((convert(SUBSTRING_INDEX(DATABASE(),'_',-1),unsigned)+osztaly.kezdoEvfolyam-osztaly.kezdoTanev-1),'.',osztaly.jel)
                     ),
                     CONCAT((convert(SUBSTRING_INDEX(DATABASE(),'_',-1),unsigned)+osztaly.kezdoEvfolyam-osztaly.kezdoTanev),'.',osztaly.jel)
                 ),
                 CONCAT(osztaly.kezdoTanev,'/',osztaly.vegzoTanev,'.',osztaly.jel)
             );
 