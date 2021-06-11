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
                WHERE ds.diakId=diak.diakId AND tolDt<dt AND dt<=igDt AND ds.statusz IN ('jogviszonyban van','magántanuló','egyéni munkarend')
            ) AS aktJogviszonyDb,
            (SELECT statusz FROM diakJogviszony AS ds
                WHERE ds.diakId=diak.diakId AND dt<=tolDt ORDER BY dt DESC LIMIT 1
            ) AS elozoStatusz
        FROM osztalyDiak LEFT JOIN diak USING (diakId)
        WHERE osztalyId=thisOsztalyId AND beDt<=inZarasDt AND (kiDt IS NULL OR kiDt>=inKezdesDt)
        HAVING (aktJogviszonyDb>0 or elozoStatusz in ('magántanuló','jogviszonyban van','egyéni munkarend'))
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
