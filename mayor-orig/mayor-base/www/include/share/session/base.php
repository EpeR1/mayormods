<?php
/*
    Module:	base/session
    Megjegyzés:	a base-ből minden oldalletöltésnél betöltődik
*/
    define('_ADMIN_ACCESS', 0);
    define('_SELF_ACCESS', 1);
    define('_OTHER_ACCESS', 2);

    $AUTH['my']['categories'] = getAccountCategories(_USERACCOUNT, _POLICY); // a függvény itt deklarált

    ######################################################
    # Az aktuális policy backend-jének alapvető session-kezelése. Csatoljuk be, vagy üzenjünk hibát
    ######################################################

    if (file_exists('include/backend/'.$AUTH[_POLICY]['backend'].'/session/base.php'))
	require_once('include/backend/'.$AUTH[_POLICY]['backend'].'/session/base.php');
    else 
	$_SESSION['alert'][] = 'page:file_not_found:'.'include/backend/'.$AUTH[_POLICY]['backend'].'/session/base.php';

/* Függvények */

/*
    function memberOf($userAccount, $group, $toPolicy = _POLICY)

	a függvény becsatolja a backend-nek megfelelő modul filet és használja a [backend]MemberOf() függvényt

    function getAccountCategories($userAccount = '', $toPolicy = _POLICY)

	a függvény lekérdezi a policy globális configfilejában megadott lehető kategóriákba userAccount bele tartozik-e
	használja: memberOf()


    --
    function getBackendAttrs($type = 'Account', $toPolicy = _POLICY)
    function getFailedLoginCount($uid, $sinceDt, $lr='')
    function getLastLoginDt($uid, $lr='')
*/

######################################################
# memberOf - csoport tag-e - session modul
######################################################

    function memberOf($userAccount, $group, $toPolicy = _POLICY) {
    
	global $AUTH;

	if ($AUTH[$toPolicy]['cacheable']=='yes' && $userAccount == _USERACCOUNT) {
	    $r = _queryCache('mOf:'.$group,$toPolicy,'value');
	    if (!is_null($r)) {
		if ($r=='1') $return = true;
		elseif ($r=='0') $return = false;
	    }
	}

	if (is_null($return)) {
	    require_once('include/backend/'.$AUTH[$toPolicy]['backend'].'/session/base.php');
	    $func = $AUTH[$toPolicy]['backend'].'MemberOf';
	    $return = $func($userAccount, $group, $toPolicy);

	    if ($AUTH[$toPolicy]['cacheable']=='yes' && $userAccount == _USERACCOUNT)
		_registerToCache('mOf:'.$group, (($return == true) ? '1':'0'), $toPolicy);
	}

	return $return;
    
    }

