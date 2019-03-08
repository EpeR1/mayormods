<?php

    if (_RIGHTS_OK !== true) die();
//    if (__NAPLOADMIN !== true && __VEZETOSEG !== true) $_SESSION['alert'][] = 'page:insufficient_access';

    require_once('include/modules/naplo/share/osztaly.php');
    require_once('include/modules/naplo/share/diak.php');
    require_once('include/modules/naplo/share/kepzes.php');
    require_once('include/modules/naplo/share/szulo.php');
    require_once('include/modules/naplo/share/hianyzas.php');
    require_once('include/modules/naplo/share/tankor.php');
    require_once('include/modules/naplo/share/tanar.php');

    $diakId = readVariable($_POST['diakId'], 'id',null);

    if (!is_numeric($diakId)) {
	$_JSON = array();
	exit;
    }
    /* PRIVÁT ADATOK */
if (__NAPLOADMIN === true || __VEZETOSEG === true || __TITKARSAG === true || __TANAR ===true || $diakId == __USERDIAKID) {
    $_JSON = getDiakAdatById($diakId);
    $_JSON['diakOsztaly'] = getDiakMindenOsztaly($diakId);
    for ($i=0;$i<count($_JSON['diakOsztaly']); $i++) {
	$OSZTALYIDS[] = $_JSON['diakOsztaly'][$i]['osztalyId'];
    }
    $_JSON['osztalyAdat'] = getOsztalyok(null,array('minden'=>true,'result'=>'assoc','keyfield'=>'osztalyId','osztalyIds'=>$OSZTALYIDS));
    $_JSON['diakJogviszony'] = getDiakJogviszony($diakId);
//    $_JSON['diakHianyzasOsszesites'] = getDiakHianyzasOsszesites($diakId,szemeszteradat);
    $_JSON['diakHianyzasStat'] = getDiakHianyzasStat($diakId);
    $_JSON['diakTankor'] = getTankorByDiakId($diakId,__TANEV);
    for ($i=0; $i<count($_JSON['diakTankor']); $i++) {
	$_JSON['diakTankorAssoc'][$_JSON['diakTankor'][$i]['tankorId']] = $_JSON['diakTankor'][$i];
    }
    $_JSON['diakFelmentes'] = getTankorDiakFelmentes($diakId, __TANEV, array('csakId'=>false));
    // $_JSON['diakJogviszony'] = getDiakTorzslapszam($diakId);

    $_tmp = getKepzesByDiakId($diakId);
    $_JSON['diakKepzes'] = $_tmp[$diakId];
    
    $tmp = getDiakSzulei($diakId);
    $_JSON['diakSzulo'] = array();
    if (is_array($tmp[0]) && count($tmp[0])>=1) {
	foreach ($tmp[0] as $tipus => $id) if (!is_null($id)) $szuloIds[] = $id;
	if (count($szuloIds)>0) {
	    $_JSON['diakSzulo'] = getSzulok(array('csakId'=>false,'result'=>'indexed','szuloIds'=>$szuloIds));
//	    $_JSON['szuloIds'] = $szuloIds;
	}
    }
}

    /* PUBLIKUS ADATOK */
    $_JSON['diakId'] = $diakId;
    $_JSON['tanev'] = __TANEV;

    // controllok:
    //enum('felvételt nyert','jogviszonyban van','magántanuló','vendégtanuló','jogviszonya felfüggesztve','jogviszonya lezárva')
//    if ($_JSON['statusz'] == 'jogviszonya lezárva') {
//    } else {
//    }
?>