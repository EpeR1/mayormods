DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_3616 $$
CREATE PROCEDURE upgrade_database_3616()
BEGIN

SET NAMES utf8 COLLATE utf8_hungarian_ci;


IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='mayor_naplo' and TABLE_NAME='osztalyJelleg' AND COLUMN_NAME='evfolyamJelek'
) THEN
    alter table osztalyJelleg add column evfolyamJelek varchar(255) character set utf8 collate utf8_hungarian_ci default '';
ELSE
    alter table osztalyJelleg modify evfolyamJelek varchar(255) character set utf8 collate utf8_hungarian_ci default '';
END IF;


-- már elavult osztály-jellegek
update osztalyJelleg set evfolyamJelek='1,2,3,4,5,6,7,8' where osztalyJellegId=1;
update osztalyJelleg set evfolyamJelek='9,10,11,12' where osztalyJellegId=2;
update osztalyJelleg set evfolyamJelek='9,10,11,12' where osztalyJellegId=3;
update osztalyJelleg set evfolyamJelek='7,8,9,10,11,12' where osztalyJellegId=4;
update osztalyJelleg set evfolyamJelek='5,6,7,8,9,10,11,12' where osztalyJellegId=5;
-- -- -- szakiskola??
update osztalyJelleg set evfolyamJelek='9,10,11,12' where osztalyJellegId=6;
-- -- -- speciális szakiskola??
update osztalyJelleg set evfolyamJelek='9/E,9,10,11,12' where osztalyJellegId=7;
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
update osztalyJelleg set evfolyamJelek='5,6,7,8',osztalyJellegNev='általános iskola felső tagozat (5-8)' where osztalyJellegId=23;
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
update osztalyJelleg set evfolyamJelek='9/AJTP,9,10,11,12',osztalyJellegNev='szakközépiskola AJTP előkészítő évfolyammal (1+4)' where osztalyJellegId=72;
update osztalyJelleg set evfolyamJelek='9/AJKP,9,10,11,12',osztalyJellegNev='szakközépiskola AJKP előkészítő évfolyammal (1+4)' where osztalyJellegId=73;
update osztalyJelleg set evfolyamJelek='9/Kny,9,10,11,12',osztalyJellegNev='szakközépiskola két tanítási nyelvű előkészítő évfolyammal (1+4)' where osztalyJellegId=74;
update osztalyJelleg set evfolyamJelek='9/N,9,10,11,12',osztalyJellegNev='szakközépiskola nemzetiségi előkészítő évfolyammal (1+4)' where osztalyJellegId=75;
update osztalyJelleg set evfolyamJelek='9/Ny,9,10,11,12',osztalyJellegNev='szakközépiskola nyelvi előkészítő évfolyammal (1+4)' where osztalyJellegId=76;
-- szakközépiskola - szakképzés
update osztalyJelleg set evfolyamJelek='13,14',osztalyJellegEles=0 where osztalyJellegId=77;
insert ignore into osztalyJelleg (osztalyJellegId,kirOsztalyJellegId,osztalyJellegNev,erettsegizo,evfolyamJelek) 
    values (78,NULL,'szakközépiskolák szakiskolai végzettséggel rendelkező tanulóinak érettségire történő felkészítése (Szé/12/1-2)',1,'Szé/12/1,Szé/12/2')
    on duplicate key update kirOsztalyJellegId=values(kirOsztalyJellegId), osztalyJellegNev=values(osztalyJellegNev), erettsegizo=values(erettsegizo), evfolyamJelek=values(evfolyamJelek);
insert ignore into osztalyJelleg (osztalyJellegId,kirOsztalyJellegId,osztalyJellegNev,erettsegizo,evfolyamJelek) 
    values (79,NULL,'szakképzés - a szakközépiskola négy évére épülő 1 évfolyamos képzés (5/13)',0,'5/13')
    on duplicate key update kirOsztalyJellegId=values(kirOsztalyJellegId), osztalyJellegNev=values(osztalyJellegNev), erettsegizo=values(erettsegizo), evfolyamJelek=values(evfolyamJelek);
-- szakiskola - szakképzés (NKT. 13. § (1) - 3 évfolyam)
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


END $$
DELIMITER ;
CALL upgrade_database_3616();


