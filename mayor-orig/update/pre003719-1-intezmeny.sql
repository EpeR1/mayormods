DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_3719 $$
CREATE PROCEDURE upgrade_database_3719()
BEGIN
IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='tankorTipus' AND COLUMN_NAME='nevsor'
) THEN
    alter table tankorTipus ADD `nevsor` ENUM('állandó','változtatható') DEFAULT NULL AFTER `jelleg`;
    update tankorTipus SET nevsor='változtatható' WHERE tankorTipusId IN (8,9,10,11);
    update tankorTipus SET nevsor='állandó' WHERE nevsor is null;
END IF;
END $$
DELIMITER ; $$
CALL upgrade_database_3719();
insert ignore into tankorTipus (tankorTipusId,oratervi,rovidNev,leiras,jelenlet,regisztralando,hianyzasBeleszamit,jelleg,nevsor,tankorJel)
VALUES (13,'óratervi','könyvtár','Könyvtári osztályfüggetlen elfoglaltság (nyitva tartás)', 'nem kötelező','nem','nem','osztályfüggetlen','állandó','');
insert ignore into tankorTipus (tankorTipusId,oratervi,rovidNev,leiras,jelenlet,regisztralando,hianyzasBeleszamit,jelleg,nevsor,tankorJel)
VALUES (14,'óratervi','gyakorlat állandó tagokkal','Óratervi (képzési hálóban kötelező) gyakorlat állandó tagokkal', 'kötelező','igen','igen','gyakorlat','állandó','');
