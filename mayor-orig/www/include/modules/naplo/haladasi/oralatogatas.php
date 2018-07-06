<?php

    function oralatogatasBeiras($ADAT) {

	// óralátogatás felvétele/cseréje
	$q = "REPLACE INTO oraLatogatas (oraId, megjegyzes) VALUES (%u, '%s')";
	$v = array($ADAT['oraId'], $ADAT['megjegyzes']);
	$oralatogatasId = db_query($q, array('fv' => 'oralatogatasBeiras', 'modul' => 'naplo', 'result' => 'insert', 'values' => $v));

	if ($oralatogatasId === false) return false;

	// régi tanárhozzárendelések törlése
	$q = "DELETE FROM oraLatogatasTanar WHERE oraLatogatasId=%u";
	db_query($q, array('fv' => 'oralatogatasBeiras', 'modul' => 'naplo', 'values' => array($oralatogatasId)));
	
	// új tanárhozzárendelések felvétele - ha van tanárhozzárendelés
	if (is_array($ADAT['tanarIds']) && count($ADAT['tanarIds']) > 0) {
	    $q = "INSERT INTO oraLatogatasTanar (oraLatogatasId,tanarId) VALUES ".implode(',', array_fill(0, count($ADAT['tanarIds']), "($oralatogatasId, %u)"));
	    return db_query($q, array('fv' => 'oralatogatasBeiras', 'modul' => 'naplo', 'values' => $ADAT['tanarIds']));
	}
	return true;
    }

    function oralatogatasTorles($oraId) {
	$q  = "DELETE FROM oraLatogatas WHERE oraId=%u";
	$v = array($oraId);
	return db_query($q, array('fv' => 'oralatogatasTorles', 'modul' => 'naplo', 'result' => '', 'values' => $v));
    }

?>
