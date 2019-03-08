<?php

    if ($_SESSION['szuloDiakIdOk'] != true) {
        header('Location: '.location('index.php?page=naplo&f=diakValaszto'));
    }

    require_once('include/modules/portal/share/hirek.php');
    require_once('include/modules/portal/share/nevnap.php');
    require_once('include/modules/portal/share/kerdoiv.php');

    $ADAT['hirek'] = getHirek(array('tolDt'=>date('Y-m-d H:i:s'), 'igDt'=>date('Y-m-d H:i:s'),'flag'=>array(1),'class'=>array(6)));

    require('skin/classic/module-portal/html/share/doboz.phtml');
    require('skin/classic/module-portal/html/share/hirek.phtml');

?>
