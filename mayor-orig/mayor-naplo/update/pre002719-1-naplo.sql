
insert into `munkatervOsztaly` (`munkatervId`, `osztalyId`) select  1 as `munkatervId`, `osztalyId` from `osztalyNaplo` 
    where `osztalyId` not in (select `osztalyId` from `munkatervOsztaly`);
