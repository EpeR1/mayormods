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
	$ADAT['esemenyId'] = readVariable($_POST['esemenyId'],'id', readVariable($_GET['esemenyId'],'id'));
	$_POST['esemenyId'] = $ADAT['esemenyId'];
	if ($ADAT['esemenyId'] != '') $ADAT['esemenyAdat'] = getEsemenyAdat($ADAT['esemenyId']);
	define('__MODOSITHAT',(
	    __NAPLOADMIN || __VEZETOSEG 
	    || (
		__TANAR
		&& ( $ADAT['esemenyId'] == '' || in_array(__USERTANARID, $ADAT['esemenyAdat']['tanarIds']))
	    )
	));

	if (__MODOSITHAT) {
	    if ($action == 'nevsorModositas') {
		$ujDiakIds = readVariable($_POST['ujDiakId'], 'id');
		// TODO: ellenőrizni, hogy a diák jelentkezhet-e az eseményre (esemenyOsztaly, osztalyDiak)
		if (is_array($ujDiakIds)) foreach ($ujDiakIds as $diakId) esemenyJelentkezes($diakId, $ADAT['esemenyId']);
		$torolDiakId = readVariable($_POST['torolDiakId'], 'id');
		if (isset($torolDiakId)) esemenyLeadas($torolDiakId, $ADAT['esemenyId']);

		$jovahagyDiakId = readVariable($_POST['jovahagyDiakId'], 'id');
		if (isset($jovahagyDiakId)) jelentkezesJovahagyas($jovahagyDiakId, $ADAT['esemenyId']);

		$elutasitDiakId = readVariable($_POST['elutasitDiakId'], 'id');
		if (isset($elutasitDiakId)) jelentkezesElutasitas($elutasitDiakId, $ADAT['esemenyId']);

	    }
	}

	if ($ADAT['esemenyId'] != '') {
	    	$ADAT['esemenyAdat'] = getEsemenyAdat($ADAT['esemenyId']);
		$ADAT['tanarok'] = getTanarok();
		$ADAT['osztalyok'] = getosztalyok();

		$ADAT['osztalyId2osztalyJel'] = array();
        	foreach ($ADAT['esemenyAdat']['osztalyIds'] as $_osztalyId) {
		    // osztályJelek lekérdezése
		    $i=0;
		    while ($i < count($ADAT['osztalyok']) && $ADAT['osztalyok'][$i]['osztalyId'] != $_osztalyId) $i++;
		    if (count($i<$ADAT['osztalyok'])) $ADAT['osztalyId2osztalyJel'][$_osztalyId] = $ADAT['osztalyok'][$i]['osztalyJel'];
		    $statuszLista = array('jogviszonyban van','magántanuló','egyéni munkarend');
            	    $tmp = getDiakokByOsztaly($_osztalyId, array('statusz' => $statuszLista));
		    $ADAT['diakIds'][$_osztalyId] = array();
            	    foreach ($statuszLista as $statusz)
                	foreach ($tmp[$statusz] as $_diakId) {
                    	    $ADAT['diakok'][$_osztalyId][] = array(
                        	'diakId' => $_diakId, 'diakNev' => $tmp[$_diakId]['diakNev'], 'beDt' => $tmp[$_diakId]['beDt'],
                        	'kiDt' => $tmp[$_diakId]['kiDt'], 'statusz' => $statusz
                    	    );
			    $ADAT['diakIds'][$_osztalyId][] = $_diakId;
			    $ADAT['diak2osztaly'][$_diakId] = $_osztalyId;
			}
        	}
	}

	$ADAT['esemenyek'] = getEsemenyLista();
        $TOOL['esemenySelect'] = array('tipus'=>'cella',
            'paramName'=>'esemenyId','paramDesc'=>'esemenyNev','esemenyId'=>$ADAT['esemenyId'],'adatok' => $ADAT['esemenyek']
        );
	if ($ADAT['esemenyId'] != '') {
	    $TOOL['oldalFlipper'] = array('tipus' => 'cella',
    		'url' => array('index.php?page=naplo&sub=esemeny&f=ujEsemeny'),
    		'titleConst' => array('_ESEMENYMODOSITAS'), 'post' => array('esemenyId'),
	    );
	}

	getToolParameters();

    }

?>