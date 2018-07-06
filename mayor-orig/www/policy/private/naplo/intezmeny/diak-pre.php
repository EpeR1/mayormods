<?php
    if (_RIGHTS_OK !== true) die();

    if (!__NAPLOADMIN && !__VEZETOSEG && !__TITKARSAG && !__TANAR && !__DIAK) {
	$_SESSION['alert'][] = 'message:insufficient_access';
    } else {

	require_once('include/share/net/upload.php');
	require_once('include/modules/naplo/share/file.php');
	require_once('include/modules/naplo/share/diak.php');
	require_once('include/modules/naplo/share/szulo.php');
	require_once('include/modules/naplo/share/intezmenyek.php');
	require_once('include/modules/naplo/share/zaradek.php');
	require_once('include/modules/naplo/share/tankor.php');
	require_once('include/modules/naplo/share/jegy.php');
	require_once('include/modules/naplo/share/osztaly.php');
	require_once('include/modules/naplo/share/kepzes.php');
	require_once('include/modules/naplo/share/szemeszter.php');
	require_once('include/modules/naplo/share/ora.php');
	require_once('include/modules/naplo/share/diakModifier.php');
	require_once('include/modules/naplo/share/tankorDiakModifier.php');
	require_once('include/modules/naplo/share/hianyzas.php');
	require_once('include/modules/naplo/share/hianyzasModifier.php');
	require_once('include/modules/naplo/share/jegyModifier.php');
	require_once('include/modules/naplo/share/osztalyModifier.php');
	require_once('include/share/print/pdf.php');

	global $_JSON;

	$ADAT['tanev'] = $tanev = readVariable($_POST['tanev'], 'numeric unsigned', defined('__TANEV')?__TANEV:null );
	if (__DIAK) $ADAT['diakId'] = $diakId = readVariable(__USERDIAKID,'id',null);
	else $ADAT['diakId'] = $diakId = readVariable($_POST['diakId'],'id',readVariable($_GET['diakId'],'id',null));
	$ADAT['osztalyId'] = $osztalyId = readVariable($_POST['osztalyId'],'id');

	$ADAT['szocialisHelyzet'] = getSetField('naplo_intezmeny', 'diak', 'szocialisHelyzet');
	$ADAT['penzugyiStatusz'] = getEnumField('naplo_intezmeny', 'diak', 'penzugyiStatusz');
	$ADAT['lakohelyiJellemzo'] = getEnumField('naplo_intezmeny', 'diak', 'lakohelyiJellemzo');
	$ADAT['torvenyesKepviselo'] = getSetField('naplo_intezmeny', 'diak', 'torvenyesKepviselo');
	$ADAT['fogyatekossag'] = getSetField('naplo_intezmeny', 'diak', 'fogyatekossag');
	$ADAT['kozteruletJelleg'] = getEnumField('naplo_intezmeny', 'diak', 'lakhelyKozteruletJelleg');
	$ADAT['statusz'] = getEnumField('naplo_intezmeny', 'diak', 'statusz');
// EZ MI????
	$ADAT['zaradek'] = $ZaradekIndex['jogviszony megnyitás'];
	$ADAT['iktatoszam'] = readVariable($_POST['iktatoszam'], 'string' );
	//$ZaradekIndex['jogviszony']['megnyitas'|'változás'|'lezárás']

	$ADAT['bekerulesModja'] = array_keys($ADAT['zaradek']);
	array_push($ADAT['bekerulesModja'],'beiratkozásra vár','vendégtanuló');
	define('_MODOSITHAT',(__NAPLOADMIN || __TITKARSAG));
	//define('_KERELMEZHET',(__DIAK===true));
	define('_KERELMEZHET',false);
	if (isset($diakId)) {
	    // A SET típusú attribútumok string reprezentációja
	    if ($action == 'diakSzocialisAdatModositas') {
		$tk = $szH = $fgy = array(); // ilyenkor kell definiálni - legalább üresként - így lehet törölni
		if (isset($_POST['szocialisHelyzet'])) {
		    $szH = readVariable($_POST['szocialisHelyzet'], 'enum', null, $ADAT['szocialisHelyzet']);
		}
		if (isset($_POST['fogyatekossag'])) {
		    $fgy = readVariable($_POST['fogyatekossag'], 'enum', null, $ADAT['fogyatekossag']);
		}
		if (isset($_POST['torvenyesKepviselo'])) {
		    $tk = readVariable($_POST['torvenyesKepviselo'], 'enum', null, $ADAT['torvenyesKepviselo']);
		}
		// A törlés miatt mindenképp kell legyen beállítva valami
		$_POST['szocialisHelyzet'] = is_array($szH) ? implode(',', $szH) : null;
		$_POST['fogyatekossag'] = is_array($fgy) ? implode(',', $fgy) : null;
		$_POST['torvenyesKepviselo'] = is_array($tk) ? implode(',', $tk) : null;
	    }
	    // diák adatainak lekérdezése
	    $ADAT['diakAdat'] = getDiakAdatById($diakId); // csak a státusz miatt kell...
	    // action
	    if (_MODOSITHAT) {
		$LZI = array_values($ZaradekIndex['jogviszony lezárás']);
		$ADAT['jogviszonyLezarasZaradek'] = getZaradekokByIndexes($LZI);
		if ($action == 'jogviszonyValtas') {
		    $ADAT['jogviszonyValtasDt'] = readVariable($_POST['jogviszonyValtasDt'], 'datetime');
		    $ADAT['ujStatusz'] = readVariable($_POST['statusz'], 'enum', null, $ADAT['statusz']);
		    if ($ADAT['ujStatusz'] == 'jogviszonya felfüggesztve') {
			$ADAT['felfuggesztesOk'] = readVariable($_POST['felfuggesztesOk'], 'string');
			$ADAT['felfuggesztesIgDt'] = readVariable($_POST['felfuggesztesIgDt'], 'string');
		    } elseif ($ADAT['ujStatusz'] == 'jogviszonya lezárva') {
			/* A lezárás záradékolása sokféle lehet, userinterakció */
			$ADAT['lezarasZaradekIndex'] = readVariable($_POST['lezarasZaradekIndex'], 'numeric unsigned', null, $LZI);
			$ADAT['lezarasIgazolatlanOrakSzama'] = readVariable($_POST['lezarasIgazolatlanOrakSzama'], 'numeric unsigned');
			$ADAT['lezarasIskola'] = readVariable($_POST['lezarasIskola'], 'string');
		    }
		    diakJogviszonyValtas($ADAT);
		} elseif ( $action == 'diakAlapadatModositas'
			|| $action == 'diakSzuletesiAdatModositas'
			|| $action == 'diakCimModositas'
			|| $action == 'diakElerhetoseg'
			|| $action == 'diakElerhetosegModositas'
			|| $action == 'diakTanulmanyiAdatModositas'
			|| $action == 'diakSzocialisAdatModositas'
		) {
		    $_JSON['result'] = diakAdatModositas($_POST);
		} elseif ($action== 'diakHozottHianyzas') {
		    diakHozottHianyzas($_POST);
		} elseif ($action== 'diakTorol' && $ADAT['diakAdat']['statusz'] == 'felvételt nyert' ) { // csak a felvételt nyert
		    if (diakTorol($ADAT['diakAdat'])) { // csak a felvételt nyert
			unset($ADAT['diakAdat']);
			unset($diakId);
		    }
		} elseif ($action == 'diakKepUpload') {
		    // --TODO könyvtár létrehozás?
		    mayorFileUpload(array('subdir'=>_DOWNLOADDIR.'/private/naplo/face/'.__TANEV,'filename'=>$diakId.'.jpg'));
		} elseif ($action == 'sulixREST') {
/*
		    require('include/share/net/rest.php');
		    //$server = 'mayor1.ulx.hu';
		    $server = 'localhost';
		    $port = 8888;

		    if (isset($_POST['createAccount'])) {
			$resource = '/Users/Create';
			$method = 'PUT';
			$params = array(
			    'params' => array(
			        'sn'=> $ADAT['diakAdat']['viseltCsaladinev'],
			        'givenname' => $ADAT['diakAdat']['viseltUtonev'],
			        'birth_year' => explode('-', $ADAT['diakAdat']['szuletesiIdo'])[0],
			        'birth_month' => explode('-', $ADAT['diakAdat']['szuletesiIdo'])[1],
			        'birth_day' => explode('-', $ADAT['diakAdat']['szuletesiIdo'])[2],
			        'employeeNumber' => $ADAT['diakAdat']['oId']
			    )
			);
		    } elseif (isset($_POST['deleteAccount'])) {
			$resource = '/Users/Delete/Eduid/'.$ADAT['diakAdat']['oId'];
			$method = 'DELETE';
		    }
		    $uri = 'https://'.$server.':'.$port.$resource;
		    try {
			$ret = restRequest($uri, $method, $params);
		    } catch (Exception $e) {
			dump($e->getMessage());
		    }
		    if ($ret['http']['status'] == 200) {
//dump('Success');
		    } else {
//dump($ret['http']['status']);
//dump($ret);
		    }
*/
		}
	    }
	} else {
	    //$ADAT['zaradek'] = array('felvétel' => 1,'átvétel' => 2, 'áthelyezés' => 3, 'beiratkozásra vár' => null);
	    if (isset($osztalyId)) {
		$ADAT['osztaly'] = getOsztalyAdat($osztalyId, $tanev);
		$ADAT['zaradek']['felvétel'] = $ZaradekIndex['jogviszony']['megnyitás']['felvétel osztályba']; // 1 helyett --> 67  ???
	    }
	} // van diakId / nincs diakId


	if (_MODOSITHAT===true && $action == 'ujDiak') {
	    $kotelezoParamOk = (isset($_POST['viseltCsaladinev']) && $_POST['viseltCsaladinev'] != '');
	    $kotelezoParamOk &= (isset($_POST['kezdoTanev']) && $_POST['kezdoTanev'] != '');
	    $kotelezoParamOk &= (isset($_POST['kezdoSzemeszter']) && $_POST['kezdoSzemeszter'] != '');
	    $kotelezoParamOk &= ($_POST['felvetelTipus']=='beiratkozásra vár' || (isset($_POST['jogviszonyKezdete']) && $_POST['jogviszonyKezdete'] != ''));
	    if ($kotelezoParamOk) {
		$_POST['zaradek'] = $ADAT['zaradek']; // felülírjuk a post-ot... remek
		$_POST['osztaly'] = $ADAT['osztaly'];
		$diakId = ujDiak($_POST);
	    } else {
		$_SESSION['alert'][] = 'message:empty_field:(viseltCsaladinev,kezdoTanev,kezdoSzemeszter,jogviszonyKezdete)';
	    }
	}

	if (isset($diakId)) {
	    // diák adatainak lekérdezése
	    $Szulok = getSzulok();
	    $ADAT['diakAdat'] = getDiakAdatById($diakId);
	    switch ($ADAT['diakAdat']['statusz']) {
		case 'felvételt nyert': 
		    $ADAT['valthatoStatusz'] = array('jogviszonyban van');
		    break;
		case 'jogviszonya lezárva':
		    $ADAT['valthatoStatusz'] = array('jogviszonyban van', 'vendégtanuló');
		    break;
		case 'vendégtanuló':
		    $ADAT['valthatoStatusz'] = array('jogviszonya lezárva');
		    break;
		default:
		    $ADAT['valthatoStatusz'] = array_diff($ADAT['statusz'],array($ADAT['diakAdat']['statusz'],'felvételt nyert'));
		    break;
	    }
	    $ADAT['diakAdat']['anyaNev'] = $Szulok[ $ADAT['diakAdat']['anyaId'] ]['szuleteskoriCsaladinev']?
		trim(implode(' ', array(
		    $Szulok[ $ADAT['diakAdat']['anyaId'] ]['szuleteskoriNevElotag'],  
		    $Szulok[ $ADAT['diakAdat']['anyaId'] ]['szuleteskoriCsaladinev'], 
		    $Szulok[ $ADAT['diakAdat']['anyaId'] ]['szuleteskoriUtonev']
		))):$Szulok[ $ADAT['diakAdat']['anyaId'] ]['szuloNev'];
	    $ADAT['diakAdat']['apaNev'] = 
		trim(implode(' ', array(
		    $Szulok[ $ADAT['diakAdat']['apaId'] ]['szuleteskoriNevElotag'],  
		    $Szulok[ $ADAT['diakAdat']['apaId'] ]['szuleteskoriCsaladinev'], 
		    $Szulok[ $ADAT['diakAdat']['apaId'] ]['szuleteskoriUtonev']
		))) . ' - ' . $Szulok[ $ADAT['diakAdat']['apaId'] ]['szuloNev'];
	    $ADAT['diakAdat']['gondviseloNev'] = $Szulok[ $ADAT['diakAdat']['gondviseloId'] ]['szuleteskoriCsaladinev']?
		trim(implode(' ', array(
		    $Szulok[ $ADAT['diakAdat']['gondviseloId'] ]['szuleteskoriNevElotag'],  
		    $Szulok[ $ADAT['diakAdat']['gondviseloId'] ]['szuleteskoriCsaladinev'], 
		    $Szulok[ $ADAT['diakAdat']['gondviseloId'] ]['szuleteskoriUtonev']
		))):$Szulok[ $ADAT['diakAdat']['gondviseloId'] ]['szuloNev'];

	    $ADAT['diakAdat']['osztaly'] = getDiakOsztalya($diakId,array('tanev'=>$tanev));
	    $ADAT['diakAdat']['mindenOsztaly'] = getDiakMindenOsztaly($diakId);
	    $ADAT['diakJogviszony'] = getDiakJogviszony($diakId);
	    $ADAT['hozottHianyzas'] = getDiakHozottHianyzas($diakId,array('tanev'=>$tanev));
	    $ADAT['diakKepzes'] = getKepzesByDiakId($diakId);

	}


	$ADAT['osztalyok'] = getOsztalyok($tanev,array('result'=>'assoc', 'minden'=>true));

	// ToolBar
	$TOOL['tanevSelect'] = array('tipus' => 'cella', 'action' => 'tanevValasztas', 'post' => array('tanev','diakId'));
	if (!__DIAK) {
	    $TOOL['osztalySelect'] = array('tipus' => 'cella', 'tanev' => $tanev, 'post' => array('tanev'));
	    $TOOL['diakSelect'] = array('tipus'=>'cella', 'tanev'=>$tanev, 'osztalyId' => $osztalyId,
		'statusz' => $ADAT['statusz'],
		'post' => array('tanev','osztalyId')
	    );
	}
	if (isset($osztalyId) || isset($diakId)) {
            $TOOL['nyomtatasGomb'] = array('titleConst' => '_NYOMTATAS','tipus'=>'cella', 'url'=>'index.php?page=naplo&sub=nyomtatas&f=diakAdatlap','post' => array('osztalyId','diakId','tanev'));
	    if (!__DIAK) {
		$TOOL['diakLapozo'] = array('tipus'=>'sor', 'tanev'=>$tanev, 'osztalyId' => $osztalyId,
		    'statusz' => $ADAT['statusz'],
		    'post' => array('tanev','osztalyId')
		);
	    }
	}
	if (__NAPLOADMIN === true) {
	    $TOOL['oldalFlipper'] = array('tipus' => 'cella',
		'url' => array('index.php?page=naplo&sub=intezmeny&f=diakStatusz'),
		'titleConst' => array('_DIAKSTATUSZ'),
        	'post' => array('diakId'),
    	    );
	}

	getToolParameters();
    } // naploadmin / vezetőség / titkárság / tanár

?>
