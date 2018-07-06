<?php

    if (_RIGHTS_OK !== true) die();

    if (!__NAPLOADMIN && !__VEZETOSEG && !__TANAR && !__TITKARSAG) {

        $_SESSION['alert'][] = 'page:illegal_access';

    } elseif (!_TANKOROK_OK) {

        $_SESSION['alert'][] = 'page:hianyzo_tankorok:'._HIANYZO_TANKOROK_SZAMA;

    } else {

        require_once('include/modules/naplo/share/osztaly.php');
        require_once('include/modules/naplo/share/ora.php');
	require_once('include/share/date/names.php');
        require_once('include/modules/naplo/share/diak.php');
        require_once('include/modules/naplo/share/hianyzas.php');
        require_once('include/modules/naplo/share/tankor.php');

	$osztalyId = $ADAT['osztalyId'] = readVariable($_POST['osztalyId'], 'id');
        if (!isset($osztalyId) && __TANAR && __OSZTALYFONOK) $osztalyId = $_OSZTALYA[0];

	$dt = $ADAT['dt'] = readVariable($_POST['dt'], 'date', date('Y-m-d'));

	if (isset($dt) && isset($osztalyId)) {

	    $ADAT['diakok'] = getDiakok(array('osztalyId' => $osztalyId, 'result' => 'assoc', 'tanev' => __TANEV, 'tolDt' => $dt, 'igDt' => $dt));
	    $ADAT['diakIds'] = array_keys($ADAT['diakok']);
	    $H = getHianyzasByDiakIds($ADAT['diakIds'], array('result' => 'multiassoc', 'keyfield' => 'ora', 'tolDt' => $dt, 'igDt' => $dt));
	    foreach ($H as $ora => $hAdat) { 
		for ($i = 0; $i < count($hAdat); $i++) {
		    $_H = $hAdat[$i]; 
		    $ADAT['hianyzas'][$ora][$_H['tankorId']][] = $_H; 
		    $ADAT['diakHianyzott'][$_H['diakId']][$_H['hTipus']][$_H['statusz']][$_H['tankorTipus']]['db']++;
		}
	    }
	    if (is_array($ADAT['hianyzas'])) $ADAT['tankorIds'] = array_keys($ADAT['hianyzas']);
	    $ADAT['tankorok'] = getTankorByOsztalyId($osztalyId, __TANEV, array('tanarral' => true, 'result' => 'assoc'));
	}
        $TOOL['datumSelect'] = array(
            'tipus' => 'sor', 'post' => array('osztalyId'),
            'paramName' => 'dt', 'hanyNaponta' => 1,
            'tolDt' => date('Y-m-d', strtotime($_TANEV['kezdesDt'])),
            'napTipusok' => array('tanítási nap', 'speciális tanítási nap'),
            'lapozo' => true
        );
        $TOOL['osztalySelect'] = array('tipus' => 'cella','paramName' => 'osztalyId', 'post' => array('dt'));
        getToolParameters();
    }

?>
