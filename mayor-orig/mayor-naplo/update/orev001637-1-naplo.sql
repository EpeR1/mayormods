-- A korábbi (2008-as) szempontrendszerek átmentése

insert into %INTEZMENYDB%.szempontRendszer select *,2008,1 from szempontRendszer;
insert into %INTEZMENYDB%.szrSzempont select * from szrSzempont;
insert into %INTEZMENYDB%.szrMinosites select * from szrMinosites;

alter table szovegesErtekeles drop foreign key szovegesErtekeles_ibfk_3;
alter table szovegesErtekeles add foreign key szovegesErtekeles_ibfk_3 (`szrId`)
    REFERENCES %INTEZMENYDB%.`szempontRendszer`(`szrId`)
      ON DELETE CASCADE
      ON UPDATE CASCADE;

alter table szeEgyediMinosites drop foreign key szeEgyediMinosites_ibfk_1;
alter table szeEgyediMinosites add FOREIGN KEY szeEgyediMinosites_ibfk_1 (`szempontId`)
    REFERENCES %INTEZMENYDB%.`szrSzempont`(`szempontId`)
      ON DELETE CASCADE
      ON UPDATE CASCADE;

alter table szeMinosites drop foreign key szeMinosites_ibfk_2;
alter table szeMinosites add FOREIGN KEY szeMinosites_ibfk_2 (`minositesId`)
    REFERENCES %INTEZMENYDB%.`szrMinosites`(`minositesId`)
      ON DELETE CASCADE
      ON UPDATE CASCADE;

drop table szrMinosites;
drop table szrSzempont;
drop table szempontRendszer;