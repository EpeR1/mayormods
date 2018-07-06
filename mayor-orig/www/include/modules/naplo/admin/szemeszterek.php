<?php

    function idoszakTorles($idoszakId) {
	$q = "DELETE FROM idoszak WHERE idoszakId=%u";
	return db_query($q, array('fv' => 'idoszakTorles', 'modul' => 'naplo_intezmeny', 'values' => array($idoszakId)));
    }

    function idoszakModositas($idoszakId, $tolDt, $igDt) {
	$q = "UPDATE idoszak SET tolDt='%s', igDt='%s' WHERE idoszakId=%u";
	$v = array($tolDt, $igDt, $idoszakId);
	return db_query($q, array('fv' => 'idoszakModositas', 'modul' => 'naplo_intezmeny', 'values' => $v));
    }

    function ujIdoszak($tolDt, $igDt, $tipus, $tanev = '', $szemeszter = '', $idoszakTipusok = '') {
	// dátum ellenőrzés
	if (strtotime($tolDt) > strtotime($igDt)) {
	    $_SESSION['alert'][] = 'message:wrong_data:dt:'.str_replace(':', '.', $tolDt.' - '.$igDt);
	    return false;
	}
	// típus ellenőrzés
	if (!is_array($idoszakTipusok)) $idoszakTipusok = getIdoszakTipusok();
	if (!in_array($tipus, $idoszakTipusok)) {
	    $_SESSION['alert'][] = 'message:wrong_data:idoszak.tipus:'.$tipus;
	    return false;
	}
	// tanev/szemeszter beállítás
	if ($tanev == '' || $szemeszter == '') {
	    $q = "SELECT tanev, szemeszter FROM szemeszter WHERE kezdesDt <= '%s' AND '%s' <= zarasDt";
	    $v = array($igDt, $tolDt);
	    $ret = db_query($q, array('fv' => 'ujIdoszak', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => $v));
	    if (is_array($ret) && count($ret) == 1) {
		$tanev = $ret[0]['tanev'];
		$szemeszter = $ret[0]['szemeszter'];
	    } else {
		return false;
	    }
	}
	// idoszak felvétele
	$q = "INSERT INTO idoszak (tolDt, igDt, tipus, tanev, szemeszter) VALUES ('%s', '%s', '%s', %u, %u)";
	$v = array($tolDt, $igDt, $tipus, $tanev, $szemeszter);
	return db_query($q, array('fv' => 'ujIdoszak', 'modul' => 'naplo_intezmeny', 'values' => $v));
    }

    function getIdoszakTipusok() {
	return getEnumField('naplo_intezmeny', 'idoszak', 'tipus');
    }

?>
