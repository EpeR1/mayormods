

alter table bejegyzesTipus add column tolDt date default null;
alter table bejegyzesTipus add column igDt date default null;
update bejegyzesTipus set tolDt='2003-01-01' where tolDt is null;