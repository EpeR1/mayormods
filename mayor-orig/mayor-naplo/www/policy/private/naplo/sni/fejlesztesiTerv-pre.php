<?php

    if (_RIGHTS_OK !== true) die();

    if (!__NAPLOADMIN && !__VEZETOSEG && !__TANAR) {

        $_SESSION['alert'][] = 'page:insufficient_access';

    } else {

	require_once('include/modules/naplo/share/osztaly.php');
	require_once('include/modules/naplo/share/diak.php');
	require_once('include/share/date/names.php');
	require_once('include/modules/naplo/share/file.php');
	require_once('include/modules/naplo/share/tanar.php');
	require_once('include/modules/naplo/share/sni.php');
	require_once('include/modules/naplo/share/tankor.php');

	// Paraméterek
	$dt = readVariable($_POST['dt'], 'date');
	if (!isset($dt)) {
            $tolTime = strtotime($_TANEV['kezdesDt']);
            $igTime = min(time(), strtotime($_TANEV['zarasDt']));
	    $dt = date('Y-m-01', $t);
            for ($t = $tolTime; $t <= $igTime; $t = strtotime("next month", $t)) $dt = date('Y-m-01', $t);
	}
	$ADAT['dt'] = $dt;
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
	
		// Mentor/Ofő lekérdezése, konstans beállítása
                $ADAT['sniDiakAdat'] = getSniDiakAdat($diakId);
                define('__MENTOR',
                    __TANAR
                    && (
                        (__OSZTALYFONOK && in_array($osztalyId, $_OSZTALYA))
                        || __USERTANARID == $ADAT['sniDiakAdat']['mentorTanarId']
                    )
                );
		// Action
		if (
		    $_TANEV['statusz'] == 'aktív' 
		    && (__NAPLOADMIN || __VEZETOSEG || __MENTOR)
		) { // ? egyéb feltételek - határidő???
		    if ($action == 'haviOsszegzesRogzitese') {
			$Fields = getTableFields('sniHaviOsszegzes', 'naplo');
			foreach ($Fields as $attr => $attrNev) {
			    if (in_array($attr, array('diakId','valtozas')))
				$Param[$attr] = readVariable($_POST[$attr], 'id');
			    elseif ($attr == 'dt')
				$Param[$attr] = readVariable($_POST[$attr], 'date');
			    else
				$Param[$attr] = readVariable($_POST[$attr], 'string');
			}
			$Param['felelos'] = readVariable($_POST['felelos'], 'id');
			sniHaviOsszegzesRogzites($Param);
		    }
		}

		$ADAT['sniHaviOsszegzes'] = getHaviOsszegzes($diakId, $dt);
		$Tankorok = getTankorByOsztalyId($osztalyId, __TANEV, array('tanarral' => true));
		$ADAT['tanarIds'] = array();
		for ($i = 0; $i < count($Tankorok); $i++) {
		    for ($j = 0; $j < count($Tankorok[$i]['tanarok']); $j++) {
			if (!in_array($Tankorok[$i]['tanarok'][$j]['tanarId'], $ADAT['tanarIds'])) 
			    $ADAT['tanarIds'][] = $Tankorok[$i]['tanarok'][$j]['tanarId'];			
		    }
		}
		$ADAT['tanarok'] = getTanarok();
	    }


	}
	// Tool
	$TOOL['osztalySelect'] = array('tipus'=>'cella','paramName' => 'osztalyId', 'post' => array('dt'));
        $TOOL['diakSelect'] = array(
		'diakok' => $Diakok,
                'tipus'=>'cella','paramName' => 'diakId',
                'osztalyId'=> $osztalyId,'post' => array('osztalyId'),
                'statusz' => array('jogviszonyban van','magántanuló','jogviszonya felfüggesztve','jogviszonya lezárva'),
		'post' => array('osztalyId','dt')
        );
        $TOOL['datumSelect'] = array(
            'tipus' => 'sor', 'ParamName' => 'dt', 'tanev' => __TANEV, 'tolDt' => $_TANEV['kezdesDt'], 'igDt' => $_TANEV['zarasDt'],
            'hanyNaponta' => 'havonta', 'post' => array('diakId', 'osztalyId')
        );
	$TOOL['oldalFlipper'] = array('tipus' => 'cella',
                'url' => array('index.php?page=naplo&sub=sni&f=diakAllapot','index.php?page=naplo&sub=sni&f=fejlesztesiTerv','index.php?page=naplo&sub=sni&f=tantargyiFeljegyzesek'),
                'titleConst' => array('_DIAK_ALLAPOT','_HAVI_OSSZEGZES','_TANTARGYI_FELJEGYZESEK'),
                'post' => array('osztalyId','diakId','dt'),
                'paramName'=>'diakId');
        if (isset($osztalyId)) $TOOL['nyomtatasGomb'] = array('titleConst' => '_NYOMTATAS','tipus'=>'cella', 'url'=>'index.php?page=naplo&sub=nyomtatas&f=sniHaviJegyzokonyv','post' => array('osztalyId'));
	getToolParameters();
    }

?>
