<?php

    function getOraAdatById($oraId, $tanev = __TANEV, $olr = null) {

	$tanev = readVariable($tanev, 'numeric unsigned', __TANEV);

        if (!isset($tanev))  return false;

        if ($oraId != '') {

            $q = "SELECT DISTINCT oraId,
                                 dt,
                                 ora,
                                 ki,
                                 kit,
                                 ora.tankorId AS tankorId,
                                 teremId,
                                 ".__INTEZMENYDBNEV.".terem.leiras AS teremLeiras,
                                 ora.leiras AS leiras,
                                 ora.tipus AS tipus,
                                 eredet,
                                 TRIM(CONCAT_WS(' ', t1.viseltNevElotag, t1.viseltCsaladiNev, t1.viseltUtonev)) AS kiCn,
                                 TRIM(CONCAT_WS(' ', t2.viseltNevElotag, t2.viseltCsaladiNev, t2.viseltUtonev)) AS kitCn,
                                 tankorNev,
				 feladatTipusId,
				 munkaido,
				 hazifeladatId, 
				hazifeladatLeiras,
				hazifeladatFeltoltesEngedely,
				hazifeladatHataridoDt,
				cimkeId, cimkeLeiras
                            FROM `%s`.ora
			    LEFT JOIN `%s`.oraHazifeladat USING (oraId)
			    LEFT JOIN `%s`.oraCimke USING (oraId)
                            LEFT JOIN ".__INTEZMENYDBNEV.".cimke USING (cimkeId)
                            LEFT JOIN ".__INTEZMENYDBNEV.".tankorSzemeszter USING (tankorId)
                            LEFT JOIN ".__INTEZMENYDBNEV.".tanar AS t1 ON ki=t1.tanarId
                            LEFT JOIN ".__INTEZMENYDBNEV.".tanar AS t2 ON kit=t2.tanarId
                    	    LEFT JOIN ".__INTEZMENYDBNEV.".feladatTipus USING (feladatTipusId)
                    	    LEFT JOIN ".__INTEZMENYDBNEV.".terem USING (teremId)
                            WHERE oraId=%u AND (tanev=%u OR feladatTipusId IS NOT NULL)";
	    $v = array(tanevDbNev(__INTEZMENY, $tanev),tanevDbNev(__INTEZMENY, $tanev), tanevDbNev(__INTEZMENY, $tanev), $oraId, $tanev);
	    return db_query($q, array('fv' => 'getOraAdatById', 'modul' => 'naplo_intezmeny', 'result' => 'record', 'values' => $v), $olr);

        } else {
            // nincs id
            return false;
        }
    }

    function getOraAdatByTankor($tankorId, $olr = '') {

        if ($tankorId != '') {

                $q = "SELECT DISTINCT oraId,
                                 dt,
                                 ora,
                                 ki,
                                 kit,
                                 ora.tankorId AS tankorId,
                                 teremId,
                                 ora.leiras AS leiras,
                                 ora.tipus AS tipus,
                                 eredet,
                                 TRIM(CONCAT_WS(' ', t1.viseltNevElotag, t1.viseltCsaladiNev, t1.viseltUtonev)) AS kiCn,
                                 TRIM(CONCAT_WS(' ', t2.viseltNevElotag, t2.viseltCsaladiNev, t2.viseltUtonev)) AS kitCn,
                                 tankorNev,
				 feladatTipusId,
				 munkaido
                            FROM ".__TANEVDBNEV.".ora
                            LEFT JOIN ".__INTEZMENYDBNEV.".tankorSzemeszter USING (tankorId)
                            LEFT JOIN ".__INTEZMENYDBNEV.".tanar AS t1 ON ki=t1.tanarId
                            LEFT JOIN ".__INTEZMENYDBNEV.".tanar AS t2 ON kit=t2.tanarId
                    	    LEFT JOIN ".__INTEZMENYDBNEV.".feladatTipus USING (feladatTipusId)
                            WHERE tankorId=%u AND tipus NOT LIKE 'elmarad%%' AND (tanev=".__TANEV." OR feladatTipusId IS NOT NULL)
			    ORDER BY dt DESC,ora DESC,tankorId";
		return db_query($q, array('fv' => 'getOraAdatByTankor', 'modul' => 'naplo', 'result' => 'indexed', 'values' => array($tankorId)), $olr);

        } else {
            // nincs id
            return false;
        }
    }

    function getHelyettesitettOra($tolDt, $igDt) {

                $q = "SELECT DISTINCT oraId,
                                 dt,
                                 ora,
                                 ki,
                                 kit,
                                 ora.tankorId AS tankorId,
                                 teremId,
                                 ora.leiras AS leiras,
                                 ora.tipus AS tipus,
                                 eredet,
                                 TRIM(CONCAT_WS(' ', t1.viseltNevElotag, t1.viseltCsaladiNev, t1.viseltUtonev)) AS kiCn,
                                 TRIM(CONCAT_WS(' ', t2.viseltNevElotag, t2.viseltCsaladiNev, t2.viseltUtonev)) AS kitCn,
                                 tankorNev,
				 munkaido
                            FROM ".__TANEVDBNEV.".ora
                            LEFT JOIN ".__INTEZMENYDBNEV.".tankorSzemeszter USING (tankorId)
                            LEFT JOIN ".__INTEZMENYDBNEV.".tanar AS t1 ON ki=t1.tanarId
                            LEFT JOIN ".__INTEZMENYDBNEV.".tanar AS t2 ON kit=t2.tanarId
                            WHERE (
				tipus IN ('helyettesítés','felügyelet','összevonás','elmarad','elmarad máskor','normál máskor')
				or eredet='plusz'
			    )
			    AND tanev=".__TANEV." AND '%s' <= dt AND dt <= '%s'
			    ORDER BY dt, ora, ki";
		return db_query($q, array('fv' => 'getHelyettesítettOrar', 'modul' => 'naplo', 'result' => 'indexed', 'values' => array($tolDt, $igDt)), $olr);

    }


