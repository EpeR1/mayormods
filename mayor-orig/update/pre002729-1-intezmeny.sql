
alter table kepzesDiak add column tolDt date default null;
alter table kepzesDiak add column igDt date default null;
update kepzesDiak left join diak using (diakId) set tolDt=jogviszonyKezdete, igDt=jogviszonyVege;
alter table kepzesDiak drop primary key;
alter table kepzesDiak add primary key (`kepzesId`,`diakId`,`tolDt`);


