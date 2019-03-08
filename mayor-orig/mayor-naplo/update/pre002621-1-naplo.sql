
-- hibás (elmaradt órákhoz rendelt) hiányzások törlése
create temporary table _torlendo select hianyzasId from hianyzas left join ora using (oraId) where ora.tipus like 'elmarad%';
delete from hianyzas where hianyzasId in (select hianyzasId from _torlendo);

