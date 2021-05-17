<?php
/*
    module: naplo
*/

    $MENU['naplo'] = array(array('txt' => 'Napló', 'url' => 'index.php?page=naplo&sub=orarend&f=orarend'));
    $MENU['modules']['naplo']['orarend'] = array(
	array('txt' => 'Órarend', 'url' => 'index.php?page=naplo&sub=orarend&f=orarend'),
	array('txt' => 'Helyettesítés', 'url'=>'index.php?page=naplo&sub=orarend&f=helyettesites', 'refresh'=>60),
	array('txt' => 'Szabad termek', 'url' => 'index.php?page=naplo&sub=orarend&f=szabadTerem'),
    );

if (__PORTAL_CODE=='kanizsay') {
//    $MENU['tanari_kar'] = array(array('txt' => 'Tanári kar', 'url' => 'index.php?page=naplo&sub=intezmeny&f=tanarok'));
    $MENU['modules']['naplo']['intezmeny'] = array(
	array('txt' => 'Tanári Kar', 'url' => 'index.php?page=naplo&sub=intezmeny&f=tanarok'),
    );
    $MENU['modules']['naplo']['dokumentum'] = array(
	array('txt' => 'Dokumentumok', 'url' => 'index.php?page=naplo&sub=dokumentum'),
    );
    $MENU['modules']['naplo']['felveteli'] = array(
	array('txt' => 'Felvételi, szóbeli, végeredmény lekérdezés', 'url' => 'index.php?page=naplo&sub=felveteli&f=szobeli'),
    );
//    require_once('include/menu/public/hu_HU/module-portal-kanizsay.php') or die();
}

?>
