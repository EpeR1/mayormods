<?php

//$_SESSION['alert'][] = 'page:insufficient_access';


if (_RIGHTS_OK !== true) die();
if (!__DIAK && !__TANAR && !__VEZETOSEG && !__NAPLOADMIN) {
    $_SESSION['alert'][] = 'page:insufficient_access';
} else {

/* 
    DESCIPTION

    Az adminisztrátor (kosziIgazoloDiak, kosziIgazoloOf, kosziIgazoloTanar) jóváhagy, elutasítás-ra kattintva
    egyesével nyilatkozik a jelentkezés jogosságáról. Esetleg szűrhető konkrét eseményre vagy eseménytípusra.

*/

    require_once('include/modules/naplo/share/osztaly.php');       
    require_once('include/modules/naplo/share/tanar.php');       
    require_once('include/modules/naplo/share/koszi.php');       
    require_once('include/modules/naplo/share/file.php');       

    define('__KOSZIADMIN',memberOf(_USERACCOUNT,'kosziAdmin'));

    $ADAT['kosziId'] = readVariable($_POST['kosziId'],'id');
    if ($action=='igazol') {

	$P['kosziId'] = readVariable($_POST['kosziId'],'id');
	$P['diakId'] = readVariable($_POST['diakId'],'id');
	$P['accept'] = readVariable($_POST['accept'],'bool');
	$P['decline'] = readVariable($_POST['decline'],'bool');
	$P['kosziPontId'] = readVariable($_POST['kosziPontId'],'id') ;

	/* ellenőrizzük a jogosultságokat is először */
	$kosziJovahagyhato = ( __KOSZIADMIN===true || ( __TANAR===true && kosziJovahagyhatoByTanarId($P['kosziId'],__USERTANARID)) || (__DIAK===true && kosziJovahagyhatoByDiakId($P['kosziId'],__USERDIAKID)) ) ? true : false;

	if ($kosziJovahagyhato===true) {
	    if ($P['decline']===true) {
		kosziElutasit($P['kosziId'],$P['diakId']);
	    } elseif ($P['accept']===true) {
		kosziJovahagy($P['kosziId'],$P['diakId']);
	    }
	} else {
	    $_SESSION['alert'][] = 'info:insufficient_access';
	}
    }


    $ADAT['kosziIgazolando'] = getKosziDiakIgazolandoLista('',array('diakId'=>__USERDIAKID,'tanarId'=>__USERTANARID,'kosziadmin'=>__KOSZIADMIN));
    $ADAT['diak'] = getDiakok(array('result'=>'assoc'));


// --------------------------------------------
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


/*
    $ADAT['tanarok'] = getTanarok();
    $ADAT['osztalyok'] = getOsztalyok();
    $ADAT['diakok'] = getDiakok();
*/

    }

?>
