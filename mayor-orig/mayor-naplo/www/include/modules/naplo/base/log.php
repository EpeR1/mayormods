<?php
/*
    module:	naplo
    version:	3.0

    function logAction($SET = array('actionId' => null, 'szoveg' => '', 'tabla' => '', 'values' => array()), $olr = null)
	Logbejegyzést készít, belerakja a datetime-ot, az action-t és a _USERACCOUNT-ot, ...
    checkReloadAction($actionId, $action = '', $tabla = '', $olr = '')
*/

    function logAction($SET = array('actionId' => null, 'szoveg' => '', 'tabla' => '', 'values' => array()), $olr = null) {
    
	global $action;
	
	$ip = $_SERVER['REMOTE_ADDR'];
	$v = mayor_array_join(array($ip, $SET['tabla'], $action), $SET['values'], array($SET['actionId']));
	$q = "INSERT INTO ".__TANEVDBNEV.".logBejegyzes (userAccount, dt, ip, tabla, action, szoveg, actionId) VALUES
		('"._USERACCOUNT."', now(), '%s', '%s', '%s', '".$SET['szoveg']."','%s')";
	return db_query($q, array('fv' => 'logAction', 'modul' => 'naplo', 'values' => $v), $olr);

    }

    function checkReloadAction($actionId, $action = '', $tabla = '', $olr = null) {

	
	$q = "SELECT logId FROM ".__TANEVDBNEV.".logBejegyzes WHERE actionId = '%s'";
	$v = array($actionId);
	if ($action != '') { $q .= " AND action='%s'";  $v[] = $action; }
        if ($tabla != '') { $q .= " AND tabla='%s'"; $v[] = $tabla; }

	$r = db_query($q, array('fv' => 'checkReloadAction', 'modul' => 'naplo', 'result' => 'value', 'values' => $v), $olr);

	if ($r===false) {
	    $_SESSION['alert'][] = 'message:sql_query_failure:checkReloadAction:'.$q.':'.$error;
	    return false;
	}
        return ($r === null);

    }    
?>
