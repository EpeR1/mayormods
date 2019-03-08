<?php

    $MENU = array(
	'home'=>array(),
	'session'=>array(),
	'naplo'=>array(),
	'portal'=>array(),
	'felveteli'=>array(),
	'forum'=>array(),
	'auth'=>array(),

    );

    if (defined('_SESSIONID') and _SESSIONID != '') {
	$MENU['session'] = array(
	    array('txt' => 'ログアウトする', 'url' => 'index.php?page=session&f=logout')
	);
    }
    $MENU['home'] = array(
	array('txt' => 'スタートページ', 'url' => 'index.php')
    );
    $MENU['auth'] = array(
	array('txt' => '親ログイン', 'url' => 'index.php?page=auth&f=login&toPolicy=parent'),
	array('txt' => 'ログイン', 'url' => 'index.php?page=auth&f=login&toPolicy=private')
    );
    $MENU['modules']['auth']['login'] = array(
	array('txt' => 'フォーラムログイン', 'url' => 'index.php?page=auth&f=login&toPolicy=public&toPSF=forum::forum'),
	array('txt' => '親ログイン', 'url' => 'index.php?page=auth&f=login&toPolicy=parent'),
	array('txt' => '保護されたページ','url' => 'index.php?page=auth&f=login&toPolicy=private'),
//	array('txt' => '登録','url' => 'index.php?page=session&f=createAccount&toPolicy=parent')
    );
//    $MENU['modules']['session']['createAccount'] = array(
//	array('txt' => '親の登録', 'url' => 'index.php?page=session&f=createAccount&toPolicy=parent'),
//	array('txt' => 'フォーラム登録', 'url' => 'index.php?page=session&f=createAccount&toPolicy=public'),
//    );

?>
