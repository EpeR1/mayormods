<?php
/*
    Module: base

    function kisbetus($str)
    function nagybetus($str)
    function ekezettelen($str)
    function utf8_to_iso88592($str)
    function iso88592_to_utf8($str)
    function str_cmp($a,$b)
*/

mb_internal_encoding("UTF-8"); 

// -------------------------------------------------------------------
// Kisbet≈±ss√© konvert√°l√°s (a magyar √©kezetes karaktereket is)
// -------------------------------------------------------------------

function kisbetus($str) {
    $str = strtolower(mb_convert_encoding($str, 'ISO-8859-2', 'UTF-8'));
    $str = strtr($str, "¡…Õ”÷’⁄‹€", "·ÈÌÛˆı˙¸˚");
    $str = mb_convert_encoding($str, 'UTF-8', 'ISO-8859-2');
    return $str;
}

// -------------------------------------------------------------------
// Nagybet≈±ss√© konvert√°l√°s (a magyar √©kezetes karaktereket is)
// -------------------------------------------------------------------

function nagybetus($str) {
    $str = strtoupper(mb_convert_encoding($str, 'ISO-8859-2', 'UTF-8'));
    $str = strtr($str, "·ÈÌÛˆı˙¸˚", "¡…Õ”÷’⁄‹€");
    $str = mb_convert_encoding($str, 'UTF-8', 'ISO-8859-2');
    return $str;
}

// -------------------------------------------------------------------
// √âkezettelenn√© konvert√°l√°s (UTF-8 --> Lat2 --> √©kezettelen)
// -------------------------------------------------------------------

function ekezettelen($str) {
    return strtr(mb_convert_encoding($str, 'ISO-8859-2', 'UTF-8'), '·‰ÈÌÛˆı˙¸˚¡ƒ…Õ”÷’⁄‹€', 'aaeiooouuuAAEIOOOUUU');
}

/*
// ---------------------------------------------------------------------------
//
//  UTF8 <--> ISO88592 v√°lt√°s
//
// ---------------------------------------------------------------------------

function UTF8_to_ISO88592($str) {

    return mb_convert_encoding($str,'ISO-8859-2','UTF-8');

}

function ISO88592_to_UTF8($str) {

    return mb_convert_encoding($str,'UTF-8','ISO-8859-2');

}
*/

// ---------------------------------------------------------------------------
//
//  K√©t string √∂sszehasonl√≠t√°sa a magyar ABC szerint ($a < $b => -1)
//
// ---------------------------------------------------------------------------