/*
 * Adott nap adott órájában mely termek szabadok - esetleg megadva, hogy kinek a számára: ilyenkor
 * az ő általa használt termek is benne maradnak a listában...
 */
    function getSzabadTermek($PARAM = array(), $olr = '') {

        if (isset($PARAM['dt']) && $PARAM['dt'] != '') $dt = $PARAM['dt'];
        else $dt = date('Y-m-d');
        if (isset($PARAM['ora']) && $PARAM['ora'] !== '') $ora = $PARAM['ora'];
        else $ora = 1;
        if (isset($PARAM['ki']) && $PARAM['ki'] != '')
            $q = "SELECT ".__INTEZMENYDBNEV.".terem.teremId AS teremId,
                        ".__INTEZMENYDBNEV.".terem.leiras AS leiras,
                        ".__INTEZMENYDBNEV.".terem.ferohely AS ferohely,
                        ".__INTEZMENYDBNEV.".terem.tipus AS tipus
                    FROM ".__INTEZMENYDBNEV.".terem LEFT JOIN ora
                    ON ora.teremId=".__INTEZMENYDBNEV.".terem.teremId
                        AND dt='%s'
                        AND ora=%u
                        AND ora.tipus NOT LIKE 'elmarad%%'
                        AND ki != %u
                    WHERE ora.eredet IS NULL ORDER BY teremId";
        else
            $q = "SELECT ".__INTEZMENYDBNEV.".terem.teremId AS teremId,
                        ".__INTEZMENYDBNEV.".terem.leiras AS leiras,
                        ".__INTEZMENYDBNEV.".terem.ferohely AS ferohely,
                        ".__INTEZMENYDBNEV.".terem.tipus AS tipus
                    FROM ".__INTEZMENYDBNEV.".terem LEFT JOIN ora
                    ON ora.teremId=".__INTEZMENYDBNEV.".terem.teremId
                        AND dt='%s'
                        AND ora=%u
                        AND ora.tipus NOT LIKE 'elmarad%%'
                    WHERE ora.eredet IS NULL ORDER BY teremId";
	$v = array($dt, $ora, $PARAM['ki']);
        return db_query($q, array('fv' => 'getSzabadTermek', 'modul' => 'naplo', 'result' => 'indexed', 'values' => $v), $olr);
    }


    function getTanarNapiOrak($tanarId, $dt='', $olr = null) {
	if ($dt=='') $dt = date('Y-m-d');
	// --TODO kitalálhatnánk, hogy az adott dátum melyik szemeszterben van!
	$q = "SELECT DISTINCT oraId, ora, ki, kit, ora.tankorId, ora.tipus AS tipus, eredet, feladatTipusId,  munkaido, tankorNev, teremId, terem.leiras AS teremLeiras, oralatogatasId, megjegyzes, ora.leiras
		FROM ora LEFT JOIN ".__INTEZMENYDBNEV.".tankorSzemeszter ON (ora.tankorId=tankorSzemeszter.tankorId AND tanev=%u)
		LEFT JOIN ".__INTEZMENYDBNEV.".terem USING (teremId)
		LEFT JOIN oraLatogatas USING (oraId)
		WHERE ki=%u AND dt='%s' ORDER BY ora";
	$v = array(__TANEV,$tanarId, $dt);
	return db_query($q, array('debug'=>false,'fv' => 'getTanarNapiOrak', 'modul' => 'naplo', 'result' => 'multiassoc', 'keyfield' => 'ora', 'values' => $v), $olr);
    }

    function getOsztalyNapiOrak($osztalyId, $dt, $olr = null) {

	$q = "SELECT DISTINCT oraId, ora, ki, kit, ora.tankorId, ora.tipus AS tipus, eredet, feladatTipusId,  munkaido, tankorNev, teremId, terem.leiras AS teremLeiras, oralatogatasId, megjegyzes, ora.leiras
		FROM ora
		    LEFT JOIN ".__INTEZMENYDBNEV.".tankorSzemeszter USING (tankorId)
		    LEFT JOIN ".__INTEZMENYDBNEV.".tankorOsztaly USING (tankorId)
		    LEFT JOIN ".__INTEZMENYDBNEV.".terem USING (teremId)
		    LEFT JOIN oraLatogatas USING (oraId)
		WHERE tanev=".__TANEV." AND osztalyId=%u AND dt='%s' 
		AND ora.tipus IN ('normál','normál máskor','helyettesítés','felügyelet','összevonás') ORDER BY ora";
	$v = array($osztalyId, $dt);
	return db_query($q, array('fv' => 'getOsztalyNapiOrak', 'modul' => 'naplo', 'result' => 'multiassoc', 'keyfield' => 'ora', 'values' => $v), $olr);
    }


    function tanarLukasOrajaE($tanarId, $dt, $ora, $olr = null) {

	$q = "SELECT COUNT(oraId) FROM ora WHERE dt='%s' AND ora=%u AND ki=%u AND tipus NOT LIKE 'elmarad%%'";
	$v = array($dt, $ora, $tanarId);
	$num = db_query($q, array('fv' => 'tanarLukasOrajaE', 'modul' => 'naplo', 'result' => 'value', 'values' => $v), $olr);
	return ($num == 0);

    }

    function tankorTagokLukasOrajaE($tankorId, $dt, $ora, $csereTankorId = '') {

	$nap = date('w', strtotime($dt)); if ($nap == 0) $nap=7;
	$Diakok = getTankorDiakjaiByInterval($tankorId, __TANEV, $dt, $dt);
	if (count($Diakok['idk']) == 0) {
	    // Nincsenek tagjai a tankörnek - év elején bizony előfordul...
	    return true;
	}
	$lukasOra = true;
	for ($i=0; $i<count($Diakok['idk']); $i++) {
	    $_diakId = $Diakok['idk'][$i];
	    // tankörök, amik alól az adott időpontban fel van mentve - megadjuk az órát és napot - a függvény beleveszi az "ora IS NULL, nap IS NULL" eseteket is
	    $_FMTANKOROK = getTankorDiakFelmentes($_diakId, __TANEV, array('csakId'=>true,'tolDt' => $dt, 'igDt' => $dt, 'nap'=> $nap, 'ora'=>$ora));
	    // a diák összes tanköre
	    $_TANKOROK = getTankorIdsByDiakIds(array($_diakId), array('tanev'=>__TANEV,'tolDt'=>$dt,'igDt'=>$dt));
	    // A diák adott időpontban kötelező tankörei
	    if (is_array($_FMTANKOROK)) $tankorIds = array_diff($_TANKOROK, $_FMTANKOROK);
	    else $tankorIds = $_TANKOROK;

	    if (is_array($tankorIds) && count($tankorIds)>0) {
		$q = "SELECT COUNT(oraId) AS db FROM ora 
		    LEFT JOIN ".__INTEZMENYDBNEV.".tankor USING (tankorId)
		    LEFT JOIN ".__INTEZMENYDBNEV.".tankorTipus USING (tankorTipusId)
		    WHERE tankorTipus.jelenlet='kötelező' 
		    AND ora.dt='%s' AND ora.ora=%u AND ora.tipus NOT LIKE 'elmarad%%' AND ora.tankorId IN (".implode(',', array_fill(0, count($tankorIds), '%u')).")";

		$v = mayor_array_join(array($dt, $ora), $tankorIds);
		if ($csereTankorId != '') {
		    $q .= " AND tankorId != %u";
		    array_push($v, $csereTankorId);
		}
		$db = db_query($q, array('fv' => 'tankorTagokLukasOrajaE/diakId='.$_diakId, 'modul' => 'naplo', 'result' => 'value', 'values' => $v));
		if ($db > 0) { // ha van ütközés, akkor próbáljunk informatívak lenni
		    $q = "SELECT DISTINCT tankorId FROM ora WHERE dt='%s' AND ora=%u AND tipus NOT LIKE 'elmarad%%' 
			    AND tankorId IN (".implode(',', array_fill(0, count($tankorIds), '%u')).")";
		    if ($csereTankorId != '') $q .= " AND tankorId != %u";
		    $r = db_query($q, array('fv' => 'tankorTagokLukasOrajaE', 'modul' => 'naplo', 'result' => 'idonly', 'values' => $v) );
		    if (is_array($r) && count($r)>0) {
			$_diakAdat = getDiakAdatById($_diakId);
			$_SESSION['alert'][] = 'message:foglalt_diak:'.$_diakAdat['diakNev'].' ('.$_diakId.'):tankörök '.implode(',',$r).':időpont '.$dt.' '.$ora.'. óra';
		    }
		}
	    } else {
		$db = 0;
	    }

	    $lukasOra = $lukasOra && ($db == 0);

	}

	return $lukasOra;

    }


    function getNapok($Param = array('tanev' => __TANEV, 'tolDt' => '', 'igDt' => '', 'napokSzama' => '', 'tipus' => '', 'munkatervId' => null), $olr = null) {

	if (isset($Param['tanev']) && $Param['tanev'] != '') $tanev = $Param['tanev'];
	if (isset($Param['tolDt']) && $Param['tolDt'] != '') $tolDt = $Param['tolDt'];
	if (isset($Param['igDt']) && $Param['igDt'] != '') $igDt = $Param['igDt'];
	if (isset($Param['napokSzama']) && $Param['napokSzama'] != '') $napokSzama = $Param['napokSzama'];

	initTolIgDt($tanev, $tolDt, $igDt);
	$v = array($tolDt, $igDt);

	if (is_array($Param['tipus']) && count($Param['tipus']) > 0) {
	    $tipusFeltetel = " AND tipus IN ('" . implode("','", array_fill(0, count($Param['tipus']), '%s')) . "') ";
	    $v = mayor_array_join($v, $Param['tipus']);
	} else $tipusFeltetel = '';
	if (isset($Param['munkatervId'])) {
	    $mtFeltetel = " AND munkatervId=%u ";
	    array_push($v, $Param['munkatervId']);
	} else $mtFeltetel = '';
	$orderBy = 'ORDER BY dt';
	if (isset($napokSzama)) {
	    if (isset($igDt)) $orderBy = 'ORDER BY dt DESC';
	    $limit = "LIMIT %u";
	    array_push($v, $napokSzama);
	}

	$q = "SELECT DISTINCT dt FROM nap
		WHERE '%s' <= dt AND dt <= '%s' $tipusFeltetel $mtFeltetel $orderBy $limit";
	return db_query($q, array('fv' => 'getNapok', 'modul' => 'naplo', 'result' => 'idonly', 'values' => $v), $olr);

    }

    function getNapAdat($dt, $olr = '') {

	$q = "SELECT * FROM nap WHERE dt='%s' ORDER BY munkatervId";
	$ret = db_query($q, array('fv' => 'getNapAdat', 'modul' => 'naplo', 'result' => 'indexed', 'values' => array($dt)), $olr);
	return $ret;

    }

