<?php

    if (_RIGHTS_OK !== true) die();
    if (!__NAPLOADMIN && !__VEZETOSEG && !__TANAR && __TITKARSAG !== true) {
	$_SESSION['alert'][] = 'page:insufficient_access';
    } else {

	require_once('include/modules/naplo/share/osztaly.php');
	require_once('include/modules/naplo/share/targy.php');
	require_once('include/modules/naplo/share/diak.php');
	require_once('include/modules/naplo/share/tankor.php');

	$ADAT['osztalyId'] = array();
	if (is_array($_POST['osztalyIds'])) for ($i = 0; $i < count($_POST['osztalyIds']); $i++) {
	    $ADAT['osztalyId'][] = readVariable($_POST['osztalyIds'][$i], 'numeric unsigned');
	}
	$_POST['osztalyIds'] = $ADAT['osztalyId'];
	$ADAT['targyId'] = $targyId = readVariable($_POST['targyId'], 'numeric unsigned');
	$ADAT['tankorIds'] = array();	

	$ADAT['osztaly'] = getOsztalyok(__TANEV, array('result' => 'assoc'));
	if (count($ADAT['osztalyId']) > 0 && isset($targyId)) {

	    // diákok lekérdezése
	    $ADAT['diak'] = getDiakokByOsztalyId($ADAT['osztalyId']);
	    // diákok tankörei
	    for ($i = 0; $i < count($ADAT['diak']); $i++) {

		$diakId = $ADAT['diak'][$i]['diakId'];
		$ADAT['diak'][$i]['tankorIds'] = array();
		$ret = getTankorByDiakId($diakId, $tanev = __TANEV, $SET = array('csakId' => false, 'tolDt' => '', 'igDt' => '', 'result'=>'', 'jelenlet'=>''));
		if ($ret !== false) {
		    for ($j = 0; $j < count($ret); $j++) {
			if ($ret[$j]['targyId'] == $targyId) {
			    $ADAT['diak'][$i]['tankorIds'][] = $ret[$j]['tankorId'];
			    if (!in_array($ret[$j]['tankorId'], $ADAT['tankorIds'])) $ADAT['tankorIds'][] = $ret[$j]['tankorId'];
			}
		    }
		}
	    }
	     
	    // Tankörök tanárai
	    $ADAT['tankorTanar'] = getTankorTanaraiByInterval(
		$ADAT['tankorIds'], array('tanev' => __TANEV, 'tolDt' => '', 'igDt' => '', 'result' => 'assoc')
	    );
	}


	// ToolBar
	$TOOL['targySelect']= array('tipus'=>'cella','paramName'=>'targyId', 'post'=>array('osztalyIds'));
	getToolParameters();

    }
?>
