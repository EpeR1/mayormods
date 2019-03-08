<?php

    require_once('include/modules/naplo/share/jegy.php');

    function jegyTorles($jegyId, $jegyAdat = '', $tanev = __TANEV, $olr = '') { // jegyid lehet indexelt tömb is.

        if (!isset($tanev))
	    if (defined('__TANEV')) $tanev = __TANEV;
    	    else return false;

        $tanevDb = tanevDbNev(__INTEZMENY, $tanev);

	// Törlendő jegyek adatai (naplózáshoz)
	$jegyInfo = array();
	if (!is_array($jegyId)) {
	    if ($jegyId == '') {
		return false; // nincs értelmes jegyId
	    } else {
		$jegyId = array($jegyId);
		if (!is_array($jegyAdat)) $jegyInfo[] = getJegyInfo($jegyId[0], $tanev);
		else $jegyInfo = array($jegyAdat);
	    }
	} else {
	    for ($i = 0; $i < count($jegyId); $i++) $jegyInfo[] = getJegyInfo($jegyId[$i], $tanev);
	}

	$lr = ($olr!='') ? $olr : db_connect('naplo_intezmeny');

	    if (count($jegyId)>0) {
		// Naplózás
		for ($i = 0; $i < count($jegyInfo); $i++) {
		    $jegyAdat = $jegyInfo[$i];
		    $param = $jegyAdat['jegyId'].', '.$jegyAdat['diakId'].', '.$jegyAdat['tankorId'].', '.$jegyAdat['dt'].', '.$jegyAdat['jegy'].', '.$jegyAdat['tipus'].', '.$jegyAdat['oraId'].', '.$jegyAdat['dolgozatId'];
    		    logAction(
			array(
			    'szoveg'=>'Jegy törlés: '.$param, 
			    'table'=>'jegy'
			), 
			$lr
		    );
    		}
		// Jegyek törlése
    		$q = "DELETE FROM `%s`.jegy WHERE jegyId IN (".implode(',', array_fill(0, count($jegyId), '%u')).")";
		array_unshift($jegyId, $tanevDb);
    		$r = db_query($q, array('fv' => 'jegyTorles', 'modul' => 'naplo', 'values' => $jegyId), $lr);
	    } else {
		// Miért false? Nincs törlendő jegy - akkor sikeres a törlés // return false;
		$r = true;
	    }
	
	if ($olr=='') db_close($lr);
	return $r;

    }

    function jegyModositas($jegyId, $jegy, $jegyTipus, $tipus, $oraId, $dolgozatId, $megjegyzes) {

	$v = array($jegy, $jegyTipus, $tipus);
	if (!is_null($oraId)) $v[] = $oraId;
	if (!is_null($dolgozatId)) $v[] = $dolgozatId;
	array_push($v, $megjegyzes, $jegyId);
        $q = "UPDATE `jegy` SET modositasDt=NOW(), jegy=%f, jegyTipus='%s', tipus=%u, oraId=".((is_null($oraId))?'NULL':'%u').", dolgozatId=".((is_null($dolgozatId))?'NULL':'%u').", megjegyzes='%s' WHERE jegyId=%u";

        return db_query($q, array('fv' => 'jegyModositas', 'modul' => 'naplo', 'values' => $v), $lr);

    }

?>
