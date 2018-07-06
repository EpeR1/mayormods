DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_3680 $$
CREATE PROCEDURE upgrade_database_3680()
BEGIN

SET NAMES utf8 COLLATE utf8_hungarian_ci;

insert ignore into osztalyJelleg (osztalyJellegId,kirOsztalyJellegId,osztalyJellegNev,erettsegizo,evfolyamJelek) 
    values (65,NULL,'12 évfolyamos gimnázium (1-12)',1,'1,2,3,4,5,6,7,8,9,10,11,12')
    on duplicate key update kirOsztalyJellegId=values(kirOsztalyJellegId), osztalyJellegNev=values(osztalyJellegNev), erettsegizo=values(erettsegizo), evfolyamJelek=values(evfolyamJelek);
insert ignore into osztalyJelleg (osztalyJellegId,kirOsztalyJellegId,osztalyJellegNev,erettsegizo,evfolyamJelek) 
    values (87,NULL,'szakképzés - középiskolai végzettséghez kötött két éves szakképzésre épülő egy éves szakképesítés-ráépülés (3/15)',0,'3/15')
    on duplicate key update kirOsztalyJellegId=values(kirOsztalyJellegId), osztalyJellegNev=values(osztalyJellegNev), erettsegizo=values(erettsegizo), evfolyamJelek=values(evfolyamJelek);

END $$
DELIMITER ;
CALL upgrade_database_3680();


