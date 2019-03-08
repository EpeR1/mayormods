<?php
/*
    Module: naplo
*/
    global $_TANEV;

    if (_RIGHTS_OK !== true) die();
    if (!__NAPLOADMIN && !__TANAR && !__DIAK && !__VEZETOSEG && !__TITKARSAG) {
	$_SESSION['alert'][] = 'message:insufficient_access';
    } else {

        require_once('include/modules/naplo/share/diak.php');
        require_once('include/modules/naplo/share/osztaly.php');
        require_once('include/modules/naplo/share/tanar.php');
        require_once('include/share/date/names.php');
	require_once('include/modules/naplo/share/kepzes.php');
        require_once('include/modules/naplo/share/tankor.php');
        require_once('include/modules/naplo/share/hianyzas.php');
        require_once('include/modules/naplo/share/hianyzasModifier.php');
        require_once('include/modules/naplo/share/szemeszter.php');
        require_once('include/modules/naplo/share/ora.php');
        require_once('include/modules/naplo/share/file.php');
        require_once('include/modules/naplo/share/orarend.php');
        require_once('include/modules/naplo/share/nap.php');

	require_once('skin/classic/module-naplo/html/share/hianyzas.phtml');

	$napiMinOra = getMinOra();
	$napiMaxOra = getMaxOra();

	if (__DIAK) {
	    $diakId = defined('__USERDIAKID') ? __USERDIAKID : null;
	} else {
	    $diakId = readVariable($_POST['diakId'], 'numeric unsigned', readVariable($_GET['diakId'], 'numeric unsigned'));
	    $osztalyId = readVariable($_POST['osztalyId'], 'numeric unsigned', readVariable($_GET['osztalyId'], 'numeric unsigned'));

    	    if (!isset($osztalyId) && !isset($diakId) && __TANAR && __OSZTALYFONOK) $osztalyId = $_OSZTALYA[0];
	    if ($diakId!='' && $osztalyId=='') {
		$OI = getDiakOsztalya($diakId, array('tolDt'=>date('Y-m-d'),'tanev'=>__TANEV) ); //--ITT a mai napot vesszük alapul, ami osztályváltáskor nem jó sajnos......
		$osztalyId = $OI[0]['osztalyId'];
	    }
	    if ($osztalyId != '') {
		$ADAT['osztalyId'] = $osztalyId;
		$ADAT['osztaly'] = getOsztalyAdat($osztalyId);
	    }
	}


if (isset($diakId) && $diakId != '') {

	// ez gyönyörű szép :)
	$_POST['diakId'] = $ADAT['diakId'] = $diakId;
	$_POST['osztalyId'] = $osztalyId;
	$ADAT['diak']['nev'] = getDiakNevById($diakId);

	/* --- igDt, tolDt --- */
	$tolDt = readVariable($_POST['tolDt'],'date',date('Y-m-d', mktime(0,0,0,date('m'),1,date('Y'))));

	$igDt = $_TANEV['zarasDt'];
	initTolIgDt(__TANEV, $tolDt, $igDt);
	// $ho beállítása
	if ($_tmp=readVariable($_POST['dt'],'date',readVariable($_GET['dt'],'date'))) {
	    $ho = intval(date('m',strtotime($_tmp)));
	} else {
	    $ho = readVariable($_POST['ho'],'numeric','',array(1,2,3,4,5,6,7,8,9,10,11,12));
	    if ($ho=='') $ho = readVariable($_GET['ho'],'numeric',
			    intval(date('m',strtotime($tolDt))),
			    array(1,2,3,4,5,6,7,8,9,10,11,12));
	}
	$ADAT['ho'] = $ho;
	/* --- */
        $DIAKOSZTALYAI = getDiakokOsztalyai(array($diakId),array('tanev'=>__TANEV,'tolDt'=>$tolDt,'igDt'=>$igDt)); // diakId => (o1, o2, ...)
	$munkatervIds = getMunkatervByOsztalyId($DIAKOSZTALYAI[$diakId], array('result'=>'idonly')); // lehet esetleg több is neki!
        define(__OFO, ( 
	    is_array($_OSZTALYA) && 
	    count(array_intersect($DIAKOSZTALYAI[$diakId],$_OSZTALYA)) > 0 ));
	if ($_TANEV['statusz']=='aktív' && (__NAPLOADMIN  ||  __OFO || __VEZETOSEG)) {    
	    $ITIPUSOK = getIgazolasTipusLista();
	    $ITIPUSOK['engedelyezett'][] = 'orvosi';
	    if (__NAPLOADMIN || __OFO) $ITIPUSOK['engedelyezett'][] = 'szülői';
	    if (__NAPLOADMIN || __OFO) $ITIPUSOK['engedelyezett'][] = 'osztályfőnöki';
	    if (__NAPLOADMIN || __OFO || __VEZETOSEG) $ITIPUSOK['engedelyezett'][] = 'verseny';
	    if (__NAPLOADMIN || __OFO || __VEZETOSEG) $ITIPUSOK['engedelyezett'][] = 'vizsga';
	    if (__NAPLOADMIN || __VEZETOSEG) $ITIPUSOK['engedelyezett'][] = 'igazgatói';
	    $ITIPUSOK['engedelyezett'][] = 'hatósági';
	    $ITIPUSOK['engedelyezett'][] = 'pályaválasztás';
	    $ADAT['igazolasTipusok']=$ITIPUSOK;	    

	    $igazolas = readVariable($_POST['igazolas'],'emptystringnull','',$ITIPUSOK['engedelyezett']);
	    if ($igazolas=='') $igazolas = readVariable($_GET['igazolas'],'emptystringnull','',$ITIPUSOK['engedelyezett']);
	    if ($igazolas=='') $igazolas = 'orvosi';
	    $_POST['igazolas'] = $igazolas;
	    //if (!in_array($igazolas,$ITIPUSOK['engedelyezett']))
		//$_SESSION['alert'][] = '::hibás beállítás('.$igazolas.')';

	    $ADAT['igazolas'] = $igazolas;

            // Hatarido
            if (__NAPLOADMIN || __VEZETOSEG) define('__BEIRAS_HATARIDO',_ZARAS_HATARIDO);
            elseif (__OFO) define('__BEIRAS_HATARIDO',_OFO_HIANYZAS_HATARIDO);
            else define('__BEIRAS_HATARIDO',_HIANYZAS_HATARIDO);

		//$hatarido = _LEGKORABBI_IGAZOLHATO_HIANYZAS;
		$hatarido = getNemIgazolhatoDt($diakId, $munkatervIds);

		if (__NAPLOADMIN || __VEZETOSEG) {
		    if (strtotime($hatarido) > strtotime(_ZARAS_HATARIDO)) 
			$hatarido = _ZARAS_HATARIDO;
		} elseif (!__OFO) {
		    $hatarido = date('Y-m-d');
		} // else nem írjuk felül

            define('__STATUS_HATARIDO',$hatarido);

	    // Beírások
	    /*
		a beíró függvényeknek átadjuk, hogy mit szeretnénk elérni,
		amik lekérdezik a szükséges adatokat, és esetleges jogosultságokat! (darabszám)
		a határidőre vonatkozó beállításokat a -pre nek kell végeznie!
	    */
	    if ($action == 'statusModositas') {

		$hianyzasId = readVariable($_GET['hianyzasId'],'id');
		if ($hianyzasId!='')
		    $_HDATA = getHianyzasById($hianyzasId);
		// lekérdezzük, hogy mi a hiányzás dátuma, státusza? ($dt,$statusz)
		if (is_array($_HDATA) && count($_HDATA)==1) {
		    if (strtotime(__STATUS_HATARIDO) < strtotime($_HDATA[0]['dt'])) {
    			if ($_HDATA[0]['statusz']=='igazolatlan') {
			    $_statusz = 'igazolt';
			    $_igtip = $igazolas;
			} else {
			    $_statusz = 'igazolatlan';
			    $_igtip = '';
			}
			$IGAZOLANDOK[] = array('id'=> $hianyzasId,'statusz'=>$_statusz,'igazolas'=>$_igtip);
			hianyzasIgazolas($IGAZOLANDOK,$diakId);
		    } else {
			$_SESSION['alert'][] = 'message:deadline_expired'.__STATUS_HATARIDO;
		    }
		} else { // nincs hianyzasId megadva, vagy nincs hozzá mégsem (pl már kitörölték egyszer) bejegyzés.

		    $_dt = readVariable($_GET['dt'],'date');
		    $_ora = readVariable($_GET['ora'],'numeric');
		    $_tipus = 'hiányzás';
		    $_statusz = readVariable($_POST['status'],'emptystringnull','igazolatlan',array('igazolt','igazolatlan'));

		    // ekkor vajon milyen oraId-jű orája volt a diakIdnek?
		    // esetleg több is! (regisztralando, de nem kötelező bejárnia)
		    if (strtotime(__BEIRAS_HATARIDO) <= strtotime($_dt)) {
			oraHianyzasBeiras($_dt,$_ora,$diakId,array('tipus'=>$_tipus,'statusz'=>$_statusz));
		    } else {
			$_SESSION['alert'][] = 'message:deadline_expired:'.__BEIRAS_HATARIDO;
		    }

		}

	    } elseif ($action == 'napiHianyzasBeiras') {
			
		// dt és diakId alapján lekérdezzük, beírjuk, nincs mese.
		// illetve van mese: lehet hogy csak a statuszokat lehet módosítani...

		$_dt = readVariable($_POST['dt'],'date');
		$_tipus = 'hiányzás';
		$_statusz = readVariable($_POST['status'],'emptystringnull','igazolatlan',array('igazolt','igazolatlan'));
		$_igazolas = ($_statusz=='igazolatlan') ? '' : $igazolas;
		napiHianyzasBeiras($_dt,$diakId,array('tipus'=>$_tipus,'statusz'=>$_statusz,'igazolas'=>$_igazolas));

	    } elseif ($action == 'igazolasTipusValtas') {
		// itt már elvileg nincs mit tennünk (v2.4)
	    }

	} else {
	    //define('__BEIRAS_HATARIDO','');
	    //define('__STATUS_HATARIDO','');
	}

	/* --- tanuló hiányzásai (figyelem! dátumok!) --- */
	// --TODO: 9!!!
	$ADAT['napok'] = getHonapNapjai($ho, $munkatervIds);
	for ($i=0; $i<count($ADAT['napok']); $i++)
	    $_NAPOK[] = $ADAT['napok'][$i]['dt'];
	$ADAT['hianyzasok'] = getHianyzasByDt(array($diakId),$_NAPOK);
	$ADAT['diak']['kepzes'] = getKepzesByDiakId($diakId, array('result'=>'assoc','dt'=>$_NAPOK[count($_NAPOK)-1])); // dátum nélkül ez a korábbi összes képzését adná. Vegyük a hónap utolsó napján...

	$ADAT['hianyzasKreta'] = getDiakKretaHianyzas($diakId,array('preprocess'=>'naptar'));
} // ha van nem üres diakId

	if (!__DIAK) {

    	    $TOOL['osztalySelect'] = array('tipus' => 'cella','paramName' => 'osztalyId', 'post' => array('ho','igazolas'));
	    $TOOL['diakSelect'] = array('tipus'=>'cella', 'diakId' => $diakId, 'post'=>array('tolDt','osztalyId','ho','igazolas'));
	    //$TOOL['tanarOraLapozo'] = array('tipus'=>'sor', 'oraId' => $oraId, 'post'=>array('tanarId'));

	}
    	if ($diakId != '') {
	    $TOOL['igazolasOsszegzo'] = array('tipus' => 'sor','paramName' => 'igazolasOsszegzo', 'post' => array());
    	    $TOOL['oldalFlipper'] = array('tipus' => 'cella', 
		'url' => array('index.php?page=naplo&sub=hianyzas&f=diakLista','index.php?page=naplo&sub=haladasi&f=stat&diakId='.$diakId),
        	'titleConst' => array('_DIAKHIANYZASLISTA','_HALADASISTATISZTIKA'),
                'post' => array('tanev','tolDt','igDt','ho','osztaly'),
                'paramName'=>'diakId');
        }
	if (__POLICY == 'parent') $TOOL['pageHelp'] = 'PAGEHELP_SZULO';
	else $TOOL['pageHelp'] = 'PAGEHELP';
	if ($ho!='') $TOOL['honapLapozo'] = array('tipus'=>'sor', 'paramName' => 'ho', 'ho'=>$ho, 
	    'diakId'=>$diakId,  // ideiglenesen!
	    'post'=>array('diakId','osztalyId','igazolas'));
	getToolParameters();

    }
		    
?>
