<?php

    // default
    $_COLOR_SCHEMES['blue'] = array(
	'head' => '#3496B9',	// a fő szín a felső sávban/menüben
	'login' => '#00c0ff',	// bejelentkezés:hover, svg-k színe
	'active' => '#3facc7',	// első szint aktív eleme, 3. szint alap,...
	'hover' => '#4fbcd7',	// hover a menüben és almenüben
	'hover-color' => '#fdb933', // !! Betűszín - hover, aktív...
	'nav' => '#3fa79c',	// a menü kinyíló része
	'nav2' => '#d2dfe9',	// nav kettő, kinyitó fül, stb
    );
    // yellow
    $_COLOR_SCHEMES['yellow'] = array(
	'head' => '#B99634',
	'login' => '#ffc000',
	'active' => '#c7ac3f',
	'hover' => '#d7bc4f',
	'hover-color' => '#800',
	'nav' => '#9ca73f',
	'nav2' => '#e9dfd2',
    );
    // red
    $_COLOR_SCHEMES['red'] = array(
	'head' => '#B93234',
	'login' => '#ff4000',
	'active' => '#c7393f',
	'hover' => '#d73f4f',
	'hover-color' => '#fdb933',
	'nav' => '#9c383f',
	'nav2' => '#e9dfd2',
    );
    // black
    $_COLOR_SCHEMES['black'] = array(
	'head' => '#000000',
	'login' => '#000000',
	'active' => '#888888',
	'hover' => '#070f0f',
	'hover-color' => '#fdb933',
	'nav' => '#666666',
	'nav2' => '#e0e0e0',
    );
    // nemzeti
    $_COLOR_SCHEMES['nemzeti'] = array(
	'head' => '#006000',
	'login' => '#a00000',
	'active' => '#a00000',
	'hover' => '#ffffff',
	'hover-color' => '#ff0000',
	'nav' => '#666666',
	'nav2' => '#e0ffe0',
    );
    // green
    $_COLOR_SCHEMES['green'] = array(
	'head' => '#32B934',
	'login' => '#40ff00',
	'active' => '#39c73f',
	'hover' => '#3fd74f',
	'hover-color' => '#b9fd33',
	'nav' => '#389c3f',
	'nav2' => '#dfe9d2',
    );
    // dark-blue
    $_COLOR_SCHEMES['dark-blue'] = array(
	'head' => '#008',
	'login' => '#008',
	'active' => '#36a',
	'hover' => '#4ac',
	'hover-color' => '#fdb933',
	'nav' => '#44b',
	'nav2' => '#bcd',
    );

    function colorToRGBA($color, $opacity) {
	if ($color[0] == '#') {
	    $hex = str_replace("#", "", $color);
	    if(strlen($hex) == 3) {
    		$r = hexdec(substr($hex,0,1).substr($hex,0,1));
    		$g = hexdec(substr($hex,1,1).substr($hex,1,1));
    		$b = hexdec(substr($hex,2,1).substr($hex,2,1));
	    } else {
    		$r = hexdec(substr($hex,0,2));
    		$g = hexdec(substr($hex,2,2));
    		$b = hexdec(substr($hex,4,2));
	    }
	    $rgb = array($r, $g, $b);
	} else {
	    $rgb = explode(',', str_replace(' ','',substr($color,4,strlen($color)-1)));
	}
	return 'rgba('.implode(', ', $rgb).', '.$opacity.')';
    }

?>