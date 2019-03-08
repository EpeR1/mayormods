<?php

    if (defined('_SESSIONID') and _SESSIONID != '') {
	$MENU['logout'] = array(
	    array(
		'txt' => '終',
		'url' => 'index.php?policy=public&page=session&f=logout',
		'get' => array('sessionID','skin','lang')
	    )
	);
	$MENU['session'] = array(
	    array(
		'txt' => 'ユーザーデータ',
		'url' => 'index.php?page=session&sub=search&f=searchAccount'
	    )
	);
    }
    $MENU['home'] = array(
	array('txt' => 'スタートページ', 'url' => 'index.php')
    );

    if (memberOf(_USERACCOUNT, $AUTH[_POLICY]['adminGroup'])) {
	$MENU['modules']['session'] = array(
	    'createAccount' => array(array('txt' => '新規ユーザー')),
	    'createGroup' => array(array('txt' => '新しいグループ')),
	    'searchAccount' => array(array('txt' => '検索ユーザー','url' => 'index.php?page=session&sub=search&f=searchAccount')),
	    'searchGroup' => array(array('txt' => 'グループ検索','url' => 'index.php?page=session&sub=search&f=searchGroup')),
	    'changeMyPassword' => array(array('txt' => 'パスワードを変更','url' => 'index.php?page=password&sub=&f=changeMyPassword&userAccount='._USERACCOUNT.'&policy=public&toPolicy=private','get' => array('skin','lang','sessionID'))),
//		'sub' => array(
//	    		'search' => array(
//			'searchAccount' => array(array('txt' => '検索ユーザー')),
//			'searchGroup' => array(array('txt' => 'グループ検索')),
//	    	)
//		)
	);
    } else {
	$MENU['modules']['session'] = array(
	    'searchAccount' => array(array('txt' => '検索ユーザー','url' => 'index.php?page=session&sub=search&f=searchAccount')),
	    'searchGroup' => array(array('txt' => 'グループ検索','url' => 'index.php?page=session&sub=search&f=searchGroup')),
	    'changeMyPassword' => array(array('txt' => 'パスワードを変更','url' => 'index.php?page=password&sub=&f=changeMyPassword&userAccount='._USERACCOUNT.'&policy=public&toPolicy=private','get' => array('skin','lang','sessionID'))),
	);
    }
?>
