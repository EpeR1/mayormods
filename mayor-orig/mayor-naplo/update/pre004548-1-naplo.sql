DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4548 $$

CREATE PROCEDURE upgrade_database_4548()
BEGIN
SET NAMES utf8 COLLATE utf8_hungarian_ci;

IF NOT EXISTS (
    SELECT * FROM information_schema.statistics WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='csoport' and INDEX_NAME = 'IDX_U_csoport_csoportNev'
) THEN

CREATE TEMPORARY TABLE _csoport AS SELECT csoportNev FROM csoport GROUP BY  csoportNev having count(*)>1;
UPDATE csoport SET csoportNev = CONCAT(SUBSTRING(csoportNev,1,120),' ',csoportId) WHERE csoportNev IN (SELECT csoportNev FROM _csoport);
DROP TABLE _csoport;
ALTER TABLE csoport ADD UNIQUE INDEX IDX_U_csoport_csoportNev (csoportNev);

END IF;

END $$
DELIMITER ;
CALL upgrade_database_4548();
