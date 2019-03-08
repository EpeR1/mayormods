<?php

    if (defined('_SESSIONID') and _SESSIONID != '') {
	$MENU['logout'] = array(
	    array(
		'txt' => 'ログアウトする',
		'url' => 'index.php?policy=public&page=session&f=logout',
		'get' => array('sessionID','skin','lang')
	    )
	);
    $MENU['session'] = array(
	    array(
		'txt' => 'ユーザープロフィール',
		'url' => 'index.php?page=password&sub=&f=changeMyPassword&userAccount='._USERACCOUNT.'&policy=public&toPolicy=parent',
		'get' => array('skin','lang','sessionID'),
	    )
	);
    }
    $MENU['modules']['session'] = array(
//	    'searchAccount' => array(array('txt' => '検索ユーザー','url' => 'index.php?page=session&sub=search&f=searchAccount')),
//	    'searchGroup' => array(array('txt' => 'グループ検索','url' => 'index.php?page=session&sub=search&f=searchGroup')),
	    'changeMyPassword' => array(array('txt' => 'パスワードを変更','url' => 'index.php?page=password&sub=&f=changeMyPassword&userAccount='._USERACCOUNT.'&policy=public&toPolicy=parent','get' => array('skin','lang','sessionID'))),
//		'sub' => array(
//	    		'search' => array(
//			'searchAccount' => array(array('txt' => '検索ユーザー')),
//			'searchGroup' => array(array('txt' => 'グループ検索')),
//	    	)
//		)
	);
    $MENU['home'] = array(
	array('txt' => 'スタートページ', 'url' => 'index.php')
    );

?>
