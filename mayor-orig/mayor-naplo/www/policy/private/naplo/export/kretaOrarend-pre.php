<?php
/*

    A sablon mezői:
	Hetirend:		az Adatszótárak/Hetirend típusai pontban megadott hetirendek közül lehet megadni (pl. Minden héten; A hét; B hét stb.)
	Nap: 			a nap megnevezése, ügyelve az elgépelésekre (kis- és nagybetű nem számít)
	Óra (adott napon belül): az óra sorszáma a tanítási napon belül (olyan sorszám legyen, amely szerepel az aktív csengetési rendben)
	Osztály: 		az osztály neve, ha az egész osztálynak, vagy egy osztálybontásnak tartunk órát (ha nincs ilyen nevű osztály a rendszerben, akkor ez rögzítésre kerül)
	Csoport: 		a csoport neve, csoport- vagy osztálybontás esetén (ha nincs ilyen nevű csoport a rendszerben, akkor ez rögzítésre kerül)
	Tantárgy: 		az óra tantárgya (ha nincs ilyen nevű tantárgy a rendszerben, akkor ez rögzítésre kerül)
	Tanár: 			az órát tartó pedagógus neve (az alkalmazottak listájában szerepelnie kell)
	Helyiség: 		a tanóra helyisége (ha nincs ilyen nevű helyiség a rendszerben, akkor ez rögzítésre kerül)


	- A Heti rend - a config_xyz.php-ben: $kretaHETIREND felsorolja az orarendiHet --> Kréta "Hetirend" típusú adatszótárának elemeit
	    a lekérdezés a hetek összegét veszi: 1 --> A hét, 2 --> B hét, 1+2=3 --> Minden hét
	- Minden tárgynál be van írva a kretaTargyNev
	- Feltételezzük, hogy minden tankör pontosan egy csoportba tartozik bele, s a csoportok a Kréta csoportoknak/osztályoknak megfelelők
	- A csoportok elnevezésében feltételezések:
	    - egész osztályos - csak az osztályjel alkotja a csoportnevet (nincs szóköz és aláhúzás karakter)	Pl: 11.a
	    - több osztályos - az érintett osztályok vesszővel felsoroltak (van benne vessző)			Pl: 9.a, 9.b tsf
	    - osztálybontás - osztályjel után szóközzel elválasztot csoportjel (van benne szóköz)		Pl: 7.a csop1
	- A terem leirasa a Krétabeli "Helyiség név" mezővel kezdődik, " - " után követheti bármi		Pl: 120Fi - Fizika előadó
	- A tanár neve a Krétabelivel pontosan egyező
*/
if (_RIGHTS_OK !== true) die();
if (!__NAPLOADMIN && !__VEZETOSEG) {
    $_SESSION['alert'] = 'page:insufficient_access';
} else {
    require_once('include/modules/naplo/share/osztaly.php');
    require_once('include/modules/naplo/share/munkakozosseg.php');
    require_once('include/modules/naplo/share/tanar.php');
    require_once('include/modules/naplo/share/targy.php');
    require_once('include/modules/naplo/share/file.php');

    $dt = $ADAT['dt'] = readVariable($_POST['dt'], 'date');
    if ($action == "kretaOrarendExport") {
	$ADAT['tanar'] = getTanarok(array('result'=>'assoc'));
	$ADAT['export'] = getOrarendAdat($ADAT);
    
        $ADAT['formatum'] = readVariable($_POST['formatum'], 'enum', null, array('csv','ods','xml'));
        if ($ADAT['formatum'] == 'xml') $ADAT['mime'] = 'application/vnd.ms-excel';
        if (isset($ADAT['formatum'])) {
            $file = _DOWNLOADDIR.'/private/naplo/export/kretaOrarend_'.date('Ymd');
            if (exportKretaOrarend($file, $ADAT)) {
                header('Location: '.location('index.php?page=session&f=download&download=true&dir=naplo/export/&file='.$file.'.'.$ADAT['formatum'].'&mimetype='.$ADAT['mime']));
            }
        }

    }

        $TOOL['datumSelect'] = array(
            'tipus'=>'sor', 'post'=>array('formatum'),
            'paramName' => 'dt', 
            'tolDt' => date('Y-m-d', strtotime('last Monday', strtotime($_TANEV['kezdesDt']))),
            'igDt' => $_TANEV['zarasDt'],
            'override' => true
        );
	getToolParameters();

}
