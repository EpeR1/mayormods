<?php
/*
    module: naplo

    TODO:
    Egy-egy osztály hiányzását is meg lehetne valósítani - vegyük ki az osztályhoz - és csak az osztályhoz
    tartozó órákat...
*/

    if (_RIGHTS_OK !== true) die();

    if (!__NAPLOADMIN && !__VEZETOSEG) {
	$_SESSION['alert'][] = 'page:illegal_access';
    } else {

	require_once('include/share/date/names.php');
	require_once('include/modules/naplo/share/orarend.php');
	require_once('include/modules/naplo/share/ora.php');
	require_once('include/modules/naplo/share/file.php');
	require_once('include/modules/naplo/share/nap.php');

	global $_TANEV;
        if ($_TANEV['statusz']!='aktív') $_SESSION['alert'][] = 'page:nem_aktív_tanev:'.$_TANEV['tanev'];

	if (isset($_POST['dt']) && $_POST['dt'] != '') $dt = $_POST['dt'];
	elseif (__FOLYO_TANEV) $dt = getTanitasiNap(array('direction'=>'előre', 'napszam'=>1));
	else $dt = $_TANEV['kezdesDt'];

	$napiMinOra = getMinOra();
	$napiMaxOra = getMaxOra();

	$Hetek = getOrarendiHetek();
	$napTipusok = getNapTipusok();
	$napAdat = getNapAdat($dt);

	// csak a tanév alatt lehet speciális tanítási nap
	$time = strtotime($dt);
	if (
	    (strtotime($_TANEV['kezdesDt']) <= $time) and
	    ($time <= strtotime($_TANEV['zarasDt']))
	) {

	    if ($time < time()) $_SESSION['alert'][] = 'message:visszamenoleges_modositas';

	    if ($action == 'napiOrakTorlese') {
		$tipus = ($_POST['tipus'] === '')?'':readVariable($_POST['tipus'], 'enum', null, $napTipusok);
		if (isset($tipus)) {
		    napiOrakTorlese($dt, $tipus);
		    $napAdat = getNapAdat($dt);
		} else {
		    $_SESSION['alert'][] = 'message:wrong_data:napiOrakTorlese:hibás típus:'.$tipus;
		}
	    } elseif ($action == 'orakBetoltese') {
		$orarendiHet = $_POST['orarendiHet'];
		if (in_array($orarendiHet, $Hetek)) {
		    orakBetoltese($dt, $orarendiHet);
		    $napAdat = getNapAdat($dt);
		} else {
		    $_SESSION['alert'][] = 'message:wrong_data:orakBetoltese:hibás hét:'.$orarendiHet;
		}
	    } elseif ($action == 'specialisNap') {
		$celOra = $_POST['celOra'];
		$het = $_POST['het'];
		$nap = $_POST['nap'];
		$ora = $_POST['ora'];
		specialisNap($dt, $celOra, $het, $nap, $ora);
		$napAdat = getNapAdat($dt);
	    } elseif ($action == 'orakTorlese') {
		if (is_array($_POST['ora'])) {
		    orakTorlese($dt, $_POST['ora']);
		    $napAdat = getNapAdat($dt);
		}
	    }
	}

	$i = 0;
	while ($i < count($napAdat) && strpos($napAdat[$i]['tipus'], 'tanítási nap') === false) $i++;
	$vanTanitasiNap = ($i < count($napAdat));
	$szabadOrak = getSzabadOrak($dt);
	$munkatervek = getMunkatervek(array('result'=>'assoc','keyfield'=>'munkatervId'));


        // toolBar
        $TOOL['datumSelect'] = array(
            'tipus'=>'cella', 'post'=>array(), 'paramName' => 'dt', 'hanyNaponta' => 1, 'tolDt' => $_TANEV['kezdesDt'],
	    'igDt' => $_TANEV['zarasDt'],

//	    'napTipusok' => array('tanítási nap', 'speciális tanítási nap'),
//	    'napokSzama' => 10
        );
	getToolParameters();
		          
    }

?>
