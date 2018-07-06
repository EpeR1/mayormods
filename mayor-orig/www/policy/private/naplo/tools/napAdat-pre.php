<?php

    if (_RIGHTS_OK !== true) die();
    if (__DIAK!==true && __TANAR!==true && __NAPLOADMIN !== true && __TITKARSAG !== true && __VEZETOSEG !== true) {
        $_SESSION['alert'][] = 'page:insufficient_access';
        exit;
    }

    require_once('include/modules/naplo/share/orarend.php');
    require_once('include/modules/naplo/share/ora.php');
    require_once('include/modules/naplo/share/nap.php');
    global $aHetNapjai;
    $dt = readVariable($_POST['napDt'],'date');

    /* PUBLIKUS ADATOK */
    $_JSON['dt'] = $dt;
    $_JSON['leiras'] = $dt.' '.$aHetNapjai[date('w',strtotime($dt))-1];
    $_JSON['tanev'] = __TANEV;

    $_JSON['napAdat'] = getNapAdat($dt);
    $_JSON['tanitasiNapAdat'] = getTanitasiNapAdat(array($dt));

?>