<?php
/*
    module: naplo
*/

    $MENU['naplo'] = array(array('txt' => 'Timetable', 'url' => 'index.php?page=naplo&sub=tanev&f=orarend'));
    $MENU['modules']['naplo']['tanev'] = array(
	array('txt' => 'Timetable', 'url' => 'index.php?page=naplo&sub=tanev&f=orarend'),
	array('txt' => 'Unused classrooms', 'url' => 'index.php?page=naplo&sub=tanev&f=szabadTerem'),
	array('txt' => 'Substitution', 'url'=>'index.php?page=naplo&sub=tanev&f=helyettesites')
    );

?>
