alter table zaroJegy MODIFY 
jegyTipus enum('jegy','aláírás','százalékos','három szintű','egyedi felsorolás','felmentett','magatartás','szorgalom') NOT NULL DEFAULT 'jegy'; 

update targy SET evkoziKovetelmeny='magatartás',zaroKovetelmeny='magatartás',targyJelleg='magatartás' WHERE targyNev='magatartás';
update targy SET evkoziKovetelmeny='szorgalom',zaroKovetelmeny='szorgalom',targyJelleg='szorgalom'  WHERE targyNev='szorgalom';
update zaroJegy SET jegyTipus='magatartás' WHERE targyId=(select targyId from targy where targy.targyJelleg  LIKE 'magatart%s' LIMIT 1);
update zaroJegy SET jegyTipus='szorgalom' WHERE targyId=(select targyId from targy where targy.targyJelleg  LIKE 'szorgalom' LIMIT 1);
-- ha véletlenül lenne ilyen tankör
update tankor SET kovetelmeny='magatartás' WHERE targyId IN (select targyId FROM targy WHERE targyJelleg LIKE 'magatart%s');
update tankor SET kovetelmeny='szorgalom' WHERE targyId IN (select targyId FROM targy WHERE targyJelleg LIKE 'szorgalom');

DROP TABLE IF EXISTS x;
CREATE TEMPORARY TABLE x (
 SELECT zaroJegyId,osztalyDiak.osztalyId,zaroJegy.diakId,zaroJegy.tanev,szemeszter,szemeszter.zarasDt AS dt,tanev-kezdoTanev+kezdoEvfolyam AS evfolyam 
 FROM zaroJegy 
    LEFT JOIN szemeszter USING (tanev,szemeszter)
    LEFT JOIN osztalyDiak ON (zaroJegy.diakId=osztalyDiak.diakId AND szemeszter.zarasDt>=osztalyDiak.beDt 
	    AND (szemeszter.zarasDt<=osztalyDiak.kiDt OR osztalyDiak.kiDt IS NULL))
    LEFT JOIN osztaly USING (osztalyId) 
);
DROP TABLE IF EXISTS dx;
CREATE TABLE dx (select DISTINCT zaroJegyId from x group by zaroJegyId HAVING count(*)>1);
DELETE FROM x WHERE zaroJegyId IN (SELECT zaroJegyId FROM dx);
ALTER TABLE x ADD INDEX zj (zaroJegyId,evfolyam);
UPDATE zaroJegy SET evfolyam=(select evfolyam from x WHERE x.zaroJegyId=zaroJegy.zaroJegyId);
DROP TABLE x;
