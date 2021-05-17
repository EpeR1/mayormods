<?php

if (in_array(__PORTAL_CODE,array('vmg','kos'))) {

    if (__PORTAL_CODE=='kos' || $sub == 'kos') {
	$MENU['felveteli'] = array(
    	    array('txt' => 'Felvételi (általános iskola)', 'url' => 'index.php?page=felveteli&sub=kos')
	);
	$MENU['modules']['felveteli'] = array(
//	    'kos' => array(array('txt'=>'Felvételi (általános iskola)', 'url'=>'index.php?page=felveteli&sub=kos')),
//    	    'jelentkezesilap' => array(array('txt' => 'Jelentkezési lap', 'url' => '')),
	    'vmg' => array(array('txt'=>'Felvételi (gimnázium)', 'url'=>'index.php?page=felveteli&f=felveteli')),
	);
    } else {
	$MENU['felveteli'] = array(
    	    array('txt' => 'Felvételi', 'url' => 'index.php?page=felveteli')
	);
	$MENU['modules']['felveteli'] = array(
    	    'felveteli' => array(array('txt' => 'Kiemelt adatok', 'url' => 'index.php?page=felveteli&f=felveteli')),
//	    'felveteli-gyik' => array(array('txt' => 'Gy.I.K.','icon'=>'icon-info-sign')),
	    'hatevfolyamos' => array(array('txt' => 'Hatévfolyamos szóbeli','icon'=>'icon-idea')),
	    'otevfolyamos' => array(array('txt' => 'Német- és spanyol nyelvi előkészítő','icon'=>'icon-hand-right')),
//	    'biologia' => array(array('txt' => 'Biológia','icon'=>'icon-hand-right')),
	    'enek' => array(array('txt' => 'Ének-zene','icon'=>'icon-hand-right')),
	    'fizika' => array(array('txt' => 'Fizika','icon'=>'icon-hand-right')),
	    'human' => array(array('txt' => 'Humán/Magyar','icon'=>'icon-hand-right')),
//	    'informatika' => array(array('txt' => 'Informatika','icon'=>'icon-hand-right')),
	    'matematika' => array(array('txt' => 'Matematika','icon'=>'icon-hand-right')),
//    	    'kozponti' => array(array('txt' => 'Központi eredmények', 'url' => 'index.php?page=felveteli&sub=&f=kozponti')),
//	    'pontszamito' => array(array('txt' => 'Pontszámító segéd', 'url' => 'index.php?page=felveteli&sub=&f=pontszamito')),
//    	    'szobeli' => array(array('txt' => 'Eredmények', 'url' => 'index.php?page=felveteli&sub=&f=szobeli')),
//    	    'nyiltlevel2016' => array(array('txt' => 'Nyílt levél 2016', 'url' => 'index.php?page=felveteli&sub=&f=nyiltlevel2014')),
//	    'kos' => array(array('txt'=>'Felvételi (általános iskola)', 'url'=>'index.php?page=felveteli&sub=kos')),
	);
    }
//echo '<pre style="margin-left: 140px; margin-top: 100px;">';
//var_dump(__PORTAL_CODE, (__PORTAL_CODE=='kos'), $MENU['felveteli']);
//echo '</pre>';
/*
//        array('txt' => 'Felvételi tájékoztató', 'url' => 'index.php?page=felveteli&sub=tajekoztato/20082009&f=1'),
    $MENU['modules']['felveteli']['felveteli/20082009'] = array(
        array('txt' => '1.', 'url' => 'index.php?page=felveteli&sub=tajekoztato/20082009&f=1'),
        array('txt' => '2.', 'url' => 'index.php?page=felveteli&sub=tajekoztato/20082009&f=2'),
        array('txt' => '3.', 'url' => 'index.php?page=felveteli&sub=tajekoztato/20082009&f=3'),
        array('txt' => '4.', 'url' => 'index.php?page=felveteli&sub=tajekoztato/20082009&f=4'),
        array('txt' => '5.', 'url' => 'index.php?page=felveteli&sub=tajekoztato/20082009&f=5'),
        array('txt' => '6.', 'url' => 'index.php?page=felveteli&sub=tajekoztato/20082009&f=6'),
    );
*/

}
?>
