<?php
/*
    Module: naplo
*/

    function initNapok($ADAT) {

	global $_TANEV, $UNNEPNAPOK;

	logAction(array('szoveg'=>'initNapok','table'=>'nap'));

	$Hetek = $ADAT['Hetek'];
        $lr = db_connect('naplo', array('fv' => 'initNapok'));

	db_start_trans($lr);

        $q = "DELETE FROM munkaterv";
        $r[] = db_query($q, array('fv' => 'initNapok', 'modul' => 'naplo'), $lr);

	/* Munkaterv */
	    $q = "INSERT INTO munkaterv (munkatervId,munkatervNev,tanitasiNap,tanitasNelkuliMunkanap,vegzosZarasDt) VALUES (1,'alapértelmezett',%u,%u,'%s')";
	    $v = array($ADAT['tanitasiNap'], $ADAT['tanitasNelkuliMunkanap'], $ADAT['vegzosZarasDt']);
            $r[] = db_query($q, array('fv' => 'initNapok1', 'modul' => 'naplo', 'values' => $v), $lr);

	    $q = "INSERT INTO munkatervOsztaly (munkatervId,osztalyId) SELECT 1 AS `munkatervId`,`osztalyId` FROM `".__INTEZMENYDBNEV."`.`osztaly` WHERE vegzoTanev>=%u AND kezdoTanev<=%u";
	    $v = array(__TANEV,__TANEV);
            $r[] = db_query($q, array('fv' => 'initNapok2', 'modul' => 'naplo', 'values' => $v), $lr);
	/* --- */

	$kovetkezoTanevAdat = getTanevAdat(__TANEV+1); 
	if (strtotime($kovetkezoTanevAdat['kezdesDt']) > strtotime($kovetkezoTanevAdat['zarasDt']))
	{
	    $_SESSION['alert'][] = 'alert:Hiba, a következő ('.(__TANEV+1).') tanév előbb végződik, mint kezdődik! Van következő tanév? (admin/tanévek megnyitása menüpont)';
	    $r[] = false;
	}

	$tanevVege = date('Y-m-d',strtotime('-1 days',strtotime($kovetkezoTanevAdat['kezdesDt'])));
	$r[] = napokHozzaadasa(__TANEV, $_TANEV['kezdesDt'], $tanevVege, $_TANEV, $lr);

	orarendiHetekHozzarendelese($_TANEV['kezdesDt'], $_TANEV['zarasDt'], $Hetek, $lr);

	if (in_array(false,$r)) {
	    db_rollback($lr);
    	    db_close($lr);
	    return false;
	} else {
	    db_commit($lr);
    	    db_close($lr);
    	    $_SESSION['alert'][] = 'info:success';
	    return true;
	}

    }

    function ujMunkaterv($ADAT) {

	$q = "INSERT INTO munkaterv (munkatervNev,tanitasiNap,tanitasNelkuliMunkanap,vegzosZarasDt) VALUES 
		('%s',%u,%u,'%s')";
	$v = array($ADAT['munkatervNev'], $ADAT['tanitasiNap'], $ADAT['tanitasNelkuliMunkanap'], $ADAT['vegzosZarasDt']);
        $munkatervId = db_query($q, array('fv' => 'ujMunkaterv/munkaterv', 'modul' => 'naplo', 'values' => $v, 'result' => 'insert'), $lr);

	if (!$munkatervId) { return false; }

	$q = "INSERT INTO nap SELECT dt, tipus, megjegyzes, orarendiHet, %u AS munkatervId, csengetesiRendTipus FROM nap WHERE munkatervId=%u";
	$v = array($munkatervId, $ADAT['munkatervId']);
	$r = db_query($q, array('fv' => 'ujMunkaterv/nap', 'modul' => 'naplo', 'values' => $v), $lr);

	if (!$r) { return false; }

	return $munkatervId;

    }

    function munkatervModositas($Dt, $Tipus, $Megjegyzes, $OrarendiHet, $Hetek, $munkatervId = 1, $csengetesiRendTipus) {

	global $_TANEV;

	logAction(
	    array(
		'szoveg'=>'munkaterv módosítás',
		'table'=>'nap'
	    )
	);
	$lr = db_connect('naplo', array('fv' => 'munkatervModositas'));
	db_start_trans($lr);
        for ($i = 0; $i < count($Dt); $i++) {	    
	    $dt = $Dt[$i];
	    $time = strtotime($dt);
	    $tipus = $Tipus[$i];
	    $megjegyzes = $Megjegyzes[$i]; 
	    $_csengetesiRendTipus = $csengetesiRendTipus[$i]; 
	    if ($tipus == 'tanítási nap') {
		// ????
		// kellene ellenőrizni, hogy szorgalmi időszakon belül van-e, vagy engedjük meg ezen kívül is a tanítási napot? Pótnap?
		// ????
		$orarendiHet = $OrarendiHet[$i];
		if ($orarendiHet == 0) { // most állítjuk be tanítási napnak, és nem rendelkeztek az órarendi hétről...
		    // kérdezzük le, hogy van-e másik munkatervben már megadott órarendi hét erre a napra
		    $q = "SELECT orarendiHet FROM nap WHERE dt='%s' AND orarendiHet<>0";
		    $v = array($dt);
		    $orarendiHet = db_query($q, array('fv' => 'munkatervModositas/hianyzoOrarendiHet','modul'=>'naplo', 'values'=>$v, 'result'=>'value'), $lr);
		    if ($orarendiHet === false) { db_rollback($lr); db_close($lr); return false; }
		    if (is_null($orarendiHet)) { // nincs beállítva órarendi hét --> legyen a $Hetek első eleme...
			$orarendiHet = $Hetek[0];
		    }
		}
	    } else {
		// Ha nem tanítási nap, akkor nincs értelme órarendi hetet beállítani --> 0
		$orarendiHet = 0;
	    }
	    if (
		// -- lehessen szorgalmi időszakon kívüli napokat is módosítani - ha már eltároljuk...
		// ($time >= strtotime($_TANEV['kezdesDt']) && $time <= strtotime($_TANEV['zarasDt'])) && 
		($tipus != 'tanítási nap' || count($Hetek) == 0 || in_array($orarendiHet, $Hetek))
	    ) {
		$q = "UPDATE nap SET csengetesiRendTipus='%s',tipus='%s', megjegyzes='%s' WHERE dt='%s' AND munkatervId=%u";
		$v = array($_csengetesiRendTipus, $tipus, $megjegyzes, $dt, $munkatervId);
		$r = db_query($q, array('fv' => 'munkatervModositas/típus, megjegyzés', 'modul' => 'naplo', 'values' => $v), $lr);
		if (!$r) { db_rollback($lr); db_close($lr); return(false); }
		// Az órarendi hét módosítás mindig az összes munkatervet érinti!!
		if ($orarendiHet != 0) {
		    $q = "UPDATE nap SET orarendiHet=%u WHERE dt='%s' AND tipus='tanítási nap'";
		    $v = array($orarendiHet, $dt);
		} else  {
		    $q = "UPDATE nap SET orarendiHet=%u WHERE dt='%s' AND munkatervId=%u";
		    $v = array($orarendiHet, $dt, $munkatervId);
		}
		$r = db_query($q, array('fv' => 'munkatervModositas/órarendiHét', 'modul' => 'naplo', 'values' => $v), $lr);
		if (!$r) { db_rollback($lr); db_close($lr); return(false); }
	    } else {
		$_SESSION['alert'][] = 'message:wrong_data:munkatervModositas:'.$dt.':'.$tipus.'/'.$orarendiHet;
	    }
        }
	db_commit($lr);
	db_close($lr);
	return true;

    }

    function munkatervOsztaly($ADAT) {

	$r = array();
	for ($i = 0; $i < count($ADAT['osztalyIds']); $i++) {

	    $osztalyId = $ADAT['osztalyIds'][$i];
	    $munkatervId = $ADAT['ujMunkatervIds'][$i];
	    $q = "UPDATE munkatervOsztaly SET munkatervId='%u' WHERE osztalyId=%u";
	    $v = array($munkatervId, $osztalyId);
	    $r[] = db_query($q, array('fv' => 'munkatervOsztaly', 'modul' => 'naplo', 'values' => $v));

	}
	return !in_array(false, $r);

    }

?>
