DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_3763 $$
CREATE PROCEDURE upgrade_database_3763()
BEGIN
    IF NOT EXISTS (
	SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND COLUMN_NAME='nev'
    ) THEN
	ALTER TABLE mayorKeychain 
	ADD `nev` varchar(128) default NULL,
	ADD `rovidNev` varchar(16) default NULL,
	ADD `cimHelyseg` varchar(32) default NULL,
	ADD `cimIrsz` varchar(8) default NULL,
	ADD `cimKozteruletNev` varchar(32) default NULL,
	ADD `cimKozteruletJelleg` varchar(32) default NULL,
	ADD `cimHazszam` varchar(20) default NULL,
	ADD `telefon` varchar(64) default NULL,
	ADD `fax` varchar(64) default NULL,
	ADD `email` varchar(96) default NULL,
	ADD `honlap` varchar(96) default NULL,
	ADD `kapcsolatNev` varchar(128) default NULL,
	ADD `kapcsolatEmail` varchar(96) default NULL,
	ADD `kapcsolatTelefon` varchar(64) default NULL,
	ADD `mayorTipus` ENUM('intézmény','fenntartó','backup','fejlesztői','boss') DEFAULT 'intézmény';
    END IF;
END $$
DELIMITER ; $$
CALL upgrade_database_3763();

DELETE FROM mayorKeychain WHERE OMKod=9862967;
INSERT IGNORE INTO mayorKeychain (dt,OMKod,publicKey,valid,naploUrl,mayorTipus)
VALUES (CURDATE(), '09862967','-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDbMFUWy0Juy/7uHROaLOMPSHMI
Vw/jSmEAKW6lCeIOH2oUVsAQkgsZCFiKqQhH3WbtLrAzSmOA7+rEU3RtgXtPZpHN
2UUPQqoHWoMOkumfIS5oM0sQgSQ738TC0X9yxZlNqZtdpdCa0zjVnGLxqVVhS3KD
+O8uuA7jQwczlSqWJwIDAQAB
-----END PUBLIC KEY-----',1,'https://www.mayor.hu','boss');
