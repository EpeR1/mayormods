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
	    array('txt' => 'Sign out', 'url' => 'index.php?page=session&f=logout')
	);
    }
    $MENU['home'] = array(
	array('txt' => 'Home', 'url' => 'index.php')
    );
    $MENU['auth'] = array(
	array('txt' => 'Parent login', 'url' => 'index.php?page=auth&f=login&toPolicy=parent'),
	array('txt' => 'Log in', 'url' => 'index.php?page=auth&f=login&toPolicy=private')
    );
    $MENU['modules']['auth']['login'] = array(
	array('txt' => 'Forum login', 'url' => 'index.php?page=auth&f=login&toPolicy=public&toPSF=forum::forum'),
	array('txt' => 'Parent login', 'url' => 'index.php?page=auth&f=login&toPolicy=parent'),
	array('txt' => 'Protected pages','url' => 'index.php?page=auth&f=login&toPolicy=private'),
//	array('txt' => 'Registration','url' => 'index.php?page=session&f=createAccount&toPolicy=parent')
    );
//    $MENU['modules']['session']['createAccount'] = array(
//	array('txt' => 'Parent registration', 'url' => 'index.php?page=session&f=createAccount&toPolicy=parent'),
//	array('txt' => 'Forum registration', 'url' => 'index.php?page=session&f=createAccount&toPolicy=public'),
//    );

?>
