<?php
/*
    Module: naplo
*/

    if (_RIGHTS_OK !== true) die();
    if (!__NAPLOADMIN and !__VEZETOSEG) {
	$_SESSION['alert'][] = 'message:insufficient_access';
    } else {

	require_once('include/modules/naplo/share/tanar.php');
	require_once('include/modules/naplo/share/ora.php');
	require_once('include/modules/naplo/share/orarend.php');
        require_once('include/share/date/names.php');
	require_once('include/modules/naplo/share/tankor.php'); // Mozgatás, csere
	require_once('include/modules/naplo/share/diak.php');  // Mozgatás, csere
	require_once('include/modules/naplo/share/helyettesitesModifier.php');
	require_once('include/modules/naplo/share/terem.php');

	global $_TANEV;

	$napiMinOra = getMinOra();
	$napiMaxOra = getMaxOra();

	$dt = readVariable($_POST['dt'], 'datetime', null);
	if (!isset($dt)) $dt = readVariable($_GET['dt'], 'datetime', null);	
	if (!isset($dt)) $dt = date('Y-m-d');

        if ($_TANEV['statusz']!='aktív') $_SESSION['alert'][] = 'page:wrong_data:nem aktív a tanev:'.$_TANEV['tanev'];
    if (__FOLYO_TANEV || __NAPLOADMIN) { // Tanéven kívül már csak admin módosítson

	if (
	    strtotime($_TANEV['kezdesDt']) <= strtotime($dt) 
	    && strtotime($dt) <= strtotime($_TANEV['zarasDt'])
	) {
	    // itt egy ciklussal ellenőrizzük ne csak dt-t, hanem vissza a mai napig (ha jövő dátum van kiválasztva)
	    checkNaplo($dt);
	    $_dt = $dt;
	    while (strtotime($_dt)>time()) {
		$_dt = date('Y-m-d',strtotime('-1 day',strtotime($_dt)));
		checkNaplo($_dt);
	    }
	}
        // -------------- action --------------//

	if ($action == 'hianyzoModositas') {
	
	    /* Régi megoldás
	    $hianyzok = $_POST['hianyzok'];
	    if (!is_array($hianyzok)) $hianyzok = array();
	    $voltHianyzok = getHianyzok($dt);

	    $ujHianyzok = array_diff($hianyzok, $voltHianyzok);
	    $toroltHianyzok = array_diff($voltHianyzok, $hianyzok);
	    */
	    $ujHianyzok = readVariable($_POST['addHianyzo'], 'numeric unsigned');
	    $toroltHianyzok = readVariable($_POST['delHianyzo'], 'numeric unsigned');

	    ujHianyzokFelvetele($ujHianyzok, $dt);
	    toroltHianyzokVisszaallitasa($toroltHianyzok, $dt);

	} elseif ($action == 'helyettesitesRogzitese') {
	    foreach ($_POST as $name => $value) {
		list($gomb,$act,$id,$koord) = explode('_', $name);
		if ($gomb == 'gomb' && isset($id) && in_array($act, array('manual','mozgat','csere'))) {
		    $$act = readVariable($id, 'numeric unsigned', null);
		    break;
		}
	    }
	    $T = $_POST['T'];

	    if (is_array($T)) helyettesitesRogzites($T);

	} elseif ($action == 'keziBeallitas') {

	    $oraId = readVariable($_POST['oraId'], 'numeric unsigned');
	    $ki = readVariable($_POST['ki'], 'numeric unsigned');
	    $tipus = readVariable($_POST['tipus'], 'enum', null, array('elmarad','helyettesítés','felügyelet','összevonás','normál','normál máskor','elmarad máskor','egyéb'));
	    $teremId = readVariable($_POST['teremId'], 'numeric unsigned');
	    if (isset($oraId) && isset($tipus)) keziBeallitas($oraId, $ki, $tipus, $teremId);

	} elseif ($action == 'oraMozgatas') {

	    $mozgat = readVariable($_POST['mozgat'], 'numeric unsigned');
	    $ujDt = readVariable($_POST['ujDt'], 'date');
	    $ora = readVariable($_POST['ora'], 'numeric unsigned');
	    $rogzit = isset($_POST['rogzit']);

	    if ($rogzit && isset($ora) && isset($ujDt) && isset($mozgat)) { // kijelölte az óra új helyét és submit gombot nyomott
		if (oraMozgatas($mozgat, $ujDt, $ora)) unset($mozgat); // visszatérés a helyettesítés oldalra, ha sikerült
	    }
	    	    
	} elseif ($action == 'oraCsere') {

	    $csDt = readVariable($_POST['csDt'], 'date');
	    $csere = readVariable($_POST['csere'], 'numeric unsigned');
	    $csId = readVariable($_POST['csId'], 'numeric unsigned');
	    $rogzit = isset($_POST['rogzit']);

	    if ($rogzit && isset($csId)) {
		if (oraCsere($csere, $csId)) unset($csere); // visszatérés a napi helyettesítés oldalra
	    }

	}
	// ------------ action vége -----------//
    } // __FOLYO_TANEV
	else {
	$_SESSION['alert'][] = 'message:wrong_data:Nem folyó tanév és nem naplóadmin';
    }

	$lr = db_connect('naplo');
	if (isset($manual)) {

	    // Kézi beállítás
    		$oraAdat = getOraadatById($manual, __TANEV, $lr);
		$Termek = getSzabadTermek(array('dt' => $oraAdat['dt'], 'ora' => $oraAdat['ora'], 'ki' => $oraAdat['ki']), $lr);
		if ($oraAdat['teremId']!='') {
		    for ($i = 0;($i < count($Termek) && $Termek[$i]['teremId'] != $oraAdat['teremId']); $i++);
		    if ($i >= count($Termek)) $Termek[] = array('teremId' => $oraAdat['teremId'], 'leiras' => $oraAdat['teremId']);
		} else {
		    $Termek[] = array('teremId' => 'NULL', 'leiras' => '-');
		}
        	// Tanárnevek lekérése
        	$Tanarok = getTanarok(array('tanev' => __TANEV,'beDt'=>$dt,'kiDt'=>$dt), $lr);

	} elseif (isset($mozgat)) {

	    // Mozgatás
	    $ujDt = readVariable($_POST['ujDt'], 'date', $dt);
	    if (isset($ujDt)) checkNaplo($ujDt);

	    $oraAdat = getOraadatById($mozgat, __TANEV, $lr);
	    if (isset($oraAdat['kit']) && $oraAdat['kit'] != '') $tanarId = $oraAdat['kit'];
	    else $tanarId = $oraAdat['ki'];
	    $TANAR_DT_NAPI_ORAK = getTanarNapiOrak($tanarId, $dt, $lr);
	    $TANAR_UJDT_NAPI_ORAK = getTanarNapiOrak($tanarId, $ujDt, $lr);

	} elseif (isset($csere)) {
	
	    // Csere
	    $csDt = readVariable($_POST['csDt'], 'date', $dt);
	    if (isset($csDt)) checkNaplo($csDt);

	    $oraAdat = getOraadatById($csere, __TANEV, $lr);
	    if (isset($oraAdat['kit']) && $oraAdat['kit'] != '') $tanarId = $oraAdat['kit'];
	    else $tanarId = $oraAdat['ki'];

	    if ($_POST['csTanarId'] == '') $csTanarId = $tanarId;
	    else $csTanarId = $_POST['csTanarId'];
	    
    	    $Tanarok = getTanarok(array('tanev' => __TANEV,'kiDt'=>$dt,'beDt'=>$dt), $lr);
	    $TANAR_DT_NAPI_ORAK = getTanarNapiOrak($tanarId, $dt, $lr);
	    $CSTANAR_CSDT_NAPI_ORAK = getTanarNapiOrak($csTanarId, $csDt, $lr);
	    	    
	} elseif (isset($_POST['csereAttekintes']) && $_POST['csereAttekintes'] != '') {

	    $oraId = $_POST['csereAttekintes'];
	    $Orak = getCsereOraiByOraId($oraId, $lr);

	} elseif ($orarend != '') {
	    // Tanár órarendje
	} else {

	    // Normál helyettesítés kiíró

	    // A beírt hiányzók, helyettesítések...
	    $HELYETTESITES = getHianyzoOrak($dt, $lr); // == ADAT!
	    $HELYETTESITES['tanarTerheles'] = getOraTerhelesStatByTanarId(array('dt'=>$dt));
	}
	db_close($lr);
    }

   // toolBar
    if (__NAPLOADMIN) {
        $TOOL['datumSelect'] = array(
            'tipus'=>'cella', 'post' => array('tanarId', 'diakId', 'osztalyId', 'tankorId'),
            'paramName' => 'dt', 'hanyNaponta' => 1,
            'tolDt' => date('Y-m-d', strtotime($_TANEV['kezdesDt'])),
//	    'igDt' => $_TANEV['zarasDt'],
	    'igDt' => getTanitasiNap(array('direction'=>'elore', 'napszam'=>10, 'fromDt'=>'curdate()')),
	    'napTipusok' => array('tanítási nap', 'speciális tanítási nap')
        );
    } elseif (__VEZETOSEG) {
        $TOOL['datumSelect'] = array(
            'tipus' => 'cella', 'post' => array('tanarId', 'diakId', 'osztalyId', 'tankorId'),
            'paramName' => 'dt', 'hanyNaponta' => 1,
            'tolDt' => date('Y-m-d', strtotime(_ZARAS_HATARIDO)),
	    'igDt' => getTanitasiNap(array('direction'=>'elore', 'napszam'=>10, 'fromDt'=>'curdate()')),
	    'napTipusok' => array('tanítási nap', 'speciális tanítási nap')
        );
    }
    getToolParameters();

?>
