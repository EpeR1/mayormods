<?php

    if (_RIGHTS_OK !== true) die();

    require('include/share/session/close.php');

    closeSession();
    closeOldAndIdleSessions();
    if (defined('_ALLOW_SULIX_SSO') && _ALLOW_SULIX_SSO===true) {
	session_start();
	unset($_SESSION['portalLoggedPassword']);
	unset($_SESSION['szuloDiakIdOk']);
	session_destroy();
	header('Location: /');
    } else {
//	session_start();
//	unset($_SESSION['szuloDiakIdOk']);
//	session_destroy();
	session_start();
	session_unset();
	session_destroy();
	session_write_close();
	setcookie(session_name(),'',0,'/');
	session_regenerate_id(true);
	header('Location: index.php');
    }

?>
