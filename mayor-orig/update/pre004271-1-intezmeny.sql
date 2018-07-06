DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4271 $$

CREATE PROCEDURE upgrade_database_4271()
BEGIN
SET NAMES utf8 COLLATE utf8_hungarian_ci;

IF EXISTS (SELECT * FROM information_schema.statistics WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='hirnokFeliratkozas' AND INDEX_NAME='K_hf_mix') THEN
    ALTER TABLE hirnokFeliratkozas DROP KEY `K_hf_mix`;
END IF;
ALTER TABLE hirnokFeliratkozas ADD UNIQUE KEY `K_hf_mix` (`userAccount`,`policy`,`naploId`,`naploTipus`,`email`);
IF EXISTS (SELECT * FROM information_schema.statistics WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='hirnokFeliratkozas' AND INDEX_NAME='K_hf_up') THEN
    ALTER TABLE hirnokFeliratkozas DROP KEY `K_hf_up`;
END IF;
ALTER TABLE hirnokFeliratkozas ADD KEY `K_hf_up` (`userAccount`,`policy`);

END $$
DELIMITER ;
CALL upgrade_database_4271();
