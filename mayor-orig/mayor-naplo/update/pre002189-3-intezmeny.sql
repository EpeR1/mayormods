drop function if exists diakNaploSorszam;

 DELIMITER //                                                                                                                                                                     
 CREATE function diakNaploSorszam ( thisDiakId INT, thisTanev INT, thisOsztalyId INT ) returns INT
 READS SQL DATA
 BEGIN                                                                                                                                                                            
    DECLARE inKezdesDt,inZarasDt DATE;
    DECLARE a,i INT; -- for loop
    DECLARE b DATE; -- for loop
    DECLARE cur1
	CURSOR FOR 
	SELECT diakId,IF(beDt<inKezdesDt,inKezdesDt,beDt) AS d FROM osztalyDiak LEFT JOIN diak USING (diakId) WHERE osztalyId=thisOsztalyId AND beDt<=inZarasDt AND (kiDt IS NULL OR kiDt>inKezdesDt) ORDER BY d, CONCAT_WS(' ',viseltCsaladinev,viseltUtonev) COLLATE utf8_hungarian_ci;
    DECLARE CONTINUE HANDLER FOR NOT FOUND RETURN NULL;

    SELECT kezdesDt FROM szemeszter WHERE tanev=thisTanev AND szemeszter=1 INTO inKezdesDt;    
    SELECT MAX(zarasDt) FROM szemeszter WHERE tanev=thisTanev INTO inZarasDt;    
    SET i := 1;
    OPEN cur1;
    lo: LOOP
	FETCH cur1 INTO a,b;
	IF a = thisDiakId THEN
	    LEAVE lo;
	END IF;
	SET i := i+1;
    END LOOP;
    CLOSE cur1;
    return i;
 END; //                                                                                                                                                                          
 DELIMITER ; //                                                                                                                                                                   

-- Minta : select (70, 2010, 4);
-- Minta: select osztalyNaploSorszamByDiak(diakId,2010,4) AS ns, diakId FROM osztalyDiak WHERE osztalyId=4 ORDER BY ns;
