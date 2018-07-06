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
        require_once('include/modules/naplo/share/file.php');
	require_once('include/modules/naplo/osztalyozo/stat.php');
	require_once('include/modules/naplo/share/osztaly.php');
	require_once('include/modules/naplo/share/hianyzas.php');
	require_once('include/modules/naplo/share/intezmenyek.php');
	require_once('include/modules/naplo/share/szemeszter.php');
	require_once('include/modules/naplo/share/osztalyzatok.php');
	require_once('include/modules/naplo/share/diak.php');
	require_once('include/modules/naplo/share/kepzes.php');
	require_once('include/modules/naplo/share/targy.php');

	$INTEZMENY = getIntezmenyByRovidnev(__INTEZMENY);

	{
	    $q = "SELECT targyId,kirTargyId FROM targy WHERE kirTargyId IS NOT NULL";
	    $TARGYID2KIR = db_query($q, array('fv'=>'...','modul'=>'naplo_intezmeny','result'=>'assoc','keyfield'=>'targyId'));
	}

//	$ADAT['magatartasIds'] = getMagatartas();
//        $ADAT['szorgalomIds']= getSzorgalom();

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
	$ADAT['sorrendNev'] = $sorrendNev = readVariable($_POST['sorrendNev'], 'enum', 'bizonyítvány', array('napló','bizonyítvány','anyakönyv','ellenőrző','egyedi'));

	if (isset($_POST['osztalyId']) && $_POST['osztalyId'] != '') { $osztalyId = $_POST['osztalyId']; }
	elseif (__OSZTALYFONOK && !isset($_POST['osztalyId'])) { $osztalyId = $_OSZTALYA[0]; $_POST['osztalyId'] = $osztalyId; }

	if (isset($szemeszterId)) {

	    $ADAT['szemeszterAdat'] = getSzemeszterAdatById($szemeszterId);
	    define('__ZARO_SZEMESZTER', $ADAT['szemeszterAdat']['szemeszter'] == $ADAT['szemeszterAdat']['tanevAdat']['maxSzemeszter']);

	    $tanev = $ADAT['szemeszterAdat']['tanev'];

	    $Osztalyok = getOsztalyok($ADAT['szemeszterAdat']['tanev']);
	    if (isset($osztalyId)) {

		define('__OSZTALYFONOKE', (__OSZTALYFONOK === true && in_array($osztalyId, $_OSZTALYA)));
		// $ADAT['evfolyamJel'] = getEvfolyamJelByOsztalyId($osztalyId,$tanev);
		$ADAT['evfolyamJel'] = getEvfolyamJel($osztalyId,$tanev);

		// intézmény adatok
		$ADAT['intezmeny'] = getIntezmenyByRovidnev(__INTEZMENY);
		// osztály statisztikák
		$ADAT['osztaly'] = getOsztalyAdat($osztalyId, $ADAT['szemeszterAdat']['tanev']);
		// Az adott szemeszterben létezik-e az osztály
		for ($i = 0; ($i < count($Osztalyok) && $Osztalyok[$i]['osztalyId'] != $osztalyId); $i++);
		if ($i < count($Osztalyok)) {
		    $ADAT['diakok'] = getDiakok(array(
			'result' => 'assoc', 'osztalyId' => $osztalyId, 'tanev' => $ADAT['szemeszterAdat']['tanev'], 
			'tolDt' => $ADAT['szemeszterAdat']['zarasDt'], 'igDt' => $ADAT['szemeszterAdat']['zarasDt'],
			'extraAttrs' => 'oId'
			)

		    );
		    $ADAT['diakIds'] = array_keys($ADAT['diakok']);
		    $ADAT['targyak'] = getTargyakByDiakIds($ADAT['diakIds'], $ADAT['szemeszterAdat'], $osztalyId, $sorrendNev);
		    //for ($i = 0; $i < count($ADAT['targyak']); $i++) $ADAT['targyAdat'][ $ADAT['targyak'][$i]['targyNev'] ] = $ADAT['targyak'][$i]['targyId'];
		    $ADAT['jegyek'] = getDiakZarojegyekByEvfolyamJel($ADAT['diakIds'], $ADAT['evfolyamJel'], $ADAT['szemeszterAdat'], array('felevivel'=>true));
//		    $ADAT['hianyzas'] = getDiakHianyzasOsszesites($ADAT['diakIds'], $ADAT['szemeszterAdat']);
//var_dump($Osztalyok);
		    /* A pdfBizonyítvány ezeket használja: szemeszterAdat|intezmeny|diakok|jegyek|hianyzas|osztaly|targyAdat|targyak*/
		    $FORPRINT['file'] = fileNameNormal('kir-'.$ADAT['szemeszterAdat']['tanev'].'-'.$ADAT['szemeszterAdat']['szemeszter'].'-'.str_replace('.','',$ADAT['osztaly']['osztalyJel']));
		    $FORPRINT['base']['feleviE'] = ($ADAT['szemeszterAdat']['szemeszter']==1)?'true':'false'; // szöveggel
		    $FORPRINT['base']['omkod'] = substr($INTEZMENY['OMKod'],-6);
		    /* osztály adatok */
		    $FORPRINT['base']['kirOsztalyJelleg'] = $ADAT['osztaly']['kirOsztalyJellegId'];
		    $FORPRINT['base']['telephelyId'] = str_pad($ADAT['osztaly']['telephelyId'],3,0,STR_PAD_LEFT);
		    $FORPRINT['base']['tanevJel'] = $tanev.'/'.($tanev+1);
		    $FORPRINT['base']['evfolyamJel'] = $ADAT['evfolyamJel'];
		    $FORPRINT['base']['diak'] = $ADAT['diakIds'];

		    for ($i=0; $i<count($ADAT['diakIds']); $i++) {
			$_diakId = $ADAT['diakIds'][$i];
			$FORPRINT['diak'][$_diakId]['oId'] = $ADAT['diakok'][$ADAT['diakIds'][$i]]['oId'];
//			if (in_array($ADAT['jegyek']...
			if (is_array($ADAT['jegyek'][$_diakId])) {
			    foreach ($ADAT['jegyek'][$_diakId] as $_targyId => $A) {
				if (is_array($TARGYID2KIR[$_targyId])) {
				    $FORPRINT['diak'][$_diakId]['targy'][] = array(
					'kirTargyKod'=> $TARGYID2KIR[$_targyId]['kirTargyId'], 
					'jegy'=>intval($A[0]['jegy']) // igaziból jegyTipus - tól függő a megjelenés
				    );
				}
			    }
			}
		    }	    	
		    nyomtatvanyKeszites($FORPRINT,'kirBizonyitvanyExport');
		    header('Location: '.location('index.php?page=session&f=download&download=true&dir=naplo/export/kirBizonyitvanyExport&file='.$FORPRINT['file'].'.xml'));
		} else {
		    unset($osztalyId);
		}

	    }
	}

        $TOOL['szemeszterSelect'] = array('tipus'=>'cella','paramName' => 'szemeszterId', 'statusz' => array('aktív','lezárt') ,'post' => array( 'osztalyId'));
	$TOOL['osztalySelect'] = array('tipus'=>'cella','paramName' => 'osztalyId', 'tanev' => $ADAT['szemeszterAdat']['tanev'], 'post' => array('szemeszterId'));



	getToolParameters();
    
    }
?>
