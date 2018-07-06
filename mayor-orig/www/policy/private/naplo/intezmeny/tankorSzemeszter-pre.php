<?php

// 3.1

    if (_RIGHTS_OK !== true) die();

    if (__NAPLOADMIN) {
	/* Including shared libraries  */
	require_once('include/modules/naplo/share/tanar.php');
	require_once('include/modules/naplo/share/munkakozosseg.php');
	require_once('include/modules/naplo/share/tankor.php');
	require_once('include/modules/naplo/share/osztaly.php');
	require_once('include/modules/naplo/share/targy.php');
        require_once('include/modules/naplo/share/szemeszter.php');
	require_once('include/modules/naplo/intezmeny/tankorTanar.php');
	require_once('include/modules/naplo/share/tankorBlokk.php');

	/* Input Variables */
	$mkId = readVariable($_POST['mkId'],'numeric unsigned');
	$tanarId = readVariable($_POST['tanarId'],'numeric unsigned');
	$osztalyId = readVariable($_POST['osztalyId'],'numeric unsigned');
	$tanev = readVariable($_POST['tanev'], 'numeric unsigned', __TANEV);

	/* Setting default data */
	if ($tanev != __TANEV) $TA = getTanevAdat($tanev);
	else $TA = $_TANEV;

	$ADAT['tanev'] = $tanev;

        // tankörök lekérdzése
	if (isset($mkId)) {
            $TANKOROK = getTankorByMkId($mkId, $tanev);
        } elseif (isset($osztalyId)) {
            $TANKOROK = getTankorByOsztalyId($osztalyId, $tanev, array('tanarral' => true));
        } elseif (isset($tanarId)) {
            $TANKOROK = getTankorByTanarId($tanarId, $tanev,
		array('csakId' => false, 'tolDt' => '', 'igDt' => '', 'tanarral' => true)
            );
	}

	// kiegészítő: tankorSzemeszter tábla és szemeszterek lekérdezése
	if (is_array($TANKOROK)) {

	    $tankorIds = array();
	    for ($i = 0; $i < count($TANKOROK); $i++) $tankorIds[] = $TANKOROK[$i]['tankorId'];
	    $tankorSzemeszterek = getTankorSzemeszterek($tankorIds);
	    $tankorSzemeszter = array();
	    foreach ($tankorSzemeszterek as $tankorId => $tankorAdat) {
		for ($i = 0; $i < count($tankorAdat); $i++) {
		    $tankorSzemeszter[$tankorId][$tankorAdat[$i]['tanev']][$tankorAdat[$i]['szemeszter']] = $tankorAdat[$i];
		}
	    }

	    $ADAT['tankorSzemeszter'] = $tankorSzemeszter;

	    $ADAT['szemeszterek'] = $Szemeszterek = getSzemeszterek_spec($tanev-1);


	    // --------  action ------------ //
	    if ($action == 'tankorSzemeszter') {
		if (is_array($_POST['T'])) {
		    $T = $_POST['T'];
		    $M = array();
		    for ($i = 0; $i < count($T); $i++) {
			list($tankorId, $szTanev, $szSzemeszter) = explode('/', $T[$i]);
			$name = 'O_'.$tankorId.'_'.$szTanev.'_'.$szSzemeszter;
			// Ha kötelezővé akarjuk tenni az óraszám megadását:
			// if (isset($_POST[$name]) && $_POST[$name] != '') $M[] = array(
//if (isset($_POST[$name]) && $_POST[$name] != '') $oraszam = readVariable($_POST[$name],'string'); // numeric?
			$oraszam = readVariable($_POST[$name], 'float unsigned', 0);
//			else $oraszam = 0;
			$M[] = array(
			    'tankorId' => $tankorId,
			    'tanev' => $szTanev,
			    'szemeszter' => $szSzemeszter,
			    'oraszam' => $oraszam
			);
		    }

		    $tankorNevek = array();
		    for ($i = 0; $i < count($TANKOROK); $i++)
			$tankorNevek[$TANKOROK[$i]['tankorId']] = $TANKOROK[$i]['tankorNev'];
		    if (tankorSzemeszterModositas($M, $tankorSzemeszter, $tankorNevek, $Szemeszterek, $TA['zarasDt'])) {
			// tankor szemesztereinek újraolvasása
			$tankorSzemeszterek = getTankorSzemeszterek($tankorIds);
			$tankorSzemeszter = array();
			foreach ($tankorSzemeszterek as $tankorId => $tankorAdat) {
			    for ($i = 0; $i < count($tankorAdat); $i++) {
				$tankorSzemeszter[$tankorId][$tankorAdat[$i]['tanev']][$tankorAdat[$i]['szemeszter']] = $tankorAdat[$i];
			    }
			}
			$ADAT['tankorSzemeszter'] = $tankorSzemeszter;

		    }
		}
	    }

	    // --------  action ------------ //

	    $ADAT['tankorok'] = $TANKOROK;
    	    if (is_array($ADAT['tankorok'])) {
        	for($i=0; $i<count($ADAT['tankorok']); $i++) {
            	    $_tankorId=$ADAT['tankorok'][$i]['tankorId'];
            	    $ADAT['tankorTanarok'][$_tankorId] = getTankorTanarai($_tankorId);
        	}
    	    }

	} // van Tankorok


	// ToolBar
	$TOOL['tanevSelect'] = array('tipus'=>'cella','paramName' => 'tanev', 
	    'tervezett'=>true,
	    'post'=>array('mkId','targyId','tankorId')
	);
	$TOOL['munkakozossegSelect'] = array('tipus'=>'cella','paramName' => 'mkId', 'post'=>array('tanev'));
	$TOOL['tanarSelect'] = array('tipus'=>'sor','paramName'=>'tanarId', 'post'=>array('tanev'));
	$TOOL['osztalySelect'] = array('tipus'=>'sor','paramName'=>'osztalyId', 'post'=>array('tanev'));
	getToolParameters();

    } // NAPLOADMIN
?>
