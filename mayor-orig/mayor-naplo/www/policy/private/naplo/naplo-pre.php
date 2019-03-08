<?php

    if (_RIGHTS_OK !== true) die();

    require_once('include/modules/naplo/share/intezmenyek.php');

    $intezmeny = readVariable($_POST['intezmeny'], 'strictstring', defined('__INTEZMENY') ? __INTEZMENY : null);
    if ($action == 'intezmenyValasztas') {
        if (isset($intezmeny) && $intezmeny !== __INTEZMENY) {
                if (updateSessionIntezmeny($intezmeny)) {
                        header('Location: '.location('index.php?page=naplo&sub=intezmeny&f=valtas'));
                }
        }
    }

    /* TOOL */
    $TOOL['intezmenySelect'] = array('tipus' => 'sor', 'post' => array(), 'action' => 'intezmenyValasztas');

    getToolParameters();
    if (__TANAR) $beirasiAdatok['beiratlan'] = getBeirasiAdatok();

?>
