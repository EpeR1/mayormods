DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_3966 $$
CREATE PROCEDURE upgrade_database_3966()
BEGIN

SET NAMES utf8 COLLATE utf8_hungarian_ci;

-- IF EXISTS (
--     SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='osztalyJelleg' AND COLUMN_NAME='erettsegizo'
-- ) THEN
-- END IF;

DROP TABLE IF EXISTS rpcJogosultsag;
CREATE TABLE `rpcJogosultsag` (
  `nodeId` mediumint(8) unsigned zerofill NOT NULL DEFAULT '00000000',
  `userAccount` varchar(50) NOT NULL default '',
  `OMKod` mediumint(8) unsigned zerofill NOT NULL DEFAULT '00000000',
  `priv` set('OMKod','Jogosultság','Tantárgyfelosztás') COLLATE utf8_hungarian_ci DEFAULT 'OMKod,Jogosultság',
  PRIMARY KEY (`nodeId`, `userAccount`,`OMKod`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


END $$
DELIMITER ;
CALL upgrade_database_3966();


