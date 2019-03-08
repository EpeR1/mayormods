DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4022 $$
CREATE PROCEDURE upgrade_database_4022()
BEGIN

SET NAMES utf8 COLLATE utf8_hungarian_ci;

INSERT IGNORE INTO osztalyJelleg VALUES (89,NULL,'szakképzés (Ksz/11-12) - szakmai elméleti és gyakorlati oktatást folytató szakközépiskolai (tizedik évf. utáni) - két éves szakképzés',0,NULL,NULL,NULL,NULL,1,'Ksz/11,Ksz/12','bizonyítvány'),
(90,NULL,'szakképzés (Ksz/11) - szakmai elméleti és gyakorlati oktatást folytató szakközépiskolai (tizedik évf. utáni) - egy éves szakképzés',0,NULL,NULL,NULL,NULL,1,'Ksz/11','bizonyítvány');

UPDATE osztalyJelleg SET osztalyJellegNev='Köznevelési Híd (I.) program - 1 évfolyamos, alapfokú végzettséghez kötött, középiskolára felkészítő képzés (KH)', evfolyamJelek='KH' WHERE osztalyJellegId=91;
UPDATE osztalyJelleg SET osztalyJellegNev='Szakképzési Híd (II.) program - 1 évfolyamos (10 hónapos), alapfokú végzettséget nem adó, szakképzést előkészítő osztály (SZH/1)', evfolyamJelek='SZH/1' WHERE osztalyJellegId=92;
UPDATE osztalyJelleg SET osztalyJellegNev='Szakképzési Híd (II.) program - 2 évfolyamos (20 hónapos), alapfokú végzettséget adó, szakképzést előkészítő osztály (SZH/1-2)', evfolyamJelek='SZH/1,SZH/2' WHERE osztalyJellegId=93;

END $$
DELIMITER ;
CALL upgrade_database_4022();
