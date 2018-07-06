
DELIMITER //
 DROP FUNCTION IF EXISTS diakNaploSorszam //
 CREATE function diakNaploSorszam ( thisDiakId INT, thisTanev INT, thisOsztalyId INT ) returns INT
 READS SQL DATA
 BEGIN
    DECLARE inKezdesDt,inZarasDt DATE;
    DECLARE a,i INT; -- for loop
    DECLARE b DATE; -- for loop
    DECLARE c DATE;
    DECLARE d INT;
    DECLARE e VARCHAR(255);
    
    DECLARE cur1
        CURSOR FOR
        SELECT diakId,IF(beDt<inKezdesDt,inKezdesDt,beDt) AS tolDt,IF(ifnull(kiDt,inZarasDt)<inZarasDt,kiDt,inZarasDt) AS igDt,
            (SELECT COUNT(*) FROM diakJogviszony AS ds
                WHERE ds.diakId=diak.diakId AND tolDt<dt AND dt<=igDt AND ds.statusz IN ('jogviszonyban van','magántanuló')
            ) AS aktJogviszonyDb,
            (SELECT statusz FROM diakJogviszony AS ds
                WHERE ds.diakId=diak.diakId AND dt<=tolDt ORDER BY dt DESC LIMIT 1
            ) AS elozoStatusz
        FROM osztalyDiak LEFT JOIN diak USING (diakId)
        WHERE osztalyId=thisOsztalyId AND beDt<=inZarasDt AND (kiDt IS NULL OR kiDt>=inKezdesDt)
        HAVING (aktJogviszonyDb>0 or elozoStatusz in ('magántanuló','jogviszonyban van'))
        ORDER BY tolDt, CONCAT_WS(' ',viseltCsaladinev,viseltUtonev) COLLATE utf8_hungarian_ci;

    DECLARE EXIT HANDLER FOR NOT FOUND RETURN NULL;
    SELECT kezdesDt FROM szemeszter WHERE tanev=thisTanev AND szemeszter=1 INTO inKezdesDt;
    SELECT MAX(zarasDt) FROM szemeszter WHERE tanev=thisTanev INTO inZarasDt;

    SET i := 1;
    OPEN cur1;
    lo: LOOP
--        FETCH cur1 INTO a,b;
        FETCH cur1 INTO a,b,c,d,e;
        IF a = thisDiakId THEN
            LEAVE lo;
        END IF;
        SET i := i+1;
    END LOOP;
    CLOSE cur1;
    return i;
 END; //
 DELIMITER ; //

 DELIMITER //
 DROP FUNCTION IF EXISTS diakTorzslapszam //
 CREATE function diakTorzslapszam ( thisDiakId INT, thisOsztalyId INT ) returns INT
 READS SQL DATA
 BEGIN
    DECLARE i,d,n01,n02,n03,n04,n05,n06,n07,n08,n09,n10,n11,n12,n13 INT; -- for loop
    DECLARE error,inKezdoTanev,inVegzoTanev INT;
    DECLARE cur1
        CURSOR FOR
        SELECT diakId,
                ifnull(diakNaploSorszam(diakId,inKezdoTanev,thisOsztalyId),99) as ns01,
                ifnull(diakNaploSorszam(diakId,inKezdoTanev+1,thisOsztalyId),99) as ns02,
                ifnull(diakNaploSorszam(diakId,inKezdoTanev+2,thisOsztalyId),99) as ns03,
                ifnull(diakNaploSorszam(diakId,inKezdoTanev+3,thisOsztalyId),99) as ns04,
                ifnull(diakNaploSorszam(diakId,inKezdoTanev+4,thisOsztalyId),99) as ns05,
                ifnull(diakNaploSorszam(diakId,inKezdoTanev+5,thisOsztalyId),99) as ns06,
                ifnull(diakNaploSorszam(diakId,inKezdoTanev+6,thisOsztalyId),99) as ns07,
                ifnull(diakNaploSorszam(diakId,inKezdoTanev+7,thisOsztalyId),99) as ns08,
                ifnull(diakNaploSorszam(diakId,inKezdoTanev+8,thisOsztalyId),99) as ns09,
                ifnull(diakNaploSorszam(diakId,inKezdoTanev+9,thisOsztalyId),99) as ns10,
                ifnull(diakNaploSorszam(diakId,inKezdoTanev+10,thisOsztalyId),99) as ns11,
                ifnull(diakNaploSorszam(diakId,inKezdoTanev+11,thisOsztalyId),99) as ns12,
                ifnull(diakNaploSorszam(diakId,inKezdoTanev+12,thisOsztalyId),99) as ns13
        FROM osztalyDiak
        WHERE osztalyId=thisOsztalyId
        ORDER BY ns01, ns02, ns03, ns04, ns05, ns06, ns07, ns08, ns09, ns10, ns11, ns12, ns13;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET error := 1; -- Ne csináljon semmit, menjen tovább...
    SELECT kezdoTanev FROM osztaly WHERE osztalyId=thisOsztalyId INTO inKezdoTanev;
    SET i := 1;
    OPEN cur1;
    lo: LOOP
        FETCH cur1 INTO d, n01, n02, n03, n04, n05, n06, n07, n08, n09, n10, n11, n12, n13;
        IF d = thisDiakId THEN
            LEAVE lo;
        END IF;
        SET i := i+1;
    END LOOP;
    CLOSE cur1;

    return i;
 END; //
 DELIMITER ; //


