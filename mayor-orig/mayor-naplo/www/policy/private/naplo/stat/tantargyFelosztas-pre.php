<?php

    if (_RIGHTS_OK !== true) die();
    if (__NAPLOADMIN !== true && __VEZETOSEG !== true) $_SESSION['alert'][] = 'page:insufficient_access';

    require_once('include/modules/naplo/share/intezmenyek.php');
    require_once('include/modules/naplo/share/osztaly.php');
    require_once('include/modules/naplo/share/targy.php');
    require_once('include/modules/naplo/share/tankor.php');

    global $ADAT;
    $ADAT = array();

/*
    $ADAT['evfolyamJelek'] = getEvfolyamJelek(array('result'=>'idonly'));
    $ADAT['tankorTipusok'] = getTankorTipusok();
    foreach ($ADAT['tankorTipusok'] as $tankorTipusId => $tAdat) $ADAT['tankorTipusIds'][$tAdat['oratervi']][] = $tankorTipusId;
    $ADAT['finanszírozott pedagógus létszám'] = array(
	'általános iskola' 				=> 11.8, // 11.8 tanuló / 1 pedagógus
	'gimnázium' 					=> 12.5, // 12.5 tanuló / 1 pedagógus
	'szakiskola, Híd programok' 			=> 12,   // ...
	'szakközépiskola, nem szakkképző évfolyam' 	=> 12.4,
	'szakközépiskola, szakkképző évfolyam' 		=> 13.7
    ); // -- TODO szakgimnázium???



    $IA['intezmenyAdat'] = getIntezmenyByRovidnev(__INTEZMENY);
    $IA['osztalyAdat'] = getOsztalyok(__TANEV, array('result' => 'assoc', 'minden'=>false, 'telephelyId' => null));
    foreach ($IA['osztalyAdat'] as $idx => $oAdat) $IA['osztalyIds'][] = $oAdat['osztalyId'];
    $IA['targyAdat'] = getTargyAdatByIds();

    $IA['diakLetszam']['statusz'] = getDiakLetszamByStatusz();
    $IA['diakLetszam']['osztaly'] = getDiakLetszamByOsztalyId($IA['osztalyIds']);
    foreach ($IA['diakLetszam']['osztaly'] as $osztalyId => $letszam) 
	if (is_numeric($osztalyId)) $IA['diakLetszam']['evfolyamJel'][ $IA['osztalyAdat'][$osztalyId]['evfolyamJel'] ]['összes'] += intval($letszam);
    foreach ($IA['diakLetszam']['osztaly']['fiú'] as $osztalyId => $letszam) 
	if (is_numeric($osztalyId)) $IA['diakLetszam']['evfolyamJel'][ $IA['osztalyAdat'][$osztalyId]['evfolyamJel'] ]['fiú'] += intval($letszam);
    foreach ($IA['diakLetszam']['osztaly']['lány'] as $osztalyId => $letszam) 
	if (is_numeric($osztalyId)) $IA['diakLetszam']['evfolyamJel'][ $IA['osztalyAdat'][$osztalyId]['evfolyamJel'] ]['lány'] += intval($letszam);
    $IA['tanarLetszam']['besorolas'] = getTanarLetszamByBesorolas();
    $IA['tanarLetszam']['statusz'] = getTanarLetszamByStatusz();
    $IA['oraszamok'] = getTankorOraszamOsszesites($ADAT['tankorTipusIds']);
    $IA['targyOraszamok'] = getTargyOraszamok($ADAT['tankorTipusIds']);
    $IA['osztalyOraszamok'] = getOsztalyOraszamok($IA['osztalyIds'], $ADAT['tankorTipusIds']);
    foreach ($IA['osztalyOraszamok']['összes'] as $osztalyId => $oraszam) {
	$IA['evfolyamOraszamok']['összes'][ $IA['osztalyAdat'][$osztalyId]['evfolyamJel'] ] += intval($oraszam);
	$IA['evfolyamOraszamok']['óratervi'][ $IA['osztalyAdat'][$osztalyId]['evfolyamJel'] ] += intval($IA['osztalyOraszamok']['óratervi'][$osztalyId]);
	$IA['evfolyamOraszamok']['tanórán kívüli'][ $IA['osztalyAdat'][$osztalyId]['evfolyamJel'] ] += intval($IA['osztalyOraszamok']['tanórán kívüli'][$osztalyId]);
    }
    $egyhaziE = ($IA['intezmenyAdat']['fenntarto']=='egyházi');
    foreach ($IA['osztalyAdat'] as $osztalyId => $osztalyAdat) {
	$IA['osztalyIdokeret'][$osztalyId] = getOsztalyHetiIdokeret($osztalyId, $osztalyAdat, array('egyhaziE'=>$egyhaziE));
	$IA['osztalyIdokeret']['összesen']['összes'] += $IA['osztalyIdokeret'][$osztalyId]['összes'];
	$IA['osztalyIdokeret']['összesen']['engedélyezett'] += $IA['osztalyIdokeret'][$osztalyId]['engedélyezett'];
	$IA['osztalyIdokeret']['összesen']['tehetséggondozás-felzárkóztatás'] += $IA['osztalyIdokeret'][$osztalyId]['tehetséggondozás-felzárkóztatás'];
	$IA['osztalyIdokeret']['összesen']['egyházi'] += $IA['osztalyIdokeret'][$osztalyId]['egyházi'];
	$IA['osztalyIdokeret']['összesen']['gimnázium'] += $IA['osztalyIdokeret'][$osztalyId]['gimnázium'];
	$IA['osztalyIdokeret']['összesen']['nemzetiségi'] += $IA['osztalyIdokeret'][$osztalyId]['nemzetiségi'];
	if (in_array($osztalyAdat['osztalyJellegId'], array(21,22)) && in_array($osztalyAdat['evfolyamJel'], array('1','2','3','4'))) {
	    $IA['osztalyIdokeret']['alsó']['összes'] += $IA['osztalyIdokeret'][$osztalyId]['összes'];
	    $IA['osztalyIdokeret']['alsó']['engedélyezett'] += $IA['osztalyIdokeret'][$osztalyId]['engedélyezett'];
	    $IA['osztalyIdokeret']['alsó']['tehetséggondozás-felzárkóztatás'] += $IA['osztalyIdokeret'][$osztalyId]['tehetséggondozás-felzárkóztatás'];
	    $IA['osztalyIdokeret']['alsó']['egyházi'] += $IA['osztalyIdokeret'][$osztalyId]['egyházi'];
	    $IA['osztalyIdokeret']['alsó']['gimnázium'] += $IA['osztalyIdokeret'][$osztalyId]['gimnázium'];
	    $IA['osztalyIdokeret']['alsó']['nemzetiségi'] += $IA['osztalyIdokeret'][$osztalyId]['nemzetiségi'];
	}
	if (in_array($osztalyAdat['osztalyJellegId'], array(21,23)) && in_array($osztalyAdat['evfolyamJel'], array('5','6','7','8'))) {
	    $IA['osztalyIdokeret']['felső']['összes'] += $IA['osztalyIdokeret'][$osztalyId]['összes'];
	    $IA['osztalyIdokeret']['felső']['engedélyezett'] += $IA['osztalyIdokeret'][$osztalyId]['engedélyezett'];
	    $IA['osztalyIdokeret']['felső']['tehetséggondozás-felzárkóztatás'] += $IA['osztalyIdokeret'][$osztalyId]['tehetséggondozás-felzárkóztatás'];
	    $IA['osztalyIdokeret']['felső']['egyházi'] += $IA['osztalyIdokeret'][$osztalyId]['egyházi'];
	    $IA['osztalyIdokeret']['felső']['gimnázium'] += $IA['osztalyIdokeret'][$osztalyId]['gimnázium'];
	    $IA['osztalyIdokeret']['felső']['nemzetiségi'] += $IA['osztalyIdokeret'][$osztalyId]['nemzetiségi'];
	}
	if (in_array($osztalyAdat['osztalyJellegId'], array(21,22,23)) && in_array($osztalyAdat['evfolyamJel'], array('1','2','3','4','5','6','7','8'))) {
	    $IA['osztalyIdokeret']['általános']['összes'] += $IA['osztalyIdokeret'][$osztalyId]['összes'];
	    $IA['osztalyIdokeret']['általános']['engedélyezett'] += $IA['osztalyIdokeret'][$osztalyId]['engedélyezett'];
	    $IA['osztalyIdokeret']['általános']['tehetséggondozás-felzárkóztatás'] += $IA['osztalyIdokeret'][$osztalyId]['tehetséggondozás-felzárkóztatás'];
	    $IA['osztalyIdokeret']['általános']['egyházi'] += $IA['osztalyIdokeret'][$osztalyId]['egyházi'];
	    $IA['osztalyIdokeret']['általános']['gimnázium'] += $IA['osztalyIdokeret'][$osztalyId]['gimnázium'];
	    $IA['osztalyIdokeret']['általános']['nemzetiségi'] += $IA['osztalyIdokeret'][$osztalyId]['nemzetiségi'];
	}
	if (in_array($osztalyAdat['osztalyJellegId'], array(51,52,53,61,62,63)) && in_array($osztalyAdat['evfolyamJel'], array('1','2','3','4','5','6','7','8'))) {
	    $IA['osztalyIdokeret']['gimnázium18']['összes'] += $IA['osztalyIdokeret'][$osztalyId]['összes'];
	    $IA['osztalyIdokeret']['gimnázium18']['engedélyezett'] += $IA['osztalyIdokeret'][$osztalyId]['engedélyezett'];
	    $IA['osztalyIdokeret']['gimnázium18']['tehetséggondozás-felzárkóztatás'] += $IA['osztalyIdokeret'][$osztalyId]['tehetséggondozás-felzárkóztatás'];
	    $IA['osztalyIdokeret']['gimnázium18']['egyházi'] += $IA['osztalyIdokeret'][$osztalyId]['egyházi'];
	    $IA['osztalyIdokeret']['gimnázium18']['gimnázium'] += $IA['osztalyIdokeret'][$osztalyId]['gimnázium'];
	    $IA['osztalyIdokeret']['gimnázium18']['nemzetiségi'] += $IA['osztalyIdokeret'][$osztalyId]['nemzetiségi'];
	}
	if (in_array($osztalyAdat['osztalyJellegId'], array(31,32,33,34,35,36,51,52,53,61,62,63)) && in_array($osztalyAdat['evfolyamJel'], array('9','10','11','12'))) {
	    $IA['osztalyIdokeret']['gimnázium92']['összes'] += $IA['osztalyIdokeret'][$osztalyId]['összes'];
	    $IA['osztalyIdokeret']['gimnázium92']['engedélyezett'] += $IA['osztalyIdokeret'][$osztalyId]['engedélyezett'];
	    $IA['osztalyIdokeret']['gimnázium92']['tehetséggondozás-felzárkóztatás'] += $IA['osztalyIdokeret'][$osztalyId]['tehetséggondozás-felzárkóztatás'];
	    $IA['osztalyIdokeret']['gimnázium92']['egyházi'] += $IA['osztalyIdokeret'][$osztalyId]['egyházi'];
	    $IA['osztalyIdokeret']['gimnázium92']['gimnázium'] += $IA['osztalyIdokeret'][$osztalyId]['gimnázium'];
	    $IA['osztalyIdokeret']['gimnázium92']['nemzetiségi'] += $IA['osztalyIdokeret'][$osztalyId]['nemzetiségi'];
	}
	if (in_array($osztalyAdat['osztalyJellegId'], array(31,32,33,34,35,36,51,52,53,61,62,63))) {
	    $IA['osztalyIdokeret']['gimnázium']['összes'] += $IA['osztalyIdokeret'][$osztalyId]['összes'];
	    $IA['osztalyIdokeret']['gimnázium']['engedélyezett'] += $IA['osztalyIdokeret'][$osztalyId]['engedélyezett'];
	    $IA['osztalyIdokeret']['gimnázium']['tehetséggondozás-felzárkóztatás'] += $IA['osztalyIdokeret'][$osztalyId]['tehetséggondozás-felzárkóztatás'];
	    $IA['osztalyIdokeret']['gimnázium']['egyházi'] += $IA['osztalyIdokeret'][$osztalyId]['egyházi'];
	    $IA['osztalyIdokeret']['gimnázium']['gimnázium'] += $IA['osztalyIdokeret'][$osztalyId]['gimnázium'];
	    $IA['osztalyIdokeret']['gimnázium']['nemzetiségi'] += $IA['osztalyIdokeret'][$osztalyId]['nemzetiségi'];
	}
	// Finanszírozott pedagógus létszámhoz diáklészámok osztály-típusonként
	if (in_array($osztalyAdat['osztalyJellegId'], array(21,22,23))) { // általános iskola
	    $IA['diakLetszam']['általános iskola'] += $IA['diakLetszam']['osztaly'][$osztalyId];
	} else if (in_array($osztalyAdat['osztalyJellegId'], array(31,32,33,34,35,36,51,52,53,61,62,63,65))) { // gimnázium
	    $IA['diakLetszam']['gimnázium'] += $IA['diakLetszam']['osztaly'][$osztalyId];
	} else if (in_array($osztalyAdat['osztalyJellegId'], array(82,83,84,85,91,92,93))) { // szakiskola, Híd programok
	    $IA['diakLetszam']['szakiskola, Híd programok'] += $IA['diakLetszam']['osztaly'][$osztalyId];
	} else if (in_array($osztalyAdat['osztalyJellegId'], array(71,72,73,74,75,76,77,78,79))) { // szakközépiskola, nem szakképző évfolyam
	    $IA['diakLetszam']['szakközépiskola, nem szakkképző évfolyam'] += $IA['diakLetszam']['osztaly'][$osztalyId];
	} else if (in_array($osztalyAdat['osztalyJellegId'], array())) { // szakközépiskola, szakképző évfolyam
	    $IA['diakLetszam']['szakközépiskola, szakképző évfolyam'] += $IA['diakLetszam']['osztaly'][$osztalyId];
	}
    } // osztályok
    $IA['tankorLetszamStat'] = getTankorLetszamStat();
*/
    $IA = getTantargyfelosztasStat();

    $ADAT['intezmeny'][__INTEZMENY] = $IA;

?>