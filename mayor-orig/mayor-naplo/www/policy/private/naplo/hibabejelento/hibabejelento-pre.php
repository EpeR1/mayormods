<?php

    if (_RIGHTS_OK !== true) die();

    require_once('include/modules/naplo/share/kerelem.php');  
    $telephelyId = readVariable($_POST['telephelyId'],'id',__TELEPHELYID);
    $kerelemId = readVariable($_GET['kerelemId'],'id');

    if ($action == 'hibabejelentes') {

	$ADAT['telephelyId'] = readVariable($_POST['kerelemTelephelyId'], 'id', $telephelyId);
	$ADAT['txt'] = readVariable($_POST['txt'], 'string');
	$ADAT['kategoria'] = readVariable($_POST['kategoria'],'string','', $KERELEM_TAG);
	if ( $ADAT['txt'] != '' && ($kerelemId = hibabejelentes($ADAT)) ) $_SESSION['alert'][] ='info:success:'.$kerelemId;

    }

    if ($kerelemId>0) $Kerelmek = getKerelmek('',$kerelemId);

//    $TOOL['kerelemStat'] = array('tipus'=>'cella', 'paramName'=>'telephelyId', 'post'=>array());
    $TOOL['telephelySelect'] = array('tipus'=>'cella', 'paramName'=>'telephelyId', 'post'=>array());
    getToolParameters();

?>
