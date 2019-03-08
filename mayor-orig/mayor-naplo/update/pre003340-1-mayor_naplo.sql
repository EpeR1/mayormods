DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_3340 $$
CREATE PROCEDURE upgrade_database_3340()
BEGIN
IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='mayor_naplo' and TABLE_NAME='osztalyJelleg' AND COLUMN_NAME='kovOsztalyJellegId'
) THEN
    ALTER TABLE osztalyJelleg ADD COLUMN `kovOsztalyJellegId` tinyint(3) unsigned DEFAULT NULL;
END IF;
UPDATE osztalyJelleg set kovOsztalyJellegId=3 where osztalyJellegId=10;
END $$
CALL upgrade_database_3340();
alter table osztalyJelleg modify `kirOsztalyJellegId` tinyint(3) unsigned;
insert into osztalyJelleg (osztalyJellegId,kirOsztalyJellegId,osztalyJellegNev,erettsegizo,kovOsztalyJellegId) 
    values (11,NULL,'1+6 évfolyamos gimnázium ny.ek.',1,NULL)
    ON DUPLICATE KEY UPDATE kovOsztalyJellegId=NULL;
insert into osztalyJelleg (osztalyJellegId,kirOsztalyJellegId,osztalyJellegNev,erettsegizo,kovOsztalyJellegId) 
    values (12,NULL,'1+6 évfolyamos gimnázium ny.ek. évfolyam végéig',0,11)
    ON DUPLICATE KEY UPDATE kovOsztalyJellegId=11;
insert into osztalyJelleg (osztalyJellegId,kirOsztalyJellegId,osztalyJellegNev,erettsegizo,kovOsztalyJellegId) 
    values (13,NULL,'1+8 évfolyamos gimnázium ny.ek.',1,NULL)
    ON DUPLICATE KEY UPDATE kovOsztalyJellegId=NULL;
insert into osztalyJelleg (osztalyJellegId,kirOsztalyJellegId,osztalyJellegNev,erettsegizo,kovOsztalyJellegId) 
    values (14,NULL,'1+8 évfolyamos gimnázium ny.ek. évfolyam végéig',0,13)
    ON DUPLICATE KEY UPDATE kovOsztalyJellegId=13;
update osztalyJelleg set osztalyJellegNev='1+4 évfolyamos gimnázium ny.ek. évfolyam végéig' where osztalyJellegId=10;
update osztalyJelleg set osztalyJellegNev='1+4 évfolyamos gimnázium ny.ek.' where osztalyJellegId=3;
