<?php

    if (_RIGHTS_OK !== true) die();

    if (!__NAPLOADMIN) {
        $_SESSION['alert'][] = 'page:insufficient_access';
    } else {

	require_once('include/modules/naplo/share/terem.php');
	require_once('include/modules/naplo/share/file.php');
	require_once('include/modules/naplo/share/intezmenyek.php');

	$ADAT['telephelyAdat'] = getTelephelyek(array('result' => 'assoc', 'keyfield' => 'telephelyId'));
	$ADAT['telephelyIds'] = array_keys($ADAT['telephelyAdat']);
	$ADAT['telephelyId'] = $telephelyId = readVariable($_GET['telephelyId'], 'id', readVariable(
		$_POST['telephelyId'], 'id', (isset($_POST['telephelyId'])?null:readVariable(__TELEPHELYID,'id')), $ADAT['telephelyIds']
	    ), $ADAT['telephelyId']);
	$ADAT['teremAdat'] = getTermek(array('result' => 'assoc', 'keyfield' => 'teremId'));
	$ADAT['teremIds'] = array_keys($ADAT['teremAdat']);
	$ADAT['teremTipusok'] = getSetField('naplo_intezmeny','terem','tipus');
	$ADAT['teremId'] = readVariable($_POST['teremId'], 'id', readVariable($_GET['teremId'], 'id', null, $ADAT['teremIds']), $ADAT['teremIds']);
	$ADAT['teremIdMod'] = readVariable($_POST['teremIdMod'], 'id', null);

	if ($action == 'teremAdatModositas' || $action=='ujTerem') {

	    $D['teremId'] = $ADAT['teremId'];
	    $D['leiras'] = readVariable($_POST['leiras'], 'string');
	    $D['ferohely'] = readVariable($_POST['ferohely'], 'numeric unsigned');
	    $D['tipus'] = readVariable($_POST['tipus'], 'enum', $ADAT['teremTipusok']);
	    $D['telephelyId'] = readVariable($_POST['telephelyId'], 'id', readVariable($_GET['telephelyId'], 'id', null, $ADAT['telephelyIds']), $ADAT['telephelyIds']);
	    $D['teremId'] = $ADAT['teremId'];
	    $D['teremIdMod'] = $ADAT['teremIdMod'];

	    teremAdatModositas($D,($action=='ujTerem'));

	    unset($ADAT['teremId']);
	}

	$ADAT['teremAdat'] = getTermek(array('result' => 'assoc', 'keyfield' => 'teremId', 'telephelyId' => $telephelyId));

	$TOOL['telephelySelect'] = array('tipus'=>'cella','paramName' => 'telephelyId', 'post' => array('tanev'));
	if ($ADAT['teremId']!='' || $ADAT['teremId']!='') {
    	    $TOOL['vissza']['icon'] = 'arrow-left';
	}
	getToolParameters();
    }
?>
