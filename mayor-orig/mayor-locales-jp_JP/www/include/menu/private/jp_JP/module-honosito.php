<?php


	if (memberOf(_USERACCOUNT,'honosito')) {

    	$MENU['honosito'] = array(array('txt' => 'Honosító', 'url' => 'index.php?page=honosito&f=text'));

		$MENU['modules']['honosito'] = array(
			'alert' => array(array('txt' => '翻訳メッセージ')),
			'menu' => array(array('txt' => '翻訳メニュー')),
			'text' => array(array('txt' => 'テキスト翻訳')),
		);

	}

?>
