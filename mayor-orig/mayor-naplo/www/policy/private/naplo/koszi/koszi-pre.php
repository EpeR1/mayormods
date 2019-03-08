<?php

if (_RIGHTS_OK !== true) die();                                                                                                                                                                        
if (!__DIAK) $_SESSION['alert'][] = 'page:insufficient_access';                                                                                                                                                            
else {

    require_once('include/modules/naplo/share/osztaly.php');
    require_once('include/modules/naplo/share/tanar.php');
    require_once('include/modules/naplo/share/koszi.php');
    require_once('include/modules/naplo/share/file.php');

    $ADAT['kosziEsemenyTipusok'] = getEnumField('naplo_intezmeny', 'kosziEsemeny', 'kosziEsemenyTipus');
    $ADAT['kosziPontTipusok'] = getEnumField('naplo_intezmeny', 'kosziPont', 'kosziPontTipus');
    $ADAT['kosziIgazolok'] = getEnumField('naplo', 'koszi', 'kosziIgazolo');

    $ADAT['kosziId'] = readVariable($_POST['kosziId'],'id');
    $ADAT['kosziEsemenyId'] = readVariable($_POST['kosziEsemenyId'], 'id');    

    if ($action=='ujKosziDiak') {

	$P['kosziId'] = readVariable($_POST['kosziId'],'id');
	$P['kosziPontId'] = readVariable($_POST['kosziPontId'],'id') ;
	$P['diakId']  = __USERDIAKID;

	if (isset($P['kosziPontId']) && isset($P['kosziId']))
	    $result = ujKosziDiak($P);

    }

    $ADAT['koszi'] = getKosziLista(); // tanev
    $ADAT['kosziDiakLista'] = getKosziDiakLista(__USERDIAKID);

    if (is_numeric($ADAT['kosziId'])) {
	$ADAT['kosziEsemenyId'] = getKosziEsemenyIdByKosziId($ADAT['kosziId']); // template Id - t lekérdezzük a valós esemény által. (béna az elnevezés!!!)
    }

    if (is_numeric($ADAT['kosziEsemenyId'])) {
	$ADAT['kosziPont'] = getKosziPont($ADAT['kosziEsemenyId']);
    }

}

/*
    $ADAT['tanarok'] = getTanarok();
    $ADAT['osztalyok'] = getOsztalyok();
    $ADAT['diakok'] = getDiakok();
*/

?>
