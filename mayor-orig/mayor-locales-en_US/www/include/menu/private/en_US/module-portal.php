<?php

    if (defined('__HIREKADMIN') && __HIREKADMIN) {
	$MENU['portal'] = array(array('txt' => 'News', 'url' => 'index.php?page=portal&sub=hirek&f=hirekAdmin'));
	$MENU['modules']['portal']['hirek'] = array(
	    array('txt' => 'Edit news', 'url' => 'index.php?page=portal&sub=hirek&f=hirekAdmin'),
//	    array('txt' => 'News', 'url' => 'index.php?page=portal&sub=hirek&f=hirek'),
	    array('txt' => 'New post', 'url' => 'index.php?page=portal&sub=hirek&f=egyhir'),
	    array('txt' => 'New poll', 'url' => 'index.php?page=portal&sub=kerdoiv&f=kerdoivAdmin')
	);
    } else {
	$MENU['portal'] = array(array('txt' => 'Submit news', 'url' => 'index.php?page=portal&sub=hirek&f=egyhir'));
    }
?>
