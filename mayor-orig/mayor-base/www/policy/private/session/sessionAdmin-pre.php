<?php

    if (_RIGHTS_OK !== true) die();

    if (_POLICY != 'private' || !memberOf(_USERACCOUNT, $AUTH[_POLICY]['adminGroup'])) {
	$_SESSION['alert'][] = 'page:insufficient_access';
    } else {

	if ($action == 'deleteSession') {

	    $policy = readVariable($_POST['delPolicy'], 'enum', null, $POLICIES);
	    $userAccount = readVariable($_POST['delSessionID'], 'string', null);
	    if (isset($policy) && isset($userAccount)) {
		deleteSession($userAccount, $policy);
	    } else { $_SESSION['alert'][] = 'message:wrong_data:userAccount,policy:'.$policy.':'.$userAccount; }
	
	}
	$ADAT['session'] = getSessions();
//echo '<pre>'; var_dump($ADAT); echo '</pre>';

    }


?>
