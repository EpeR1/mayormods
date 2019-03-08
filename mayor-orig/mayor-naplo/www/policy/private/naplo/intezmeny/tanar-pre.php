<?php

    if (_RIGHTS_OK !== true) die();


    if (!__NAPLOADMIN && !__VEZETOSEG) {
	$_SESSION['alert'][] = 'page:insufficient_access';
    } else {

	require_once('include/share/net/upload.php');
        require_once('include/modules/naplo/share/file.php');
	require_once('include/modules/naplo/share/tanar.php');
	require_once('include/modules/naplo/share/file.php');
	require_once('include/modules/naplo/share/kepesites.php');

	$tanarId = readVariable($_POST['tanarId'],'id',readVariable($_GET['tanarId'],'id'));
	// Adatok frissítése adatállományból
    	if ($action == 'kepUpload') {
            mayorFileUpload(array('subdir'=>_DOWNLOADDIR.'/private/naplo/face/tanar/','filename'=>$tanarId.'.jpg'));
	} elseif (isset($_POST['fileName']) && $_POST['fileName'] != '') {

	    $mezo_elvalaszto = '	';
	    $fileName = fileNameNormal($_POST['fileName']);
	    $ADATOK = array();
    	    if (file_exists($fileName)) {

                if (!is_array($_POST['MEZO_LISTA'])) {

                        $ADATOK = readUpdateFile($fileName);
                        if (count($ADATOK) > 0) $attrList = getTableFields('tanar', 'naplo_intezmeny', $extraAttrs = array());
                        else $_SESSION['alert'][] = 'message:wrong_data';

                } else {

                        $MEZO_LISTA = $_POST['MEZO_LISTA'];
                        $KULCS_MEZOK = $_POST['KULCS_MEZOK'];
                        updateTable('tanar', $fileName, $MEZO_LISTA, $KULCS_MEZOK, $mezo_elvalaszto, $_POST['rovatfej']);

                } // MEZO_LISTA tömb
    	    } else {
                $_SESSION['alert'][] = 'message:file_not_found:'.$fileName;
    	    } // A file létezik-e

	} // van file

	if ($action == 'ujTanar') {
            $kotelezoParamOk = (isset($_POST['viseltCsaladinev']) && $_POST['viseltCsaladinev'] != '');
            $kotelezoParamOk &= (isset($_POST['beDt']) && $_POST['beDt'] != '');
            if ($kotelezoParamOk) {
                $tanarId = ujTanar($_POST);
                if ($tanarId) list($ADAT['tanarAdat']) = getTanarAdatById($tanarId);
            } else {
                $_SESSION['alert'][] = 'message:empty_field:(viseltCsaladinev,beDt)';
            }
	}
	if (isset($tanarId)) {

	    $ADAT['tanarId'] = $tanarId;
	    $ADAT['kepesitesek'] = getKepesitesek();
	    foreach ($ADAT['kepesitesek'] as $idx => $kAdat) $ADAT['kepesitesIds'][] = $kAdat['kepesitesId'];
	    $ADAT['besorolasok'] = getEnumField('naplo_intezmeny', 'tanar', 'besorolas');
	    $ADAT['vegzettsegek'] = getEnumField('naplo_intezmeny', 'kepesites', 'vegzettseg');
	    $ADAT['fokozatok'] = getEnumField('naplo_intezmeny', 'kepesites', 'fokozat');
	    $ADAT['specializaciok'] = getEnumField('naplo_intezmeny', 'kepesites', 'specializacio');
	    $ADAT['statuszok'] = getEnumField('naplo_intezmeny', 'tanar', 'statusz');

	    if (
		    $action == 'tanarAlapadatModositas' ||
                    $action == 'tanarSzuletesiAdatModositas' ||
                    $action == 'tanarJogviszonyModositas'
	    ) {

                $ok = tanarAdatModositas($_POST);

            } elseif ($action == 'tanarKepesitesModositas') {
		$addKepesitesId = readVariable($_POST['addKepesitesId'], 'id',null, $ADAT['kepesitesIds']);
		if (isset($addKepesitesId)) {
		    tanarKepesitesHozzarendeles($tanarId, $addKepesitesId);
		} else {
		    $vegzettseg = readVariable($_POST['vegzettseg'], 'enum', null, $ADAT['vegzettsegek']);
		    $fokozat = readVariable($_POST['fokozat'], 'enum', null, $ADAT['fokozatok']);
		    $specializacio = readVariable($_POST['specializacio'], 'enum', null, $ADAT['specializaciok']);
		    $kepesitesNev = readVariable($_POST['kepesitesNev'], 'string');
		    if (isset($vegzettseg) && isset($fokozat) && isset($specializacio) && isset($kepesitesNev)) {
			$kepesitesId = ujKepesites($vegzettseg, $fokozat, $specializacio, $kepesitesNev);
			if ($kepesitesId !== false) tanarKepesitesHozzarendeles($tanarId, $kepesitesId);
		    } else {
			$_SESSION['alert'][] = 'message:wrong_data:'.implode(',', array($vegzettseg, $fokozat, $specializacio, $kepesitesNev));
		    }
		}
	    }
	    list($ADAT['tanarAdat']) = getTanarAdatById($tanarId);
	    $ADAT['tanarAdat']['kepesites'] = getTanarKepesites($tanarId);


	}

        // ToolBar
	// $TOOL['munkakozossegSelect'] = array('tipus'=>'cella','paramName' => 'mkId', 'post'=>array());
	$TOOL['tanarSelect'] = array('tipus'=>'cella', 'mkId' => $mkId, 'beDt' => '1900-01-01', 'kiDt' => date('Y-m-d'), 'összes'=>true, 'override'=> true, 'post'=>array('mkId'));
	getToolParameters();


    } // naploadmin / vezetőség



?>
