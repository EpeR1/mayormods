<?php

    function addKerdoiv($kerdes, $valaszok) {

	$v = $Pattern = array();
	// ellenőrizük, hogy van-e válasz...
	for ($i = 0; $i < count($valaszok); $i++) if ($valaszok[$i] != '') {
	    $Pattern[] = "(%u, '%s')";
	}

	if (count($Pattern) > 0) {
	    // A kérdés felvétele
	    $q = "INSERT INTO kerdesek (vszam,kerdes) VALUES (%u,'%s')";
	    $kszam = db_query($q, array('fv' => 'addKerdoiv', 'modul' => 'portal', 'result' => 'insert', 'values' => array(count($valaszok), $kerdes)));
	    // A válaszok rögzítése
	    for ($i = 0; $i < count($valaszok); $i++) if ($valaszok[$i] != '') { $v[] = $kszam; $v[] = $valaszok[$i]; }
	    $q = "INSERT INTO valaszok (kszam,valasz) VALUES ".implode(',', $Pattern);
	    return db_query($q, array('fv' => 'addKerdoiv', 'modul' => 'portal', 'values' => $v));
	} else {
	    return false;
	}
    }

    function delKerdoivKerdes($kerdesek) {
	$q = "DELETE FROM kerdesek WHERE sorszam IN (".implode(',',array_fill(0, count($kerdesek), '%u')).")";
	$v = $kerdesek;
	db_query($q, array('fv' => 'delKeroivKerdes', 'modul' => 'portal', 'values'=>$v));
	$q = "DELETE FROM valaszok WHERE kszam IN (".implode(',',array_fill(0, count($kerdesek), '%u')).")";
	db_query($q, array('fv' => 'delKeroivKerdes', 'modul' => 'portal', 'values'=>$v));
    }

?>
