<?php
/*
    Modules: base/session
*/

    function createAccount($userCn, $userAccount, $userPassword, $toPolicy = _POLICY, $SET = array('category' => null, 'container' => null, 'groups' => '', 'policyAttrs' => array())) {

        global $AUTH;

        require_once('include/backend/'.$AUTH[$toPolicy]['backend'].'/session/createAccount.php');
        $func = $AUTH[$toPolicy]['backend'].'CreateAccount';
        $r = $func($userCn, $userAccount, $userPassword, $toPolicy, $SET);
	$_SESSION['lastCreatedAccount'] = $userAccount;

	return $r;

    }

?>
