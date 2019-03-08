<?php

    if (defined('__HIREKADMIN') && __HIREKADMIN) {
	$MENU['portal'] = array(array('txt' => 'Nachrichten', 'url' => 'index.php?page=portal&sub=hirek&f=hirekAdmin'));
	$MENU['modules']['portal']['hirek'] = array(
	    array('txt' => 'Administrierung der Nachrichten', 'url' => 'index.php?page=portal&sub=hirek&f=hirekAdmin'),
//	    array('txt' => 'Nachrichten', 'url' => 'index.php?page=portal&sub=hirek&f=hirek'),
	    array('txt' => 'Neue Nachricht', 'url' => 'index.php?page=portal&sub=hirek&f=egyhir'),
	    array('txt' => 'Neue Frage', 'url' => 'index.php?page=portal&sub=kerdoiv&f=kerdoivAdmin')
	);
    } else {
	$MENU['portal'] = array(array('txt' => 'Einschicken der Nachricht', 'url' => 'index.php?page=portal&sub=hirek&f=egyhir'));
    }
?>
