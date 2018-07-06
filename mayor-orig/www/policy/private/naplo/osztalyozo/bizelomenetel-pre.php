<?php

    if (_RIGHTS_OK !== true) die();
    
    define('_TIME',strtotime(date('Y-m-d')));

    if (
	!__NAPLOADMIN && !__VEZETOSEG && !__TANAR && !__TITKARSAG && !__DIAK
    ) {
	$_SESSION['alert'][] = 'page:insufficient_access';
    } else {
    
	require_once('include/share/date/names.php');
	require_once('include/modules/naplo/share/osztaly.php');
	require_once('include/modules/naplo/share/diak.php');
	require_once('include/modules/naplo/share/file.php');
	require_once('include/modules/naplo/share/targy.php');
	require_once('include/modules/naplo/share/intezmenyek.php');
	require_once('include/modules/naplo/share/szemeszter.php');
	require_once('include/modules/naplo/share/osztalyzatok.php');
	require_once('include/modules/naplo/share/vizsga.php');
	require_once('include/modules/naplo/share/zaradek.php');
	require_once('include/modules/naplo/share/zaroJegyModifier.php');
	require_once('include/modules/naplo/share/kepzes.php');

	$ADAT['sorrendNev'] = $sorrendNev = readVariable($_POST['sorrendNev'], 'emptystringnull', 'bizonyítvány', getTargySorrendNevek(__TANEV));
        $ADAT['zaroJegyTipusok'] = getEnumField('naplo_intezmeny', 'zaroJegy', 'jegyTipus');

        $ADAT['magatartasIdk'] = getMagatartas();
	$ADAT['szorgalomIdk']= getSzorgalom();
//	$tmp = getTargyakByDiakId($diakId); -- ezt sajnos itt nem tudjuk használni, mert erősen tanév függő
	$tmp = getTargyak(array('targySorrendNev'=>$ADAT['sorrendNev']));
	// reindex
	$ADAT['targyak'] = array();
	for ($i=0; $i<count($tmp); $i++) {
	    $ADAT['targyak'][$tmp[$i]['targyId']] = $tmp[$i];
	}	

	// Melyik osztály diákjait nézzük
	$ADAT['osztalyId'] = $osztalyId = readVariable($_POST['osztalyId'], 'id');
	if (__OSZTALYFONOK && !isset($osztalyId)) $_POST['osztalyId'] = $ADAT['osztalyId'] = $osztalyId = $_OSZTALYA[0];
    	if (__DIAK) { // diák / szülő csak a saját adatait nézheti
	    $diakId = __USERDIAKID;
	} elseif (isset($_POST['diakId']) && $_POST['diakId'] != '') {
	    $diakId = readVariable($_POST['diakId'],'numeric');
	}

	$ADAT['kepzesId'] = $kepzesId = readVariable($_POST['kepzesId'],'id');

	if (!is_null($diakId)) {
	    define('__VEGZOS', diakVegzosE($diakId));
	    // intézmlényi adatok lekérdezése
	    $ADAT['intezmeny'] = getIntezmenyByRovidnev(__INTEZMENY);
	    // diák adatai
	    $ADAT['diakAdat'] = getDiakAdatById($diakId);
	    // Na de ez így semmit nem jelent. Melyik osztályban, melyik tanévben?
	    // ez így nem elég!
	    $ADAT['diakKepzes'] = getKepzesByDiakId($diakId, array('result'=>'indexed')); // Ez a diák valaha volt összes képzése!!
	    if (isset($kepzesId)) {
		$ADAT['kepzesOraterv'][$kepzesId] = getKepzesOraterv($kepzesId,array('arraymap'=>array('targyId','evfolyamJel')));		
	    } else {
		for ($i=0; $i<count($ADAT['diakKepzes']); $i++) {
		    $_kepzesId = $ADAT['diakKepzes'][$i]['kepzesId'];
		    $ADAT['kepzesOraterv'][$_kepzesId] = getKepzesOraterv($_kepzesId,array('arraymap'=>array('targyId','evfolyamJel')));
		}
		if ($i==1) $ADAT['kepzesId'] = $kepzesId = $_kepzesId;
	    }

	    // Erre nincs szükség
	    $ADAT['diakOsztaly'] = getDiakMindenOsztaly($diakId);
    		for($j=0; $j<count($ADAT['diakOsztaly']); $j++) {
    		    $_osztalyId = $ADAT['diakOsztaly'][$j]['osztalyId'];
            	    $ADAT['diakEvfolyamJel'][$_osztalyId] = getEvfolyamJel($_osztalyId);
        	}

	    /* 
		Zárójegyek évfolyamonként 
		Minden zárójegy, függetlenül attól milyen képzésen szerezte
	    */
	    $ADAT['zaroJegy'] = getDiakZaroJegyek($diakId,null,null,array('arraymap'=>array('diakId','targyId','evfolyamJel','felev')));

	    $_VIZSGA = getVizsgak(array('diakId'=>$diakId));
	    /* REINDEX */
	    for ($i=0; $i<count($_VIZSGA); $i++) {
		$ADAT['zaroJegyVizsga'][$_VIZSGA[$i]['zaroJegyId']]=$_VIZSGA[$i];
	    }
	}

//	$TOOL['szemeszterSelect'] = array('tipus'=>'cella','paramName' => 'szemeszterId', 'statusz' => array('aktív','lezárt') , 'post' => array('osztalyId', 'diakId', 'sorrendNev'));
	if (!__DIAK) {
	    $TOOL['osztalySelect'] = array('tipus'=>'cella','paramName' => 'osztalyId', 'post' => array('szemeszterId', 'sorrendNev'));
	    $TOOL['diakSelect'] = array(
		'tipus'=>'cella','paramName' => 'diakId',
		'osztalyId'=> $osztalyId,'post' => array('osztalyId','szemeszterId', 'sorrendNev'),
		'statusz' => array('jogviszonyban van','magántanuló','jogviszonya felfüggesztve','jogviszonya lezárva')
	    );
	    if (isset($diakId)) {
		$TOOL['diakLapozo'] = array(
		'tipus'=>'sor','paramName' => 'diakId',
		'osztalyId'=> $osztalyId,'post' => array('osztalyId','szemeszterId', 'sorrendNev'),
		'statusz' => array('jogviszonyban van','magántanuló','jogviszonya felfüggesztve','jogviszonya lezárva')
		);
		$TOOL['kepzesSelect'] = array('tipus'=>'sor','paramName'=>'kepzesId', 'kepzesId'=>$kepzesId, 'post' => array('osztalyId','diakId'));
	    }
	    
	}
	$TOOL['targySorrendSelect'] = array('tipus'=>'cella','paramName' => 'sorrendNev', 'post' => array('szemeszterId', 'osztalyId', 'diakId','kepzesId'));
	getToolParameters();
    
    }
?>
