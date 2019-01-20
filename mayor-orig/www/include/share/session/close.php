<?php
/*
    Modules: base/session

    Az alábbi függvények nem ellenőrzik, hogy van-e jogunk a használatukhoz!
    At ellenőrzés az őket hívó program feladata.
    A visszatérési érték a törölt sorok száma, illetve -1, ha sikertelen a törlés (ld. php kézikönyv)

    function closeSession($sessionID = '')
    function closeUserSessions($userAccount = '', $policy = '')
*/

##########################################################
# Kitörli az összes lejart session-höz tartozó bejegyzést (policy-től függetlenül).
##########################################################

    function closeIdleSessions() {
	if (defined('_SESSION_MAX_IDLE_TIME') and _SESSION_MAX_IDLE_TIME > 0) {
	    $q = "DELETE FROM session WHERE activity + INTERVAL "._SESSION_MAX_IDLE_TIME." HOUR < NOW()";
	    return db_query($q, array('fv' => 'closeIdleSessions', 'modul' => 'login', 'result' => 'affected rows'));
	} else {
	    return true;
	}
    }

##########################################################
# Kitörli az összes adott session-höz tartozó bejegyzést (policy-től függetlenül).
##########################################################

    function closeSession($sessionID = '') {
	// _SESSIONID csak validUser esetén van, de mi lehet, hogy másik policy-ből jöttünk!
	if ($sessionID == '') $sessionID = $_REQUEST['sessionID'];
	_clearSessionCache($sessionID);
	unsetTokenCookies();
	$q = "DELETE FROM session WHERE sessionID='%s'";
	return db_query($q, array('fv' => 'closeSession', 'modul' => 'login', 'result' => 'affected rows', 'values' => array($sessionID)));
    }

##########################################################
# Kitörli az összes adott userAccount/policy-hez tartozó bejegyzést( (esetleg több sessionID-t is).
##########################################################

    function closeUserSessions($userAccount = '', $policy = '') {
	if ($userAccount == '') $userAccount = _USERACCOUNT;
	if ($policy == '') $policy = _POLICY;
	$q = "DELETE FROM session WHERE userAccount='%s' and policy='%s'";
	return db_query($q, array('fv' => 'closeUserSessions', 'modul' => 'login', 'result' => 'affected rows', 'values' => array($userAccount, $policy)));
    }

##########################################################
# Kitörli az összes "elavult" session-t.
##########################################################

    function closeOldSessions() {
	if (defined('_SESSION_MAX_TIME') and _SESSION_MAX_TIME > 0) {
	    $dt = date('Y-m-d H:i:s', mktime(date('H')-_SESSION_MAX_TIME,date('i'),date('s'),date('m'),date('d'),date('Y')));
	    $q = "DELETE FROM session WHERE dt<'%s'";
	    return db_query($q, array('fv' => 'closeOldSessions', 'modul' => 'login', 'result' => 'affected rows', 'values' => array($dt)));
	}
    }

##########################################################
# Kitörli az összes "elavult" és lejart session-t.
##########################################################
    function closeOldAndIdleSessions() {
	if (
	    (defined('SESSION_MAX_TIME') and _SESSION_MAX_TIME > 0)
	    || (defined('_SESSION_MAX_IDLE_TIME') and _SESSION_MAX_IDLE_TIME > 0)
	) {
		
	    $q = ''; $v = array();
	    if (defined('_SESSION_MAX_TIME') and _SESSION_MAX_TIME > 0) {
		$dt = date('Y-m-d H:i:s', mktime(date('H')-_SESSION_MAX_TIME,date('i'),date('s'),date('m'),date('d'),date('Y')));
		$q = "DELETE FROM session WHERE dt<'%s'";
		$v = array($dt);
	    }
	    if (defined('_SESSION_MAX_IDLE_TIME') and _SESSION_MAX_IDLE_TIME > 0) {
		if ($q == '') $q = "DELETE FROM session WHERE activity + INTERVAL "._SESSION_MAX_IDLE_TIME." HOUR < NOW()";
		else $q .= " OR activity + INTERVAL "._SESSION_MAX_IDLE_TIME." HOUR < NOW()";
	    }
	    if ($q == '') return true;
	    else return db_query($q, array('fv' => 'closeOldAndIdleSessions', 'modul' => 'login', 'result' => 'affected rows', 'values' => $v));

	} else {
	    return true;
	}

    }

##########################################################
# Kitörli az összes session-t.
##########################################################

    function closeAllSession() {
	return db_query("DELETE FROM session", array('fv' => 'closeAllSession', 'modul' => 'login', 'result' => 'affected rows'));
    }

?>
