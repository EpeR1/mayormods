<?php

    if (defined('_SESSIONID') and _SESSIONID != '') {
	$MENU['session'] = array(
	    array('txt' => 'ログアウトする', 'url' => 'index.php?page=session&f=logout')
	);
    }
    $MENU['forum'] = array(
	array('txt' => 'フォーラム', 'url' => 'index.php?page=forum&f=forum')
    );
    $MENU['modules']['forum']['forum'] = array(
	array('txt' => 'フォーラム', 'url' => 'index.php?page=forum&f=forum'),
	array('txt' => 'フォーラムログイン', 'url' => 'index.php?page=auth&f=login&toPSF=forum::forum&toPolicy=public'),
	array('txt' => 'ユーザー登録', 'url' => 'index.php?page=session&f=createAccount&toPolicy=public&toPSF=forum::forum')
    );

?>
