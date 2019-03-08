DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_3379 $$
CREATE PROCEDURE upgrade_database_3379()
BEGIN
IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='mayor_naplo' and TABLE_NAME='osztalyJelleg' AND COLUMN_NAME='kezdoEvfolyam'
) THEN
    alter table osztalyJelleg add column kezdoEvfolyam tinyint unsigned default NULL;
END IF;
IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='mayor_naplo' and TABLE_NAME='osztalyJelleg' AND COLUMN_NAME='vegzoEvfolyam'
) THEN
    alter table osztalyJelleg add column vegzoEvfolyam tinyint unsigned default NULL;
END IF;
IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='mayor_naplo' and TABLE_NAME='osztalyJelleg' AND COLUMN_NAME='elokeszitoEvfolyam'
) THEN
    alter table osztalyJelleg add column elokeszitoEvfolyam enum('','AJTP','AJKP','Kny','N','Ny') default NULL;
END IF;
END $$
CALL upgrade_database_3379();

-- Arany János Tehetséggondozó Program (AJTP) - 9
-- Arany János Kollégiumi Program (AJKP) - 9
-- két tanítási nyelvű előkészítő (Kny) - 9
-- nemzetiségi előkészítő (N) - 9
-- nyelvi előkészítő (Ny) - 9
-- Híd I. program (H/I) - 9
-- Híd II. program (H/II) - 8

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

-- általános iskola
insert ignore into osztalyJelleg (osztalyJellegId,kirOsztalyJellegId,osztalyJellegNev,erettsegizo,kovOsztalyJellegId,kezdoEvfolyam,vegzoEvfolyam,elokeszitoEvfolyam) 
    values (21,NULL,'általános iskola (1-8)',0,NULL,1,8,'');
insert ignore into osztalyJelleg (osztalyJellegId,kirOsztalyJellegId,osztalyJellegNev,erettsegizo,kovOsztalyJellegId,kezdoEvfolyam,vegzoEvfolyam,elokeszitoEvfolyam) 
    values (22,NULL,'általános iskola alsó tagozat (1-4)',0,NULL,1,4,'');
insert ignore into osztalyJelleg (osztalyJellegId,kirOsztalyJellegId,osztalyJellegNev,erettsegizo,kovOsztalyJellegId,kezdoEvfolyam,vegzoEvfolyam,elokeszitoEvfolyam) 
    values (23,NULL,'általlános iskola felső tagozat (5-8)',0,NULL,5,8,'');
-- 4 évfolyamos gimnázium
insert ignore into osztalyJelleg (osztalyJellegId,kirOsztalyJellegId,osztalyJellegNev,erettsegizo,kovOsztalyJellegId,kezdoEvfolyam,vegzoEvfolyam,elokeszitoEvfolyam) 
    values (31,NULL,'4 évfolyamos gimnázium',1,NULL,9,12,'');
insert ignore into osztalyJelleg (osztalyJellegId,kirOsztalyJellegId,osztalyJellegNev,erettsegizo,kovOsztalyJellegId,kezdoEvfolyam,vegzoEvfolyam,elokeszitoEvfolyam) 
    values (32,NULL,'4 évfolyamos gimnázium AJTP előkészítő évfolyammal (1+4)',1,NULL,9,12,'AJTP');
insert ignore into osztalyJelleg (osztalyJellegId,kirOsztalyJellegId,osztalyJellegNev,erettsegizo,kovOsztalyJellegId,kezdoEvfolyam,vegzoEvfolyam,elokeszitoEvfolyam) 
    values (33,NULL,'4 évfolyamos gimnázium AJKP előkészítő évfolyammal (1+4)',1,NULL,9,12,'AJKP');
insert ignore into osztalyJelleg (osztalyJellegId,kirOsztalyJellegId,osztalyJellegNev,erettsegizo,kovOsztalyJellegId,kezdoEvfolyam,vegzoEvfolyam,elokeszitoEvfolyam) 
    values (34,NULL,'4 évfolyamos gimnázium két tanítási nyelvű előkészítő évfolyammal (1+4)',1,NULL,9,12,'Kny');
insert ignore into osztalyJelleg (osztalyJellegId,kirOsztalyJellegId,osztalyJellegNev,erettsegizo,kovOsztalyJellegId,kezdoEvfolyam,vegzoEvfolyam,elokeszitoEvfolyam) 
    values (35,NULL,'4 évfolyamos gimnázium nemzetiségi előkészítő évfolyammal (1+4)',1,NULL,9,12,'N');
