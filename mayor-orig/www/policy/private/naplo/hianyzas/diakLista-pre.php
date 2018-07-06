<?php
/*
    Module: naplo

    --TODO :: check (diak-prev2?
*/
    global $_TANEV;

    if (_RIGHTS_OK !== true) die();
    if (!__NAPLOADMIN && !__TANAR && !__DIAK && !__VEZETOSEG) {
	$_SESSION['alert'][] = 'message:insufficient_access';
    } else {

        require_once('include/modules/naplo/share/diak.php');
        require_once('include/modules/naplo/share/osztaly.php');
        require_once('include/modules/naplo/share/tanar.php');
        require_once('include/share/date/names.php');
	require_once('include/modules/naplo/share/kepzes.php');
        require_once('include/modules/naplo/share/tankor.php');
        require_once('include/modules/naplo/share/hianyzas.php');
        require_once('include/modules/naplo/share/hianyzasModifier.php');
        require_once('include/modules/naplo/share/szemeszter.php');
        require_once('include/modules/naplo/share/ora.php');
        require_once('include/modules/naplo/share/orarend.php');

	require_once('skin/classic/module-naplo/html/share/hianyzas.phtml');

	$napiMinOra = getMinOra();
	$napiMaxOra = getMaxOra();

	if (__DIAK) {
	    $diakId = defined('__USERDIAKID') ? __USERDIAKID : 0;
	} else {
	    $diakId = readVariable($_POST['diakId'], 'numeric unsigned', readVariable($_GET['diakId'], 'numeric unsigned'));
	    
	    $osztalyId = readVariable($_POST['osztalyId'], 'numeric unsigned', readVariable($_GET['osztalyId'], 'numeric unsigned'));

    	    if (!isset($osztalyId) && !isset($diakId) && __TANAR && __OSZTALYFONOK) $osztalyId = $_OSZTALYA[0];
	    if (isset($diakId) && !isset($osztalyId)) {
		$OI = getDiakOsztalya($diakId);
		$_POST['osztalyId'] = $osztalyId = $OI[0]['osztalyId'];
	    }
	    if ($osztalyId!='') {
		$ADAT['osztalyId'] = $osztalyId;
		$ADAT['osztaly'] = getOsztalyAdat($osztalyId);
	    }
	}


if (isset($diakId) && $diakId != '') {

	$_POST['diakId'] = $ADAT['diakId'] = $diakId;
	$_POST['osztalyId'] = $ADAT['osztalyId'] = $osztalyId;
	$ADAT['diak']['nev'] = getDiakNevById($diakId);

	/* --- igDt, tolDt --- */
	$tolDt = readVariable($_POST['tolDt'], 'datetime', date('Y-m-d', mktime(0,0,0,date('m'),1,date('Y'))));
	$igDt = $_TANEV['zarasDt'];
	initTolIgDt(__TANEV, $tolDt, $igDt);
	// $ho beállítása
        $ho = readVariable($_POST['ho'],'numeric','',array(1,2,3,4,5,6,7,8,9,10,11,12));
        if ($ho=='') $ho = readVariable($_GET['ho'],'numeric',
                            intval(date('m',strtotime($tolDt))),
                            array(1,2,3,4,5,6,7,8,9,10,11,12));
        $ADAT['ho'] = $ho;
	/* --- */

        $DIAKOSZTALYAI = getDiakokOsztalyai(array($diakId));
        define(__OFO, ( is_array($_OSZTALYA) && count(array_intersect($DIAKOSZTALYAI[$diakId],$_OSZTALYA)) > 0 ));

	/* --- tanuló hiányzásai (figyelem! dátumok!) --- */
	// --TODO: 9!!!

	$ADAT['napok'] = getHonapNapjai($ho);
	for ($i=0; $i<count($ADAT['napok']); $i++)
	    $_NAPOK[] = $ADAT['napok'][$i]['dt'];
	$ADAT['hianyzasok'] = getHianyzasByDt(array($diakId),$_NAPOK);

} // ha van nem üres diakId

	if (!__DIAK) {

    	    $TOOL['osztalySelect'] = array('tipus' => 'cella','paramName' => 'osztalyId', 'post' => array('ho'));
	    $TOOL['diakSelect'] = array('tipus'=>'cella', 'diakId' => $diakId, 'post'=>array('tolDt','osztalyId','ho'));
	    //$TOOL['tanarOraLapozo'] = array('tipus'=>'sor', 'oraId' => $oraId, 'post'=>array('tanarId'));

	}
    	if ($diakId != '') {
	    $TOOL['igazolasOsszegzo'] = array('tipus' => 'sor','paramName' => 'igazolasOsszegzo', 'post' => array());
	    $TOOL['oldalFlipper'] = array('tipus' => 'cella', 'url' => array('index.php?page=naplo&sub=hianyzas&f=diak','index.php?page=naplo&sub=haladasi&f=stat&diakId='.$diakId),
    		'titleConst' => array('_DIAKHIANYZASNAPTAR','_HALADASISTATISZTIKA'), 
		'post' => array('tanev','osztaly','tolDt','ho'),
    		'paramName'=>'diakId');
	}
	if ($ho != '') $TOOL['honapLapozo'] = array('tipus'=>'sor', 'paramName' => 'ho', 'ho'=>$ho, 'post'=>array('diakId','osztalyId'));	    
	getToolParameters();


    }
		    
?>
