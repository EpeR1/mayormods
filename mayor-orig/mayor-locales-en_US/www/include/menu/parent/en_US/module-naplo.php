<?php
/*
    module: naplo
*/

   $MENU['naplo'] = array(
	array('txt' => 'Online Register',	'url' => 'index.php?page=naplo&f=diakValaszto'),
	array('txt' => 'Sign on for consultation', 'url' => 'index.php?page=naplo&sub=tanev&f=fogadoOra')
    );

    // A menüpontok sorrendjének beállítása - ettől még nem jelenik meg semmi :)
    $MENU['modules']['naplo'] = array(
	'haladasi' => array(),
	'osztalyozo' => array(),
	'hianyzas' => array(),
	'bejegyzesek' => array(),
	'tanev' => array(),
	'intezmeny' => array(),
	'admin' => array(),
    );

    if (__DIAK) {
	$MENU['modules']['naplo']['haladasi'] = array(array('txt' => 'Progress register', 'url' => 'index.php?page=naplo&sub=haladasi&f=haladasi'));
	$MENU['modules']['naplo']['osztalyozo'] = array(array('txt' => 'Grades', 'url' => 'index.php?page=naplo&sub=osztalyozo&f=diak'));
	$MENU['modules']['naplo']['hianyzas'] =  array(array('txt' => 'Register of absencies', 'url' => 'index.php?page=naplo&sub=hianyzas&f=diak'));
	$MENU['modules']['naplo']['bejegyzesek'] =  array(array('txt' => 'Entries', 'url' => 'index.php?page=naplo&sub=bejegyzesek&f=bejegyzesek'));
        $MENU['modules']['naplo']['sub']['osztalyozo'] = array(
            'dolgozat' => array(array('txt' => 'Tests')),
            'bizonyitvany' => array(array('txt' => 'Report')),
            'stat' => array(array('txt' => 'Statistics')),
        );
	$MENU['modules']['naplo']['tanev'] = array(
	    array('txt' => 'Timetable', 'url' => 'index.php?page=naplo&sub=tanev&f=orarend'),
	    array('txt' => 'Workplan', 'url' => 'index.php?page=naplo&sub=tanev&f=munkaterv')
	);
	$MENU['modules']['naplo']['intezmeny'] = array(array('txt' => 'Change schoolyear', 'url' => 'index.php?page=naplo&sub=intezmeny&f=valtas'));
	$MENU['modules']['naplo']['sub']['haladasi'] = array(
	    'stat' => array(array('txt' => 'Progress statistics')),
	);
	if (__UZENO_INSTALLED===true)
	    $MENU['modules']['naplo']['uzeno'] = array(array('txt' => 'Messenger', 'url' => 'index.php?page=naplo&sub=uzeno&f=uzeno'));

    }
?>
