<?php

    if (_RIGHTS_OK !== true) die();
    if (!__NAPLOADMIN && !__VEZETOSEG) {

	$_SESSION['alert'][] = 'page:insufficient_access';

    } else {

	global $_TANEV;
        if ($_TANEV['statusz']!='aktív') $_SESSION['alert'][] = 'page:nem_aktív_tanev:'.$_TANEV['tanev'];

	$ADAT['dt'] = $dt = readVariable($_POST['dt'], 'datetime', date('Y-m-d'));
//	$ora = readVariable($_POST['ora'], 'numeric unsigned', null);
	$ADAT['tanarId'] = $tanarId = readVariable($_POST['tanarId'], 'numeric unsigned', null);
	if (!isset($tanarId)) $ADAT['osztalyId'] = $osztalyId = readVariable($_POST['osztalyId'], 'numeric unsigned', null);
//	$tankorId = readVariable($_POST['tankorId'], 'numeric unsigned', null);
//	$teremId = readVariable($_POST['teremId'], 'numeric unsigned', null);
//	$SzabadOrak = '';

	require_once('include/modules/naplo/share/tanar.php');
	require_once('include/modules/naplo/share/osztaly.php');
        require_once('include/modules/naplo/share/tankor.php');
        require_once('include/modules/naplo/share/ora.php');
        require_once('include/modules/naplo/share/helyettesitesModifier.php');
        require_once('include/modules/naplo/share/diak.php');
        require_once('include/modules/naplo/share/orarend.php');
        require_once('include/share/date/names.php');
	
	$ADAT['napiMinOra'] = getMinOra();
	$ADAT['napiMaxOra'] = getMaxOra();

	if (defined('__MAXORA_MINIMUMA') && $ADAT['napiMaxOra'] < __MAXORA_MINIMUMA) $ADAT['napiMaxOra'] = __MAXORA_MINIMUMA; // Hogy lehessen "törölni" későbbi órákat is
	if (isset($dt)) {

	    // órarendiÓrák betöltése - ha szükséges - a fv. maga ellenőrzi, hogy kell-e betölteni adatokat...
	    checkNaplo($dt);

	    // órák lekérdezése
	    if (isset($tanarId)) $ADAT['orak'] = getTanarNapiOrak($tanarId, $dt);
	    elseif (isset($osztalyId)) $ADAT['orak'] = getOsztalyNapiOrak($osztalyId, $dt);

	    // Action
	    if ($action == 'oraElmaradas') {

		if (is_array($_POST['oraId'])) {
		    for ($i = 0; $i < count($_POST['oraId']); $i++) {
			$oraId = readVariable($_POST['oraId'][$i], 'numeric unsigned');
			if (isset($oraId)) {
			    oraElmarad($oraId);
			    // órák újra lekérdezése
			    if (isset($tanarId)) $ADAT['orak'] = getTanarNapiOrak($tanarId, $dt);
			    elseif (isset($osztalyId)) $ADAT['orak'] = getOsztalyNapiOrak($osztalyId, $dt);
			}
		    }
		}
	    }
	}

	// toolBar
        $TOOL['datumSelect'] = array(
            'tipus' => 'cella', 'post' => array('ora', 'tanarId', 'osztalyId', 'tankorId'),
            'paramName' => 'dt', 'hanyNaponta' => 1,
            'tolDt' => $_TANEV['kezdesDt'], 'igDt' => $_TANEV['zarasDt'],
	    'napTipusok' => array('tanítási nap','speciális tanítási nap'),
        );
	if (isset($dt)) {
    	    if (!isset($osztalyId) || isset($tankorId)) $TOOL['tanarSelect'] = array('tipus'=>'cella', 'tanarok' => $Tanarok, 'post' => array('dt', 'tankorId'));
    	    if (!isset($tanarId)) $TOOL['osztalySelect'] = array('tipus'=>'sor','paramName' => 'osztalyId', 'post'=>array('dt', 'tankor'));
	}
        getToolParameters();

    } // admin vagy igazgató

?>
