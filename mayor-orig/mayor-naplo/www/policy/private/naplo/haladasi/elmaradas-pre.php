<?php

    if (_RIGHTS_OK !== true) die();

    if (!__NAPLOADMIN && !__VEZETOSEG) {

	$_SESSION['alert'][] = 'page:insufficient_access';

    } else {

	require_once('include/modules/naplo/share/tanar.php');
	$Tanarok = getTanarok(array('tanev' => __TANEV, 'beDt'=>$_TANEV['kezdesDt'],'kiDt'=>$_TANEV['zarasDt'],'result' => 'assoc'));
	$Elmaradas = getHaladasiElmaradas();

    }

?>
