
DROP DATABASE IF EXISTS %MYSQL_LOGIN_DB%;
CREATE DATABASE %MYSQL_LOGIN_DB% CHARACTER SET utf8 DEFAULT COLLATE utf8_hungarian_ci;
GRANT ALL ON %MYSQL_LOGIN_DB%.* TO '%MYSQL_LOGIN_USER%'@'localhost' IDENTIFIED BY '%MYSQL_LOGIN_PW%';
USE %MYSQL_LOGIN_DB%;

--
-- Table structure for table 'login_log'
--

CREATE TABLE `mayorUpdateLog` (
  `scriptFile` varchar(255) COLLATE utf8_hungarian_ci NOT NULL DEFAULT '',
  `dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`scriptFile`,`dt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE loginLog (
  logId int(10) unsigned NOT NULL auto_increment,
  policy varchar(10) NOT NULL default 'private',
  userAccount varchar(50) default NULL,
  ip varchar(15) default NULL,
  dt datetime default NULL,
  flag tinyint(3) unsigned default NULL,
  PRIMARY KEY  (logId)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_hungarian_ci;

--
-- Table structure for table 'session'
--

CREATE TABLE session (
  sessionID char(40) COLLATE utf8_hungarian_ci NOT NULL,
  userPassword varchar(32) character set latin1 default NULL,
  userAccount varchar(50) default NULL,
  userCn varchar(50) default NULL,
  studyId BIGINT UNSIGNED NULL,
  dt datetime default NULL,
  policy varchar(10) COLLATE utf8_hungarian_ci NOT NULL default 'private',
  skin varchar(16) default NULL,
  lang varchar(5) default NULL,
  jsLevel tinyint(3) unsigned default 2,
  activity datetime default NULL,
  sessionCookie char(40) NOT NULL,
  PRIMARY KEY (sessionID,policy)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_hungarian_ci;

--
-- Table structure for table 'settings'
--

CREATE TABLE settings (
  userAccount varchar(50) NOT NULL,
  policy varchar(10) NOT NULL default 'private',
  skin varchar(20) default 'default',
  lang varchar(5) default 'hu_HU',
  jsLevel tinyint(3) unsigned default 2,
  lastlogin datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY (userAccount,policy)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_hungarian_ci;

--
-- Table structure for table 'start'
--

CREATE TABLE start (
  userAccount varchar(50) NOT NULL,
  type varchar(10) NOT NULL default 'column',
  name varchar(20) default NULL,
  idx tinyint unsigned default 0,
  PRIMARY KEY (userAccount,type,name),
  KEY (userAccount)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_hungarian_ci;

--
-- Table structure for table 'stat'
--

CREATE TABLE stat (
  dt datetime default NULL,
  policy varchar(20) default NULL,
  page varchar(20) default NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_hungarian_ci;

CREATE TABLE `cache` (
  `sessionID` char(40), 
  `policy` varchar(10), 
  `kulcs` varchar(32) NOT NULL, 
  `ertek` varchar(100), dt DATETIME,
  PRIMARY KEY (sessionID,policy,kulcs)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_hungarian_ci;

CREATE TABLE `mayorSsl` (
  `sslId` tinyint(1) unsigned NOT NULL AUTO_INCREMENT,
  `nodeId` mediumint(8) unsigned zerofill NOT NULL DEFAULT '00000000',
  `privateKey` text COLLATE utf8_hungarian_ci NOT NULL,
  `publicKey` text COLLATE utf8_hungarian_ci NOT NULL,
  `secret` varchar(40) COLLATE utf8_hungarian_ci DEFAULT NULL,
  PRIMARY KEY (`sslId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `mayorKeychain` (
  `nodeId` mediumint(8) unsigned zerofill NOT NULL DEFAULT '00000000',
  `nodeTipus` enum('intézmény','fenntartó','backup','fejlesztői','controller') COLLATE utf8_hungarian_ci DEFAULT 'intézmény',
  `dt` date DEFAULT NULL,
  `OMKod` mediumint(8) unsigned zerofill NOT NULL DEFAULT '00000000',
  `publicKey` text COLLATE utf8_hungarian_ci NOT NULL,
  `valid` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `url` varchar(255) COLLATE utf8_hungarian_ci NOT NULL,
  `nev` varchar(128) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `rovidNev` varchar(16) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `cimHelyseg` varchar(32) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `cimIrsz` varchar(8) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `cimKozteruletNev` varchar(32) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `cimKozteruletJelleg` varchar(32) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `cimHazszam` varchar(20) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `telefon` varchar(64) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `fax` varchar(64) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `email` varchar(96) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `honlap` varchar(96) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `kapcsolatNev` varchar(128) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `kapcsolatEmail` varchar(96) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `kapcsolatTelefon` varchar(64) COLLATE utf8_hungarian_ci DEFAULT NULL,
  PRIMARY KEY (`nodeId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

INSERT IGNORE INTO mayorKeychain (dt,nodeId,nodeTipus,publicKey,url,valid) 
VALUES (CURDATE(), '09862967','controller','-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDbMFUWy0Juy/7uHROaLOMPSHMI
Vw/jSmEAKW6lCeIOH2oUVsAQkgsZCFiKqQhH3WbtLrAzSmOA7+rEU3RtgXtPZpHN
2UUPQqoHWoMOkumfIS5oM0sQgSQ738TC0X9yxZlNqZtdpdCa0zjVnGLxqVVhS3KD
+O8uuA7jQwczlSqWJwIDAQAB
-----END PUBLIC KEY-----','https://www.mayor.hu',1);

CREATE TABLE `accountRecovery` (
  `recoveryId` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `policy` enum('private','parent','public') COLLATE utf8_hungarian_ci NOT NULL,
  `userAccount` varchar(32) COLLATE utf8_hungarian_ci NOT NULL,
  `selector` char(16) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `token` char(64) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `expires` datetime DEFAULT NULL,
  PRIMARY KEY (`recoveryId`),
  KEY `selector` (`selector`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `facebookConnect` (
  `fbUserId` bigint(20) NOT NULL,
  `userAccount` varchar(64) COLLATE utf8_hungarian_ci NOT NULL,
  `policy` enum('public','parent','private') COLLATE utf8_hungarian_ci DEFAULT NULL,
  `fbUserCn` varchar(64) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `fbUserEmail` varchar(64) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `studyId` varchar(11) COLLATE utf8_hungarian_ci DEFAULT NULL,
  UNIQUE KEY `fbUserId` (`fbUserId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `googleConnect` (
  `googleSub` varchar(32) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `userAccount` varchar(64) COLLATE utf8_hungarian_ci NOT NULL,
  `policy` enum('public','parent','private') COLLATE utf8_hungarian_ci DEFAULT NULL,
  `googleUserCn` varchar(64) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `googleUserEmail` varchar(64) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `studyId` varchar(12) COLLATE utf8_hungarian_ci DEFAULT NULL,
  UNIQUE KEY `googleSub` (`googleSub`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE loginLog_a (
  logId int(10) unsigned NOT NULL,
  policy varchar(10) NOT NULL default 'private',
  userAccount varchar(50) default NULL,
  ip varchar(15) default NULL,
  dt datetime default NULL,
  flag tinyint(3) unsigned default NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_hungarian_ci;

CREATE TABLE `eduroam` (
    `userAccount` varchar(64) COLLATE utf8_hungarian_ci NOT NULL,
    `policy` enum('public','parent','private') COLLATE utf8_hungarian_ci DEFAULT NULL,
    `eduroamUID` varchar(32)  COLLATE utf8_hungarian_ci NOT NULL,
    `eduroamPASSWORD` varchar(128) COLLATE utf8_hungarian_ci NOT NULL,
    `eduroamAFFILIATION` ENUM ('staff','faculty','student','') DEFAULT '',
    `eduroamDOMAIN` varchar(128) COLLATE utf8_hungarian_ci NOT NULL,
    `modositasDt` datetime DEFAULT NULL,
  UNIQUE KEY `eduroamUID` (`eduroamUID`),
  UNIQUE KEY `userAccount` (`userAccount`,`policy`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `authToken` (
  `tokenId` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `policy` enum('private','parent','public') COLLATE utf8_hungarian_ci NOT NULL,
  `userAccount` varchar(32) COLLATE utf8_hungarian_ci NOT NULL,
  `userCn` varchar(64) COLLATE utf8_hungarian_ci NOT NULL,
  `studyId` bigint(20) unsigned DEFAULT NULL,
  `selector` char(16) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `token` char(64) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `expires` datetime DEFAULT NULL,
  `ipAddress` varchar(64) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `activity` datetime DEFAULT NULL,
  PRIMARY KEY (`tokenId`),
  UNIQUE KEY `selector` (`selector`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;
