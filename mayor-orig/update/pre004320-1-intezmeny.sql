DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4320 $$

CREATE PROCEDURE upgrade_database_4320()
BEGIN
SET NAMES utf8 COLLATE utf8_hungarian_ci;

IF NOT EXISTS (SELECT * FROM information_schema.statistics WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='diak' AND INDEX_NAME='diak_ibfk_6') THEN
    ALTER TABLE diak ADD CONSTRAINT `diak_ibfk_6` FOREIGN KEY (`neveloId`) REFERENCES `szulo` (`szuloId`) ON DELETE SET NULL ON UPDATE SET NULL;
END IF;

IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='diak' AND COLUMN_NAME='beiratoId'
) THEN
    ALTER TABLE `diak` ADD `beiratoId` int(10) unsigned DEFAULT NULL AFTER neveloId;
END IF;

IF NOT EXISTS (SELECT * FROM information_schema.statistics WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='diak' AND INDEX_NAME='diak_ibfk_7') THEN
    ALTER TABLE diak ADD CONSTRAINT `diak_ibfk_7` FOREIGN KEY (`beiratoId`) REFERENCES `szulo` (`szuloId`) ON DELETE SET NULL ON UPDATE SET NULL;
END IF;

END $$
DELIMITER ;
CALL upgrade_database_4320();
