
alter table mkTanar modify `mkId` smallint(5) unsigned NOT NULL;
alter table tankorSzemeszter modify tankorId int(10) unsigned NOT NULL;
alter table tankorTanar modify tankorId int(10) unsigned NOT NULL;
alter table szeEgyediMinosites modify `szempontId` int(10) unsigned NOT NULL;
alter table tankorDiak modify tankorId int(10) unsigned NOT NULL;
alter table tankorOsztaly modify tankorId int(10) unsigned NOT NULL;
