-- SQL utasítások a intezmeny adatbázisban --
alter table tankor modify tankorTipus enum('','első nyelv','második nyelv','délutáni') not null default '';
