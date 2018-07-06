<?php

    function userAuthentication($userAccount, $userPassword, &$accountInformation, $toPolicy) {
	global $AUTH;
        require_once('include/share/auth/base.php');

	if (file_exists('include/backend/'.$AUTH[$toPolicy]['backend'].'/auth/login.php')) {
            require_once('include/backend/'.$AUTH[$toPolicy]['backend'].'/auth/login.php');
        } else {
	    throw new Exception('Fatal Error');
	}

	//$x = call_user_func( str_replace('-','_',$AUTH[$toPolicy]['backend'])."UserAuthentication",$userAccount, $userPassword, $accountInformation, $toPolicy);	
	switch ($AUTH[$toPolicy]['backend']) {
	    case 'mysql':
		$r = mysqlUserAuthentication($userAccount, $userPassword, $accountInformation, $toPolicy);
		break;
	    case 'ldap':
		$r = ldapUserAuthentication($userAccount, $userPassword, $accountInformation, $toPolicy);
		break;
	    case 'ldap-ng':
		$r = ldap_ngUserAuthentication($userAccount, $userPassword, $accountInformation, $toPolicy);
		break;
	    case 'ldapng':
		$r = ldapngUserAuthentication($userAccount, $userPassword, $accountInformation, $toPolicy);
		break;
	    case 'ads':
		$r = adsUserAuthentication($userAccount, $userPassword, $accountInformation, $toPolicy);
		break;
	    case 'file':
		$r = fileUserAuthentication($userAccount, $userPassword, $accountInformation, $toPolicy);
		break;
	}
	return $r;
    }

?>