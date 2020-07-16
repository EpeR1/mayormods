DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4663 $$
CREATE PROCEDURE upgrade_database_4663()
BEGIN

SET NAMES utf8 COLLATE utf8_hungarian_ci;

-- nem nevezzük át!
-- update osztalyJelleg SET osztalyJellegNev=REPLACE(osztalyJellegNev,'szakközépiskola','szakgimnázium') where osztalyJellegNev LIKE '%szakkozepiskola%' AND osztalyJellegEles=1;
-- update osztalyJelleg SET osztalyJellegNev=REPLACE(osztalyJellegNev,'szakiskola','szakközépiskola') where osztalyJellegNev LIKE '%szakiskola%' AND osztalyJellegEles=1;

-- DELETE FROM osztalyJelleg WHERE osztalyJellegId=37;
INSERT IGNORE INTO `osztalyJelleg` VALUES (37,NULL,'4 évfolyamos gimnázium érettségire felkészítő évfolyammal WALDORF (4+1)',1,NULL,9,13,NULL,1,'9,10,11,12,13','érettségi vizsga');

-- DELETE FROM osztalyJelleg WHERE osztalyJellegId IN (171,172,173,174,175,176);
INSERT IGNORE INTO `osztalyJelleg` VALUES
(171,NULL,'technikum (5)',1,NULL,9,NULL,'',1,'9,10,11,12,13','érettségi vizsga'),
(172,NULL,'technikum AJTP előkészítő évfolyammal (1+5)',1,NULL,9,NULL,'AJTP',1,'9/AJTP,9,10,11,12,13','érettségi vizsga'),
(173,NULL,'technikum AJKP előkészítő évfolyammal (1+5)',1,NULL,9,NULL,'AJKP',1,'9/AJKP,9,10,11,12,13','érettségi vizsga'),
(174,NULL,'technikum két tanítási nyelvű előkészítő évfolyammal (1+5)',1,NULL,9,NULL,'Kny',1,'9/Kny,9,10,11,12,13','érettségi vizsga'),
(175,NULL,'technikum nemzetiségi előkészítő évfolyammal (1+5)',1,NULL,9,NULL,'N',1,'9/N,9,10,11,12,13','érettségi vizsga'),
(176,NULL,'technikum nyelvi előkészítő évfolyammal (1+5)',1,NULL,9,NULL,'Ny',1,'9/Ny,9,10,11,12,13','érettségi vizsga');

-- (201,NULL,'szakképző iskola szakképzés (3)',1,NULL,9,NULL,'',1,'9,10,11','szakmai vizsga'),

END $$
DELIMITER ;
CALL upgrade_database_4663();
