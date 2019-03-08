
alter table diak add column `lakohelyiJellemzo` enum('körzetes','kerületi','helybéli','bejáró','kollégista') COLLATE utf8_hungarian_ci DEFAULT NULL after kollegista;
alter table diak add column megjegyzes varchar(255) collate utf8_hungarian_ci default null;

set @helyseg=(select cimHelyseg from telephely where telephelyId = 1);
set @kerIrsz=(select floor(cimIrsz/10)*10 from telephely where telephelyId = 1);

update diak set lakohelyiJellemzo='kollégista' where kollegista=1;
update diak set lakohelyiJellemzo='helybéli' where lakhelyHelyseg=@helyseg and kollegista<>1;
update diak set lakohelyiJellemzo='kerületi' where @helyseg='Budapest' and @kerIrsz=(floor(lakhelyIrsz/10)*10) and kollegista<>1;

alter table diak drop column kollegista;
