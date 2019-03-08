<?php
/*
    module: naplo
*/

        $NAVI[] = array('txt' => 'Hírnök', 'url' => 'index.php?page=naplo&sub=hirnok&f=hirnok', 'icon' => 'icon-bullhorn');
        $NAVI[] = array('txt' => 'Órarend', 'url' => 'index.php?page=naplo&sub=orarend&f=orarend', 'icon' => 'icon-th');
        $NAVI[] = array('txt' => 'Jegyek', 'url' => 'index.php?page=naplo&sub=osztalyozo&f=diak', 'icon' => 'icon-pencil');


   $MENU['naplo'] = array(
	array('txt' => 'Napló'),
//	array('txt' => 'Jelentkezés fogadóórára', 'url' => 'index.php?page=naplo&sub=tanev&f=fogadoOra'),
	array('txt' => 'Kezdőlap (diák választás)', 'url' => 'index.php?page=naplo&sub=&f=diakValaszto')
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

if ($page=='naplo') {

    if (__DIAK) {
	$MENU['modules']['naplo']['haladasi'] = array(array('txt' => 'Haladási napló', 'url' => 'index.php?page=naplo&sub=haladasi&f=haladasi'));
	$MENU['modules']['naplo']['osztalyozo'] = array(array('txt' => 'Osztályozó napló', 'url' => 'index.php?page=naplo&sub=osztalyozo&f=diak'));
	$MENU['modules']['naplo']['hianyzas'] =  array(array('txt' => 'Hiányzások', 'url' => 'index.php?page=naplo&sub=hianyzas&f=diak'));
	$MENU['modules']['naplo']['bejegyzesek'] =  array(array('txt' => 'Bejegyzések', 'url' => 'index.php?page=naplo&sub=bejegyzesek&f=bejegyzesek'));
	$MENU['modules']['naplo']['ertekeles'] =  array(array('txt' => 'Értékelés', 'url' => 'index.php?page=naplo&sub=ertekeles&f=ertekeles'));
        $MENU['modules']['naplo']['sub']['osztalyozo'] = array(
            'dolgozat' => array(array('txt' => 'Dolgozatok')),
            'bizonyitvany' => array(array('txt' => 'Bizonyítvány')),
            'stat' => array(array('txt' => 'Zárási statisztika')),
        );
	$MENU['modules']['naplo']['sub']['tanev'] = array(
	    'munkaterv' => array(array('txt' => 'Éves munkaterv', 'url' => 'index.php?page=naplo&sub=tanev&f=munkaterv')),
	    'fogadoOra' => array(array('txt' => 'Fogadóóra', 'url' => 'index.php?page=naplo&sub=tanev&f=fogadoOra'))
	);
	$MENU['modules']['naplo']['fogad'] = array(array('txt' => 'Fogadóóra', 'url' => 'index.php?page=naplo&sub=tanev&f=fogadoOra'));
	$MENU['modules']['naplo']['munkaterv'] = array(array('txt' => 'Éves munkaterv', 'url' => 'index.php?page=naplo&sub=tanev&f=munkaterv'));
	$MENU['modules']['naplo']['orarend'] =  array(array('txt' => 'Órarend', 'url' => 'index.php?page=naplo&sub=orarend&f=orarend'));
	$MENU['modules']['naplo']['intezmeny'] = array(
	    array('txt' => 'Diák adatlapja', 'url' => 'index.php?page=naplo&sub=intezmeny&f=diak'),
	    array('txt' => 'Tanévváltás', 'url' => 'index.php?page=naplo&sub=intezmeny&f=valtas')
	);
	$MENU['modules']['naplo']['sub']['haladasi'] = array(
	    'stat' => array(array('txt' => 'Haladási statisztika')),
	);
	if (__UZENO_INSTALLED===true)
	    $MENU['modules']['naplo']['uzeno'] = array(array('txt' => 'Üzenő', 'url' => 'index.php?page=naplo&sub=uzeno&f=uzeno'));
	$MENU['modules']['naplo']['hirnok'] = array(array('txt' => 'Hírnök', 'url' => 'index.php?page=naplo&sub=hirnok&f=hirnok'));
	$MENU['modules']['naplo']['valasztas'] = array(array('txt' => 'Kezdőlap (diák választás)', 'url' => 'index.php?page=naplo&sub=&f=diakValaszto'));

    }

    // Navigáció - alapértelmezés
    array_unshift($NAV[1], array('page' => 'naplo', 'sub' => 'orarend'));
    array_unshift($NAV[1], array('page' => 'naplo', 'sub' => 'hianyzas'));
    array_unshift($NAV[1], array('page' => 'naplo', 'sub' => 'osztalyozo'));
    array_unshift($NAV[1], array('page' => 'naplo', 'sub' => 'haladasi'));

    if (is_array($MENU['modules']['naplo']['sub'][$sub])) foreach ($MENU['modules']['naplo']['sub'][$sub] as $_f => $M) {
        $NAV[2][] = array('page' => 'naplo', 'sub' => $sub, 'f' => $_f);
    } elseif (is_array($MENU['modules']['naplo']))  foreach ($MENU['modules']['naplo'] as $_f => $M)    {
        if ($_f != 'sub') $NAV[2][] = array('page' => 'naplo', 'sub' => $_f);
    }
}


?>
