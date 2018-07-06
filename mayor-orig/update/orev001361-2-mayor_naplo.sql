-- Újabb intézményi adatok felvétele

alter table intezmeny add column telefon VARCHAR(64) NULL;
alter table intezmeny add column fax VARCHAR(64) NULL;
alter table intezmeny add column email VARCHAR(96) NULL;
alter table intezmeny add column honlap VARCHAR(96) NULL;
