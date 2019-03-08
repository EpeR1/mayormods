-- Hiányzó diak státuszok 'jogviszonyban van'-ra állítása
update diak set statusz='jogviszonyban van' where statusz is null;
-- A státusz mező alapértelmezett értékének megadása
alter table diak modify statusz ENUM('jogviszonyban van','magántanuló','jogviszonya felfüggesztve','jogviszonya lezárva') NOT NULL DEFAULT 'jogviszonyban van';
