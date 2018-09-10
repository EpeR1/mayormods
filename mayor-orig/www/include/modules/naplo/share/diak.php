<?php

    if (file_exists("lang/$lang/module-naplo/share/diak.php")) {
        require_once("lang/$lang/module-naplo/share/diak.php");
    } elseif (file_exists('lang/'._DEFAULT_LANG.'/module-naplo/share/diak.php')) {
        require_once('lang/'._DEFAULT_LANG.'/module-naplo/share/diak.php');
    }

    function getVegzoDiakok($SET = array('tanev' => __TANEV)) {

	global $_TANEV;

	// tanév adatai
	$tanev = readVariable($SET['tanev'], 'numeric unsigned', __TANEV);
	if ($tanev != __TANEV) $tanevAdat = getTanevAdat($tanev, $lr);
	else $tanevAdat = $_TANEV;

	// ez volt eredetileg:
	/*$q = "SELECT diakId FROM osztalyDiak LEFT JOIN osztaly USING (osztalyId) 
		WHERE beDt <= '%s' AND (kiDt IS NULL OR '%s' <= kiDt)
		GROUP BY diakId HAVING MAX(vegzoTanev) = %u";*/
	// le kell kéredezni a megadott tanévben végzős OSZTÁLYOK diákjait, kivéve azokat, akik később végzős osztályok tagjai
	$q = "SELECT diakId FROM osztalyDiak LEFT JOIN osztaly USING (osztalyId) 
		WHERE beDt <= '%s' AND (kiDt IS NULL OR '%s' <= kiDt)
		AND diakId NOT IN (
		    SELECT diakId FROM osztalyDiak LEFT JOIN osztaly USING (osztalyId) WHERE vegzoTanev>%u
		)
		GROUP BY diakId HAVING MAX(vegzoTanev) = %u";
	return db_query($q, array('fv' => 'getVegzosDiakok', 'modul' => 'naplo_intezmeny', 'values' => array($tanevAdat['zarasDt'], $tanevAdat['zarasDt'],$tanev, $tanev), 'result' => 'idonly'));
    }

    function getDiakokOld($SET = array('osztalyId' => null, 'tanev' => __TANEV, 'tolDt' => null, 'igDt' => null, 'override' => false, 'statusz' => null, 'result'=>''), $olr = null) {

	global $_TANEV;

	if ($SET['csakId']===true ||  $SET['result'] == 'csakId') $SET['result'] = 'idonly';
	$osztalyId = readVariable($SET['osztalyId'], 'numeric unsigned', null);
	$tolDt = readVariable($SET['tolDt'], 'datetime', null);
	$igDt = readVariable($SET['igDt'], 'datetime', null);
	// Az adott tanév elejének és végének lekérdezése
	$tanev = readVariable($SET['tanev'], 'numeric unsigned', __TANEV);
	initTolIgDt($tanev, $tolDt, $igDt, $SET['override']);

	if ($tanev != __TANEV) $tanevAdat = getTanevAdat($tanev, $lr);
	else $tanevAdat = $_TANEV;

	$KIBEDT = "beDt <= '%2\$s' AND (kiDt IS NULL OR '%3\$s' <= kiDt) AND ";

	// A lekérdezendő diákok státusza
	if (!is_array($SET['statusz']) || count($SET['statusz']) == 0)	{
	    if ($tanevAdat['statusz'] == 'aktív') {
		$Statusz = array('jogviszonyban van','magántanuló','vendégtanuló');
	    } else {
		$Statusz = array('jogviszonyban van','magántanuló','vendégtanuló','jogviszonya felfüggesztve','jogviszonya lezárva');
		// ebben az esetben kit érdekel a diák kilépésének ideje???
		$KIBEDT = ''; $v = array();
	    }
	} else {
	    $Statusz = readVariable($SET['statusz'], 'enum', null, 
		array('felvételt nyert','jogviszonyban van','magántanuló','vendégtanuló','jogviszonya felfüggesztve','jogviszonya lezárva')
	    );
	}

	// Intézményi adatbázis neve
	$intezmenyDb = intezmenyDbNev(__INTEZMENY);
/*
    Szerintem ez nem jó így.
    1. a tol-igDt alapján a diakJogviszony táblát kellene néznünk - közben volt-e jogviszonyban, vagy magántanulóként.
    2. A having végén a jogviszony miért is?

*/
	if (!isset($osztalyId)) {
	    if ($SET['result'] == 'idonly') $mezok = 'diakId';
	    else $mezok = "diakId, TRIM(CONCAT_WS(' ',viseltNevElotag, ViseltCsaladiNev, viseltUtoNev)) AS diakNev, 
			statusz, MAX(osztaly.vegzoTanev) AS maxVegzoTanev";
	    $q = "SELECT $mezok
		    FROM %2\$s.diak 
		    LEFT JOIN %2\$s.osztalyDiak USING (diakId)
		    LEFT JOIN %2\$s.osztaly USING (osztalyId)
		    WHERE diak.kezdoTanev <= %u
		    AND statusz IN ('".implode("','", $Statusz)."')
		    GROUP BY diakId
		    HAVING %1\$u <= maxVegzoTanev OR maxVegzoTanev IS NULL OR statusz IN ('jogviszonyban van','magántanuló')
		    ORDER BY viseltCsaladiNev,viseltUtonev";
	    $v = array($tanev, $intezmenyDb);
	} else {
	    if ($SET['result'] == 'idonly') $mezok = 'diakId';
	    else $mezok = "osztalyDiak.diakId, TRIM(CONCAT_WS(' ', viseltNevElotag, viseltCsaladinev, viseltUtonev)) AS diakNev, 
			    statusz, DATE_FORMAT(beDt,'%%Y-%%m-%%d') AS beDt, DATE_FORMAT(kiDt,'%%Y-%%m-%%d') AS kiDt";
	    $q = "SELECT $mezok
			FROM %1\$s.osztalyDiak LEFT JOIN %1\$s.diak USING (diakId)
			WHERE $KIBEDT 
			    osztalyDiak.osztalyId=%4\$u
			AND statusz IN ('".implode("','", $Statusz)."')
			ORDER BY ViseltCsaladiNev,ViseltUtonev";
	    $v = array($intezmenyDb, $igDt, $tolDt, $osztalyId);

	}

	if ($SET['result'] == 'idonly')
	    return db_query($q, array('fv' => 'getDiakok', 'modul' => 'naplo_intezmeny', 'values' => $v, 'result' => 'idonly'), $olr);
	elseif ($SET['result'] == 'assoc')
	    return db_query($q, array('fv' => 'getDiakok', 'modul' => 'naplo_intezmeny', 'result' => 'assoc', 'keyfield' => 'diakId', 'values' => $v), $olr);
	else 
	    return db_query($q, array('fv' => 'getDiakok', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => $v), $olr);

    }

    function getDiakok($SET = array('osztalyId' => null, 'tanev' => __TANEV, 'tolDt' => null, 'igDt' => null, 'override' => false, 'statusz' => null, 'result'=>'', 'extraAttrs'=>''), $olr = null) {

	global $_TANEV;

	$result = readVariable($SET['result'], 'enum', 'indexed', array('indexed','assoc','idonly','keyvaluepair'));
	$osztalyId = readVariable($SET['osztalyId'], 'id', null);
	if (!is_null($osztalyId) && !is_array($osztalyId)) $osztalyId = array($osztalyId); // ha csak egy érték, legyen tömb
	$tolDt = readVariable($SET['tolDt'], 'datetime', null);
	$igDt = readVariable($SET['igDt'], 'datetime', null);
	$tanev = readVariable($SET['tanev'], 'numeric unsigned', __TANEV);
	initTolIgDt($tanev, $tolDt, $igDt, $SET['override']);
	if ($tanev != __TANEV) $tanevAdat = getTanevAdat($tanev, $lr);
	else $tanevAdat = $_TANEV;

	$extraAttrs = ($SET['extraAttrs']!='') ? ','.$SET['extraAttrs'] : '';

	// A lekérdezendő diákok státusza
	if (!is_array($SET['statusz']) || count($SET['statusz']) == 0)	{
	    if ($tanevAdat['statusz'] == 'aktív') {
		$Statusz = array('jogviszonyban van','magántanuló','vendégtanuló');
	    } else {
		$Statusz = array('jogviszonyban van','magántanuló','vendégtanuló','jogviszonya felfüggesztve','jogviszonya lezárva');
	    }
	} else {
	    $Statusz = readVariable($SET['statusz'], 'enum', null, 
		array('felvételt nyert','jogviszonyban van','magántanuló','vendégtanuló','jogviszonya felfüggesztve','jogviszonya lezárva')
	    );
	}

	// jogviszony szerinti lekérdezés - szóbajövő diakId-k
	// Ez MySQL 5.1.41 alatt (Ubuntu) működött, de 5.0.32-vel (Debian Etch?) nem
/*	$q = "SELECT diakId FROM diakJogviszony AS dj1 WHERE statusz IN ('".implode("','", $Statusz)."') AND dt <= '%s'
		GROUP BY diakId 
		HAVING (SELECT COUNT(*) FROM diakJogviszony AS dj2 WHERE diakId = dj1.diakId AND MAX(dj1.dt) < dt AND dt <= '%s') = 0";
*/
	// Ez működik 5.0.32-vel is
        $q = "SELECT diakId FROM diakJogviszony WHERE dt <= '%s' GROUP BY diakId HAVING
                MAX(IF(statusz IN ('".implode("','", $Statusz)."'),dt,'0000-00-00')) >
                MAX(IF(statusz NOT IN ('".implode("','", $Statusz)."') AND dt <= '%s',dt,'0000-00-00'))";
	$v = array($igDt, $tolDt);
	$diakIds = db_query($q, array('fv' => 'getDiakok', 'modul' => 'naplo_intezmeny', 'values' => $v, 'result' => 'idonly'));
	if (!is_array($diakIds)) return false; // hiba
	if (count($diakIds) == 0) return array(); // nincs ilyen diák
        // A felvételt nyert státuszú diák ebben a listában nem szerepel
        if (in_array('felvételt nyert', $Statusz)) {
            $FNY_WHERE = " OR statusz = 'felvételt nyert' ";
        }
	// Névsor
	if (!isset($osztalyId)) {
	    $q = "SELECT diakId, TRIM(CONCAT_WS(' ',viseltNevElotag, ViseltCsaladiNev, viseltUtoNev)) AS diakNev, statusz AS aktualisStatusz 
		    $extraAttrs
		    FROM diak 
		    WHERE diak.kezdoTanev <= %u AND (diakId IN (".implode(",", $diakIds).") $FNY_WHERE )
		    ORDER BY viseltCsaladiNev,viseltUtonev";
	    $v = array($tanev, $intezmenyDb);
	} else {

	    if ($tanev!='') 	
		$qNs = ", diakNaploSorszam(osztalyDiak.diakId,$tanev,osztalyDiak.osztalyId) AS diakNaploSorszam";
	    else
		$qNs = '';

	    $q = "SELECT DISTINCT osztalyDiak.diakId, TRIM(CONCAT_WS(' ', viseltNevElotag, viseltCsaladinev, viseltUtonev)) AS diakNev, statusz AS aktualisStatusz
			$qNs $extraAttrs
			FROM osztalyDiak LEFT JOIN diak USING (diakId)
			WHERE beDt <= '%s' AND (kiDt IS NULL OR '%s' <= kiDt) AND 
			    osztalyDiak.osztalyId IN (". implode(',', $osztalyId) .")
			AND (diakId IN (".implode(",", $diakIds).") $FNY_WHERE )
			ORDER BY ViseltCsaladiNev,ViseltUtonev";
	    $v = array($igDt, $tolDt);
	}

	return db_query($q, array('fv' => 'getDiakok', 'modul' => 'naplo_intezmeny', 'result' => $result, 'keyfield' => 'diakId', 'values' => $v), $olr);

    }



    function getDiakokByOsztalyId($IDs, $SET = array('tanev' => __TANEV, 'tolDt' => null, 'igDt' => null, 'result' => '', 'statusz' => array('jogviszonyban van','magántanuló'))) {
	////////////////////////////////////////////////////////////////
	// !!! Ez a függvény csak a diák aktuális státuszát nézi! !!! //
	////////////////////////////////////////////////////////////////
	if (!is_array($IDs) || count($IDs) == 0) return false;

	if ($SET['result']=='assoc' || $SET['result']=='multiassoc') $SET['result'] = 'multiassoc';
	else $SET['result'] = 'indexed';

	if ($SET['tanev']!='') $tanev = $SET['tanev']; else $tanev=__TANEV;
	// Az adott tanév elejének és végének lekérdezése
	if ($tanev != __TANEV) $tanevAdat = getTanevAdat($tanev, $lr);
	else { global $_TANEV; $tanevAdat = $_TANEV; }

//	$tanevKezdes = $tanevAdat['kezdesDt']; $tanevZaras = $tanevAdat['zarasDt']; $time = time();
	$tolDt = readVariable($SET['tolDt'], 'date', $tanevAdat['kezdesDt']);
	$igDt = readVariable($SET['igDt'], 'date', $tanevAdat['zarasDt']);

	if (!is_array($SET['statusz']) || count($SET['statusz']) == 0) 
	    if ($tanevAdat['statusz'] == 'aktív') $SET['statusz'] = array('jogviszonyban van','magántanuló');
	    else $SET['statusz'] = array('jogviszonyban van','magántanuló','jogviszonya felfüggesztve','jogviszonya lezárva');
	// Intézményi adatbázis neve
	$intezmenyDb = intezmenyDbNev(__INTEZMENY);
	$RESULT = false;

	$q = "SELECT osztalyDiak.diakId AS diakId, TRIM(CONCAT_WS(' ', viseltNevElotag, viseltCsaladinev, viseltUtonev)) AS diakNev, 
			    osztalyId, DATE_FORMAT(beDt,'%%Y-%%m-%%d') AS beDt, DATE_FORMAT(kiDt,'%%Y-%%m-%%d') AS kiDt
			FROM `%s`.osztalyDiak LEFT JOIN `%s`.diak USING (diakId) 
			WHERE beDt <= '%s'
			    AND (kiDt IS NULL OR kiDt >= '%s')
			    AND osztalyDiak.osztalyId IN (".implode(',', array_fill(0, count($IDs), '%u')).")
			    AND statusz IN ('".implode("','", $SET['statusz'])."')
			ORDER BY ViseltCsaladiNev,ViseltUtonev";
//	array_unshift($IDs, $intezmenyDb, $intezmenyDb, $tanevZaras, $tanevKezdes);
	array_unshift($IDs, $intezmenyDb, $intezmenyDb, $igDt, $tolDt);
	return db_query($q, array('result' => $SET['result'], 'fv' => 'getDiakokByOsztalyId', 'modul' => 'naplo_intezmeny', 'values' => $IDs, 'keyfield' => 'osztalyId'));


    }

    /*
	Az előző függvény hiányosságait kiküszöbölő függvény, ami a diakJogviszony táblát is figyelembeveszi

	$SET['tanev'|'tolDt'|'igDt'|'statusz'|'statuszonkent']

	return[ diakId ][ 'diakId'|'diakNev' ]
	return[ diakId ][ 'osztalyDiak' ][][ 'beDt'|'kiDt' ] -- DESC !!
	return[ diakId ][ 'statusz' ][][ 'statusz'|'dt' ]    -- DESC !!
	return['jogviszonyban van'|...|'jogviszonya lezárva'][]

    */

    /* EZ ETTŐL MÉG GLOBAL SCOPE!!!! Kéretik normális nevet adni neki!!!*/
    function _tmp11($value) { return array('beDt' => $value['beDt'], 'kiDt' => $value['kiDt']); }
    function _tmp22($value) { return array('statusz' => $value['statusz'], 'dt' => $value['dt']); }

    function getDiakokByOsztaly($osztalyId, $SET = array()) {

	global $_TANEV;

	$tanev = readVariable($SET['tanev'], 'numeric unsigned', __TANEV);
	if ($tanev != __TANEV) $_TA = getTanevAdat($tanev, $lr);
	else $_TA = $_TANEV;

	$orderBy = ($SET['orderBy']=='naploSorszam') ? " ORDER BY diakNaploSorszam(osztalyDiak.diakId,$tanev,osztalyDiak.osztalyId) " : "ORDER BY ViseltCsaladiNev, ViseltUtonev, beDt DESC";
	$tolDt = readVariable($SET['tolDt'], 'datetime', $_TA['kezdesDt']);
	$igDt = readVariable($SET['igDt'], 'datetime', $_TA['zarasDt']);
	initTolIgDt($tanev, $tolDt, $igDt);
	$statusz = readVariable($SET['statusz'], 'enum', null, array('jogviszonyban van','magántanuló','vendégtanuló','jogviszonya felfüggesztve','jogviszonya lezárva'));
	if (!is_array($statusz) || count($statusz) == 0) $statusz = array('jogviszonyban van','magántanuló','vendégtanuló','jogviszonya felfüggesztve','jogviszonya lezárva');
	$statuszonkent = readVariable($SET['statuszonkent'],'bool',true);
	$felveteltNyertEkkel = readVariable($SET['felveteltNyertEkkel'],'bool',false);
	$intezmenyDb = intezmenyDbNev(__INTEZMENY);

	// Az összes diák lekérdezése (esetleg lehet majd bent többször is az osztályban!)
	$q = "SELECT osztalyDiak.diakId AS diakId, TRIM(CONCAT_WS(' ', viseltNevElotag, viseltCsaladinev, viseltUtonev)) AS diakNev, 
			    osztalyId, DATE_FORMAT(beDt,'%%Y-%%m-%%d') AS beDt, DATE_FORMAT(kiDt,'%%Y-%%m-%%d') AS kiDt, statusz AS aktualisStatusz
			FROM `%s`.osztalyDiak LEFT JOIN `%s`.diak USING (diakId) 
			WHERE beDt <= '%s' AND (kiDt IS NULL OR kiDt >= '%s')
			    AND osztalyDiak.osztalyId=%u ".$orderBy;
	$v = array($intezmenyDb, $intezmenyDb, $igDt, $tolDt, $osztalyId);
	$ret1 = db_query($q, array('result' => 'multiassoc', 'keyfield' => 'diakId', 'fv' => 'getDiakokByOsztaly', 'modul' => 'naplo_intezmeny', 'values' => $v));

	if (is_array($ret1) && count($ret1)>0) $diakIds = array_keys($ret1);
	else $diakIds = array();

	if ($statuszonkent) $return = array('jogviszonyban van' => array(), 'magántanuló' => array(), 'vendégtanuló' => array(), 'jogviszonya felfüggesztve' => array(), 'jogviszonya lezárva' => array(), 'felvételt nyert'=>array());
	else $return = array();

	// Ha nincs tagja az osztálynak még/már
	if (!is_array($diakIds) || count($diakIds) == 0) return $return;

	// Jogviszonyadatok lekérdezése
	$q = "SELECT * FROM diakJogviszony WHERE diakId IN (".implode(', ', array_fill(0, count($diakIds), '%u')).") AND dt <='$igDt' ORDER BY diakId, dt DESC";
	array_push($diakIds, $igDt);
	$ret2 = db_query($q, array('result' => 'multiassoc', 'keyfield' => 'diakId', 'fv' => 'getDiakokByOsztaly', 'modul' => 'naplo_intezmeny', 'values' => $diakIds ));
	/* --TODO, ellenőrizni ret1 és ret2-t! 
        Warning: array_map() [function.array-map]: Argument #2 should be an array in /var/mayor/www/include/modules/naplo/share/diak.php on line 187
	Warning: Cannot modify header information - headers already sent by (output started at /var/mayor/www/include/modules/naplo/share/diak.php:187) in /var/mayor/www/policy/private/naplo/nyomtatas/osztalyozonaplo-pre.php on line 124
	*/
	if (is_array($ret1)) {
	    foreach ($ret1 as $diakId => $stat) {
		$_felveteltNyert = ($stat[0]['aktualisStatusz']=='felvételt nyert')?true:false;
		// Szűrés a státuszra
		// Ha az utolsó státusz jó, akkor ok (order by dt desc)
		$i = 0;
		if ($felveteltNyertEkkel===true) // ha a paraméter listában nincs felvételt nyert felsorolva, akkor miért engedjük meg? Nem értem.
		    $ok = in_array($ret2[$diakId][$i]['statusz'], $statusz) || $_felveteltNyert;
		else 
		    $ok = in_array($ret2[$diakId][$i]['statusz'], $statusz);
		// addig megyünk visszafele, amíg
		//     - nem $ok (még nem találtunk megfelelő státuszt)
		//     - van még statusz bejegyzés
		//     - az aktuális bejegyzés dátuma > tolDt (az előtte lévők nem érvényesek a megadott időszakra)
		while (!$ok && strtotime($ret2[$diakId][$i]['dt'])>strtotime($tolDt) && $i < count($ret2[$diakId])-1) {
		    $i++;
		    $ok = in_array($ret2[$diakId][$i]['statusz'], $statusz);
		}
		if ($ok) { // Ha az adott időszakban volt a megadott státuszok valamelyikében
		    $return[$diakId] = array('diakId' => $ret1[$diakId][0]['diakId'], 'diakNev' => $ret1[$diakId][0]['diakNev']);
		    $return[$diakId]['osztalyDiak'] = @array_map('_tmp11', $ret1[$diakId]);
		    if ($_felveteltNyert)
		    	$return[$diakId]['statusz'][] = array('statusz'=>'felvételt nyert');
		    else
			$return[$diakId]['statusz'] = @array_map('_tmp22', $ret2[$diakId]);
		    if ($statuszonkent) {
			for ($i = 0; $i < count($return[$diakId]['statusz']); $i++) {
			    if (!in_array($diakId, $return[ $return[$diakId]['statusz'][$i]['statusz'] ])) $return[ $return[$diakId]['statusz'][$i]['statusz'] ][] = $diakId;
			    if (strtotime($tolDt) >= strtotime($return[$diakId]['statusz'][$i]['dt'])) break;
			}
		    }
		}
	    }
	}
	return $return;

    }

    function getDiakokById($IDs, $olr = '') {
	// Visszaadjuk a statusz-t is, de dátum nélkül ez nem túl értelemes... de a diakSelect-ben kell...
	if (!is_array($IDs) || count($IDs) == 0) return false;
	$q = "SELECT diakId, TRIM(CONCAT_WS(' ',viseltNevElotag, ViseltCsaladiNev, viseltUtoNev)) AS diakNev, statusz, statusz as aktualisStatusz
		  FROM ".__INTEZMENYDBNEV.".diak WHERE diakId IN (".implode(',',$IDs).") ORDER BY viseltcsaladinev,viseltutonev";
	return db_query($q, array('keyfield' => 'diakId', 'result' => 'assoc', 'fv' => 'getDiakokById', 'modul' => 'naplo_intezmeny'));
    }

    function getDiakNevById($diakId) {
        $q = "SELECT TRIM(CONCAT_WS(' ', viseltNevElotag, viseltCsaladiNev, viseltUtonev)) AS diakNev 
		FROM `".__INTEZMENYDBNEV."`.`diak` WHERE diakId=%u";
        return db_query($q, array('fv' => 'getDiakNevById', 'modul' => 'naplo_intezmeny', 'result' => 'value', 'values' => array($diakId)));
    }

    function getDiakMindenOsztaly($diakId) {

	$q = "SELECT * FROM osztalyDiak WHERE diakId=%u";
	$osztalyIds = db_query($q, array('fv'=>'getDiakMindenOsztaly', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => array($diakId)));
    
	return $osztalyIds;
    }
    function getDiakOsztalya($diakId, $SET= array('tanev'=>__TANEV,'tolDt'=>'','igDt'=>'', 'result'=>''), $olr='') {

	global $_TANEV;
	//$tolDt = $SET['tolDt']; $igDt=$SET['igDt'];
	$osztalyId = array();

	// Az adott tanév elejének és végének lekérdezése
	if ($SET['tanev']=='') $SET['tanev'] = __TANEV;
	$tanev = $SET['tanev'];
	if ($tanev != __TANEV) $tanevAdat = getTanevAdat($tanev, $olr);
	else $tanevAdat = $_TANEV;

	$tolDt = $SET['tolDt']; $igDt=$SET['igDt']; $tanev = $SET['tanev'];
	if ($tolDt==$igDt && $tolDt=='') {
	    $tolDt = $tanevAdat['kezdesDt']; $igDt = $tanevAdat['zarasDt'];
	} else {
	    initTolIgDt($tanev, $tolDt, $igDt);
	}

	if ($diakId != '') {

	    if ($SET['result']=='csakid' || $SET['result']=='idonly') {
		$q = "SELECT DISTINCT osztalyId FROM osztalyDiak WHERE beDt <= '%s' AND (kiDt IS NULL OR kiDt >= '%s') AND diakId=%u ORDER BY beDt DESC";
		$osztalyId = db_query($q, array('fv' => 'getDiakOsztalya1', 'modul' => 'naplo_intezmeny', 'result' => 'idonly', 'values' => array($igDt, $tolDt, $diakId)), $olr);
	    } elseif ($tanevAdat['statusz'] != 'tervezett') {
		$q = "SELECT osztalyId, osztalyJel, beDt, kiDt, diakNaploSorszam(diakId,%u,osztalyId) AS naploSorszam
			FROM osztalyDiak LEFT JOIN ".tanevDbNev(__INTEZMENY, $tanev).".osztalyNaplo USING (osztalyId)
			WHERE diakId=%u 
			AND beDt <= '%s'
			AND (kiDt IS NULL OR kiDt >= '%s')
			ORDER BY beDt DESC";		
		$osztalyId = db_query($q, array('fv' => 'getDiakOsztalya2', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => array($tanev,$diakId,$igDt, $tolDt)),$olr);
	    } else {
		$q = "SELECT DISTINCT osztalyId FROM osztalyDiak WHERE beDt <= '%s'
			AND (kiDt IS NULL OR kiDt >= '%s') AND diakId=%u ORDER BY beDt DESC";
		$osztalyId = db_query($q, array('fv'=>'getDiakOsztalya3', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => array($igDt, $tolDt, $diakId)),$olr); // biztos ilyen formátumot vársz???
	    }
	}

	return $osztalyId;
    }

    function getDiakokOsztalyai($diakIds, $SET = array('tanev' => __TANEV, 'tolDt' => null, 'igDt' => null, 'result'=>null), $olr='') {

	global $_TANEV;

	$tolDt = $SET['tolDt']; 
	$igDt=$SET['igDt']; 
	if ($SET['tanev']=='') $tanev=__TANEV; else $tanev = $SET['tanev'];
	initTolIgDt($tanev, $tolDt, $igDt);

	$RESULT = array();
	if (is_array($diakIds) && count($diakIds)>0) {
	    if ($SET['result']==='csakId') {
		$FIELDS = 'DISTINCT `osztalyId`';
		$result = 'idonly';
	    } else {
		$FIELDS = '`diakId`, `osztalyId`';
		$result = 'indexed';
	    }
		$q = "SELECT $FIELDS
			FROM osztalyDiak LEFT JOIN diak USING (diakId) WHERE beDt <= '%s'
			AND (kiDt IS NULL OR kiDt >= '%s')
			AND diakId IN (". implode(',', array_fill(0, count($diakIds), '%u')) .") ";
		array_unshift($diakIds, $igDt, $tolDt);
		$R = db_query($q, array('fv' => 'getDiakokOsztalyai', 'result' => $result, 'modul' => 'naplo_intezmeny', 'values' => $diakIds));
	    if ($SET['result']!=='csakId') {
		for ($i = 0; $i < count($R); $i++) {
		    $RESULT[$R[$i]['diakId']][] = $R[$i]['osztalyId'];
		}
	    } else {
		$RESULT = $R;
	    }
	}
	return $RESULT;
    }
    function getDiakAdatById($diakIds, $SET = array('result'=>'indexed', 'keyfield'=>''), $olr='') {

	if ($olr=='') $lr = db_connect('naplo');
	else $lr=$olr;

	if (!is_array($diakIds)) { $diakIds = array($diakIds); $result = 'record'; } 
	else { 
	    if ($SET['result']!='') $result = $SET['result'];
	    else $result = 'indexed'; 
	}

	//$q = "SELECT * FROM ".__INTEZMENYDBNEV.".diak WHERE diakId IN (".implode(',', array_fill(0, count($diakIds), '%u')).")";
	$q = "SELECT diak.*, TRIM(CONCAT_WS(' ',viseltNevElotag, ViseltCsaladiNev, viseltUtoNev)) AS diakNev, TIMESTAMPDIFF(YEAR, diak.szuletesiIdo, CURDATE()) AS diakEletkor, dj.dt AS jogviszonyDt, dj.statusz AS jogviszonyStatusz 
		FROM ".__INTEZMENYDBNEV.".diak LEFT JOIN ".__INTEZMENYDBNEV.".diakJogviszony AS dj 
		ON diak.diakId=dj.diakId AND dj.dt=(SELECT MAX(dt) FROM ".__INTEZMENYDBNEV.".diakJogviszony WHERE dt<=CURDATE() AND diakId=dj.diakId) 
		WHERE diak.diakId IN (".implode(',', array_fill(0, count($diakIds), '%u')).")";
	 
	$r = db_query($q, array('fv' => 'getDiakAdatById', 'modul' => 'naplo_intezmeny', 'result' => $result, 'keyfield'=>$SET['keyfield'],'values' => $diakIds), $lr);

	if ($olr=='') db_close($lr);
	return $r;
    }

    function getDiakBySzulDt($md) 
    {
	if ($md == '') $md = date('m-d');
	$q = "SELECT diakId FROM diak WHERE szuletesiIdo like '%%-%s' AND jogviszonyVege is NULL"; // credits: Neumayer Béla <szepi1971@gmail.com>
	$diakIds = db_query($q, array('fv' => 'getDiakBySzulDt', 'modul' => 'naplo_intezmeny', 'result' => 'idonly', 'values' => array($md)));
	if (count($diakIds)>0) {
		$RET['diak'] = getDiakAdatById($diakIds);
		$RET['diakOsztaly'] = getDiakokOsztalyai($diakIds);
	} else {
		$RET = false;
	}
	return $RET;
    }

    function diakVegzosE($diakId, $SET = array('tanev' => __TANEV, 'dt' => null)) {

	global $_TANEV;

	// tanév adatai
	$tanev = readVariable($SET['tanev'], 'numeric unsigned', __TANEV);
	$dt = readVariable($SET['dt'], 'datetime', date('Y-m-d'));

	$q = "SELECT MAX(vegzoTanev) FROM osztalyDiak LEFT JOIN osztaly USING (osztalyId) 
		WHERE diakId=%u AND beDt<='%s' AND (kiDt IS NULL OR '%s'<=kiDt)";
	$maxVegzoTanev = db_query($q, array('fv' => 'diakVegzosE', 'modul' => 'naplo_intezmeny', 'result' => 'value', 'values' => array($diakId, $dt, $dt)));

	return ($tanev == $maxVegzoTanev);
    }


    function getDiakJogviszony($diakId) {

        $v = array($diakId);
        $q = "SELECT statusz,dt FROM diakJogviszony WHERE diakId=%u ORDER BY dt";
        $ret = db_query($q, array('fv' => 'getDiakJogviszony', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => $v));

        return $ret;

    }

    function getDiakJogviszonyByDt($diakId, $dt) { // dt előtt és után vagy egyenlőt adja vissza, ezért dt - nek lennie kellene...

        $v = array($diakId, $dt);

	$q = "SELECT statusz FROM diak WHERE diakId=%u";
	$ret['aktualis'] = db_query($q, array('fv' => 'getDiakJogviszonyByDt', 'modul' => 'naplo_intezmeny', 'result' => 'value', 'values' => array($diakId)));
	if ($ret['aktualis']=='felvételt nyert') { /* Ha felvételt nyert, nincs bejegyzése a diakJogviszony táblába */
	    $ret['elotte'] = $ret['aktualis']; // ==felvételt nyert
	} else {
    	    $q = "SELECT statusz FROM diakJogviszony WHERE diakId=%u AND dt<'%s' ORDER BY dt DESC LIMIT 1";
    	    $ret['elotte'] = db_query($q, array('fv' => 'getDiakJogviszonyByDt', 'modul' => 'naplo_intezmeny', 'result' => 'value', 'values' => $v));
	}
	/* Az utána statuszt lekérdezem, hisz lehet hogy a jövőbe már be van állítva */
        $q = "SELECT statusz FROM diakJogviszony WHERE diakId=%u AND dt>='%s' ORDER BY dt LIMIT 1";
        $ret['utana'] = db_query($q, array('fv' => 'getDiakJogviszonyByDt', 'modul' => 'naplo_intezmeny', 'result' => 'value', 'values' => $v));

        return $ret;

    }

    function getDiakJogviszonyByDts($diakIds, $dts, $olr='') {
	// lekérdezzük az adott diák/diákok megadott dátum(ok) szerinti jogviszonyait - pl Osztályozónapló
	$ret = array();
	if (is_array($diakIds) && is_array($dts)) {
	    foreach ($diakIds as $diakId) {
		foreach ($dts as $dt) {
		    $q = "SELECT diakId, dt, statusz FROM diakJogviszony WHERE diakId=%u AND dt <= '%s' ORDER BY dt DESC LIMIT 1";
		    $ret[$diakId][$dt] = db_query($q, array('fv' => 'getDiakJogviszonyByDts', 'modul' => 'naplo_intezmeny', 'result' => 'record', 'values' => array($diakId, $dt)), $olr);
		}
	    }
	}
	return $ret;
    }

    function getDiakAdatkezeles($diakId, $filter = null) {

	if (is_numeric($diakId)) {

	    $v = array($diakId);
	    if (is_array($filter)) {
		foreach ($filter as $kulcs => $ertek) {
		    $W[] = " AND kulcs = '%s'";
		    $W[] = " AND ertek = '%s'";
		    $v[] = $kulcs;
		    $v[] = $ertek;
		}
	    } else $W = '';

	    $q = "SELECT * FROM diakAdatkezeles WHERE diakId = %u".implode(' ',$W);
	    $r = db_query($q, array('fv' => 'getDiakAdatkezeles', 'modul' => 'naplo_intezmeny', 'result' => 'assoc', 'keyfield'=>'kulcs', 'values' => $v));

	    return $r;

	} else {
	    return false;
	}

    }


    function getDiakTorzslapszam($diakId, $osztalyId, $SET = array('osztalyJellel' => true, 'tanev'=>__TANEV)) {
        /*
         * Ha van a diak táblában törzslapszám, akkor azt adjuk vissza, ha nincs akkor a diakTorzslapszam tábla megfelelő értékét, vagy ennek hiányában false null értéket.
         * Nem használt függvény (még)
         */

        $q = "SELECT torzslapszam FROM diak WHERE diakId=%u";
        $v = array($diakId);
        $r = db_query($q, array('fv'=>'getDiakTorzslapszam/diak', 'modul'=>'naplo_intezmeny','result'=>'value','values'=>$v));

        if ($r && !is_null($r)) return $r;

        $q = "SELECT torzslapszam FROM diakTorzslapszam WHERE diakId=%u AND osztalyId=%u";
        $v = array($diakId, $osztalyId);
        $r = db_query($q, array('fv'=>'getDiakTorzslapszam/osztaly', 'modul'=>'naplo_intezmeny','result'=>'value','values'=>$v));

        if ($SET['osztalyJellel']) {
            $q = "select concat('/',kezdoTanev, '-', vegzoTanev+1,'/',jel) from osztaly where osztalyId = %u";
            $v = array($osztalyId);
            $r .= db_query($q, array('fv'=>'getDiakTorzslapszam/osztalyJel', 'modul'=>'naplo_intezmeny','result'=>'value','values'=>$v));
        }

        return $r;
    }


    function getDiakokTorzslapszamaByOsztalyId($osztalyId, $SET = array('osztalyJellel' => true, 'tanev'=>__TANEV)) {
        /*
         * Ha van a diak táblában törzslapszám, akkor azt adjuk vissza, ha nincs akkor a diakTorzslapszam tábla megfelelő értékét, vagy ennek hiányában false null értéket.
         * Nem használt függvény (még)
         */

        // kérdezzük le az osztály valaha volt összes tagját:
        $q = "SELECT count(diakId) FROM osztalyDiak WHERE osztalyId=%u";
        $v = array($osztalyId);
        $db = db_query($q, array('fv'=>'getDiakokTorzslapszama/osztalyTagok', 'modul'=>'naplo_intezmeny','result'=>'value','values'=>$v));

        // Ha nincsenek tagok
        if ($db == 0) return false;

        // OsztalyJel lekérdezése
        if ($SET['osztalyJellel']) {
            $q = "select concat('/',kezdoTanev, '-', vegzoTanev+1,'/',jel) from osztaly where osztalyId = %u";
            $v = array($osztalyId);
            $osztalyStr = db_query($q, array('fv'=>'getDiakokTorzslapszama/osztalyJel', 'modul'=>'naplo_intezmeny','result'=>'value','values'=>$v));
        } else {
            $osztalyStr = '';
        }
        // Törzslapszámok lekérdezése
        $q = "select diakId, ifnull(diak.torzslapszam, concat(diakTorzslapszam.torzslapszam,'%s')) as torzslapszam
                from diak left join diakTorzslapszam using(diakId)
                where osztalyId=%u
                and diakId in (select diakId from osztalyDiak where osztalyId=%u)
                order by torzslapszam";
        $v = array($osztalyStr, $osztalyId, $osztalyId);
        $r = db_query($q, array('fv'=>'getDiakokTorzslapszama/osztaly', 'modul'=>'naplo_intezmeny','result'=>'keyvaluepair','values'=>$v));

        return $r;
    }

    function getDiakNaploSorszam($diakId,$tanev,$osztalyId) {
	$q = "SELECT diakNaploSorszam(%u,%u,%u)";
        $v = array($diakId,$tanev,$osztalyId);
        return db_query($q, array('fv'=>'getDiakNaploSorszam', 'modul'=>'naplo_intezmeny','result'=>'value','values'=>$v));
    }


    function getDiakNyelvvizsga($diakId) {
	$q = "SELECT * FROM diakNyelvvizsga WHERE diakId=%u";
        $v = array($diakId);
        return db_query($q, array('fv'=>'getDiaknyelvvizsga', 'modul'=>'naplo_intezmeny','result'=>'indexed','values'=>$v));
    }

    function diakNyelvvizsgaFelvesz($ADAT) {
	$q = "INSERT INTO diakNyelvvizsga  (diakId,targyId,vizsgaSzint,vizsgaTipus,vizsgaDt,vizsgaIntezmeny,vizsgaBizonyitvanySzam) 
	VALUES (%u,%u,'%s', '%s', '%s', '%s', '%s')";
        $v = array($ADAT['diakId'],$ADAT['targyId'],$ADAT['vizsgaSzint'],$ADAT['vizsgaTipus'],$ADAT['vizsgaDt'],$ADAT['vizsgaIntezmeny'],$ADAT['vizsgaBizonyitvanySzam']);
        return db_query($q, array('fv'=>'diakNyelvvizsgaFelvesz', 'modul'=>'naplo_intezmeny','result'=>'record','values'=>$v));
    }
    function diakNyelvvizsgaTorol($ADAT) {
	if (count($ADAT)>0) {
	    $q = "DELETE FROM diakNyelvvizsga WHERE nyelvvizsgaId IN (".implode(',',$ADAT).") ";
    	    return db_query($q, array('fv'=>'diakNyelvvizsgaTorol', 'modul'=>'naplo_intezmeny','values'=>$v));
	}
    }

    function getNyelvvizsgak($SET) {
	if ($SET['igDt']=='') $SET['igDt'] = date('Y-m-d', strtotime('+365 days',strtotime($SET['tolDt'])));
	$q = "SELECT * FROM diakNyelvvizsga WHERE vizsgaDt>='%s' AND vizsgaDt<'%s'";
        $v = array($SET['tolDt'],$SET['igDt']);
	return $r = db_query($q, array('fv'=>'getDiaknyelvvizsga', 'modul'=>'naplo_intezmeny','result'=>'indexed','values'=>$v));
    }

?>
