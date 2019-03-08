<?php

    function ujErtekeles($diakId, $szrId, $targyId, $dt, $minosites, $egyediMinosites) {

	// A korábbi értékelés törlése
	$q = "DELETE FROM szovegesErtekeles WHERE diakId=%u AND szrId=%u AND targyId=%u AND dt='%s'";
	$v = array($diakId, $szrId, $targyId, $dt);
	db_query($q, array('fv' => 'ujErtekeles', 'modul' => 'naplo', 'values' => $v));

	// Szoveges értékelés felvétele
	$q = "INSERT INTO szovegesErtekeles (diakId, szrId, targyId, dt) VALUES (%u, %u, %u, '%s')";
	$v = array($diakId, $szrId, $targyId, $dt);
	$szeId = db_query($q, array('fv' => 'ujErtekeles', 'modul' => 'naplo', 'result' => 'insert', 'values' => $v));
	if (!$szeId) return false;

	// Minősítések felvétele
	if (count($minosites) > 0) { 
	    $q = "INSERT INTO szeMinosites (szeId,minositesId) VALUES ($szeId,".implode("),($szeId,", array_fill(0, count($minosites), '%u')).")";
	    db_query($q, array('fv' => 'ujErtekeles', 'modul' => 'naplo', 'values' => $minosites));
	}
	// Egyedi minősítések felvétele
	if (count($egyediMinosites) > 0) {
	    $v = $V = array();
	    foreach ($egyediMinosites as $szempontId => $egyediMinosites) {
		$V[] = "(%u, %u, '%s')";
		array_push($v, $szeId, $szempontId, $egyediMinosites);
	    }
	    $q = "INSERT INTO szeEgyediMinosites (szeId,szempontId,egyediMinosites) VALUES ".implode(',', $V);
	    db_query($q, array('fv' => 'ujErtekeles', 'modul' => 'naplo', 'values' => $v));
	}

	return true;
    }

    function ujZaroErtekeles($diakId, $szrId, $targyId, $tanev, $szemeszter, $minosites, $egyediMinosites) {

	// A korábbi értékelés törlése
	$q = "DELETE FROM szovegesErtekeles WHERE diakId=%u AND szrId=%u AND targyId=%u AND tanev=%u AND szemeszter=%u";
	$v = array($diakId, $szrId, $targyId, $tanev, $szemeszter);
	db_query($q, array('fv' => 'ujZaroErtekeles', 'modul' => 'naplo_intezmeny', 'values' => $v));

	// Szoveges értékelés felvétele
	$q = "INSERT INTO szovegesErtekeles (diakId, szrId, targyId, dt, tanev, szemeszter) VALUES (%u, %u, %u, CURDATE(), %u, %u)";
	$v = array($diakId, $szrId, $targyId, $tanev, $szemeszter);
	$szeId = db_query($q, array('fv' => 'ujZaroErtekeles', 'modul' => 'naplo_intezmeny', 'result' => 'insert', 'values' => $v));
	if (!$szeId) return false;

	// Minősítések felvétele
	if (count($minosites) > 0) { 
	    $q = "INSERT INTO szeMinosites (szeId,minositesId) VALUES ($szeId,".implode("),($szeId,", array_fill(0, count($minosites), '%u')).")";
	    db_query($q, array('fv' => 'ujZaroErtekeles', 'modul' => 'naplo_intezmeny', 'values' => $minosites));
	}
	// Egyedi minősítések felvétele
	if (count($egyediMinosites) > 0) {
	    $v = $V = array();
	    foreach ($egyediMinosites as $szempontId => $egyediMinosites) {
		$V[] = "(%u, %u, '%s')";
		array_push($v, $szeId, $szempontId, $egyediMinosites);
	    }
	    $q = "INSERT INTO szeEgyediMinosites (szeId,szempontId,egyediMinosites) VALUES ".implode(',', $V);
	    db_query($q, array('fv' => 'ujZaroErtekeles', 'modul' => 'naplo_intezmeny', 'values' => $v));
	}

	return true;
    }

?>
