<?php

    $MENU['felveteli'] = array(
        array('txt' => 'Felvételi', 'url' => 'index.php?page=felveteli')
    );

//    if (_USERACCOUNT=='mayoradmin' || memberOf(_USERACCOUNT, 'felveteliadmin')) $NAV[1][] = array('page' => 'felveteli');

// Csak akkor van __FELVETELIADMIN kondtans, ha $page=felveteli
if (__FELVETELIADMIN===true) {
    $MENU['modules']['felveteli'] = array(
        'szobeli' => array(array('txt' => 'Eredmények')),
        'kozponti' => array(array('txt' => 'Végeredmény')),
//        'import' => array(array('txt' => 'Import')),
        'level' => array(array('txt' => 'Levelek generálása')),
        'levelIgazgato' => array(array('txt' => 'Levelek (igazgató)')),
        'boritek' => array(array('txt' => 'Boríték')),
        'boritekIgazgato' => array(array('txt' => 'Boríték (igazgató)')),
	'print2009' => array(array('txt'=>'Nyomtatás 2009')),
	'sub' => array('print2009' => array(
    		'igazgato' => array(array('txt' => 'Levelek generálása (igazgató)', 'url' => 'index.php?page=felveteli&sub=print2009&f=igazgato')),
    		'etikett' => array(array('txt' => 'Etikett generálása', 'url' => 'index.php?page=felveteli&sub=print2009&f=etikett')),
    		'etikettIG' => array(array('txt' => 'Etikett generálása (igazgató)', 'url' => 'index.php?page=felveteli&sub=print2009&f=etikettIG')),
	    ),
	),
    );

    global $NAV;
    if ($page=='felveteli') {
        if (is_array($MENU['modules']['felveteli'])) foreach ($MENU['modules']['felveteli'] as $_f => $M) {
	    foreach ($M as $index => $N) {
        	if ($_f != 'sub') $NAV['2'][] = array('page' => 'felveteli', 'f' => $_f, 'index' => $index);
	    }
        }
    }

}

?>
