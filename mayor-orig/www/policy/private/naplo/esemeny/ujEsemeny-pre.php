<?php

    if (_RIGHTS_OK !== true) die();

    if (!__NAPLOADMIN && !__VEZETOSEG && !__TANAR) {
        $_SESSION['alert'][] = 'page:insufficient_access';
    } else {
        require_once('include/modules/naplo/share/file.php');
        require_once('include/modules/naplo/share/esemeny.php');
        require_once('include/modules/naplo/share/tanar.php');
        require_once('include/modules/naplo/share/osztaly.php');

	$ADAT['esemenyKategoriak'] = getEnumField('naplo','esemeny','esemenyKategoria');
	$ADAT['esemenyId'] = readVariable($_POST['esemenyId'],'id');
	if ($ADAT['esemenyId'] != '') $ADAT['esemenyAdat'] = getEsemenyAdat($ADAT['esemenyId']);

	define('__MODOSITHAT',(
	    __NAPLOADMIN || __VEZETOSEG 
	    || (
		__TANAR
		&& ( $ADAT['esemenyId'] == '' || in_array(__USERTANARID, $ADAT['esemenyAdat']['tanarIds']))
	    )
	));

	if ($action == 'esemenyTorles') {

	    if (__MODOSITHAT && $ADAT['esemenyId'] != '') {
		if (esemenyTorles($ADAT['esemenyId'])) {
		    $_SESSION['alert'][] = 'info:success';
		    unset($ADAT);
		}
	    } else {
		$_SESSION['alert'][] = 'message:insufficient_access';
	    }

	} elseif ($action != '') {

	    $ADAT['esemenyRovidnev'] = readVariable($_POST['esemenyRovidnev'],'string');
	    $ADAT['esemenyNev'] = readVariable($_POST['esemenyNev'],'string');
	    $ADAT['esemenyKategoria'] = readVariable($_POST['esemenyKategoria'],'enum',null,$ADAT['esemenyKategoriak']);
	    $ADAT['esemenyLeiras'] = readVariable($_POST['esemenyLeiras'],'string');
	    $ADAT['jelentkezesTolDt'] = readVariable($_POST['jelentkezesTolDt'],'datetime');
	    $ADAT['jelentkezesIgDt'] = readVariable($_POST['jelentkezesIgDt'],'datetime');
	    $ADAT['min'] = readVariable($_POST['min'],'numeric unsigned');
	    $ADAT['max'] = readVariable($_POST['max'],'numeric unsigned');

	    if (
		$ADAT['esemenyRovidnev']!='' && $ADAT['esemenyKategoria']!='' && $ADAT['jelentkezesTolDt']!='' && $ADAT['jelentkezesIgDt']!=''
		&& ($action == 'ujEsemeny' || $ADAT['esemenyId']!='')
	    ) {

		if ($action == 'ujEsemeny') {
	    	    $ADAT['esemenyId'] = ujEsemeny($ADAT);
		    if (is_numeric($ADAT['esemenyId'])) $_SESSION['alert'][] = 'info:success';
		} elseif ($action == 'esemenyModositas') {
		    $ADAT['esemenyTanar'] = readVariable($_POST['esemenyTanar'],'id',array());
		    $ADAT['esemenyOsztaly'] = readVariable($_POST['esemenyOsztaly'],'id',array());
		    $ADAT['esemenyTanar'] = readVariable($_POST['esemenyTanar'],'id',array());
		    // TODO: jogosultságok! Tanár csak a hozzárendeltet!
		    if (__MODOSITHAT) {
			esemenyModositas($ADAT);
			$_SESSION['alert'][] = 'info:success';
		    } else $_SESSION['alert'][] = 'message:insufficient_access';
		}

	    } else {
		$_SESSION['alert'][] = 'message:wrong_data';
	    } // wrong_data
	} // action

	if ($ADAT['esemenyId'] != '') {
	    	$ADAT['esemenyAdat'] = getEsemenyAdat($ADAT['esemenyId']);
		$ADAT['tanarok'] = getTanarok();
		$ADAT['osztalyok'] = getosztalyok();
	}
	$ADAT['esemenyek'] = getEsemenyLista();

	$TOOL['esemenySelect'] = array('tipus'=>'cella',
	    'paramName'=>'esemenyId','paramDesc'=>'esemenyNev','esemenyId'=>$ADAT['esemenyId'],'adatok' => $ADAT['esemenyek']
	);
	if ($ADAT['esemenyId'] != '') {
    	    $TOOL['oldalFlipper'] = array('tipus' => 'cella',
        	'url' => array('index.php?page=naplo&sub=esemeny&f=esemenyDiak'),
        	'titleConst' => array('_ESEMENYJELENTKEZOK'), 'post' => array('esemenyId'),
    	    );
	}
        getToolParameters();

    }

?>