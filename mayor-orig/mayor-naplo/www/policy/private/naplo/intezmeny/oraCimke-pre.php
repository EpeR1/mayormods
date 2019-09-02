<?php

    if (_RIGHTS_OK !== true) die();

    if (!__NAPLOADMIN) {
        $_SESSION['alert'][] = 'page:insufficient_access';
    } else {

	require_once('include/modules/naplo/share/ora.php');

	if ($action == 'oraCimkez') {

	    $D['cimkeId'] = readVariable($_POST['oraCimkeId'], 'id');
	    $D['cimkeLeiras'] = readVariable($_POST['oraCimkeLeiras'], 'string');

	    for($i=0; $i<count($D['cimkeId']); $i++) {
		if ($D['cimkeLeiras'][$i]=='') {
    		    $q = "DELETE FROM cimke WHERE cimkeId = %u";
		    $v = array($D['cimkeId'][$i]);
		    db_query($q, array('fv'=>'oraCimke','modul'=>'naplo_intezmeny','values'=>$v));
		}
	    }
	    for($i=0; $i<count($D['cimkeLeiras']); $i++) {
		if ($D['cimkeId'][$i]>0) {
		    if ($D['cimkeLeiras'][$i]!='') {
    			$q = "UPDATE cimke SET cimkeLeiras = '%s' WHERE cimkeId = %u";
			$v = array($D['cimkeLeiras'][$i],$D['cimkeId'][$i]);
		    }
		} elseif ($D['cimkeLeiras'][$i]!='') {
		    $q = "INSERT INTO  cimke (cimkeLeiras) VALUES ('%s')";
		    $v = array($D['cimkeLeiras'][$i]);
		}
		db_query($q, array('fv'=>'oraCimke','modul'=>'naplo_intezmeny','values'=>$v));
	    }

	    //teremAdatModositas($D,($action=='ujTerem'));
	    //unset($ADAT['teremId']);
	}

	$ADAT['cimkek'] = getoracimkek();

    }
?>
