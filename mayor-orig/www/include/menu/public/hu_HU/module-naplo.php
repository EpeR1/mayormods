<?php
/*
    module: naplo
*/

    $MENU['naplo'] = array(array('txt' => 'Órarend', 'url' => 'index.php?page=naplo&sub=orarend&f=orarend'));
    $MENU['modules']['naplo']['orarend'] = array(
	array('txt' => 'Órarend', 'url' => 'index.php?page=naplo&sub=orarend&f=orarend'),
	array('txt' => 'Helyettesítés', 'url'=>'index.php?page=naplo&sub=orarend&f=helyettesites', 'refresh'=>60),
	array('txt' => 'Szabad termek', 'url' => 'index.php?page=naplo&sub=orarend&f=szabadTerem'),
    );

?>
