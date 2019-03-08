<?php


   function getValaszthatoTankorok($tanev, $szemeszter, $OSZTALYIDK) {

        if ($tanev=='') {
            $tanevAdat = $_TANEV;
        } else {
            $tanevAdat = getTanevAdat($tanev);
        }

        $tanevDbNev = tanevDbNev(__INTEZMENY, $tanev);

        $DT['tolDt'] = $tanevAdat['kezdesDt'];
        $DT['igDt']  = $tanevAdat['zarasDt'] ;

        $tankorBlokkok = getTankorBlokkok($tanev);
	if (is_array($tankorBlokkok) && is_array($tankorBlokkok['idk']))
        foreach ($tankorBlokkok['idk'] as $blokkId => $TB) {
	    for ($j=0; $j<count($TB); $j++) {
		$TID2B[$TB[$j]][] = $blokkId;
	    }
	}

	if (is_array($OSZTALYIDK) && count($OSZTALYIDK)>0) {
	    $W = " AND osztalyId IN (".implode(',', array_fill(0, count($OSZTALYIDK), '%u')).")";
	    $v = mayor_array_join(array($tanev, $szemeszter), $OSZTALYIDK, $OSZTALYIDK, array($tanev,$tanev,$szemeszter));
	} else {
	    $v = array($tanev, $szemeszter, $tanev, $tanev, $szemeszter);
	}
	$q = "SELECT DISTINCT tankorId, targyId, kovetelmeny, min, max, tanev, szemeszter, oraszam, tankorNev 
		FROM tankor LEFT JOIN tankorSzemeszter USING (tankorId) JOIN tankorOsztaly USING (tankorId)  
		WHERE tanev=%u and szemeszter=%u and tankor.felveheto =1".$W." 
		    AND tankorId NOT IN (
			SELECT distinct tankorId FROM tankor LEFT JOIN tankorSzemeszter USING (tankorId) JOIN tankorOsztaly USING (tankorId) 
			WHERE tankor.felveheto =1".$W." AND (tanev<%u OR (tanev=%u AND szemeszter<%u))
		    )
		ORDER BY tankorNev,tankor.tankorId";
	$felvehetoTankorok = db_query($q, array('fv' => 'getValaszthatoTankorok', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => $v));

        for ($i=0; $i<count($felvehetoTankorok); $i++) {
	    $felvehetoTankorok[$i]['blokkIdk'] = $TID2B[$felvehetoTankorok[$i]['tankorId']];
	    $felvehetoTankorok[$i]['letszam'] =  getTankorLetszam($felvehetoTankorok[$i]['tankorId'],$DT);
	    $felvehetoTankorok[$i]['tanarok'] =  getTankorTanaraiByInterval($felvehetoTankorok[$i]['tankorId'], array('tolDt' => $DT['tolDt'], 'igDt' => $DT['igDt'], 'result' => 'nevsor'));
        }
        return $felvehetoTankorok;
    }



   function getValaszthatoTankorokOrig($tanev, $szemeszter, $OSZTALYIDK) {

        if ($tanev=='') {
            $tanevAdat = $_TANEV;
        } else {
            $tanevAdat = getTanevAdat($tanev);
        }

        $tanevDbNev = tanevDbNev(__INTEZMENY, $tanev);

        $DT['tolDt'] = $tanevAdat['kezdesDt'];
        $DT['igDt']  = $tanevAdat['zarasDt'] ;

        $tankorBlokkok = getTankorBlokkok($tanev);
	if (is_array($tankorBlokkok) && is_array($tankorBlokkok['idk']))
        foreach ($tankorBlokkok['idk'] as $blokkId => $TB) {
	    for ($j=0; $j<count($TB); $j++) {
		$TID2B[$TB[$j]][] = $blokkId;
	    }
	}

	if (is_array($OSZTALYIDK) && count($OSZTALYIDK)>0) {
	    $W = " AND osztalyId IN (".implode(',', array_fill(0, count($OSZTALYIDK), '%u')).")";
	    $v = mayor_array_join(array($tanev, $szemeszter), $OSZTALYIDK, $OSZTALYIDK, array($tanev));
	} else {
	    $v = array($tanev, $szemeszter, $tanev);
	}
	$q = "SELECT DISTINCT tankorId, targyId, kovetelmeny, min, max, tanev, szemeszter, oraszam, tankorNev 
		FROM tankor LEFT JOIN tankorSzemeszter USING (tankorId) JOIN tankorOsztaly USING (tankorId)  
		WHERE tanev=%u and szemeszter=%u and tankor.felveheto =1".$W." 
		    AND tankorId NOT IN (
			SELECT distinct tankorId FROM tankor LEFT JOIN tankorSzemeszter USING (tankorId) JOIN tankorOsztaly USING (tankorId) 
			WHERE tankor.felveheto =1".$W." AND tanev<%u
		    )
		ORDER BY tankorNev,tankor.tankorId";
	$felvehetoTankorok = db_query($q, array('fv' => 'getValaszthatoTankorok', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => $v));

        for ($i=0; $i<count($felvehetoTankorok); $i++) {
	    $felvehetoTankorok[$i]['blokkIdk'] = $TID2B[$felvehetoTankorok[$i]['tankorId']];
	    $felvehetoTankorok[$i]['letszam'] =  getTankorLetszam($felvehetoTankorok[$i]['tankorId'],$DT);
	    $felvehetoTankorok[$i]['tanarok'] =  getTankorTanaraiByInterval($felvehetoTankorok[$i]['tankorId'], array('tolDt' => $DT['tolDt'], 'igDt' => $DT['igDt'], 'result' => 'nevsor'));
        }
        return $felvehetoTankorok;
    }


?>
