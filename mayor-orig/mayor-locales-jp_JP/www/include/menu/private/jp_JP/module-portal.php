<?php

    if (defined('__HIREKADMIN') && __HIREKADMIN) {
	$MENU['portal'] = array(array('txt' => 'ニュース', 'url' => 'index.php?page=portal&sub=hirek&f=hirekAdmin'));
	$MENU['modules']['portal']['hirek'] = array(
	    array('txt' => 'ニュースの編集', 'url' => 'index.php?page=portal&sub=hirek&f=hirekAdmin'),
//	    array('txt' => 'ニュース', 'url' => 'index.php?page=portal&sub=hirek&f=hirek'),
	    array('txt' => 'Új hír', 'url' => 'index.php?page=portal&sub=hirek&f=egyhir'),
	    array('txt' => '新しい質問', 'url' => 'index.php?page=portal&sub=kerdoiv&f=kerdoivAdmin')
	);
    } else {
	$MENU['portal'] = array(array('txt' => 'ニュースの提出', 'url' => 'index.php?page=portal&sub=hirek&f=egyhir'));
    }
?>
