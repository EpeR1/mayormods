<?php

    $MENU = array(
	'home'=>array(),
	'session'=>array(),
	'naplo'=>array(),
	'portal'=>array(),
	'felveteli'=>array(),
//	'forum'=>array(),
//	'auth'=>array(),
    );

    if (defined('_SESSIONID') and _SESSIONID != '') {
	$MENU['session'] = array(
	    array('txt' => 'Kilépés', 'url' => 'index.php?page=session&f=logout')
	);
    }
    $MENU['home'] = array(
	array('txt' => 'Kezdőlap', 'url' => 'index.php')
    );

    // $NAVI[] = array('txt' => 'Kezdőlap', 'url' => 'index.php', 'icon' => 'icon-home-alt');

//    $MENU['modules']['session']['createAccount'] = array(
//	array('txt' => 'Szülői regisztráció', 'url' => 'index.php?page=session&f=createAccount&toPolicy=parent'),
//	array('txt' => 'Fórum regisztráció', 'url' => 'index.php?page=session&f=createAccount&toPolicy=public'),
//    );
    if ($f == 'changeMyPassword') {
	$MENU['back'] = array(array('txt'=>'Vissza','url'=>'index.php?policy='.$toPolicy,'get'=>array('sessionID','skin','lang')));
	$MENU['password'] = array(array('txt'=>'Jelszóváltoztatás','url'=>'index.php?page=password&sub=&f=changeMyPassword'));
//	$NAV[1][] = array('page'=>'back');
    }

?>
