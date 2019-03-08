
CREATE TABLE diakJogviszony (
  diakId INTEGER UNSIGNED NOT NULL,
  statusz ENUM('jogviszonyban van','magántanuló','jogviszonya felfüggesztve','jogviszonya lezárva') NOT NULL,
  dt DATE NOT NULL,
  PRIMARY KEY(diakId, statusz, dt),
  INDEX diakJogviszony_FKIndex1(diakId),
  FOREIGN KEY(diakId)
    REFERENCES diak(diakId)
      ON DELETE CASCADE
      ON UPDATE CASCADE
)
ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_hungarian_ci;

insert into diakJogviszony select diakId, 'jogviszonyban van' as statusz, jogviszonyKezdete as dt from diak;
insert into diakJogviszony select diakId, 'jogviszonya lezárva' as statusz, jogviszonyVege as dt from diak where jogviszonyVege is not null and jogviszonyVege<>'0000-00-00';

-- A magántanulókat és felfüggesztett jogviszonűakat mai dátummal vesszük fel, de ez teljesen önkényes - kézzel javítandó!

insert into diakJogviszony select diakId, statusz, curdate() from diak where statusz not in ('jogviszonyban van','jogviszonya lezárva');

alter table diak add column torvenyesKepviselo SET('anya','apa','gyám','gondnok');
update diak left join szulo on apaId=szuloId set torvenyesKepviselo='apa' where szulo.statusz='törvényes képviselő';
update diak left join szulo on anyaId=szuloId set torvenyesKepviselo=concat_ws(',',torvenyesKepviselo,'anya') where szulo.statusz='törvényes képviselő';
update diak left join szulo on gondviseloId=szuloId set torvenyesKepviselo=concat_ws(',',torvenyesKepviselo,'gyám') where szulo.statusz='törvényes képviselő';

-- A diak.statusz újraértelmezésre szorul...
alter table szulo modify statusz enum('elhunyt','házas','egyedülálló','hajadon / nőtlen','elvált','özvegy','élettársi kapcsolatban él') NULL;