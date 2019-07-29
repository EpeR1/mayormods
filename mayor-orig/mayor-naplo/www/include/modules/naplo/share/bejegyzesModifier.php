<?php

    function ujBejegyzes($bejegyzesTipusId, $szoveg, $referenciaDt ,$diakId, $hianyzasDb = null) {


	$dj = getDiakJogviszonyByDt($diakId, date('Y-m-d'));
	if (!in_array($dj['aktualis'], array('jogviszonyban van', 'magántanuló', 'egyéni munkarend', 'vendégtanuló'))) {
	    $_SESSION['alert'][] = 'message:wrong_data:ujBejegyzes:Nincs jogviszonyban!';
	    return false;
	}

	if (is_null($hianyzasDb)) { $hianyzasDb = 'NULL'; $hDbPatt = '%s'; }
	else { $hDbPatt = '%u'; }

	if ($referenciaDt != '') {
    	    if (defined('__USERTANARID')) {
    		$q = "INSERT INTO bejegyzes (bejegyzesTipusId, szoveg, beirasDt, referenciaDt, tanarId, diakId, hianyzasDb)
            		VALUES (%u, '%s', CURDATE(), '%s', %u, %u, $hDbPatt)";
		$v = array($bejegyzesTipusId, $szoveg, $referenciaDt, __USERTANARID, $diakId, $hianyzasDb);
    	    } else {
    		$q = "INSERT INTO bejegyzes (bejegyzesTipusId, szoveg, beirasDt,referenciaDt, tanarId, diakId, hianyzasDb)
            		VALUES (%u, '%s', CURDATE(), '%s', NULL, %u, $hDbPatt)";
		$v = array($bejegyzesTipusId, $szoveg, $referenciaDt, $diakId, $hianyzasDb);
	    }
	} else {
    	    if (defined('__USERTANARID')) {
    		$q = "INSERT INTO bejegyzes (bejegyzesTipusId, szoveg, beirasDt, referenciaDt, tanarId, diakId, hianyzasDb)
            		VALUES (%u, '%s', CURDATE(), NULL, %u, %u, $hDbPatt)";
		$v = array($bejegyzesTipusId, $szoveg, __USERTANARID, $diakId, $hianyzasDb);
    	    } else {
    		$q = "INSERT INTO bejegyzes (bejegyzesTipusId, szoveg, beirasDt,referenciaDt, tanarId, diakId, hianyzasDb)
            		VALUES (%u, '%s', CURDATE(), NULL, NULL, %u, $hDbPatt)";
		$v = array($bejegyzesTipusId, $szoveg, $diakId, $hianyzasDb);
	    }
	}
        return db_query($q, array('fv' => 'ujBejegyzes', 'modul' => 'naplo', 'result' => 'insert', 'values' => $v));

    }

?>
