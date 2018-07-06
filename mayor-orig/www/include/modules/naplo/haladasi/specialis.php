<?php
/*
    module: naplo
*/

    function napiOrakTorlese($dt, $tipus) {
    

	$lr = db_connect('naplo', array('fv' => 'napiOrakTorlese'));
	
	$q = "DELETE FROM ora WHERE dt='%s'";
	$r = db_query($q, array('fv' => 'napiOrakTorlese', 'modul' => 'naplo', 'values' => array($dt)), $lr);
	if (!$r) {
	    db_close($lr);
	    return false;
	}
	
	if ($tipus !== '') {
	    $q = "UPDATE nap SET tipus='%s' WHERE dt='%s'";
	    $r = db_query($q, array('fv' => 'napiOrakTorlese', 'modul' => 'naplo', 'values' => array($tipus, $dt)), $lr);
	    // ?? Mi van, ha szünetről tanítási nap-ra állítjuk? Marad a 0. órarendi hét? ??
	}
	db_close($lr);
	return $r;

    }
    
    function orakBetoltese($dt, $orarendiHet) {


	$lr = db_connect('naplo', array('fv' => 'orakBetoltese'));

	// Ellenőrizzük, hogy van-e már betöltve óra az adott napra
    	$q = "SELECT COUNT(oraId) FROM ora WHERE dt='%s'";
	$num = db_query($q, array('fv' => 'orakBetoltese', 'modul' => 'naplo', 'values' => array($dt), 'result'=>'value'), $lr);
	if ($num === false) {
	    db_close($lr);
	    return false;
	}
	if ($num > 0) {
	    $_SESSION['alert'][] = 'message:wrong_data:orakBetoltese:van már betöltve óra az adott napon:'.$dt;
	    db_close($lr);
	    return false;
	}

	// Ha az órarendi órákat töltjük be, akkor a nap csak tanítási nap típusú lehet
	$q = "UPDATE nap SET tipus='tanítási nap',orarendiHet=%u WHERE dt='%s'";
	$r = db_query($q, array('fv' => 'orakBetoltese', 'modul' => 'naplo', 'values' => array($orarendiHet, $dt)), $lr);
	if (!$r) {
	    db_close($lr);
	    return false;
	}

	// Órák betöltése
	checkNaplo($dt, $lr);
	db_close($lr);
	return true;

    }

    function specialisNap($dt, $celOra, $het, $nap, $ora, $olr = null) {

        if ($olr == '') $lr = db_connect('naplo', array('fv' => 'specialisNap'));
        else $lr = $olr;
	db_start_trans($lr);

	    // A (speciális) tanítási napokhoz rendelt osztályok
	    $q = "SELECT osztalyId FROM nap LEFT JOIN munkatervOsztaly USING (munkatervId) WHERE dt='%s' 
                        AND tipus IN ('tanítási nap','speciális tanítási nap') AND osztalyId IS NOT NULL"; // null akkor lehet, ha nincs hozzárendelve egyetlen osztály sem egy munkatervhez...
	    $v = array($dt);
	    $osztalyIds = db_query($q, array('fv' => 'specialisNap/osztalyIds', 'modul' => 'naplo', 'result' => 'idonly', 'values' => $v), $lr);
	    if (!is_array($osztalyIds) || count($osztalyIds) == 0) {
		db_rollback($lr, 'specialisNap/#1');
		if ($olr == '') db_close($lr);
		return false;
	    }

	    // Érintett tankörök
	    $q = "SELECT DISTINCT tankorId FROM ".__INTEZMENYDBNEV.".tankorOsztaly WHERE osztalyId IN (".implode(',',$osztalyIds).")";
            $tankorIds = db_query($q, array('fv'=>'specialisNap/tankorIds', 'modul'=>'naplo', 'result'=>'idonly'), $lr);
	    if (!is_array($tankorIds) || count($tankorIds) == 0) {
		db_rollback($lr, 'specialisNap/#2');
		if ($olr == '') db_close($lr);
		return false;
	    }

	    // Órák betöltése sávonként
	    $ok = true;
		for ($i = 0; $i < count($celOra); $i++) {
		    if ($het[$i] != '' and $nap[$i] != '' and $ora[$i] != '') {

        		$napszam = date('w',strtotime($nap[$i]));
			if ($napszam == 0) $napszam = 7;

                    	$q = "INSERT INTO ora (dt,ora,ki,tankorId,teremId,tipus,eredet)
                                SELECT '%s', %u, orarendiOra.tanarId,orarendiOraTankor.tankorId,teremId,'normál','órarend'
                                    FROM orarendiOra LEFT JOIN orarendiOraTankor USING (tanarId, osztalyJel, targyJel)
                                    WHERE orarendiOraTankor.tankorId IS NOT NULL
				    AND tankorId IN (".implode(',', $tankorIds).")
				    AND het=%u
                                    AND nap=%u
				    AND ora=%u
				    AND tolDt<='%s'
				    AND (igDt IS NULL OR igDt>='%s')";
			$v = array($dt, $celOra[$i], $het[$i], $nap[$i], $ora[$i], $dt, $dt);
                    	$r = db_query($q, array('fv' => 'specialisNap', 'modul' => 'naplo', 'values' => $v), $lr);
			if (!$r) $ok = false;

		    } // minden adat megvan
		} // end for
	    if (!$ok) {
		db_rollback($lr, 'specialisNap/#3');
		if ($olr == '') db_close($lr);
		return false;
	    }

	    // speciális tanítási nap-ra állítjuk a tanítási napokat
	    $q = "UPDATE nap SET tipus='speciális tanítási nap',orarendiHet=0 WHERE dt='%s' AND tipus='tanítási nap'";
	    $r = db_query($q, array('fv' => 'specialisNap', 'modul' => 'naplo', 'values' => array($dt)), $lr);
	    if (!$r) {
		db_rollback($lr, 'specialisNap/#4');
		if ($olr == '') db_close($lr);
		return false;
	    }

	db_commit($lr);
        if ($olr == '') db_close($lr);
	return true;
    }

    function getSzabadOrak($dt) {
    
	$q = "SELECT DISTINCT ora FROM ora
		WHERE dt='%s' AND tipus NOT LIKE 'elmarad%%'";
	$foglaltOrak = db_query($q, array('fv' => 'getSzabadOrak', 'modul' => 'naplo', 'result' => 'idonly', 'values' => array($dt)));
	if (is_array($foglaltOrak)) {
	    $szabadOrak = array();
	    for ($i = getMinOra(); $i <= getMaxOra(); $i++) {
		if (!in_array($i, $foglaltOrak)) $szabadOrak[] = $i;
	    }
	}
	
	return $szabadOrak;
	
    }
    
    function orakTorlese($dt, $Orak) {
    

	$lr = db_connect('naplo', array('fv' => 'orakTorlese'));
	
	$q = "DELETE FROM ora WHERE dt='%s' AND ora IN (".implode(',', array_fill(0, count($Orak), '%u')).")";
	array_unshift($Orak, $dt);
	$r = db_query($q, array('fv' => 'orakTorlese', 'modul' => 'naplo', 'values' => $Orak), $lr);
	if (!$r) {
	    db_close($lr);
	    return false;
	}
	
	$q = "UPDATE nap SET tipus='speciális tanítási nap', orarendiHet=0 WHERE dt='%s'";
	$r = db_query($q, array('fv' => 'orakTorlese', 'modul' => 'naplo', 'values' => array($dt)), $lr);
	db_close($lr);
	return $r;
    }
    

?>
