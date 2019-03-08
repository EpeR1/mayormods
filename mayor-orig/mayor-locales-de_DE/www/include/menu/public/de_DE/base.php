<?php

    $MENU = array(
	'home'=>array(),
	'session'=>array(),
	'naplo'=>array(),
	'portal'=>array(),
	'felveteli'=>array(),
	'forum'=>array(),
	'auth'=>array(),

    );

    if (defined('_SESSIONID') and _SESSIONID != '') {
	$MENU['session'] = array(
	    array('txt' => 'Austritt', 'url' => 'index.php?page=session&f=logout')
	);
    }
    $MENU['home'] = array(
	array('txt' => 'Startseite', 'url' => 'index.php')
    );
    $MENU['auth'] = array(
	array('txt' => 'Eltern Einmeldung', 'url' => 'index.php?page=auth&f=login&toPolicy=parent'),
	array('txt' => 'Einmeldung', 'url' => 'index.php?page=auth&f=login&toPolicy=private')
    );
    $MENU['modules']['auth']['login'] = array(
	array('txt' => 'Forum Einmeldung', 'url' => 'index.php?page=auth&f=login&toPolicy=public&toPSF=forum::forum'),
	array('txt' => 'Eltern Einmeldung', 'url' => 'index.php?page=auth&f=login&toPolicy=parent'),
	array('txt' => 'geschützte Seiten','url' => 'index.php?page=auth&f=login&toPolicy=private'),
//	array('txt' => 'Registrierung','url' => 'index.php?page=session&f=createAccount&toPolicy=parent')
    );
//    $MENU['modules']['session']['createAccount'] = array(
//	array('txt' => 'Eltern Registrierung', 'url' => 'index.php?page=session&f=createAccount&toPolicy=parent'),
//	array('txt' => 'Forum Registrierung', 'url' => 'index.php?page=session&f=createAccount&toPolicy=public'),
//    );

?>
