<?php

if (_RIGHTS_OK !== true) die();                                                                                                                                                                            

if (!defined('__KOSZIADMIN')) define('__KOSZIADMIN',memberOf(_USERACCOUNT,'kosziadmin'));

if (!(__KOSZIADMIN || __NAPLOADMIN)) { /* vagy naploadmin vagy kosziadmin (ez a csoport opcionális!) */
    $_SESSION['alert'][] = 'page:insufficient_access';
} else {

    require_once('include/modules/naplo/share/osztaly.php');
    require_once('include/modules/naplo/share/tanar.php');
    require_once('include/modules/naplo/share/koszi.php');
    require_once('include/modules/naplo/share/targy.php');
    require_once('include/modules/naplo/share/file.php');

    $ADAT['kosziEsemenyTipusok'] = getEnumField('naplo_intezmeny', 'kosziEsemeny', 'kosziEsemenyTipus');
    $ADAT['kosziPontTipusok'] = getEnumField('naplo_intezmeny', 'kosziPont', 'kosziPontTipus');
    $ADAT['kosziIgazolok'] = getEnumField('naplo', 'koszi', 'igazolo');
    $ADAT['kosziEsemenyId'] = readVariable($_POST['kosziEsemenyId'], 'id');    

    if ($action=='ujKosziEsemeny') {

	$P['kosziEsemenyTipus'] = readVariable($_POST['kosziEsemenyTipus'],'enum',null,$ADAT['kosziEsemenyTipusok']);
	$P['kosziEsemenyNev'] = readVariable($_POST['kosziEsemenyNev'],'string') ;
	$P['kosziEsemenyLeiras'] = readVariable($_POST['kosziEsemenyLeiras'],'string') ;

	$ADAT['kosziEsemenyId'] = ujEsemeny($P);

    } elseif ( $action== 'ujPont') {
	$P['kosziEsemenyId'] = $ADAT['kosziEsemenyId'];
	$P['kosziPontTipus'] = readVariable($_POST['kosziPontTipus'],'enum',null,$ADAT['kosziPontTipusok']);
	$P['kosziPont']	     = readVariable($_POST['kosziPont'],'id');
	$P['kosziHelyezes']  = readVariable($_POST['kosziHelyezes'],'id');

	ujKosziPont($P);
    } elseif ( $action== 'delKoszi') {

	$P['kosziId'] = readVariable($_POST['kosziId'],'id');

	delKoszi($P['kosziId']);

    } elseif ( $action== 'ujKoszi') {

	$P['kosziEsemenyId'] = $ADAT['kosziEsemenyId'];
	$P['dt'] = readVariable($_POST['dt'],'date');
	$P['tolDt'] = readVariable($_POST['tolDt'],'datetime');
	$P['igDt'] = readVariable($_POST['igDt'],'datetime');
	$P['felev']	     = readVariable($_POST['felev'],'id');
	$P['igazolo']  = @implode(',',readVariable($_POST['igazolo'],'enum',null,$ADAT['kosziIgazolok']));
	
	$P['diakId'] = readVariable($_POST['diakId'],'id');
	$P['tanarId'] = readVariable($_POST['tanarId'],'id');
	$P['targyId'] = readVariable($_POST['targyId'],'id');
	$P['osztalyfonokId'] = readVariable($_POST['osztalyfonokId'],'id');

	$kosziId = ujKoszi($P);
	
	// ez a rész nincs használatban igaziból, de megtartjuk
	if (is_array($P['diakId'])) $ig[] = 'diák';
	if (is_array($P['tanarId'])) $ig[] = 'tanár';
	if (is_array($P['osztalyfonokId'])) $ig[] = 'osztályfőnök';
	if (is_array($ig) && isset($kosziId)) {
	    $P['igazolo'] = implode(',',$ig);
	    if (is_array($P['diakId'])) kosziIgazolo($kosziId, $P['diakId'],'Diak');
	    if (is_array($P['tanarId'])) kosziIgazolo($kosziId, $P['tanarId'],'Tanar');
	    if (is_array($P['osztalyfonokId'])) kosziIgazolo($kosziId, $P['osztalyfonokId'],'Of');
	}
    }

    if (is_numeric($ADAT['kosziEsemenyId'])) {
	$ADAT['kosziPont'] = getKosziPont($ADAT['kosziEsemenyId']);
	$ADAT['koszi'] = getKoszi($ADAT['kosziEsemenyId']);
    }
    $ADAT['kosziEsemenyek'] = getKosziEsemenyek();
    $ADAT['tanarok'] = getTanarok();
    $ADAT['osztalyok'] = getOsztalyok();
    //$ADAT['diakok'] = getDiakok();
    $ADAT['targyak'] = getTargyak();

}

?>
