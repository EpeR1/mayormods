<?php

    if (_RIGHTS_OK !== true) die();
    if (!__NAPLOADMIN && !__VEZETOSEG) {

	$_SESSION['alert'][] = 'page:insufficient_access';

    } else {

	global $_TANEV;
        if ($_TANEV['statusz']!='aktív') $_SESSION['alert'][] = 'page:nem_aktív_tanev:'.$_TANEV['tanev'];

	$ADAT['dt']=$dt = readVariable($_POST['dt'], 'datetime', date('Y-m-d'));
	$ADAT['ora']=$ora = readVariable($_POST['ora'], 'numeric', null);
	$tanarId = readVariable($_POST['tanarId'], 'numeric unsigned', null);
	if (!isset($tanarId)) $osztalyId = readVariable($_POST['osztalyId'], 'id', null);
	$tankorId = readVariable($_POST['tankorId'], 'id', null);
	$blokkId = readVariable($_POST['tankorBlokkId'], 'id', null);
	$teremId = readVariable($_POST['teremId'], 'id', null);
	$SzabadOrak = '';

	require_once('include/modules/naplo/share/tanar.php');
	require_once('include/modules/naplo/share/osztaly.php');
        require_once('include/modules/naplo/share/tankor.php');
        require_once('include/modules/naplo/share/tankorBlokk.php');
        require_once('include/modules/naplo/share/ora.php');
        require_once('include/modules/naplo/share/oraModifier.php');
        require_once('include/modules/naplo/share/diak.php');
        require_once('include/modules/naplo/share/orarend.php');
        require_once('include/modules/naplo/share/munkakozosseg.php');
        require_once('include/share/date/names.php');

	$napiMinOra = getMinOra();
	$napiMaxOra = getMaxOra();

        if ($napiMaxOra < __MAXORA_MINIMUMA) $napiMaxOra = __MAXORA_MINIMUMA; 
	$ADAT['napiMinOra'] = $napiMinOra;
	$ADAT['napiMaxOra'] = $napiMaxOra;

	$ADAT['tanarok'] = getTanarok();
	$ADAT['feladatTipus'] = getFeladatTipus();

	if (isset($dt)) {
	    // órarendiÓrák betöltése - ha szükséges - a fv. maga ellenőrzi, hogy kell-e/lehet-e órákat betölteni...
	    checkNaplo($dt);

	    $ADAT['tankorBlokkok'] = getTankorBlokkok();

	    if (isset($tankorId)) {
		// A tankör tanárainak lekérdezése (tanarSelect számára)
		$Tanarok = getTankorTanaraiByInterval($tankorId, array('tanev' => __TANEV, 'tolDt' => $dt, 'igDt' => $dt, 'result' => 'nevsor'));
		if (isset($tanarId)) {
		    // A tanár a tankör tanára-e?
		    for ($i = 0; ($i < count($Tanarok) && $Tanarok[$i]['tanarId'] != $tanarId); $i++);
		    if ($i >= count($Tanarok)) {
			$_SESSION['alert'][] = 'info:not_member:pluszOra:Nem a tankör tanára:'."$tankorId/$tanarId";
			$Tanarok[] = array('tanarId' => $tanarId, 'tanarNev' => getTanarNevById($tanarId));
			$mkId = getTankorMkId($tankorId);
			$tanarMkIds = getTanarMunkakozosseg($tanarId);
			$ADAT['kit'] = $Tanarok[0]['tanarId'];
			if (in_array($mkId, $tanarMkIds)) $ADAT['tipus'] = 'helyettesítés';
			else $ADAT['tipus'] = 'felügyelet';
//			unset($tanarId); unset($_POST['tanarId']);
		    }
		}
		// Ha csak egy tanarId van, akkor azt állítsuk be! 
		if (count($Tanarok) == 1 && !isset($tanarId)) {
		    $tanarId = $Tanarok[0]['tanarId'];
		    if (isset($osztalyId)) unset($osztalyId);
		}
	    }
	    // Felvehető-e az óra
	    $ok = (isset($ora) && isset($tanarId) && isset($tankorId));

	    if (isset($tanarId)) {
		// tanar Napi órái
		$Orak = getTanarNapiOrak($tanarId, $dt);
		// Szabad Órák
		for ($i = $napiMinOra; $i <= $napiMaxOra; $i++) if (!is_array($Orak[$i])) $SzabadOrak[] = $i;
	    } elseif (isset($osztalyId)) {
		// osztalyNapiOrai
		$Orak = getOsztalyNapiOrak($osztalyId, $dt);
		// Szabad Órák
		for ($i = $napiMinOra; $i <= $napiMaxOra; $i++) if (!is_array($Orak[$i][0])) $SzabadOrak[] = $i;
	    }
	    if (isset($ora)) {
		$Termek = getSzabadTermek(array('dt' => $dt, 'ora' => $ora));
		$szabadTankorok = getSzabadTankorok($dt, $ora);
		if (isset($tankorId)) {
		    // Tankör tagok ütközés ellenőrzése
		    $TA = getTankorAdat($tankorId);
		    if ($TA[$tankorId][0]['jelenlet'] == 'kötelező' && !tankorTagokLukasOrajaE($tankorId, $dt, $ora)) {
			$ok = false;
		    }
		} elseif (!isset($osztalyId)) {
		    // Szabad tanárok lekérdezése? (tanarSelect számára)
		    $Tanarok = getSzabadTanarok($dt, $ora);
		}
		if (isset($tanarId)) {
		    // Tanár ütközés ellenőrzés
		    if (!tanarLukasOrajaE($tanarId, $dt, $ora, $lr)) {
			$_SESSION['alert'][] = 'message:utkozes:1:?:'.$dt.':'.$ora.':'.$tanarId;
//			unset($tanarId);			$ok = false;
		    }
		}
		if (isset($teremId)) {
		    for ($i = 0; ($i < count($Termek) && $Termek[$i]['teremId'] != $teremId); $i++);
		    if ($i >= count($Termek)) { // nincs a szabad termek között
			$_SESSION['alert'][] = 'message:wrong_data:pluszOra/terem:'.$ora.'. óra:'.$teremId;
			unset($teremId);
		    }
		}
	    } // ora
	} // dt

	// Action
	if ($ok && $action == 'oraFelvetele') {
		$eredet = $_POST['eredet'];
		if (isset($ADAT['tipus'])) $tipus = $ADAT['tipus']; 
		else $tipus = 'normál';
		if (oraFelvetele($dt, $ora, $tanarId, $tankorId, $teremId, $tipus, $eredet, $ADAT['kit'])) {
		    unset($_POST); 
		    $_POST['dt'] = $dt; $_POST['tanarId'] = $tanarId;
		    unset($ora); unset($teremId); unset($tankorId); 
		    $Orak = getTanarNapiOrak($tanarId, $dt);
		    $SzabadOrak = array(); 
		    for ($i = $napiMinOra; $i <= $napiMaxOra; $i++) if (!is_array($Orak[$i])) $SzabadOrak[] = $i;
		    $_SESSION['alert'][] = 'info:change_success';
		}
	} elseif ($action=='csoportos') {
	    $feladatTipusId = readVariable($_POST['feladatTipusId'],'id');
	    $leiras = readVariable($_POST['leiras'],'string');
	    $tanarIdk = readVariable($_POST['tanarIdk'],'id');
	    $lr = db_connect('naplo');
	    for ($i=0;$i<count($tanarIdk); $i++) {
		$_ki=$tanarIdk[$i];
		$_tipus='egyéb';
		$_eredet='órarend';
                    $UJORAIDK[] = ujOraFelvesz(
			array('dt'=>$dt,
			    'ora'=>$ora,
			    'ki'=> $_ki,
			    'tipus'=>$_tipus,
			    'eredet'=>$_eredet,
			    'leiras'=>$leiras,
			    'feladatTipusId'=>$feladatTipusId,
			    'munkaido'=>'fennmaradó'),
		    $lr);
	    }
	    db_close($lr);
	    $_SESSION['alert'][] = 'info:'.count($UJORAIDK).'db órát felvettem!';
	}

	$ADAT['munkakozossegek'] = getMunkakozossegek($FILTER=array(),$SET=array('result' => 'indexed'));
	$ADAT['mkTanar'] = getMunkakozossegTanaraMatrix();

	// toolBar
        $TOOL['datumSelect'] = array(
            'tipus' => 'cella', 'post' => array('ora', 'tanarId', 'osztalyId', 'tankorId'),
            'paramName' => 'dt', 'hanyNaponta' => 1,
            'tolDt' => $_TANEV['kezdesDt'], 'igDt' => $_TANEV['zarasDt'],
	    'napTipusok' => array('tanítási nap','speciális tanítási nap'),
        );
	if (isset($dt)) {
    	    $TOOL['oraSelect'] = array('tipus'=>'cella', 'orak' => $SzabadOrak, 'foglaltOrakkal' => true, 'post'=>array('osztalyId', 'tanarId', 'tankorId', 'dt', 'ora'));
    	    if (!isset($osztalyId) || isset($tankorId)) $TOOL['tanarSelect'] = array('tipus'=>'cella', 'tanarok' => $Tanarok, 'post' => array('dt', 'ora', 'tankorId', 'teremId' ));
    	    if (!isset($tanarId)) $TOOL['osztalySelect'] = array('tipus'=>'sor','paramName' => 'osztalyId', 'post'=>array('dt', 'ora', 'tankor', 'teremId'));
    	    if (isset($osztalyId) || isset($tanarId) || isset($ora)) {
        	$TOOL['tankorSelect'] = array('tipus'=>'sor', 'paramName'=>'tankorId', 'tankorok' => $szabadTankorok, 'tolDt'=>$dt, 'igDt'=>$dt, 'post'=>array('osztalyId','tanarId','dt','ora','teremId'));
	    }
    	    if (isset($ora)) $TOOL['teremSelect'] = array('tipus'=>'cella', 'paramName'=>'teremId', 'termek' => $Termek, 'post'=>array('osztalyId','tanarId','dt','ora','tankorId'));
	}

        getToolParameters();
    } // admin vagy igazgató

?>
