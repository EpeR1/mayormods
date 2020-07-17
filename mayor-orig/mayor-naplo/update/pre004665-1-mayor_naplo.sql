DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4665 $$
CREATE PROCEDURE upgrade_database_4665()
BEGIN

SET NAMES utf8 COLLATE utf8_hungarian_ci;

INSERT IGNORE INTO `osztalyJelleg` VALUES (201,NULL,'szakközépiskola - 3 szakképzési évfolyam (3)',1,NULL,9,11,NULL,1,'9,10,11','szakmai vizsga');
INSERT IGNORE INTO `osztalyJelleg` VALUES (202,NULL,'szakképző iskola - 3 szakképzési évfolyam (3)',1,NULL,9,11,NULL,1,'9,10,11','szakmai vizsga');

END $$
DELIMITER ;
CALL upgrade_database_4665();
