DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4789 $$

CREATE PROCEDURE upgrade_database_4789()
BEGIN
SET NAMES utf8 COLLATE utf8_hungarian_ci;

-- https://tasz.hu/cikkek/igy-vedd-meg-a-gyermeked-az-allamositastol

ALTER TABLE `diakJogviszony` MODIFY `statusz` enum('felvételt nyert','jogviszonyban van','magántanuló','egyéni munkarend','vendégtanuló','jogviszonya felfüggesztve','jogviszonya lezárva') COLLATE utf8_hungarian_ci NOT NULL;
ALTER TABLE `diak` MODIFY `statusz` enum('felvételt nyert','jogviszonyban van','magántanuló','egyéni munkarend','vendégtanuló','jogviszonya felfüggesztve','jogviszonya lezárva') COLLATE utf8_hungarian_ci NOT NULL;

END $$
DELIMITER ;
CALL upgrade_database_4789();
