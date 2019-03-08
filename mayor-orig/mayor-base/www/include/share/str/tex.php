<?php

    function TeXSpecialChars($szoveg) {
        $trans = array(
//                        "\\'" => "'", ## ezt kell kiszedni 2.0-ban
                        '+' => '$+$',
                        '/' => '$/$',
                        '*' => '$*$',
                        '=' => '$=$',
                        '<' => '$<$',
                        '>' => '$>$',
                        '{' => '$\{$',
                        '}' => '$\}$',
                        '#' => '\#',
                        '%' => '\%',
                        '_' => '\_',
                        '&' => '\&',
                        '~' => ' ',
			'…' => '...',
			'´' => '\'',
                        '^' => '(exp)',
//                        '~' => '\~\ ',
//                        '$' => '\$',
//                        "\\" => '$\backslash$'
                );
	if (__NYOMTATAS_XETEX) {
    	    $trans['^'] = '\^\ ';
	}
        return strtr(strtr($szoveg, array('$' => '\$', "\\" => '$\backslash$')), $trans);
    }

    function LaTeXSpecialChars($szoveg) {
        $trans = array(
                        '_' => '\_',
			'#' => '\#',
			'$' => '\$',
			'%' => '\%',
			'&' => '\&',
			'~' => '\verb|~|',
			'^' => '\verb|^|',
			'{' => '\{',
			'}' => '\}',
			'\\' => '$\backslash$',
// 0160 => 0032, // valami' spéci szóköz --> sima szóköz
                );
        return strtr($szoveg,$trans);
    }



?>
