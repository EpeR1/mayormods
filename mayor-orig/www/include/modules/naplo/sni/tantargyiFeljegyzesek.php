<?php

    function getDiakFeljegyzesByTankorId($ADAT) {

        $q = "SELECT dt, megjegyzes FROM sniTantargyiFeljegyzes WHERE diakId = %u AND tankorId = %u ORDER BY dt";
        $v = array($ADAT['diakId'], $ADAT['tankorId']);
        return db_query($q, array('fv' => 'getDiakFeljegyzesByTankorId', 'modul' => 'naplo', 'values' => $v, 'result' => 'keyvaluepair'));

    }

    function getDiakFeljegyzesByDt($ADAT) {

        $q = "SELECT tankorId, megjegyzes FROM sniTantargyiFeljegyzes WHERE diakId = %u AND dt = '%s'";
        $v = array($ADAT['diakId'], $ADAT['dt']);
        return db_query($q, array('fv' => 'getDiakFeljegyzesByDt', 'modul' => 'naplo', 'values' => $v, 'result' => 'keyvaluepair'));

    }

    function tantargyiFeljegyzesRogzites($Param) {

	$q = "REPLACE INTO sniTantargyiFeljegyzes (diakId, tankorId, dt, megjegyzes) VALUES (%u, %u, '%s', '%s')";
	return db_query($q, array('fv' => 'tantargyiFeljegyzesRogzites', 'modul' => 'naplo', 'values' => $Param));

    }

?>
