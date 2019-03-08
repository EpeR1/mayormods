<?php
/*
    module: naplo
*/

    $MENU['naplo'] = array(array('txt' => '時刻表', 'url' => 'index.php?page=naplo&sub=orarend&f=orarend'));
    $MENU['modules']['naplo']['tanev'] = array(
	array('txt' => '時刻表', 'url' => 'index.php?page=naplo&sub=orarend&f=orarend'),
	array('txt' => '未使用の教室', 'url' => 'index.php?page=naplo&sub=orarend&f=szabadTerem'),
	array('txt' => 'Helyettesítés', 'url'=>'index.php?page=naplo&sub=orarend&f=helyettesites')
    );

?>
