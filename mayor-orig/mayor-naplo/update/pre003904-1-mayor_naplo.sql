DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_3904 $$
CREATE PROCEDURE upgrade_database_3904()
BEGIN

SET NAMES utf8 COLLATE utf8_hungarian_ci;

IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='osztalyJelleg' AND COLUMN_NAME='vegzesKovetelmenye'
) THEN
alter table osztalyJelleg add column vegzesKovetelmenye enum('bizonyítvány','érettségi vizsga','szakmai vizsga') default 'bizonyítvány';
update osztalyJelleg set `vegzesKovetelmenye` = 'szakmai vizsga' where `osztalyJellegId` in (83, 84, 85);
END IF;

IF EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='osztalyJelleg' AND COLUMN_NAME='erettsegizo'
) THEN
-- A szakközépiskolák végzőseinek is vizsgaidőszaka van!
update osztalyJelleg set `erettsegizo` = 1 where `osztalyJellegId` in (83, 84, 85);
update osztalyJelleg set `vegzesKovetelmenye` = 'érettségi vizsga' where `erettsegizo` = 1;
update osztalyJelleg set `vegzesKovetelmenye` = 'szakmai vizsga' where `osztalyJellegId` in (83, 84, 85);
alter table osztalyJelleg change `erettsegizo` `_erettsegizo` tinyint(3) unsigned NOT NULL DEFAULT '0';
END IF;

END $$
DELIMITER ;
CALL upgrade_database_3904();


