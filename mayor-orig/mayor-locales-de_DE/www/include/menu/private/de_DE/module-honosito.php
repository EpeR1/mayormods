<?php


	if (memberOf(_USERACCOUNT,'honosito')) {

    	$MENU['honosito'] = array(array('txt' => 'Übersetzer', 'url' => 'index.php?page=honosito&f=text'));

		$MENU['modules']['honosito'] = array(
			'alert' => array(array('txt' => 'Übersetzung der Nachricht')),
			'menu' => array(array('txt' => 'Übersetzung des Menüs')),
			'text' => array(array('txt' => 'Übersetzung der Texte')),
		);

	}

?>
