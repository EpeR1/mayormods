<?php

    if (_RIGHTS_OK !== true) die();

    require_once('include/modules/naplo/share/terem.php');
    require_once('include/modules/naplo/share/intezmenyek.php');
    require_once('include/share/date/names.php');

    $tolDt = readVariable($_POST['tolDt'],'date',date('Y-m-d', strtotime('last Monday', strtotime('+1 days'))));
    $igDt = date('Y-m-d', strtotime('next Friday',strtotime($tolDt)));

    $forras = readVariable($_POST['forras'],'enum','orarendiOra',array('orarendiOra','ora'));

    $ADAT['telephelyek'] = getTelephelyek();
    $telephelyIds = array();

    foreach ($ADAT['telephelyek'] as $tAdat) $telephelyIds[] = $tAdat['telephelyId'];
    $telephelyId = readVariable($_POST['telephelyId'], 'id', (defined('__TELEPHELYID')===true?__TELEPHELYID:null), $telephelyIds);
    $ADAT['termek'] = getTermek(array('result' => 'assoc'));
    $ADAT['teremIdk'] = getTermek(array('result' => 'idonly', 'tipus' => array('tanterem','szaktanterem','osztályterem','labor','gépterem','tornaterem','tornaszoba','fejlesztőszoba','tanműhely','előadó','könyvtár','díszterem','templom','egyéb'), 'telephelyId' => $telephelyId));
    $ADAT['szabadTermek'] = getSzabadTermekByDtInterval($tolDt, $igDt, $ADAT['teremIdk'], $forras);
    $ADAT['toPrint'] = 'Szabad termek ('.$forras.')';

    $TOOL['datumSelect'] = array(
            'tipus'=>'cella', 'post'=>array('tanarId','osztalyId','tankorId','mkId','diakId'),
            'paramName' => 'tolDt', 'hanyNaponta' => 7,
            'override'=>true, // használathoz még át kell írni pár függvényt!!!
            'tolDt' => date('Y-m-d', strtotime('last Monday', strtotime($_TANEV['kezdesDt']))),
            'igDt' => $_TANEV['zarasDt'],
    );
    $TOOL['telephelySelect'] = array('tipus'=>'cella', 'paramName'=>'telephelyId', 'telephelyek'=>$ADAT['telephelyek'], 'post'=>array('tolDt'));

    $TOOL['forrasSelect'] = array('tipus'=>'cella', 'paramName'=>'forras', 'paramValue'=>$forras, 'adatok'=>array('ora'=>'haladási napló','orarendiOra'=>'órarend'), 'post'=>array('tolDt','telephelyId'));

?>
