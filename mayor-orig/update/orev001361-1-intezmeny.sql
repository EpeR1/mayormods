-- Cím attribútumok módosítása

alter table szulo modify cimEmelet VARCHAR(5) NULL;
alter table szulo modify cimAjto SMALLINT UNSIGNED NULL;

alter table diak modify lakhelyEmelet VARCHAR(5) NULL;
alter table diak modify lakhelyAjto SMALLINT UNSIGNED NULL;

alter table diak modify tartEmelet VARCHAR(5) NULL;
alter table diak modify tartAjto SMALLINT UNSIGNED NULL;
