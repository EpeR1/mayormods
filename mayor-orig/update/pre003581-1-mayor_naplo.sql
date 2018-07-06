DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_3580 $$
CREATE PROCEDURE upgrade_database_3580()
BEGIN
IF EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='mayor_naplo' and TABLE_NAME='osztalyJelleg' AND COLUMN_NAME='kezdoEvfolyam'
) THEN
    alter table osztalyJelleg change `kezdoEvfolyam` `_kezdoEvfolyam` tinyint(3) unsigned DEFAULT NULL;
END IF;
IF EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='mayor_naplo' and TABLE_NAME='osztalyJelleg' AND COLUMN_NAME='vegzoEvfolyam'
) THEN
    alter table osztalyJelleg change `vegzoEvfolyam` `_vegzoEvfolyam` tinyint(3) unsigned DEFAULT NULL;
END IF;
-- IF EXISTS (
--     SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='mayor_naplo' and TABLE_NAME='osztalyJelleg' AND COLUMN_NAME='elokeszitoEvfolyam'
-- ) THEN
--     alter table osztalyJelleg drop column elokeszitoEvfolyam;
-- END IF;
-- IF EXISTS (
--     SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='mayor_naplo' and TABLE_NAME='osztalyJelleg' AND COLUMN_NAME='kovOsztalyJellegId'
-- ) THEN
--     alter table osztalyJelleg drop column kovOsztalyJellegId;
-- END IF;
IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='mayor_naplo' and TABLE_NAME='osztalyJelleg' AND COLUMN_NAME='evfolyamJelek'
) THEN
    alter table osztalyJelleg add column evfolyamJelek varchar(255) character set utf8 collate utf8_hungarian_ci default '';
ELSE
    alter table osztalyJelleg modify evfolyamJelek varchar(255) character set utf8 collate utf8_hungarian_ci default '';
END IF;
END $$
DELIMITER ;
CALL upgrade_database_3580();

SET NAMES utf8 COLLATE utf8_hungarian_ci;

-- Arany János Tehetséggondozó Program (AJTP) - 9
-- Arany János Kollégiumi Program (AJKP) - 9
-- két tanítási nyelvű előkészítő (Kny) - 9
-- nemzetiségi előkészítő (N) - 9
-- nyelvi előkészítő (Ny) - 9
-- Híd I. program (H/I) - 9 (H/I)
-- Híd II. program (H/II) - 8 (H/II/1, H/II/2)

-- kizárólag szakmai oktatást folytató képzések - Ksz/11, Ksz/12 - 11
-- szakközépiskolák szakiskolai végzettséggel rendelkező tanulóinak érettségire történő felkészítése - Szé/12/1, Szé/12/2 - 13, 14

-- szakképzés:
-- 5/13 - szakközépiskola 4 évére épülő 1 évfolyamos szakképzés - 13
-- 1/13, 2/14 - középiskolai képzésre nem épülő két évfolyamos képzés - 13, 14
-- 1/8, 2/9... - iskolai előképzettséghez nem kötött kifutó képzés - 8, 9, ...
-- 1/9, 2/10... - alapfokú iskolai végzettséghez kötött kifutó képzés - 9, 10, ...
-- 1/11, 2/12... - 10. évfolyam elvégzéséhez kötött kifutó képzés - 11, 12, ...
-- 1/13, 2/14... - középiskolai végzettséghez kötött kifutó képzés - 13, 14, ...

-- 1. az évfolyamot meghatározó függvényt bonyolítani kellene
-- 2. ennek felhasználásával kellene az osztály jelet generáló függvényt is módosítani
-- 3. ezt kellene használni a tanév megnyitáskor - rögzíteni az évfolyamot is
-- 4. megnézni a share/osztaly-ban, hogy hol van még évfolyam lekérdezés, azt javítani
-- a korábbi osztályok kezelése valóban problémás - esetleg egy speciális típust lehet nekik adni, ami folyamatos évfolyamszámozást jelent - kezelhetőnek tűnik
-- 5. az eddigi "evfolyam" (szám) mellett jelenjen meg az "evfolyamJel" (szöveg)
-- 6. a képzések létrehozásakor meg kell adni, hogy milyen osztályJelleg-re alkalmazandó. Innen tudhatók az évfolyamai!
-- 7. az érintett táblákat és funkciókat át kell alakítani
-- evfolyam mezők:
-- osztaly.kezdoEvfolyam - ok


