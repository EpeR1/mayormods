DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4002 $$
CREATE PROCEDURE upgrade_database_4002()
BEGIN

SET NAMES utf8 COLLATE utf8_hungarian_ci;

IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='kerelem' AND COLUMN_NAME='felelosCsoport'
) THEN
alter table kerelem add column felelosCsoport enum('naploadmin','vezetoseg','titkarsag') default 'naploadmin';
update kerelem set felelosCsoport='naploadmin' WHERE felelosCsoport IS NULL OR felelosCsoport=''; 
END IF;

alter table kerelem modify kategoria varchar(32);

IF NOT EXISTS (
    SELECT * FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='kerelemValasz'
) THEN
CREATE TABLE kerelemValasz (
valaszId int unsigned not null PRIMARY KEY auto_increment,
kerelemId int unsigned not null,
valasz text,
valaszDt timestamp DEFAULT CURRENT_TIMESTAMP,
userAccount varchar(32),
CONSTRAINT `kerelemValasz_ibfk_1` FOREIGN KEY (`kerelemId`) REFERENCES `kerelem` (`kerelemId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

END IF;


END $$
DELIMITER ;
CALL upgrade_database_4002();


