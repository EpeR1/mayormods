-- javított szkript
DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_3568 $$
CREATE PROCEDURE upgrade_database_3568()
BEGIN

SET NAMES utf8 COLLATE utf8_hungarian_ci;

IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='tanar' AND COLUMN_NAME='statusz'
) THEN
    ALTER TABLE `tanar` ADD `statusz` ENUM('határozatlan idejű','határozott idejű', 'tartósan távol') CHARACTER SET utf8 COLLATE utf8_hungarian_ci;
ELSE
    ALTER TABLE `tanar` MODIFY `statusz` ENUM('határozatlan idejű','határozott idejű', 'tartósan távol') CHARACTER SET utf8 COLLATE utf8_hungarian_ci;
END IF;

IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='tanar' AND COLUMN_NAME='hetiKotelezoOraszam'
) THEN
    ALTER TABLE `tanar` ADD `hetiKotelezoOraszam` decimal(3,1) DEFAULT 0.0;
ELSE
    ALTER TABLE `tanar` MODIFY `hetiKotelezoOraszam` decimal(3,1) DEFAULT 0.0;
END IF;

IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='tanar' AND COLUMN_NAME='megjegyzes'
) THEN
    ALTER TABLE `tanar` ADD `megjegyzes` varchar(255) CHARACTER SET utf8 COLLATE utf8_hungarian_ci DEFAULT NULL;
ELSE
    ALTER TABLE `tanar` MODIFY `megjegyzes` varchar(255) CHARACTER SET utf8 COLLATE utf8_hungarian_ci DEFAULT NULL;
END IF;

END $$
DELIMITER ;
CALL upgrade_database_3568();



