<?php
#
# MaYoR keretrendszer - konfigurációs file
# (A telepítő által generálva)
#

// Base
if (file_exists('include/menu/'._POLICY.'/'.$lang.'/base.php')) {
    require('include/menu/'._POLICY.'/'.$lang.'/base.php');
} else {
    require('include/menu/'._POLICY.'/'._DEFAULT_LANG.'/base.php');
}
// Modules
    $files = scandir('include/menu/'._POLICY.'/'.$lang);
    $ff = array();
    if (is_array($files)) foreach ($files as $file) {
	list($name,$ext) = explode('.',$file);
	if ($ext=='php' && substr($file,0,7)=='module-') $ff[] = $name;
    }
    sort($ff);
    foreach ($ff as $name) {
	require_once('include/menu/'._POLICY.'/'.$lang.'/'.$name.'.php');
    }

// Custom
if (file_exists('../config/menu/'._POLICY.'/menu-'.$lang.'.php')) {
    require('../config/menu/'._POLICY.'/menu-'.$lang.'.php');
}

/*
    Egy menüpont (item, tétel) felépítése:
	txt   - a megjelenő szöveg
	[url] - ha nincs: a $MENU alapján kiolvasható PSF-re mutat
	      - ha &-tel kezdődik: az lőzőhöz fúzzük
	      - ha http-vel kezdődik: a megadott URL-t vesszük egy az egyben (sessionID, lang, skin stb nélkül)
              - egyéb esetben: az adott linket használjuk átadva a fontos paramétereket (sessionId, policy, lang, skin)
	[get] - az utolsó esetben az átadandó paraméterek listája ()

    A $MENU tömb felépítése:

    $MENU = array(
	$page1 => array(			// Az első szinten a modulok menüpontjai
	    array('txt1'[,'url1']),
	    [array('txt2'[,'url2']), ...]
	),
	[$page2 => array( ... ), ...]
	$item1 => array(			// Az elsp szinten nem modulhoz tartozó tételek
	    array('txt1'[,'url1']),
	    [array('txt2'[,'url2']), ...]
	),
	[$item2 => array( ... ), ...]
	'modules' => array(			// 'modules'-en belul csak page-ek vannak
	    $page1 => array(
		'sub' => array(			// 'sub'-on belül csak sub-ok vannak
		    $sub1 => array(
			$f1 => array(
			    array('txt1'[,'url1']),
			    [array('txt2'[,'url2']), ...]
			),
			[$f2 => array(
			    array('txt1'[,'url1']),
			    [array('txt2'[,'url2']), ...]
			), ...]
		    ),
		    [$sub2 => arrray( ... ), ...]
		),
		[$f1 => array(
		    array('txt1'[,'url1']),
		    [array('txt2'[,'url2']), ...]
		), ...]
	    ),
	    [$page2 => array( ... ), ...]
	)
    )

Kirajzolva:

$M[$page1][0] | $M[$page1][1] ... | $M[$page2][0] | ... | $M[$item1][0] | $M[$item1][1] ...
$M['modules'][$page][$f1][0] | $M['modules'][$page][$f1][1] ... | $M['modules'][$page][$f2][0] | $M['modules'][$page][$f1][0] ...
$M['modules'][$page]['sub'][$sub][$f1][0] ... $M['modules'][$page]['sub'][$sub][$f2][0]

Például:

    $MENU['modules']['login'] = array(
        'sub' => array(

            'sub1' => array(
                'f11' => array(
                    array('txt' => 'Kakukk'),
                    array('txt' => 'Tojás', 'url'=>'http://')
                )
            ),
            'sub2' => array(
                'f21' => array(
                    array('txt' => 'Cica'),
                )
            )

        ),
        'login' => array(
            array('txt' => 'Szülői bejelentkezés','url' => '&toPolicy=parent'),
            array('txt' => 'Bejelentkezés','url' => '&toPolicy=private'),
        )
    );
*/

?>
