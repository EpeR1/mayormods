<?php

    if (defined('_SESSIONID') and _SESSIONID != '') {
	$MENU['logout'] = array(
	    array(
		'txt' => 'Quit',
		'url' => 'index.php?policy=public&page=session&f=logout',
		'get' => array('sessionID','skin','lang')
	    )
	);
	$MENU['session'] = array(
	    array(
		'txt' => 'User data',
		'url' => 'index.php?page=session&sub=search&f=searchAccount'
	    )
	);
    }
    $MENU['home'] = array(
	array('txt' => 'Start page', 'url' => 'index.php')
    );

    if (memberOf(_USERACCOUNT, $AUTH[_POLICY]['adminGroup'])) {
	$MENU['modules']['session'] = array(
	    'createAccount' => array(array('txt' => 'New user')),
	    'createGroup' => array(array('txt' => 'New group')),
	    'searchAccount' => array(array('txt' => 'Search user','url' => 'index.php?page=session&sub=search&f=searchAccount')),
	    'searchGroup' => array(array('txt' => 'Search group','url' => 'index.php?page=session&sub=search&f=searchGroup')),
	    'changeMyPassword' => array(array('txt' => 'Change password','url' => 'index.php?page=password&sub=&f=changeMyPassword&userAccount='._USERACCOUNT.'&policy=public&toPolicy=private','get' => array('skin','lang','sessionID'))),
//		'sub' => array(
//	    		'search' => array(
//			'searchAccount' => array(array('txt' => 'Search user')),
//			'searchGroup' => array(array('txt' => 'Search group')),
//	    	)
//		)
	);
    } else {
	$MENU['modules']['session'] = array(
	    'searchAccount' => array(array('txt' => 'Search user','url' => 'index.php?page=session&sub=search&f=searchAccount')),
	    'searchGroup' => array(array('txt' => 'Search group','url' => 'index.php?page=session&sub=search&f=searchGroup')),
	    'changeMyPassword' => array(array('txt' => 'Change password','url' => 'index.php?page=password&sub=&f=changeMyPassword&userAccount='._USERACCOUNT.'&policy=public&toPolicy=private','get' => array('skin','lang','sessionID'))),
	);
    }
?>
