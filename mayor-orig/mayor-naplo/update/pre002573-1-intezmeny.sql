
alter table tanmenet add column `statusz` enum('új','kész','jóváhagyott','publikus','elavult') default 'új';

update tanmenet set statusz='jóváhagyott' where jovahagyva > 0;
update tanmenet set statusz='kész' where statusz='új' and oraszam=(select sum(oraszam) from tanmenetTankor where tanmenetTankor.tanmenetId=tanmenet.tanmenetId);

alter table tanmenet drop column jovahagyva;