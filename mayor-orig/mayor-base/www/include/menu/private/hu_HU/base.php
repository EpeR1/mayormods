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
		'txt' => 'Felhasználók',
		'url' => 'index.php?page=session'
//		'url' => 'index.php?page=session&sub=search&f=searchAccount'
	    )
	);
    }

    if (memberOf(_USERACCOUNT, $AUTH[_POLICY]['adminGroup'])) {
/*	$MENU['admin'] = array(
	    array(
		'txt' => 'Admin',
		'url' => 'index.php?page=session&sub=admin&f=registration',
	    )
	);
*/
	$MENU['modules']['session'] = array(
	    'createAccount' => array(array('txt' => 'Új felhasználó')),
	    'createGroup' => array(array('txt' => 'Új csoport')),
	    'searchAccount' => array(array('txt' => 'Felhasználó keresése','url' => 'index.php?page=session&sub=search&f=searchAccount')),
	    'searchGroup' => array(array('txt' => 'Csoport keresése','url' => 'index.php?page=session&sub=search&f=searchGroup')),
	    'changeMyPassword' => array(array('txt' => 'Jelszóváltoztatás','url' => 'index.php?page=password&sub=&f=changeMyPassword&userAccount='._USERACCOUNT.'&policy=public&toPolicy=private','get' => array('skin','lang','sessionID'))),
	    'sessionAdmin' => array(array('txt' => 'Munkamenetek')),
	    'facebookConnect' => array(array('txt' => 'Facebook azonosítás')),
	    'googleapi' => array(array('txt' => 'Google azonosítás')),
	    'admin' => array(array('txt' => 'Szerver regisztráció','url'=>'index.php?page=session&sub=admin&f=registration')),
	);
	$MENU['modules']['session']['sub']['admin'] = array(
	    'registration' => array(array('txt' => 'Végpont regisztráció a felhőbe','url'=>'index.php?page=session&sub=admin&f=registration')),
	    'knownNodes' => array(array('txt' => 'Ismert végpontok','url'=>'index.php?page=session&sub=admin&f=knownNodes')),
	);

    } else {
	$MENU['modules']['session'] = array(
	    'searchAccount' => array(array('txt' => 'Felhasználó keresése','url' => 'index.php?page=session&sub=search&f=searchAccount')),
	    'searchGroup' => array(array('txt' => 'Csoport keresése','url' => 'index.php?page=session&sub=search&f=searchGroup')),
	    'changeMyPassword' => array(array('txt' => 'Jelszóváltoztatás','url' => 'index.php?page=password&sub=&f=changeMyPassword&userAccount='._USERACCOUNT.'&policy=public&toPolicy=private','get' => array('skin','lang','sessionID'))),
	    'facebookConnect' => array(array('txt' => 'Facebook belépés')),
	    'googleapi' => array(array('txt' => 'Google belépés')),
	);
    }

    global $NAV;
    if ($page=='session') {
	if (is_array($MENU['modules']['session'])) foreach ($MENU['modules']['session'] as $_sub => $M) {
    	    $NAV['2'][] = array('page' => 'session', 'f' => $_sub);
	}
    } else {
/*	if (memberOf(_USERACCOUNT, $AUTH[_POLICY]['adminGroup'])) {
    	    $NAV[1][] = array('page'=>'session');
	} else {
    	    $NAV[1][] = array('page'=>'session','f'=>'changeMyPassword');
	}
*/
    }
?>