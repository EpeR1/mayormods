DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4682 $$

CREATE PROCEDURE upgrade_database_4682()
BEGIN
SET NAMES utf8 COLLATE utf8_hungarian_ci;

ALTER TABLE `tanar` MODIFY `viseltNevElotag` varchar(8) COLLATE utf8_hungarian_ci DEFAULT '';
ALTER TABLE `tanar` MODIFY `szuleteskoriNevElotag` varchar(8) COLLATE utf8_hungarian_ci DEFAULT '';
ALTER TABLE `szulo` MODIFY `nevElotag` varchar(8) COLLATE utf8_hungarian_ci DEFAULT '';
ALTER TABLE `szulo` MODIFY `szuleteskoriNevElotag` varchar(8) COLLATE utf8_hungarian_ci DEFAULT '';
ALTER TABLE `diak` MODIFY `viseltNevElotag` varchar(8) COLLATE utf8_hungarian_ci DEFAULT '';
ALTER TABLE `diak` MODIFY `szuleteskoriNevElotag` varchar(8) COLLATE utf8_hungarian_ci DEFAULT '';

END $$
DELIMITER ;
CALL upgrade_database_4682();
