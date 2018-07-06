<?php

    if (_RIGHTS_OK !== true) die();
    if (!__NAPLOADMIN && !__VEZETOSEG) {
	$_SESSION['alert'][] = 'page:insufficient_access';
    } else {

	require_once('include/modules/naplo/share/tankor.php');
	require_once('include/modules/naplo/share/tanar.php');
	require_once('include/modules/naplo/share/kerdoiv.php');
	require_once('include/modules/naplo/share/file.php');

	$ADAT['tanev'] = $tanev = readVariable($_POST['tanev'], 'numeric unsigned', __TANEV);

	$ADAT['kerdoiv'] = getOsszesKerdoiv($tanev);
	$ADAT['kerdoivIds'] = array();
	for ($i = 0; $i < count($ADAT['kerdoiv']); $i++) $ADAT['kerdoivIds'][] = $ADAT['kerdoiv'][$i]['kerdoivId'];

	$ADAT['kerdoivId'] = $kerdoivId = readVariable($_POST['kerdoivId'], 'numeric', null, $ADAT['kerdoivIds']);
	if (isset($kerdoivId)) {

	    $ADAT['stat'] = getKerdoivStat($kerdoivId, $tanev);

	}

    }
//echo '<pre>'; var_dump($ADAT['stat']['kerdes'][1]); echo '</pre>';

    $TOOL['tanevSelect'] = array('tipus' => 'cella', 'paramName' => 'tanev', 'post' => array());
    $TOOL['kerdoivSelect'] = array('tipus' => 'cella', 'paramName' => 'kerdoivId', 'kerdoiv' => $ADAT['kerdoiv'], 'post' => array('tanev'));
    getToolParameters();

?>
