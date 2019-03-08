<?php

    if (defined('_SESSIONID') and _SESSIONID != '') {
	$MENU['session'] = array(
	    array('txt' => 'Log out', 'url' => 'index.php?page=session&f=logout')
	);
    }
    $MENU['forum'] = array(
	array('txt' => 'Forum', 'url' => 'index.php?page=forum&f=forum')
    );
    $MENU['modules']['forum']['forum'] = array(
	array('txt' => 'Forum', 'url' => 'index.php?page=forum&f=forum'),
	array('txt' => 'Log in', 'url' => 'index.php?page=auth&f=login&toPSF=forum::forum&toPolicy=public'),
	array('txt' => 'Registration', 'url' => 'index.php?page=session&f=createAccount&toPolicy=public&toPSF=forum::forum')
    );

?>
