
alter table osztalyDiak drop primary key;
alter table osztalyDiak add primary key (`osztalyId`,`diakId`,`beDt`);

