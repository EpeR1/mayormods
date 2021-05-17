<?php

    if (_RIGHTS_OK !== true) die();

    require_once('include/modules/naplo/share/intezmenyek.php');        
    require_once('include/modules/naplo/share/kerelem.php');        

    $_telephelyIdDefault = (isset($_POST['telephelyId'])?null:(defined('__TELEPHELYID') ? __TELEPHELYID:null));
    $telephelyId = readVariable($_POST['telephelyId'],'id', $_telephelyIdDefault);
    $kerelemId = readVariable($_POST['kerelemId'],'id',readVariable($_GET['kerelemId'],'id'));      
    $tolDt = readVariable($_POST['tolDt'],'date',date('Y-m-d'));

    if ($action == 'hibabejelentes') {
        $ADAT['txt'] = readVariable($_POST['txt'], 'string');
        $ADAT['kategoria'] = readVariable($_POST['kategoria'],'string','', $KERELEM_TAG);
	$ADAT['telephelyId'] = $telephelyId;
        if ( $ADAT['txt'] != '' && ($kerelemId = hibabejelentes($ADAT)) ) $_SESSION['alert'][] ='info:success:'.$kerelemId;
    }

    if ((is_array($hibaAdmin) && in_array(_USERACCOUNT,$hibaAdmin)) || (!is_array($hibaAdmin) && (__VEZETOSEG || __NAPLOADMIN))) {
	define('__HIBAADMIN',true);
    } else {
	define('__HIBAADMIN',false);
    }
    if (__HIBAADMIN === TRUE) {

	if ($action == 'hibaAdminRogzites') {
    	    $_ADAT['kerelemId'] = $kerelemId;
    	    $_ADAT['kerelemTelephelyId'] = $_ADAT['telephelyId'] = $telephelyId;
    	    $_ADAT['valasz'] = readVariable($_POST['valasz'],'string');
    	    $_ADAT['jovahagy'] = readVariable($_POST['jovahagy'],'string');
    	    $_ADAT['nemHagyJova'] = readVariable($_POST['nemHagyJova'],'string');
    	    $_ADAT['lezar'] = readVariable($_POST['lezar'],'string');
    	    $_ADAT['kategoria'] = readVariable($_POST['kategoria'],'string','', $KERELEM_TAG);
	    hibaAdminRogzites($_ADAT);

	    //if ($_ADAT['lezar']) 
	    unset($kerelemId);
	    unset($_ADAT);
	}

	// Összes lezáratlan kérelem lekérdezése
	if ($kerelemId>0) {
	    $Kerelmek = getKerelmek('',$kerelemId,$tolDt);
	} else {
	    $Kerelmek = getKerelmek($telephelyId,null,$tolDt);
	}
    } else {
	if ($action == 'hibaAdminRogzites') {
    	    $_ADAT['kerelemId'] = $kerelemId;
    	    $_ADAT['kerelemTelephelyId'] = $_ADAT['telephelyId'] = $telephelyId;
    	    $_ADAT['valasz'] = readVariable($_POST['valasz'],'string');
    	    $_ADAT['kategoria'] = readVariable($_POST['kategoria'],'string','', $KERELEM_TAG);
	    hibaAdminRogzites($_ADAT);

	    //if ($_ADAT['lezar']) 
	    unset($kerelemId);
	    unset($_ADAT);
	}
	// Saját kérelmek lekérdezése
	$Kerelmek = getSajatKerelmek($telephelyId);
    }

    $TELEPHELY = getTelephelyek();
  
//    $TOOL['kerelemStat'] = array('tipus'=>'cella', 'paramName'=>'telephelyId', 'post'=>array());
        $TOOL['datumSelect'] = array(
            'tipus'=>'cella', 'post'=>array('telephelyId'),
            'paramName' => 'tolDt', 'hanyNaponta' => 1,
            'tolDt' => date('Y-m-d', strtotime('last Monday', strtotime($_TANEV['kezdesDt']))),
            'igDt' => date('Y-m-d'),
        );
    $TOOL['telephelySelect'] = array('tipus'=>'cella', 'paramName'=>'telephelyId', 'post'=>array());                                                                             
    $TOOL['vissza'] = array('tipus'=>'vissza','paramName'=>'','icon'=>'inbox','post'=>array('telephelyId'));
    if ($kerelemId!='') {
        $TOOL['vissza']['icon'] = 'arrow-left';
    }
    getToolParameters();

?>
