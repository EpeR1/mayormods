DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_3980 $$
CREATE PROCEDURE upgrade_database_3980()
BEGIN

SET NAMES utf8 COLLATE utf8_hungarian_ci;

-- IF EXISTS (
--     SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='osztalyJelleg' AND COLUMN_NAME='erettsegizo'
-- ) THEN
-- END IF;

ALTER TABLE rpcJogosultsag ADD CONSTRAINT `nodeId_login` FOREIGN KEY (`nodeId`) REFERENCES `mayor_login`.`mayorKeychain` (`nodeId`) ON DELETE CASCADE ON UPDATE CASCADE;


END $$
DELIMITER ;
CALL upgrade_database_3980();