/* Nem használt függvény - most már munkaterv függő
    function getNapTipus($dt, $munkatervId = 1) {

        $q = "SELECT tipus FROM nap WHERE dt='%s' AND munkatervId=%u";
        return db_query($q, array('fv' => 'getNapTipus', 'modul' => 'naplo', 'result' => 'value', 'values' => array($dt, $munkatervId)));

    }
*/ 
    function getTanevNapjai($munkatervId = 1, $olr = null) {

	$q = "SELECT * FROM nap WHERE munkatervId=%u ORDER BY dt";
	return db_query($q, array('fv' => 'getTanevNapjai', 'modul' => 'naplo', 'result' => 'indexed', 'values' => array($munkatervId)), $olr);

    }

    function getHonapNapjai($ho, $munkatervId = 1, $olr = null) {
    /*
	$munkatervId lehet tömb is! (pl. egy tanuló több osztályba is tartozik)

	Ekkor a függvény lekérdezi az adott munkatervId-khez tartozó napokat, rendezi őket, hogy minden nap elöl legyenek
	a tanítási, speciális tanítási, majd a tanítás nélküli munkanapok, végül a tanítási szünetek, majd ezekből az elsőt
	- tehát a "legszigorúbbat" - adja csak vissza az adott napra.
    */

	if (is_array($munkatervId) && count($munkatervId)==0) $munkatervId=1;
	if (is_array($munkatervId)) {
	    $q = "SELECT * FROM nap WHERE month(dt)=%u AND munkatervId in (".implode(",", array_fill(0, count($munkatervId), '%u')).") 
		    ORDER BY dt,
		    CASE tipus WHEN 'tanítási nap' THEN 1 WHEN 'speciális tanítási nap' THEN 2 WHEN 'tanítás nélküli munkanap' THEN 3 ELSE 4 END";

	    $r = db_query($q, array('fv' => 'getHonapNapjai', 'modul' => 'naplo', 'result' => 'indexed', 'values' => mayor_array_join(array($ho), $munkatervId)), $olr);
	    $elozoDt = ''; $ret = array();
	    // Az adott napi munkatervek közül csak egyet adjunk vissza - a legszigorúbbat
	    for ($i = 0; $i < count($r); $i++) {
		if ($elozoDt <> $r[$i]['dt']) $ret[] = $r[$i];
		$elozoDt = $r[$i]['dt'];
	    }
	} else {
	    $q = "SELECT * FROM nap WHERE month(dt)=%u AND munkatervId=%u ORDER BY dt";
	    $ret = db_query($q, array('fv' => 'getHonapNapjai', 'modul' => 'naplo', 'result' => 'indexed', 'values' => array($ho, $munkatervId)), $olr);
	}
	return $ret;

    }

