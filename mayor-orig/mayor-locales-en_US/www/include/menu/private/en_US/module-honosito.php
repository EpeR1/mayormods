<?php

	if (memberOf(_USERACCOUNT,'honosito')) {

    	    $MENU['honosito'] = array(array('txt' => 'Translator', 'url' => 'index.php?page=honosito&f=text'));

	    $MENU['modules']['honosito'] = array(
		'alert' => array(array('txt' => 'Translate messages')),
		'menu' => array(array('txt' => 'Translate menu')),
		'text' => array(array('txt' => 'Translate texts')),
	    );

	}

?>
