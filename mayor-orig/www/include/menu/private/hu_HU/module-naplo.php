<?php
/*
    module: naplo
*/
    $MENU['naplo'] = array(array('txt' => 'Napló',	'url' => 'index.php?page=naplo'));

    if (__DIAK === true) {
	$NAVI[] = array('txt' => 'Hírnök', 'url' => 'index.php?page=naplo&sub=hirnok&f=hirnok', 'icon' => 'icon-bullhorn');
	$NAVI[] = array('txt' => 'Órarend', 'url' => 'index.php?page=naplo&sub=orarend&f=orarend', 'icon' => 'icon-th');
	$NAVI[] = array('txt' => 'Jegyek', 'url' => 'index.php?page=naplo&sub=osztalyozo&f=diak', 'icon' => 'icon-pencil');
    } elseif (__TANAR === true) {
	$NAVI[] = array('txt' => 'Hírnök', 'url' => 'index.php?page=naplo&sub=hirnok&f=hirnok', 'icon' => 'icon-bullhorn');
	$NAVI[] = array('txt' => 'Órarend', 'url' => 'index.php?page=naplo&sub=orarend&f=orarend', 'icon' => 'icon-th');
	$NAVI[] = array('txt' => 'Osztályozó', 'url' => 'index.php?page=naplo&sub=osztalyozo&f=tankor', 'icon' => 'icon-pencil');
    } else {
	$NAVI[] = array('txt' => 'Hírnök', 'url' => 'index.php?page=naplo&sub=hirnok&f=hirnok', 'icon' => 'icon-bullhorn');
	$NAVI[] = array('txt' => 'Órarend', 'url' => 'index.php?page=naplo&sub=orarend&f=orarend', 'icon' => 'icon-th');
    }


    // A menüpontok sorrendjének beállítása - ettől még nem jelenik meg semmi :)
    $MENU['modules']['naplo'] = array(
	'haladasi' => array(),
	'osztalyozo' => array(),
	'hianyzas' => array(),
	'bejegyzesek' => array(),
	'orarend' => array(),
	'tanev' => array(),
	'intezmeny' => array(),
	'stat' => array(),
	'ertekeles' => array(),
	'admin' => array(),
    );

    /* Kiegészítő beállítások egy oldalhoz */
    /* Contrib */
    // $MENU['set']['private']['naplo']['tanev']['helyettesites']['refresh'] = 60;
    /* -- */

    if (__DIAK) {
	$MENU['modules']['naplo']['haladasi'] = array(array('txt' => 'Haladási napló', 'url' => 'index.php?page=naplo&sub=haladasi'));
	$MENU['modules']['naplo']['osztalyozo'] = array(array('txt' => 'Osztályozó napló', 'url' => 'index.php?page=naplo&sub=osztalyozo'));
	$MENU['modules']['naplo']['hianyzas'] =  array(array('txt' => 'Hiányzások', 'url' => 'index.php?page=naplo&sub=hianyzas'));
	$MENU['modules']['naplo']['bejegyzesek'] =  array(array('txt' => 'Bejegyzések', 'url' => 'index.php?page=naplo&sub=bejegyzesek'));
	$MENU['modules']['naplo']['diakTankorJelentkezes'] = array(array('txt'=>   'Tankörjelentkezés', 'url'=> 'index.php?page=naplo&sub=intezmeny&f=diakTankorJelentkezes'));
	$MENU['modules']['naplo']['esemeny'] =  array(array('txt' => 'Esemény jelentkezés', 'url' => 'index.php?page=naplo&sub=esemeny'));
	$MENU['modules']['naplo']['diakFelmentes'] =  array(array('txt' => 'Felmentés', 'url' => 'index.php?page=naplo&sub=intezmeny&f=diakFelmentes'));
	$MENU['modules']['naplo']['ertekeles'] =  array(array('txt' => 'Értékelés', 'url' => 'index.php?page=naplo&sub=ertekeles'));
	$MENU['modules']['naplo']['hibabejelento'] =  array(array('txt' => 'Kérelmek', 'url' => 'index.php?page=naplo&sub=hibabejelento&f=admin'));
	$MENU['modules']['naplo']['koszi'] =  array(array('txt' => 'Köszi', 'url' => 'index.php?page=naplo&sub=koszi'));
	$MENU['modules']['naplo']['hirnok'] =  array(array('txt' => 'Hírnök', 'url' => 'index.php?page=naplo&sub=hirnok&f=hirnok'));

	$MENU['modules']['naplo']['sub']['hirnok'] =  array(
	    'hirnok' =>array(array('txt' => 'Hírnök', 'icon'=>'icon-bullhorn')),
	    'hirnokFeliratkozas' => array(array('txt' => 'Hírnök beállítás', 'icon'=>'icon-cog'))
	);
	$MENU['modules']['naplo']['sub']['osztalyozo'] = array(
	    'dolgozat' => array(array('txt' => 'Dolgozatok')),
	    'szovegesErtekeles' => array(array('txt' => 'Szöveges értékelés')),
	    'bizonyitvany' => array(array('txt' => 'Bizonyítvány')),
	    'stat' => array(array('txt' => 'Zárási statisztika')),
	);
	$MENU['modules']['naplo']['orarend'] = array(
	    array('txt' => 'Órarend', 'url' => 'index.php?page=naplo&sub=orarend'),
	    array('txt' => 'Szabad termek', 'url' => 'index.php?page=naplo&sub=orarend&f=szabadTerem'),
	);
	$MENU['modules']['naplo']['tanev'] = array(
	    array('txt' => 'Munkaterv', 'url' => 'index.php?page=naplo&sub=tanev&f=munkaterv')
	);
	$MENU['modules']['naplo']['sub']['hibabejelento'] = array(
	    'hibabejelento' => array(array('txt' => 'Új kérelem a hangyának')),
	);
	$MENU['modules']['naplo']['intezmeny'] = array(array('txt' => 'Tanévváltás', 'url' => 'index.php?page=naplo&sub=intezmeny'));

	$MENU['modules']['naplo']['sub']['haladasi'] = array(
	    'hetes' => array(array('txt' => 'Hetesek')),
	    'stat' => array(array('txt' => 'Haladási statisztika')),
	);
	if(__UZENO_INSTALLED===true)
	    $MENU['modules']['naplo']['uzeno'] =  array(array('txt' => 'Üzenő', 'url' => 'index.php?page=naplo&sub=uzeno&f=uzeno'));
	$MENU['modules']['naplo']['sub']['koszi'] = array(
	    'koszi'=> array(array('txt' => 'Jelentkezés', 'url' => 'index.php?page=naplo&sub=koszi&f=koszi')),
	    'kosziAdmin'=>array(array('txt' => 'Pont jóváhagyás', 'url' => 'index.php?page=naplo&sub=koszi&f=kosziAdmin')),
	    'esemenyAdmin'=>array(array('txt' => 'Esemény adminisztráció', 'url' => 'index.php?page=naplo&sub=koszi&f=esemenyAdmin')),
	);

    } elseif (__TANAR) {
	$MENU['modules']['naplo']['haladasi'] = array(array('txt' => 'Haladási napló', 'url' => 'index.php?page=naplo&sub=haladasi'));
	$MENU['modules']['naplo']['osztalyozo'] = array(array('txt' => 'Osztályozó napló', 'url' => 'index.php?page=naplo&sub=osztalyozo'));
	$MENU['modules']['naplo']['hianyzas'] = array(array('txt' => 'Hiányzások', 'url' => 'index.php?page=naplo&sub=hianyzas'));
	$MENU['modules']['naplo']['bejegyzesek'] =  array(array('txt' => 'Bejegyzések', 'url' => 'index.php?page=naplo&sub=bejegyzesek'));
	$MENU['modules']['naplo']['tanmenet'] =  array(array('txt' => 'Tanmenetek', 'url' => 'index.php?page=naplo&sub=tanmenet'));
	$MENU['modules']['naplo']['intezmeny'][] = array('txt' => 'Intézményi adatok', 'url' => 'index.php?page=naplo&sub=intezmeny');
	$MENU['modules']['naplo']['hibabejelento'] =  array(array('txt' => 'Kérelmek', 'url' => 'index.php?page=naplo&sub=hibabejelento&f=admin'));
	$MENU['modules']['naplo']['ertekeles'] =  array(array('txt' => 'Értékelés', 'url' => 'index.php?page=naplo&sub=ertekeles'));
	$MENU['modules']['naplo']['orarend'] = array(array('txt' => 'Órarend', 'url' => 'index.php?page=naplo&sub=orarend'));
	$MENU['modules']['naplo']['esemeny'] =  array(array('txt' => 'Események', 'url' => 'index.php?page=naplo&sub=esemeny'));
	$MENU['modules']['naplo']['nyomtatas'][] = array('txt' => 'Nyomtatás', 'url' => 'index.php?page=naplo&sub=nyomtatas');
	$MENU['modules']['naplo']['tanev'] = array(
	    array('txt' => 'Munkaterv', 'url' => 'index.php?page=naplo&sub=tanev&f=munkaterv'),
	    array('txt' => 'Fogadóóra', 'url' => 'index.php?page=naplo&sub=tanev&f=fogadoOra'),
	);
	$MENU['modules']['naplo']['sub']['ertekeles'] = array(
	    'osszesites' => array(array('txt' => 'Összesítés')),
	);
	$MENU['modules']['naplo']['sub']['intezmeny'] = array(
	    'tankorDiak' => array(array('txt' => 'Tankörnévsor', 'url' => 'index.php?page=naplo&sub=intezmeny&f=tankorDiak')),
	    'diakTankor' => array(array('txt' => 'Diák tankörei', 'url'=> 'index.php?page=naplo&sub=intezmeny&f=diakTankor')),
	    'diakTankorJelentkezes' => array(array('txt' => 'Diák választott tankörei', 'url'=> 'index.php?page=naplo&sub=intezmeny&f=diakTankorJelentkezes')),
	    'diak' => array(array('txt' => 'Diákok', 'url' => 'index.php?page=naplo&sub=intezmeny&f=diak')),
	    'diakFelmentes' => array(array('txt' => 'Felmentés', 'url' => 'index.php?page=naplo&sub=intezmeny&f=diakFelmentes')),
	    'valtas'=> array(array('txt' => 'Tanévváltás', 'url' => 'index.php?page=naplo&sub=intezmeny&f=valtas')),
	    'tovabbkepzes' => array(array('txt' => 'Továbbképzés')),
	);
	$MENU['modules']['naplo']['sub']['orarend'] = array(
	    'helyettesites' => array(array('txt'=> 'Helyettesítések')),
	    'szabadTerem' => array(array('txt' => 'Szabad termek' /*, 'url' => 'index.php?page=naplo&sub=tanev&f=szabadTerem' */)),
	    'tanarOrarend' => array(array('txt' => 'Speciális összesített tanári órarend (xls)')),
	);
	$MENU['modules']['naplo']['sub']['tanev'] = array(
	    'diakTanarai' => array(array('txt' => 'Diák tanára')),
	);
	$MENU['modules']['naplo']['sub']['hibabejelento'] = array(
	    'hibabejelento' => array(array('txt' => 'Új kérelem a hangyának')),
	);
	$MENU['modules']['naplo']['sub']['osztalyozo'] = array(
	    'tankor' => array(array('txt' => 'Tankör osztályzatai')),
	    'diak' => array(array('txt' => 'Diák osztályzatai')),
	    'dolgozat' => array(array('txt' => 'Dolgozatok')),
	    'szovegesErtekeles' => array(array('txt' => 'Szöveges értékelés')),
	    'bizonyitvany' => array(array('txt' => 'Bizonyítvány')),
	    'stat' => array(array('txt' => 'Zárási statisztika')),
	);
	if (__OSZTALYFONOK === true) $MENU['modules']['naplo']['sub']['osztalyozo']['targySorrend'] = array(array('txt' => 'Tárgysorrend'));
	$MENU['modules']['naplo']['sub']['bejegyzesek'] = array(
	    'bejegyzesek' => array(array('txt' => 'Bejegyzések listája')),
	    'ujBejegyzes' => array(array('txt' => 'Új bejegyzések')),
	);
	$MENU['modules']['naplo']['sub']['esemeny'] = array(
	    'ujEsemeny' => array(array('txt' => 'Esemény felvétel/módosítás')),
	    'esemenyDiak' => array(array('txt' => 'Jelentkezők kezelése')),
	);
	$MENU['modules']['naplo']['sub']['tanmenet'] = array(
	    'tanmenetTankor' => array(array('txt' => 'Tanmenetek tankörhöz rendelése')),
	    'tanmenetJovahagyas' => array(array('txt' => 'Tanmenet jóváhagyás')),
	    'tanmenetInfo' => array(array('txt' => 'Tanmenet részletei')),
	    'tanmenetModositas' => array(array('txt' => 'Tanmenet módosítása')),
	    'ujTanmenet' => array(array('txt' => 'Új tanmenet létrehozása')),
	);
	$MENU['modules']['naplo']['sub']['haladasi'] = array(
	    'hetes' => array(array('txt' => 'Hetesek')),
	    'stat' => array(array('txt' => 'Haladási statisztika')),
	    'elszamolas' => array(array('txt' => 'Munkaidő')),
	);
	$MENU['modules']['naplo']['sub']['hianyzas'] = array(
	    'osztaly' => array(array('txt' => 'Osztály hiányzásösszesítő')),
	    'osztalyNap' => array(array('txt' => 'Osztály napi hiányzásösszesítő')),
	    'diak' => array(array('txt' => 'Diák hiányzásai (naptár nézet)')),
	    'diakLista' => array(array('txt' => 'Diák hiányzásai (lista)')),
	    'diakIgazolatlan' => array(array('txt' => 'Diák igazolatlanjai (lista)')),
	    'info' => array(array('txt' => 'Statisztika')),
	);
	$MENU['modules']['naplo']['sub']['nyomtatas'] = array(
	    'osztalyozonaplo' => array(array('txt' => 'Osztályozónapló nyomtatása')),
	    'torzslap' => array(array('txt'=>'Törzslap')),
	    'helyettesitesinaplo' => array(array('txt' => 'Helyettesítési-napló nyomtatása')),
	    'ertesito' => array(array('txt' => 'Értesítő'))
	);
	if(__UZENO_INSTALLED===true)
	    $MENU['modules']['naplo']['uzeno'] =  array(array('txt' => 'Üzenő', 'url' => 'index.php?page=naplo&sub=uzeno&f=uzeno'));

	$MENU['modules']['naplo']['hirnok'] =  array(array('txt' => 'Hírnök', 'url' => 'index.php?page=naplo&sub=hirnok&f=hirnok'));

	$MENU['modules']['naplo']['koszi'] =  array(array('txt' => 'Köszi', 'url' => 'index.php?page=naplo&sub=koszi&f=kosziAdmin'));
	$MENU['modules']['naplo']['sub']['hirnok'] =  array(
	    'hirnok' =>array(array('txt' => 'Hírnök', 'icon'=>'icon-bullhorn')),
	    'hirnokFeliratkozas' => array(array('txt' => 'Hírnök beállítás', 'icon'=>'icon-cog'))
	);


    } elseif (__TITKARSAG === true) {

/* Titkárság */

	$MENU['modules']['naplo']['haladasi'] = array(array('txt' => 'Haladási napló', 'url' => 'index.php?page=naplo&sub=haladasi'));
	$MENU['modules']['naplo']['osztalyozo'] = array(array('txt' => 'Osztályozó napló', 'url' => 'index.php?page=naplo&sub=osztalyozo'));
	$MENU['modules']['naplo']['hianyzas'] = array(array('txt' => 'Hiányzások', 'url' => 'index.php?page=naplo&sub=hianyzas'));
	$MENU['modules']['naplo']['orarend'] = array(array('txt' => 'Órarend', 'url' => 'index.php?page=naplo&sub=orarend'));
	$MENU['modules']['naplo']['hibabejelento'] =  array(array('txt' => 'Kérelmek', 'url' => 'index.php?page=naplo&sub=hibabejelento&f=admin'));
	$MENU['modules']['naplo']['nyomtatas'][] = array('txt' => 'Nyomtatás', 'url' => 'index.php?page=naplo&sub=nyomtatas');
	$MENU['modules']['naplo']['export'][] = array('txt' => 'Export', 'url' => 'index.php?page=naplo&sub=export&f=diakExport');
	$MENU['modules']['naplo']['stat'][] = array('txt' => 'Statisztika', 'url' => 'index.php?page=naplo&sub=export&f=letszam');
	$MENU['modules']['naplo']['tanev'] = array(
	    array('txt' => 'Munkaterv', 'url' => 'index.php?page=naplo&sub=tanev&f=munkaterv'),
	    array('txt' => 'Fogadóóra', 'url' => 'index.php?page=naplo&sub=tanev&f=fogadoOra'),
	);
	$MENU['modules']['naplo']['intezmeny'] = array(array('txt' => 'Intézményi adatok', 'url' => 'index.php?page=naplo&sub=intezmeny'));
	$MENU['modules']['naplo']['sub']['orarend'] = array(
	    'helyettesites' => array(array('txt' => 'Helyettesítés')),
	    'szabadTerem' => array(array('txt' => 'Szabad termek' )),
	);
	$MENU['modules']['naplo']['sub']['tanev'] = array(
	    'diakTanarai' => array(array('txt' => 'Diák tanára')),
	);
	$MENU['modules']['naplo']['sub']['hibabejelento'] = array(
	    'hibabejelento' => array(array('txt' => 'Új kérelem a hangyának')),
	);
	$MENU['modules']['naplo']['sub']['osztalyozo'] = array(
	    'tankor' => array(array('txt' => 'Tankör osztályzatai')),
	    'diak' => array(array('txt' => 'Diák osztályzatai')),
	    'dolgozat' => array(array('txt' => 'Dolgozatok')),
	    'bizonyitvany' => array(array('txt' => 'Bizonyítvány')),
	    'stat' => array(array('txt' => 'Zárási statisztika')),
	    'targySorrend' => array(array('txt' => 'Tárgysorrend')),
	);
	$MENU['modules']['naplo']['sub']['haladasi'] = array(
	    'stat' => array(array('txt' => 'Haladási statisztika')),
//	    'elszamolas' => array(array('txt' => 'Elszámolás')),
	);
	$MENU['modules']['naplo']['sub']['intezmeny'] = array(
	    'valtas' => array(array('txt' => 'Intézményváltás')),
	    'diak' => array(array('txt' => 'Diákok')),
	    'zaradek' => array(array('txt' => 'Záradék')),
	);
	$MENU['modules']['naplo']['sub']['export'] = array(
	    'diakExport' => array(array('txt' => 'Diak adatok')),
	    'tantargyFelosztas' => array(array('txt' => 'Tantárgyfelosztás export')),
	);
	$MENU['modules']['naplo']['sub']['hianyzas'] = array(
	    'info' => array(array('txt' => 'Statisztika')),
	    'oktstat' => array(array('txt' => 'Októberi statisztika')),
	    'osztaly' => array(array('txt' => 'Osztály hiányzásösszesítő')),
	);
	$MENU['modules']['naplo']['sub']['nyomtatas'] = array(
	    'tankorNaplohozRendeles' => array(array('txt' => 'Tankör naplóhozrendelése')),
	    'osztalyozonaplo' => array(array('txt' => 'Osztályozónapló nyomtatása')),
	    'haladasinaplo' => array(array('txt' => 'Haladásinapló nyomtatása')),
	    'torzslap' => array(array('txt'=>'Törzslap')),
	    'zaradekok' => array(array('txt' => 'Záradékok, bejegyzések nyomtatása')),
	    'diakTankorJelentkezes' => array(array('txt' => 'Tankörjelentkezés')),
	    'ertesito' => array(array('txt' => 'Értesítő', 'rejtett' => true)),
	    'szovegesErtekeles' => array(array('txt' => 'Szöveges értékelés', 'rejtett' => true)),
	);

	$MENU['modules']['naplo']['sub']['stat'] = array(
	    'letszam' => array(array('txt'=>'Létszám statisztika')),
	    'lemorzsolodas' => array(array('txt'=>'Lemorzsolódás')),
	);


    }
    if (__VEZETOSEG===true) {

/* Vezetőség */

	$MENU['modules']['naplo']['haladasi'] = array(array('txt' => 'Haladási napló', 'url' => 'index.php?page=naplo&sub=haladasi'));
	$MENU['modules']['naplo']['osztalyozo'] = array(array('txt' => 'Osztályozó napló', 'url' => 'index.php?page=naplo&sub=osztalyozo'));
	$MENU['modules']['naplo']['hianyzas'] = array(array('txt' => 'Hiányzások', 'url' => 'index.php?page=naplo&sub=hianyzas'));
	$MENU['modules']['naplo']['bejegyzesek'] =  array(array('txt' => 'Bejegyzések', 'url' => 'index.php?page=naplo&sub=bejegyzesek'));
	$MENU['modules']['naplo']['tanmenet'] =  array(array('txt' => 'Tanmenetek', 'url' => 'index.php?page=naplo&sub=tanmenet'));
	$MENU['modules']['naplo']['nyomtatas'][] = array('txt' => 'Nyomtatás', 'url' => 'index.php?page=naplo&sub=nyomtatas');
	$MENU['modules']['naplo']['esemeny'] =  array(array('txt' => 'Események', 'url' => 'index.php?page=naplo&sub=esemeny'));
	$MENU['modules']['naplo']['tanev'][] = array('txt' => 'Tanév adatok', 'url' => 'index.php?page=naplo&sub=tanev');
	$MENU['modules']['naplo']['export'][] = array('txt' => 'Export', 'url' => 'index.php?page=naplo&sub=export&f=diakExport');
	$MENU['modules']['naplo']['ertekeles'] =  array(array('txt' => 'Értékelés', 'url' => 'index.php?page=naplo&sub=ertekeles'));
	$MENU['modules']['naplo']['hibabejelento'] =  array(array('txt' => 'Kérelmek', 'url' => 'index.php?page=naplo&sub=hibabejelento&f=admin'));

	$MENU['modules']['naplo']['intezmeny'] = array(array('txt' => 'Intézményi adatok', 'url' => 'index.php?page=naplo&sub=intezmeny'));

	$MENU['modules']['naplo']['stat'] = array(array('txt' => 'Statisztika', 'url' => 'index.php?page=naplo&sub=stat&f=letszam'));

	$MENU['modules']['naplo']['sub']['intezmeny'] = array (
	    'tankorDiak' => array(array('txt' => 'Tankörnévsor', 'url' => 'index.php?page=naplo&sub=intezmeny&f=tankorDiak')),
	    'tankorTanar' => array(array('txt' => 'Tankör tanárok', 'url' => 'index.php?page=naplo&sub=intezmeny&f=tankorTanar')),
	    'tankorTanarHozzarendeles' => array(array('txt' => 'Tantárgyfelosztás, tankör-tanár')),
	    'diak' => array(array('txt' => 'Diákok')),
	    'diakTankor' => array(array('txt' => 'Diák tankörei', 'url'=> 'index.php?page=naplo&sub=intezmeny&f=diakTankor')),
	    'diakTankorJelentkezes' => array(array('txt'=>  'Tankörjelentkezés', 'url'=> 'index.php?page=naplo&sub=intezmeny&f=diakTankorJelentkezes')),
	    'valtas' => array(array('txt' => 'Tanévváltás', 'url' => 'index.php?page=naplo&sub=intezmeny&f=valtas')),
	    'tankorCsoport' => array(array('txt' => 'Tankörcsoportok*', 'url' => 'index.php?page=naplo&sub=tanev&f=tankorCsoport')),
	    'tankorBlokk' => array(array('txt' => 'Tankörblokkok*', 'url' => 'index.php?page=naplo&sub=tanev&f=tankorBlokk')),
	    'diakFelmentes' => array(array('txt' => 'Diák felmentése', 'rejtett' => true)),
	    'zaradek' => array(array('txt' => 'Záradék')),
	    'tankorLetszam' => array(array('txt' => 'Tankörlétszámok', 'rejtett' => true)),
	    'kepesitesek' => array(array('txt' => 'Képesítések')),
	    'tovabbkepzes' => array(array('txt' => 'Továbbképzés')),
	);


	$MENU['modules']['naplo']['sub']['hibabejelento'] = array(
	    'hibabejelento' => array(array('txt' => 'Új kérelem a hangyának')),
	);
	$MENU['modules']['naplo']['sub']['haladasi'] = array(
	    'helyettesites' => array(array('txt' => 'Helyettesítés')),
	    'pluszora' => array(array('txt' => 'Plusz óra')),
	    'minuszora' => array(array('txt' => 'Óra elmaradás')),
	    'osszevonas' => array(array('txt' => 'Összevonás')),
	    'teremModositas' => array(array('txt' => 'Haladási teremmódosító')),
	    'specialis' => array(array('txt' => 'Speciális nap')),
	    'elmaradas' => array(array('txt' => 'Haladási elmaradások')),
	    'stat' => array(array('txt' => 'Haladási statisztika')),
	    'elszamolas' => array(array('txt' => 'Elszámolás')),
	    'oralatogatas' => array(array('txt' => 'Óralátogatás')),
	    'hetes' => array(array('txt' => 'Hetesek')),
	);
	$MENU['modules']['naplo']['sub']['osztalyozo'] = array(
	    'tankor' => array(array('txt' => 'Tankör osztályzatai')),
	    'diak' => array(array('txt' => 'Diák osztályzatai')),
	    'szovegesErtekeles' => array(array('txt' => 'Szöveges értékelés')),
	    'dolgozat' => array(array('txt' => 'Dolgozatok')),
	    'vizsga' => array(array('txt' => 'Vizsga')),
	    'bizonyitvany' => array(array('txt' => 'Bizonyítvány')),
	    'stat' => array(array('txt' => 'Zárási statisztika')),
	    'targySorrend' => array(array('txt' => 'Tárgysorrend')),
	);
	$MENU['modules']['naplo']['sub']['bejegyzesek'] = array(
	    'bejegyzesek' => array(array('txt' => 'Bejegyzések listája')),
	    'ujBejegyzes' => array(array('txt' => 'Új bejegyzések')),
	);
	$MENU['modules']['naplo']['sub']['esemeny'] = array(
	    'ujEsemeny' => array(array('txt' => 'Esemény felvétel/módosítás')),
	    'esemenyDiak' => array(array('txt' => 'Jelentkezők kezelése')),
	);
	$MENU['modules']['naplo']['sub']['tanmenet'] = array(
	    'tanmenetLeadas' => array(array('txt' => 'Tanmenet leadás')),
	    'tanmenetJovahagyas' => array(array('txt' => 'Tanmenet jóváhagyás')),
	    'tanmenetTankor' => array(array('txt' => 'Tanmenetek tankörhöz rendelése')),
	    'tanmenetInfo' => array(array('txt' => 'Tanmenet részletei')),
	    'tanmenetModositas' => array(array('txt' => 'Tanmenet módosítása')),
	    'ujTanmenet' => array(array('txt' => 'Új tanmenet létrehozása')),
	);	
	$MENU['modules']['naplo']['sub']['nyomtatas'] = array(
	    'tankorNaplohozRendeles' => array(array('txt' => 'Tankör naplóhozrendelése')),
	    'osztalyozonaplo' => array(array('txt' => 'Osztályozónapló nyomtatása')),
	    'haladasinaplo' => array(array('txt' => 'Haladásinapló nyomtatása')),
	    'torzslap' => array(array('txt'=>'Törzslap')),
	    'zaradekok' => array(array('txt' => 'Záradékok, bejegyzések nyomtatása')),
	    'helyettesitesinaplo' => array(array('txt' => 'Helyettesítési-napló nyomtatása')),
	    'diakTankorJelentkezes' => array(array('txt' => 'Tankörjelentkezés nyomtatvány')),
	    'ertesito' => array(array('txt' => 'Értesítő'))
	);
	$MENU['modules']['naplo']['sub']['export'] = array(
	    'bizonyitvany' => array(array('txt' => 'Bizonyítvány export')),
	    'kirBizonyitvanyExport' => array(array('txt' => 'Bizonyítvány export (KIR)')),
	    'diakExport' => array(array('txt' => 'Diak adatok')),
	    'tantargyFelosztas' => array(array('txt' => 'Tantárgyfelosztás export')),
	    'kreta' => array(array('txt' => 'KRÉTA export')),
	);

	
	$MENU['modules']['naplo']['sub']['orarend'] = array(
	    'orarend' => array(array('txt' => 'Órarend')),
	    'helyettesites' => array(array('txt'=> 'Helyettesítések')),
	    'orarendEllenorzes' => array(array('txt' => 'Órarend ellenőrzés')),
	    'orarendTeremModositas' => array(array('txt' => 'Teremmódosítás')),
	    'termez' => array(array('txt' => 'Órarend termező')),
	    'szabadTerem' => array(array('txt' => 'Szabad termek')),
	    'tanarOrarend' => array(array('txt' => 'Összesített tanári órarend')),
	    'ascExport' => array(array('txt' => 'ascExport')),
	);
	$MENU['modules']['naplo']['sub']['tanev'] = array(
	    'munkaterv' => array(array('txt' => 'Éves munkaterv, tanév rendje')),
	    'tankorCsoport' => array(array('txt' => 'Tankörcsoportok')),
	    'tankorBlokk' => array(array('txt' => 'Tankörblokkok')),
	    'fogadoOra' => array(array('txt' => 'Fogadóóra')),
	    'targyOraszam' => array(array('txt' => 'Tárgy óraszámok')),
	    'diakTanarai' => array(array('txt' => 'Diák tanára')),
	    'targyBontas' => array(array('txt' => 'Tantárgyfelosztás, bontás-tankör')),
	);
	$MENU['modules']['naplo']['sub']['ertekeles'] = array(
	    'kerdoivBetoltes' => array(array('txt' => 'Kérdőív betöltése')),
	    'osszesites' => array(array('txt' => 'Összesítés')),
	);
	$MENU['modules']['naplo']['sub']['hianyzas'] = array(
	    'osztaly' => array(array('txt' => 'Osztály hiányzásösszesítő')),
	    'osztalyNap' => array(array('txt' => 'Osztály napi hiányzásösszesítő')),
	    'diak' => array(array('txt' => 'Diák hiányzásai (naptár nézet)')),
	    'diakLista' => array(array('txt' => 'Diák hiányzásai (lista)')),
	    'oktstat' => array(array('txt' => 'Októberi statisztika')),
	    'info' => array(array('txt' => 'Statisztika')),

	);

	$MENU['modules']['naplo']['sub']['stat'] = array(
	    'tantargyFelosztas' => array(array('txt' => 'Tantárgyfelosztás statisztika')),
	    'letszam' => array(array('txt'=>'Létszám statisztika')),
	    'lemorzsolodas' => array(array('txt'=>'Lemorzsolódás')),
	);

    }
    if (__NAPLOADMIN === true) {
	$MENU['modules']['naplo']['haladasi'] = array(array('txt' => 'Haladási napló', 'url' => 'index.php?page=naplo&sub=haladasi'));
	$MENU['modules']['naplo']['osztalyozo'] = array(array('txt' => 'Osztályozó napló', 'url' => 'index.php?page=naplo&sub=osztalyozo'));
	$MENU['modules']['naplo']['hianyzas'] = array(array('txt' => 'Hiányzások', 'url' => 'index.php?page=naplo&sub=hianyzas'));
	$MENU['modules']['naplo']['bejegyzesek'] =  array(array('txt' => 'Bejegyzések', 'url' => 'index.php?page=naplo&sub=bejegyzesek'));
	$MENU['modules']['naplo']['orarend'] = array(array('txt' => 'Órarend', 'url' => 'index.php?page=naplo&sub=orarend'));
	$MENU['modules']['naplo']['tanmenet'] =  array(array('txt' => 'Tanmenetek', 'url' => 'index.php?page=naplo&sub=tanmenet'));
	$MENU['modules']['naplo']['esemeny'] =  array(array('txt' => 'Események', 'url' => 'index.php?page=naplo&sub=esemeny'));
	$MENU['modules']['naplo']['tanev'] = array(
	    array('txt' => 'Tanév adatok', 'url' => 'index.php?page=naplo&sub=tanev'),
	    array('txt' => 'Fogadóóra', 'url' => 'index.php?page=naplo&sub=tanev&f=fogadoOra'),
	);
	$MENU['modules']['naplo']['nyomtatas'][] = array('txt' => 'Nyomtatás', 'url' => 'index.php?page=naplo&sub=nyomtatas', 'rejtett' => true);
	$MENU['modules']['naplo']['export'][] = array('txt' => 'Export', 'url' => 'index.php?page=naplo&sub=export&f=diakExport');
	$MENU['modules']['naplo']['intezmeny'] = array(array('txt' => 'Intézményi adatok', 'url' => 'index.php?page=naplo&sub=intezmeny'));

	$MENU['modules']['naplo']['stat'] = array(array('txt' => 'Statisztika', 'url' => 'index.php?page=naplo&sub=stat&f=letszam'));
	$MENU['modules']['naplo']['ertekeles'] =  array(array('txt' => 'Értékelés', 'url' => 'index.php?page=naplo&sub=ertekeles', 'rejtett' => true));
	$MENU['modules']['naplo']['admin'] = array(array('txt' => 'Admin', 'url' => 'index.php?page=naplo&sub=admin'));
	$MENU['modules']['naplo']['koszi'] =  array(array('txt' => 'Köszi', 'url' => 'index.php?page=naplo&sub=koszi&f=kosziAdmin'));
	$MENU['modules']['naplo']['hibabejelento'] =  array(array('txt' => 'Kérelmek', 'url' => 'index.php?page=naplo&sub=hibabejelento&f=admin', 'rejtett' => true));
	$MENU['modules']['naplo']['hirnok'] =  array(array('txt' => 'Hírnök', 'url' => 'index.php?page=naplo&sub=hirnok&f=hirnok'));
	if(__UZENO_INSTALLED===true)
	    $MENU['modules']['naplo']['uzeno'] =  array(array('txt' => 'Üzenő', 'url' => 'index.php?page=naplo&sub=uzeno&f=uzeno'));

	$MENU['modules']['naplo']['sub']['stat'] = array(
	    'tantargyFelosztas' => array(array('txt' => 'Tantárgyfelosztás statisztika')),
	    'letszam' => array(array('txt'=>'Létszám statisztika')),
	    'lemorzsolodas' => array(array('txt'=>'Lemorzsolódás')),
	);

	$MENU['modules']['naplo']['sub']['haladasi'] = array(
	    'helyettesites' => array(array('txt' => 'Helyettesítés')),
	    'pluszora' => array(array('txt' => 'Plusz óra')),
	    'minuszora' => array(array('txt' => 'Óra elmaradás')),
	    'osszevonas' => array(array('txt' => 'Összevonás', 'rejtett' => true)),
	    'teremModositas' => array(array('txt' => 'Haladási teremmódosító')),
	    'specialis' => array(array('txt' => 'Speciális nap', 'rejtett' => true)),
	    'elmaradas' => array(array('txt' => 'Haladási elmaradások', 'rejtett' => true)),
	    'stat' => array(array('txt' => 'Haladási statisztika', 'rejtett' => true)),
	    'elszamolas' => array(array('txt' => 'Elszámolás', 'rejtett' => true)),
	    'oralatogatas' => array(array('txt' => 'Óralátogatás', 'rejtett' => true)),
	    'hetes' => array(array('txt' => 'Hetesek')),
	);
	$MENU['modules']['naplo']['sub']['hibabejelento'] = array(
	    'hibabejelento' => array(array('txt' => 'Új kérelem a hangyának')),
	);
	$MENU['modules']['naplo']['sub']['koszi'] = array(
	    // ez csak diákoknak van, nem? // 'koszi' => array(array('txt' => 'Köszi')),
	    'esemenyAdmin' => array(array('txt' => 'Esemény adminisztráció')),
	    'kosziAdmin' => array(array('txt' => 'Pont adminisztráció')),
	);
	$MENU['modules']['naplo']['sub']['hianyzas'] = array(
	    'osztaly' => array(array('txt' => 'Osztály hiányzásösszesítő')),
	    'osztalyNap' => array(array('txt' => 'Osztály napi hiányzásösszesítő')),
	    'diak' => array(array('txt' => 'Diák hiányzásai (naptár nézet)')),
	    'diakLista' => array(array('txt' => 'Diák hiányzásai (lista)')),
	    'diakIgazolatlan' => array(array('txt' => 'Diák igazolatlanjai (lista)')),
	    'oktstat' => array(array('txt' => 'Októberi statisztika')),
	    'info' => array(array('txt' => 'Statisztika')),

	);
	$MENU['modules']['naplo']['sub']['osztalyozo'] = array(
	    'tankor' => array(array('txt' => 'Tankör osztályzatai')),
	    'diak' => array(array('txt' => 'Diák osztályzatai', 'rejtett' => true)),
	    'dolgozat' => array(array('txt' => 'Dolgozatok', 'rejtett' => true)),
	    'vizsga' => array(array('txt' => 'Vizsga')),
	    'bizonyitvany' => array(array('txt' => 'Bizonyítvány', 'rejtett' => true)),
//	    'bizelomenetel' => array(array('txt' => 'Előmenetel', 'rejtett' => true)),
	    'zaroJegyCheck' => array(array('txt' => 'Zárójegy évfolyam ellenőrző')),
	    'stat' => array(array('txt' => 'Zárási statisztika')),
	    'targySorrend' => array(array('txt' => 'Tárgysorrend', 'rejtett' => true)),
	    'szovegesErtekeles' => array(array('txt' => 'Szöveges értékelés', 'rejtett' => true)),
	    'szempontRendszer' => array(array('txt' => 'Szempont rendszer', 'rejtett' => true)),
	);
	$MENU['modules']['naplo']['sub']['bejegyzesek'] = array(
	    'bejegyzesek' => array(array('txt' => 'Bejegyzések listája')),
	    'ujBejegyzes' => array(array('txt' => 'Új bejegyzések')),
	    'bejegyzesTipus' => array(array('txt' => 'Bejegyzés-típusok')),
	);
	$MENU['modules']['naplo']['sub']['tanmenet'] = array(
	    'tanmenetLeadas' => array(array('txt' => 'Tanmenet leadás')),
	    'tanmenetJovahagyas' => array(array('txt' => 'Tanmenet jóváhagyás')),
	    'tanmenetTankor' => array(array('txt' => 'Tanmenetek tankörhöz rendelése')),
	    'tanmenetInfo' => array(array('txt' => 'Tanmenet részletei')),
	    'tanmenetModositas' => array(array('txt' => 'Tanmenet módosítása')),
	    'ujTanmenet' => array(array('txt' => 'Új tanmenet létrehozása')),
	);
	$MENU['modules']['naplo']['sub']['esemeny'] = array(
	    'ujEsemeny' => array(array('txt' => 'Esemény felvétel/módosítás')),
	    'esemenyDiak' => array(array('txt' => 'Névsor kezelése')),
	);
	$MENU['modules']['naplo']['sub']['orarend'] = array(
	    'orarend' => array(array('txt' => 'Órarend')),
	    'szabadTerem' => array(array('txt' => 'Szabad termek', 'rejtett' => true)),
	    'helyettesites' => array(array('txt' => 'Helyettesítés', 'rejtett' => true)),
	    'orarendTankor' => array(array('txt' => 'Órarend-tankör összerendező', 'rejtett' => true)),
	    'orarendUtkozes' => array(array('txt' => 'Órarend ütközés', 'rejtett' => true)),
	    'orarendBetolto' => array(array('txt' => 'Órarend betöltő', 'rejtett' => true)),
	    'orarendEllenorzes' => array(array('txt' => 'Órarend ellenőrzés', 'rejtett' => true)),
	    'orarendTeremModositas' => array(array('txt' => 'Teremmódosítás', 'rejtett' => true)),
	    'orarendModosito' => array(array('txt' => 'Órarend módosítás', 'rejtett' => true)),
	    'termez' => array(array('txt' => 'Órarend termező', 'rejtett' => true)),
	    'tanarOrarend' => array(array('txt' => 'Összesített tanári órarend', 'rejtett' => true)),
	    'ascExport' => array(array('txt' => 'ascExport', 'rejtett' => true)),
	);
	$MENU['modules']['naplo']['sub']['tanev'] = array(
	    'munkaterv' => array(array('txt' => 'Éves munkaterv, tanév rendje', 'rejtett' => true)),
	    'checkStatus' => array(array('txt' => 'Ellenőr', 'rejtett' => true)),
	    'tankorCsoport' => array(array('txt' => 'Tankörcsoportok')),
	    'tankorBlokk' => array(array('txt' => 'Tankörblokkok')),
	    // 'vegzosTankorLezaras' => array(array('txt' => 'Végzős tankör kiléptetés', 'rejtett' => true)),
	    'vegzosOrarendLezaras' => array(array('txt' => 'Végzős órarend lezárása', 'rejtett' => true)),
	    'fogadoOra' => array(array('txt' => 'Fogadóóra', 'rejtett' => true)),
	    // 'intezmeny' => array(array('txt' => 'Intézményváltó', 'url' => 'index.php?page=naplo&sub=intezmeny&f=valtas')),
	    'targyOraszam' => array(array('txt' => 'Tárgy óraszámok', 'rejtett' => true)),
	    'diakTanarai' => array(array('txt' => 'Diák tanára', 'rejtett' => true)),
	    'targyBontas' => array(array('txt' => 'Tantárgyfelosztás, bontás-tankör')),
	);
	$MENU['modules']['naplo']['sub']['nyomtatas'] = array(
	    'tankorNaplohozRendeles' => array(array('txt' => 'Tankör naplóhozrendelése')),
	    'osztalyozonaplo' => array(array('txt' => 'Osztályozónapló nyomtatása')),
	    'haladasinaplo' => array(array('txt' => 'Haladásinapló nyomtatása')),
	    'torzslap' => array(array('txt'=>'Törzslap')),
	    'zaradekok' => array(array('txt' => 'Záradékok, bejegyzések nyomtatása')),
	    'helyettesitesinaplo' => array(array('txt' => 'Helyettesítési-napló nyomtatása')),
	    'diakTankorJelentkezes' => array(array('txt' => 'Tankörjelentkezés')),
	    'ertesito' => array(array('txt' => 'Értesítő', 'rejtett' => true)),
	    'szovegesErtekeles' => array(array('txt' => 'Szöveges értékelés', 'rejtett' => true)),
	);

	$MENU['modules']['naplo']['sub']['export'] = array(
	    'bizonyitvany' => array(array('txt' => 'Bizonyítvány export')),
	    'kirBizonyitvanyExport' => array(array('txt' => 'Bizonyítvány export (KIR)')),
	    'diakExport' => array(array('txt' => 'Diak adatok')),
	    'tantargyFelosztas' => array(array('txt' => 'Tantárgyfelosztás export')),
	    'sulix' => array(array('txt' => 'Együttműködés SuliXerverrel')),
	    'kreta' => array(array('txt' => 'KRÉTA export')),
	    'tanarOsztalyOraszam' => array(array('txt' => 'Tanár-Osztály óraszámok')),
	);
	$MENU['modules']['naplo']['sub']['intezmeny'] = array(
	    'valtas' => array(array('txt' => 'Intézményváltás', 'rejtett' => true)),
	    'osztaly' => array(array('txt' => 'Osztályok')),
	    'diak' => array(array('txt' => 'Diákok')),
	    'diakSzulo' => array(array('txt' => 'Szülő/Nevelő', 'rejtett' => true)),
	    'tanar' => array(array('txt' => 'Tanárok')),
	    'kepesitesek' => array(array('txt' => 'Képesítések')),
	    'tovabbkepzes' => array(array('txt' => 'Továbbképzés')),
	    'munkakozosseg' => array(array('txt' => 'Munkaközösségek, tárgyak', 'rejtett' => true)),
	    'tankor' => array(array('txt' => 'Tankörök')),
	    'tankorTanar' => array(array('txt' => 'Tankör tanárok')),
	    'tankorTanarHozzarendeles' => array(array('txt' => 'Tantárgyfelosztás, tankör-tanár')),
	    'tankorDiak' => array(array('txt' => 'Tankör diákok')),
	    'diakTankor' => array(array('txt' => 'Diák tankörei')),
	    'diakFelmentes' => array(array('txt' => 'Diák felmentése', 'rejtett' => true)),
	    'diakTankorJelentkezes' => array(array('txt' => 'Tankörjelentkezés', 'rejtett' => true)),
	    'tankorSzemeszter' => array(array('txt' => 'Tankör óraterve', 'rejtett' => true)),
	    'tankorLetszam' => array(array('txt' => 'Tankörlétszámok', 'rejtett' => true)),
	    'kepzes' => array(array('txt' => 'Képzések', 'rejtett' => true)),
	    'kepzesOraterv' => array(array('txt' => 'Képzés óraterv', 'rejtett' => true)),
	    'zaradek' => array(array('txt' => 'Záradék')),
	    'terem' => array(array('txt' => 'Terem')),
	    'verseny' => array(array('txt' => 'Verseny')),
	);
	$MENU['modules']['naplo']['sub']['ertekeles'] = array(
	    'kerdoivBetoltes' => array(array('txt' => 'Kérdőív betöltése')),
	    'osszesites' => array(array('txt' => 'Összesítés')),
	);
	$MENU['modules']['naplo']['sub']['admin'] = array(
		'intezmenyek' => array(array('txt' => 'Intézmények')),
		'tanevek' => array(array('txt' => 'Tanévek megnyitása, lezárása')),
		'szemeszterek' => array(array('txt' => 'Szemeszterek')),
		'fillhaladasi' => array(array('txt' => 'Haladási napló feltöltése')),
		'import' => array(array('txt' => 'Import')),
		'azonositok' => array(array('txt' => 'Azonosító generálás')),
		'szuloiAzonositok' => array(array('txt' => 'Szülői azonosítók generálása')),
		'rpcPrivilege' => array(array('txt' => 'RPC jogosultságok')),
	);
	$MENU['modules']['naplo']['sub']['hirnok'] =  array(
	    'hirnok' =>array(array('txt' => 'Hírnök', 'icon'=>'icon-bullhorn')),
	    'hirnokFeliratkozas' => array(array('txt' => 'Hírnök beállítás', 'icon'=>'icon-cog'))
	);

    }
    if (__UZENO_INSTALLED===true && __UZENOADMIN===true)
	    $MENU['modules']['naplo']['uzeno'] =  array(array('txt' => 'Üzenő', 'url' => 'index.php?page=naplo&sub=uzeno&f=uzeno'));

    // SNI
    if (__NAPLOADMIN || __VEZETOSEG || __TANAR) {
        $MENU['modules']['naplo']['sni'] = array(array('txt' => 'Egyéni fejlesztés', 'url' => 'index.php?page=naplo&sub=sni'));
        $MENU['modules']['naplo']['sub']['sni'] = array(
            'diakAllapot' => array(array('txt' => 'Belépő/kilépő állapot')),
            'fejlesztesiTerv' => array(array('txt' => 'Havi összegzés')),
            'tantargyiFeljegyzesek' => array(array('txt' => 'Tantárgyi feljegyzések')),
        );
        $MENU['modules']['naplo']['sub']['nyomtatas']['sniHaviJegyzokonyv'] = array(array('txt' => 'Havi jegyzőkönyv'));
        $MENU['modules']['naplo']['sub']['nyomtatas']['sniEvVegiJegyzokonyv'] = array(array('txt' => 'Év végi jegyzőkönyv'));
    }
    // SNI VEGE

if ($page==='naplo') {

    // Navigáció - alapértelmezés
    $NAV[1] = array(
	array('page' => 'naplo', 'sub' => 'haladasi'),
	array('page' => 'naplo', 'sub' => 'osztalyozo'),
	array('page' => 'naplo', 'sub' => 'hianyzas'),
	array('page' => 'naplo', 'sub' => 'orarend'),
    );
    if (__NAPLOADMIN) {
	$NAV[1][] = array('page' => 'naplo', 'sub' => 'intezmeny');
	$NAV[1][] = array('page' => 'naplo', 'sub' => 'admin');
	$NAV[1][] = array('page' => 'naplo', 'sub' => 'hibabejelento');
    }

    if (is_array($MENU['modules']['naplo']['sub'][$sub])) foreach ($MENU['modules']['naplo']['sub'][$sub] as $_f => $M) {
	$NAV[2][] = array('page' => 'naplo', 'sub' => $sub, 'f' => $_f);
    } elseif (is_array($MENU['modules']['naplo']))  foreach ($MENU['modules']['naplo'] as $_f => $M) 	{
	if ($_f != 'sub') $NAV[2][] = array('page' => 'naplo', 'sub' => $_f);
    }

}
?>