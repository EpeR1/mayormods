<?php

    if (_RIGHTS_OK !== true) die();
    if (__TANAR!==true && __NAPLOADMIN !== true && __TITKARSAG !== true && __VEZETOSEG !== true) return false;

    require_once('include/modules/naplo/share/osztaly.php');
    require_once('include/modules/naplo/share/tankor.php');
    require_once('include/modules/naplo/tanev/tankorCsoport.php');
    require_once('include/modules/naplo/share/tankorBlokk.php');
    require_once('include/modules/naplo/share/targy.php');
    require_once('include/modules/naplo/share/tanmenet.php');
    require_once('include/modules/naplo/share/tanar.php');

    $tankorCsoportId = readVariable($_POST['tankorCsoportId'], 'id');

    $CSOPORTTANKOR = getTankorCsoportTankoreiByCsoportId($tankorCsoportId);



    
    $_JSON = array();
    $_JSON['tankorCsoportId'] = $tankorCsoportId;
    $_JSON['tankorCsoportAdat'] = getTankorCsoportAdat($tankorCsoportId);
    $_JSON['tankorCsoportTankorei'] = $CSOPORTTANKOR;

    $_JSON['tankorCsoportNev'] = $_JSON['tankorCsoportAdat'][0]['csoportNev'];
    $_JSON['visibleData'] = true;
?>