-- zaroJegy.evfolyam --> evfolyamJel
-- vizsga.evfolyam --> evfolyamJel (zaroJegy)
-- kepzes.kezdoEvfolyam - ok (kell ez? - kell.) (~osztaly)
-- kepzes.zaroEvfolyam - ok (kell ez? - kell.) (~osztaly)
-- szempontRendszer.evfolyam --> evfolyamJel (kepzes)
-- kepzesOraszam.evfolyam --> evfolyamJel (osztalyJelleg)
-- kepzesOraterv.evfolyam --> evfolyamJel (osztalyJelleg)
-- kepzesTargyOraszam.evfolyam --> használjuk ezt egyáltalán???g	
-- tanmenet.evfolyam --> evfolyamJel (kepzes)

-- kovOsztalyJellegId -- csak az intezmeny/osztaly (NyEK osztály-léptetés) és az osztalyozo/zaroJegyCheck oldalakat érinti
-- elokeszitoEvfolyam -- talán ez is felesleges...

-- már elavult osztály-jellegek
update osztalyJelleg set evfolyamJelek='1,2,3,4,5,6,7,8' where osztalyJellegId=1;
update osztalyJelleg set evfolyamJelek='9,10,11,12' where osztalyJellegId=2;
update osztalyJelleg set evfolyamJelek='9,10,11,12' where osztalyJellegId=3;
update osztalyJelleg set evfolyamJelek='7,8,9,10,11,12' where osztalyJellegId=4;
update osztalyJelleg set evfolyamJelek='5,6,7,8,9,10,11,12' where osztalyJellegId=5;
-- -- -- szakiskola??
update osztalyJelleg set evfolyamJelek='' where osztalyJellegId=6;
-- -- -- speciális szakiskola??
update osztalyJelleg set evfolyamJelek='' where osztalyJellegId=7;
update osztalyJelleg set evfolyamJelek='9,10,11,12' where osztalyJellegId=8;
update osztalyJelleg set evfolyamJelek='9,10,11,12' where osztalyJellegId=9;
update osztalyJelleg set evfolyamJelek='9Ny' where osztalyJellegId=10;

update osztalyJelleg set evfolyamJelek='7,8,9,10,11,12' where osztalyJellegId=11;
update osztalyJelleg set evfolyamJelek='7Ny' where osztalyJellegId=12;
update osztalyJelleg set evfolyamJelek='5,6,7,8,9,10,11,12' where osztalyJellegId=13;
update osztalyJelleg set evfolyamJelek='5Ny' where osztalyJellegId=14;

