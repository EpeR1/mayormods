<?php

    if (_RIGHTS_OK !== true) die();

    if (!__TANAR && !__VEZETOSEG && !__NAPLOADMIN && !__TITKARSAG) {

	$_SESSION['alert'][] = 'page:insufficient_access';

    } else {

	require_once('include/modules/naplo/share/diak.php');
	require_once('include/modules/naplo/share/osztaly.php');
	require_once('include/modules/naplo/share/zaradek.php');
	require_once('include/share/date/names.php');

	$osztalyId = $ADAT['osztalyId'] = readVariable($_POST['osztalyId'], 'numeric unsigned');
	$diakId = $ADAT['diakId'] = readVariable($_POST['diakId'], 'numeric unsigned');
	$ADAT['iktatoszam'] = readVariable($_POST['iktatoszam'], 'string');
	$dt = $ADAT['dt'] = readVariable($_POST['dt'], 'datetime');
	$zaradekIndex = $ADAT['zaradekIndex'] = readVariable($_POST['zaradekIndex'], 'numeric unsigned');

	if (isset($zaradekIndex)) {
	    $tmp = explode('%', $Zaradek[$zaradekIndex]['szoveg']);
	    $ADAT['zaradek'] = $Zaradek[$zaradekIndex];
	    $ADAT['params'] = array();
	    for ($i=1; $i < count($tmp); $i = $i+2) $ADAT['params'][] = $tmp[$i];

	    if ($action == 'zaradekRogzites') {
		$ok = true;
		$values = readVariable($_POST['values'], 'string', '');
		$ADAT['csere'] = array();
		for ($i = 0; $i < count($ADAT['params']); $i++) {
		    $ADAT['csere'][ '%'.$ADAT['params'][$i].'%' ] = $values[$i];
		    if ($values[$i] == '') $ok = false;
		}
		if ($ok) {
		    if (zaradekRogzites($ADAT)) {
			$_SESSION['alert'][] = 'info:success';
			unset($zaradekIndex); unset($ADAT['zaradekIndex']);
		    }
		} else {
		    $_SESSION['alert'][] = 'message:empty_field';
		}
	    }
	}
	if ($action=='zaradekTorles') {
	    foreach ($_POST as $key => $val) {
		list($akt, $zaradekId) = explode('-',$key);
		if ($akt == 'del') zaradekTorles($zaradekId);
	    }

	}
	if (isset($diakId)) {
	    $diakZaradekok = getDiakZaradekok($diakId);
	    if ($diakZaradekok) $ADAT['diakZaradekok'] = $diakZaradekok;
	}

	$TOOL['osztalySelect'] = array('tipus' => 'cella','paramName' => 'osztalyId', 'post' => array('dt'));
	$TOOL['diakSelect'] = array('tipus' => 'cella', 'paramName' => 'diakId', 'post' => array('osztalyId','dt'),
	    'statusz' => array('jogviszonyban van','magántanuló','egyéni munkarend','vendégtanuló','jogviszonya felfüggesztve','jogviszonya lezárva')
	);
	$TOOL['datumSelect'] = array(
            'tipus'=>'sor', 'post'=>array('tanarId', 'diakId', 'osztalyId', 'tankorId'),
            'paramName' => 'dt', 'hanyNaponta' => 1,
//            'tolDt' => date('Y-m-d', strtotime('-1 month', strtotime($_TANEV['kezdesDt']))),
            'tolDt' => $_TANEV['elozoZarasDt'],
            'igDt' => $_TANEV['kovetkezoKezdesDt'],
            'override' => true
        );
	if (isset($diakId) && isset($dt))
	    $TOOL['zaradekSelect'] = array('tipus' => 'sor', 'paramName' => 'zaradekIndex', 'post' => array('osztalyId', 'diakId', 'dt'));
	

	getToolParameters();

    }
