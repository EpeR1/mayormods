CREATE TABLE IF NOT EXISTS `mayorSsl` (
  `sslId` tinyint(1) unsigned NOT NULL auto_increment,
  `privateKey` text COLLATE utf8_hungarian_ci NOT NULL,
  `publicKey` text COLLATE utf8_hungarian_ci NOT NULL,
  `secret` varchar(40) COLLATE utf8_hungarian_ci DEFAULT NULL,
  PRIMARY KEY (`sslId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE IF NOT EXISTS `mayorKeychain` (
  `dt` date null,
  `OMKod` mediumint(8) unsigned zerofill,
  `publicKey` text COLLATE utf8_hungarian_ci NOT NULL,
  `valid` tinyint(1) unsigned NULL DEFAULT '1',
  PRIMARY KEY (`OMKod`,`valid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

ALTER TABLE `mayorSsl` modify  `sslId` tinyint(1) unsigned NOT NULL auto_increment;

INSERT IGNORE INTO `mayorKeychain` (dt,OMKod,publicKey,valid) VALUES ('2011-04-07',0,'-----BEGIN PUBLIC KEY-----MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDbMFUWy0Juy/7uHROaLOMPSHMIVw/jSmEAKW6lCeIOH2oUVsAQkgsZCFiKqQhH3WbtLrAzSmOA7+rEU3RtgXtPZpHN2UUPQqoHWoMOkumfIS5oM0sQgSQ738TC0X9yxZlNqZtdpdCa0zjVnGLxqVVhS3KD+O8uuA7jQwczlSqWJwIDAQAB-----END PUBLIC KEY-----',1);
