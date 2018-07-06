alter table logBejegyzes ADD actionId varchar(23) NULL;
alter table logBejegyzes ADD INDEX IDX_a (actionId);
