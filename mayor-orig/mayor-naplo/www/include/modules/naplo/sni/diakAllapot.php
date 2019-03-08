<?php

    function getDiakAllapot($diakId) {

	$v = array($diakId);
	$q = "SELECT * FROM sniDiakAllapot WHERE diakId=%u";
	$ret = db_query($q, array('fv' => 'getDiakAllapot','modul' => 'naplo','values' => $v, 'result' => 'assoc', 'keyfield' => 'szemeszter'));
	// Gyengeségek és erősségek riorizált listája
	$q = "SELECT * FROM sniDiakGyengesegErosseg WHERE diakId=%u ORDER BY prioritas";
	$ret2 = db_query($q, array('fv' => 'getDiakAllapot/gyengeségek, erősségek','modul' => 'naplo','values' => $v, 'result' => 'multiassoc', 'keyfield' => 'szemeszter'));
	foreach ($ret2  as $szemeszter => $gyeAdat) {
	    foreach ($gyeAdat as $key => $value) {
		$ret[ $value['szemeszter'] ][ $value['gyengesegErosseg'] ][] = array(
		    'leiras' => $value['leiras'],
		    'prioritas' => $value['prioritas']
		);
	    }
	}

	return $ret;


    }

    function sniDiakAllapotRogzites($Parameters) {

	$lr = db_connect('naplo', array('fv' => 'sniDiakAllapotRogzites'));
	db_start_trans($lr);

	$Param = $Parameters['diakAllapot'];
	$diakId = $Param['diakId']; $szemeszter = $Param['szemeszter'];
	// Korábbi bejegyzés törlése
	$val = array($Param['diakId'], $Param['szemeszter']);
	$q = "DELETE FROM `sniDiakAllapot` WHERE `diakId` = %u AND `szemeszter` = %u";
	db_query($q, array('fv' => 'sniDiakAllapotRogzites', 'modul' => 'naplo', 'values' => $val), $lr);
	$q = "DELETE FROM `sniDiakGyengesegErosseg` WHERE `diakId` = %u AND `szemeszter` = %u";
	db_query($q, array('fv' => 'sniDiakAllapotRogzites', 'modul' => 'naplo', 'values' => $val), $lr);
	// Paraméterek feldolgozása
	$pattern = $v = array();
	foreach ($Param as $attr => $value) {
	    if (in_array($attr, array('gyengesegLeiras','gyengesegPrioritas','erossegLeiras','erossegPrioritas'))) continue;
	    if (in_array($attr, array('diakId','szemeszter','vizsgalatTanarId','priorizalas')))
		if ($value == '') { $pattern[] = '%s'; $value = 'NULL'; }
		else $pattern[] = "%u";
	    else
		if ($attr == 'vizsgalatDt' && $value == '') { $pattern[] = '%s'; $value = 'NULL'; }
		else $pattern[] = "'%s'";
	    $v[] = $value;
	}
	// új bejegyzés beszúrása
	$q = "INSERT INTO `sniDiakAllapot` (`".implode('`,`',array_keys($Param))."`) VALUES (".implode(',', $pattern).")";
	$ret = db_query($q, array('fv' => 'sniDiakAllapotRogzites', 'modul' => 'naplo', 'values' => $v), $lr);
	if (!$ret) { 
	    db_rollback($lr, 'Az SNI adatok módosítása nem sikerült. Visszaállítjuk az eredeti állapotot...'); 
	    db_close($lr); return false;
	}
	// Gyengeségek/Erősségek rögzítése
	$Param = $Parameters['gyengesegekErossegek'];
	$v = array();
	foreach (array('gyengeseg','erosseg') as $key => $gye) {
	    if (is_array($Param[$gye.'Leiras'])) {
		foreach ($Param[$gye.'Leiras'] as $index => $leiras)  if ($leiras != '') { // Lehessen törölni így...
		    array_push($v, $diakId, $szemeszter, ($key+1), $leiras, $Param[$gye.'Prioritas'][$index]);
		}
	    }
	}
	if (count($v) != 0) {
	    $q = "INSERT INTO `sniDiakGyengesegErosseg` VALUES ".implode(',', array_fill(0, (count($v)/5), "(%u,%u,%u,'%s',%u)"));
	    $ret = db_query($q, array('fv' => 'sniDiakAllapotRogzites/Gyengeségek-erősségek', 'modul' => 'naplo', 'values' => $v), $lr);
	    var_dump($Param);
	}

	db_commit($lr);
	db_close($lr);
	return true;

    }

    function sniDiakAdatRogzites($Param) {

	// diak.fogytekossag módosítása
	$q = "UPDATE diak SET `fogyatekossag`='%s' WHERE diakId=%u";
	db_query($q, array('fv' => 'sniDiakAdatRogzites', 'modul' => 'naplo_intezmeny', 'values' => array(implode(',', $Param['fogyatekossag']), $Param['diakId'])));
	unset($Param['fogyatekossag']);
	// sniDiakAdat modosítás
	$q = "REPLACE INTO `sniDiakAdat` (`diakId`, `kulsoInfo`, `mentorTanarId`) VALUES (%u, '%s', %u)";
	return db_query($q, array('fv' => 'sniDiakAdatRogzites', 'modul' => 'naplo', 'values' => $Param));

    }

?>
