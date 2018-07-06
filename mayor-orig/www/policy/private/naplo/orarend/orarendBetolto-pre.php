<?php

    if (_RIGHTS_OK !== true) die();
    if (!__NAPLOADMIN) {

	$_SESSION['alert'][] = 'page:insufficient_access';

    } else {

	require_once('include/share/date/names.php');
	require_once('include/share/print/pdf.php');
        require_once('include/modules/naplo/share/orarend.php');
        require_once('include/modules/naplo/share/tanar.php');
        require_once('include/modules/naplo/share/osztaly.php');
        require_once('include/modules/naplo/share/terem.php');
        require_once('include/modules/naplo/share/tankor.php');
        require_once('include/modules/naplo/share/targy.php');
//        require_once('include/modules/naplo/share/diak.php');

	$ADAT['tanev'] = $tanev = readVariable($_POST['tanev'], 'numeric unsigned', __TANEV);
	if ($tanev == __TANEV) $TA = $_TANEV;
	else  $TA = getTanevAdat($tanev);
	$ADAT['tanevDb'] = $tanevDb = tanevDbNev(__INTEZMENY, $tanev);
	$ADAT['tolDt'] = $tolDt = readVariable($_POST['tolDt'], 'datetime', null);
	$ADAT['igDt'] = $igDt = readVariable($_POST['igDt'], 'datetime', null);
	$ADAT['orarendiHet'] = $orarendiHet = readVariable($_POST['orarendiHet'], 'numeric unsigned', null);
	// A konvertáló állományok lekérdezése
	$ADAT['convert'] = array();
	foreach (glob("include/modules/naplo/orarend/convert-*.php") as $file) $ADAT['convert'][] = substr($file,38,-4);

	//quickfix- ez így nem jo $ADAT['fileName'] = $fileName = fileNameNormal(readVariable($_POST['fileName'], 'emptystringnull', null));
	$ADAT['fileName'] = $fileName = (readVariable($_POST['fileName'], 'emptystringnull', null));
	$ADAT['conv'] = $conv = readVariable($_POST['conv'], 'emptystringnull', null, $ADAT['convert']);
	$ADAT['force'] = readVariable($_POST['force'], 'bool', false);
	$ADAT['lezaras'] = readVariable($_POST['lezaras'], 'bool', false);

    // ----- action ----- //

	if ($action == 'fileBetoltes') {
	    if (isset($fileName) && isset($conv) && isset($tanev) && isset($tolDt) && isset($igDt) && isset($orarendiHet)) {
		if (file_exists($fileName)) {
		    require_once("include/modules/naplo/orarend/convert-$conv.php");
		    define('__CONVERTED', loadFile($ADAT));
		    if (count($OrarendiOra)==0) {
			$_SESSION['alert'][] = 'message:wrong_data:OrarendiOra tömb üres';
		    } else {
			if (__CONVERTED === true) {
			    orarendBetoltes($ADAT, $OrarendiOra, $OrarendiOraTankor);
			} else {
			    $ADAT['showForceOption'] = true;
			}
		    }
		} else {
		    $_SESSION['alert'][] = 'message:file_not_found:'.$fileName;
		}
	    } else {
		$_SESSION['alert'][] = 'message:empty_field:fileName,conv,tanev,tolDt,igDt,orarendiHet';
	    }
	}

    // ----- action ----- //

        $TOOL['tanevSelect'] = array('tipus' => 'cella', 'paramName' => 'tanev','post' => array('fileName','conv'));
        $TOOL['datumTolIgSelect'] = array(
            'tipus' => 'sor', 'post' => array('tanev','fileName','conv','orarendiHet'),
            'tolParamName' => 'tolDt', 'igParamName' => 'igDt', 'hanyNaponta' => 1,
            'tolDt' => date('Y-m-d', strtotime('last Monday', strtotime($TA['kezdesDt']))),
            'igDt' => $TA['zarasDt'],
        );
	$TOOL['orarendiHetSelect'] = array(
	    'tanev' => $tanev, 'tolDt' => $tolDt, 'igDt' => $igDt, 'tipus' => 'cella', 
	    'megjelenitendoHetek' => array(1,2,3,4,5,6,7,8,9,10),
	    'post' => array('fileName','tanev','tolDt','igDt','conv'), 'paramName' => 'orarendiHet'
	);
	getToolParameters();


    }

?>
