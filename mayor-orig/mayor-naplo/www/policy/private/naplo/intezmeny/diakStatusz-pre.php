<?php

    if (_RIGHTS_OK !== true) die();

    if (!__NAPLOADMIN && !__TITKARSAG) {
        $_SESSION['alert'][] = 'message:insufficient_access';
    } else {

	require_once('include/modules/naplo/share/file.php');
        require_once('include/modules/naplo/share/diak.php');
        require_once('include/modules/naplo/share/diakModifier.php');
        require_once('include/modules/naplo/share/osztaly.php');
        require_once('include/modules/naplo/share/zaradek.php');

	$ADAT['tanev'] = $tanev = readVariable($_POST['tanev'], 'numeric unsigned', defined('__TANEV')?__TANEV:null );
	$ADAT['diakId'] = $diakId = readVariable($_POST['diakId'],'id', readVariable($_GET['diakId'], 'id'));
	$ADAT['osztalyId'] = $osztalyId = readVariable($_POST['osztalyId'],'numeric unsigned');
	$ADAT['statusz'] = getEnumField('naplo_intezmeny', 'diak', 'statusz');

	if (isset($diakId)) {

	    $ADAT['statusz2zaradek'] = array(
		'jogviszonyban van' => array_values($ZaradekIndex['jogviszony megnyitás']),
		'magántanuló' => array($ZaradekIndex['jogviszony változás']['magántanuló']),
		'vendégtanuló' => array(),
		'jogviszonya felfüggesztve' => array($ZaradekIndex['jogviszony változás']['felfüggesztés']),
		'jogviszonya lezárva' => array_values($ZaradekIndex['jogviszony lezárás'])
	    );
	    $ADAT['jogviszonyZaradekok'] = array_merge(
		$ADAT['statusz2zaradek']['jogviszonyban van'],
		$ADAT['statusz2zaradek']['magántanuló'],
		$ADAT['statusz2zaradek']['jogviszonya felfüggesztve'],
		$ADAT['statusz2zaradek']['jogviszonya lezárva']
	    );

	    if ($action == 'diakAdatModositas') {
		$Param = array(
		    'diakId' => $diakId,
		    'jogviszonyKezdete' => readVariable($_POST['jogviszonyKezdete'], 'date')
		);
		//if (isset($Param['jogviszonyKezdete'])) 
		diakAdatModositas($Param);
	    } elseif ($action == 'diakJogviszonyTorles') {
		$Param = array(
		    'diakId' => $diakId,
		    'dt' => readVariable($_POST['dt'], 'date'),
		    'statusz' => readVariable($_POST['statusz'], 'enum', null, $ADAT['statusz']),
		    'zaradekId' => readVariable($_POST['zaradekId'], 'id')
		);
		diakJogviszonyBejegyzesTorles($Param);
	    } elseif ($action == 'diakZaradek') {
		$Param = array(
		    'diakId' => $diakId,
		    'dt' => readVariable($_POST['dt'], 'date'),
		    'zaradekIndex' => readVariable($_POST['zaradekIndex'], 'numeric unsigned', null, $ADAT['jogviszonyZaradekok']),
		    'zaradekId' => readVariable($_POST['zaradekId'], 'id'),
		    'values' => readVariable($_POST['values'], 'string')
		);
		$ok = true;
		$tmp = explode('%', $Zaradek[ $Param['zaradekIndex'] ]['szoveg']);
		$Param['params'] = array();
		for ($i=1; $i < count($tmp); $i = $i+2) $Param['params'][] = $tmp[$i];
		$Param['csere'] = array();
		for ($i = 0; $i < count($Param['params']); $i++) {
                    $Param['csere'][ '%'.$Param['params'][$i].'%' ] = $Param['values'][$i];
                    if ($Param['values'][$i] == '') $ok = false;
                }
		if ($ok) {
		    if (zaradekRogzites($Param)) { $_SESSION['alert'][] = 'info:success'; }
		} else {
		    $_SESSION['alert'][] = 'message:empty_field';
		}
	    }

	    $ADAT['diakAdat'] = getDiakAdatById($diakId);
	    $ADAT['diakStatusz'] = getDiakJogviszony($diakId);
	    $ADAT['diakZaradekok'] = getDiakZaradekok($diakId, array('result' => 'multiassoc', 'keyfield' => 'dt'));
	    $ADAT['zaradekok'] = getZaradekok();

	    // záradékok státusz változásokhoz rendelése
	    foreach ($ADAT['diakStatusz'] as $index => $djAdat) {
		if (is_array($ADAT['diakZaradekok'][ $djAdat['dt'] ])) {
		    foreach ($ADAT['diakZaradekok'][ $djAdat['dt'] ] as $j => $zAdat) {
			if (in_array($zAdat['zaradekIndex'], $ADAT['statusz2zaradek'][ $djAdat['statusz'] ])) {
			    $ADAT['diakStatusz'][$index]['zaradek'] = $zAdat;
			    unset($ADAT['diakZaradekok'][ $djAdat['dt'] ][$j]);
			    break;
			}
		    }
		}
	    }

	} else {

	    $ADAT['hibas'] = getHibasJogviszony();

	}

        // ToolBar                                                                                                                                           
        $TOOL['tanevSelect'] = array('tipus' => 'cella', 'action' => 'tanevValasztas', 'post' => array('tanev','diakId'));
        $TOOL['osztalySelect'] = array('tipus' => 'cella', 'tanev' => $tanev, 'post' => array('tanev'));
        $TOOL['diakSelect'] = array('tipus'=>'cella', 'tanev'=>$tanev, 'osztalyId' => $osztalyId,
            'statusz' => $ADAT['statusz'],
            'post' => array('tanev','osztalyId')
        );
// EZ MI?! (maxValue?!)
	$TOOL['szamSelect'] = array('tipus' => 'cella', 'title' => 'DIAKIDTITLE', 'minValue' => 1, 'maxValue' => 3000, 'paramName' => 'diakId', 'post' => array('tanev','osztalyId'));
/*	$TOOL['oldalFlipper'] = array('tipus' => 'cella',
	    'url' => array('index.php?page=naplo&sub=intezmeny&f=diak'),
	    'titleConst' => array('_DIAKADATLAP'),
            'post' => array('diakId'),
        );
*/        getToolParameters();

    }

?>
