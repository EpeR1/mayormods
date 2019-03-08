<?php

    if (defined('_SESSIONID') and _SESSIONID != '') {
	$MENU['logout'] = array(
	    array(
		'txt' => 'Sign out',
		'url' => 'index.php?policy=public&page=session&f=logout',
		'get' => array('sessionID','skin','lang')
	    )
	);
    $MENU['session'] = array(
	    array(
		'txt' => 'Profile',
		'url' => 'index.php?page=password&sub=&f=changeMyPassword&userAccount='._USERACCOUNT.'&policy=public&toPolicy=parent',
		'get' => array('skin','lang','sessionID'),
	    )
	);
    }
    $MENU['modules']['session'] = array(
//	    'searchAccount' => array(array('txt' => 'Account search','url' => 'index.php?page=session&sub=search&f=searchAccount')),
//	    'searchGroup' => array(array('txt' => 'Group search','url' => 'index.php?page=session&sub=search&f=searchGroup')),
	    'changeMyPassword' => array(array('txt' => 'Change password','url' => 'index.php?page=password&sub=&f=changeMyPassword&userAccount='._USERACCOUNT.'&policy=public&toPolicy=parent','get' => array('skin','lang','sessionID'))),
//		'sub' => array(
//	    		'search' => array(
//			'searchAccount' => array(array('txt' => 'Account search')),
//			'searchGroup' => array(array('txt' => 'Group search')),
//	    	)
//		)
	);
    $MENU['home'] = array(
	array('txt' => 'Home', 'url' => 'index.php')
    );

?>
