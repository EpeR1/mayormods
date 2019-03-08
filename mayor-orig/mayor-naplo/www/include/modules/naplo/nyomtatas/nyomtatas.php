<?php
/*
    Module: naplo
*/

    global $HIANYZASJELOLES, $targyTXT, $targyTXT2, $targyCsoportTXT;

//    function generatePDF($texFile) {
//
//echo 'tex -output-directory /tmp -fmt '._BASEDIR.'../install/module-naplo/tex/mayor '.$texFile;
//	exec('tex -output-directory /tmp -fmt '._BASEDIR.'../install/module-naplo/tex/mayor '.$texFile);
//
//    }

    $HIANYZASJELOLES = array(
	'hiányzás'=>'H',
	'késés'=>'k',
	'felszerelés hiány'=>'f',
	'egyenruha hiány'=>'e'
    );

    $targyCsoportTXT = array(
	'tanóra' => '',
	'fakultáció' => 'fakt.',
	'előfakultáció' => 'előfakt.',
	'szakkör' => 'szakk.'
    );

    $targyTXT = array(
        'angol nyelv' => 'Angol% nyelv',
        'biológia' => 'Bio-%lógia',
        'dráma' => 'Dráma',
        'ének-zene' => 'Ének%-zene',
        'fizika' => 'Fizika',
        'filozófia' => 'Filo-%zófia',
        'földrajz' => 'Föld-%rajz',
        'francia nyelv' => 'Francia% nyelv',
        'idegenvezetés' => 'Idegen-%vezetés',
        'japán nyelv' => 'Japán% nyelv',
        'kémia' => 'Kémia',
        'latin nyelv' => 'Latin% nyelv',
        'magyar' => 'Magyar',
        'magyar irodalom' => 'Magyar% irodalom',
        'magyar nyelv' => 'Magyar% nyelv',
        'matematika' => 'Mate-%matika',
        'művészettörténet' => 'Művészet-%történet',
        'német nyelv' => 'Német% nyelv',
        'olasz nyelv' => 'Olasz% nyelv',
        'rajz' => 'Rajz',
        'spanyol nyelv' => 'Spanyol% nyelv',
        'számítástechnika' => 'Számítás-%technika',
        'szociálpszichológia' => 'Szociál-%pszich.',
        'testnevelés' => 'Testne-%velés',
        'történelem' => 'Törté-%nelem',
        'zenetörténet' => 'Zene-%történet'
    );

    // Ezt az órarendnél, és az első lapon fogjuk használni :)
    $targyTXT2 = array(
        'angol nyelv' => 'Angol',
        'biológia' => 'Biológia',
        'dráma' => 'Dráma',
        'ének-zene' => 'Ének',
        'fizika' => 'Fizika',
        'filozófia' => 'Filozófia',
        'földrajz' => 'Földrajz',
        'francia nyelv' => 'Francia',
        'idegenvezetés' => 'Idegenvezetés',
        'japán nyelv' => 'Japán',
        'kémia' => 'Kémia',
        'latin nyelv' => 'Latin',
        'magyar' => 'Magyar',
        'magyar irodalom' => 'Magyar irodalom',
        'magyar nyelv' => 'Magyar nyelv',
        'matematika' => 'Matematika',
        'művészettörténet' => 'Művészettörténet',
        'német nyelv' => 'Német',
        'olasz nyelv' => 'Olasz',
        'osztályfőnöki' => 'Osztályfőnöki',
        'rajz' => 'Rajz',
        'spanyol nyelv' => 'Spanyol',
        'számítástechnika' => 'Szám. tech.',
        'szociálpszichológia' => 'Szoc.pszich.',
        'testnevelés' => 'Testnevelés',
        'történelem' => 'Történelem',
        'zenetörténet' => 'Zenetörténet'
    );

?>
