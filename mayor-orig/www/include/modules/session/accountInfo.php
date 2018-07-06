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

?>
