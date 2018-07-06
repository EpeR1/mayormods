DROP FUNCTION IF EXISTS getNev;

delimiter //
CREATE FUNCTION getNev(id int(10) unsigned, tipus varchar(20))
RETURNS VARCHAR(60) DETERMINISTIC
BEGIN
    DECLARE nev varchar(60) character set utf8;
    DECLARE tnv int(10);
    SELECT SUBSTRING(database(),-4) INTO tnv;

    IF tipus = 'diak' THEN
	SELECT TRIM(CONCAT_WS(' ',viseltNevElotag,viseltCsaladiNev,viseltUtonev)) FROM %INTEZMENYDB%.diak WHERE diakId=id LIMIT 1 INTO nev;
    ELSEIF tipus = 'tanar' THEN
	SELECT TRIM(CONCAT_WS(' ',viseltNevElotag,viseltCsaladiNev,viseltUtonev)) FROM %INTEZMENYDB%.tanar WHERE tanarId=id INTO nev;
    ELSEIF tipus = 'szulo' THEN
	SELECT TRIM(CONCAT_WS(' ',nevElotag,csaladinev,utonev)) FROM %INTEZMENYDB%.szulo WHERE szuloId=id INTO nev;
    ELSEIF tipus = 'tankor' THEN
	SELECT tankorNev FROM %INTEZMENYDB%.tankorSzemeszter WHERE tankorId=id AND tanev=tnv LIMIT 1 INTO nev;
    ELSEIF tipus = 'munkakozosseg' THEN
	SELECT leiras FROM %INTEZMENYDB%.munkakozosseg WHERE mkId=id INTO nev;
    END IF;

    RETURN (nev);
END
//
delimiter ; //
