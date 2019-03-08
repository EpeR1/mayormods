
update zaroJegy left join szemeszter using (tanev,szemeszter) set dt=zarasDt where dt is null;
alter table zaroJegy drop FOREIGN KEY `zaroJegy_ibfk_1`;
alter table zaroJegy drop javitoJegy;

alter table zaroJegy change tanev _tanev smallint(5) unsigned NOT NULL;
alter table zaroJegy change szemeszter _szemeszter tinyint(3) unsigned DEFAULT NULL;

alter table zaroJegy change dt modositasDt date NOT NULL;
alter table zaroJegy change jegySzemeszter felev tinyint(3) unsigned DEFAULT NULL;
alter table zaroJegy add hivatalosDt date NOT NULL;

update zaroJegy left join vizsga using (zaroJegyId) set hivatalosDt=vizsgaDt where vizsgaId is not null;
update zaroJegy left join szemeszter on tanev=_tanev AND szemeszter=_szemeszter set hivatalosDt=zarasDt where hivatalosDt='0000-00-00';

