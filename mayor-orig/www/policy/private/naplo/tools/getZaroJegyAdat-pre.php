<?php

    if (_RIGHTS_OK !== true) die();
    if (__TANAR!==true && __NAPLOADMIN !== true && __TITKARSAG !== true && __VEZETOSEG !== true) {
	$_SESSION['alert'][] = 'page:insufficient_access';
	exit;
    }

    global $KOVETELMENY;

    require_once('include/modules/naplo/share/osztaly.php');
    require_once('include/modules/naplo/share/tankor.php');
    require_once('include/modules/naplo/share/targy.php');
    require_once('include/modules/naplo/share/osztalyzatok.php');

    $jegyId = readVariable($_POST['jegyId'], 'id');
    $zaroJegyId = readVariable($_POST['zaroJegyId'], 'id');
    $ZA = getZaroJegyAdat($zaroJegyId);

    $targyTargy = getTargyTargy();
    $_JSON['fotargy'] = (is_array($targyTargy['FOal'][$ZA['targyId']]));
    $_JSON['altargy'] = (is_array($targyTargy['alFO'][$ZA['targyId']]));

    $_JSON['zaroJegyId'] = $zaroJegyId;
//    $_JSON['jegyAdat'] = serialize(getJegyAdat($jegyId));
    $_JSON['zaroJegyAdat'] = $ZA;
    $_JSON['zaroJegyAdat']['hivatalos'] = $KOVETELMENY[$ZA['jegyTipus']][$ZA['jegy']]['hivatalos'];
    $_JSON['zaroJegyAdat']['rovid'] = $KOVETELMENY[$ZA['jegyTipus']][$ZA['jegy']]['rovid'];
    $_JSON['diakNev'] = getDiakNevById($ZA['diakId']);
    $_JSON['targyNev'] = getTargyNevByTargyId($ZA['targyId']);

// ez zsákutca...
// ez így nem jo    require_once('skin/default/base/html/base.phtml');
    require_once('skin/classic/module-naplo/html/share/jegy.phtml'); // !!!

//    $jegySelect = formBegin(null,array('print'=>false));
    $jegySelect .= putJegySelect($ZA,'return');
//    $jegySelect .= formEnd(array('print'=>false));
    $_JSON['jegySelect'] = $jegySelect;
    
?>