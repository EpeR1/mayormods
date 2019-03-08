<?php

    if (_RIGHTS_OK !== true) die();
    if (!__NAPLOADMIN && !__VEZETOSEG) $_SESSION['alert'][] = 'page:insufficient_access';


    $ADAT['tanev'] = $tanev = readVariable($_POST['tanev'], 'numeric unsigned', __TANEV);
    if ($tanev = __TANEV) $_TA = $_TANEV;
    else $_TA = getTanevAdat($tanev);
    if (isset($tanev)) {

	require_once('include/modules/naplo/share/tanar.php');
	require_once('include/modules/naplo/share/terem.php');
	require_once('include/modules/naplo/share/tankor.php');
	require_once('include/modules/naplo/share/orarend.php');

	require_once('include/share/date/names.php');

	$ADAT['dt'] = $dt = readVariable($_POST['dt'], 'datetime', $_TA['kezdesDt']);

	$ADAT['tanarok'] = getTanarok(array('tanev' => $tanev, 'result' => 'assoc'));
	$ADAT['termek'] = getTermek(array('tipus'=>array()));
	$ADAT['tankorok'] = getTankorok(array("tanev=$tanev"));
	for ($i = 0; $i < count($ADAT['tankorok']); $i++) {
    	    $tankorId = $ADAT['tankorok'][$i]['tankorId'];
    	    $ADAT['tankorok'][$i]['tankorAdat'] = getTankorAdat($tankorId, $tanev);
    	    $ADAT['tankorok'][$i]['hetiOraszam'] = 0;
    	    for ($j = 0; $j < count($ADAT['tankorok'][$i]['tankorAdat'][$tankorId]); $j++)  // Szemeszterenként végigmenve
        	$ADAT['tankorok'][$i]['hetiOraszam'] += $ADAT['tankorok'][$i]['tankorAdat'][$tankorId][$j]['oraszam'];
    	    if ($j != 0) {
        	$ADAT['tankorok'][$i]['hetiOraszam'] /= $j;
    	    }
    	    $ADAT['tankorIndex'][$tankorId] = $i;
	}

	$ADAT['orarendiHetek'] = getOrarendiHetek(array('tanev' => $tanev, 'tolDt' => $dt, 'igDt' => $dt, 'csakOrarendbol'=>false));

	// orarendiOra - tankor ellenőrzés
	$ADAT['check']['orarendiOraTankor'] = checkOrarendiOraTankor($ADAT);
	// tankör óraszám ellenőrzés
	$ADAT['check']['tankorOraszam'] = checkTankorOraszam($ADAT);
	// hiányzó termek
	$ADAT['check']['hianyzoTermek'] = checkHianyzoTermek($ADAT);
	// teremütközés
	$ADAT['check']['teremUtkozes'] = checkTeremUtkozes($ADAT);

    }

    $TOOL['tanevSelect'] = array('tipus' => 'cella', 'paramName' => 'tanev', 'post' => array());
    $TOOL['datumSelect'] = array(
        'tipus' => 'cella', 'post' => array('tanev'),'paramName' => 'dt', 'hanyNaponta' => 1,
        'override' => true,'tolDt' => $_TA['kezdesDt'], 'igDt' => $_TA['zarasDt'],
    );
    getToolParameters();

?>
