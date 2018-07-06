DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_3738 $$
CREATE PROCEDURE upgrade_database_3738()
BEGIN

SET NAMES utf8 COLLATE utf8_hungarian_ci;

CREATE TABLE IF NOT EXISTS accountRecovery (
    `recoveryId` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `policy` enum('private','parent','public') COLLATE utf8_hungarian_ci NOT NULL,
    `userAccount` varchar(32) COLLATE utf8_hungarian_ci NOT NULL,
    `selector` CHAR(16),
    `token` CHAR(64),
    `expires` DATETIME,
    PRIMARY KEY(`recoveryId`),
    KEY(`selector`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

END $$
DELIMITER ;
CALL upgrade_database_3738();




