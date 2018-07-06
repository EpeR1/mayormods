DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_3379b $$
CREATE PROCEDURE upgrade_database_3379b()
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
CALL upgrade_database_3379b();
-- update osztalyNaplo left join %INTEZMENYDB%.osztaly using (osztalyId) left join mayor_naplo.osztalyJelleg using (osztalyJellegId)
-- set evfolyam=if (
--              (osztaly.vegzoTanev>=%TANEV% AND osztaly.kezdoTanev<=%TANEV%),
--              if(
--                  ((elokeszitoEvfolyam IS NOT NULL AND elokeszitoEvfolyam<>'') OR osztaly.osztalyJellegId=92) AND osztaly.kezdoTanev <> %TANEV%,
--                  (%TANEV%+osztaly.kezdoEvfolyam-osztaly.kezdoTanev-1),
--                  (%TANEV%+osztaly.kezdoEvfolyam-osztaly.kezdoTanev)
--              ),
--              NULL
--          );
-- update osztalyNaplo left join %INTEZMENYDB%.osztaly using (osztalyId) left join mayor_naplo.osztalyJelleg using (osztalyJellegId)
-- set evfolyamJel=if (
--                 (osztaly.vegzoTanev>=%TANEV% AND osztaly.kezdoTanev<=%TANEV%),
--                 if(
--                     (elokeszitoEvfolyam IS NOT NULL AND elokeszitoEvfolyam<>''),
--                     if (
--                         osztaly.kezdoTanev = %TANEV%,
--                         CONCAT((%TANEV%+osztaly.kezdoEvfolyam-osztaly.kezdoTanev),elokeszitoEvfolyam),
--                         (%TANEV%+osztaly.kezdoEvfolyam-osztaly.kezdoTanev-1)
--                     ),
--                     (%TANEV%+osztaly.kezdoEvfolyam-osztaly.kezdoTanev)
--                 ),
--                 NULL
--             );
-- update osztalyNaplo left join %INTEZMENYDB%.osztaly using (osztalyId) left join mayor_naplo.osztalyJelleg using (osztalyJellegId)
-- set osztalyJel=if (
--                 (osztaly.vegzoTanev>=%TANEV% AND osztaly.kezdoTanev<=%TANEV%),
--                 if(
--                     (elokeszitoEvfolyam IS NOT NULL AND elokeszitoEvfolyam<>''),
--                     if (
--                         osztaly.kezdoTanev = %TANEV%,
--                         CONCAT((%TANEV%+osztaly.kezdoEvfolyam-osztaly.kezdoTanev),elokeszitoEvfolyam,'.',osztaly.jel),
--                         CONCAT((%TANEV%+osztaly.kezdoEvfolyam-osztaly.kezdoTanev-1),'.',osztaly.jel)
--                     ),
--                     CONCAT((%TANEV%+osztaly.kezdoEvfolyam-osztaly.kezdoTanev),'.',osztaly.jel)
--                 ),
--                 CONCAT(osztaly.kezdoTanev,'/',osztaly.vegzoTanev,'.',osztaly.jel)
--             );
 