insert ignore into osztalyJelleg (osztalyJellegId,kirOsztalyJellegId,osztalyJellegNev,erettsegizo,kovOsztalyJellegId,kezdoEvfolyam,vegzoEvfolyam,elokeszitoEvfolyam) 
    values (36,NULL,'4 évfolyamos gimnázium nyelvi előkészítő évfolyammal (1+4)',1,NULL,9,12,'Ny');
-- 5 évfolyamos gimnázium
insert ignore into osztalyJelleg (osztalyJellegId,kirOsztalyJellegId,osztalyJellegNev,erettsegizo,kovOsztalyJellegId,kezdoEvfolyam,vegzoEvfolyam,elokeszitoEvfolyam) 
    values (41,NULL,'5 évfolyamos gimnázium',1,NULL,9,13,'');
insert ignore into osztalyJelleg (osztalyJellegId,kirOsztalyJellegId,osztalyJellegNev,erettsegizo,kovOsztalyJellegId,kezdoEvfolyam,vegzoEvfolyam,elokeszitoEvfolyam) 
    values (42,NULL,'5 évfolyamos gimnázium AJTP előkészítő évfolyammal (1+5)',1,NULL,9,13,'AJTP');
insert ignore into osztalyJelleg (osztalyJellegId,kirOsztalyJellegId,osztalyJellegNev,erettsegizo,kovOsztalyJellegId,kezdoEvfolyam,vegzoEvfolyam,elokeszitoEvfolyam) 
    values (43,NULL,'5 évfolyamos gimnázium AJKP előkészítő évfolyammal (1+5)',1,NULL,9,13,'AJKP');
insert ignore into osztalyJelleg (osztalyJellegId,kirOsztalyJellegId,osztalyJellegNev,erettsegizo,kovOsztalyJellegId,kezdoEvfolyam,vegzoEvfolyam,elokeszitoEvfolyam) 
    values (44,NULL,'5 évfolyamos gimnázium két tanítási nyelvű előkészítő évfolyammal (1+5)',1,NULL,9,13,'Kny');
insert ignore into osztalyJelleg (osztalyJellegId,kirOsztalyJellegId,osztalyJellegNev,erettsegizo,kovOsztalyJellegId,kezdoEvfolyam,vegzoEvfolyam,elokeszitoEvfolyam) 
    values (45,NULL,'5 évfolyamos gimnázium nemzetiségi előkészítő évfolyammal (1+5)',1,NULL,9,13,'N');
insert ignore into osztalyJelleg (osztalyJellegId,kirOsztalyJellegId,osztalyJellegNev,erettsegizo,kovOsztalyJellegId,kezdoEvfolyam,vegzoEvfolyam,elokeszitoEvfolyam) 
    values (46,NULL,'5 évfolyamos gimnázium nyelvi előkészítő évfolyammal (1+5)',1,NULL,9,13,'Ny');
-- 6 évfolyamos gimnázium
insert ignore into osztalyJelleg (osztalyJellegId,kirOsztalyJellegId,osztalyJellegNev,erettsegizo,kovOsztalyJellegId,kezdoEvfolyam,vegzoEvfolyam,elokeszitoEvfolyam) 
    values (51,NULL,'6 évfolyamos gimnázium',1,NULL,7,12,'');
insert ignore into osztalyJelleg (osztalyJellegId,kirOsztalyJellegId,osztalyJellegNev,erettsegizo,kovOsztalyJellegId,kezdoEvfolyam,vegzoEvfolyam,elokeszitoEvfolyam) 
    values (52,NULL,'6 évfolyamos gimnázium nemzetiségi előkészítő évfolyammal (1+6)',1,NULL,7,12,'N');
insert ignore into osztalyJelleg (osztalyJellegId,kirOsztalyJellegId,osztalyJellegNev,erettsegizo,kovOsztalyJellegId,kezdoEvfolyam,vegzoEvfolyam,elokeszitoEvfolyam) 
    values (53,NULL,'6 évfolyamos gimnázium nyelvi előkészítő évfolyammal  (1+6)',1,NULL,7,12,'Ny');
-- 8 évfolyamos gimnázium
insert ignore into osztalyJelleg (osztalyJellegId,kirOsztalyJellegId,osztalyJellegNev,erettsegizo,kovOsztalyJellegId,kezdoEvfolyam,vegzoEvfolyam,elokeszitoEvfolyam) 
    values (61,NULL,'8 évfolyamos gimnázium',1,NULL,4,12,'');