function str_cmp($a,$b) {

    $ABC = Array('a'=>1,
                '·'=>1,
		'‰'=>1,
                'b'=>3,
                'c'=>4,
                'd'=>5,
                'e'=>6,
                'È'=>7,
                'f'=>8,
                'g'=>9,
                'h'=>10,
                'i'=>11,
                'Ì'=>12,
                'j'=>13,
                'k'=>14,
                'l'=>15,
                'm'=>16,
                'n'=>17,
                'o'=>18,
                'Û'=>18,
                'ˆ'=>20,
                'ı'=>20,
                'p'=>22,
                'q'=>23,
                'q'=>24,
                'r'=>25,
                's'=>26,
                't'=>27,
                'u'=>28,
                '˙'=>28,
                '¸'=>30,
                '˚'=>30,
                'v'=>32,
                'x'=>33,
                'w'=>34,
                'y'=>35,
                'z'=>36
    );

    $a = kisbetus(mb_convert_encoding($a,'ISO-8859-2','UTF-8'));
    $b = kisbetus(mb_convert_encoding($b,'ISO-8859-2','UTF-8'));

    if ($a==$b) return 0;

    $i=0;
    while( $i<strlen($a) && $i<strlen($b) && $a[$i]==$b[$i]) {
        $i++;
    }

    if ($i==strlen($a) && $i<strlen($b)) {
        return 1;
    } elseif ($i==strlen($b) && $i<strlen($a)) {
        return -1;
    } elseif ($ABC[$a[$i]] < $ABC[$b[$i]]) {
        return -1;
    } else {
        return 1;
    }
}

    function decimal_to_roman($number) {
	$roman = '';
	while ($number >= 1000) { $roman .= "M"; $number = $number -1000; }
	while ($number >= 900) { $roman .= "CM"; $number = $number -900; }
	while ($number >= 500) { $roman .= "D"; $number = $number -500; }
	while ($number >= 400) { $roman .= "CD"; $number = $number -400; }
	while ($number >= 100) { $roman .= "C"; $number = $number -100; }
	while ($number >= 90) { $roman .= "XC"; $number = $number -90; }
	while ($number >= 50) { $roman .= "L"; $number = $number -50; }
	while ($number >= 40) { $roman .= "XL"; $number = $number -40; }
	while ($number >= 10) { $roman .= "X"; $number = $number -10; }
	while ($number >= 9) { $roman .= "IX"; $number = $number -9; }
	while ($number >= 5) { $roman .= "V"; $number = $number -5; }
	while ($number >= 4) { $roman .= "IV"; $number = $number -4; }
	while ($number >= 1) { $roman .= "I"; $number = $number -1; }
	return $roman;
    }

    function visszafele($e) {$b='';for($i=0; $i<mb_strlen($e,'utf-8'); $i++) $b = mb_substr($e,$i,1).$b; return $b;}

    function mayor_array_join ($a='') {
        $ARGS = func_get_args();
        $x = array();
        for ($i=0;$i<count($ARGS);$i++) {
            $a = $ARGS[$i];
            if (is_array($a)) foreach($a as $v) $x[] = $v; elseif ($a!='') $x[] = $a;
        }
        return $x;
    }

    if(!function_exists('hash_equals')) {
      function hash_equals($str1, $str2) {
	if(strlen($str1) != strlen($str2)) {
    	    return false;
	} else {
	    $res = $str1 ^ $str2;
	    $ret = 0;
	    for($i = strlen($res) - 1; $i >= 0; $i--) $ret |= ord($res[$i]);
	    return !$ret;
	}
      }
    }

    if ( !function_exists( 'hex2bin' ) ) {
      function hex2bin( $str ) {
        $sbin = "";
        $len = strlen( $str );
        for ( $i = 0; $i < $len; $i += 2 ) {
            $sbin .= pack( "H*", substr( $str, $i, 2 ) );
        }
        return $sbin;
      }
    }

    function makeLinksClickable($text){
	$pattern[] = '/(\S+@\S+\.\S+)/';
	$replace[] = '<a href="mailto:$1">$1</a>';
	$pattern[] = '!(((f|ht)tp(s)?://)[-a-zA-Z–∞-—è–ê-–Ø()0-9@:%_+.~#?&;//=]+)!i';
//	$replace[] = '<a href="$1" target="_blank">$1</a> <a href="$1" target="_blank"><span class="icon-circle-arrow-right"></span></a>';
	$replace[] = '<a href="$1" target="_blank">[LINK]</a>';
	return preg_replace($pattern, $replace, $text);
    }
    function supertext($txt) {
        $r = $txt;
        $r = htmlspecialchars($r);
        $pattern[]='/@diakId:(\d+)/';
        $pattern[]='/@tanarId:(\d+)/';
        $pattern[]='/@tankorId:(\d+)/';
        //$pattern[]='/@osztalyId:(\d+)/';
        $replacement[]='<b><span class="diakNev icon-child" data-diakid="${1}"></span></b>';
        $replacement[]='<b><span class="tanarNev icon-adult" data-tanarid="${1}"></span></b>';
        $replacement[]='<b><span class="tankorAdat" data-tankorid="${1}">[tank√∂r adatok]</span></b>';
        //$replacement[]='<b><span class="osztalyAdat" data-osztalyid="${1}">[oszt√°ly adatok]</span></b>';
        $r = preg_replace($pattern,$replacement,$r);
        return $r;
    }


    require_once('include/share/date/names.php');
    function superdate($datetime) {
	global $aHetNapjai;
	$stamp = strtotime($datetime);
	$date = date('Y-m-d',$stamp);
	$dow = date('N',$stamp);
	$Hi = date('H:i',$stamp);
	if ($Hi == '00:00') $Hi = '';
	if ($stamp<=strtotime('-6 day')) {
	    return date('Y.m.d.', $stamp).' '.$Hi;
	} elseif ($date==date('Y-m-d')) {
	    return 'Ma '.$Hi;	    
	} elseif ($dow>date('N')) {
	    return 'M√∫lt h√©t '.kisbetus($aHetNapjai[$dow-1]).' '.$Hi;
	} else {
	    return $aHetNapjai[$dow-1].' '.$Hi;
	}
    }

?>
