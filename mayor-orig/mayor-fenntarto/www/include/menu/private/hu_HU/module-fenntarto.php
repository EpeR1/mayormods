<?php

    $MENU['fenntarto'] = array(array('txt' => 'Fenntartó',      'url' => 'index.php?page=fenntarto'));
                        
    // A menüpontok sorrendjének beállítása - ettől még nem jelenik meg semmi :)
    $MENU['modules']['fenntarto'] = array(
        'vegpontok' => array(),
        'tantargyfelosztas' => array(),
    );

    $MENU['modules']['fenntarto']['admin'] =  array(array('txt' => 'Admin', 'url' => 'index.php?page=fenntarto&sub=admin&f=nodes'));
    $MENU['modules']['fenntarto']['naplo'] = array(array('txt' => 'Napló', 'url' => 'index.php?page=fenntarto&sub=naplo&f=tantargyfelosztas'));
    $MENU['modules']['fenntarto']['sub']['admin'] = array(
        'nodes' => array(array('txt' => 'Végpontok')),
    );
    $MENU['modules']['fenntarto']['sub']['naplo'] = array(
        'tantargyfelosztas' => array(array('txt' => 'Tantárgyfelosztás')),
    );


?>