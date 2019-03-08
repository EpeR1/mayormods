<?php

    if (defined('_SESSIONID') and _SESSIONID != '') {
	$MENU['session'] = array(
	    array('txt' => 'Austritt', 'url' => 'index.php?page=session&f=logout')
	);
    }
    $MENU['forum'] = array(
	array('txt' => 'Forum', 'url' => 'index.php?page=forum&f=forum')
    );
    $MENU['modules']['forum']['forum'] = array(
	array('txt' => 'Forum', 'url' => 'index.php?page=forum&f=forum'),
	array('txt' => 'Eintritt ins Forum', 'url' => 'index.php?page=auth&f=login&toPSF=forum::forum&toPolicy=public'),
	array('txt' => 'User Registrierung', 'url' => 'index.php?page=session&f=createAccount&toPolicy=public&toPSF=forum::forum')
    );

?>
