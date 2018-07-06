<?php

   if (_RIGHTS_OK !== true) die();

    if ($skin=='ajax') {

	require_once('include/modules/naplo/share/diak.php');
	require_once('include/modules/naplo/share/osztaly.php');

	$ADAT['ma'] = getDiakBySzulDt($dt);
	$ADAT['osztaly'] = getOsztalyok(__TANEV,array('result'=>'assoc'));

    }

?>
