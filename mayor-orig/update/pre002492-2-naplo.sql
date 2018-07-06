INSERT INTO munkaterv (munkatervId,munkatervNev) VALUES (1,'alapértelmezett');
UPDATE nap SET munkatervId=1 WHERE munkatervId IS NULL;
 alter table ora drop foreign key `ora_ibfk_5`;
 alter table nap drop primary key, add primary key (`munkatervId`,`dt`);