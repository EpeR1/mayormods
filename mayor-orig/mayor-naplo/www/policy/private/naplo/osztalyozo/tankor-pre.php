<?php

if (_RIGHTS_OK !== true) die();

if (!__NAPLOADMIN && !__VEZETOSEG && !__TANAR && !__DIAK && !__TITKARSAG) {
	$_SESSION['alert'][] = 'message:insufficient_access';
} elseif (__DIAK) {
	header('Location: '.location('index.php?page=naplo&sub=osztalyozo&f=diak'));
} else {

	require_once('include/modules/naplo/share/tanar.php');
	require_once('include/modules/naplo/share/osztaly.php');
	require_once('include/modules/naplo/share/tankor.php');
	require_once('include/modules/naplo/share/diak.php');
	require_once('include/share/date/names.php');
	require_once('include/modules/naplo/share/osztalyzatok.php');
	require_once('include/modules/naplo/share/dolgozat.php');
	require_once('include/modules/naplo/share/ora.php');
	require_once('include/modules/naplo/share/szemeszter.php');
	require_once('include/modules/naplo/share/zaroJegyModifier.php');
	require_once('include/modules/naplo/share/kepzes.php');
	require_once('include/modules/naplo/share/jegy.php');

	$time = time();
	// A toolBar-ból érkező adatok feldolgozása, beállítása - mit is akarunk épp látni..
	$diakId = readVariable($_POST['diakId'], 'id');
	$osztalyId = readVariable($_POST['osztalyId'], 'id', readVariable($_GET['osztalyId'], 'id') );
	//	if (isset($diakId)) {
	//	    // Nem egyértelmű az osztály - itt legfeljebb ellenőrizni kell, hogy tagja-e... //$osztaly = getOsztalyByDiakId($diakId);
	$tankorId = readVariable($_POST['tankorId'], 'id', readVariable($_GET['tankorId'], 'id') );
	$tanarId = readVariable($_POST['tanarId'], 'id', readVariable($_GET['tanarId'],'id'));
	if ($_POST['tanarId']=='' && $tanarId!='') $_POST['tanarId'] = $tanarId; // H4CK 
	
	/* Képek vezérlésének beállítása */
	if (__SHOW_FACES=='optional') $ADAT['kepMutat'] = readVariable($_POST['kepMutat'],'bool');
	else $ADAT['kepMutat'] = __SHOW_FACES_TF;
	/* == */

	if (!isset($tanarId) && !isset($osztalyId) && __TANAR)
	if (!isset($tankorId)) $tanarId = __USERTANARID;
	else {
		$tankorTanarai = getTankorTanaraiByInterval($tankorId, array('tanev' => __TANEV, 'tolDt' => '', 'igDt' => '', 'result' => 'csakId'));
		if (in_array(__USERTANARID, $tankorTanarai)) $tanarId = __USERTANARID;
	}

	$nevsor = readVariable($_POST['nevsor'],'emptystringnull','aktualis',array('aktualis','teljes'));
	// tankörök lekérdzése
	if (isset($diakId)) $Tankorok = getTankorByDiakId($diakId, __TANEV);
	elseif (isset($osztalyId)) $Tankorok = getTankorByOsztalyId($osztalyId, __TANEV);
	elseif (isset($tanarId)) $Tankorok = getTankorByTanarId($tanarId, __TANEV);

	$ADAT['tankorok'] = $Tankorok;

	if (isset($tankorId)) {
		// Az aktuális szemeszter kiválasztása
		if (__FOLYO_TANEV) {
			foreach ($_TANEV['szemeszter'] as $szemeszter => $szemeszterAdat) {
			    if (strtotime($szemeszterAdat['kezdesDt']) <= $time && $time <= strtotime($szemeszterAdat['zarasDt'])) {
				break;
			    }
			}
			if ($szemeszter !== false) {
				define('__FOLYO_SZEMESZTER', $szemeszter);
				$szemeszterKezdesDt = $szemeszeterAdat['kezdesDt'];
				$szemeszterZarasDt = $szemeszeterAdat['zarasDt'];
			}
		}
		if (!defined('__FOLYO_SZEMESZTER')) define('__FOLYO_SZEMESZTER',false);

		if (isset($_POST['tolDt']) && $_POST['tolDt'] != '') $tolDt = readVariable($_POST['tolDt'],'date');
		elseif (isset($szemeszterKezdesDt)) $tolDt = $szemeszterKezdesDt;
		else $tolDt = $_TANEV['kezdesDt'];

		if (isset($_POST['igDt']) && $_POST['igDt'] != '') $igDt = readVariable($_POST['igDt'],'date');
		elseif (isset($szemeszterZarasDt)) $igDt = $szemeszterZarasDt;
		else $igDt = $_TANEV['zarasDt'];

		// a tankör diákjainak lekérdezése
		if ($nevsor == 'aktualis') $Diakok = getTankorDiakjaiByInterval($tankorId, __TANEV);
		else $Diakok = getTankorDiakjaiByInterval($tankorId, __TANEV, $tolDt, $igDt);

		$dts = array(date('Y-m-d'));
		foreach ($_TANEV['szemeszter'] as $szemeszter => $szAdat) $dts[] = $szAdat['zarasDt'];
		$ADAT['diakJogviszony'] = getDiakJogviszonyByDts($Diakok['idk'], $dts);

		// lehetne regexp-pel is: $sulyozas = readVariable($_POST['sulyozas'],'regexp',__DEFAULT_SULYOZAS,array('^[0-9]*:[0-9]*:[0-9]*:[0-9]*:[0-9]*$'));
		$ADAT['sulyozas'] = $sulyozas = readVariable($_POST['sulyozas'],'enum',__DEFAULT_SULYOZAS,$SULYOZAS);

		// tankör osztályzatainak lekérdezése
		$Jegyek = getJegyek($tankorId, $tolDt, $igDt, $sulyozas, $Diakok);
		if (is_array($Jegyek['tankörök'])) for ($j=0; $j<count($Jegyek['tankörök']['tankorId']); $j++) {
			$_tankorId = $Jegyek['tankörök']['tankorId'][$j];
			$tankorAdat = $Jegyek['tankörök'][$_tankorId];
			$Jegyek['tankörök'][$_tankorId]['tanarIds'] = array();
			for ($i = 0; $i < count($tankorAdat['tanárok']); $i++)
			$Jegyek['tankörök'][$_tankorId]['tanarIds'][] = $tankorAdat['tanárok'][$i]['tanarId'];
		}
		$Orak = getOraAdatByTankor($tankorId);
		$Dolgozatok = getTankorDolgozatok($tankorId);
		$TA = getTankorById($tankorId); // át kéne írni getTankorAdat() - ra...

		$ADAT['diakok'] = $Diakok; // kompatibilitás
		$ADAT['tankorAdat'] = $TA[0];
		$ADAT['tankorAdat2'] = getTankorAdat($tankorId);
		$targyId = $TA[0]['targyId'];
		$ADAT['tankorAdat']['osztalyai'] = getTankorOsztalyai($tankorId, array('result' => 'id'));
		// Diákok osztályai - folyó tanévben: jelenleg...
		if (__FOLYO_TANEV) {
		    $ADAT['diakOsztaly']['aktualis'] = getDiakokOsztalyai($ADAT['diakok']['idk'], array('result'=>'indexed'));
		    $dt['aktualis'] = date('Y-m-d');
		} else {
		    $ADAT['diakOsztaly']['aktualis'] = getDiakokOsztalyai($ADAT['diakok']['idk'], array('result'=>'indexed', 'tolDt' => $_TANEV['zarasDt'], 'igDt' => $_TANEV['zarasDt']));
		    $dt['aktualis'] = $_TANEV['zarasDt'];
		}
		// Kérdezzük le a szemeszter záráskori osztályokat is!
		foreach ($_TANEV['szemeszter'] as $szemeszter => $szAdat) {
		    $ADAT['diakOsztaly'][$szemeszter] = getDiakokOsztalyai(
			$ADAT['diakok']['idk'], array('result'=>'indexed', 'tolDt' => $szAdat['zarasDt'], 'igDt' => $szAdat['zarasDt'])
		    );
		    $dt[$szemeszter] = $szAdat['zarasDt'];
		}
		/* Diákonkénti évfolyam meghatározás 
		Külön kell itt is meghatározni, itt esetleg leszűkíthető a több évfolyamra járó diák évfolyama */
		for ($i=0; $i<count($ADAT['diakok']['idk']); $i++) {
		    $_diakId = $ADAT['diakok']['idk'][$i];
		    $_metszet = array();
		    // A jelenlegi és a szemeszter zárások idején levő évfolyamokat is megállapítjuk
		    foreach ($ADAT['diakOsztaly'] as $key => $value) { // $key: aktualis, 1, 2
			$_diakEvfolyam = array();
			$_diakEvfolyamJel = array();
			if ( is_array($ADAT['diakOsztaly'][$key][$_diakId]) && is_array($ADAT['tankorAdat']['osztalyai']) ) {
			    $_metszet = array_intersect($ADAT['diakOsztaly'][$key][$_diakId], $ADAT['tankorAdat']['osztalyai']);
//			} else {
//			    $_SESSION['alert'][] = 'info:nincs_osztaly:'.$ADAT['diakok']['nevek'][$_diakId]['diakNev'].':'.$key;
			}

			// ez a feltétel lazítható több osztályban is, ha azok azonos évolfymon vannak:
			foreach ( $_metszet as $j => $_osztalyId ) {
			    // csak ebben a tankörben ennek felel meg, ezen az évfolyamon levő a zárójegyet kaphatja:
			    $_diakEvfolyamJel[] = getEvfolyamJel($_osztalyId);
			    $_diakEvfolyam[] = evfolyamJel2Evfolyam(end($_diakEvfolyamJel));
			}
			if (count(array_unique($_diakEvfolyamJel))==1) { // korábban az evfolyam-nak kellett uniq-nak lennie - ez szigorúbb...
			    $ADAT['diakEvfolyam'][$key][$_diakId] = $_diakEvfolyam[0];
			    $ADAT['diakEvfolyamJel'][$key][$_diakId] = $_diakEvfolyamJel[0];
			    // OK, van-e meghatározva képzés?
			    // TODO: képzések legyenek evfolyamJel függők - ekkor itt is javítani kell!
			    if (is_numeric($key)) {
				$_kepzesIdk = getKepzesByDiakId($_diakId,array('result'=>'idonly', 'dt' => $dt[$key]));
				if (is_array($_kepzesIdk) && count($_kepzesIdk)>0) {
				    // ITT MIÉRT CSAK AZ ELSŐ KÉPZÉST NÉZZÜK??? - mert nem tudjuk eldönteni, hogy melyik az erősebb
				    $ADAT['diakKepzesKovetelmeny'][$key][$_diakId] = getTargyAdatFromKepzesOraterv($_kepzesIdk[0], 
					array('targyId'=>$targyId, 'evfolyam'=>$_diakEvfolyam[0], 'evfolyamJel'=>$_diakEvfolyamJel[0], 'szemeszter'=>$key));
				}
			    }
			    //
			} elseif (count($_diakEvfolyam) == 0) { // _Már_ nincs a tankör szerinti osztályok egyikében sem
			} else { // Több évfolyamban is bent van...
			    $_SESSION['alert'][] = "info:multi_evfolyam:".$ADAT['diakok']['nevek'][$_diakId]['diakNev'].':'.$key;
			}


		    }

		}
		/* --még1* felmentési célból- */
		for ($i=0; $i<count($ADAT['diakok']['idk']); $i++) {
		    $_diakId = $ADAT['diakok']['idk'][$i];
		    $ADAT['diakFelmentes'][$_diakId] = getTankorDiakFelmentes($_diakId,__TANEV,array('felmentesTipus'=>array('értékelés alól'),'csakId'=>true,'tolDt'=>date('Y-m-d'), 'igDt'=>date('Y-m-d')));
		}
	} else {
	    // -- TODO - fejlesztés alatt
	    /*for ($i=0; $i<count($ADAT['tankorok']); $i++) {
		$_tankorId=$ADAT['tankorok'][$i]['tankorId'];
		$_diakok = getTankorDiakjaiByInterval($_tankorId, __TANEV, $_TANEV['kezdesDt'], $_TANEV['zarasDt']);
		//$ADAT['atlagok'][$_tankorId] = ':..:';
	    }
	    */
	}

//------
	$tanevIdoszak = getIdoszakByTanev(
	    array('tanev' => __TANEV, 'tipus' => array('zárás','bizonyítvány írás'), 'tolDt' => date('Y-m-d H:i:s'), 'igDt' => date('Y-m-d H:i:s'), 
	    'arraymap'=>array('tipus','szemeszter'))
	);


	// Beírhat-e jegyet?
	define('__EVKOZI_JEGYET_ADHAT',
	    (__NAPLOADMIN && ($_TANEV['statusz'] == 'aktív'))
	    || (
		__TANAR 
		&& __FOLYO_TANEV
		&& is_array($Jegyek['tankörök'][$tankorId]['tanarIds'])
		&& in_array(__USERTANARID, $Jegyek['tankörök'][$tankorId]['tanarIds'])
	    )
	);
	define('__ZAROJEGYET_ADHAT',
	    (__NAPLOADMIN && ($_TANEV['statusz'] == 'aktív'))
	    || (
		__TANAR 
		&& is_array($Jegyek['tankörök'][$tankorId]['tanarIds'])
		&& in_array(__USERTANARID, $Jegyek['tankörök'][$tankorId]['tanarIds'])
		&& count($tanevIdoszak['zárás']) > 0
	    )
            || (
                __VEZETOSEG
                && !is_null($tanevIdoszak['bizonyítvány írás'])
                && is_null($tanevIdoszak['zárás'])
            )
	);

	if ( __TANAR 
		&& is_array($Jegyek['tankörök'][$tankorId]['tanarIds'])
		&& in_array(__USERTANARID, $Jegyek['tankörök'][$tankorId]['tanarIds'])
		&& count($tanevIdoszak['zárás']) > 0 )
	{
	    list($_szemeszter,$_idoszakok) = (each($tanevIdoszak['zárás'])); // --TODO 8.0
	    define('__IDOSZAK_TOLDT',$_idoszakok[0]['tolDt']); // Nem lehet két szemeszterhez tartozó ugyanolyan típusú (pl. zárás) időszak egyidőben!!!
	    define('__IDOSZAK_IGDT',$_idoszakok[0]['igDt']);
	    define('__IDOSZAK_SZEMESZTER',$_szemeszter);
	} elseif (
                __VEZETOSEG
                && !is_null($tanevIdoszak['bizonyítvány írás'])
                && is_null($tanevIdoszak['zárás'])
        ) {
	    list($_szemeszter,$_idoszakok) = (each($tanevIdoszak['bizonyítvány írás'])); // --TODO 8.0
	    define('__IDOSZAK_TOLDT',$_idoszakok[0]['tolDt']);
	    define('__IDOSZAK_IGDT',$_idoszakok[0]['igDt']);
	    define('__IDOSZAK_SZEMESZTER',$_szemeszter);
	}

	define('__JEGYET_ADHAT',false);

//------
	if ($action == 'jegyBeiras' && (isset($_POST['bizBeiroGomb']) || $_POST['bizBeiroGomb2']!='')) $action = 'jegyLezaras';
	if (__EVKOZI_JEGYET_ADHAT === true) {

		if ($action == 'jegyBeiras') {
			$actionId = readVariable($_POST['actionId'],'strictstring');
			for ($i = 0; $i < count($_POST['jegy']); $i++)
			if ($_POST['jegy'][$i] != '') {
				list($_diakId, $_jegyTipus, $_jegy) = explode('|', $_POST['jegy'][$i]);
				$Beirando[] = array('diakId' => $_diakId, 'jegy' => $_jegy, 'jegyTipus' => $_jegyTipus);
			} // for-if
			$oraId = readVariable($_POST['oraId'],'id','NULL'); // NULL string!!!
			$megjegyzes = readVariable($_POST['megjegyzes'],'string');

			// A jegy típusa - a submit alapján
			if (isset($_POST['jegy1'])) $tipus = 1;
			elseif (isset($_POST['jegy2'])) $tipus = 2;
			elseif (isset($_POST['jegy3'])) $tipus = 3;
			elseif (isset($_POST['jegy4'])) $tipus = 4;
			elseif (isset($_POST['jegy5'])) $tipus = 5;
			if ($tipus < 3 || $_POST['dolgozatId'] == '') $dolgozatId = 'NULL';
			elseif ($_POST['dolgozatId']=='uj') $dolgozatId='uj';	// uj-nak nevezzük a felveendő dolgozatot...
			else $dolgozatId = readVariable($_POST['dolgozatId'],'id','NULL'); // NULL string!

			if (isset($tipus) && count($Beirando) > 0) { // Nem csak súlyozást vagy nevsort változtatott és van jeg
				// --TODO: ez nem ide való
				$lr = db_connect('naplo');
				// Ellenőrizzük a reload-ot!!
				if (checkReloadAction($actionId, $action, 'jegy', $lr)) {
					if (jegyBeiras($tankorId, $tipus, $oraId, $dolgozatId, $tanarId, $megjegyzes, $Beirando, $actionId, $lr)) {
						$Jegyek = getJegyek($tankorId, $tolDt, $igDt, $sulyozas, $Diakok, $lr);
						if ($tipus > 2) $Dolgozatok = getTankorDolgozatok($tankorId);
					}
				} // reload
				db_close($lr);

			} // Nem csak súlyozást változtat
		}
	}
	if (__ZAROJEGYET_ADHAT === true) {
		if ($action == 'jegyLezaras') {
			$zaroJegyek = $_POST['zaroJegy'];
			$tankorAdat = getTankorById($tankorId);
			$targyId = $tankorAdat[0]['targyId'];
			$actionId = readVariable($_POST['actionId'],'strictstring');
			if (is_array($zaroJegyek)) {
			    /* Prepare */
			    for($i=0; $i<count($zaroJegyek); $i++) {
				$X = explode('|',$zaroJegyek[$i]);
				for ($j=0; $j<count($X); $j++) {
				    list($key,$value) = explode('=',$X[$j]);
				    // Van már ilyen jegy?
				    $beirandoJegyek[$i][$key] = $value;
				}
				
			    }
			    /* Időszak ellenőrzése */
				if (checkReloadAction($actionId, $action, 'bizonyitvany'))
				    if (jegyLezaras($beirandoJegyek, $tankorId, $targyId, $actionId))
					$Jegyek = getJegyek($tankorId, $tolDt, $igDt, $sulyozas, $Diakok);
			    //--
			}
		}
	} // jegyet adhat


	// És a zárójegyek
	$ADAT['zaroJegyek'] = getDiakokZarojegyeiByTargyId($Diakok['idk'],$ADAT['tankorAdat']['targyId'], array('arraymap'=>array('diakId','evfolyamStr','felev')));
	$ADAT['vizsgaJegyek'] = getDiakokVizsgajegyeiByTargyId($Diakok['idk'],$ADAT['tankorAdat']['targyId'], array('arraymap'=>array('diakId','evfolyamStr','felev')));
	$ADAT['idoszak'] = $tanevIdoszak;

	// ----------- action vége ------------- //

	$TOOL['tanarSelect'] = array('tipus' => 'cella', 'post' => array('tolDt', 'igDt'));
	$TOOL['osztalySelect'] = array('tipus' => 'cella', 'paramName' => 'osztalyId', 'post' => array('tolDt', 'igDt'));
	if (isset($osztalyId)) {
	    $TOOL['diakSelect'] = array('tipus' => 'cella', 'paramName' => 'diakId', 'post' => array('osztalyId', 'tolDt', 'igDt'));
	    $TOOL['oldalFlipper'] = array('tipus' => 'cella', 
		'url' => array('index.php?page=naplo&sub=osztalyozo&f=diak','index.php?page=naplo&sub=orarend&f=orarend'),
    		'title' => array('diák osztályzatai','diák órarendje'), 'post' => array('osztalyId'),
    		'paramName'=>'diakId');
//	    $TOOL['toolBarHamburger'] = array('tipus' => 'cella', 
//		'url' => array('index.php?page=naplo&sub=osztalyozo&f=diak','index.php?page=naplo&sub=orarend&f=orarend'),
//    		'title' => array('diák osztályzatai','diák órarendje'), 'post' => array('osztalyId'),
//    		'paramName'=>'diakId');
	}


	if (isset($osztalyId) or isset($tanarId) or isset($diakId))
	    $TOOL['tankorSelect'] = array('tipus' => 'sor', 'tankorok' => $Tankorok, 'paramName' => 'tankorId', 'post' => array('osztalyId', 'tanarId', 'diakId', 'tolDt', 'igDt'));
	if (isset($tankorId) && $skin != 'pda')
	    $TOOL['datumTolIgSelect'] = array('tipus' => 'sor', 'tolParamName' => 'tolDt', 'igParamName' => 'igDt',
	'tolDt' => $_TANEV['kezdesDt'], 'igDt' => $_TANEV['zarasDt'],
	'hanyNaponta' => 'havonta', 'post' => array('tanarId', 'osztalyId', 'tankorId', 'sulyozas'));


	getToolParameters();

} // rights

?>
