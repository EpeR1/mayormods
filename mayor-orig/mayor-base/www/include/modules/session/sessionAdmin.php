<?php

    function getSessions() {
    
	$W = array();
	if (intval(_SESSION_MAX_TIME) != 0) $W[] = "dt + INTERVAL ".intval(_SESSION_MAX_TIME)." HOUR > NOW()";
	if (intval(_SESSION_MAX_IDLE_TIME) != 0) $W[] = "activity + INTERVAL ".intval(_SESSION_MAX_IDLE_TIME)." HOUR > NOW()";
	$q = "SELECT userAccount,dt,policy,sessionID,userCn,studyId,skin,lang,activity,sessionCookie,ip FROM session LEFT JOIN loginLog USING (policy,userAccount,dt)";
	if (count($W) > 0) $q .= " WHERE ".implode(' AND ', $W);
	$q .= " ORDER BY activity DESC";

	$ret = db_query($q, array('fv' => 'getSessions', 'modul' => 'login', 'result' => 'indexed'));
	return $ret;    
    }
    
    function deleteSession($sessionID, $policy) {
    
	$q = "DELETE FROM session WHERE sessionID='%s' AND policy='%s'";
	$v = array($sessionID, $policy);
	return db_query($q, array('fv' => 'deleteSession','modul' => 'login', 'values' => $v));
    
    }

?>
