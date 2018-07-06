DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_3517 $$
CREATE PROCEDURE upgrade_database_3517()
BEGIN
IF NOT EXISTS (
    SELECT * FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='diakTorzslapszam'
) THEN
    CREATE TABLE `diakTorzslapszam` (
  `osztalyId` int(10) unsigned NOT NULL,
  `diakId` int(10) unsigned NOT NULL,
  `torzslapszam` tinyint unsigned NOT NULL,
  PRIMARY KEY (`osztalyId`,`diakId`),
  KEY `diakTorzslapszam_ibfk_2` (`diakId`),
  CONSTRAINT `diakTorzslapszam_ibfk_1` FOREIGN KEY (`osztalyId`) REFERENCES `osztaly` (`osztalyId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `diakTorzslapszam_ibfk_2` FOREIGN KEY (`diakId`) REFERENCES `diak` (`diakId`) ON DELETE CASCADE ON UPDATE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;
    -- A névsor miatt az üres mező NULL kell legyen!
    update diak set viseltNevElotag = NULL where viseltNevElotag = '';

    -- A törzslapszámok feltöltése
    set @oszt=0; 
    set @sz=0; 
    insert ignore into diakTorzslapszam
    select osztalyId, diakId, sorsz from (
    select 
    @sz:=if(@oszt=osztalyId,@sz:=@sz+1,1) as sorsz, 
    @oszt:=osztalyId as o, 
    osztalyId, diakId, sort, diakNev 
    from (
	select 
	    osztalyId, diakId, 
	    if (month(min(beDt))>8 or month(min(beDt))<6 or (month(min(beDt))=6 and day(min(beDt))<16), min(beDt), date_format(min(beDt),'%Y-09-01')) as sort, 
	    concat_ws(' ',viseltNevElotag, viseltCsaladinev, viseltUtonev) as diakNev 
	from osztalyDiak left join diak using (diakId) WHERE diak.diakId IS NOT NULL
	group by osztalyId, diakId 
	order by osztalyId, sort, diakNev
    ) as t
    ) as k;
    -- where diakId=... and osztalyId=...;
END IF;
END $$
DELIMITER ; $$
CALL upgrade_database_3517();
