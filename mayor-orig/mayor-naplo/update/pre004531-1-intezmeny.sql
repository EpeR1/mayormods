DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4531 $$

CREATE PROCEDURE upgrade_database_4531()
BEGIN
SET NAMES utf8 COLLATE utf8_hungarian_ci;

IF NOT EXISTS (SELECT * FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='cimke') THEN
 CREATE TABLE `cimke` (
  `cimkeId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cimkeLeiras` varchar(16) NOT NULL,
  PRIMARY KEY (`cimkeId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;
END IF;


END $$
DELIMITER ;
CALL upgrade_database_4531();
