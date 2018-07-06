<?php

	if (_RIGHTS_OK !== true) die();
	if (!__NAPLOADMIN && !__VEZETOSEG && !__TANAR) {
		$_SESSION['alert'][] = 'page:insufficient_access';
	} else {
		
		require_once('include/modules/naplo/share/tanar.php');
		require_once('include/modules/naplo/share/file.php');
		require_once('include/modules/naplo/share/nap.php');
		require_once('include/share/date/names.php');

		$dt = readVariable($_POST['dt'],'date');
		$igDt = readVariable($_POST['igDt'],'date');
		$tolDt = readVariable($_POST['tolDt'],'date');
		if (!isset($dt) && !isset($tolDt) && !isset($igDt)) {
		    $dt = date('Y-m-d',strtotime('Saturday', strtotime(date('Y-m-d'))));
		    $igDt = $dt;
		    $tolDt = getTanitasiNapVissza(5,$dt);;
		} elseif (isset($dt)) {
		    $igDt = $dt;
		    $tolDt = getTanitasiNapVissza(5,$dt);;
		} else {
		    $igDt = readVariable($_POST['igDt'],'date',$_TANEV['zarasDt']);
		    $tolDt = readVariable($_POST['tolDt'],'date',$_TANEV['kezdesDt']);
		}		

	    if (__VEZETOSEG || __NAPLOADMIN) {
		$ADAT = getElszamolas($tolDt, $igDt);
	    } elseif (defined('__USERTANARID')) {
		$ADAT = getElszamolas($tolDt, $igDt, __USERTANARID);
	    } else {
		// hiba
	    }
	    $ADAT['tanarok'] = getTanarok();
	    $ADAT['napTipusok'] = getNapTipusok();
		
    	    $TOOL['datumSelect'] = array(
        	'tipus'=>'cella', 
		// 'post'=>array('tanarId', 'diakId', 'osztalyId', 'tankorId'),
        	'paramName' => 'dt', 'hanyNaponta' => 7,
        	'tolDt' => date('Y-m-d', strtotime('Saturday', strtotime($_TANEV['kezdesDt']))),
        	'igDt' => date('Y-m-d', strtotime('next Saturday', strtotime($_TANEV['zarasDt']))),
        	'override' => true
    	    );
	    $TOOL['datumTolIgSelect'] = array(
		    'tipus' => 'sor',
		    'tolParamName' => 'tolDt',
		    'igParamName' => 'igDt',
		    'tolDt' => $_TANEV['kezdesDt'],
		    'igDt' => $_TANEV['zarasDt'],
//		    'hanyNaponta' => 'havonta',
		    'post' => array('tanarId', 'osztalyId', 'tankorId', 'sulyozas')
	    );
	    getToolParameters();
		
	}

?>
