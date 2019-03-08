DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_3877 $$
CREATE PROCEDURE upgrade_database_3877()
BEGIN

SET NAMES utf8 COLLATE utf8_hungarian_ci;


-- 6 évfolyamos gimnázium 11/Ny nyelvi előkészítő évfolyammal (AKG)
insert ignore into osztalyJelleg (osztalyJellegId,kirOsztalyJellegId,osztalyJellegNev,erettsegizo,kovOsztalyJellegId,_kezdoEvfolyam,_vegzoEvfolyam,elokeszitoEvfolyam,osztalyJellegEles,evfolyamJelek) 
    values (54,NULL,'6 évfolyamos gimnázium 11/Ny nyelvi előkészítő évfolyammal (4+1+2)',1,NULL,7,12,'Ny',1,'7,8,9,10,11/Ny,11,12')
    on duplicate key update kirOsztalyJellegId=values(kirOsztalyJellegId), osztalyJellegNev=values(osztalyJellegNev), erettsegizo=values(erettsegizo), evfolyamJelek=values(evfolyamJelek);

END $$
DELIMITER ;
CALL upgrade_database_3877();


