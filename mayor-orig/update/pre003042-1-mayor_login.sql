CREATE TABLE `loginLog_a` (
  `logId` int(10) unsigned NOT NULL,
  `policy` varchar(10) NOT NULL DEFAULT 'private',
  `userAccount` varchar(50) DEFAULT NULL,
  `ip` varchar(15) DEFAULT NULL,
  `dt` datetime DEFAULT NULL,
  `flag` tinyint(3) unsigned DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8;
INSERT INTO loginLog_a SELECT * FROM loginLog WHERE dt<CURDATE() - INTERVAL 1 DAY;
DELETE FROM loginLog WHERE dt<CURDATE() - INTERVAL 1 DAY;
alter table loginLog ADD INDEX IDX_puf (policy,userAccount,flag);
alter table loginLog ADD INDEX IDX_pudf (policy,userAccount,dt,flag);
