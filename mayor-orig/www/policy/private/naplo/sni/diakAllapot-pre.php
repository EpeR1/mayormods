<?php

    if (_RIGHTS_OK !== true) die();

    if (!__NAPLOADMIN && !__VEZETOSEG && !__TANAR) {

        $_SESSION['alert'][] = 'page:insufficient_access';

    } else {

	require_once('include/modules/naplo/share/osztaly.php');
	require_once('include/modules/naplo/share/diak.php');
	require_once('include/modules/naplo/share/file.php');
	require_once('include/modules/naplo/share/tanar.php');
	require_once('include/modules/naplo/share/sni.php');
	require_once('include/modules/naplo/share/tankor.php');

	// Paraméterek
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
	    if (isset($diakId)) {
	
		$ADAT['fields']['olvasas'] = getEnumField('naplo','sniDiakAllapot','olvasas');
		$ADAT['fields']['olvasasTempoja'] = getEnumField('naplo','sniDiakAllapot','olvasasTempoja');
		$ADAT['fields']['olvasasHibak'] = getSetField('naplo','sniDiakAllapot','olvasasHibak');
		$ADAT['fields']['iras'] = getEnumField('naplo','sniDiakAllapot','iras');
		$ADAT['fields']['iraskepe'] = getEnumField('naplo','sniDiakAllapot','iraskepe');
		$ADAT['fields']['irasHibak'] = getSetField('naplo','sniDiakAllapot','irasHibak');
		$ADAT['fields']['szovegertes'] = getEnumField('naplo','sniDiakAllapot','szovegertes');
		$ADAT['fields']['matematika'] = getSetField('naplo','sniDiakAllapot','matematika');
		$ADAT['fogyatekossag'] = getSetField('naplo_intezmeny','diak', 'fogyatekossag');

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
		    if ($action == 'allapotRogzites') {
			$Fields = getTableFields('sniDiakAllapot','naplo');
			foreach ($Fields as $attr => $attrNev) {
			    if (is_array($ADAT['fields'][$attr])) {
				$Param['diakAllapot'][$attr] = readVariable($_POST[$attr], 'enum', null, $ADAT['fields'][$attr]);
				if (is_array($Param['diakAllapot'][$attr])) $Param['diakAllapot'][$attr] = implode(',',$Param['diakAllapot'][$attr]);
			    } elseif (in_array($attr, array('diakId','szemeszter','vizsgalatTanarId','priorizalas'))) {
				$Param['diakAllapot'][$attr] = readVariable($_POST[$attr], 'id');
			    } elseif ($attr == 'vizsgalatDt') {
				$Param['diakAllapot'][$attr] = readVariable($_POST[$attr], 'date');
			    } else {
				$Param['diakAllapot'][$attr] = readVariable($_POST[$attr], 'string');
			    }
			}
			foreach (array('gyengeseg','erosseg') as $val) {
			    $Param['gyengesegekErossegek'][$val.'Leiras'] = readVariable($_POST[$val.'Leiras'], 'string');
			    $Param['gyengesegekErossegek'][$val.'Prioritas'] = readVariable($_POST[$val.'Prioritas'], 'numeric unsigned');
			}
			if ($diakId != $ADAT['diakId']) {
			    $_SESSION['alert'][] = 'message:wrong_data:allapotRogzites:diakId='.$diakId.'?'.$Param['diakId'];
			} else {
			    sniDiakAllapotRogzites($Param);
			}
		    } elseif ($action == 'sniDiakAdatRogzites') {
			$Param['diakId'] = $diakId;
			$Param['kulsoInfo'] = readVariable($_POST['kulsoInfo'], 'string');
			$Param['mentorTanarId'] = readVariable($_POST['mentorTanarId'], 'id');
			$Param['fogyatekossag'] = readVariable($_POST['fogyatekossag'], 'enum', null, $ADAT['fogyatekossag']);
			sniDiakAdatRogzites($Param);
		    }
		}
		$ADAT['diakAdat'][$diakId] = getDiakAdatById($diakId);
		$ADAT['diakAllapot'] = getDiakAllapot($diakId);
		$ADAT['sniDiakAdat'] = getSniDiakAdat($diakId);
		// Osztály tanárai
                $Tankorok = getTankorByOsztalyId($osztalyId, __TANEV, array('tanarral' => true));
                $ADAT['tanarIds'] = array();
                for ($i = 0; $i < count($Tankorok); $i++) {
                    for ($j = 0; $j < count($Tankorok[$i]['tanarok']); $j++) {
                        if (!in_array($Tankorok[$i]['tanarok'][$j]['tanarId'], $ADAT['tanarIds']))
                            $ADAT['tanarIds'][] = $Tankorok[$i]['tanarok'][$j]['tanarId'];
                    }
                }
		// Összes tanár névsorban
		$ADAT['tanarok'] = getTanarok();
	    }

	}
	// Tool
	$TOOL['osztalySelect'] = array('tipus'=>'cella','paramName' => 'osztalyId', 'post' => array());
	if (isset($osztalyId)) {
    	    $TOOL['diakSelect'] = array(
		'diakok' => $Diakok,
                'tipus'=>'cella','paramName' => 'diakId',
                'osztalyId'=> $osztalyId,'post' => array('osztalyId'),
                'statusz' => array('jogviszonyban van','magántanuló','jogviszonya felfüggesztve','jogviszonya lezárva')
    	    );
	}
	$TOOL['oldalFlipper'] = array('tipus' => 'cella',
                'url' => array('index.php?page=naplo&sub=sni&f=diakAllapot','index.php?page=naplo&sub=sni&f=fejlesztesiTerv','index.php?page=naplo&sub=sni&f=tantargyiFeljegyzesek'),
                'titleConst' => array('_DIAK_ALLAPOT','_HAVI_OSSZEGZES','_TANTARGYI_FELJEGYZESEK'),
                'post' => array('osztalyId','diakId','dt'),
                'paramName'=>'diakId');
	getToolParameters();

    }

?>
