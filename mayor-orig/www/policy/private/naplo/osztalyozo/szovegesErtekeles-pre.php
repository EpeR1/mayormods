<?php

    if (_RIGHTS_OK !== true) die();
    if (!__NAPLOADMIN && !__TANAR && !__DIAK) {
	$_SESSION['alert'][] = 'page:insufficien_access';
    } else {
//!!
//Ha van szemeszterId, akkor az annak megfelelő évfolyamot kell kideríteni, és aszerinti szempontrendszert lekérdezni.
//ld lentebb: getEvfolyamJelByOsztalyId
//!!
	require_once('include/modules/naplo/share/tanar.php');
	require_once('include/modules/naplo/share/osztaly.php');
	require_once('include/modules/naplo/share/tankor.php');
	require_once('include/modules/naplo/share/diak.php');
	require_once('include/modules/naplo/share/szovegesErtekeles.php');
	require_once('include/modules/naplo/share/kepzes.php');
	require_once('include/modules/naplo/share/file.php');
	require_once('include/modules/naplo/share/targy.php');
	require_once('include/modules/naplo/share/szemeszter.php');
	require_once('include/share/date/names.php');


	$ADAT['targySorrendNev'] = $sorrendNev = readVariable($_POST['sorrendNev'], 'enum', 'bizonyítvány', array('napló','bizonyítvány','anyakönyv','ellenőrző','egyedi'));
	//$aktDt = date('Y-m-d');
	$now = date('Y-m-d H:i:s');
	// Feltételezzük, hogy a zárás időszak a szemeszteren belül van!
        $ADAT['idoszak'] = getIdoszakByTanev(array('tanev' => __TANEV, 'tolDt' => $now, 'igDt' => $now, 'tipus' => array('zárás')));
	// Ha nincs kiválasztva szemeszter, de épp zárási időszak van, akkor legyen kiválasztva az aktuális szemeszter
	if (!isset($_POST['szemeszterId']) && is_array($ADAT['idoszak']) && count($ADAT['idoszak']) > 0) {
	    $ADAT['szemeszterId'] = $szemeszterId = $_POST['szemeszterId'] = getSzemeszterIdBySzemeszter($ADAT['idoszak'][0]['tanev'], $ADAT['idoszak'][0]['szemeszter']);
	} else {
	    $ADAT['szemeszterId'] = $szemeszterId = readVariable($_POST['szemeszterId'], 'numeric unsigned', null);
	}
	if (is_null($szemeszterId)) {
	    $_tanev = readVariable($_POST['tanev'], 'numeric unsigned', null);
	    $_szemeszter = readVariable($_POST['szemeszter'], 'numeric unsigned', null);
	    if (!is_null($_tanev) && !is_null($_szemeszter)) {
		$ADAT['szemeszterId'] = $szemeszterId = $_POST['szemeszterId'] = getSzemeszterIdBySzemeszter($_tanev, $_szemeszter);
	    }
	}

	if (isset($szemeszterId)) { // szemesztert záró értékelés - intézményi adatbázis
	    $ADAT['szemeszter'] = getSzemeszterAdatById($ADAT['szemeszterId']);
	    // Annak eldöntése, hogy _MOST_ az adott szemeszter zárási időszaka van-e - ezért itt nem kell a $dt-t módosítani. Ez átkerült alább...
	    // $ADAT['dt'] = $dt = $ADAT['szemeszter']['zarasDt'];
	    // zárási időszak-e
	    $zarasIdoszak = false;
	    for ($i = 0; $i < count($ADAT['szemeszter']['idoszak']); $i++) {
		$ISz = $ADAT['szemeszter']['idoszak'][$i];
		if ($ISz['tipus'] == 'zárás' && strtotime($ISz['tolDt']) <= strtotime($now) && strtotime($now) <= strtotime($ISz['igDt'])) {
		    $zarasIdoszak = true;
		    break;
		}
	    }
	    // Gondolom ez a dátum az értékelés hivatalos dátuma lesz. Itt jogos a zarasDt-re állítás....
	    $ADAT['dt'] = $dt = $ADAT['szemeszter']['zarasDt'];
	} else { // évközi értékelés - tanáv adatbázis
	    $ADAT['dt'] = $dt = readVariable($_POST['dt'], 'datetime', '');
	    if (!isset($dt)) $ADAT['dt'] = $dt = date('Y-m-d');
	    $ADAT['szemeszter'] = getSzemeszterByDt($ADAT['dt']);
//	    $ADAT['tolDt'] = $tolDt = readVariable($_POST['tolDt'], 'datetime', $_TANEV['kezdesDt']); // Ezt nem is használjuk!!!
	}
	$tanev = readVariable($ADAT['szemeszter']['tanev'], 'numeric unsigned', __TANEV);

	if (__DIAK) {
	    $ADAT['diakId'] = $diakId = __USERDIAKID;
	} else {
	    $ADAT['diakId'] = $diakId = readVariable($_POST['diakId'], 'numeric unsigned', null);
	    $ADAT['osztalyId'] = $osztalyId = readVariable($_POST['osztalyId'], 'numeric unsigned', null);
	    $ADAT['tanarId'] = $tanarId = readVariable($_POST['tanarId'], 'numeric unsigned', null);
	    if (!isset($osztalyId) && !isset($tanarId) && __TANAR && $_POST['tanarId'] !== '') $ADAT['tanarId'] = $tanarId = __USERTANARID;
		if (isset($tanarId)) $_POST['tanarId'] = $tanarId;
	}
        if (isset($diakId)) {
	    $Tankorok = getTankorByDiakId($diakId, $tanev);
	    $Targyak = getTargyakByDiakId($diakId, array('tanev' => $tanev, 'result' => 'indexed'));
	    $targyIds = array(); foreach ($Targyak as $key => $val) $targyIds[] = $val['targyId'];
	    $ADAT['targyId'] = $targyId = readVariable($_POST['targyId'], 'numeric unsigned', null, $targyIds);
	    if (isset($targyId)) {
		$magatartasId = getMagatartas(array('result'=>'value'));
		$szorgalomId = getSzorgalom(array('result' => 'value'));
		$ADAT['targyTankorei'] = array();
		foreach ($Tankorok as $key => $tAdat) {
		    if ($tAdat['targyId'] == $targyId) {
			$ADAT['targyTankorei'][] = $tAdat['tankorId'];
		    }
		}
	    }
        } elseif (isset($osztalyId)) { 
	    $Tankorok = getTankorByOsztalyId($osztalyId, $tanev); 
        } elseif (isset($tanarId)) { 
	    $Tankorok = getTankorByTanarId($tanarId, $tanev); 
	}

	$tankorIds = array();
	if (is_array($Tankorok)) for ($i = 0; $i < count($Tankorok); $i++) $tankorIds[] = $Tankorok[$i]['tankorId'];

	$ADAT['tankorId'] = $tankorId = readVariable($_POST['tankorId'], 'numeric unsigned', null, $tankorIds);
	if (isset($tankorId)) {
	    $D = getTankorDiakjaiByInterval($tankorId, $tanev, $dt, $dt);
	    $Diakok = array();
	    foreach ($D['nevek'] as $_diakId => $dAdat) $Diakok[] = $dAdat;
	    if (isset($diakId) && !in_array($diakId, $D['idk'])) { unset($diakId); unset($ADAT['diakId']); }
	}

	// Az értékelési szempontrendszer lekérdezése
	if (isset($diakId) && isset($dt)) {
	    // Az értékeléshez kell: évfolyam [ képzésId ] (targyId | targyTipus) [ tanev, szemeszter ]
	    $diakOsztaly = getDiakOsztalya($diakId, array('tanev'=>$tanev,'tolDt'=> $dt,'igDt'=> $dt, 'result'=>'csakid'));
	    if (is_array($diakOsztaly) && count($diakOsztaly) > 0) {
		// évfolyam
		$diakEvfolyamJel = array();
		for ($i = 0; $i < count($diakOsztaly); $i++) {
		    $evf = getEvfolyamJel($diakOsztaly[$i], $tanev);
		    if (!in_array($evf, $diakEvfolyamJel)) $diakEvfolyamJel[] = $evf;
		    foreach (getOsztalyfonokok($diakOsztaly[$i], $tanev) as $key => $oAdat)
			if ($oAdat['aktiv']) $ADAT['diakOsztalyfonokei'][] = $oAdat['tanarId'];
		}
		if (count($diakEvfolyamJel) == 1) $ADAT['evfolyamJel'] = $evfolyamJel = $diakEvfolyamJel[0];
		else $ADAT['evfolyamJel'] = $evfolyamJel = readVariable($_POST['evfolyamJel'], 'enum', null, $diakEvfolyamJel);
		// képzés
		$diakKepzesIds = getKepzesByDiakId($diakId, array('result' => 'csakid', 'dt' => $dt));
		if (is_array($diakKepzesIds))
		    if (count($diakKepzesIds) == 1) $ADAT['kepzesId'] = $kepzesId = $diakKepzesIds[0];
		    else $ADAT['kepzesId'] = $kepzesId = readVariable($_POST['kepzesId'], 'numeric unsigned', null, $diakKepzesIds);
	    }
	    $ADAT['diakTargyak'] = getTargyakByDiakId($diakId, array('tanev' => $tanev, 'dt' => $dt, 'result' => 'assoc'));

	    if (isset($tankorId)) {
		// Jogosultsághoz
		$tankorTanarIds = getTankorTanaraiByInterval($tankorId, array('tanev' => $tanev, 'tolDt' => $dt, 'igDt' => $dt, 'result' => 'idonly'));
		$tankorAdat = getTankorAdat($tankorId);
		// targyId
		$ADAT['targyId'] = $tankorAdat[$tankorId][0]['targyId'];
	
	    } elseif (isset($targyId)) {
		$tankorTanarIds = getTankorTanaraiByInterval($ADAT['targyTankorei'], array('tanev' => $tanev, 'tolDt' => $dt, 'igDt' => $dt, 'result' => 'idonly'));
		// magatartás és szorgalom értékelse az osztályfőnökkel mehet
		if ($targyId == $magatartasId || $targyId == $szorgalomId) {
		    foreach ($ADAT['diakOsztalyfonokei'] as $key => $id) $tankorTanarIds[] = $id;
		}
	    }

	    if (isset($ADAT['targyId'])) {
		// Módosíthatja az értékelést:
		// A naplóadmin bármikor
		// A tankör tanára (v. ofő magatartás/szorgalom esetén), ha évközi értékelés, vagy záró értékelés és a szemeszter zárási időszakában vagyunk
		define(__MODOSITHAT, 
		    __NAPLOADMIN
		    || (
			__VEZETOSEG
			// itt kellene, hogy csak bizonyítvány írás időszakban...
		    )
		    || (
			__TANAR && is_array($tankorTanarIds) && in_array(__USERTANARID, $tankorTanarIds)
			&& (!isset($ADAT['szemeszterId']) || $zarasIdoszak) 
			&& $ADAT['szemeszter']['statusz'] == 'aktív'
		    )
		);
		// ?? targyTipus ??
		$ADAT['szempontRendszer'] = getSzempontRendszer($ADAT);
		if (is_array($ADAT['szempontRendszer'])) {
		    $szrId = $ADAT['szempontRendszer']['szrId'];
		    // Kérdezzük le a diák utolsó értékelését - ha van
		    if (isset($szemeszterId)) $ADAT['szovegesErtekeles'] = getDiakSzovegesTargyZaroErtekeles($diakId, $szrId, $ADAT['targyId'], $tanev, $ADAT['szemeszter']['szemeszter']);
		    else $ADAT['szovegesErtekeles'] = getDiakUtolsoSzovegesTargyErtekeles($diakId, $szrId, $ADAT['targyId'], $dt);

		    if (__MODOSITHAT && $action == 'ujErtekeles') {
			for ($i = 0; $i < count($_POST['egyediMinosites']); $i++) {
			    $tmp = readVariable($_POST['egyediMinosites'][$i], 'string', null);
			    if ($tmp != '') {
				$szempontId = readVariable($_POST['szempontId'][$i], 'numeric unsigned', null, $ADAT['szempontRendszer']['szempontIds']);
				if (isset($szempontId)) $egyediMinosites[$szempontId] = $tmp;
			    }
			}
			for ($i = 0; $i < count($_POST['minosites']); $i++) {
			    $tmp = readVariable($_POST['minosites'][$i], 'numeric unsigned', null, $ADAT['szempontRendszer']['minositesIds']);
			    if (isset($tmp)) $minosites[] = $tmp;
			}
			if (isset($szemeszterId)) {
			    // Az értékelések újraolvasása
			    ujZaroErtekeles($diakId, $szrId, $ADAT['targyId'], $tanev, $ADAT['szemeszter']['szemeszter'], $minosites, $egyediMinosites);
			    $ADAT['szovegesErtekeles'] = getDiakSzovegesTargyZaroErtekeles($diakId, $szrId, $ADAT['targyId'], $tanev, $ADAT['szemeszter']['szemeszter']);
			} else {
			    // Az új értékelés rögzítése
			    if (ujErtekeles($diakId, $szrId, $ADAT['targyId'], date('Y-m-d'), $minosites, $egyediMinosites))
				// Az értékelések újraolvasása
				$ADAT['szovegesErtekeles'] = getDiakUtolsoSzovegesTargyErtekeles($diakId, $szrId, $ADAT['targyId'], $dt);
			}
		    }
		} else {
		    $_SESSION['alert'][] = 'info:nincs_szempontrendszer';
		}
	    } else { // Egy diák összes értékelése
		define(__MODOSITHAT, false);
		$ADAT['osszes'] = getDiakOsszesSzovegesErtekeles($ADAT);
	    }
	}

/* **** ToolBar **** */

	// tanár vagy osztály szerint szűkíthetünk, majd diákot választunk
	if (__TANAR || __NAPLOADMIN) {
   	    if (!isset($osztalyId) || __OSZTALYFONOK === true)
    		$TOOL['tanarSelect'] = array('tipus' => 'cella', 'post' => array('tolDt', 'dt', 'szemeszterId'));
    	    if (!isset($tanarId) || __OSZTALYFONOK === true)
			$TOOL['osztalySelect'] = array('tipus' => 'cella', 'paramName' => 'osztalyId', 'post' => array('tolDt', 'tolDt', 'dt', 'szemeszterId'));
    	    $TOOL['diakSelect'] = array(
			'tipus' => 'sor', 'paramName' => 'diakId', 'diakok' => $Diakok, 
			'post' => array('osztalyId', 'targyId', 'tankorId', 'tanarId', 'tolDt', 'dt', 'szemeszterId')
	    );
    	    if (isset($diakId)) $TOOL['diakLapozo'] = array( 'withSelect' => false,
			'tipus' => 'sor', 'paramName' => 'diakId', 'diakok' => $Diakok, 
			'post' => array('osztalyId', 'targyId', 'tankorId', 'tanarId', 'tolDt', 'dt', 'szemeszterId')
	    );
	}
	// Záró vagy évközi értékelés - dt vagy szemeszterId van kiválasztva
	if (!isset($szemeszterId)) {
	    $TOOL['datumSelect'] = array(
		'tipus' => 'cella', 'paramName' => 'dt', 'tolDt' => $_TANEV['kezdesDt'], 'igDt' => $_TANEV['zarasDt'], 'hanyNaponta' => 1,
		'post' => array('tankorId','tanarId','osztalyId','diakId','szemeszterId'));
	}
	if ($dt == $ADAT['szemeszter']['zarasDt']) {
	    $TOOL['szemeszterSelect'] = array(
		'tipus'=>'cella','paramName' => 'szemeszterId', 'statusz' => array('aktív','lezárt') , 
		'post' => array('osztalyId', 'tanarId', 'diakId', 'dt', 'tankorId', 'kepzesId', 'evfolyamJel'));
	}
	// tárgyat vagy tankört választunk (és ezen keresztül tárgyat)
        if (!isset($targyId) && (isset($osztalyId) || isset($tanarId) || isset($diakId))) {
    	    $TOOL['tankorSelect'] = array(
		'tipus' => 'sor', 'tankorok' => $Tankorok, 'paramName' => 'tankorId', 
		'post' => array('osztalyId', 'tanarId', 'diakId', 'dt', 'szemeszterId')
	    );
	}
        if (!isset($tankorId) && isset($diakId)) {
	    $TOOL['targySelect'] = array(
		'tipus' => 'sor', 'targyak' => $Targyak, 'paramName' => 'targyId', 
		'post' => array('osztalyId', 'tanarId', 'diakId', 'dt', 'szemeszterId')
	    );
	}
	if (is_array($diakEvfolyamJel) && count($diakEvfolyamJel) > 1)
	    $TOOL['evfolyamJelSelect'] = array(
		'tipus' => 'cella', 'evfolyamJel' => $diakEvfolyamJel, 'paramName' => 'evfolyamJel', 
		'post' => array('osztalyId', 'tanarId', 'diakId', 'dt', 'tankorId', 'kepzesId', 'szemeszterId')
	    );
	if (is_array($diakKepzesIds) && count($diakKepzesIds) > 1)
	    $TOOL['kepzesSelect'] = array(
		'tipus' => 'cella', 'paramName' => 'kepzesId', 
		'post' => array('osztalyId', 'tanarId', 'diakId', 'dt', 'tankorId', 'evfolyamJel', 'szemeszterId')
	    );
        $TOOL['targySorrendSelect'] = array('tipus'=>'cella','paramName' => 'sorrendNev', 'post' => array('szemeszterId', 'osztalyId','diakId','tolDt','dt','tankorId','kepzesId','evfolyamJel'));
	$TOOL['nyomtatasGomb'] = array('titleConst' => '_NYOMTATAS','tipus'=>'cella','url'=>'index.php?page=naplo&sub=nyomtatas&f=szovegesErtekeles',
	'post' => array('osztalyId','szemeszterId','sorrendNev'));
	getToolParameters();

    }

?>
