<?php
/*
    Module:	base/session
*/

##############################################################
# searchGroup - csoport kereső függvény
##############################################################

    function searchGroup($attr, $pattern, $searchAttrs = array('groupCn, groupDesc'), $toPolicy = _POLICY) {
    
	global $AUTH;
	
	require_once('include/backend/'.$AUTH[$toPolicy]['backend'].'/session/search/searchAccount.php');
	$func = $AUTH[$toPolicy]['backend'].'SearchGroup';
	return $func($attr, $pattern, $searchAttrs, $toPolicy);
    
    }

##############################################################
# deleteGroup - csoport torlese
##############################################################

    function deleteGroup($groupCn, $toPolicy = _POLICY) {
    
	global $AUTH;
	
	require_once('include/backend/'.$AUTH[$toPolicy]['backend'].'/session/search/searchAccount.php');
	$func = $AUTH[$toPolicy]['backend'].'DeleteGroup';
	return $func($groupCn, $toPolicy);
    
    }

?>