// -- korábbi haladasi.php-ből...

    function getTanarOrak($tanarId, $SET = array('tolDt'=>'', 'igDt'=>'', 'ora'=> '', 'result' => 'indexed', 'tipus' => null)) {
    
	if ($SET['csakId'] === true || $SET['result'] == 'csakId') $SET['result'] = 'idonly';

	$tolDt = readVariable($SET['tolDt'], 'datetime', date('Y-m-d'));
	$igDt  = readVariable($SET['igDt'],  'datetime', $tolDt);

	if ($SET['ora']!='') { // akkor egyetlen óraid adatai a kérdés!
	    $WHERE = ' AND ora=%u';
	    $v = array($SET['ora']);
	} else {
	    $WHERE = '';
	    $v = array();
	}
	
	if (is_array($SET['tipus']) && count($SET['tipus']) > 0) {
	    $WHERE .= " AND tipus IN ('".implode("','", array_fill(0, count($SET['tipus']), '%s'))."')";
	    $v = mayor_array_join($v, $SET['tipus']);
	}
	
	if ($SET['result'] === 'idonly') {
	    $q = "SELECT oraId FROM ora WHERE dt>='%s' and dt<='%s' AND ki=%u $WHERE ORDER BY dt,ora";
	    array_unshift($v, $tolDt, $igDt, $tanarId);
	    $RESULT = db_query($q, array('modul' => 'naplo', 'fv' => 'getTanarOrak', 'result' => 'idonly', 'values' => $v));			 
	} else {
	    $q = "SELECT * FROM ora 
LEFT JOIN oraHazifeladat USING (oraId)
LEFT JOIN oraCimke USING (oraId)
LEFT JOIN ".__INTEZMENYDBNEV.".cimke USING (cimkeId)
WHERE dt>='%s' and dt<='%s' AND (ki=%u OR kit=%u) $WHERE ORDER BY dt,ora";
	    array_unshift($v, $tolDt, $igDt, $tanarId, $tanarId);
	    if ($SET['result']=='assoc') 
		$RESULT = db_query($q, array('modul' => 'naplo', 'fv' => 'getTanarOrak', 'keyfield' => 'ora', 'result' => 'assoc', 'values' => $v));
	    else 
		$RESULT = db_query($q, array('modul' => 'naplo', 'fv' => 'getTanarOrak', 'result' => 'indexed', 'values' => $v));
	    if ($SET['result']=='likeOrarend') {

		for ($i = 0; $i < count($RESULT); $i++) {

		    $_put = $RESULT[$i];
		    $_put['oo'] = false;
		    $RE['orak'][$RESULT[$i]['dt']][$RESULT[$i]['ora']][$RESULT[$i]['tankorId']] = $_put;
		    if (!@in_array($RESULT[$i]['tankorId'], $RE['tankorok'])) $RE['tankorok'][] = $RESULT[$i]['tankorId'];

		}
		$RESULT = $RE;
	    }
	}
	return $RESULT;
    }

    function getOrak($TANKORIDK, $SET=array('tolDt'=>'','igDt'=>'', 'result'=>'likeOrarend', 'elmaradokNelkul'=>false, 'diakId'=>null)) {

	/* FIGYELEM! A függvény feltételezi, hogy az átadott tankoridkben az adott intervallumon helyes adatok szerepelnek!
	    -- problémát okozhat, ha hosszú intervallumot adunk meg!!! -- lásd FS#100 */
	if (!is_array($TANKORIDK) || count($TANKORIDK)==0) return false;
	$tolDt = readVariable($SET['tolDt'], 'datetime', date('Y-m-d'));
	$igDt  = readVariable($SET['igDt'],  'datetime', $tolDt);

	$RE = false; $v = $TANKORIDK;
	array_unshift($v, $tolDt, $igDt);
	if ($SET['result']=='csakId' || $SET['csakId']===true) {
	    $q = "SELECT oraId FROM ora WHERE dt>='%s' and dt<='%s' AND tankorId IN (".implode(',', array_fill(0, count($TANKORIDK), '%u')).")";
	    $RE = db_query($q, array('modul' => 'naplo', 'fv' => 'getOrak', 'result' => 'idonly', 'values' => $v));
	} elseif ($SET['result']=='forXml') {
	    $q = "SELECT *,getOraTolTime(ora.oraId) AS tolTime,getOraIgTime(ora.oraId) AS igTime FROM ora WHERE dt>='%s' and dt<='%s' AND tankorId IN (".implode(',', array_fill(0, count($TANKORIDK), '%u')).")";
	    $RE = db_query($q, array('modul' => 'naplo', 'fv' => 'getOrak', 'result' => 'indexed', 'values' => $v));
	} else {
	    if ($SET['elmaradokNelkul'])
		$q = "SELECT *,getOraTolTime(ora.oraId) AS tolTime,getOraIgTime(ora.oraId) AS igTime, cimkeId, cimkeLeiras 
FROM ora 
LEFT JOIN oraHazifeladat USING (oraId)
LEFT JOIN oraCimke USING (oraId)
LEFT JOIN ".__INTEZMENYDBNEV.".cimke USING (cimkeId)
WHERE dt>='%s' and dt<='%s' AND tankorId IN (".implode(',', array_fill(0, count($TANKORIDK), '%u')).") 
			AND tipus NOT IN ('elmarad','elmarad máskor')";
	    else 
		$q = "SELECT *,getOraTolTime(ora.oraId) AS tolTime,getOraIgTime(ora.oraId) AS igTime, cimkeId, cimkeLeiras 
FROM ora
LEFT JOIN oraHazifeladat USING (oraId)
LEFT JOIN oraCimke USING (oraId)
LEFT JOIN ".__INTEZMENYDBNEV.".cimke USING (cimkeId)
WHERE dt>='%s' and dt<='%s' AND tankorId IN (".implode(',', array_fill(0, count($TANKORIDK), '%u')).")";
	    $R = db_query($q, array('modul' => 'naplo', 'fv' => 'getOrak', 'result' => 'indexed', 'values' => $v));
	    $RE['tankorok']=array();
	    for ($i = 0; $i < count($R); $i++) {
		$_put = $R[$i];
		$_put['oo'] = false;
		$RE['orak'][$R[$i]['dt']][$R[$i]['ora']][$R[$i]['tankorId']] = $_put;
		if (!in_array($R[$i]['tankorId'],$RE['tankorok'])) $RE['tankorok'][] = intval($R[$i]['tankorId']);
		if ($R[$i]['hazifeladatId']>0) {
    		    if ($SET['diakId']>0) {
		        $diakHazifeladat = getDiakHazifeladatByOraIds(array($R[$i]['oraId']) , $SET['diakId']);
		        $RE['orak'][$R[$i]['dt']][$R[$i]['ora']][$R[$i]['tankorId']]['diakHazifeladat'] = $diakHazifeladat[$R[$i]['oraId']];
		    }
		}
	    }
	}
	return $RE;
    }

    /* EZT A FÜGGVÉNYT ÁT KELL NÉZNI, csak másolva, javaslat: összevonás az előzővel */
    function getOrakByTeremId($teremId, $SET=array('tolDt'=>'','igDt'=>'', 'result'=>'likeOrarend', 'elmaradokNelkul'=>false)) {

	/* FIGYELEM! A függvény feltételezi, hogy az átadott tankoridkben az adott intervallumon helyes adatok szerepelnek!
	    -- problémát okozhat, ha hosszú intervallumot adunk meg!!! -- lásd FS#100 */
	if ($teremId=='') return false;
	$tolDt = readVariable($SET['tolDt'], 'datetime', date('Y-m-d'));
	$igDt  = readVariable($SET['igDt'],  'datetime', $tolDt);
	$v = array($tolDt,$igDt,$teremId);

	if ($SET['result']=='csakId' || $SET['csakId']===true) {
	    $q = "SELECT oraId FROM ora WHERE dt>='%s' and dt<='%s' AND teremId=%u";
	    $RE = db_query($q, array('modul' => 'naplo', 'fv' => 'getOrak', 'result' => 'idonly', 'values' => $v));
	} elseif ($SET['result']=='forXml') {
	    $q = "SELECT * FROM ora WHERE dt>='%s' and dt<='%s' AND teremId=%u";
	    $RE = db_query($q, array('modul' => 'naplo', 'fv' => 'getOrak', 'result' => 'indexed', 'values' => $v));
	} else {
	    if ($SET['elmaradokNelkul'])
		$q = "SELECT * FROM ora WHERE dt>='%s' and dt<='%s' AND teremId=%u
			AND tipus NOT IN ('elmarad','elmarad máskor')";
	    else 
		$q = "SELECT * FROM ora WHERE dt>='%s' and dt<='%s' AND teremId=%u";
	    $R = db_query($q, array('modul' => 'naplo', 'fv' => 'getOrak', 'result' => 'indexed', 'values' => $v));
	    for ($i = 0; $i < count($R); $i++) {
		$_put = $R[$i];
		$_put['oo'] = false;
		$RE['orak'][$R[$i]['dt']][$R[$i]['ora']][$R[$i]['tankorId']] = $_put;
		if (!@in_array($R[$i]['tankorId'],$RE['tankorok'])) $RE['tankorok'][] = $R[$i]['tankorId'];
	    }
	}
	return $RE;
    }
    /* --- --- --- */

    function getOralatogatasByOraIds($oraIds, $SET = array('result' => 'assoc')) {

	if (!is_array($oraIds) || count($oraIds) == 0) return array();

	$q = "SELECT * FROM oraLatogatas WHERE oraId IN (".implode(',', array_fill(0, count($oraIds), '%u')).") ORDER BY oraId";
	$v = $oraIds;
	$ret = db_query($q, array('modul' => 'naplo', 'fv' => 'getOraLatogatasByOraIds', 'result' => $SET['result'], 'keyfield' => 'oraId', 'values' => $v));
	if ($SET['result'] == 'assoc') {
	    if (is_array($ret)) foreach ($ret as $oraId => $olAdat) {
		$ret[$oraId]['tanarIds'] = getOraLatogatoByLatogatasId($olAdat['oraLatogatasId']);
	    }
	} elseif ($SET['result'] == 'indexed') {
	    if (is_array($ret)) foreach ($ret as $i => $olAdat) {
		$ret[$i]['tanarIds'] = getOraLatogatoByLatogatasId($olAdat['oraLatogatasId']);
	    }
	}
	return $ret;

    }

    function getOraLatogatoByLatogatasId($latogatasId) {
	$q = "SELECT tanarId FROM oraLatogatasTanar WHERE oraLatogatasId=%u";
	return db_query($q, array('modul' => 'naplo', 'fv' => 'getOraLatogatoByLatogatasId', 'result' => 'idonly', 'values' => array($latogatasId)));
    }

    function getFeladatTipus() {
	$q = "SELECT * FROM feladatTipus";
	return db_query($q, array('modul' => 'naplo_intezmeny', 'fv' => 'getFeladatTipus', 'result' => 'assoc', 'keyfield'=>'feladatTipusId'));
    }

    function getOraTerhelesByTanarId($SET = array()) { // -- DEPRECATED
	$q = "SELECT feladatTipusId,count(*) AS db FROM ora WHERE ki=%u AND dt>='%s' AND dt<='%s' AND feladatTipusId IS NOT NULL GROUP BY feladatTipusId";
	$v = array($SET['tanarId'],$SET['tolDt'],$SET['igDt']);
	return db_query($q, array('modul' => 'naplo', 'fv' => 'getOraTerheles', 'result' => 'assoc', 'keyfield'=>'feladatTipusId','values'=>$v));
    }

    function getOraTerhelesStatByTanarId($SET = array(), $olr='') {
	/* ha a tanítási hetet úgy értelmezzük, hogy az a hét, amin az adott DT van, de nem így teszünk!
	  ehelyett az elmúlt 5 tanítási napot vizsgáljuk (egyéb értelmes szempontként)
	*/

	if (isset($SET['tanarId']) && !is_array($SET['tanarId']) && is_numeric($SET['tanarId'])) $SET['tanarId'] = array($SET['tanarId']);

	$dt=($SET['dt']=='')?date('Y-m-d'):$SET['dt'];
	/* azt is biztosítani kell, hogy a megadott dt tanítási nap legyen */
	$dt = (getTanitasiNapVissza(0,$dt));

	if ($SET['tolDt']!='' && $SET['igDt']!='') { // akkor nem prediktálható az eredmény... mit is kéne számolnunk? ezt nem engedjük
	    $_SESSION['alert'][] = '::';
	} else {
	    $tolDt = getTanitasiNapVissza(4,$dt);
	    $igDt = $dt;
	}
	initTolIgDt(__TANEV, $tolDt, $igDt);
	$v_default = array($tolDt, $igDt);
	if (is_array($SET['tanarId']) && count($SET['tanarId']) > 0) {
	    $w = " AND ki IN (" . implode(",", array_fill(0, count($SET['tanarId']), '%u')) . ") GROUP BY ki";
	    $w1 = " WHERE tanarId IN (" . implode(",", array_fill(0, count($SET['tanarId']), '%u')) . ")";
	    $v = $SET['tanarId'];
	} else {
	    $w = " GROUP BY ki";
	    $w1 = '';
	}
	$lr = ($olr=='') ? db_connect('naplo'):$olr;

	/* tanár kötelező óraszámának beállítása -- lehetne máshol is*/
	$q = "SELECT tanarId, hetiLekotottMinOraszam,hetiLekotottMaxOraszam,hetiKotottMaxOraszam,hetiMunkaora FROM ".__INTEZMENYDBNEV.".tanar".$w1;
	$R = db_query($q,array('modul'=>'naplo','result'=>'indexed','values'=>$v),$lr);
	for ($i=0;$i<count($R); $i++) {
	    $RES[$R[$i]['tanarId']]['munkaido']['heti'] = ($R[$i]['hetiMunkaora']>0) ? intval($R[$i]['hetiMunkaora']):40;
	    $RES[$R[$i]['tanarId']]['munkaido']['kotott'] = ($R[$i]['hetiKotottMaxOraszam']>0) ? intval($R[$i]['hetiKotottMaxOraszam']) :
		(($R[$i]['hetiMunkaora']>0) ? intval($R[$i]['hetiMunkaora']*0.8):32);
	    $RES[$R[$i]['tanarId']]['munkaido']['lekotott'] = ($R[$i]['hetiLekotottMaxOraszam']>0) ? intval($R[$i]['hetiLekotottMaxOraszam']) :
		(($R[$i]['hetiMunkaora']>0) ? intval($R[$i]['hetiMunkaora']*0.65):26);
	}
	/* ---- */

	// összes típus
	$TYPE = array(
	    'ossz'=> " (tipus NOT IN ('elmarad','elmarad máskor'))",
	    'kotottEloirt'=> " (tipus IN ('helyettesítés','normál','normál máskor') OR (tipus='egyéb' AND eredet='órarend'))",
	    'kotott'=> " (tipus IN ('helyettesítés','normál','normál máskor') OR (tipus='egyéb'))",
	    'lekotott'=>" (tipus IN ('helyettesítés','normál','normál máskor'))",
	    'over'=> " tipus = 'helyettesítés' AND munkaido='fennmaradó'"
	);
	foreach($TYPE as $munkaidoTipus => $TIPUSOK)
	{
	    $q = "SELECT ki AS tanarId,count(*) AS db FROM ora WHERE $TIPUSOK AND dt>='%s' AND dt<='%s'".$w;
	    $v = mayor_array_join($v_default,$SET['tanarId']);
	    $R= db_query($q, array('fv'=>'getOraTerhelesStatByTanarid','values'=>$v,'result'=>'indexed'),$lr);
	    for ($i=0;$i<count($R); $i++) $RES[$R[$i]['tanarId']][$munkaidoTipus]['heti'] = $R[$i]['db'];

	    $q = "SELECT ki AS tanarId,count(*) AS db FROM ora WHERE $TIPUSOK AND dt>='%s' AND dt<='%s'".$w;
	    $v = mayor_array_join(array($dt,$dt),$SET['tanarId']);
	    $R= db_query($q, array('fv'=>'getOraTerhelesStatByTanarid','values'=>$v,'result'=>'indexed'),$lr);
	    for ($i=0;$i<count($R); $i++) $RES[$R[$i]['tanarId']][$munkaidoTipus]['napi'] = $R[$i]['db'];

//	    $q = "SELECT ki AS tanarId,count(*) AS db FROM ora WHERE $TIPUSOK ".$w;
//	    $v = $SET['tanarId'];
//	    $R= db_query($q, array('fv'=>'getOraTerhelesStatByTanarid','values'=>$v,'result'=>'indexed'),$lr);
//	    for ($i=0;$i<count($R); $i++) $RES[$R[$i]['tanarId']]['osszOra'] = $R[$i]['db'];
	}

/*
	// EZ ITT NEM JÓ MÉG!
	$q = "SELECT ki AS tanarId,count(DISTINCT dt) AS db FROM ora WHERE $TIPUSOK ".$w;
	$v = $SET['tanarId'];
	$R= db_query($q, array('fv'=>'getOraTerhelesStatByTanarid','values'=>$v,'debug'=>false,'result'=>'indexed'),$lr);
	for ($i=0;$i<count($R); $i++) $RES[$R[$i]['tanarId']]['HPosszNapDb'] = $R[$i]['db'];
*/

	if ($olr=='') db_close($lr);
	return $RES;
    }