-- általános iskola
update osztalyJelleg set evfolyamJelek='1,2,3,4,5,6,7,8' where osztalyJellegId=21;
update osztalyJelleg set evfolyamJelek='1,2,3,4' where osztalyJellegId=22;
update osztalyJelleg set evfolyamJelek='5,6,7,8' where osztalyJellegId=23;
-- 4 évfolyamos gimnázium
update osztalyJelleg set evfolyamJelek='9,10,11,12' where osztalyJellegId=31;
update osztalyJelleg set evfolyamJelek='9/AJTP,9,10,11,12' where osztalyJellegId=32;
update osztalyJelleg set evfolyamJelek='9/AJKP,9,10,11,12' where osztalyJellegId=33;
update osztalyJelleg set evfolyamJelek='9/Kny,9,10,11,12' where osztalyJellegId=34;
update osztalyJelleg set evfolyamJelek='9/N,9,10,11,12' where osztalyJellegId=35;
update osztalyJelleg set evfolyamJelek='9/Ny,9,10,11,12' where osztalyJellegId=36;
-- 5 évfolyamos gimnázium (NKT. szerint ilyen nincs.)
update osztalyJelleg set evfolyamJelek='9,10,11,12,13', osztalyJellegEles=0 where osztalyJellegId=41;
update osztalyJelleg set evfolyamJelek='9/AJTP,9,10,11,12,13', osztalyJellegEles=0 where osztalyJellegId=42;
update osztalyJelleg set evfolyamJelek='9/AJKP,9,10,11,12,13', osztalyJellegEles=0 where osztalyJellegId=43;
update osztalyJelleg set evfolyamJelek='9/Kny,9,10,11,12,13', osztalyJellegEles=0 where osztalyJellegId=44;
update osztalyJelleg set evfolyamJelek='9/N,9,10,11,12,13', osztalyJellegEles=0 where osztalyJellegId=45;
update osztalyJelleg set evfolyamJelek='9/N,9,10,11,12,13', osztalyJellegEles=0 where osztalyJellegId=46;
-- 6 évfolyamos gimnázium
update osztalyJelleg set evfolyamJelek='7,8,9,10,11,12' where osztalyJellegId=51;
update osztalyJelleg set evfolyamJelek='7/N,7,8,9,10,11,12' where osztalyJellegId=52;
update osztalyJelleg set evfolyamJelek='7/Ny,7,8,9,10,11,12' where osztalyJellegId=53;
-- 8 évfolyamos gimnázium
update osztalyJelleg set evfolyamJelek='5,6,7,8,9,10,11,12' where osztalyJellegId=61;
update osztalyJelleg set evfolyamJelek='5/N,5,6,7,8,9,10,11,12' where osztalyJellegId=62;
update osztalyJelleg set evfolyamJelek='5/Ny,5,6,7,8,9,10,11,12' where osztalyJellegId=63;
-- szakközépiskola - közismereti képzés (NKT. 12. § (1) - 4 évfolyam)
update osztalyJelleg set evfolyamJelek='9,10,11,12',osztalyJellegNev='szakközépiskola (1-4)' where osztalyJellegId=71;
update osztalyJelleg set evfolyamJelek='9/AJTP,9,10,11,12',osztalyJellegNev=concat_ws(' ',osztalyJellegNev,'(1+4)') where osztalyJellegId=72;
update osztalyJelleg set evfolyamJelek='9/AJKP,9,10,11,12',osztalyJellegNev=concat_ws(' ',osztalyJellegNev,'(1+4)') where osztalyJellegId=73;
update osztalyJelleg set evfolyamJelek='9/Kny,9,10,11,12',osztalyJellegNev=concat_ws(' ',osztalyJellegNev,'(1+4)') where osztalyJellegId=74;
update osztalyJelleg set evfolyamJelek='9/N,9,10,11,12',osztalyJellegNev=concat_ws(' ',osztalyJellegNev,'(1+4)') where osztalyJellegId=75;
update osztalyJelleg set evfolyamJelek='9/Ny,9,10,11,12',osztalyJellegNev=concat_ws(' ',osztalyJellegNev,'(1+4)') where osztalyJellegId=76;


-- szakközépiskola - szakképzés
update osztalyJelleg set evfolyamJelek='13,14',osztalyJellegEles=0 where osztalyJellegId=77;
insert ignore into osztalyJelleg (osztalyJellegId,kirOsztalyJellegId,osztalyJellegNev,erettsegizo,evfolyamJelek) 
    values (78,NULL,'szakközépiskolák szakiskolai végzettséggel rendelkező tanulóinak érettségire történő felkészítése (Szé/12/1-2)',1,'Szé/12/1,Szé/12/2')
    on duplicate key update kirOsztalyJellegId=values(kirOsztalyJellegId), osztalyJellegNev=values(osztalyJellegNev), erettsegizo=values(erettsegizo), evfolyamJelek=values(evfolyamJelek);
insert ignore into osztalyJelleg (osztalyJellegId,kirOsztalyJellegId,osztalyJellegNev,erettsegizo,evfolyamJelek) 
    values (79,NULL,'szakképzés - a szakközépiskola négy évére épülő 1 évfolyamos képzés (5/13)',0,'5/13')
    on duplicate key update kirOsztalyJellegId=values(kirOsztalyJellegId), osztalyJellegNev=values(osztalyJellegNev), erettsegizo=values(erettsegizo), evfolyamJelek=values(evfolyamJelek);

-- szakiskola - szakképzés (NKT. 13. § (1) - 3 évfolyam)
-- insert ignore into osztalyJelleg (osztalyJellegId,kirOsztalyJellegId,osztalyJellegNev,erettsegizo,evfolyamJelek) 
--    values (81,NULL,'szakiskola',0,'HIÁNYZIK');
update osztalyJelleg set evfolyamJelek='9,10,11',osztalyJellegEles=0 where osztalyJellegId=81;
insert ignore into osztalyJelleg (osztalyJellegId,kirOsztalyJellegId,osztalyJellegNev,erettsegizo,evfolyamJelek) 
    values (82,NULL,'szakképzés - iskolai előképzettséghez nem kötött 3 évfolyamos képzés (1/8-3/10)',0,'1/8,2/9,3/10')
    on duplicate key update kirOsztalyJellegId=values(kirOsztalyJellegId), osztalyJellegNev=values(osztalyJellegNev), erettsegizo=values(erettsegizo), evfolyamJelek=values(evfolyamJelek);
