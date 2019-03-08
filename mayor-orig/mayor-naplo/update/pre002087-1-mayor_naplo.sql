
-- intezmeny.telephely létrehozása
alter table settings add column telephelyId tinyint unsigned;
alter table session add column telephelyId tinyint(3) unsigned default null after intezmeny;

