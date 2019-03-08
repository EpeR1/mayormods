<?php

    if (_RIGHTS_OK !== true) die();
    if (!__NAPLOADMIN && !__VEZETOSEG) {
	$_SESSION['alert'][] = 'page:insufficient_access';
    } else {

	require_once('include/share/date/names.php');
	require_once('include/modules/naplo/share/ora.php');
	require_once('include/modules/naplo/share/file.php');
	require_once('include/modules/naplo/share/intezmenyek.php');
	require_once('include/share/print/pdf.php');
	require_once('include/share/str/tex.php');

	$tolDt = readVariable($_POST['tolDt'], 'datetime');
	$igDt = readVariable($_POST['igDt'], 'datetime');
	if (defined('__TANEV')) {
	    if (!isset($tolDt)) $tolDt = $_TANEV['kezdesDt'];
	    if (!isset($igDt)) $igDt = date('Y-m-d');
	    initTolIgDt(__TANEV, $tolDt, $igDt);
	}

	if ($action == 'naploGeneralas') {

	    $filename = fileNameNormal('helyettesitesiNaplo-'.date('Y-m-d'));
	    if (naploGeneralas($filename, $tolDt, $igDt))
		header('Location: '.location('index.php?page=session&f=download&download=true&dir=naplo/nyomtatas/helyettesitesinaplo&file='.$filename.'.pdf'));

	}	

        $TOOL['datumTolIgSelect'] = array('tipus' => 'sor', 'tolParamName' => 'tolDt', 'igParamName' => 'igDt',
            'tolDt' => $_TANEV['kezdesDt'], 'igDt' => $_TANEV['zarasDt'],
            'hanyNaponta' => 1, 'post'=>array('osztalyId', 'diakId', 'targySorrend')
	);
	getToolParameters();

    }

?>
