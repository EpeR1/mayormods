DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_3760 $$
CREATE PROCEDURE upgrade_database_3760()
BEGIN
IF NOT EXISTS (
    SELECT * FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='mayorKeychain'
) THEN
  CREATE TABLE `mayorKeychain` (
  `dt` date DEFAULT NULL,
  `OMKod` mediumint(8) unsigned zerofill NOT NULL DEFAULT '00000000',
  `publicKey` text COLLATE utf8_hungarian_ci NOT NULL,
  `valid` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `naploUrl` varchar(255) COLLATE utf8_hungarian_ci NOT NULL,
  PRIMARY KEY (`OMKod`,`valid`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;
ELSE
    IF NOT EXISTS (
	SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND COLUMN_NAME='naploUrl'
    ) THEN
	ALTER TABLE mayorKeychain ADD `naploUrl` varchar(255) NOT NULL;
    END IF;
END IF;

END $$
DELIMITER ; $$
CALL upgrade_database_3760();

INSERT IGNORE INTO mayorKeychain (dt,OMKod,publicKey,valid,naploUrl)
VALUES (CURDATE(), '09862967','-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDbMFUWy0Juy/7uHROaLOMPSHMI
Vw/jSmEAKW6lCeIOH2oUVsAQkgsZCFiKqQhH3WbtLrAzSmOA7+rEU3RtgXtPZpHN
2UUPQqoHWoMOkumfIS5oM0sQgSQ738TC0X9yxZlNqZtdpdCa0zjVnGLxqVVhS3KD
+O8uuA7jQwczlSqWJwIDAQAB
-----END PUBLIC KEY-----',1,'https://www.mayor.hu');
