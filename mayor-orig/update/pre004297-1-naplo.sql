DROP FUNCTION IF EXISTS getOraTolTime;
DELIMITER //
 CREATE FUNCTION getOraTolTime(id int(10) unsigned)
 RETURNS TIME DETERMINISTIC
 BEGIN
    DECLARE oraTolTime TIME;

    SELECT DISTINCT tolTime FROM
    (SELECT ora.*,osztalyDiak.osztalyId, osztalyDiak.diakId, %INTEZMENYDB%.csengetesiRend.csengetesiRendTipus,
    tolTime, igTime FROM ora
    LEFT JOIN %INTEZMENYDB%.tankorDiak ON (ora.tankorId = tankorDiak.tankorId AND tankorDiak.beDt<=ora.dt AND (tankorDiak.kiDt IS NULL or tankorDiak.kiDt>=ora.dt))
    LEFT JOIN %INTEZMENYDB%.osztalyDiak ON (tankorDiak.diakId = osztalyDiak.diakId AND tankorDiak.beDt<=ora.dt AND (osztalyDiak.kiDt IS NULL or osztalyDiak.kiDt>=ora.dt))
    LEFT JOIN %INTEZMENYDB%.osztaly ON (osztalyDiak.osztalyId = osztaly.osztalyId)
    LEFT JOIN %INTEZMENYDB%.telephely USING (telephelyId)
    LEFT JOIN %INTEZMENYDB%.csengetesiRend ON (telephely.telephelyId = csengetesiRend.telephelyId AND naplo_vmg_2017.ora.ora=csengetesiRend.ora)
    WHERE oraId = id) AS a
    LEFT JOIN munkatervOsztaly USING (osztalyId)
    LEFT JOIN nap ON (nap.dt=a.dt AND nap.munkatervId=munkatervOsztaly.munkatervId)
    WHERE nap.csengetesiRendTipus = a.csengetesiRendTipus
    LIMIT 1
    INTO oraTolTime;

    RETURN (oraTolTime);
 END
 //
DELIMITER ; //

DROP FUNCTION IF EXISTS getOraIgTime;
DELIMITER //
 CREATE FUNCTION getOraIgTime(id int(10) unsigned)
 RETURNS TIME DETERMINISTIC
 BEGIN
    DECLARE oraIgTime TIME;

    SELECT DISTINCT igTime FROM
    (SELECT ora.*,osztalyDiak.osztalyId, osztalyDiak.diakId, %INTEZMENYDB%.csengetesiRend.csengetesiRendTipus,
    tolTime, igTime FROM ora
    LEFT JOIN %INTEZMENYDB%.tankorDiak ON (ora.tankorId = tankorDiak.tankorId AND tankorDiak.beDt<=ora.dt AND (tankorDiak.kiDt IS NULL or tankorDiak.kiDt>=ora.dt))
    LEFT JOIN %INTEZMENYDB%.osztalyDiak ON (tankorDiak.diakId = osztalyDiak.diakId AND tankorDiak.beDt<=ora.dt AND (osztalyDiak.kiDt IS NULL or osztalyDiak.kiDt>=ora.dt))
    LEFT JOIN %INTEZMENYDB%.osztaly ON (osztalyDiak.osztalyId = osztaly.osztalyId)
    LEFT JOIN %INTEZMENYDB%.telephely USING (telephelyId)
    LEFT JOIN %INTEZMENYDB%.csengetesiRend ON (telephely.telephelyId = csengetesiRend.telephelyId AND naplo_vmg_2017.ora.ora=csengetesiRend.ora)
    WHERE oraId = id) AS a
    LEFT JOIN munkatervOsztaly USING (osztalyId)
    LEFT JOIN nap ON (nap.dt=a.dt AND nap.munkatervId=munkatervOsztaly.munkatervId)
    WHERE nap.csengetesiRendTipus = a.csengetesiRendTipus
    LIMIT 1
    INTO oraIgTime;

    RETURN (oraIgTime);
 END
 //
DELIMITER ; //
