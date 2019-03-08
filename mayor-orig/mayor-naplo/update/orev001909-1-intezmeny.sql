drop table if exists _hibasZarojegyek;
create table _hibasZarojegyek (select distinct a.zaroJegyId from vizsga AS a LEFT JOIN zaroJegy AS b USING (zaroJegyId) WHERE szemeszter=1);
update zaroJegy SET szemeszter=2 WHERE zaroJegyId IN (SELECT * FROM _hibasZarojegyek);
drop table if exists _hibasZarojegyek;
