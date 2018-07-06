
CREATE TEMPORARY TABLE _tmpDj SELECT CONCAT_WS(':', `diakId`, `dt`, MIN(0+`statusz`)) AS kulcs FROM diakJogviszony GROUP BY diakId, dt HAVING COUNT(*) > 1;

UPDATE diakJogviszony SET dt=dt-INTERVAL 1 DAY WHERE CONCAT_WS(':', `diakId`, `dt`, (0+`statusz`)) IN (SELECT kulcs FROM _tmpDj);

ALTER TABLE diakJogviszony DROP PRIMARY KEY;
ALTER TABLE diakJogviszony ADD PRIMARY KEY (`diakId`, `dt`);

