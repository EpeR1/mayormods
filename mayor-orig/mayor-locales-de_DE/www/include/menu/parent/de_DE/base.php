<?php

    if (defined('_SESSIONID') and _SESSIONID != '') {
	$MENU['logout'] = array(
	    array(
		'txt' => 'Verlassen',
		'url' => 'index.php?policy=public&page=session&f=logout',
		'get' => array('sessionID','skin','lang')
	    )
	);
    $MENU['session'] = array(
	    array(
		'txt' => 'Benutzerdaten',
		'url' => 'index.php?page=password&sub=&f=changeMyPassword&userAccount='._USERACCOUNT.'&policy=public&toPolicy=parent',
		'get' => array('skin','lang','sessionID'),
	    )
	);
    }
    $MENU['modules']['session'] = array(
//	    'searchAccount' => array(array('txt' => 'Suche des Benutzer','url' => 'index.php?page=session&sub=search&f=searchAccount')),
//	    'searchGroup' => array(array('txt' => 'Suche der Gruppe','url' => 'index.php?page=session&sub=search&f=searchGroup')),
	    'changeMyPassword' => array(array('txt' => 'Passwortänderung','url' => 'index.php?page=password&sub=&f=changeMyPassword&userAccount='._USERACCOUNT.'&policy=public&toPolicy=parent','get' => array('skin','lang','sessionID'))),
//		'sub' => array(
//	    		'search' => array(
//			'searchAccount' => array(array('txt' => 'Suche des Benutzer')),
//			'searchGroup' => array(array('txt' => 'Suche der Gruppe')),
//	    	)
//		)
	);
    $MENU['home'] = array(
	array('txt' => 'Startseite', 'url' => 'index.php')
    );

?>
