
alter table kepzesOraszam add column tanitasiHetekSzama smallint unsigned;
update kepzesOraszam set tanitasiHetekSzama=37 where evfolyam <> 12;
update kepzesOraszam set tanitasiHetekSzama=31 where evfolyam = 12;
-- A "felvételt nyert" státusz bevezetése
alter table diakJogviszony modify statusz enum('felvételt nyert','jogviszonyban van','magántanuló','jogviszonya felfüggesztve','jogviszonya lezárva') NOT NULL;
alter table diak modify statusz enum('felvételt nyert','jogviszonyban van','magántanuló','jogviszonya felfüggesztve','jogviszonya lezárva') NOT NULL;
