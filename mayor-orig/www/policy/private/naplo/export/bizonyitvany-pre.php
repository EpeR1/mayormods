<?php

    if (_RIGHTS_OK !== true) die();
    
    define('_TIME',strtotime(date('Y-m-d')));

    if (
	!__NAPLOADMIN && !__VEZETOSEG && !__TANAR && !__TITKARSAG
    ) {
	$_SESSION['alert'][] = 'page:insufficient_access';
    } else {
    
	require_once('include/share/date/names.php');
	require_once('include/share/print/pdf.php');
	require_once('include/modules/naplo/osztalyozo/stat.php');
	require_once('include/modules/naplo/share/nap.php');
	require_once('include/modules/naplo/share/osztaly.php');
	require_once('include/modules/naplo/share/hianyzas.php');
	require_once('include/modules/naplo/share/intezmenyek.php');
	require_once('include/modules/naplo/share/szemeszter.php');
	require_once('include/modules/naplo/share/osztalyzatok.php');
	require_once('include/modules/naplo/share/diak.php');
	require_once('include/modules/naplo/share/kepzes.php');
	require_once('include/modules/naplo/share/targy.php');
	require_once('include/modules/naplo/share/szulo.php');
	require_once('include/modules/naplo/share/file.php');

	$ADAT['formatum'] = readVariable($_POST['formatum'], 'enum', 'ods', array('csv','ods','xml'));
	if ($ADAT['formatum'] == 'xml') $ADAT['mime'] = 'application/vnd.ms-excel';

	if (file_exists($file = _MAYOR_DIR.'/export/module-naplo/'.__INTEZMENY.'/bizonyitvany.php'))
	    require_once($file);
	elseif (file_exists($file = _MAYOR_DIR.'/export/module-naplo/default/bizonyitvany.php'))
	    require_once($file);

	$ADAT['magatartasIdk'] = getMagatartas();
        $ADAT['szorgalomIdk']= getSzorgalom();

	// melyik szemeszter adatait nézzük
	if (isset($_POST['szemeszterId']) && $_POST['szemeszterId'] != '') {
	    $szemeszterId = $_POST['szemeszterId'];
	} elseif (!isset($_POST['szemeszterId'])) {
	    for ($i = 1; $i <= count($_TANEV['szemeszter']); $i++) {
		if (
		    strtotime($_TANEV['szemeszter'][$i]['kezdesDt']) <= _TIME
		    && strtotime($_TANEV['szemeszter'][$i]['zarasDt']) >= _TIME
		) {
		    $_POST['szemeszterId'] = $szemeszterId = $_TANEV['szemeszter'][$i]['szemeszterId'];
		    break;
		}
	    }
	}
	$ADAT['sorrendNev'] = $sorrendNev = readVariable($_POST['sorrendNev'], 'enum', 'bizonyítvány', array('bizonyitvany'));

	$ADAT['osztalyId'] = $osztalyId = readVariable($_POST['osztalyId'], 'id');
	if (!isset($osztalyId) && __OSZTALYFONOK) { $ADAT['osztalyId'] = $osztalyId = $_OSZTALYA[0]; }

	if (isset($szemeszterId)) {

	    $ADAT['szemeszterAdat'] = getSzemeszterAdatById($szemeszterId);
	    define('__ZARO_SZEMESZTER', $ADAT['szemeszterAdat']['szemeszter'] == $ADAT['szemeszterAdat']['tanevAdat']['maxSzemeszter']);

	    $Osztalyok = getOsztalyok($ADAT['szemeszterAdat']['tanev']);
	    if (isset($osztalyId) && $action == 'bizonyitvanyExport') {

		define('__OSZTALYFONOKE', (__OSZTALYFONOK === true && in_array($osztalyId, $_OSZTALYA)));

		// intézmény adatok
		$ADAT['intezmeny'] = getIntezmenyByRovidnev(__INTEZMENY);
		// osztály statisztikák
		$ADAT['osztaly'] = getOsztalyAdat($osztalyId, $ADAT['szemeszterAdat']['tanev']);
		// Az adott szemeszterben létezik-e az osztály
		for ($i = 0; ($i < count($Osztalyok) && $Osztalyok[$i]['osztalyId'] != $osztalyId); $i++);
		if ($i < count($Osztalyok)) {
		    $Szulok = getSzulok();                                                                                                                               
		    $ADAT['diakok'] = getDiakok(array('osztalyId' => $osztalyId, 'tanev' => $ADAT['szemeszterAdat']['tanev']));
		    for ($i = 0; $i < count($ADAT['diakok']); $i++) {
			$ADAT['diakIds'][] = intval($ADAT['diakok'][$i]['diakId']);
			$ADAT['diakAdat'][ $ADAT['diakok'][$i]['diakId'] ] = getDiakAdatById( $ADAT['diakok'][$i]['diakId'] );
    			foreach ($ADAT['diakAdat'] as $diakId => $dAdat) {
        		    foreach (array('anya','apa','gondviselo') as $tipus) {
            			$szuloId = $dAdat[ $tipus.'Id' ];
            			if (is_array($Szulok[$szuloId])) foreach ($Szulok[$szuloId] as $attr => $value) {
                		    $ADAT['diakAdat'][$diakId][ $tipus . ucfirst($attr) ] = $value;
            			} elseif ($i == 0 && is_array($Szulok[1])) foreach ($Szulok[1] as $attr => $value) {
                		    $ADAT['diakAdat'][$diakId][ $tipus . ucfirst($attr) ] = '';
            			}
        		    }
    			}
		    }
		    $ADAT['targyak'] = getTargyakByDiakIds($ADAT['diakIds'], $ADAT['szemeszterAdat'], $osztalyId, $sorrendNev);
		    //for ($i = 0; $i < count($ADAT['targyak']); $i++) $ADAT['targyAdat'][ $ADAT['targyak'][$i]['targyNev'] ] = $ADAT['targyak'][$i]['targyId'];
		    $ADAT['tanarok'] = getTanarokByDiakIds($ADAT['diakIds'], $ADAT['szemeszterAdat']);
		    $ADAT['jegyek'] = getDiakZarojegyek($ADAT['diakIds'], $ADAT['szemeszterAdat']['tanev'], $ADAT['szemeszterAdat']['szemeszter']);
		    $ADAT['hianyzas'] = getDiakHianyzasOsszesites($ADAT['diakIds'], $ADAT['szemeszterAdat']);

		    /* A pdfBizonyítvány ezeket használja: szemeszterAdat|intezmeny|diakok|jegyek|hianyzas|osztaly|targyAdat|targyak*/
		    $file = fileNameNormal('bizonyitvany-'.$ADAT['szemeszterAdat']['tanev'].'-'.$ADAT['szemeszterAdat']['szemeszter'].'-'
			.str_replace('/','',str_replace('.','',$ADAT['osztaly']['osztalyJel']))); // 9/ Ny.a --> 9Nya
		    if (exportBizonyitvany($file, $ADAT))
			header('Location: '.location('index.php?page=session&f=download&download=true&dir=naplo/export/bizonyitvany&file='.$file.'.'.$ADAT['formatum'].'&mimetype='.$ADAT['mime']));
		} else {
		    unset($osztalyId);
		}

	    }
	}

        $TOOL['szemeszterSelect'] = array('tipus'=>'cella','paramName' => 'szemeszterId', 'statusz' => array('aktív','lezárt') ,'post' => array('sorrendNev', 'osztalyId'));
	$TOOL['osztalySelect'] = array('tipus'=>'cella','paramName' => 'osztalyId', 'tanev' => $ADAT['szemeszterAdat']['tanev'], 'post' => array('szemeszterId', 'sorrendNev'));
	getToolParameters();
    
    }
?>
