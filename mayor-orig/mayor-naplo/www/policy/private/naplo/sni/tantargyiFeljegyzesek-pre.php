<?php


    if (_RIGHTS_OK !== true) die();

    if (!__NAPLOADMIN && !__VEZETOSEG && !__TANAR) {

        $_SESSION['alert'][] = 'page:insufficient_access';

    } else {

	require_once('include/share/date/names.php');
	require_once('include/modules/naplo/share/osztaly.php');
	require_once('include/modules/naplo/share/diak.php');
	require_once('include/modules/naplo/share/tanar.php');
	require_once('include/modules/naplo/share/tankor.php');
	require_once('include/modules/naplo/share/szemeszter.php');

	// Paraméterek
        $ADAT['tolTime'] = $tolTime = strtotime('last Monday', strtotime('+1 day', strtotime($_TANEV['kezdesDt'])));
	$ADAT['tolDt'] = date('Y-m-d', $ADAT['tolTime']);
        $ADAT['igTime'] = min(time(), strtotime($_TANEV['zarasDt']));
	$ADAT['igDt'] = $ADAT['tolDt'];
        for ($t = $ADAT['tolTime']; $t <= $ADAT['igTime']; $t = strtotime("+7 days", $t)) $ADAT['igDt'] = date('Y-m-d', $t);

	$ADAT['dt'] = $dt = readVariable($_POST['dt'], 'date', $ADAT['igDt']);

	$osztalyId = $ADAT['osztalyId'] = readVariable($_POST['osztalyId'], 'id');
	if (isset($osztalyId)) {
	    $ADAT['diakIds'] = getDiakok(array('osztalyId' => $osztalyId, 'result' => 'idonly','override' => false));
	    if (is_array($ADAT['diakIds']) && count($ADAT['diakIds']) > 0) {
		$ADAT['diakAdat'] = getDiakAdatById($ADAT['diakIds'], array('result' => 'assoc', 'keyfield' => 'diakId'));
		$ADAT['sniDiakIds'] = $Diakok = array();
		foreach ($ADAT['diakAdat'] as $_diakId => $dAdat) {
		    if ($dAdat['fogyatekossag'] != '') {
			$ADAT['sniDiakIds'][] = $_diakId;
			$dAdat['aktualisStatusz'] = $dAdat['statusz'];
			$Diakok[] = $dAdat;
		    }
		}
	    }
	    $diakId = $ADAT['diakId'] = readVariable($_POST['diakId'], 'id', null, $ADAT['sniDiakIds']);

	    if (isset($diakId) && isset($dt)) {

		// Az osztály tanárai
		$ADAT['tankorIds'] = getTankorByDiakId($diakId, __TANEV, array('result' => 'idonly'));
		$ADAT['tankorTanar'] = getTankorTanaraiByInterval($ADAT['tankorIds'], array('tanev' => __TANEV, 'tolDt' => '', 'igDt' => '', 'result' => 'assoc', 'datumKenyszeritessel' => false));
		$ADAT['tankorAdat'] = getTankorAdatByIds($ADAT['tankorIds'], array('tanev' => __TANEV, 'dt' => $dt));
		foreach ($ADAT['tankorTanar'] as $_tankorId => $Tanarok) {
		    $ADAT['tankorTanaraE'][$_tankorId] = false;
		    for ($i = 0; $i < count($Tanarok); $i++)
			if ($Tanarok[$i]['tanarId'] == __USERTANARID) { $ADAT['tankorTanaraE'][$_tankorId] = true; break; }
		}

		$ADAT['tankorId'] = $tankorId = readVariable($_POST['tankorId'], 'id', null, $ADAT['tankorIds']);
		if (isset($tankorId)) {
		    for ($i = 0; $i < count($ADAT['tankorTanar'][$tankorId]); $i++) {
			if ($ADAT['tankorTanar'][$tankorId][$i]['tanarId'] == __USERTANARID) { define('__TANARA', true); break; }
		    }
		    if (!defined('__TANARA')) define('__TANARA', false);

		}

		// Action
		if ($_TANEV['statusz'] == 'aktív') {
		    if ($action == 'tantargyiFeljegyzesRogzites') {
			$Param = array(
			    'diakId' => $diakId, 
			    'tankorId' => readVariable($_POST['feljegyzesTankorId'], 'id', null, $ADAT['tankorIds']), 
			    'dt' => readVariable($_POST['feljegyzesDt'], 'date'), 
			    'megjegyzes' => readVariable($_POST['megjegyzes'], 'string')
			);
			if (
			    __NAPLOADMIN
			    || ($ADAT['tankorTanaraE'][$Param['tankorId']] && strtotime($Param['dt']) >= strtotime(_HALADASI_HATARIDO))
			) tantargyiFeljegyzesRogzites($Param);
		    }
		}
		if (isset($tankorId)) $ADAT['sniTantargyiFeljegyzes'] = getDiakFeljegyzesByTankorId($ADAT);
		else $ADAT['sniTantargyiFeljegyzes'] = getDiakFeljegyzesByDt($ADAT);
	    }

	}

	// Tool
	$TOOL['osztalySelect'] = array('tipus'=>'cella','paramName' => 'osztalyId', 'post' => array('dt'));
        if (isset($osztalyId)) 
	    $TOOL['diakSelect'] = array(
		'diakok' => $Diakok,
                'tipus'=>'cella','paramName' => 'diakId',
                'osztalyId'=> $osztalyId,'post' => array('osztalyId'),
                'statusz' => array('jogviszonyban van','magántanuló','jogviszonya felfüggesztve','jogviszonya lezárva'),
		'post' => array('osztalyId','dt')
    	    );
	$TOOL['oldalFlipper'] = array('tipus' => 'cella',
                'url' => array('index.php?page=naplo&sub=sni&f=diakAllapot','index.php?page=naplo&sub=sni&f=fejlesztesiTerv','index.php?page=naplo&sub=sni&f=tantargyiFeljegyzesek'),
                'titleConst' => array('_DIAK_ALLAPOT','_HAVI_OSSZEGZES','_TANTARGYI_FELJEGYZESEK'),
                'post' => array('osztalyId','diakId','tankorId'),
                'paramName'=>'diakId');
	if (isset($diakId))
	    $TOOL['tankorSelect'] = array('tipus' => 'sor', 'tankorok' => $TankorokMutat, 'paramName' => 'tankorId', 'post' => array('osztalyId','diakId','dt'));
	if (!isset($tankorId)) 
	    $TOOL['datumSelect'] = array(
		'lapozo' => true,
		'tipus'=>'sor', 'post'=>array('diakId', 'osztalyId', 'tankorId'), 'paramName' => 'dt', 'hanyNaponta' => 7,
        	'tolDt' => $ADAT['tolDt'],
        	'igDt' => $_TANEV['zarasDt'], 'override' => true
	    );
	getToolParameters();

    }
?>
