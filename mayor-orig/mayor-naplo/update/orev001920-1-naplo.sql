drop table if exists _hibasHianyzasok;
create table _hibasHianyzasok (select hianyzasId from hianyzas left join ora using (oraId) WHERE ora.tipus IN ('elmarad','elmarad máskor'));
DELETE FROM `hianyzas` WHERE `hianyzasId` IN (SELECT * FROM _hibasHianyzasok);
drop table if exists _hibasHianyzasok;
