<?php

    if (_RIGHTS_OK !== true) die();
    
    define('_TIME',strtotime(date('Y-m-d')));

    if (
	!__NAPLOADMIN && !__VEZETOSEG && !__TANAR && !__TITKARSAG && !__DIAK
    ) {
	$_SESSION['alert'][] = 'page:insufficient_access';
    } else {
    
	require_once('include/share/date/names.php');
	require_once('include/modules/naplo/share/osztaly.php');
	require_once('include/modules/naplo/share/nap.php');
	require_once('include/modules/naplo/share/diak.php');
	require_once('include/modules/naplo/share/file.php');
	require_once('include/modules/naplo/share/targy.php');
	require_once('include/modules/naplo/share/intezmenyek.php');
	require_once('include/modules/naplo/share/szemeszter.php');
	require_once('include/modules/naplo/share/osztalyzatok.php');
	require_once('include/modules/naplo/share/vizsga.php');
	require_once('include/modules/naplo/share/zaradek.php');
	require_once('include/modules/naplo/share/zaroJegyModifier.php');
	require_once('include/modules/naplo/share/kepzes.php');
	require_once('include/modules/naplo/share/hianyzas.php');

	$ADAT['sorrendNev'] = $sorrendNev = readVariable($_POST['sorrendNev'], 'emptystringnull', 'bizonyítvány', getTargySorrendNevek(__TANEV));
        $ADAT['zaroJegyTipusok'] = getEnumField('naplo_intezmeny', 'zaroJegy', 'jegyTipus');

        $ADAT['magatartasIdk'] = getMagatartas();
	$ADAT['szorgalomIdk']= getSzorgalom();
	// Az összes tárgy kell az előmenetel megjelenítéséhez...
	$tmp = getTargyak();
	// reindex
	$ADAT['targyak'] = array();
	for ($i=0; $i<count($tmp); $i++) {
	    $ADAT['targyak'][$tmp[$i]['targyId']] = $tmp[$i];
	}

	// Melyik osztály diákjait nézzük
	$ADAT['osztalyId'] = $osztalyId = readVariable($_POST['osztalyId'], 'id');
	if (__OSZTALYFONOK && !isset($osztalyId)) $_POST['osztalyId'] = $ADAT['osztalyId'] = $osztalyId = $_OSZTALYA[0];
	// melyik szemeszter adatait nézzük	
	$szemeszterId = readVariable($_POST['szemeszterId'],'id',null);
	if (!is_null($szemeszterId)) {
	    //$_TANEV2 = getTanevAdat($szemeszterId);
	    $_TANEV2 = getTanevAdatBySzemeszterId($szemeszterId); // itt volt egy TYPO
	    for ($i = 1; $i <= count($_TANEV2['szemeszter']); $i++) { // aktuális tanév szemeszter számai alapján... (???)
		if (
		    strtotime($_TANEV2['szemeszter'][$i]['kezdesDt']) <= _TIME
		    && strtotime($_TANEV2['szemeszter'][$i]['zarasDt']) >= _TIME
		) {
		    $szemeszterId = $_POST['szemeszterId'] = $_TANEV2['szemeszter'][$i]['szemeszterId'];
		    $tanev = $_TANEV2['szemeszter'][$i]['tanev'];
		    $szemeszter = $_TANEV2['szemeszter'][$i]['szemeszter'];
		    break;
		}
	    }
	}

	if (!is_null($szemeszterId)) $ADAT['szemeszterAdat'] = getSzemeszterAdatById($szemeszterId);

    	if (__DIAK) { // diák / szülő csak a saját adatait nézheti
	    $diakId = __USERDIAKID;
	} elseif (isset($_POST['diakId']) && $_POST['diakId'] != '') {
	    $diakId = readVariable($_POST['diakId'],'numeric');
	}

	if (!is_null($diakId)) {
	    // intézmlényi adatok lekérdezése
	    $ADAT['intezmeny'] = getIntezmenyByRovidnev(__INTEZMENY);
	    // diák adatai
	    $ADAT['diakAdat'] = getDiakAdatById($diakId);
	    $ADAT['diakKepzes'] = getKepzesByDiakId($diakId, array('result'=>'indexed')); // itt a valaha volt összes képzés lekérdezésre kerül!
	    if (count($ADAT['diakKepzes'])===1) { /* egyelőre csak ha egy képzésben vesz részt a diák */
		$ADAT['kepzesOraterv'] = getKepzesOraterv($ADAT['diakKepzes'][0]['kepzesId'],array('arraymap'=>array('targyId','evfolyam','szemeszter')));
	    }

	    if (isset($szemeszterId)) {

		define('__MODOSITHAT',($ADAT['szemeszterAdat']['statusz'] == 'aktív' && __NAPLOADMIN));
		define('__ZARO_SZEMESZTER', $ADAT['szemeszterAdat']['szemeszter'] == $ADAT['szemeszterAdat']['tanevAdat']['maxSzemeszter']);
		define('__FOLYO_SZEMESZTER',
		    strtotime($ADAT['szemeszterAdat']['kezdesDt']) <= _TIME
		    && strtotime($ADAT['szemeszterAdat']['zarasDt']) >= _TIME
		);
		// ---- action ---- //
		if (__MODOSITHAT) {
		    $evfolyam = readVariable($_POST['evfolyam'],'numeric',null);
		    $targyId = readVariable($_POST['targyId'],'id',null);
		    if ($action == 'zaroJegyModositas' && isset($_POST['targyId']) && $_POST['targyId'] != '') {
			// jogviszony ellenőrzés, 
			// majd beírás:
			for ($i=0; $i<count($_POST['zaroJegy']); $i++) {
			    $X = explode('|',$_POST['zaroJegy'][$i]);
			    for ($j=0; $j<count($X); $j++) { 
				list($_key,$_val) = explode('=',$X[$j]);
				$_JEGYEK[$i][$_key] = $_val;
			    }
			    if ($_JEGYEK[$i]['evfolyam']=='') $_JEGYEK[$i]['evfolyam'] = $evfolyam;
			    $_JEGYEK[$i]['targyId'] = $targyId;
			}
			zaroJegyBeiras($_JEGYEK);
		    }
		}
		// ---- action ---- //

		// diák osztályai
		$ADAT['diakAdat']['osztaly'] = getDiakOsztalya(($diakId), array('tanev' => $ADAT['szemeszterAdat']['tanev']));
		/* 
		    A tanítási hetek számát nem tankörönként vagy tárgyanként nézzük, hanem egységesen - tehát, ha több osztálya
		    van a tanulónak és azokban eltérő a tanítási hetek száma, akkor úgy is hibás lesz a mutatott kép.
		*/
		if (!isset($ADAT['osztalyId'])) $ADAT['osztalyId'] = intval($ADAT['diakAdat']['osztaly'][0]['osztalyId']);
		// A tanítási hetek száma csak az aktuális évben kérdezhető le (az osztalyJelleg alaján döntjük el, hogy végzős/érettségiző-e
		if ($ADAT['szemeszterAdat']['tanev'] == __TANEV)
			define('TANITASI_HETEK_SZAMA', getTanitasiHetekSzama(array('osztalyId'=>$ADAT['osztalyId'])));
		/* Évfolyam meghatározás ha lehet
		    Peremfeltétel: adott legyen a szemeszter
		*/
		$ADAT['diakOsztaly'] = getDiakokOsztalyai(array($diakId), array('tanev' => $ADAT['szemeszterAdat']['tanev']));
    		for($j=0; $j<count($ADAT['diakOsztaly'][$diakId]); $j++) {
            	    $ADAT['diakEvfolyam'][] = getEvfolyam($ADAT['diakOsztaly'][$diakId][$j],$ADAT['szemeszterAdat']['tanev']);
            	    $ADAT['diakEvfolyamJel'][] = getEvfolyamJel($ADAT['diakOsztaly'][$diakId][$j],$ADAT['szemeszterAdat']['tanev']);
        	}
		// Ha vendégtanuló, akkor lehet, hogy nincs osztálya --> nincs évfolyama sem
		if (!is_array($ADAT['diakEvfolyam']) || count($ADAT['diakEvfolyam'])==0) $ADAT['diakEvfolyam'] = $ADAT['diakEvfolyamJel'] = range(1,16);

		// diák  adott szemeszterének bizonyítványa
		$ADAT['bizonyitvany'] = getDiakBizonyitvany($diakId, $ADAT);
		$ADAT['hianyzas'] = getHianyzasOsszesitesByDiakId($diakId, $ADAT['szemeszterAdat']);
	    } else {
		// diák eddigi "pályafutása" (több szemeszter adatai együtt)
		$ADAT['bizonyitvany'] = getDiakBizonyitvany($diakId, $ADAT);
		$ADAT['hianyzas'] = getHianyzasOsszesitesByDiakId($diakId);
	    }

	    $_VIZSGA = getVizsgak(array('diakId'=>$diakId));
	    /* REINDEX */
	    for ($i=0; $i<count($_VIZSGA); $i++) {
		$ADAT['zaroJegyVizsga'][$_VIZSGA[$i]['zaroJegyId']]=$_VIZSGA[$i];
	    }
	}

	$TOOL['szemeszterSelect'] = array('tipus'=>'cella','paramName' => 'szemeszterId', 'statusz' => array('aktív','lezárt') , 'post' => array('osztalyId', 'diakId', 'sorrendNev'));
	if (!__DIAK) {
	    $TOOL['osztalySelect'] = array('tipus'=>'cella','paramName' => 'osztalyId', 'post' => array('szemeszterId', 'sorrendNev'));
	    $TOOL['diakSelect'] = array(
		'tipus'=>'sor','paramName' => 'diakId',
		'osztalyId'=> $osztalyId,'post' => array('osztalyId','szemeszterId', 'sorrendNev'),
		'statusz' => array('vendégtanuló','jogviszonyban van','magántanuló','jogviszonya felfüggesztve','jogviszonya lezárva')
	    );
	    if (isset($diakId)) {
		$TOOL['diakLapozo'] = array(
		'tipus'=>'sor','paramName' => 'diakId',
		'osztalyId'=> $osztalyId,'post' => array('osztalyId','szemeszterId', 'sorrendNev'),
		'statusz' => array('vendégtanuló','jogviszonyban van','magántanuló','jogviszonya felfüggesztve','jogviszonya lezárva')
		);
	    }
	}
	$TOOL['targySorrendSelect'] = array('tipus'=>'cella','paramName' => 'sorrendNev', 'post' => array('szemeszterId', 'osztalyId', 'diakId'));
	getToolParameters();
    
    }
?>
