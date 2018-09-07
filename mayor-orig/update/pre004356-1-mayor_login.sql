DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4356 $$
CREATE PROCEDURE upgrade_database_4356()
BEGIN
    IF NOT EXISTS (
        SELECT * FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='eduroam'
    ) THEN

CREATE TABLE `eduroam` (
    `userAccount` varchar(64) COLLATE utf8_hungarian_ci NOT NULL,
    `policy` enum('public','parent','private') COLLATE utf8_hungarian_ci DEFAULT NULL,
    `eduroamUID` varchar(32)  COLLATE utf8_hungarian_ci NOT NULL,
    `eduroamPASSWORD` varchar(128) COLLATE utf8_hungarian_ci NOT NULL,
    `eduroamAFFILIATION` ENUM ('staff','faculty','student','') DEFAULT '',
    `modositasDt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

END IF;
END $$
DELIMITER ; $$
CALL upgrade_database_4356();