insert ignore into osztalyJelleg (osztalyJellegId,kirOsztalyJellegId,osztalyJellegNev,erettsegizo,evfolyamJelek) 
    values (83,NULL,'szakképzés - alapfokú iskolai végzettséghez kötött 3 évfolyamos képzés (1/9-3/11)',0,'1/9,2/10,3/11')
    on duplicate key update kirOsztalyJellegId=values(kirOsztalyJellegId), osztalyJellegNev=values(osztalyJellegNev), erettsegizo=values(erettsegizo), evfolyamJelek=values(evfolyamJelek);
insert ignore into osztalyJelleg (osztalyJellegId,kirOsztalyJellegId,osztalyJellegNev,erettsegizo,evfolyamJelek) 
    values (84,NULL,'szakképzés - a tizedik évfolyam elvégzéséhez kötött 2 évfolyamos képzés (1/11-2/12)',0,'1/11,2/12')
    on duplicate key update kirOsztalyJellegId=values(kirOsztalyJellegId), osztalyJellegNev=values(osztalyJellegNev), erettsegizo=values(erettsegizo), evfolyamJelek=values(evfolyamJelek);
insert ignore into osztalyJelleg (osztalyJellegId,kirOsztalyJellegId,osztalyJellegNev,erettsegizo,evfolyamJelek) 
    values (85,NULL,'szakképzés - a tizedik évfolyam elvégzéséhez kötött 3 évfolyamos képzés (1/11-3/13)',0,'1/11,2/12,3/13')
    on duplicate key update kirOsztalyJellegId=values(kirOsztalyJellegId), osztalyJellegNev=values(osztalyJellegNev), erettsegizo=values(erettsegizo), evfolyamJelek=values(evfolyamJelek);
insert ignore into osztalyJelleg (osztalyJellegId,kirOsztalyJellegId,osztalyJellegNev,erettsegizo,evfolyamJelek) 
    values (86,NULL,'szakképzés - a középiskola utolsó évfolyamának elvégzéséhez vagy középiskolai végzettséghez kötött 2 évfolyamos képzés (1/13-2/14)',0,'1/13,2/14')
    on duplicate key update kirOsztalyJellegId=values(kirOsztalyJellegId), osztalyJellegNev=values(osztalyJellegNev), erettsegizo=values(erettsegizo), evfolyamJelek=values(evfolyamJelek);

-- egyéb
delete from osztalyJelleg where osztalyJellegId in (91,92);
insert ignore into osztalyJelleg (osztalyJellegId,kirOsztalyJellegId,osztalyJellegNev,erettsegizo,evfolyamJelek) 
    values (91,NULL,'Híd I. program - 1 évfolyamos, alapfokú végzettséghez kötött, középiskolára felkészítő képzés (H/I)',0,'H/I')
    on duplicate key update kirOsztalyJellegId=values(kirOsztalyJellegId), osztalyJellegNev=values(osztalyJellegNev), erettsegizo=values(erettsegizo), evfolyamJelek=values(evfolyamJelek);
insert ignore into osztalyJelleg (osztalyJellegId,kirOsztalyJellegId,osztalyJellegNev,erettsegizo,evfolyamJelek) 
    values (92,NULL,'Híd II. program - 1 évfolyamos (10 hónapos), alapfokú végzettséget nem adó, szakképzést előkészítő osztály (H/II)',0,'H/II/1')
    on duplicate key update kirOsztalyJellegId=values(kirOsztalyJellegId), osztalyJellegNev=values(osztalyJellegNev), erettsegizo=values(erettsegizo), evfolyamJelek=values(evfolyamJelek);
insert ignore into osztalyJelleg (osztalyJellegId,kirOsztalyJellegId,osztalyJellegNev,erettsegizo,evfolyamJelek) 
    values (93,NULL,'Híd II. program - 2 évfolyamos (20 hónapos), alapfokú végzettséget adó, szakképzést előkészítő osztály (H/II/1-2)',0,'H/II/1,H/II/2')
    on duplicate key update kirOsztalyJellegId=values(kirOsztalyJellegId), osztalyJellegNev=values(osztalyJellegNev), erettsegizo=values(erettsegizo), evfolyamJelek=values(evfolyamJelek);
