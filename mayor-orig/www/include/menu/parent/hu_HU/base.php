<?php

    if (defined('_SESSIONID') and _SESSIONID != '') {
	$MENU['logout'] = array(
	    array(
		'txt' => 'Kilépés',
		'url' => 'index.php?policy=public&page=session&f=logout',
		'get' => array('sessionID','skin','lang')
	    )
	);
	$MENU['session'] = array(
	    array(
		'txt' => 'Jelszóváltoztatás',
		'url' => 'index.php?page=password&sub=&f=changeMyPassword&userAccount='._USERACCOUNT.'&policy=public&toPolicy=parent',
		'get' => array('skin','lang','sessionID'),
	    )
	);
    }
    $MENU['modules']['session'] = array(
    //	    'searchAccount' => array(array('txt' => 'Felhasználó keresése','url' => 'index.php?page=session&sub=search&f=searchAccount')),
//	    'searchGroup' => array(array('txt' => 'Csoport keresése','url' => 'index.php?page=session&sub=search&f=searchGroup')),
	    'changeMyPassword' => array(array('txt' => 'Jelszóváltoztatás','url' => 'index.php?page=password&sub=&f=changeMyPassword&userAccount='._USERACCOUNT.'&policy=public&toPolicy=parent','get' => array('skin','lang','sessionID'))),
//		'sub' => array(
//	    		'search' => array(
//			'searchAccount' => array(array('txt' => 'Felhasználó keresése')),
//			'searchGroup' => array(array('txt' => 'Csoport keresése')),
//	    	)
//		)
	);
//    $MENU['home'] = array(
//	array('txt' => 'Kezdőlap', 'url' => 'index.php')
//    );

    global $NAV;
    if ($page=='session') {
        if (is_array($MENU['modules']['session'])) foreach ($MENU['modules']['session'] as $_sub => $M) {
            $NAV[2][] = array('page' => 'session', 'f' => $_sub);
        }
    } 
    $NAV[1][] = array('page'=>'session','f'=>'changeMyPassword');
    $NAV[1][] = array('page'=>'logout');
    

?>