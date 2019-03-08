<?php

    ////////////////////////////////////////
    //
    // Négy féle "üzemmód"
    // 1. Ha aktív a tanév és átadom a $igDts[diakId] => dt tömböt, akkor
    //    diákonként lekérdezi a megadott dátumig, de legkésőbb a szemszter
    //    zárásig lévő hiányzásokat - a tanév adatbázisból!
    // 2. Ha aktív a tanév, de nincs $igDts, akkor egyszerre kérdezi le az
    //    összes diákét a szemeszter zárásig
    // 3. Ha nem aktív a tanév, akkor az intézményi adatbázisból kérdezi le
    //    az összesített adatokat (amik záráskor jönnek létre)
    // 4. Ha nincs megadva szemeszter, akkor az összes szemszter összesítését lekérdezi - az intézményi adatbázisból
    //
    ////////////////////////////////////////

    function defWnemszamit() {
	$W['nemszamit'] = ' AND hianyzasBeleszamit="igen" ';
	$W['join'] = ' LEFT JOIN '.__INTEZMENYDBNEV.'.tankorTipus USING (tankorTipusId) ';
	return $W;
    }

    function getDiakHianyzasOsszesites($diakIds, $szemeszterAdat, $igDts = null) {
        $ret = array();
	if (count($diakIds)<1) return $ret;

	$Wnemszamit = defWnemszamit();
        if (is_array($szemeszterAdat)) {
            // Egy szemeszter hiányzási adatainak lekérdezése
            if (
		($szemeszterAdat['statusz'] == 'aktív' || $szemeszterAdat['statusz'] == 'lezárt')
		&& is_array($igDts)
	    ) {
        	// Folyó vagy lezárt tanév - a tanév adatbázisból kérdezünk le
		// diákonként más-más vég határidővel (pl Osztályból kilépett)
    		foreach ($diakIds as $diakId) {
			$igDt = readVariable(
			    $igDts[$diakId], 'datetime', $szemeszterAdat['zarasDt'], array(), 'strtotime($return) <= '.strtotime($szemeszterAdat['zarasDt'])
			);
			$q = "SELECT diakId,
                    		COUNT(IF(tipus='hianyzas' AND statusz='igazolt',1,NULL)) AS igazolt,
                        	COUNT(IF(tipus='hianyzas' AND statusz='igazolatlan',1,NULL)) AS igazolatlan,
                        	SUM(IF(tipus='késés' AND statusz='igazolatlan',perc,NULL)) AS kesesPercOsszeg,

				COUNT(IF(tipus='hianyzas' AND statusz='igazolt' AND tankorTipus.jelleg='gyakorlat',1,NULL)) AS gyakorlatIgazolt,
                        	COUNT(IF(tipus='hianyzas' AND statusz='igazolatlan' AND tankorTipus.jelleg='gyakorlat',1,NULL)) AS gyakorlatIgazolatlan,
                        	SUM(IF(tipus='késés' AND statusz='igazolatlan' AND tankorTipus.jelleg='gyakorlat',perc,NULL)) AS gyakorlatKesesPercOsszeg,

                        	COUNT(IF(tipus='hianyzas' AND statusz='igazolt' AND tankorTipus.jelleg='elmélet',1,NULL)) AS elmeletIgazolt,
                        	COUNT(IF(tipus='hianyzas' AND statusz='igazolatlan' AND tankorTipus.jelleg='elmélet',1,NULL)) AS elmeletIgazolatlan,
                        	SUM(IF(tipus='késés' AND statusz='igazolatlan' AND tankorTipus.jelleg='elmélet',perc,NULL)) AS elmeletKesesPercOsszeg

                    		FROM `%s`.hianyzas ".$Wnemszamit['join']."
				WHERE (
                        	    tipus = 'hiányzás' OR (tipus='késés' AND statusz='igazolatlan' AND perc IS NOT NULL)
                    		) AND dt<='%s' AND diakId=%u
			    ".$Wnemszamit['nemszamit']."
                        GROUP BY diakId";

			$v = array(tanevDbNev(__INTEZMENY, $szemeszterAdat['tanev']), $igDt, $diakId);
            		$ret[$diakId] = db_query($q, array(
			    'fv' => 'getDiakHianyzasOsszesites', 'modul' => 'naplo_intezmeny', 'result' => 'record', 'values' => $v
			));
			//++ Hozott hiányzások a tanévben
			$_hozott = getDiakHozottHianyzas($diakId, array('tanev'=> $szemeszterAdat['tanev'] , 'igDt'=>$igDt));
			$ret[$diakId]['igazolatlan'] += intval($_hozott['igazolatlan']['db']);
			$ret[$diakId]['igazolt'] += intval($_hozott['igazolt']['db']);
		}
	    } elseif ($szemeszterAdat['statusz'] == 'aktív') {
		// Aktív tanévből kérdezünk le összesítést - ami még nem készült el -> tanév adatbázist használjuk 
		    foreach ($diakIds as $diakId) {
			$_hozott[$diakId] = getDiakHozottHianyzas($diakId, array('tanev'=> $szemeszterAdat['tanev']));
		    }
            	    $q = "SELECT diakId,

                            COUNT(IF(tipus='felszerelés hiány',1,NULL)) AS felszerelesHianyDb,
                            COUNT(IF(tipus='egyenruha hiány',1,NULL)) AS egyenruhaHianyDb,
                            COUNT(IF(tipus='késés',1,NULL)) AS kesesDb,
			    
                            COUNT(IF(tipus='hianyzas' AND statusz='igazolt',1,NULL)) AS igazolt,
                            COUNT(IF(tipus='hianyzas' AND statusz='igazolatlan',1,NULL)) AS igazolatlan,
                            SUM(IF(tipus='késés' AND statusz='igazolatlan',perc,NULL)) AS kesesPercOsszeg,

			    COUNT(IF(tipus='hianyzas' AND statusz='igazolt' AND tankorTipus.jelleg='gyakorlat',1,NULL)) AS gyakorlatIgazolt,
                    	    COUNT(IF(tipus='hianyzas' AND statusz='igazolatlan' AND tankorTipus.jelleg='gyakorlat',1,NULL)) AS gyakorlatIgazolatlan,
                    	    SUM(IF(tipus='késés' AND statusz='igazolatlan' AND tankorTipus.jelleg='gyakorlat',perc,NULL)) AS gyakorlatKesesPercOsszeg,

                    	    COUNT(IF(tipus='hianyzas' AND statusz='igazolt' AND tankorTipus.jelleg='elmélet',1,NULL)) AS elmeletIgazolt,
                    	    COUNT(IF(tipus='hianyzas' AND statusz='igazolatlan' AND tankorTipus.jelleg='elmélet',1,NULL)) AS elmeletIgazolatlan,
                    	    SUM(IF(tipus='késés' AND statusz='igazolatlan' AND tankorTipus.jelleg='elmélet',perc,NULL)) AS elmeletKesesPercOsszeg

                        FROM `%s`.hianyzas ".$Wnemszamit['join']."
			WHERE (
                            tipus = 'hiányzás' OR (tipus='késés' AND statusz='igazolatlan' AND perc IS NOT NULL)
                        ) AND dt<='%s' AND diakId IN (".implode(',', array_fill(0, count($diakIds), '%u')).")
			    ".$Wnemszamit['nemszamit']."
                        GROUP BY diakId";
		    array_unshift($diakIds, tanevDbNev(__INTEZMENY, $szemeszterAdat['tanev']), $szemeszterAdat['zarasDt']);
            	    $ret = db_query($q, array(
			'fv' => 'getDiakHianyzasOsszesites', 'modul' => 'naplo_intezmeny', 'result' => 'assoc', 'keyfield' => 'diakId', 'values' => $diakIds
		    ));

		    //++ Hozott hiányzások a tanévben
		    foreach ($ret as $diakId => $dAdat) {
			$ret[$diakId]['igazolatlan'] += intval($_hozott[$diakId]['igazolatlan']['db']);
			$ret[$diakId]['igazolt'] += intval($_hozott[$diakId]['igazolt']['db']);
		    }
            } elseif ($szemeszterAdat['statusz'] != 'tervezett') {
                // lezárt vagy archív tanév - az intézmény adatbázisból kérdezünk le - nincs $Wnemszamit!! se tankortipusid... :(
                $q = "SELECT * FROM hianyzasOsszesites 
			WHERE diakId IN (".implode(',', array_fill(0, count($diakIds), '%u')).") AND tanev=%u AND szemeszter=%u
		     ";

		array_push($diakIds, $szemeszterAdat['tanev'], $szemeszterAdat['szemeszter']);
                $ret = db_query($q, array(
		    'fv' => 'getDiakHianyzasOsszesites', 'modul' => 'naplo_intezmeny', 'result' => 'assoc', 'keyfield' => 'diakId', 'values' => $diakIds

		));
            } else {
		$_SESSION['alert'][] = 'message:wrong_dara:tervezett tanév hiányzás összesítése:getDiakHianyzasOsszesites';
	    }
        } else {
            // A diák összes hiányzási adata ?????????????????????????????????????????? BIZTOS KELL MÉG EZZZZ?????????????????
	    // !!!!!!!!!!!!!!!!!
            $q = "SELECT * FROM hianyzasOsszesites WHERE diakId IN (".implode(',', array_fill(0, count($diakIds), '%u')).") ORDER BY tanev,szemeszter";
            $r = db_query($q, array('fv' => 'getDiakHianyzasOsszesites', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => $diakIds));
            for ($i = 0; $i < count($r); $i++) $ret[ $r[$i]['tanev'] ][ $r[$i]['szemeszter'] ][ $r[$i]['diakId'] ] = $r[$i];
        }
        return $ret;

    }

    function getIgazolasTipusLista() {

	global $lang;

	if (file_exists("lang/$lang/module-naplo/share/hianyzas.php")) {
    	    require_once("lang/$lang/module-naplo/share/hianyzas.php");
	} elseif (file_exists('lang/'._DEFAULT_LANG.'/module-naplo/share/hianyzas.php')) {
    	    require_once('lang/'._DEFAULT_LANG.'/module-naplo/share/hianyzas.php');
	}

	$igazolasTipusok['lista'] = getEnumField('naplo', 'hianyzas', 'igazolas');
	foreach ($igazolasTipusok['lista'] as $index => $tipus) {
	    $const = '_'.str_replace(' ', '_', nagybetus(ekezettelen($tipus)));
	    if (defined($const)) $igazolasTipusok[$tipus] = constant($const);
	    elseif ($tipus != '') {
		$igazolasTipusok[$tipus] = $tipus;
		$_SESSION['alert'][]= 'message:wrong_data:hiányzó nyelvi konstans:'.$tipus.':getIgazolasTipusLista';
	    }
	}
	return $igazolasTipusok;
    }

    function getHianyzasByOraId($oraId, $SET = array('csakId' => false)) {
    
	if ($SET['csakId'] === true) {
	    $q = "SELECT hianyzasId FROM hianyzas WHERE oraId=%u";
	    $RES = db_query($q, array('fv' => 'getHianyzasByOraId', 'modul' => 'naplo', 'result' => 'idonly', 'values' => array($oraId)));
	} else {
	    $q = "SELECT * FROM hianyzas WHERE oraId=%u";
	    $RES = db_query($q,array('fv'=>'getHianyzasByOraId', 'modul' => 'naplo', 'result' => 'indexed', 'values' => array($oraId)));
	}
	return $RES;
    }

    function getHianyzasByDiakIds($diakIds, $SET = array('tolDt' => null, 'igDt' => null, 'result' => 'indexed', 'keyfield' => null)) {

	if (!is_array($diakIds) || count($diakIds) == 0) return false;

	$result = readVariable($SET['result'], 'enum', 'indexed', array('indexed','multiassoc'));
	if ($result == 'multiassoc') $keyfield = readVariable($SET['keyfield'], 'enum', 'tankorId', array('tankorId', 'diakId', 'ora', 'oraId'));
	$tolDt = readVariable($SET['tolDt'], 'date');
	$igDt = readVariable($SET['igDt'], 'date');
	initTolIgDt(__TANEV, $tolDt, $igDt);

	$q = "SELECT *,hianyzas.tipus as hTipus FROM hianyzas LEFT JOIN ora USING (oraId,dt,ora) 
		WHERE '%s' <= dt AND dt <= '%s' AND diakId IN (".implode(',', array_fill(0, count($diakIds), '%u')).")
		ORDER BY ora,tankorId";
	array_unshift($diakIds, $tolDt, $igDt);
	$ret = db_query($q, array('fv' => 'getHianyzasByDiakIds', 'modul' => 'naplo', 'result' => $result, 'keyfield' => $keyfield, 'values' => $diakIds));

	return $ret;

    }

    function getOraIdByHianyzasId($hianyzasId, $olr = null) {
    
	    $q = "SELECT oraId FROM hianyzas WHERE hianyzasId=%u";
	    return db_query($q, array('fv' => 'getOraIdByHianyzasId', 'modul' => 'naplo', 'result' => 'value', 'values' => array($hianyzasId)),$olr);
    }

    function getHianyzasByDt($DIAKIDK, $DTK, $SET = array('result' => '')) {
	if(!is_array($DIAKIDK) || count($DIAKIDK) == 0) return false;
	if (!is_array($DTK))
	    if ($DTK=='') return false;
	    else $DTK = array($DTK);
	$v = mayor_array_join($DTK, $DIAKIDK);
	if ($SET['csakId']!==true) {
	    $q = "SELECT * FROM hianyzas WHERE dt IN ('".implode("','", array_fill(0, count($DTK), '%s'))."') 
		    AND diakId IN (".implode(',',array_fill(0, count($DIAKIDK), '%u')).") ORDER BY dt,ora";
	    $R = db_query($q,array('fv' => 'getHianyzasByDt', 'modul' => 'naplo', 'result' => 'indexed', 'values' => $v));
	    for ($j=0; $j<count($R); $j++) {
		$RES[$R[$j]['diakId']][$R[$j]['dt']][$R[$j]['ora']][] = $R[$j];
	    }
	} else {
	    $q = "SELECT hianyzasId FROM hianyzas WHERE dt IN ('".implode("','", array_fill(0, count($DTK), '%s'))."') 
		    AND diakId IN (".implode(',',array_fill(0, count($DIAKIDK), '%u')).")";
	    $RES = db_query($q,array('fv' => 'getHianyzasByDt', 'modul' => 'naplo', 'result' => 'idonly', 'values' => $v));
	}
	return $RES;	
    }

    function getHianyzasById($hianyzasId) {
	if ($hianyzasId == '') return false;
	$q = "SELECT * FROM hianyzas WHERE hianyzasId=%u";
	return db_query($q, array('fv'=>'getHianyzasById', 'modul' => 'naplo', 'result' => 'indexed', 'values' => array($hianyzasId)));
    }

    function getDiakIgazolatlan($diakId) {
    
	global $_TANEV;
    
	$Wnemszamit = defWnemszamit();
	$WHERE = "diakId=%u AND tipus IN ('hiányzás','késés') AND statusz='igazolatlan' AND '%s' <= dt AND dt<='%s'";
        $q = "SELECT * FROM hianyzas ".$Wnemszamit['join']." WHERE $WHERE ".$Wnemszamit['nemszamit']." ORDER BY dt, ora";
        $v = array($diakId, $_TANEV['kezdesDt'], $_TANEV['zarasDt']);
	return db_query($q, array('fv' => 'getDiakIgazolatlan', 'modul' => 'naplo', 'result' => 'indexed', 'values' => $v));

    }

    function getIgazolasSzam($diakId, $dt = '') {

	global $_TANEV;
        // ha dt adott, azt a napot ne számoljuk bele a napi! limit-be (hisz még beírhatok több hiányzást is!)
        $RETURN = array();

	$Wnemszamit = defWnemszamit();
	// szemeszterenként
	foreach ($_TANEV['szemeszter'] as $szemeszter => $szAdat) {

	    $WHERE = "diakId=%u AND tipus='hiányzás' AND statusz='igazolt' AND '%s' <= dt AND dt<='%s'";
	    $v = array($diakId, $szAdat['kezdesDt'], $szAdat['zarasDt']);
	    if ($dt!='') {
		$WHERE2 = " AND dt!='%s' ";
		$v[] = $dt;
	    } else $WHERE2 = ''; // vajon az óráknál ez nem kell?

	    // napok
            $q = "SELECT COUNT(DISTINCT dt) AS darab, igazolas FROM hianyzas ".$Wnemszamit['join']."WHERE $WHERE $WHERE2".$Wnemszamit['nemszamit']." GROUP BY igazolas";
	    $ret = db_query($q, array('fv' => 'getIgazolasSzam', 'modul' => 'naplo', 'result' => 'indexed', 'values' => $v));
	    for ($i = 0; $i < count($ret); $i++) {
                $RETURN['napok'][$szemeszter][ $ret[$i]['igazolas'] ] =  $ret[$i]['darab'];
                $RETURN['napok']['osszesen'][ $ret[$i]['igazolas'] ] += $ret[$i]['darab'];
	    }
	    // órák
            $q = "SELECT COUNT(*) AS darab, igazolas FROM hianyzas ".$Wnemszamit['join']." WHERE $WHERE ".$Wnemszamit['nemszamit']." GROUP BY igazolas";
	    $ret = db_query($q, array('fv' => 'getIgazolasSzam', 'modul' => 'naplo', 'result' => 'indexed', 'values' => $v));
	    for ($i = 0; $i < count($ret); $i++) {
                $RETURN['orak'][$szemeszter][ $ret[$i]['igazolas'] ] =  $ret[$i]['darab'];
                $RETURN['orak']['osszesen'][ $ret[$i]['igazolas'] ] += $ret[$i]['darab'];
	    }
	    $RETURN['szemeszterek'][] = $szemeszter;
	}
        return $RETURN;
    }

    function legkorabbiIgazolhatoHianyzasVeg($osztalyId, $olr = '') {


	if (!isset($osztalyId) || $osztalyId == '' || count($osztalyId)==0) {
	    $_SESSION['alert'][] = 'message:wrong_data:Nincs megadva osztály (legkorabbiIgazolhatoHianyzasVeg)';
	    // return false;
	    return _LEGKORABBI_IGAZOLHATO_HIANYZAS;
	}

        // _IGAZOLAS_BEIRAS_HATARIDO előtti első osztályfőnöki óra - vagy _LEGKORABBI_IGAZOLHATO_HIANYZAS
        if ($olr == '') $lr = db_connect('naplo');
        else $lr = $olr;

	    if (!is_array($osztalyId)) $osztalyId = array($osztalyId);

	    // Az osztályfőnöki tankör lekérdezése (!!! ez hibás, targyNev='osztályfőnöki' helyett a tárgy típus alapján kell! -- TODO 
	    $q = "SELECT tankorId FROM ".__INTEZMENYDBNEV.".tankorOsztaly
		    LEFT JOIN ".__INTEZMENYDBNEV.".tankor USING (tankorId)
		    LEFT JOIN ".__INTEZMENYDBNEV.".targy USING (targyId) 
		    WHERE targyNev='osztályfőnöki' AND osztalyId IN (".implode(',', array_fill(0, count($osztalyId), '%u')).")";
	    $ofoTankorId = db_query($q, array(
		'fv' => 'legkorabbiIgazolhatoHianyzasVeg', 'modul' => 'naplo_intezmeny', 'result' => 'idonly', 'values' => $osztalyId
	    ), $lr);

	    // Legutóbbi osztályfőnöki óra dátuma - Jó ez? Több osztály esetén az egyikét adja meg... nem? Ennek így nincs is értelme...
	    if (is_array($ofoTankorId) && count($ofoTankorId)>0) {
        	$q = "SELECT dt FROM ".__TANEVDBNEV.".ora WHERE tankorId IN (".implode(',', array_fill(0, count($ofoTankorId), '%u')).")
                    AND dt<'"._IGAZOLAS_BEIRAS_HATARIDO."'
		    AND tipus NOT LIKE 'elmarad%%'
                    ORDER BY dt DESC LIMIT 1";
		$ofoOraDt = db_query($q, array('fv' => 'legkorabbiIgazolhatoHianyzasVeg', 'modul' => 'naplo', 'result' => 'value', 'values' => $ofoTankorId));
	    } else {
		$ofoOraDt = '';
	    }
        if ($olr == '') db_close($lr);

        if ($ofoOraDt != '' && strtotime($ofoOraDt) > strtotime(_LEGKORABBI_IGAZOLHATO_HIANYZAS)) {
            return $ofoOraDt;
        } else {
	    //$_SESSION['alert'][] = 'info:wrong_data:Nem volt még osztályfőnöki óra!';
            return _LEGKORABBI_IGAZOLHATO_HIANYZAS;
        }

    }

    function getNemIgazolhatoDt($diakId, $munkatervIds, $ofoOraDt = '', $olr = '') {

	global $_TANEV;

        if ($olr == '') $lr = db_connect('naplo');
        else $lr = $olr;

	if (!is_array($munkatervIds) || count($munkatervIds)==0) $munkatervIds = array(1); // a default 

        if ($ofoOraDt == '') {
	    // A diák osztályai
	    $q = "SELECT DISTINCT osztalyId FROM ".__INTEZMENYDBNEV.".osztalyDiak
		    WHERE diakId=%u AND beDt<='%s'
		    AND (kiDt IS NULL OR kiDt >= '%s')";
	    $v = array($diakId, $_TANEV['zarasDt'], $_TANEV['kezdesDt']);
	    $diakOsztalyId = db_query($q, array('fv' => 'getNemIgazolhatoDt-1', 'modul' => 'naplo_intezmeny', 'result' => 'idonly', 'values' => $v), $lr);
	    if (is_array($diakOsztalyId)) $ofoOraDt = legkorabbiIgazolhatoHianyzasVeg($diakOsztalyId, $lr); // ugye tudjuk, hogy ez nem feltétlenül az osztályfőnöki órát jelenti!
	} 

	if ($ofoOraDt != '') {
            // Hianyzott-e az ofő óra napján
            $q = "SELECT COUNT(dt) FROM ".__TANEVDBNEV.".hianyzas
                    WHERE diakId = %u
                    AND tipus = 'hiányzás'
                    AND dt = '%s'";
	    $v = array($diakId, $ofoOraDt);
	    $num = db_query($q, array('fv' => 'getNemIgazolhatoDt-2', 'modul' => 'naplo', 'result' => 'value', 'values' => $v), $lr);
            if ($num > 0) {
                // Ha hiányzott, mikor volt előtte utoljára iskolában

                // A tanuló hiányzásainak listája
		// LEZÁRT TANÉVNÉL EZ PROBLÉMÁS!
                $q = "CREATE TEMPORARY TABLE ".__INTEZMENYDBNEV.".tanulo_hianyzasai
                        SELECT DISTINCT dt FROM hianyzas
                        WHERE diakId = %u
                        AND tipus = 'hiányzás'";
		$v = array($diakId);
                $r = db_query($q, array('fv' => 'getNemIgazolhatoDt-3', 'modul' => 'naplo', 'values' => $v), $lr);
               // első nem hiányzásos tanítási nap...
                $q = "SELECT nap.dt
                        FROM nap LEFT JOIN ".__INTEZMENYDBNEV.".tanulo_hianyzasai USING (dt)
                        WHERE tanulo_hianyzasai.dt IS NULL
                        AND nap.dt < '%s'
                        AND nap.tipus IN ('tanítási nap','speciális tanítási nap')
			AND munkatervId IN (".implode(',', array_fill(0, count($munkatervIds), '%u')).")
                        ORDER BY nap.dt DESC
                        LIMIT 1";
		$v = mayor_array_join(array($ofoOraDt),$munkatervIds);
                $r = db_query($q, array('fv' => 'getNemIgazolhatoDt-4', 'modul' => 'naplo', 'result' => 'indexed', 'values' => $v), $lr);
                if (count($r) > 0) {
                    // ha nem az első tanítási napig hiányzik...
                    $dt = $r[0]['dt'];
                } else {
                    // ha az első tanítási napig hiányzik...
                    $dt = date('Y-m-d',strtotime('last day',strtotime($_TANEV['kezdesDt'])));
                }

                // Az ideiglenes táblák a kapcsolat zárásakor törlődnek!
                // Meglevő kapcsolat használatakor azonban törölni kell.
                if ($olr != '') db_query("DROP TABLE ".__INTEZMENYDBNEV.".tanulo_hianyzasai", array('fv' => 'getNemIgazolhatoDt-4', 'modul' => 'naplo'), $lr);

            } else {
                // Ha nem hiányzott, akkor az ofő óra napja előtti nap a keresett dt
                $dt = date('Y-m-d',strtotime('last days',strtotime($ofoOraDt)));
            }

        } else { // ide be sem megyünk!
            // Ha nem volt osztályfőnöki óra - vagy inkább legkorábbi igazolható hiányzás vég - azaz nincs osztály?
            $dt = date('Y-m-d', strtotime('last days', strtotime($_TANEV['kezdesDt'])));
        }

        if ($olr == '' ) db_close($lr);

        return $dt;

    }

    function getDiakHianyzasStat($diakId, $SET = array('tankorIds'=>null, 'tanev'=>__TANEV)) {
	if (is_array($SET['tankorIds'])) $tankorIds = $SET['tankorIds'];
	else 
	    return false;
	    
	$tanevDbNev = tanevDbNev(__INTEZMENY,$SET['tanev']);
	
	$q = "SELECT tankorId,COUNT(*) AS db FROM `%s`.hianyzas LEFT JOIN `%s`.ora USING (oraId) 
		WHERE diakId=%u AND hianyzas.tipus='hiányzás' AND hianyzas.dt<=CURDATE() GROUP BY tankorId";
	$v = array($tanevDbNev, $tanevDbNev, $diakId);
	return db_query($q, array(
	    'fv' => 'getDiakHianyzasStat', 'modul' => 'naplo_intezmeny', 'result' => 'assoc', 'keyfield' => 'tankorId', 'values' => $v
	));
    
    }

    function _relevance($x,$base) {
        return ($x<20)  ? ($base-sqrt($x)/(sqrt(20)/(0+$base))) : 0; // a képlet normalizálható, de továbbfejleszés miatt ilyen formájú
    }
    function getDarabDiakHianyzas($dt='') {

        if ($dt=='') $dt = date('Y-m-d');
        //$q = "select count(*) AS db from (select diakId,count(*) AS db FROM hianyzas WHERE dt='%s' AND tipus LIKE 'hi_nyz_s' GROUP BY diakId HAVING db>2) AS stat";

	/* Mai hiányzók száma (súlyozottan) */
        $stamp1=strtotime($dt);
        $stamp2=mktime(0,0,1,date('m'),date('d'),date('y'));
        $relevance = floor( ($stamp2-$stamp1) / (3600*24) )+0;
	$q = "select count(*) AS dbDiak, dbOra from (select diakId,count(*) AS dbOra FROM hianyzas WHERE dt='%s' AND tipus = 'hiányzás' GROUP BY diakId) AS stat GROUP BY dbOra";
        $v = array($dt);
        $R = db_query($q, array('fv' => 'getDarabDiakHianyzas', 'modul' => 'naplo', 'result' => 'assoc', 'keyfield'=>'dbDiak', 'values' => $v));
	$hianyzo=0;
        foreach ($R as $dbDiak => $D) {
            if ($D['dbOra']==1) $hianyzo += _relevance($relevance,0.8)*$dbDiak;
            elseif ($D['dbOra']<=2) $hianyzo +=  _relevance($relevance,0.95)*$dbDiak;
            elseif ($D['dbOra']<=3) $hianyzo +=  _relevance($relevance,1)*$dbDiak;
            else $hianyzo += $dbDiak;
        }
	$RESULT['hianyzokSulyozva'] = floor($hianyzo);
	/* --- */
	/* Mai hiányzók száma (súlyozottan) */
	$q = "select count(*) AS dbDiak FROM (select diakId,count(*) AS dbOra FROM hianyzas WHERE dt='%s' AND tipus LIKE 'hiányzás' AND igazolas LIKE 'tanulmányi verseny' GROUP BY diakId) AS stat";
        $v = array($dt);
        $R = db_query($q, array('fv' => 'getDarabDiakHianyzas', 'modul' => 'naplo', 'result' => 'value', 'values' => $v));
	$RESULT['hianyzokTanulmanyin'] = $R;
	/* --- */
	/* Az órák hány százalékán volt hiányzó */
	if ($RESULT['hianyzokSulyozva']!=0) {
	    $q = "select FORMAT((select count( DISTINCT oraId ) FROM hianyzas WHERE tipus LIKE 'hi_nyz_s' AND dt='%s')*100/count(*),2) AS dbHianyzas FROM ora WHERE ora.tipus NOT IN ('elmarad','elmarad máskor') AND dt='%s'";
	    $v = array($dt,$dt);
	    $RESULT['oranHianyzasSzazalek'] = db_query($q, array('fv' => 'getDarabDiakHianyzas', 'modul' => 'naplo', 'result' => 'value', 'values' => $v));
	} else {
	    $RESULT['oranHianyzasSzazalek'] = 0;
	}
	// ---

	return $RESULT;
    }

    function getDiakHozottHianyzas($diakId=null,$SET = array('tanev'=>__TANEV, 'igDt'=>null)) { // tanév-et nem vesszük figyelembe
	$RESULT = false;
	if (isset($diakId) && is_numeric($diakId))
	{
	    $tanev = (isset($SET['tanev']) && $SET['tanev']!=__TANEV) ? $SET['tanev'] : __TANEV;
	    $tanevDbNev = tanevDbNev(__INTEZMENY, $tanev);

	    $v = array($diakId);
	    if (isset($SET['igDt'])) {
		$W = " AND dt<='%s'";
		$v[] = $SET['igDt'];
	    }

	    $q = "SELECT * FROM `$tanevDbNev`.`hianyzasHozott` WHERE diakId=%u".$W." GROUP BY statusz";
	    $R = db_query($q, array('fv'=>'getDiakHozottHianyzas','modul'=>'naplo','result'=>'indexed','values'=>$v));
	    /* ReIndex */
	    for ($i=0; $i<count($R); $i++) {
		if ($R[$i]['dbHianyzas']>0) { // igen, SQL - ben is lehetne összeadni, a továbbfejlesztés miatt van így.
//		    $_felev = getFelevByDt($R[$i]['dt']);
//		    $RESULT[$_felev][$R[$i]['statusz']] += $R[$i]['dbHianyzas'];
//		    if ($_felev==1) $RESULT[($_felev+1)][$R[$i]['statusz']] += $R[$i]['dbHianyzas']; //+1 ? inkább a szemeszter vég dátuma előttieket kéne összeadni azt csókolom...
		    $RESULT[$R[$i]['statusz']]['db'] += $R[$i]['dbHianyzas'];
		}
	    }
	    
	}
	return $RESULT;
    }

    function getDiakKretaHianyzas($diakId, $SET = array('preprocess'=>'stat','tanev'=>__TANEV, 'igDt'=>null, 'tolDt'=>null)) {
	$RESULT = false;
	if (isset($diakId) && is_numeric($diakId))
	{
	    $tanev = (isset($SET['tanev']) && $SET['tanev']!=__TANEV) ? $SET['tanev'] : __TANEV;
	    $tanevDbNev = tanevDbNev(__INTEZMENY, $tanev);

	    $v = array($diakId);
	    $W = '';
	    if (isset($SET['tolDt'])) {
		$W .= " AND dt>='%s'";
		$v[] = $SET['tolDt'];
	    }
	    if (isset($SET['igDt'])) {
		$W .= " AND dt<='%s'";
		$v[] = $SET['igDt'];
	    }

	    $q = "SELECT * FROM `$tanevDbNev`.`hianyzasKreta` WHERE diakId=%u".$W."";
	    $R = db_query($q, array('debug'=>false,'fv'=>'getDiakKretaHianyzas','modul'=>'naplo','result'=>'indexed','values'=>$v));
	    /* ReIndex */
	    if ($SET['preprocess'] == 'stat') {	
		for ($i=0; $i<count($R); $i++) {
		//if ($R[$i]['dbHianyzas']>0) { // igen, SQL - ben is lehetne összeadni, a továbbfejlesztés miatt van így.
		    $_igazoltStr = $R[$i]['kretaStatusz'] == 'igen' ? 'igazolt':'igazolatlan';
		    $RESULT[$R[$i]['tipus']][$_igazoltStr]['db']++;
		    if ($R[$i]['tipus']=='késés') $RESULT[$R[$i]['tipus']][$_igazoltStr]['perc']+=$R[$i]['perc'];
		//}
		}
	    } elseif ($SET['preprocess']=='naptar') {
		for ($i=0; $i<count($R); $i++) {
		    $RESULT[$R[$i]['diakId']][$R[$i]['dt']][$R[$i]['ora']][] = $R[$i];
		}
	    } else {
		$RESULT = $R;
	    }
	}
	return $RESULT;

    }

    function kretaIgazolas2mayor($key) { // -- TODO
	$KRETA2MAYOR= array(
		'Szülői igazolás'=>'szülői',
		'Orvosi igazolás'=>'orvosi',
		'Egyéb'=>'egyéb',
		'Iskolai engedély'=>'igazgatói',
		'Iskolaérdekű távollét'=>'igazgatói',
		'Kikérő' => 'igazgatói',
		'Pályaválasztási célú igazolás'=>'pályaválasztás',
		'Szolgáltatói igazolás' => 'hatósági',
		'Hivatalos távollét' => 'egyéb',
		'Táppénz' => 'egyéb');
	return $KRETA2MAYOR[$key]!='' ? $KRETA2MAYOR[$key] : 'egyéb';
    }

    function getKretaIgazolasOsszegzo($diakId) { // -- TODO
	$q = "SELECT tipus, kretaIgazolas, count(distinct dt) AS db, count(*) AS dbBejegyzes FROM hianyzasKreta WHERE diakId=%u AND kretaStatusz='igen' GROUP BY tipus,kretaIgazolas ORDER BY tipus, kretaIgazolas";
	$v = array($diakId);
	$R = db_query($q, array('fv'=>'getKretaIgazolasOsszegzo','modul'=>'naplo','result'=>'indexed','values'=>$v));
	return reindex($R,array(kretaIgazolas,tipus));
    }

?>