/*
    function _arrayJoin ($a='') {
	$ARGS = func_get_args();
	$x = array();
	for ($i=0;$i<count($ARGS);$i++) {
	    $a = $ARGS[$i];
	    if (is_array($a)) foreach($a as $v) $x[] = $v; elseif ($a!='') $x[] = $a;
	}
	return $x;
    }
*/
    function getOraStatByTankorId($tankorId,$dt='') {

	if ($tankorId=='') return false;
//	if ($tankorId=='') $tankorId=3000;
	if ($dt=='') $dt = date('Y-m-d');
	$q = "SELECT tipus,eredet,count(*) AS db FROM ora WHERE tankorId =%u GROUP BY tipus,eredet";	
	$v = array($tankorId);
	$r = db_query($q, array('modul'=>'naplo','fv'=>'getOraStatByTankorId','values'=>$v,'result'=>'indexed'),$lr);
	$R['éves'] = reindex($r,array('eredet','tipus'));

	$q = "SELECT tipus,eredet,count(*) AS db FROM ora WHERE
	    dt >= '%s' - INTERVAL DAYOFWEEK('%s')+6 DAY
	    AND dt < '%s' - INTERVAL DAYOFWEEK('%s')-1 DAY
	    AND tankorId =%u GROUP BY tipus,eredet";
	$v = array($dt,$dt,$dt,$dt,$tankorId);
	$r= db_query($q, array('debug'=>false,'modul'=>'naplo','fv'=>'getOraStatByTankorId','values'=>$v,'result'=>'indexed'),$lr);
	$R['heti'] = reindex($r,array('eredet','tipus'));

	return $R;

    }

    function oraMostVane($oraId) {
	$most = false;
	if ($oraId!='') {
	    $q = "select DISTINCT 
		IF(tolTime<curtime() AND curtime()<igTime AND nap.dt=CURDATE(),true,false) AS mostVan,
		IF(tolTime<curtime() AND curtime()<igTime,true,false) AS idosavbanMostLenne,
		csengetesiRend.tolTime,csengetesiRend.igTime,IF(terem.telephelyId!=osztaly.telephelyId,FALSE,true) AS telephelyCheck 
		FROM ora LEFT JOIN ".__INTEZMENYDBNEV.".tankorOsztaly USING (tankorId) LEFT JOIN ".__INTEZMENYDBNEV.".osztaly USING (osztalyId) LEFT JOIN munkatervOsztaly USING (osztalyId)  LEFT JOIN nap ON (munkatervOsztaly.munkatervId=nap.munkatervId AND ora.dt=nap.dt) LEFT JOIN ".__INTEZMENYDBNEV.".terem USING (teremId) LEFT JOIN ".__INTEZMENYDBNEV.".csengetesiRend ON (csengetesiRend.csengetesiRendTipus=nap.csengetesiRendTipus AND csengetesiRend.telephelyId=terem.telephelyId AND ora.ora=csengetesiRend.ora AND (csengetesiRend.nap = DAYOFWEEK(nap.dt)-1 OR csengetesiRend.nap IS NULL)) WHERE oraId=%u";
	    $v = array($oraId);
	    $R = db_query($q,array('debug'=>false,'fv'=>'oraMostVane','modul'=>'naplo','values'=>$v,'result'=>'indexed'));
	    if (count($R)>1) { // többféle eredményt kaptunk
		$most = false;	
	    }
	    $most = ($R[0]['mostVan']) ? true : false;
	}
	return $most;
    }

    function getOrakMost() {
	$q = "select oraId,IF(tolTime<curtime() AND curtime()<igTime,true,false) AS mostVan,csengetesiRend.tolTime,csengetesiRend.igTime,IF(terem.telephelyId!=osztaly.telephelyId,FALSE,true) AS telephelyCheck from ora LEFT JOIN ".__INTEZMENYDBNEV.".tankorOsztaly USING (tankorId) LEFT JOIN ".__INTEZMENYDBNEV.".osztaly USING (osztalyId) LEFT JOIN munkatervOsztaly USING (osztalyId)  LEFT JOIN nap ON (munkatervOsztaly.munkatervId=nap.munkatervId AND ora.dt=nap.dt) LEFT JOIN ".__INTEZMENYDBNEV.".terem USING (teremId) LEFT JOIN ".__INTEZMENYDBNEV.".csengetesiRend ON (csengetesiRend.csengetesiRendTipus=nap.csengetesiRendTipus AND csengetesiRend.telephelyId=terem.telephelyId AND ora.ora=csengetesiRend.ora AND (csengetesiRend.nap = DAYOFWEEK(nap.dt)-1 OR csengetesiRend.nap IS NULL)) WHERE ora.dt=curdate() AND tolTime<curtime() AND curtime()<igTime";
	$v = array();
	$R = db_query($q,array('debug'=>false,'fv'=>'oraMostVane','modul'=>'naplo','values'=>$v,'result'=>'idonly'));
	return $R;
    }

    function getKovetkezoOraAdatByOraId($oraId) {
	if ($oraId>0) {
	    $q = "SELECT * FROM ora WHERE oraId = %u";
	    $v = array($oraId);
	    $ORA = db_query($q,array('debug'=>false,'fv'=>'oraMostVane','modul'=>'naplo','values'=>$v,'result'=>'record'));
	    $q = "SELECT * FROM ora WHERE dt>'%s' AND tankorId=%u AND tipus NOT IN ('elmarad','elmarad_máskor') ORDER BY dt LIMIT 1";
	    $v = array($ORA['dt'],$ORA['tankorId']);
	    $R = db_query($q,array('debug'=>false,'fv'=>'oraMostVane','modul'=>'naplo','values'=>$v,'result'=>'record'));
	}
	return $R;
    }

    function getDiakOra($diakId,$dt,$ora,$olr_intezmeny = '',$olr_naplo) { // jelenlét mezőt nem vesszük figyelembe!!!

	// diakId->tankor->ora

	$TANKOR = getTankorByDiakId($diakId, __TANEV, array('csakId' => true, 'tolDt' => $dt, 'igDt' => $dt, 'override' => false, 'result'=>'indexed'),$olr_intezmeny); // jelenlét!!!
	// --TODO!!! minden jelenlét számít, még az is ami nem kötelező :(
	if (count($TANKOR)>0 ) {
	    $q = "SELECT *,getNev(tankorId,'tankor') AS tankorNev FROM ora WHERE dt='%s' AND ora=%u AND tankorId IN (".implode(',',$TANKOR).")";
	    $v = array($dt,$ora);
	    $R = db_query($q,array('debug'=>false,'fv'=>'getDiakOra','modul'=>'naplo','values'=>$v,'result'=>'indexed'),$olr_naplo);
	    if (count($R)==1) return $R[0];
	}
	return false;
    }

    function getOraCimkek() {
	return getCimkek();
    }
    function getCimkek() {
	if (__ORACIMKE_ENABLED === true) {
	    $q = "SELECT * FROM ".__INTEZMENYDBNEV.".cimke ORDER BY cimkeLeiras";
	    $R = db_query($q,array('debug'=>false,'fv'=>'getCimkek','modul'=>'naplo','values'=>$v,'result'=>'indexed'),$olr_naplo);
	    return $R;
	}
    }

    function getDiakHazifeladatByOraIds($oraIdk,$diakId,$olr='') {
	$R = array();
	    if (count($oraIdk)>0 && $diakId>0) {
		$q = "SELECT * FROM oraHazifeladat LEFT JOIN oraHazifeladatDiak USING (hazifeladatId) WHERE diakId=%u AND oraId IN (".implode(',',$oraIdk).")";
		$v = array($diakId);
		$R = db_query($q,array('debug'=>false,'fv'=>'getDiakhazifeladatByOraIds','modul'=>'naplo','values'=>$v,'result'=>'assoc','keyfield'=>'oraId'),$olr);
	    }
	return $R;
    }

    function getDiakHazifeladatByHatarido($diakId,$ADAT,$olr='') {
	$dt = $hazifeladatHataridoDt = readVariable($ADAT['hazifeladatHataridoDt'],'date',null);
	$R = array();
	    if ($diakId>0 && !is_null($hazifeladatHataridoDt)) {
		$tankorIds = getTankorByDiakId($diakId, __TANEV, $SET = array('csakId' => true, 'tolDt' => $dt, 'igDt' => $dt, 'result'=>'idonly'),$olr);
		if (count($tankorIds)>0) {
		    $q = "SELECT *,getNev(tankorId,'tankor') AS tankorNev 
FROM oraHazifeladat 
LEFT JOIN ora USING (oraId)
LEFT JOIN oraHazifeladatDiak ON (oraHazifeladat.hazifeladatId = oraHazifeladatDiak.hazifeladatId AND diakId=%u) 
WHERE tankorId IN (".implode(',',$tankorIds).") AND hazifeladatHataridoDt BETWEEN '%s' AND '%s 23:59:59'";
		    $v = array($diakId,$hazifeladatHataridoDt,$hazifeladatHataridoDt);
		} else { // fallback
		    $q = "SELECT *,getNev(tankorId,'tankor') AS tankorNev FROM oraHazifeladat LEFT JOIN oraHazifeladatDiak USING (hazifeladatId) LEFT JOIN ora USING (oraId) WHERE diakId=%u AND DATE(hazifeladatHataridoDt)='%s'";
		    $v = array($diakId,$hazifeladatHataridoDt);
		}
		$R = db_query($q,array('debug'=>false,'fv'=>'getDiakhazifeladatByOraIds','modul'=>'naplo','values'=>$v,'result'=>'indexed'),$olr);
	    }
	return $R;
    }

    function getOsztalyHazifeladatByHatarido($osztalyId,$ADAT,$olr='') {
	// loop $diakId
	$hazifeladatHataridoDt = $ADAT['hazifeladatHataridoDt'];
	
    }

?>
