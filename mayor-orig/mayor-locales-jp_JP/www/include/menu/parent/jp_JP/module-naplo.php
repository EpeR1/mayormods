<?php
/*
    module: naplo
*/

   $MENU['naplo'] = array(
	array('txt' => 'ログ',	'url' => 'index.php?page=naplo&f=diakValaszto'),
	array('txt' => 'サインオン相談', 'url' => 'index.php?page=naplo&sub=tanev&f=fogadoOra')
    );

    // A menüpontok sorrendjének beállítása - ettől még nem jelenik meg semmi :)
    $MENU['modules']['naplo'] = array(
	'haladasi' => array(),
	'osztalyozo' => array(),
	'hianyzas' => array(),
	'bejegyzesek' => array(),
	'tanev' => array(),
	'intezmeny' => array(),
	'admin' => array(),
    );

    if (__DIAK) {
	$MENU['modules']['naplo']['haladasi'] = array(array('txt' => 'ログの進展', 'url' => 'index.php?page=naplo&sub=haladasi&f=haladasi'));
	$MENU['modules']['naplo']['osztalyozo'] = array(array('txt' => 'ロググレード', 'url' => 'index.php?page=naplo&sub=osztalyozo&f=diak'));
	$MENU['modules']['naplo']['hianyzas'] =  array(array('txt' => 'Hiányzási ログ', 'url' => 'index.php?page=naplo&sub=hianyzas&f=diak'));
	$MENU['modules']['naplo']['bejegyzesek'] =  array(array('txt' => 'エントリ', 'url' => 'index.php?page=naplo&sub=bejegyzesek&f=bejegyzesek'));
        $MENU['modules']['naplo']['sub']['osztalyozo'] = array(
            'dolgozat' => array(array('txt' => 'テスト')),
        );
	$MENU['modules']['naplo']['tanev'] = array(
	    array('txt' => '時刻表', 'url' => 'index.php?page=naplo&sub=tanev&f=orarend'),
	    array('txt' => 'Munkaterv', 'url' => 'index.php?page=naplo&sub=tanev&f=munkaterv')
	);
	$MENU['modules']['naplo']['intezmeny'] = array(array('txt' => 'Tanévváltás', 'url' => 'index.php?page=naplo&sub=intezmeny&f=valtas'));
	$MENU['modules']['naplo']['sub']['haladasi'] = array(
	    'stat' => array(array('txt' => '進行中の統計情報')),
	);
    }
?>
