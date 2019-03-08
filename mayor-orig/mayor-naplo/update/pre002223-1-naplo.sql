create temporary table _hianyzasr2223 select hianyzasId,oraId,tankorId,%INTEZMENYDB%.tankor.tankorTipusId from hianyzas LEFT JOIN ora USING (oraId) LEFT JOIN %INTEZMENYDB%.tankor USING (tankorId) where hianyzas.tankorTipusId is null;
create index hh on _hianyzasr2223 (hianyzasId);
update hianyzas SET tankorTipusId = (SELECT tankorTipusId FROM _hianyzasr2223 WHERE hianyzasId=hianyzas.hianyzasId) WHERE hianyzas.tankorTipusId IS NULL;
