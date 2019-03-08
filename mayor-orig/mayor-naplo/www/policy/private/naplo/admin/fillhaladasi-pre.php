<?php

    if (_RIGHTS_OK !== true) die();                                                                                                                                              
    if (__NAPLOADMIN !== true) {                                                                                                                                                 
        $_SESSION['alert'][] = 'page:insufficient_access';                                                                                                                                   
    } else {                                                                                                                                                                     

	require_once('include/share/date/names.php');

	$tolDt = readVariable($_POST['tolDt'],'date',$_TANEV['kezdesDt']);	
	$igDt = readVariable($_POST['igDt'],'date',date('Y-m-d'));	
	if ($action != '') {
    	    $q = "SELECT DISTINCT dt FROM nap WHERE dt BETWEEN '%s' AND '%s' AND tipus='tanítási nap'";
	    $NAPOK = db_query($q, array('fv'=>'fillhaladasi-pre','modul'=>'naplo','values'=>array($tolDt,$igDt),'result'=>'indexed'));
	    for ($i=0; $i<count($NAPOK);$i++) {
		$res = (checkNaplo($NAPOK[$i]['dt']));
		if ($res===false) $_SESSION['alert'][] = '::hiba:'.$NAPOK[$i]['dt'];
		else $SUCCESS[] = " + ".$NAPOK[$i]['dt'];
	    }
	    if (count($SUCCESS)>=1) $_SESSION['alert'][] = 'info:success:***'.implode('***',$SUCCESS);
	}

        $TOOL['datumTolIgSelect'] = array('tipus' => 'sor', 'tolParamName' => 'tolDt', 'igParamName' => 'igDt',
                    'tolDt' => $_TANEV['kezdesDt'], 'igDt' => $igDt,
                    'hanyNaponta' => '1'
	);

    }

?>
