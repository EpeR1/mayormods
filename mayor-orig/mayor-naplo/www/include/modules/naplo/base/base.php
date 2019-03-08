<?php

    function initTolIgDt($tanev, &$tolDt, &$igDt, $override = false) {

        global $_TANEV;

	if (!is_numeric($tanev)&&$tanev!='') $_SESSION['alert'][] = 'info:TANEV:paramalert'.serialize($tanev); 

        if ($tanev != '') {
	    if ($tanev != __TANEV) $TA = getTanevAdat($tanev);
    	    else $TA = $_TANEV;
    	    if ($tanev == __TANEV && __FOLYO_TANEV) {
        	$dt = date('Y-m-d');
        	if ($tolDt == '') $tolDt = $dt;
        	elseif (!$override && strtotime($tolDt) < strtotime($TA['kezdesDt'])) $tolDt = $TA['kezdesDt'];
        	if ($igDt == '') if (strtotime($dt) > strtotime($tolDt)) $igDt = $dt; else $igDt = $tolDt;
        	elseif (!$override && strtotime($igDt) > strtotime($TA['zarasDt'])) $igDt = $TA['zarasDt'];
    	    } else {
        	if ($tolDt == '' or (!$override && strtotime($tolDt) < strtotime($TA['kezdesDt']))) $tolDt = $TA['kezdesDt'];
		elseif ($tolDt != '' && strtotime($TA['zarasDt']) < strtotime($tolDt)) $tolDt = $TA['zarasDt'];
        	if ($igDt == '' or (!$override && strtotime($igDt) > strtotime($TA['zarasDt']))) $igDt = $TA['zarasDt'];
		elseif ($igDt != '' && strtotime($igDt) < strtotime($TA['kezdesDt'])) $igDt = $TA['kezdesDt'];
    	    }
	}
    }

 // -------------------------------------------------- //

    function getTanitasiNapVissza($napszam, $from = 'curdate()', $olr = '') {
	return getTanitasiNap(array('direction'=>'vissza','napszam'=>$napszam,'fromDt'=>$from), $olr);
    }

    function getTanitasiNap($ADAT = array('direction'=>'', 'napszam'=>0, 'fromDt'=>'curdate()'), $olr = null) {

	global $_TANEV;

	if ($ADAT['fromDt']!='') $fromDt = $ADAT['fromDt']; else $fromDt = 'curdate()'; 
	if ($ADAT['direction']!='') $direction = $ADAT['direction'];
	if ($ADAT['napszam']!='') $napszam = $ADAT['napszam'];
        if ($napszam < 0 || !defined('__TANEV') || is_null(__TANEV) ||!is_array($_TANEV) || $_TANEV['kezdesDt']=='' || $_TANEV['statusz']=='tervezett') {
	    return false;
	}
        if ($direction == 'vissza') { $relacio='<'; $DESC = 'DESC'; }
        else { $relacio = '>'; $DESC = 'ASC'; }

	if ($napszam==0) { // extra eset
	    $v = array($fromDt,1);
	    $from="CAST('%s' AS DATE)";
	    if ($direction == 'vissza') $relacio = '<=';
	    else $relacio = '>=';
	    $limit = "1";
        } elseif ($fromDt != 'curdate()') {
	    $v = array($fromDt, ($napszam-1));
	    $from="CAST('%s' AS DATE)";
	    $limit = "%u,1";
	} else {
	    $v = array(($napszam-1)); 
	    $from='curdate()';
	    $limit = "%u,1";
	}

	    $q = "SELECT DISTINCT(dt) FROM nap
                    WHERE dt $relacio $from
                    AND tipus LIKE '%%tanítási nap'
                    ORDER BY dt $DESC
                    LIMIT ".$limit;
	    $nap = db_query($q, array('fv' => 'getTanitasiNap', 'modul' => 'naplo', 'result' => 'value', 'values' => $v), $olr);
	    // Ha nincs elég tanítási nap, akkor iránytól függően adjuk vissza a szélső értékeket
            if ($nap == '') {
		if ($direction == 'vissza') $nap = date('Y-m-d',strtotime($_TANEV['kezdesDt'])); // ez ugye lehet nem tanítási nap is!
		else $nap = date('Y-m-d',strtotime($_TANEV['zarasDt'])); // ez ugye lehet nem tanítási nap is!
	    }

        return $nap;

    }

    function getTanitasiHetHetfo($ADAT = array('direction'=>'', 'napszam'=>0, 'fromDt'=> ''), $olr = null) {

	global $_TANEV;

	if ($ADAT['fromDt']!='') $fromDt = $ADAT['fromDt']; else $fromDt = date('Y-m-d'); 
	if ($ADAT['direction']!='') $direction = $ADAT['direction'];
	if ($ADAT['napszam']!='') $napszam = $ADAT['napszam'];
        if ($napszam < 0 || !defined('__TANEV') || is_null(__TANEV) ||!is_array($_TANEV) || $_TANEV['kezdesDt']=='') {
	    return false;
	}
	if ($napszam == 0) {
	    // Az előző/következő napot követő/megelőző nap utáni 1 tanítási nap
	    if ($direction == 'vissza') { $muv = '+'; }
	    else { $muv = '-'; }
	    $v = array($napszam);
	    $from = "'$fromDt' $muv INTERVAL 1 DAY";
	} elseif ($fromDt != 'curdate()') {
	    $v = array($fromDt, ($napszam-1));
	    $from="CAST('%s' AS DATE)";
	} else { 
	    $v = array(($napszam-1)); 
	    $from = $fromDt;
	}
        if ($direction == 'vissza') { $relacio='<'; $DESC = 'DESC'; }
        else { $relacio = '>'; $DESC = 'ASC'; }

	    $q = "SELECT DISTINCT(  DATE(dt-INTERVAL (DAYOFWEEK(dt)-2) DAY)  ) AS dt  FROM nap
                    WHERE dt $relacio $from
                    AND tipus LIKE '%%tanítási nap'
                    ORDER BY dt $DESC
                    LIMIT %u,1";
	    $nap = db_query($q, array('fv' => 'getTanitasiNap', 'modul' => 'naplo', 'result' => 'value', 'values' => $v), $olr);

            if ($nap == '') $nap = date('Y-m-d',strtotime($_TANEV['kezdesDt'])); // ez ugye lehet nem tanítási nap is!

        return $nap;

    }


 // -------------------------------------------------- //

    function checkNaplo($dt = '') {

	global $_TANEV;

        $time = strtotime($dt);

        if ($time < strtotime($_TANEV['kezdesDt']) || $time > strtotime($_TANEV['zarasDt'])) return false;
        if ($time === false // $dt == '0000-00-00' || $dt == '' || !isset($dt)
            || __TANKOROK_OK !== true
	    || (
                !__NAPLOADMIN
                && ($time < strtotime($_TANEV['kezdesDt']) || $time > strtotime($_TANEV['zarasDt']))
            )
        ) {
	    $_SESSION['alert'][] = 'message:wrong_data:checkNaplo:'.$dt.'('.(__TANKOROK_OK?1:2).')';
            return false;
        } else {

            $napszam = date('w', strtotime($dt));
            if ($napszam == 0) $napszam = 7;

	    // Muszáj újrakapcsolódnunk a lock miatt - nem adható át $olr...
	    $lr = db_connect('naplo', array('force' => true, 'fv' => 'checkNaplo'));

	    // E helyett: Van-e olyan munkaterv, amiben van tanítási nap - sőt, lekérdezzük rögtön a hozzá tartozó órarendi hét-osztalyId párokat is!
	    $query = "SELECT distinct orarendiHet, osztalyId FROM nap LEFT JOIN munkatervOsztaly USING (munkatervId) WHERE dt=CAST('%s' AS DATE) 
			AND tipus='tanítási nap' AND osztalyId IS NOT NULL"; // ha egy munkatervhez nincs osztály rendelve, akkor se zavarjon be...
	    $v = array($dt);
	    // keyvalues = első mező a kulcs, azon belül a második mező indexelve jelenik meg
	    $RESULT = db_query($query, array('fv' => 'checkNaplo', 'modul' => 'naplo', 'result' => 'keyvalues', 'values' => $v), $lr);
	    if ($RESULT===false) {
		db_close($lr);
		return false;
	    }
	    if (is_array($RESULT) && count($RESULT) > 0) {

                    $query = "SELECT oraId FROM ora WHERE dt=CAST('%s' AS DATE) LIMIT 1";
		    $_oraId = db_query($query, array('fv' => 'checkNaplo/testIfCheck', 'modul' => 'naplo', 'result' => 'value', 'values' => $v), $lr);
		    if ($_oraId === false) {
			db_close($lr);
			return false;
		    }

                    if ($_oraId === null) {

			// lock
			$lock_q = 'LOCK TABLE ora write, orarendiOra read, orarendiOraTankor read, osztalyNaplo READ, '.__INTEZMENYDBNEV.'.osztaly READ,'.__INTEZMENYDBNEV.'.tankorOsztaly READ,'.__INTEZMENYDBNEV.'.tankorSzemeszter READ';
			db_query($lock_q, array('fv' => 'checkNaplo/lock ora', 'modul' => 'naplo'), $lr);
			// recheck
                	$query = "SELECT oraId FROM ora WHERE dt=CAST('%s' AS DATE) LIMIT 1";
			$_oraId = db_query($query, array('fv' => 'checkNaplo/testIfCheck', 'modul' => 'naplo', 'result' => 'value', 'values' => $v), $lr);
			if ($_oraId === false || $_oraId !== null) {
			    db_query('unlock tables', array('fv' => 'checkNaplo/lock ora', 'modul' => 'naplo'), $lr);
			    db_close($lr);
			    return false;
                	}
			// --

			// órarendi hetenként töltjük be... bár jelenleg egy nap csak egy órarendi hét lehet!
			foreach ($RESULT as $orarendiHet => $osztalyIds) {
			    if (!is_array($osztalyIds) || count($osztalyIds)==0) continue;
			    // INSERT-be pedig csak azon tankorId-k, amik szerepelnek a tankorOsztaly táblában
			    $q = "SELECT DISTINCT tankorId FROM ".__INTEZMENYDBNEV.".tankorOsztaly WHERE osztalyId IN (".implode(',',$osztalyIds).")";
			    $tankorIds = db_query($q, array('fv'=>'checkNaplo/tankorIds', 'modul'=>'naplo', 'result'=>'idonly'));
                    	    // Elvileg nem lehet tankor NULL - _TANKOROK_OK
			    // De sajnos minden lehet:
			    if (!is_array($tankorIds) || count($tankorIds)==0) continue;
                    	    $query = "INSERT INTO ora (dt,ora,ki,tankorId,teremId,tipus,eredet)
                            	    SELECT '%s',ora,orarendiOra.tanarId AS tanarId, orarendiOraTankor.tankorId AS tankorId,
					teremId, 'normál','órarend'
                                    FROM orarendiOra LEFT JOIN orarendiOraTankor USING (tanarId,osztalyJel,targyJel)
                                    WHERE orarendiOraTankor.tankorId IS NOT NULL
				    AND tankorId IN (".implode(',', $tankorIds).")
                                    AND het=%u
                                    AND nap=%u
				    AND tolDt<='%s' AND igDt>='%s' "; // !!!!
			    $v = array($dt, $orarendiHet, $napszam, $dt, $dt);
			    $er = db_query($query, array('fv' => 'checkNaplo/finally', 'modul' => 'naplo', 'values' => $v), $lr);
			}

			db_query('unlock tables', array('fv' => 'checkNaplo/unlock', 'modul' => 'naplo'), $lr);
			if ($er === false) {
			    db_close($lr);
			    return false;
			}
                    }
                //}
            }
	    db_close($lr);
	    return true;
        }
    }

 // -------------------------------------------------- //

    function checkNaploStatus($olr = '') {

	if ($olr == '') $lr = db_connect('naplo');
	else $lr = $olr;
	
	// A munkaterv meglétének ellenőrzése
        $q = "SELECT COUNT(*) FROM nap";
	$count = db_query($q, array('fv' => 'checkNaploStatus/darab', 'modul' => 'naplo', 'result' => 'value'), $lr);
        define('__MUNKATERV_OK', ($count != 0));

        $q = "SELECT COUNT(*) FROM orarendiOra WHERE tolDt <= curdate() AND igDt >= curdate()";
	$count = db_query($q, array('fv' => 'checkNaploStatus/darab', 'modul' => 'naplo', 'result' => 'value'), $lr);
        define('__ORAREND_OK', ($count != 0));

        $q = "SELECT COUNT(DISTINCT orarendiOra.tanarId, orarendiOra.targyJel, orarendiOra.osztalyJel)
                FROM orarendiOra LEFT JOIN orarendiOraTankor USING(tanarId, targyJel, osztalyJel)
                WHERE tankorId IS NULL";
	$count = db_query($q, array('fv' => 'checkNaploStatus/darab', 'modul' => 'naplo', 'result' => 'value'),$lr);
        if ($count != 0) {
            define('__TANKOROK_OK', false);
            define('__HIANYZO_TANKOROK_SZAMA', $count);
        } else {
            define('__TANKOROK_OK', true);
	    define('__HIANYZO_TANKOROK_SZAMA', 0);
        }

	if ($olr == '') db_close($lr);

    }

    function checkDiakStatusz() {

	/* Konzisztencia ellenőrzés */

	$lr = db_connect('naplo_intezmeny', array('fv' => 'checkDiakStatusz'));
	db_start_trans($lr);

	// Ha státusz!='felvételt nyert' akkor kell lennie jogviszonyKezdete dátumnak
	$q = "SELECT COUNT(*) AS db FROM diak WHERE statusz!='felvételt nyert' AND (jogviszonyKezdete IS NULL OR jogviszonyKezdete = '0000-00-00')";
	$db = db_query($q, array('fv' => 'checkDiakStatusz/pre#1', 'modul' => 'naplo_intezmeny', 'result' => 'value'), $lr);
	if (__NAPLOADMIN && $db > 0) $_SESSION['alert'][] = 'message:wrong_data:jogviszonyKezdete hiányzik '.$db.' darab rekordban';

	// Ha van olyan jogviszonyKezdete bejegyzés, melyhez nem tartozik diakJogviszony rekord, akkor azt pótoljuk
	$q = "SELECT COUNT(*) AS db FROM diak LEFT JOIN diakJogviszony 
		ON diak.diakId = diakJogviszony.diakId AND diakJogviszony.statusz IN ('jogviszonyban van','vendégtanuló') AND diak.jogviszonyKezdete=diakJogviszony.dt
		WHERE diakJogviszony.dt IS NULL AND jogviszonyKezdete IS NOT NULL AND jogviszonyKezdete != '0000-00-00'";
	$db = db_query($q, array('fv' => 'checkDiakStatusz/pre#1', 'modul' => 'naplo_intezmeny', 'result' => 'value'), $lr);

	$insDb = 0;
	if ($db!==false && $db>0) {
	    $q = "INSERT INTO ".__INTEZMENYDBNEV.".diakJogviszony
		SELECT diak.diakId as diakId, IF(diak.statusz='vendégtanuló','vendégtanuló','jogviszonyban van') AS statusz, jogviszonyKezdete AS dt FROM diak LEFT JOIN diakJogviszony 
                ON diak.diakId = diakJogviszony.diakId AND diakJogviszony.statusz IN ('jogviszonyban van','vendégtanuló') AND diak.jogviszonyKezdete=diakJogviszony.dt
 		WHERE diakJogviszony.dt IS NULL AND jogviszonyKezdete IS NOT NULL AND jogviszonyKezdete != '0000-00-00'";
	    $insDb = db_query($q, array('fv' => 'checkDiakStatusz/#1', 'modul' => 'naplo_intezmeny', 'result' => 'affected rows'), $lr);
	}
	// A diakJogviszony tábla alapján állítjuk az aktuális státuszt
	/* $q = "UPDATE ".__INTEZMENYDBNEV.".diak LEFT JOIN ".__INTEZMENYDBNEV.".diakJogviszony AS dj ON diak.diakId=dj.diakId 
		AND dj.dt=(SELECT MAX(dt) FROM ".__INTEZMENYDBNEV.".diakJogviszony WHERE dt<=CURDATE() AND diakId=dj.diakId)
		SET diak.statusz = dj.statusz
		WHERE diak.statusz<>'felvételt nyert' AND dj.statusz IS NOT NULL AND diak.statusz<>dj.statusz";
	*/
	$q = "SELECT ".__INTEZMENYDBNEV.".diak.diakId,dj.statusz,dt 
		FROM ".__INTEZMENYDBNEV.".diak LEFT JOIN ".__INTEZMENYDBNEV.".diakJogviszony AS dj 
		ON (
		    diak.diakId=dj.diakId 
		    AND dj.dt=(SELECT MAX(dt) FROM ".__INTEZMENYDBNEV.".diakJogviszony WHERE dt<=CURDATE() AND diakId=dj.diakId)
		) WHERE diak.statusz<>'felvételt nyert' AND dj.statusz IS NOT NULL AND diak.statusz<>dj.statusz ORDER BY diakId,dt";
	$r = db_query($q, array('fv' => 'checkDiakStatusz/#2', 'modul' => 'naplo_intezmeny', 'result' => 'indexed'), $lr);
	$updDb = 0;
	for ($i=0; $i<count($r); $i++) {
	    $q = "UPDATE ".__INTEZMENYDBNEV.".diak SET statusz='%s' WHERE diakId=%u";
	    $v = array($r[$i]['statusz'],$r[$i]['diakId']);
	    $updDb += db_query($q, array('fv' => 'checkDiakStatusz/#2', 'modul' => 'naplo_intezmeny', 'values'=>$v, 'result' => 'affected rows'), $lr);
	}

	db_commit($lr);
	db_close($lr);

	return intval($insDb)+intval($updDb);

    }

?>
