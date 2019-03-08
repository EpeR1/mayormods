<?php

    if (_RIGHTS_OK !== true) die();
    if (__NAPLOADMIN !== true) {
	$_SESSION['alert'][] = 'page:insufficient_access';
    } else {

	require_once('include/modules/naplo/share/osztaly.php');

	if (isset($_POST['osztalyId'])) {
	    $osztalyId = $_POST['osztalyId'];
	    $Csoportok = getCsoportok($osztalyId);
	    $Tankorok = getOsztalyTankorei($osztalyId);
	}
	if (isset($_POST['csoportId'])) {
	    $csoportId = $_POST['csoportId'];
	    $csoportAdatok = getCsoportAdatok($csoportId);
	}

	if ($action == 'ujCsoport') {

	    if (!is_array($_POST['tankorId'])) {
		$_SESSION['alert'][] = 'message:empty_field:tankorId[]';
	    } else {
		$tankorIds = $_POST['tankorId'];
		if ($_POST['csoportNev'] != '') {
		    $csoportNev = $_POST['csoportNev'];
		} else {
		    $csNev = array();
		    for ($i = 0; $i < count($Tankorok); $i++) {
			if (in_array($Tankorok[$i]['tankorId'],$tankorIds)) {
			    $csNev[] = $Tankorok[$i]['tankorNev'] ;
			}
		    }
		    $csoportNev = implode(', ',$csNev);
		}
		$csoportId = ujCsoport($csoportNev, $tankorIds);
		$Csoportok = getCsoportok($osztalyId);
		if ($csoportId) $csoportAdatok = getCsoportAdatok($csoportId);
	    }
	} elseif ($action == 'csoportModositas') {

	    if (!is_array($_POST['tankorId'])) {
		$_SESSION['alert'][] = 'message:empty_field:tankorId[]';
	    } else {
		$tankorIds = $_POST['tankorId'];
		if (csoportModositas($csoportId, $_POST['csoportNev'], $tankorIds)) {
		    $Csoportok = getCsoportok($osztalyId);
		    $csoportAdatok = getCsoportAdatok($csoportId);
		}
	    }	    
	} elseif ($action == 'csoportTorlese') {

	    if (csoportTorles($csoportId)) {
	        $Csoportok = getCsoportok($osztalyId);
		unset($csoportId);
		unset($csoportAdatok);
	    }

	}


	$TOOL['osztalySelect'] = array('tipus'=>'cella');
	getToolParameters();

    }

?>
