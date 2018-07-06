<?php

    if (_RIGHTS_OK !== true) die();
    if (!__NAPLOADMIN && !__VEZETOSEG && !__TANAR && !__TITKARSAG) {
	$_SESSION['alert'][] = 'page:insufficient_access';
    } else {

	require_once('include/modules/naplo/share/osztaly.php');
	require_once('include/modules/naplo/share/intezmenyek.php');
	require_once('include/modules/naplo/share/targy.php');
	require_once('include/modules/naplo/share/kepzes.php');
	require_once('include/modules/naplo/share/diak.php');
	require_once('include/modules/naplo/share/szemeszter.php');
	require_once('include/modules/naplo/share/hianyzas.php');
	require_once('include/modules/naplo/share/szovegesErtekeles.php');
	require_once('include/share/date/names.php');
	require_once('include/share/print/pdf.php');
	require_once('include/modules/naplo/share/file.php');

	$ADAT['tanev'] = $tanev = __TANEV;
	if ($tanev == __TANEV) $ADAT['tanevAdat'] = $TA = $_TANEV;
	else $ADAT['tanevAdat'] = $TA = getTanevAdat($tanev);

	// A dátum, osztály és diákok kiválasztása
        $ADAT['szemeszterId'] = $szemeszterId = readVariable($_POST['szemeszterId'], 'numeric unsigned', null);
	if (isset($szemeszterId)) { // szemesztert záró értékelés - intézményi adatbázis
            $ADAT['szemeszter'] = getSzemeszterAdatById($ADAT['szemeszterId']);
            $ADAT['dt'] = $dt = $ADAT['szemeszter']['zarasDt'];
	} else {
	    $condition = strtotime($TA['kezdesDt']).'<=strtotime($return) && strtotime($return)<='.strtotime($TA['zarasDt']);
	    $ADAT['dt'] = $dt = readVariable($_POST['dt'], 'datetime', null, array(), $condition);
	    $ADAT['tolDt'] = $tolDt = readVariable($_POST['tolDt'], 'datetime', $_TANEV['kezdesDt'], array(), $condition);
	}
	$ADAT['osztalyId'] = $osztalyId = readVariable($_POST['osztalyId'], 'numeric unsigned', null);
	$ADAT['targySorrendNev'] = $sorrendNev = readVariable($_POST['sorrendNev'], 'enum', 'bizonyítvány', array('napló','bizonyítvány','anyakönyv','ellenőrző','egyedi'));
	$diakIds = array();
	if (isset($osztalyId)) {
	    $ADAT['osztalyAdat'] = getOsztalyAdat($osztalyId, $tanev);
	    $ADAT['file'] = fileNameNormal('szovegesErtekeles-'.str_replace('.', '', $ADAT['osztalyAdat']['osztalyJel']));
	    $Diakok = getDiakok(array('osztalyId' => $osztalyId, 'tanev' => $tanev, 'tolDt' => $dt, 'igDt' => $dt, 
		'statusz'=>array('jogviszonyban van'), 'extraAttrs'=>'oId as oktatasiAzonosito,torzslapSzam'));
	    if (is_array($Diakok)) for ($i = 0; $i < count($Diakok); $i++) {
		$diakIds[] = $Diakok[$i]['diakId'];
		$ADAT['diakAdat'][$Diakok[$i]['diakId']] = $Diakok[$i];
	    }
	}
	$diakId = readVariable($_POST['diakId'], 'numeric unsigned', null, $diakIds);
	if (isset($diakId)) $diakIds = array($diakId);
	$ADAT['diakIds'] = $diakIds;

	if (count($ADAT['diakIds']) > 0 && (isset($ADAT['dt']) || isset($ADAT['szemeszterId']))) {
	    $ADAT['hianyzas'] = getDiakHianyzasOsszesites($ADAT['diakIds'], $ADAT['szemeszter']);
	    $printFile = nyomtatvanyKeszites($ADAT); // ???
	    $printFile = fileNameNormal($printFile);
	    if ($printFile !== false && file_exists(_DOWNLOADDIR."/$policy/$page/$sub/$f/$printFile"))
    		header('Location: '.location('index.php?page=session&f=download&download=true&dir=naplo/nyomtatas/szovegesErtekeles&file='.$printFile));
	}

//        $TOOL['datumSelect'] = array(
//            'tipus' => 'cella', 'post' => array('diakId','osztalyId'), 
//	    'paramName' => 'dt', 'hanyNaponta' => 1, 'tolDt' => date('Y-m-d', strtotime($TA['kezdesDt'])), 'igDt' => $TA['zarasDt']
//        );
        $TOOL['datumTolIgSelect'] = array('tipus' => 'sor', 'tolParamName' => 'tolDt', 'igParamName' => 'dt',
            'tolDt' => date('Y-m-d', strtotime($TA['kezdesDt'])), 'igDt' => $TA['zarasDt'],
            'hanyNaponta' => 1, 'post' => array('osztalyId','diakId','sorrendNev')
	);
        $TOOL['osztalySelect'] = array('tipus' => 'cella', 'paramName' => 'osztalyId', 'post' => array('tolDt', 'tolDt', 'dt','sorrendNev'));
        if (isset($osztalyId))
    	    $TOOL['diakSelect'] = array('tipus' => 'cella', 'paramName' => 'diakId', 'diakok' => $Diakok, 'post' => array('osztalyId', 'tolDt', 'dt','sorrendNev'));
        $TOOL['szemeszterSelect'] = array(
            'tipus'=>'cella','paramName' => 'szemeszterId', 'statusz' => array('aktív','lezárt') ,
            'post' => array('osztalyId', 'tanarId', 'diakId', 'tolDt', 'dt', 'tankorId', 'kepzesId', 'evfolyam','sorrendNev')
        );
	$TOOL['targySorrendSelect'] = array('tipus'=>'cella','paramName' => 'sorrendNev', 'post' => array('szemeszterId', 'osztalyId','diakId','tolDt','dt','tankorId','kepzesId','evfolyam'));
	getToolParameters();
    }

?>
