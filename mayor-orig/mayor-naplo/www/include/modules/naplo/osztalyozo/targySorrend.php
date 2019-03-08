<?php
    function targySorrendValtas($osztalyId, $sorrendNev, $targyId, $irany = 'fel') {

	// A tárgy aktuális sorszámát lekérdezzük...
	$q = "SELECT sorszam FROM targySorszam WHERE osztalyId=%u AND targyId=%u AND sorrendNev='%s'";
	$v = array($osztalyId, $targyId, $sorrendNev);
	$s = db_query($q, array('fv' => 'targySorrendValtas', 'modul' => 'naplo', 'result' => 'value', 'values' => $v));

	$q = "UPDATE targySorszam SET sorszam=%u-sorszam WHERE osztalyId=%u AND sorrendNev='%s' AND sorszam IN (%u, %u)";
	if ($irany == 'fel' && $s > 0) $v = array((2*$s-1), $osztalyId, $sorrendNev, $s, ($s-1));
	elseif ($irany == 'le') $v = array((2*$s+1), $osztalyId, $sorrendNev, $s, ($s+1));
	else return false;

	return db_query($q, array('fv' => 'targySorrendValtas', 'modul' => 'naplo', 'values' => $v));
	
    }

    function checkTargySor($osztalyId, $sorrendNev, $Targyak) {

	$q = "SELECT COUNT(sorszam) AS db FROM targySorszam WHERE osztalyId=%u AND sorrendNev='%s'";
	$v = array($osztalyId, $sorrendNev);
	$db = db_query($q, array('fv' => 'checkTargySor', 'modul' => 'naplo', 'result' => 'value', 'values' => $v));
	if ($db == 0 && count($Targyak) > 0) {
	    // feltöltjük
	    $v = $V = array();
	    for ($i = 0; $i < count($Targyak); $i++) {
		$V[] = "(%u, %u, '%s', %u)";
		array_push($v, $osztalyId, $Targyak[$i]['targyId'], $sorrendNev, $i);
	    }
	    $q = "INSERT INTO targySorszam (osztalyId, targyId, sorrendNev, sorszam) VALUES ".implode(',', $V);
	    db_query($q, array('fv' => 'checkTargySor', 'modul' => 'naplo', 'values' => $v));
	}

    }

    function ujTargySorrend($osztalyId, $sorrendNev, $targyIds) {

	$q = "DELETE FROM targySorszam WHERE osztalyId=%u AND sorrendNev='%s'";
	db_query($q, array('fv' => 'usTargySorrend', 'modul' => 'naplo', 'values' => array($osztalyId, $sorrendNev)));

	if (count($targyIds) > 0) {
	    $v = $V = array();
	    for ($i = 0; $i < count($targyIds); $i++) {
		$V[] = "(%u, %u, '%s', %u)";
		array_push($v, $osztalyId, $targyIds[$i], $sorrendNev, $i);
	    }
	    $q = "INSERT INTO targySorszam (osztalyId, targyId, sorrendNev, sorszam) VALUES ".implode(',', $V);
	    db_query($q, array('fv' => 'usTargySorrend', 'modul' => 'naplo', 'values' => $v));
	}

	return true;

    }

?>
