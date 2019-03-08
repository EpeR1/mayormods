<?php
/*
    module:	naplo
    version:	3.0

    function getJegyek($tankorId, $sulyozas='', $NEVSOR)

    function jegyBeiras($tankorId, $tipus, $oraId, $dolgozatId, $tanarId, $Beirando, $actionId) {
	+ checkTankorDolgozata()
	+ ujDolgozat()

    csinálmány: jó lenne, ha nem $Jegyek-nek hívnánk mindent ebben, esetleg elemjeire bonthatnánk a tömböt.
*/

    function getJegyek($tankorId, $tolDt, $igDt, $sulyozas = '', $Diakok = array()) {
    
	global $_TANEV, $KOVETELMENY;

	// Diákok lekérdezése
	if (!is_array($Diakok['idk']) || count($Diakok['idk']) == 0) $Diakok = getTankorDiakjaiByInterval($tankorId, __TANEV, $tolDt, $igDt);
	// kezdőértékek
	$Jegyek = array('dolgozatok' => array('lista' => array()), 'tankörök' => array(), 'tanárok' => array('tanarIds' => array()));
	// A tankör adatainak lekérdezése
	list($tankorAdat) = getTankorById($tankorId, __TANEV);
	// Ha nincsenek diákok
	if (!is_array($Diakok['idk']) || count($Diakok['idk']) == 0) {
		$Tanarok = $Jegyek['tankörök'][$tankorId]['tanárok'] = getTankorTanaraiByInterval($tankorId, array('tanev' => __TANEV, 'tolDt' => $tolDt, 'igDt' => $igDt, 'result' => 'nevsor'));
		for ($t = 0; $t < count($Tanarok); $t++) {
		    if (!in_array($Tanarok[$t]['tanarId'], $Jegyek['tanárok']['tanarIds'])) {
			$Jegyek['tanárok'][$Tanarok[$t]['tankorId']] = $Tanarok[$t];
			$Jegyek['tanárok']['tanarIds'][] = $Tanarok[$t]['tanarId'];
			$Jegyek['tanárok']['tanarNevek'][] = $Tanarok[$t]['tanarNev'];
		    }
		}
		$Jegyek['tankörök'][$tankorId] = $tankorAdat;
		return $Jegyek;
	}


	if ($sulyozas == '') $suly = array(0,1,1,1,1,1,1);
	else $suly = explode(':', '0:'.$sulyozas);

	
	// A diákok tárgyhoz tartozó tankörei
	$q = "SELECT DISTINCT tankorDiak.tankorId, tankorNev, tankor.targyId, felveheto
		FROM ".__INTEZMENYDBNEV.".tankorDiak LEFT JOIN ".__INTEZMENYDBNEV.".tankor USING (tankorId)
		LEFT JOIN ".__INTEZMENYDBNEV.".tankorSzemeszter USING (tankorId)
		WHERE tankor.targyId=%u AND tanev=".__TANEV."
		AND diakId IN ('".implode("','", array_fill(0, count($Diakok['idk']), '%u'))."')
		AND beDt<='%s' AND (kiDt IS NULL OR kiDt>='%s')";
	$v = mayor_array_join(array($tankorAdat['targyId']), $Diakok['idk'], array($igDt, $tolDt));
	$Jegyek['tankörök'] = db_query($q, array('fv' => 'getJegyek (Tankör)', 'modul' => 'naplo_intezmeny', 'result' => 'assoc', 'keyfield' => 'tankorId', 'values' => $v));
	$Jegyek['tanárok']['tanarIds'] =  $Jegyek['tanárok']['tanarNevek'] = array();
	if (is_array($Jegyek['tankörök']))
	foreach ($Jegyek['tankörök'] as $_tankorId => $a) {
		$Jegyek['tankörök']['tankorId'][] = $_tankorId;
		$Tanarok = $Jegyek['tankörök'][$_tankorId]['tanárok'] = getTankorTanaraiByInterval($_tankorId, array('tanev' => __TANEV, 'tolDt' => $tolDt, 'igDt' => $igDt, 'result' => 'nevsor'));
		for ($t = 0; $t < count($Tanarok); $t++) {
		    if (!in_array($Tanarok[$t]['tanarId'], $Jegyek['tanárok']['tanarIds'])) {
			$Jegyek['tanárok'][$Tanarok[$t]['tankorId']] = $Tanarok[$t];
			$Jegyek['tanárok']['tanarIds'][] = $Tanarok[$t]['tanarId'];
			$Jegyek['tanárok']['tanarNevek'][] = $Tanarok[$t]['tanarNev'];
		    }
		}
	}

	// ---
	if (count($Diakok['idk']) > 0 && count($Jegyek['tankörök']['tankorId']) > 0) { // Vannak diákok és a diákoknak adott tárgyhoz tankörei - olyankor lehet gond, ha a tankör csak egy korábbi időszakban volt, most már nem aktív
	    $q = "SELECT * FROM jegy
		WHERE tankorId IN ('".implode("','", array_fill(0, count($Jegyek['tankörök']['tankorId']), '%u'))."')
		AND tipus <> 0
		AND diakId IN ('".implode("','", array_fill(0, count($Diakok['idk']), '%u'))."')
		ORDER BY jegy.dt, jegy.jegyId";
	    $v = mayor_array_join($Jegyek['tankörök']['tankorId'], $Diakok['idk']);
	    $ret = db_query($q, array('fv' => 'getJegyek (Tankör)', 'modul' => 'naplo', 'result' => 'indexed', 'values' => $v));
	    reset($_TANEV['szemeszter']);
	    $szAdat = current($_TANEV['szemeszter']); $szemeszter = $szAdat['szemeszter'];
	    foreach ($ret as $i => $a) {
		if (strtotime($a['dt']) > strtotime($szAdat['zarasDt'])) { $szAdat = next($_TANEV['szemeszter']); $szemeszter = $szAdat['szemeszter']; }
		$tipus = $a['tipus'];
		if ($tipus > 2) {
		    if (!in_array($a['dolgozatId'], $Jegyek['dolgozatok']['lista'])) {
			list($ev,$ho,$napEsIdo) = explode('-', $a['dt']);
			$Jegyek['dolgozatok']['lista'][] = $a['dolgozatId'];
			$Jegyek['dolgozatok']['dátum szerint'][$ev][$ho][] = $a['dolgozatId'];
			$Jegyek['dolgozatok']['dátum szerint'][$szemeszter][$ev][$ho][] = $a['dolgozatId'];
			$Jegyek['dolgozatok'][$a['dolgozatId']] = array('év' => $ev, 'hó' => $ho, 'szemeszter' => $szemeszter);
			$Jegyek['dolgozatok'][$a['dolgozatId']]['tankorIds'] = array($a['tankorId']);
			$dSzemeszter =  $szemeszter; // A dolozat első jegyének szemesztere
		    } else {
			$ev = $Jegyek['dolgozatok'][$a['dolgozatId']]['év'];
			$ho = $Jegyek['dolgozatok'][$a['dolgozatId']]['hó'];
			$dSzemeszter = $Jegyek['dolgozatok'][$a['dolgozatId']]['szemeszter'];
			if (!in_array($a['tankorId'], $Jegyek['dolgozatok'][$a['dolgozatId']]['tankorIds'])) $Jegyek['dolgozatok'][$a['dolgozatId']]['tankorIds'][] = $a['tankorId'];
		    }
//		    $Jegyek[$a['diakId']][$ev][$ho]['dolgozat'][$a['dolgozatId']][] = $a;
		    $Jegyek[$a['diakId']][$dSzemeszter][$ev][$ho]['dolgozat'][$a['dolgozatId']][] = $a;

		    $Jegyek['dolgozatok'][$a['dolgozatId']]['jegyTipus'] = $a['jegyTipus'];
		    $Jegyek['dolgozatok'][$a['dolgozatId']]['átlag'] = $Jegyek['dolgozatok'][$a['dolgozatId']]['átlag'] * $Jegyek['dolgozatok'][$a['dolgozatId']]['sulyösszeg'] + $a['jegy']*$suly[$tipus];
		    $Jegyek['dolgozatok'][$a['dolgozatId']]['db']++;
		    $Jegyek['dolgozatok'][$a['dolgozatId']]['sulyösszeg'] += $suly[$tipus];
		    if ($Jegyek['dolgozatok'][$a['dolgozatId']]['sulyösszeg'] != 0)
			$Jegyek['dolgozatok'][$a['dolgozatId']]['átlag'] = $Jegyek['dolgozatok'][$a['dolgozatId']]['átlag'] / $Jegyek['dolgozatok'][$a['dolgozatId']]['sulyösszeg'];
		    else
			$Jegyek['dolgozatok'][$a['dolgozatId']]['átlag'] = 0;
		} else {
		    list($ev,$ho,$nap) = explode('-',$a['dt']);
		    $Jegyek[$a['diakId']][$ev][$ho]['jegyek'][] = $a;
		    $Jegyek[$a['diakId']][$szemeszter][$ev][$ho]['jegyek'][] = $a;
		}
		if (
		    in_array($a['jegyTipus'],array('jegy','féljegy'))
		    || $KOVETELMENY[ $a['jegyTipus'] ]['átlagolható'] === true
		) {
		    $Jegyek[$a['diakId']]['átlag'] = $Jegyek[$a['diakId']]['átlag'] * $Jegyek[$a['diakId']]['sulyösszeg'] + $a['jegy']*$suly[$tipus];
		    $Jegyek[$a['diakId']]['db']++;
		    $Jegyek[$a['diakId']]['sulyösszeg'] += $suly[$tipus];
	    
		    if ($Jegyek[$a['diakId']]['sulyösszeg'] != 0) 
			$Jegyek[$a['diakId']]['átlag'] = $Jegyek[$a['diakId']]['átlag'] / $Jegyek[$a['diakId']]['sulyösszeg'];
		    else
			$Jegyek[$a['diakId']]['átlag'] = 0;
		}
	    }
	    // Osztályátlag
	    $sum = $db = 0;
	    foreach ($Jegyek as $diakId => $dAdat)
		if (isset($dAdat['átlag'])) { $sum += $dAdat['átlag']; $db++; }
	    if ($db > 0) $Jegyek['átlag'] = $sum / $db;
	} // vannak diákok	

	// ------------------------------------
	// A tárgycsoporthoz tartozó zárójegyek
	// EZ NEM IDE TARTOZIK! --> share lib
	return $Jegyek;
    
    }

    /*
	Ez kerüljön át a share/jegyModifier-be 
    */
    function jegyBeiras($tankorId, $tipus, $oraId, $dolgozatId, $tanarId, $megjegyzes, $Beirando, $actionId, $lr) {


            // ha kell, van megadva dolgozat, ami tényleg a tankörhöz tartozik, vagy 'uj'...
            if ($tipus < 3 || checkTankorDolgozata($tankorId, $dolgozatId)) {
                    // Új dolgozat felvétele - ha kell
                    if (($tipus > 2) and ($dolgozatId == 'uj')) $dolgozatId = ujDolgozat($tanarId, $tankorId);
                    // Jegyek beírása
                    $v = $Values = array();
                    for ($i = 0; $i < count($Beirando); $i++) {
                	/* oraId, dolgozatId 'NULL' stringet is kaphat a hívó függvénytől */
			if ($oraId == 'NULL') {
			    if ($dolgozatId == 'NULL') $Values[] = "(%u, '%s', %f, %u, %u, NOW(), %s, %s, '%s',NOW())";
			    else $Values[] = "(%u, '%s', %f, %u, %u, NOW(), %s, %u, '%s',NOW())";
			} else {
			    if ($dolgozatId == 'NULL') $Values[] = "(%u, '%s', %f, %u, %u, NOW(), %u, %s, '%s',NOW())";
			    else $Values[] = "(%u, '%s', %f, %u, %u, NOW(), %u, %u, '%s',NOW())";
			}
                        array_push($v, $Beirando[$i]['diakId'], $Beirando[$i]['jegyTipus'], $Beirando[$i]['jegy'], $tipus, $tankorId, $oraId, $dolgozatId, $megjegyzes);
                    }
                    $q = "INSERT INTO jegy (diakId, jegyTipus, jegy, tipus, tankorId, dt, oraId, dolgozatId, megjegyzes, modositasDt)
                                VALUES ".implode(',',$Values);
                    $r = db_query($q, array('fv' => 'jegyBeiras', 'modul' => 'naplo', 'values' => $v), $lr);
                    if (!$r) return false;
                    logAction(
			array(
			    'actionId'=>$actionId,
			    'szoveg'=>"Jegybeírás: $tankorId, $tipus, $oraId, $dolgozatId",
			    'table'=>'jegy'
			)
		    );
            } else {
                    // dolgozat jegy lenne, de nincs dolgozat megadva, legalábbis nem a tankörhöz tartozó...
                    $_SESSION['alert'][] = 'message:wrong_data:jegyBeiras:tipus '.$tipus.':dolgozatId '.$did;
                    return false;
            }

            return true;

    }




    // -- 2009
    /* $jegyek[index] = assoc array, melyben a módosuló jegy adatai szerepelnek
	tankorId, targyId, actionId csak a loghoz kell!!! --> (???)
    */
    function jegyLezaras($jegyek, $tankorId, $targyId, $actionId) {

	    zaroJegyBeiras($jegyek);
            logAction(
		array(
		    'actionId'=>$actionId,
		    'szoveg'=>"Bizonyítvány: $tankorId, $targyId", 
		    'table'=>'bizonyitvany'
		)
	    );
            return true;

    }

?>