insert ignore into osztalyJelleg (osztalyJellegId,kirOsztalyJellegId,osztalyJellegNev,erettsegizo,kovOsztalyJellegId,kezdoEvfolyam,vegzoEvfolyam,elokeszitoEvfolyam) 
    values (62,NULL,'8 évfolyamos gimnázium nemzetiségi előkészítő évfolyammal (1+8)',1,NULL,4,12,'N');
insert ignore into osztalyJelleg (osztalyJellegId,kirOsztalyJellegId,osztalyJellegNev,erettsegizo,kovOsztalyJellegId,kezdoEvfolyam,vegzoEvfolyam,elokeszitoEvfolyam) 
    values (63,NULL,'8 évfolyamos gimnázium nyelvi előkészítő évfolyammal (1+8)',1,NULL,4,12,'Ny');
-- szakközépiskola - közismereti képzés (4-5 évfolyam)
insert ignore into osztalyJelleg (osztalyJellegId,kirOsztalyJellegId,osztalyJellegNev,erettsegizo,kovOsztalyJellegId,kezdoEvfolyam,vegzoEvfolyam,elokeszitoEvfolyam) 
    values (71,NULL,'szakközépiskola',1,NULL,9,NULL,'');
insert ignore into osztalyJelleg (osztalyJellegId,kirOsztalyJellegId,osztalyJellegNev,erettsegizo,kovOsztalyJellegId,kezdoEvfolyam,vegzoEvfolyam,elokeszitoEvfolyam) 
    values (72,NULL,'szakközépiskola AJTP előkészítő évfolyammal',1,NULL,9,NULL,'AJTP');
insert ignore into osztalyJelleg (osztalyJellegId,kirOsztalyJellegId,osztalyJellegNev,erettsegizo,kovOsztalyJellegId,kezdoEvfolyam,vegzoEvfolyam,elokeszitoEvfolyam) 
    values (73,NULL,'szakközépiskola AJKP előkészítő évfolyammal',1,NULL,9,NULL,'AJKP');
insert ignore into osztalyJelleg (osztalyJellegId,kirOsztalyJellegId,osztalyJellegNev,erettsegizo,kovOsztalyJellegId,kezdoEvfolyam,vegzoEvfolyam,elokeszitoEvfolyam) 
    values (74,NULL,'szakközépiskola két tanítási nyelvű előkészítő évfolyammal',1,NULL,9,NULL,'Kny');
insert ignore into osztalyJelleg (osztalyJellegId,kirOsztalyJellegId,osztalyJellegNev,erettsegizo,kovOsztalyJellegId,kezdoEvfolyam,vegzoEvfolyam,elokeszitoEvfolyam) 
    values (75,NULL,'szakközépiskola nemzetiségi előkészítő évfolyammal',1,NULL,9,NULL,'N');
insert ignore into osztalyJelleg (osztalyJellegId,kirOsztalyJellegId,osztalyJellegNev,erettsegizo,kovOsztalyJellegId,kezdoEvfolyam,vegzoEvfolyam,elokeszitoEvfolyam) 
    values (76,NULL,'szakközépiskola nyelvi előkészítő évfolyammal',1,NULL,9,NULL,'Ny');
-- szakközépiskola - szakképzés
insert ignore into osztalyJelleg (osztalyJellegId,kirOsztalyJellegId,osztalyJellegNev,erettsegizo,kovOsztalyJellegId,kezdoEvfolyam,vegzoEvfolyam,elokeszitoEvfolyam) 
    values (77,NULL,'szakközépiskola - szakképzés',0,NULL,NULL,NULL,'');
-- szakiskola
insert ignore into osztalyJelleg (osztalyJellegId,kirOsztalyJellegId,osztalyJellegNev,erettsegizo,kovOsztalyJellegId,kezdoEvfolyam,vegzoEvfolyam,elokeszitoEvfolyam) 
    values (81,NULL,'szakiskola',0,NULL,NULL,NULL,'');
-- egyéb
insert ignore into osztalyJelleg (osztalyJellegId,kirOsztalyJellegId,osztalyJellegNev,erettsegizo,kovOsztalyJellegId,kezdoEvfolyam,vegzoEvfolyam,elokeszitoEvfolyam) 
    values (91,NULL,'Híd I. program',0,NULL,9,9,'');
insert ignore into osztalyJelleg (osztalyJellegId,kirOsztalyJellegId,osztalyJellegNev,erettsegizo,kovOsztalyJellegId,kezdoEvfolyam,vegzoEvfolyam,elokeszitoEvfolyam) 
    values (92,NULL,'Híd II. program',0,NULL,8,8,'');
