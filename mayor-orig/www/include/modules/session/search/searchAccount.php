<?php
/*
    Module:	base/session
*/

##############################################################
# searchAccount - felhasználó kereső függvény
##############################################################


    function searchAccount($attr, $pattern, $searchAttrs = array('userCn'), $toPolicy = _POLICY) {

	global $AUTH;

	require_once('include/backend/'.$AUTH[$toPolicy]['backend'].'/session/search/searchAccount.php');
	$func = $AUTH[$toPolicy]['backend'].'SearchAccount';
	
	return $func($attr, $pattern, $searchAttrs, $toPolicy);
    
    }

##############################################################
# deleteAccount - felhasználó törlése
##############################################################

    function deleteAccount($userAccount, $toPolicy = _POLICY) {

	global $AUTH;
	
	require_once('include/backend/'.$AUTH[$toPolicy]['backend'].'/session/search/searchAccount.php');
	$func = $AUTH[$toPolicy]['backend'].'DeleteAccount';
	return $func($userAccount, $toPolicy);
    
    }


?>
