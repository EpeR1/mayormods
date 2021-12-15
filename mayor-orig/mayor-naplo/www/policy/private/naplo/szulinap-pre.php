<?php

   if (_RIGHTS_OK !== true) die();

//    if ($skin=='ajax') {

	require_once('include/modules/naplo/share/diak.php');
	require_once('include/modules/naplo/share/tanar.php');
	require_once('include/modules/naplo/share/osztaly.php');

	$ADAT['ma'] = getDiakBySzulDt(date('Y-m-d'));

	$dt = readVariable($_POST['dt'],'date');
	if (is_null($dt)) {
	    $refDt = date('Y-m-d',strtotime('+1 days')); 
	    $dt = date('Y-m-d',strtotime('last sunday',strtotime($refDt))); 
	}
	for ($i=0; $i<=6; $i++) {
	    $_md = date('m-d',strtotime('+'.$i.' day',strtotime($dt)));
	    $ADAT['heti'][$i]['dt']= date('Y-m-d',strtotime('+'.$i.' day',strtotime($dt)));
	    $ADAT['heti'][$i]['diakok'] = getDiakBySzulDt($_md);
	    $ADAT['heti'][$i]['tanarok'] = getTanarBySzulDt($_md);
	}
	$ADAT['osztaly'] = getOsztalyok(__TANEV,array('result'=>'assoc'));
//    }

    dump($ADAT['heti']);


    if ($skin!='ajax') {
    global $_TANEV;
    $TOOL['datumSelect'] = array(
            'tipus'=>'sor', 'post'=>array('tanarId', 'diakId', 'osztalyId', 'tankorId'),
            'paramName' => 'dt', 'hanyNaponta' => 7,
            'tolDt' => date('Y-m-d', strtotime('last sunday', strtotime($_TANEV['kezdesDt']))),
            'igDt' => date('Y-m-d', strtotime('last sunday', strtotime($_TANEV['zarasDt']))),
            'override' => true
        );
    }
?>
