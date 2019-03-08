<?php
/*
    Modules: base/session

    share --> function getAccountInfo($userAccount, $toPolicy = '') {
    share --> function getUserInfo($userAccount, $toPolicy = '') {
    function changeAccountInfo($userAccount, $toPolicy = '') {
*/

    require('include/share/session/accountInfo.php');

###########################################################
# changeAccountInfo - felhasználói információk módosítása
###########################################################

    function changeAccountInfo($userAccount, $toPolicy = _POLICY) {

        global $AUTH;

        require_once('include/backend/'.$AUTH[$toPolicy]['backend'].'/session/accountInfo.php');
        $func = $AUTH[$toPolicy]['backend'].'ChangeAccountInfo';
        return $func($userAccount, $toPolicy);


    }

    function getUserSettings($userAccount,$toPolicy = _POLICY) {

	$q = "SELECT * FROM settings WHERE userAccount='%s' AND policy='%s'";
	$res = db_query($q, array('modul'=>'login','values'=>array($userAccount,$toPolicy), 'result'=>'record'));
	return $res;

    }

    function setUserSettings($userAccount,$toPolicy,$ADAT) {

	if (!isset($ADAT['skin']) || $ADAT['skin']=='') {
	    $q = "UPDATE settings SET skin=NULL WHERE userAccount='%s' AND policy='%s'";
	    $res = db_query($q, array('modul'=>'login','values'=>array($userAccount,$toPolicy)));
	} else {
	    $q = "UPDATE settings SET skin='%s' WHERE userAccount='%s' AND policy='%s'";
	    $res = db_query($q, array('modul'=>'login','values'=>array($ADAT['skin'],$userAccount,$toPolicy)));
	}
	return $res;

    }

    function setEduroamRecord($ADAT) {

	//	ALTER TABLE eduroam ADD UNIQUE  INDEX (userAccount,policy);
	//	ALTER TABLE eduroam ADD UNIQUE  INDEX (eduroamUID);
	// https://wiki.niif.hu/index.php?title=Sulinet_felhaszn%C3%A1l%C3%B3k_t%C3%B6meges_felvitele

	    $q = "UPDATE eduroam SET eduroamPASSWORD='%s', modositasDt = NOW() WHERE userAccount='%s' AND policy='%s'";
	    $res = db_query($q, array('modul'=>'login','values'=>array($ADAT['eduroamPASSWORD'],$userAccount,$toPolicy)));

    }

    function createEduroamSettings($ADAT) {

	    $q = "INSERT INTO eduroam (userAccount,policy,eduroamUID,eduroamPASSWORD,eduroamAFFILIATION,eduroamDOMAIN)
	    VALUES ('%s','%s','%s','%s','%s','%s')";
	    $values = array(
		$ADAT['userAccount'],
		$ADAT['policy'],
		$ADAT['eduroamUID'],
		$ADAT['eduroamPASSWORD'],
		$ADAT['eduroamAFFILIATION'],
		$ADAT['eduroamDOMAIN'],
	    );
	    $res = db_query($q, array('modul'=>'login','values'=>$values));
	    return $res;
    }

    function getEduroamSettings($userAccount,$toPolicy,$ADAT) {

	$res = false;
	if (_ACCESS_AS == _ADMIN_ACCESS) {
	    $userAccoungt = ($userAccount);
	    $toPolicy = ($toPolicy);
	} else {
	    $userAccount = (_USERACCOUNT);
	    $toPolicy = (_POLICY);
	}

	if ($toPolicy == 'private') {
	    $q = "SELECT * FROM eduroam WHERE userAccount='%s' AND policy='%s'";
	    $res = db_query($q, array('modul'=>'login','values'=>array($userAccount,$toPolicy), 'result'=>'record'));
	}
	return $res;

    }

?>
