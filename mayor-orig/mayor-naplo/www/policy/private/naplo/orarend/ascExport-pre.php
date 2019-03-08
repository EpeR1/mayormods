<?php

    if (_RIGHTS_OK !== true) die();
    if (__NAPLOADMIN || __VEZETOSEG) {

	require_once('include/modules/naplo/share/tanar.php');
	require_once('include/modules/naplo/share/orarend.php');
	require_once('include/modules/naplo/share/osztaly.php');
	require_once('include/modules/naplo/share/terem.php');
	require_once('include/modules/naplo/share/tankor.php');
	require_once('include/modules/naplo/share/tankorBlokk.php');
	require_once('include/modules/naplo/share/targy.php');
	require_once('include/modules/naplo/share/diak.php');
	require_once('include/share/date/names.php');

	$dt = $ADAT['dt'] = readVariable($_POST['dt'], 'datetime', null);
	$ADAT['tanev'] = $tanev = readVariable($_POST['tanev'], 'numeric', __TANEV);
	$ADAT['tanevDb'] = tanevDbNev(__INTEZMENY, $tanev);

	if ($tanev != __TANEV) $_TA = getTanevAdat($tanev);
	else $_TA = $_TANEV;

	$ADAT['orarendiHet'] = getOrarendiHetek(array('tanev' => $tanev, 'tolDt' => $dt, 'igDt' => $dt, 'csakOrarendbol' => true));
	$ADAT['exportalandoHet'] = readVariable($_POST['orarendiHet'], 'numeric', null, $ADAT['orarendiHet']);
	if (isset($ADAT['exportalandoHet'])) $ADAT['szeminariumkent'] = true;
	else $ADAT['szeminariumkent'] = readVariable($_POST['szeminariumkent'], 'bool', false, array(true, false));
	$ADAT['szakkorokkel'] = readVariable($_POST['szakkorokkel'], 'bool', false, array(true, false));
	$ADAT['targyBontas'] = readVariable($_POST['targyBontas'], 'bool', false, array(true, false));

	// Bontások lekérdezése
	$ADAT['bontas'] = ascBontasLekerdezes($ADAT['tanevDb']);

	if (!readVariable($_POST['blokkokNelkul'], 'bool', false, array(true, false))) $ADAT['tankorBlokk'] = getTankorBlokkok($tanev);
	else $ADAT['tankorBlokk'] = array();
	// A tankörök blokk óraszámai
        $tankor2blokk = array();
        if (is_array($ADAT['tankorBlokk']['exportOraszam']))
        foreach ($ADAT['tankorBlokk']['exportOraszam'] as $bId => $oraszam) { // blokkonként
            for ($i = 0; $i < count($ADAT['tankorBlokk']['idk'][$bId]); $i++) { // az érintett tankörökön végigmenve
                $tankor2blokk[ $ADAT['tankorBlokk']['idk'][$bId][$i] ]['blokkOraszam'] += $oraszam;
            }
        }
	$ADAT['tankorIndex'] = array();
	if ($ADAT['szakkorokkel']) $ADAT['tankorok'] = getTankorok(array("tanev=$tanev"));
        else $ADAT['tankorok'] = getTankorok(array("tanev=$tanev","jelenlet='kötelező'"));
        for ($i = 0; $i < count($ADAT['tankorok']); $i++) {
            $tankorId = $ADAT['tankorok'][$i]['tankorId'];
            $ADAT['tankorok'][$i]['tankorAdat'] = getTankorAdat($tankorId, $tanev);
	    // óraszám
	    if (isset($ADAT['exportalandoHet'])) {
		$ADAT['tankorok'][$i]['hetiOraszam'] = getTankorHetiOraszam($tankorId, array('tanev' => $tanev, 'dt' => $dt, 'het' => $ADAT['exportalandoHet']));
	    } else {
        	$ADAT['tankorok'][$i]['hetiOraszam'] = 0;
        	for ($j = 0; $j < count($ADAT['tankorok'][$i]['tankorAdat'][$tankorId]); $j++)  // Szemeszterenként végigmenve
            	    $ADAT['tankorok'][$i]['hetiOraszam'] += $ADAT['tankorok'][$i]['tankorAdat'][$tankorId][$j]['oraszam'];
        	if ($j != 0) {
            	    $ADAT['tankorok'][$i]['hetiOraszam'] /= $j;
            	    // Korrigáljuk a tankört érintő blokkok óraszámával
            	    if ($ADAT['tankorok'][$i]['hetiOraszam'] >= $tankor2blokk[$tankorId]['blokkOraszam'])
                	$ADAT['tankorok'][$i]['hetiOraszam'] -= $tankor2blokk[$tankorId]['blokkOraszam'];
            	    else
                	$_SESSION['alert'][] = 'message:wrong_data:tankorOraszam='.$ADAT['tankorok'][$i]['hetiOraszam'].'; blokkOraszam='
                                    .$tankor2blokk[$tankorId]['blokkOraszam'].'; tankorId='.$tankorId;
        	}
	    } // óraszám
	    $ADAT['tankorIndex'][$tankorId] = $i;
        }

	// --------------------- action -------------------------- //

	if ($action == 'ascExport') {
	    if (
//		tankorTanarRendbenE($tanev, $dt) 
//		&& 
		ascExport($ADAT)
	    )  
define('__LOADURL', href('index.php?page=session&f=download&download=true&dir=orarend&file=ascExport.xml',array('sessionID','lang','skin','policy','alert')));
//header('Location: '.location('index.php?page=session&f=download&download=true&dir=orarend&file=ascExport.xml',array('sessionID','lang','skin','policy','alert')));

	} elseif ($action == 'blokkOraszam') {

	    if (is_array($_POST['blokkOraszam'])) 
		for ($i = 0; $i < count($_POST['blokkOraszam']); $i++) {
		    list($bId, $oraszam) = explode(':', $_POST['blokkOraszam'][$i]);
		    $blokkOraszam = readVariable($oraszam, 'float unsigned', 0);
		    if ($blokkOraszam >= 0) $ADAT['blokkOraszam'][$bId] = $oraszam;
	    } // if+for
	    blokkOraszamRogzites($ADAT['blokkOraszam'], $ADAT['tanevDb']);
	    $ADAT['tankorBlokk'] = getTankorBlokkok($tanev);

	} elseif ($action == 'tobbszorosOra') {

	    ascBontasModositas($ADAT);
	    $ADAT['bontas'] = ascBontasLekerdezes($ADAT['tanevDb']);

	}

	// --------------------- action -------------------------- //

	$TOOL['tanevSelect'] = array('tipus' => 'cella', 'paramName' => 'tanev', 'post' => array());
	$TOOL['datumSelect'] = array(
            'tipus'=>'cella', 'post' => array('tanev'),'paramName' => 'dt', 'hanyNaponta' => 1,
	    'override'=>true,'tolDt' => $_TA['kezdesDt'],'igDt' => $_TA['zarasDt'],
        );

        getToolParameters();

    }

?>
