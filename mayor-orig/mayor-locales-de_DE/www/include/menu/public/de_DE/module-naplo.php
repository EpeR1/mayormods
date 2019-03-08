<?php
/*
    module: naplo
*/

    $MENU['naplo'] = array(array('txt' => 'Stundenplan', 'url' => 'index.php?page=naplo&sub=tanev&f=orarend'));
    $MENU['modules']['naplo']['tanev'] = array(
	array('txt' => 'Stundenplan', 'url' => 'index.php?page=naplo&sub=tanev&f=orarend'),
	array('txt' => 'Freie Räume', 'url' => 'index.php?page=naplo&sub=tanev&f=szabadTerem'),
	array('txt' => 'Vertretung', 'url'=>'index.php?page=naplo&sub=tanev&f=helyettesites')
    );

?>
