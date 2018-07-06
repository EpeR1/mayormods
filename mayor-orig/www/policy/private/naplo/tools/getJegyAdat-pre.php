<?php

    if (_RIGHTS_OK !== true) die();
    if (__TANAR!==true && __NAPLOADMIN !== true && __TITKARSAG !== true && __VEZETOSEG !== true && __DIAK !== true ) {
	$_SESSION['alert'][] = 'page:insufficient_access';
	exit;
    }

    global $KOVETELMENY;

    require_once('include/modules/naplo/share/osztaly.php');
    require_once('include/modules/naplo/share/tankor.php');
    require_once('include/modules/naplo/share/targy.php');
    require_once('include/modules/naplo/share/osztalyzatok.php');

    $jegyId = readVariable($_POST['jegyId'], 'id');
    $ADAT = getJegyAdat($jegyId);

    if (__DIAK===true && $ADAT['diakId'] != __USERDIAKID) return; // más jegyet ne nézhessen meg a diák

    $_JSON['jegyId'] = $jegyId;
    $_JSON['jegyAdat'] = $ADAT;
    $_JSON['jegyAdat']['hivatalos'] = $KOVETELMENY[$ADAT['jegyTipus']][$ADAT['jegy']]['hivatalos'];
    $_JSON['jegyAdat']['rovid'] = $KOVETELMENY[$ADAT['jegyTipus']][$ADAT['jegy']]['rovid'];
    $_JSON['diakNev'] = getDiakNevById($ADAT['diakId']);
    $_JSON['targyNev'] = getTargyNevByTargyId($ADAT['targyId']);

//    require_once('skin/classic/module-naplo/html/share/jegy.phtml'); // !!!
//    $jegySelect = formBegin(null,array('print'=>false));
//    $jegySelect .= putJegySelect($ADAT,'return');
//    $jegySelect .= formEnd(array('print'=>false));
//    $_JSON['jegySelect'] = $jegySelect;
    
//    $_JSON['debug'] = $ADAT;

?>