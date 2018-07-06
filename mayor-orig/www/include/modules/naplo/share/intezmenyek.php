<?php

    function getIntezmenyek() {

        $q = "SELECT * FROM intezmeny";
	return db_query($q, array('fv' => 'getIntezmenyek', 'modul' => 'naplo_base', 'result' => 'indexed'));
        
    }

    function getTelephelyek($SET = array('result' => 'indexed')) {

	$result = readVariable($SET['result'], 'enum', 'indexed', array('indexed','assoc'));
	$keyfield = 'telephelyId';
        $q = "SELECT * FROM telephely";
	return db_query($q, array('fv' => 'getTelephelyek', 'modul' => 'naplo_intezmeny', 'result' => $result, 'keyfield' => $keyfield));

    }

    function getIntezmenyByRovidnev($rovidnev) {

	$q = "SELECT * FROM intezmeny LEFT JOIN `intezmeny_%s`.`telephely` ON telephely.alapertelmezett=1 WHERE intezmeny.rovidnev='%s'";
	return db_query($q, array('fv' => 'getIntezmenyByRovidnev', 'modul' => 'naplo_base', 'result' => 'record', 'values' => array($rovidnev, $rovidnev)));

    }

    function getTanevek($tervezett = false) {
    	
        $q = "SELECT DISTINCT tanev FROM szemeszter";
        if (!$tervezett) $q .= " WHERE statusz != 'tervezett'";
	return db_query($q, array('fv' => 'getTanevek', 'modul' => 'naplo_intezmeny', 'result' => 'idonly'));
        
    }

    
    function updateSessionIntezmeny($intezmeny) {


    	$lr = db_connect('naplo_base', array('fv' => 'updateSessionIntezmeny'));
    	if (!$lr) return false;

    	if ($intezmeny != '') {

    		$intDb = intezmenyDbNev($intezmeny);

		// telephelyId lekérdezése
		$q = "SELECT `telephelyId` FROM `%s`.telephely WHERE alapertelmezett=1 LIMIT 1";
		$telephelyId = db_query($q, array('fv' => 'updateSessionIntezmeny/telephely', 'modul' => 'naplo_base', 'values' => array($intDb), 'result' => 'value'), $lr);
		if ($telephelyId != '') $telephelyIdPattern = '%u';
		else $telephelyIdPattern = 'NULL';
    		$q = "SELECT tanev,
				IF(ABS(DATEDIFF(zarasDt,CURDATE()))<ABS(DATEDIFF(kezdesDt,CURDATE())),
					ABS(DATEDIFF(zarasDt,CURDATE())),
					ABS(DATEDIFF(kezdesDt,CURDATE()))) AS sub
				FROM `%s`.szemeszter WHERE statusz!='tervezett' ORDER BY sub";
		$r = db_query($q, array('fv' => 'updateSessionIntezmeny/tanev', 'modul' => 'naplo_base', 'values' => array($intDb), 'result' => 'indexed'), $lr);
		
    		if ($r===false) { // Ha például nem létező intézményre váltanánk...
    			$_SESSION['alert'][] = 'message:wrong_data:no_database?:'.$intDb;
    			db_close($lr);
    			return false;
    		} elseif (count($r) > 0) {
    			$tanev = $r[0]['tanev'];
    			$q = "UPDATE session SET intezmeny='%s', tanev=%u, telephelyId=$telephelyIdPattern
                		WHERE sessionID='"._SESSIONID."' ";
			$v = array($intezmeny, $tanev, $telephelyId);
    		} else {
    			$q = "UPDATE session SET intezmeny='%s', tanev=NULL, telephelyId=$telephelyIdPattern
                		WHERE sessionID='"._SESSIONID."' ";
			$v = array($intezmeny, $telephelyId);
    		}

    	} else {
    		// Intézmény törlése
    		$q = "UPDATE session SET intezmeny=NULL, tanev=NULL, telephelyId=NULL WHERE sessionID='%s' ";
		$v = array(_SESSIONID);
    	}
    	$r = db_query($q, array('fv' => 'updateSessionIntezmeny/update', 'modul' => 'naplo_base', 'values' => $v), $lr);

    	db_close($lr);

    	return true;

    }

    function updateSessionTanev($tanev) {


    	if (is_numeric($tanev)) {

    	    $intDb = intezmenyDbNev(__INTEZMENY);

    	    $q = "SELECT COUNT(tanev) FROM $intDb.szemeszter WHERE statusz!='tervezett' AND tanev=$tanev";
    	    $num = db_query($q, array('fv' => 'updateSessionTanev', 'modul' => 'naplo_base', 'values' => array($intDb, $tanev), 'result' => 'value'));
    	    if ($num > 0) {
    		$q = "UPDATE session SET tanev=%u WHERE sessionID='"._SESSIONID."' ";
    	    } else {
    		$_SESSION['alert'][] = 'message:nincs_ilyen_tanev:'.$tanev;
		return false;
    	    }

    	} else {
    		// Tanév törlése
    		$q = "UPDATE session SET tanev=NULL WHERE sessionID='"._SESSIONID."' ";
    	}

    	return db_query($q, array('fv' => 'updateSessionTanev', 'modul' => 'naplo_base', 'values' => array($tanev)));

    }

    function updateSessionTelephelyId($telephelyId) {


    	if (is_numeric($telephelyId)) {

    	    $q = "SELECT COUNT(`telephelyId`) FROM `telephely` WHERE `telephelyId`=%u";
	    $v = array($telephelyId);
    	    $num = db_query($q, array('fv' => 'updateSessionTelephely', 'modul' => 'naplo_intezmeny', 'values' => array($telephelyId), 'result' => 'value'));
    	    if ($num == 1) {
    		$q = "UPDATE `session` SET `telephelyId`=%u WHERE `sessionID`='"._SESSIONID."' ";
    	    } else {
    		$_SESSION['alert'][] = 'message:nincs_ilyen_telephelyId:'.$telephelyId;
		return false;
    	    }

    	} else {
    		// telephelyId törlése
    		$q = "UPDATE `session` SET `telephelyId`=NULL WHERE `sessionID`='"._SESSIONID."' ";
    	}

    	return db_query($q, array('fv' => 'updateSessionTelephely', 'modul' => 'naplo_base', 'values' => array($telephelyId)));

    }

?>
