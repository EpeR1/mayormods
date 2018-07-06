<?php

    function teremModositas($ADAT) {

        $dt = readVariable($ADAT['dt'], 'datetime', null);
        initTolIgDt($ADAT['tanev'], $dt, $dt);
	$tanevDb = tanevDbNev(__INTEZMENY, $ADAT['tanev']);

	$return = false;

	if (is_array($ADAT['foglaltTermek'][ $ADAT['teremId'] ])) {

	    $return = $ADAT['foglaltTermek'][ $ADAT['teremId'] ]['tanarId'];
	    // A foglalt terem felszabadítása
	    $q = "UPDATE `%s`.orarendiOra SET teremId=NULL WHERE tolDt<='%s' AND '%s'<=igDt AND het=%u AND nap=%u AND ora=%u AND teremId=%u";
	    $v = array($tanevDb, $dt, $dt, $ADAT['het'], $ADAT['nap'], $ADAT['ora'], $ADAT['teremId']);
	    db_query($q, array('fv' => 'teremModositas/foglalt terem felszabadítása', 'modul' => 'naplo', 'values' => $v));
	}
	// teremhozzárendelés módosítása
	$q = "UPDATE `%s`.orarendiOra SET teremId=%u WHERE tolDt <= '%s' AND '%s' <= igDt AND het=%u AND nap=%u AND ora=%u AND tanarId=%u";
	$v = array($tanevDb, $ADAT['teremId'], $dt, $dt, $ADAT['het'], $ADAT['nap'], $ADAT['ora'], $ADAT['tanarId']);
	db_query($q, array('fv' => 'teremModositas/foglalt terem felszabadítása', 'modul' => 'naplo', 'values' => $v));

	return $return;
	
    }

?>
