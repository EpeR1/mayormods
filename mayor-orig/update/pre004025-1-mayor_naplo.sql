DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4025 $$
CREATE PROCEDURE upgrade_database_4025()
BEGIN

SET NAMES utf8 COLLATE utf8_hungarian_ci;

update osztalyJelleg SET osztalyJellegNev=REPLACE(osztalyJellegNev,'szakközépiskola','szakgimnázium') where osztalyJellegNev LIKE '%szakkozepiskola%' AND osztalyJellegEles=1;
update osztalyJelleg SET osztalyJellegNev=REPLACE(osztalyJellegNev,'szakiskola','szakközépiskola') where osztalyJellegNev LIKE '%szakiskola%' AND osztalyJellegEles=1;

END $$
DELIMITER ;
CALL upgrade_database_4025();
