<?php

    if (_RIGHTS_OK !== true) die();

    if (!__NAPLOADMIN && !__VEZETOSEG && !__TANAR) {
	$_SESSION['alert'][] = 'page:insufficient_access';
    } else {

	require_once('include/modules/naplo/share/tanar.php');
	require_once('include/modules/naplo/share/tankor.php');
	require_once('include/modules/naplo/share/munkakozosseg.php');
	require_once('include/modules/naplo/share/tankor.php');
	require_once('include/modules/naplo/share/tankorBlokk.php');
	require_once('include/modules/naplo/share/targy.php');
	require_once('include/modules/naplo/intezmeny/tankorTanar.php');
	require_once('include/share/date/names.php');

	$kuuk = $_COOKIE['mayorNaploUzen'];

	$tanev = $ADAT['tanev'] = readVariable($_POST['tanev'], 'numeric unsigned', readVariable($_GET['tanev'], 'numeric unsigned', __TANEV));
	if ($tanev == __TANEV) $TA = $_TANEV;
	else $TA = getTanevAdat($tanev);

//?? Ez mi is ??
	if ($kuuk==$_GET['kuuk']) {
	    $_POST['tankorId'] = $ADAT['tankorId'] = $tankorId = readVariable($_GET['tankorId'],'numeric');    
	} else {
	    $_POST['tankorId'] = $ADAT['tankorId'] = $tankorId = readVariable($_POST['tankorId'], 'id', readVariable($_GET['tankorId'], 'id'));
	}
	
	if (isset($ADAT['tankorId'])) {
	    $_POST['mkId'] = $ADAT['mkId'] = $mkId = getTankorMkId($ADAT['tankorId']);
	    $tmp = current(getTankorAdat($tankorId));
	    $ADAT['tankorAdat'] = $tmp[0];
	} else {
	    $_POST['mkId'] = $ADAT['mkId'] = $mkId = readVariable($_POST['mkId'], 'id', readVariable($_GET['mkId'], 'id'));
	}

	/* Dátumok */
        $_POST['refDt'] = $ADAT['refDt'] = $refDt = readVariable($_POST['refDt'], 'date', readVariable($_GET['refDt'], 'date', $TA['kezdesDt']));

//        if (isset($_POST['igDt']) && $_POST['igDt'] != '') $ADAT['igDt'] = $igDt = $_POST['igDt'];
//	else $ADAT['igDt'] = $igDt = $TA['zarasDt'];
	$ADAT['igDt'] = $igDt = readVariable($_POST['igDt'], 'date', $TA['zarasDt']);
	if (strtotime($igDt) < strtotime($refDt)) $ADAT['igDt'] = $igDt = $refDt;

        $tankorIds = $_POST['tankorok'];
	$tankorTanarIds = array();
	foreach ($_POST as $name => $value) if (substr($name, 0, 2) == 'TA') {
	    $_tankorId = substr($name, 2);
	    $tankorTanarIds[$_tankorId] = $value;
	}

	if (__NAPLOADMIN===true || __VEZETOSEG===true) {
	    switch ($action) {
		case 'tankorTanarAssoc':
		    if (isset($tanev))
			tankorTanarFelvesz($tankorIds, $tankorTanarIds, $TA, $refDt, $igDt);
		    break;
		case 'tankorTanarTorol':
		    if ($kuuk == $_GET['kuuk']) { 
			list($_tankorId,$_tanarId,$_beDt,$_kiDt) = explode('|',readVariable($_GET['tt'],'string'));
			tankorTanarTorol($_tankorId,$_tanarId,$_beDt,$_kiDt);
		    }
		    break;
		case 'tankorTanarJavit':
		    if ($kuuk == $_GET['kuuk']) { 
			list($_tankorId,$_tanarId,$_beDt,$_kiDt) = explode('|',readVariable($_GET['tt'],'string'));
			tankorTanarJavit($_tankorId,$_tanarId,$_beDt,$refDt);
		    }
		    break;
	    }
	}

	if (isset($ADAT['mkId'])) {
	    if (!isset($tankorId)) {
    		$ADAT['tanarok'] = getTanarok(array('mkId' => $ADAT['mkId'], 'tanev' => $tanev));
		$ADAT['tankorok'] = getTankorByMkId($ADAT['mkId'], $tanev, array('datumKenyszeritessel' => true, 'tolDt' => $refDt, 'igDt' => $refDt));
	    } else {
    		$ADAT['tanarok'] = getTanarok(array('targyId' => $ADAT['tankorAdat']['targyId'], 'tanev' => $tanev));
		$ADAT['tankorok'] = getTankorByMkId(
		    $ADAT['mkId'], $tanev, array('filter' => array("tankor.tankorId=$tankorId"), 'datumKenyszeritessel' => true, 'tolDt' => $refDt, 'igDt' => $refDt)
		);
	    }
	}
	if (is_array($ADAT['tankorok'])) {
	    for($i=0; $i<count($ADAT['tankorok']); $i++) {
		$_tankorId=$ADAT['tankorok'][$i]['tankorId'];
		$ADAT['tankorTanarok'][$_tankorId] = getTankorTanarai($_tankorId);
		$ADAT['tankorTanarBejegyzesek'][$_tankorId] = getTankorTanarBejegyzesek($_tankorId);
	    }
	}

	$ADAT['kuuk'] = rand();
	setcookie('mayorNaploUzen',$ADAT['kuuk']);

	$tolDt = (strtotime($TA['kezdesDt'])<time()) ? date('Y-m-d', strtotime('-1 month', strtotime($TA['kezdesDt']))) : date('Y-m-d');

	$TOOL['tanevSelect'] = array(
	    'tipus' => 'cella', 'paramName' => 'tanev', 
	    'tervezett' => true, 'post' => array('mkId','targyId','tankorId'), 'get'=>array()
	);
	$TOOL['munkakozossegSelect'] = array('tipus' => 'cella', 'paramName' => 'mkId', 'post' => array('tanev','refDt'), 'get'=>array());
	$TOOL['tankorSelect'] = array('tipus' => 'cella', 'paramName' => 'tankorId', 'post' => array('tanev','mkId','targyId','refDt'), 'get'=>array());
        $TOOL['datumSelect'] = array(
	    'override' => true,
            'tipus' => 'sor', 'paramName' => 'refDt', 'hanyNaponta' => 1,
            'tolDt' => $tolDt, 'igDt' => $TA['zarasDt'], 'post' => array('tanev','mkId','targyId','tankorId'),
        );

	getToolParameters();

    }
?>
