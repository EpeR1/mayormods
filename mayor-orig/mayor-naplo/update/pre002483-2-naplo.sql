-- drop existing key and columns
drop procedure if exists drop_column;
delimiter ';;'
create procedure drop_column() 
begin 
    if exists (select * from information_schema.table_constraints where table_schema=database() and table_name = 'bejegyzes' and constraint_name='bejegyzes_ibfk_3') then
	alter table `bejegyzes` drop foreign key `bejegyzes_ibfk_3`;
    end if; 
    if exists (select * from information_schema.columns where table_schema = database() and table_name = 'bejegyzes' and column_name = 'bejegyzesTipusId') then 
	alter table `bejegyzes` drop column `bejegyzesTipusId`;
    end if; 
    if exists (select * from information_schema.columns where table_schema = database() and table_name = 'bejegyzes' and column_name = 'hianyzasDb') then 
	alter table `bejegyzes` drop column `hianyzasDb`;
    end if; 
end;;
delimiter ';'
call drop_column();

-- alter table bejegyzes (add column)
alter table `bejegyzes` add column `bejegyzesTipusId` tinyint(3) unsigned;
alter table `bejegyzes` add column `hianyzasDb` tinyint(3) unsigned DEFAULT NULL;

-- update bejegyzes
update `bejegyzes` left join `%INTEZMENYDB%`.`bejegyzesTipus` 
on `bejegyzes`.`tipus`=`bejegyzesTipus`.`tipus` and (`bejegyzes`.`fokozat`=`bejegyzesTipus`.`fokozat` or `bejegyzes`.`tipus`='üzenet')
set `bejegyzes`.`bejegyzesTipusId`=`bejegyzesTipus`.`bejegyzesTipusId`;

-- Még nincsenek a bejegyzesTipus táblában beállítva a hianyzasDb értékek
-- -- update `bejegyzes` left join `%INTEZMENYDB%`.`bejegyzesTipus` using (`bejegyzesTipusId`) 
-- --    set `bejegyzes`.`hianyzasDb`=`bejegyzesTipus`.`hianyzasDb` where `szoveg`='Igazolatlan hiányzás';
-- Ezért fix 1-re állítjuk (hogy pozitív legyen - látszik, hogy hiányzásért kapta)
update `bejegyzes` left join `%INTEZMENYDB%`.`bejegyzesTipus` using (`bejegyzesTipusId`) 
    set `bejegyzes`.`hianyzasDb`=1 where `szoveg`='Igazolatlan hiányzás' and `bejegyzes`.`tipus`='fegyelmi';

-- alter table bejegyzes (drop column)
alter table `bejegyzes` drop column `fokozat`;
alter table `bejegyzes` drop column `tipus`;
alter table `bejegyzes` modify `bejegyzesTipusId` tinyint(3) unsigned NOT NULL;
alter table `bejegyzes` ADD FOREIGN KEY `bejegyzes_ibfk_3` (`bejegyzesTipusId`) 
    REFERENCES `%INTEZMENYDB%`.`bejegyzesTipus` (`bejegyzesTipusId`) ON DELETE NO ACTION ON UPDATE CASCADE;

