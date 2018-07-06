DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache` (`sessionID` char(40), `policy` varchar(10), `kulcs` varchar(32) NOT NULL, `ertek` varchar(100), dt DATETIME)
ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_hungarian_ci;
ALTER TABLE `cache` add primary key PK (sessionID,policy,kulcs);
