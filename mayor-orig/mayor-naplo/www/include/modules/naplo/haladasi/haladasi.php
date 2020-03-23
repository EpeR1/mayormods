<?php

    require_once ( 'include/modules/naplo/share/oraModifier.php' );

    function getOraszamByOraId($oraId, $olr='') {
        $lr = ($olr=='') ? db_connect('naplo', array('fv' => 'getOraszamByOraId')) : $olr;

        $q = "SELECT tankorId, dt, ora FROM ora WHERE oraId=%u";
	$r = db_query($q, array('fv' => 'getOraszamByOraId', 'modul' => 'naplo', 'result' => 'record', 'values' => array($oraId)), $lr);

        $tankorId = $r['tankorId'];
	$dt = $r['dt'];
	$ora = $r['ora'];
	
	if (defined('__ORASZAMOT_NOVELO_TIPUSOK')) {
	    $oraszamNoveloTipus = explode(',', __ORASZAMOT_NOVELO_TIPUSOK);
	} else {
	    if (!in_array('info:missing_constant:__ORASZAMOT_NOVELO_TIPUSOK',$_SESSION['alert'])) $_SESSION['alert'][] = 'info:missing_constant:__ORASZAMOT_NOVELO_TIPUSOK';
	    $oraszamNoveloTipus = array('normál', 'normál máskor', 'helyettesítés', 'összevonás');
	}
        $q = "SELECT count(oraId) FROM ora
                    WHERE tankorId=%u
                    AND tipus IN ('".implode("','", array_fill(0, count($oraszamNoveloTipus), '%s'))."')
                    AND (dt<'%s' OR (dt='%s' AND ora<=%u))";
	$v = mayor_array_join(array($tankorId), $oraszamNoveloTipus, array($dt, $dt, $ora));
	$oraszam = db_query($q, array('fv' => 'getOraszamByOraId', 'modul' => 'naplo', 'result' => 'value', 'values' => $v), $lr);

        if ($olr == '') db_close($lr);
        return $oraszam;
    }

    function getHaladasi($Tankorok, $munkatervIds, $orderBy, $tanarId = '', $csakUres=false, $teremId=false) {

	$ret = array();

	// Munkatervidk
	if (!is_array($munkatervIds) || count($munkatervIds)==0) $munkatervIds = array(1); // a default 

	// Az érintett tankörök id-inek listája
	$tankorIds = $tankorAdat = array();
	if (is_array($Tankorok) && ($count = count($Tankorok)) > 0) {
	    $_tankorId = intval($Tankorok[0]['tankorId']);
	    $tankorFeltetel = 'tankorId IN (' . $_tankorId;
	    $tankorIds[] = $_tankorId;
	    $Tankorok[0]['tanar'] = getTankorTanaraiByInterval($_tankorId, array('tanev' => __TANEV, 'tolDt' => _SHOW_DAYS_FROM, 'igDt' => _SHOW_DAYS_TO, 'result' => 'nevsor'));
	    $tankorAdat[$_tankorId] = $Tankorok[0];
	    for ($i = 1; $i < $count; $i++) {
		$_tankorId = intval($Tankorok[$i]['tankorId']);
		$tankorFeltetel .= ', '.$_tankorId;
		$tankorIds[] = $_tankorId;
		$Tankorok[$i]['tanar'] = getTankorTanaraiByInterval($_tankorId, array('tanev' => __TANEV, 'tolDt' => _SHOW_DAYS_FROM, 'igDt' => _SHOW_DAYS_TO, 'result' => 'nevsor'));
		$tankorAdat[$_tankorId] = $Tankorok[$i];
	    }
	    $tankorFeltetel .= ')';
	} 
	elseif ($tanarId=='') return false;
	// else return false; // Ha egy kollégának nincs rendszeres órája, tanköre, de helyettesít, akkor meg kell jelenjenek ezek az órái... (Bug #53)

	if ($teremId!==false && is_numeric($teremId)) {
	    $teremFeltetel = ' and teremId = '.$teremId;
	} else
	    $teremFeltetel = '';

	// Ha tanarId is van, akkor az általa helyettesített órák is kellenek
	if ($tanarId != '') {
	    if (isset($tankorFeltetel)) $kiFeltetel = 'OR ki = '.$tanarId;
	    else $kiFeltetel = 'ki = '.$tanarId;
	}
        if (isset($tankorFeltetel) || isset($kiFeltetel)) $feltetel = "AND ($tankorFeltetel $kiFeltetel)";
	if (isset($csakUres) && $csakUres==true) $feltetel .= " AND (leiras='' OR leiras IS NULL) ";

	// Csatlakozás az adatbázishoz
	$lr = db_connect('naplo', array('fv' => 'getHaladasi'));
        $q = "SELECT oraId, dt, ora, ki, kit, tankorId, teremId, ora.leiras, tipus, eredet, csoportId, feladatTipusId,
		hazifeladatId,
		getOraTolTime(oraId) AS tolTime,
		getOraIgTime(oraId) AS igTime
                        FROM ora 
			LEFT JOIN tankorCsoport USING (tankorId) 
			LEFT JOIN oraHazifeladat USING (oraId) 
			LEFT JOIN ".__INTEZMENYDBNEV.".feladatTipus USING (feladatTipusId)
                            WHERE dt>='%s' AND dt<='%s' AND tipus NOT LIKE 'elmarad%%'
                            $feltetel $teremFeltetel
                            ORDER BY ".implode(',',$orderBy);
	$v = array(_SHOW_DAYS_FROM, _SHOW_DAYS_TO);
	$r = db_query($q, array('fv' => 'getHaladasi', 'modul' => 'naplo', 'result' => 'indexed', 'values' => $v), $lr);

	if ($r===false) {
	    db_close($lr);
	    return false;
	}
	foreach ($r as $i => $sor) {
	    // ha nincs a tankorok kozott a tankorId, akkor le kell kérdezni az adatait
	    if (!in_array($sor['tankorId'],$tankorIds)) {
		$T = getTankorById($sor['tankorId'], __TANEV);
		$tankorIds[] = $sor['tankorId'];
		$tankorAdat[$sor['tankorId']] = $T[0];
		$Tankorok[$i]['tanar'] = getTankorTanaraiByInterval($sor['tankorId'], array('tanev' => __TANEV, 'tolDt' => _SHOW_DAYS_FROM, 'igDt' => _SHOW_DAYS_TO, 'result' => 'nevsor'));
	    }
	    $sor['kiCn'] = getTanarNevById($sor['ki'], $lr);
	    $sor['tankorNev'] = $tankorAdat[$sor['tankorId']]['tankorNev'];
	    $sor['tankorTipusId'] = $tankorAdat[$sor['tankorId']]['tankorTipusId'];
	    $sor['oraszam'] = getOraszamByOraId($sor['oraId'], $lr);
	    $sor['tanar'] = $tankorAdat[$sor['tankorId']]['tanar'];
	    // Az óracsoportokat is!!!
	    if (isset($sor['csoportId']) && $tanarId != '') { // Csak tanár nézet esetén lehet váltani!!!
		if (!is_array($tankorAdat[$sor['tankorId']]['csoport'])) {
		    // Csoport adatok lekérdezése
		    // Ha minden oldalon le akarjuk kérdezi a csoportokat, akkor valahogy így...
		    // if ($sor['kit'] == '') $tanarId = $sor['ki'];
		    // else $tanarId = $sor['kit'];
		    $q = "SELECT DISTINCT tankorCsoport.tankorId AS tankorId, tankorNev
			FROM tankorCsoport
			LEFT JOIN ".__INTEZMENYDBNEV.".tankorSzemeszter USING (tankorId)
			LEFT JOIN ".__INTEZMENYDBNEV.".tankorTanar USING (tankorId)
			WHERE tanarId=%u AND csoportId=%u
			AND beDt<='"._SHOW_DAYS_TO."' AND (kiDt IS NULL OR '"._SHOW_DAYS_FROM."'<=kiDt)
			AND tanev=" . __TANEV;
		    $v = array($tanarId, $sor['csoportId']);
		    $r_cs = db_query($q, array('fv' => 'getHaladasi/csoport', 'modul' => 'naplo', 'result' => 'indexed', 'values' => $v), $lr);
		    if ($r_cs===false) { //!!!! nem jó simán a tagadás!
			db_close($lr);
			return false;
		    }
		    foreach ($r_cs as $key => $val) {
			$tankorAdat[$sor['tankorId']]['csoport'][] = $val;
		    }
		}
		$sor['csoport'] = $tankorAdat[$sor['tankorId']]['csoport'];
	    }
	    if (__ORACIMKE_ENABLED === true) {
		// Cimkek
		$q = "SELECT cimkeId from oraCimke where oraId=%u";
		$v = array($sor['oraId']);
		$sor['cimke'] = db_query($q, array('fv' => 'getHaladasi/cimkek', 'modul' => 'naplo', 'result' => 'idonly', 'values' => $v), $lr);
	    }

	    $ret[$sor['dt']][] = $sor;
	}
        // Nap információk lekérdezése
        $q = "SELECT dt,tipus,megjegyzes,orarendiHet FROM nap
                WHERE dt>='%s' AND dt<='%s' AND munkatervId IN (".implode(',', $munkatervIds).")";
	$v = array(_SHOW_DAYS_FROM, _SHOW_DAYS_TO);
	$ret['napok'] = db_query($q, array('fv' => 'getHaladasi', 'modul' => 'naplo', 'result' => 'multiassoc', 'keyfield' => 'dt', 'values' => $v), $lr);
	if (!$ret['napok']) {
	    db_close($lr);
	    return false;
	}

        // dolgozatok lekérdezése;
        $ret['dolgozatok'] = getTankorDolgozatok($tankorIds,true,_SHOW_DAYS_FROM,_SHOW_DAYS_TO, $lr);
        db_close($lr);
        return $ret;
    }

    function haladasiTeremModositas($oraId,$teremId,$lr) {
        if (!is_numeric($oraId) || !is_numeric($teremId)) return false;
        $lr = $olr=='' ? db_connect('naplo', array('fv' => 'haladasiTeremModositas')):$olr;
        $q = "UPDATE ora SET teremId=%u WHERE oraId=%u";
        $v = array($teremId,$oraId);
        $RESULT = db_query($q, array( 'fv' => 'haladasiTeremModositas','modul' => 'naplo', 'values' => $v), $lr);
        if ($olr == '') db_close($lr);
	return $RESULT;
    }

    function exportTankorHaladasi($file, $ADAT) {


	$EXPORT = array(array('Óraszám','Téma'));
	foreach ($ADAT['haladasi'] as $dt => $nAdat) {
	    foreach ($nAdat as $index => $oAdat) {
		if ($oAdat['oraszam'] != '')
		    $EXPORT[] = array($oAdat['oraszam'], $oAdat['leiras']);
	    }
	}
	if ($ADAT['formatum'] == 'xml') return generateXLS("$file.${ADAT['formatum']}", $EXPORT, 'haladási napló');
        elseif ($ADAT['formatum'] == 'csv') return generateCSV("$file.${ADAT['formatum']}", $EXPORT, 'haladási napló');
        elseif ($ADAT['formatum'] == 'ods') return generateODS("$file.${ADAT['formatum']}", $EXPORT, 'haladási naplo');
        else return false;

    }

?>
