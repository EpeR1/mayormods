<?php

    if (_RIGHTS_OK !== true) die();
    if (!__NAPLOADMIN && !__TANAR && !__DIAK && !__VEZETOSEG) {
	$_SESSION['alert'][] = 'message:insufficient_access';
    } else {

	require_once('include/modules/naplo/share/hianyzas.php');
	require_once('include/modules/naplo/share/osztaly.php');
	require_once('include/modules/naplo/share/diak.php');
	
	$ADAT['osztalyId'] = $osztalyId = readVariable($_POST['osztalyId'], 'id');
	$ADAT['diakId'] = $diakId = readVariable($_POST['diakId'], 'id', readVariable($_GET['diakId'], 'id'));
	
	if (isset($diakId)) {
	    $ADAT['diakNev'] = getDiakNevById($diakId);
	    $ADAT['igazolatlan'] = getDiakIgazolatlan($diakId);
	}

	$TOOL['osztalySelect'] = array('tipus' => 'cella','paramName' => 'osztalyId', 'post' => array('ho'));
	$TOOL['diakSelect'] = array('tipus'=>'cella', 'diakId' => $diakId, 'post'=>array('tolDt','osztalyId','ho'));
	getToolParameters();

    }

?>
