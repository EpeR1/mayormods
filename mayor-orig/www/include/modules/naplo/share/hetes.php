<?php

    function getHetes($osztalyId=null, $dt = null) {
	    if (!isset($dt)) {
    		$q = "SELECT * FROM hetes WHERE osztalyId=%u AND dt=(SELECT MAX(dt) FROM hetes WHERE osztalyId=%u GROUP BY sorszam ORDER BY dt)";
    		$v = array($osztalyId, $osztalyId);
	    } else {
    		$q = "SELECT * FROM hetes WHERE osztalyId=%u AND dt=(SELECT MAX(dt) FROM hetes WHERE osztalyId=%u AND dt<='%s' GROUP BY sorszam ORDER BY dt LIMIT 1)";
    		$v = array($osztalyId, $osztalyId, $dt);
	    }
    	    return db_query($q, array('fv' => 'getHetesek', 'modul' => 'naplo', 'result' => 'assoc', 'keyfield' => 'sorszam', 'values' => $v));
    }

    function getHetesek($osztalyId=null, $dt = null) {

	if ($osztalyId=='') {
	    if (!isset($dt)) {
    		$q = "SELECT * FROM hetes ORDER BY dt,sorszam";
		$v = array();
	    } else {
    		$q = "SELECT * FROM hetes WHERE dt<='%s' ORDER BY dt,sorszam";
    		$v = array($dt);
	    }
	} else {
	    if (!isset($dt)) {
    		$q = "SELECT * FROM hetes WHERE osztalyId=%u ";
    		$v = array($osztalyId);
	    } else {
    		$q = "SELECT * FROM hetes WHERE osztalyId=%u AND dt<'%s'";
    		$v = array($osztalyId, $dt);
	    }
	}
    	return db_query($q, array('fv' => 'getHetesek', 'modul' => 'naplo', 'result' => 'multiassoc', 'keyfield'=>'osztalyId', 'values' => $v));

    }

    function hetesFelvetel($ADAT) {                                                                                                                                                                        
        for ($i = 1; $i < 3; $i++) {                                                                                                                                                                       
            if (isset($ADAT['hetes'][$i])) 
		$q = "REPLACE INTO hetes (osztalyId,dt,sorszam,diakId) VALUES (%u, '%s', $i, %u)";                                                                              
            else                                                                                                                                                                                           
                $q = "DELETE FROM hetes WHERE osztalyId=%u AND dt='%s' AND sorszam=$i";                                                                                                                    

            $v = array($ADAT['osztalyId'], $ADAT['dt'], $ADAT['hetes'][$i]);                                                                                                                               
            db_query($q, array('fv' => 'hetesFelvetel', 'modul' => 'naplo', 'values' => $v));                                                                                                              
        }                                                                                                                                                                                                  
    }                           
?>