######################################################
# getCategories - felhasználó besorolása(i) - session modul
######################################################
    function getAccountCategories($userAccount = '', $toPolicy = _POLICY) {

        global $AUTH;

	if ($userAccount == '') $userAccount = _USERACCOUNT;

	if ($AUTH[$toPolicy]['cacheable']=='yes' && $userAccount == _USERACCOUNT) {
	    $cacheable = true;
	    _maintainCache();
	    $r = _queryCache('aCat', $toPolicy);
	    for ($i=0; $i<count($r); $i++) { // valójában csak egy érték lehet, de azért bent hagyjuk a ciklust
		$_eArr[] = $r[$i]['ertek'];
		$_eArr = explode(';',$r[$i]['ertek']);
		if (is_array($_eArr)) $return = $_eArr;
		else $return[] = $_eArr;
	    }
	} else $cacheable = false;
	if (count($r)>0) return $return;
	else {
    	    $return = array();
	    if (is_array($AUTH[$toPolicy]['categories']))
    		foreach ($AUTH[$toPolicy]['categories'] as $key => $category) {
        	    if (memberOf($userAccount, $category, $toPolicy)) {
			$return[] = $category;
		    }
		    if ($cacheable) _registerToCache('aCat',implode(';',$return),$toPolicy); // ha több csoport tagja is, ";"
    		}
    	    return $return;
	}
    }

    function _queryCache($kulcs, $policy, $rType="indexed") {
	$v = array(_SESSIONID,$kulcs,$policy);
    	return db_query("SELECT `ertek` FROM `cache` WHERE sessionID='%s' AND dt>NOW() - INTERVAL 5 MINUTE AND `kulcs`='%s' AND policy='%s'", array('fv' => 'getAccountCategories', 'modul' => 'login', 'result' => $rType, 'values'=>$v));
    }

    function _registerToCache($kulcs,$ertek,$policy) {
	$v = array(_SESSIONID,$policy,$kulcs,$ertek);
    	db_query("REPLACE INTO `cache` (sessionID,policy,kulcs,ertek,dt) VALUES ('%s','%s','%s','%s',NOW())", array('fv' => 'cache', 'modul' => 'login', 'result' => 'indexed', 'values'=>$v));
    }

    function _maintainCache() {
	$q = "DELETE FROM `cache` WHERE dt<NOW() - INTERVAL 5 MINUTE";
	db_query($q, array('fv' => 'cache', 'modul' => 'login', 'result' => 'indexed', 'values'=>$v));
    }

    function _clearSessionCache($sessionID) {
	$q = "DELETE FROM `cache` WHERE sessionID  IN ('%s','%s')";
	$v = array(_SESSIONID,$sessionID);
	db_query($q, array('debug'=>false,'fv' => 'cache', 'modul' => 'login', 'result' => 'indexed', 'values'=>$v));
    }

######################################################
# getBackendAttrs - az adott policy backend-jéhez tartozó attribútumok - session modul
######################################################

    function getBackendAttrs($type = 'Account', $toPolicy = _POLICY) {

	global $AUTH;
	require_once('include/backend/'.$AUTH[$toPolicy]['backend'].'/session/base.php');

        global $AUTH;
	$attrArrayName = $AUTH[$toPolicy]['backend'].$type.'AttrDef';
	global $$attrArrayName;
	$backendAttrDef = $$attrArrayName;

        // Válogassuk ki az olvasható attribútumokat
	reset($backendAttrDef);
        foreach ($backendAttrDef as $attr => $def) {

            if (!isset($def['rights']) || $def['rights'] == '') {
		$rigths = _DEFAULT_LDAP_RIGHTS; //LDAP???
		$backendAttrDef[$attr]['rights'] = $rights;
	    } else $rights = $def['rights'];

            if ($rights[_ACCESS_AS] != '-') $attrList[] = $attr;

        }
	return array($attrList,$backendAttrDef);

    }

######################################################################
# Utolsó bejelentkezés dátuma
######################################################################

    function getLastLoginDt($toPolicy, $userAccount=_USERACCOUNT, $lr = null) {
	$q = "SELECT dt FROM loginLog WHERE policy='%s' AND userAccount='%s' AND flag=0 ORDER BY dt DESC LIMIT 1";
	return db_query($q , array('fv' => 'getLastLoginDt', 'modul' => 'login', 'result' => 'value', 'values' => array($toPolicy,$userAccount)), $lr);
    }

######################################################################
# Hibás bejelentkezések száma, a legutolsó sikeres bejelentkezés óta
######################################################################

    function getFailedLoginCount($toPolicy, $userAccount=_USERACCOUNT, $lr = null) {
	if ($sinceDt == '') $sinceDt = getLastLoginDt($toPolicy, $userAccount, $lr);
	$q = "SELECT COUNT(*) AS db FROM loginLog WHERE policy='%s' AND userAccount='%s' AND dt>'%s' AND flag>0";
	return db_query($q , array('fv' => 'getFailedLoginCount', 'modul' => 'login', 'result' => 'value', 'values' => array($toPolicy, $userAccount, $sinceDt)), $lr);
    }

?>
