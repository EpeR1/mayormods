alter table koszi modify igazolo set('diák','tanár','osztályfőnök','dök');
alter table koszi add targyId smallint(5) unsigned default null;
alter table koszi add constraint koszi_ibfk_2 foreign key (targyId) references %INTEZMENYDB%.targy (targyId) ON DELETE set null on update cascade;
