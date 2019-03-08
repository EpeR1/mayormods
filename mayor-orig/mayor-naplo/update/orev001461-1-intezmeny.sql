
alter table diak drop foreign key `diak_ibfk_1`;
alter table diak drop column vegzoTanev;
alter table diak drop column vegzoSzemeszter;
alter table diak add column penzugyiStatusz enum('állami finanszírozás','térítési díj','tandíj') default 'állami finanszírozás';
alter table diak add column szocialisHelyzet set(
    'szülei elváltak','három vagy több gyerekes család','rendszeres gyermekvédelmi támogatást kap','állami gondozott',
    'veszélyeztetett','hátrányos helyzetű','halmozottan hátrányos helyzetű','sajátos nevelési igényű'
) null;
alter table diak add column fogyatekossag set(
    'tartósan beteg','mozgássérült','beszédfogyatékos','hallássérült','látássérült','diszlexia','diszkalkulia','diszgráfia',
    'tanulásban akadályozott','értelmileg akadályozott','autista','tanulási képességek kevert zavarával küzdő'
) null;
alter table diak add column gondozasiSzam varchar(128);

alter table diak add column adoazonosito bigint(10) zerofill unsigned null after tajSzam;
alter table diak add column szemelyiIgazolvanySzam varchar(16) null after adoazonosito;
-- tartozkodasiOkiratSzam
alter table diak add column elozoIskolaOMKod mediumint unsigned zerofill not null;
alter table diak add column kollegista tinyint unsigned not null default 0;
alter table diak add column neveloId integer unsigned null after gondviseloId;

alter table szulo add column foglalkozas varchar(128) after email;
alter table szulo add column szuletesiEv year after nem;

alter table zaroJegy add column javitoJegy tinyint unsigned not null after jegy;
