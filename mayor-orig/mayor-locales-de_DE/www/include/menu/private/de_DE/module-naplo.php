<?php
/*
    module: naplo
*/

   $MENU['naplo'] = array(
	array('txt' => '',	'url' => 'index.php?page=naplo')
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
	$MENU['modules']['naplo']['haladasi'] = array(array('txt' => '', 'url' => 'index.php?page=naplo&sub=haladasi&f=haladasi'));
	$MENU['modules']['naplo']['osztalyozo'] = array(array('txt' => '', 'url' => 'index.php?page=naplo&sub=osztalyozo&f=diak'));
	$MENU['modules']['naplo']['hianyzas'] =  array(array('txt' => '', 'url' => 'index.php?page=naplo&sub=hianyzas&f=diak'));
	$MENU['modules']['naplo']['bejegyzesek'] =  array(array('txt' => '', 'url' => 'index.php?page=naplo&sub=bejegyzesek&f=bejegyzesek'));
	$MENU['modules']['naplo']['hibabejelento'] =  array(array('txt' => '', 'url' => 'index.php?page=naplo&sub=hibabejelento&f=admin'));
	$MENU['modules']['naplo']['sub']['osztalyozo'] = array(
	    'dolgozat' => array(array('txt' => '')),
	);
	$MENU['modules']['naplo']['tanev'] = array(
	    array('txt' => '', 'url' => 'index.php?page=naplo&sub=tanev&f=orarend'),
	    array('txt' => '', 'url' => 'index.php?page=naplo&sub=tanev&f=szabadTerem'),
	    array('txt' => '', 'url' => 'index.php?page=naplo&sub=tanev&f=munkaterv')
	);
	$MENU['modules']['naplo']['sub']['hibabejelento'] = array(
	    'hibabejelento' => array(array('txt' => '')),
	);
	$MENU['modules']['naplo']['intezmeny'] = array(array('txt' => '', 'url' => 'index.php?page=naplo&sub=intezmeny&f=valtas'));
	$MENU['modules']['naplo']['sub']['haladasi'] = array(
	    'stat' => array(array('txt' => '')),
	);
    } elseif (__TANAR) {
	$MENU['modules']['naplo']['haladasi'] = array(array('txt' => '', 'url' => 'index.php?page=naplo&sub=haladasi&f=haladasi'));
	$MENU['modules']['naplo']['osztalyozo'] = array(array('txt' => '', 'url' => 'index.php?page=naplo&sub=osztalyozo&f=tankor'));
	$MENU['modules']['naplo']['hianyzas'] = array(array('txt' => '', 'url' => 'index.php?page=naplo&sub=hianyzas&f=osztaly'));
	$MENU['modules']['naplo']['bejegyzesek'] =  array(array('txt' => '', 'url' => 'index.php?page=naplo&sub=bejegyzesek&f=bejegyzesek'));
	$MENU['modules']['naplo']['hibabejelento'] =  array(array('txt' => '', 'url' => 'index.php?page=naplo&sub=hibabejelento&f=admin'));
	$MENU['modules']['naplo']['tanev'] = array(
	    array('txt' => '', 'url' => 'index.php?page=naplo&sub=tanev&f=orarend'),
	    array('txt' => '', 'url' => 'index.php?page=naplo&sub=tanev&f=szabadTerem'),
	    array('txt' => '', 'url' => 'index.php?page=naplo&sub=tanev&f=munkaterv'),
	    array('txt' => '', 'url' => 'index.php?page=naplo&sub=tanev&f=fogadoOra'),
	);
	$MENU['modules']['naplo']['intezmeny'] = array(
	    array('txt' => '', 'url' => 'index.php?page=naplo&sub=intezmeny&f=valtas'),
	    array('txt' => '', 'url' => 'index.php?page=naplo&sub=intezmeny&f=tankorDiak'),
	    array('txt' => '', 'url'=> 'index.php?page=naplo&sub=intezmeny&f=diakTankor'),
	);
	$MENU['modules']['naplo']['sub']['tanev'] = array(
	    'tanarOrarend' => array(array('txt' => '')),
	);
	$MENU['modules']['naplo']['sub']['hibabejelento'] = array(
	    'hibabejelento' => array(array('txt' => '')),
	);
	$MENU['modules']['naplo']['sub']['osztalyozo'] = array(
	    'tankor' => array(array('txt' => '')),
	    'diak' => array(array('txt' => '')),
	    'dolgozat' => array(array('txt' => '')),
	);
	$MENU['modules']['naplo']['sub']['bejegyzesek'] = array(
	    'bejegyzesek' => array(array('txt' => '')),
	    'ujBejegyzes' => array(array('txt' => '')),
	);
	$MENU['modules']['naplo']['sub']['haladasi'] = array(
	    'stat' => array(array('txt' => '')),
	);
    } elseif (__TITKARSAG) {
	$MENU['modules']['naplo']['haladasi'] = array(array('txt' => '', 'url' => 'index.php?page=naplo&sub=haladasi&f=haladasi'));
	$MENU['modules']['naplo']['osztalyozo'] = array(array('txt' => '', 'url' => 'index.php?page=naplo&sub=osztalyozo&f=tankor'));
	$MENU['modules']['naplo']['hianyzas'] = array(array('txt' => '', 'url' => 'index.php?page=naplo&sub=hianyzas&f=osztaly'));
	$MENU['modules']['naplo']['hibabejelento'] =  array(array('txt' => '', 'url' => 'index.php?page=naplo&sub=hibabejelento&f=admin'));
	$MENU['modules']['naplo']['tanev'] = array(
	    array('txt' => '', 'url' => 'index.php?page=naplo&sub=tanev&f=orarend'),
	    array('txt' => '', 'url' => 'index.php?page=naplo&sub=tanev&f=szabadTerem'),
	    array('txt' => '', 'url' => 'index.php?page=naplo&sub=tanev&f=munkaterv'),
	    array('txt' => '', 'url' => 'index.php?page=naplo&sub=tanev&f=fogadoOra'),
	);
	$MENU['modules']['naplo']['intezmeny'] = array(array('txt' => '', 'url' => 'index.php?page=naplo&sub=intezmeny&f=valtas'));
	$MENU['modules']['naplo']['sub']['tanev'] = array(
	    'tanarOrarend' => array(array('txt' => '')),
	);
	$MENU['modules']['naplo']['sub']['hibabejelento'] = array(
	    'hibabejelento' => array(array('txt' => '')),
	);
	$MENU['modules']['naplo']['sub']['osztalyozo'] = array(
	    'tankor' => array(array('txt' => '')),
	    'diak' => array(array('txt' => '')),
	    'dolgozat' => array(array('txt' => '')),
	);
	$MENU['modules']['naplo']['sub']['haladasi'] = array(
	    'stat' => array(array('txt' => '')),
	);
    }
    if (__VEZETOSEG) {
	$MENU['modules']['naplo']['haladasi'] = array(array('txt' => '', 'url' => 'index.php?page=naplo&sub=haladasi&f=haladasi'));
	$MENU['modules']['naplo']['osztalyozo'] = array(array('txt' => '', 'url' => 'index.php?page=naplo&sub=osztalyozo&f=tankor'));
	$MENU['modules']['naplo']['hianyzas'] = array(array('txt' => '', 'url' => 'index.php?page=naplo&sub=hianyzas&f=osztaly'));
	$MENU['modules']['naplo']['bejegyzesek'] =  array(array('txt' => '', 'url' => 'index.php?page=naplo&sub=bejegyzesek&f=bejegyzesek'));
	$MENU['modules']['naplo']['tanev'][] = array('txt' => '', 'url' => 'index.php?page=naplo&sub=tanev&f=munkaterv');
	$MENU['modules']['naplo']['hibabejelento'] =  array(array('txt' => '', 'url' => 'index.php?page=naplo&sub=hibabejelento&f=admin'));
	$MENU['modules']['naplo']['intezmeny'] = array(
	    array('txt' => '', 'url' => 'index.php?page=naplo&sub=intezmeny&f=tankorDiak'),
	    array('txt' => '', 'url' => 'index.php?page=naplo&sub=intezmeny&f=valtas'),
	    array('txt' => '', 'url'=> 'index.php?page=naplo&sub=intezmeny&f=diakTankor'),
	);
	$MENU['modules']['naplo']['sub']['hibabejelento'] = array(
	    'hibabejelento' => array(array('txt' => '')),
	);
	$MENU['modules']['naplo']['sub']['haladasi'] = array(
	    'helyettesites' => array(array('txt' => '')),
	    'pluszora' => array(array('txt' => '')),
	    'osszevonas' => array(array('txt' => '')),
	    'specialis' => array(array('txt' => '')),
	    'elmaradas' => array(array('txt' => '')),
	    'stat' => array(array('txt' => '')),
	    'elszamolas' => array(array('txt' => '')),
	);
	$MENU['modules']['naplo']['sub']['osztalyozo'] = array(
	    'tankor' => array(array('txt' => '')),
	    'diak' => array(array('txt' => '')),
	    'dolgozat' => array(array('txt' => '')),
	);
	$MENU['modules']['naplo']['sub']['bejegyzesek'] = array(
	    'bejegyzesek' => array(array('txt' => '')),
	    'ujBejegyzes' => array(array('txt' => '')),
	);
	$MENU['modules']['naplo']['sub']['tanev'] = array(
	    'orarend' => array(array('txt' => '')),
	    'szabadTerem' => array(array('txt' => '')),
	    'munkaterv' => array(array('txt' => '')),
	    'tankorCsoport' => array(array('txt' => '')),
	    'fogadoOra' => array(array('txt' => '')),
	    'tanarOrarend' => array(array('txt' => '')),
	);
    }
    if (__NAPLOADMIN) {
	$MENU['modules']['naplo']['haladasi'] = array(array('txt' => '', 'url' => 'index.php?page=naplo&sub=haladasi&f=haladasi'));
	$MENU['modules']['naplo']['osztalyozo'] = array(array('txt' => '', 'url' => 'index.php?page=naplo&sub=osztalyozo&f=tankor'));
	$MENU['modules']['naplo']['hianyzas'] = array(array('txt' => '', 'url' => 'index.php?page=naplo&sub=hianyzas&f=osztaly'));
	$MENU['modules']['naplo']['bejegyzesek'] =  array(array('txt' => '', 'url' => 'index.php?page=naplo&sub=bejegyzesek&f=bejegyzesek'));
	$MENU['modules']['naplo']['tanev'] = array(array('txt' => '', 'url' => 'index.php?page=naplo&sub=tanev&f=orarend'));
	$MENU['modules']['naplo']['tanev'][] = array('txt' => '', 'url' => 'index.php?page=naplo&sub=tanev&f=munkaterv');
	$MENU['modules']['naplo']['intezmeny'] = array(array('txt' => '', 'url' => 'index.php?page=naplo&sub=intezmeny&f=valtas'));
	$MENU['modules']['naplo']['admin'] = array(array('txt' => '', 'url' => 'index.php?page=naplo&sub=admin&f=import'));
	$MENU['modules']['naplo']['hibabejelento'] =  array(array('txt' => '', 'url' => 'index.php?page=naplo&sub=hibabejelento&f=admin'));

	$MENU['modules']['naplo']['sub']['haladasi'] = array(
	    'helyettesites' => array(array('txt' => '')),
	    'pluszora' => array(array('txt' => '')),
	    'osszevonas' => array(array('txt' => '')),
	    'specialis' => array(array('txt' => '')),
	    'elmaradas' => array(array('txt' => '')),
	    'stat' => array(array('txt' => '')),
	    'elszamolas' => array(array('txt' => '')),
	);
	$MENU['modules']['naplo']['sub']['hibabejelento'] = array(
	    'hibabejelento' => array(array('txt' => '')),
	);
	$MENU['modules']['naplo']['sub']['osztalyozo'] = array(
	    'tankor' => array(array('txt' => '')),
	    'diak' => array(array('txt' => '')),
	    'dolgozat' => array(array('txt' => '')),
	);
	$MENU['modules']['naplo']['sub']['bejegyzesek'] = array(
	    'bejegyzesek' => array(array('txt' => '')),
	    'ujBejegyzes' => array(array('txt' => '')),
	);
	$MENU['modules']['naplo']['sub']['tanev'] = array(
	    'orarend' => array(array('txt' => '')),
	    'szabadTerem' => array(array('txt' => '')),
	    'helyettesites' => array(array('txt' => '')),
	    'munkaterv' => array(array('txt' => '')),
	    'tankorCsoport' => array(array('txt' => '')),
	    'orarendTankor' => array(array('txt' => '')),
	    'orarendUtkozes' => array(array('txt' => '')),
	    'orarendLoad' => array(array('txt' => '')),
	    'fogadoOra' => array(array('txt' => '')),
	    'tanarOrarend' => array(array('txt' => '')),
	    // 'intezmeny' => array(array('txt' => '', 'url' => 'index.php?page=naplo&sub=intezmeny&f=valtas')),
	);
	$MENU['modules']['naplo']['sub']['intezmeny'] = array(
	    'valtas' => array(array('txt' => '')),
	    'osztaly' => array(array('txt' => '')),
	    'diak' => array(array('txt' => '')),
	    'tanar' => array(array('txt' => '')),
	    'munkakozosseg' => array(array('txt' => '')),
	    'tankor' => array(array('txt' => '')),
	    'tankorTanar' => array(array('txt' => '')),
	    'tankorDiak' => array(array('txt' => '')),
	    'diakTankor' => array(array('txt' => '')),
	    'tankorSzemeszter' => array(array('txt' => '')),
	);
	$MENU['modules']['naplo']['sub']['admin'] = array(
		'intezmenyek' => array(array('txt' => '')),
		'tanevek' => array(array('txt' => '')),
		'import' => array(array('txt' => '')),
		'azonositok' => array(array('txt' => '')),
	);
    }

?>
