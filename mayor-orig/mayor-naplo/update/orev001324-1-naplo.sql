-- SQL utasítások a tanév adatbázisban --
alter table ora drop foreign key ora_ibfk_4;
alter table ora add constraint ora_ibfk_4 foreign key (teremId)
    references %INTEZMENYDB%.terem(teremId) on delete set null on update cascade;

alter table orarendiOra drop foreign key orarendiOra_ibfk_2;
alter table orarendiOra add constraint orarendiOra_ibfk_2 foreign key (teremId)
    references %INTEZMENYDB%.terem(teremId) on delete set null on update cascade;

alter table fogadoOra drop foreign key fogadoOra_ibfk_2;
alter table fogadoOra add constraint fogadoOra_ibfk_2 foreign key (teremId)
    references %INTEZMENYDB%.terem(teremId) on delete set null on update cascade;

