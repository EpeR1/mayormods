<?php

    function getSniDiakAdat($diakId) {

	$q = "SELECT * FROM sniDiakAdat WHERE diakId=%u";
	$v = array($diakId);
	return db_query($q, array('fv' => 'getDiakAdat','modul' => 'naplo','values' => $v, 'result' => 'record'));

    }

    function getHaviOsszegzes($diakId, $dt) {

        $q = "SELECT * FROM sniHaviOsszegzes WHERE diakId=%u AND MONTH(dt)=MONTH('%s')";
        $v = array($diakId, $dt);
        $ret = db_query($q, array('fv' => 'getHaviOsszegzes', 'modul' => 'naplo', 'values' => $v, 'result' => 'record'));

	$q = "SELECT tanarId FROM sniHaviOsszegzesFelelos WHERE haviOsszegzesId = %u";
	$ret['felelos'] = db_query($q, array('fv' => 'getHaviOsszegzes', 'modul' => 'naplo', 'values' => array($ret['haviOsszegzesId']), 'result' => 'idonly'));

	return $ret;

    }

    function sniHaviOsszegzesRogzites($Param) {

	$Felelos = $Param['felelos']; unset($Param['felelos']);
        // Korábbi bejegyzés törlése
        $q = "SELECT haviOsszegzesId FROM sniHaviOsszegzes WHERE diakId = %u AND dt = '%s'";
        $v = array($Param['diakId'], $Param['dt']);
        $Param['haviOsszegzesId'] = db_query($q, array('fv' => 'sniHaviOsszesitesRogzites', 'modul' => 'naplo', 'values' => $v, 'result' => 'value'));
        // Paraméterek feldolgozása
        $pattern = $v = array();
        foreach ($Param as $attr => $value) {
        	if (in_array($attr, array('diakId','haviOsszegzesId','valtozas')))
            	    if ($value == '') { $pattern[] = '%s'; $value = 'NULL'; }
            	    else $pattern[] = "%u";
        	else
        	    $pattern[] = "'%s'";
        	$v[] = $value;
        }
        // új bejegyzés beszúrása
        $q = "REPLACE INTO `sniHaviOsszegzes` (`".implode('`,`',array_keys($Param))."`) VALUES (".implode(',', $pattern).")";
        $id = db_query($q, array('fv' => 'sniHaviOsszegzesRogzites', 'modul' => 'naplo', 'values' => $v, 'result' => 'insert'));
	// felelosok törlése
	$q = "DELETE FROM `sniHaviOsszegzesFelelos` WHERE haviOsszegzesId = %u";
	$ret = db_query($q, array('fv' => 'sniHaviOsszegzesRogzites', 'modul' => 'naplo', 'values' => array($id)));
	// új felelősök felvétele
	if (is_array($Felelos) && count($Felelos) > 0) {
	    $q = "INSERT INTO `sniHaviOsszegzesFelelos` (`haviOsszegzesId`,`tanarId`) VALUES ".implode(',', array_fill(0, count($Felelos), "($id, %u)"));
	    $ret = db_query($q, array('fv' => 'sniHaviOsszegzesRogzites', 'modul' => 'naplo', 'values' => $Felelos));
	}
	return $ret;
    }
?>
