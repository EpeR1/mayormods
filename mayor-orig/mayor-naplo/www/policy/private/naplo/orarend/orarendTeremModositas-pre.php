<?php

    if (_RIGHTS_OK !== true) die();
    if (!__NAPLOADMIN && !__VEZETOSEG) {

	$_SESSION['alert'][] = 'page:insufficient_access';

    } else {

	$ADAT['tanev'] = $tanev = readVariable($_POST['tanev'], 'numeric unsigned', __TANEV);
	if ($tanev = __TANEV) $_TA = $_TANEV;
	else $_TA = getTanevAdat($tanev);

	if (isset($tanev)) {

    	    require_once('include/modules/naplo/share/tanar.php');
    	    require_once('include/modules/naplo/share/terem.php');
    	    require_once('include/modules/naplo/share/tankor.php');
    	    require_once('include/modules/naplo/share/orarend.php');

    	    require_once('include/share/date/names.php');

	    $ADAT['napiMinOra'] = getMinOra(array('tanev' => $tanev));
	    $ADAT['napiMaxOra'] = getMaxOra(array('tanev' => $tanev));
    	    $ADAT['dt'] = $dt = readVariable($_POST['dt'], 'datetime', $_TA['kezdesDt']);

    	    $ADAT['tanarok'] = getTanarok(array('tanev' => $tanev, 'result' => 'assoc'));
    	    $ADAT['termek'] = getTermek(array('tipus'=>array()));
    	    $ADAT['tankorok'] = getTankorok(array("tanev=$tanev"));
    	    for ($i = 0; $i < count($ADAT['tankorok']); $i++) {
        	$tankorId = $ADAT['tankorok'][$i]['tankorId'];
        	$ADAT['tankorok'][$i]['tankorAdat'] = getTankorAdat($tankorId, $tanev);
        	$ADAT['tankorIndex'][$tankorId] = $i;
    	    }

	    $ADAT['orarendiHetek'] = getOrarendiHetek(array('tanev' => $tanev, 'tolDt' => $dt, 'igDt' => $dt,'felsohatar'=>99));
	    $ADAT['orak'] = array();
	    if (isset($ADAT['napiMaxOra']) && isset($ADAT['napiMinOra'])) {
		for ($n = $ADAT['napiMinOra']; $n <= $ADAT['napiMaxOra']; $n++) $ADAT['orak'][] = $n;
	    } else {
		$_SESSION['alert'][] = 'message:nincs órarend?:'.$ADAT['napiMaxOra'].', '.$ADAT['napiMinOra'];
		$ADAT['orak'] = array(0,1,2,3,4,5,6,7,8,9);
	    }

	    if ($action == 'orarendTeremModositas') {

    		$ADAT['tanarId'] = readVariable($_POST['tanarId'], 'numeric unsigned', null);
    		$ADAT['het'] = readVariable($_POST['het'], 'numeric unsigned', null, $ADAT['orarendiHet']);
    		$ADAT['nap'] = readVariable($_POST['nap'], 'numeric unsigned', null, array(1,2,3,4,5,6,7));
    		$ADAT['ora'] = readVariable($_POST['ora'], 'numeric unsigned', null, $ADAT['orak']);

		if (isset($ADAT['tanarId']) && isset($ADAT['het']) && isset($ADAT['nap']) && isset($ADAT['ora'])) {

		    $ADAT['termek'] = getTermek(array('result' => 'assoc'));
		    $ADAT['orarendiOra'] = getOrarendiOraAdat($ADAT);
		    $ADAT['foglaltTermek'] = getFoglaltTeremekByOrarendiOra($ADAT);

		    $ADAT['teremIds'] = array_keys($ADAT['termek']);
	
    		    $ADAT['teremId'] = readVariable($_POST['teremId'], 'numeric unsigned', intval($ADAT['orarendiOra']['teremId']), $ADAT['teremIds']);
		    if ($ADAT['teremId'] != $ADAT['orarendiOra']['teremId']) {
			if ($tanarId = teremModositas($ADAT)) { // ütközés esetén az ütkörő óra adatait 
			    $ADAT['tanarId'] = $tanarId;
			    $ADAT['orarendiOra'] = getOrarendiOraAdat($ADAT);
			} else {
			    $ADAT['orarendiOra']['teremId'] = $ADAT['teremId'];
			}
			$ADAT['foglaltTermek'] = getFoglaltTeremekByOrarendiOra($ADAT);
		    }

		}

	    }

	}

	$TOOL['tanevSelect'] = array('tipus' => 'cella', 'paramName' => 'tanev', 'post' => array());
	$TOOL['datumSelect'] = array(
    	    'tipus' => 'cella', 'post' => array('tanev'),'paramName' => 'dt', 'hanyNaponta' => 1,
    	    'override' => true,'tolDt' => $_TA['kezdesDt'], 'igDt' => $_TA['zarasDt'],
	);
	getToolParameters();

    }

?>
