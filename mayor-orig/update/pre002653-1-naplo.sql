create temporary table _duplaHianyzas select diakId , oraId , tipus , min(hianyzasId) as hianyzasId, count(*) as db from hianyzas group by diakId , oraId , tipus having db>1;
create temporary table _torlendoHianyzas select hianyzas.hianyzasId from hianyzas left join _duplaHianyzas using (diakId, oraId, tipus) where _duplaHianyzas.hianyzasId is not null and _duplaHianyzas.hianyzasId<>hianyzas.hianyzasId;
delete from hianyzas where hianyzasId in (select hianyzasId from _torlendoHianyzas);
alter table hianyzas add UNIQUE KEY (`oraId`,`diakId`,`tipus`);
