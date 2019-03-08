<?php

    if (_RIGHTS_OK !== true) die();
    if (!__NAPLOADMIN && !__VEZETOSEG && !__TITKARSAG && !__OSZTALYFONOK) {
	$_SESSION['alert'][] = 'page:insufficient_access';
    } else {

	require_once('include/modules/naplo/share/osztaly.php');
	require_once('include/modules/naplo/share/tankor.php');
	require_once('include/modules/naplo/share/diak.php');
	require_once('include/modules/naplo/share/targy.php');
	require_once('include/modules/naplo/share/file.php');

	$sorrendNev = readVariable($_POST['sorrendNev'], 'enum', null, getTargySorrendNevek(__TANEV));
	if (!__NAPLOADMIN && !__VEZETOSEG && !__TITKARSAG) $_POST['osztalyId'] = $osztalyId = $_OSZTALYA[0]; // osztályfőnök - saját osztálya
	else $osztalyId = readVariable($_POST['osztalyId'], 'numeric unsigned', null);
	if (is_array($_POST['targyId']) && count($_POST['targyId']) > 0) 
	    $_POST['targyId'] = $targyId = readVariable($_POST['targyId'][0], 'numeric unsigned', null);


	if (isset($osztalyId) && isset($sorrendNev)) {

	    if ($action == 'sorrendValtas') { // Javascript nélküli eset - fel/le lépkedés

		if (isset($_POST['fel'])) targySorrendValtas($osztalyId, $sorrendNev, $targyId, 'fel');
		elseif (isset($_POST['le'])) targySorrendValtas($osztalyId, $sorrendNev, $targyId, 'le');

	    } elseif ($action == 'ujSorrend' && is_array($_POST['targyIds']) && count($_POST['targyIds']) > 0) {
		ujTargySorrend($osztalyId, $sorrendNev, $_POST['targyIds']);

	    }

	    $Targyak = getTanevTargySorByOsztalyId($osztalyId, __TANEV, $sorrendNev);
	    checkTargySor($osztalyId, $sorrendNev, $Targyak);

	}


	if (__NAPLOADMIN || __VEZETOSEG || __TITKARSAG)
    	    $TOOL['osztalySelect'] = array('tipus' => 'cella', 'paramName' => 'osztalyId', 'post' => array('sorrendNev'));
        $TOOL['targySorrendSelect'] = array('tipus' => 'cella', 'post' => array('osztalyId'));

        getToolParameters();

    }

?>
