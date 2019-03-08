<?php

    define('__PRINT_DIR',_DOWNLOADDIR.'/private/osztalyozo');

    function pdfBizonyitvany($file, $ADAT) {

	global $KOVETELMENY, $Honapok, $bizonyitvanyMegjegyzesek; 

//	$ftex = fopen(__PRINT_DIR.'/'.$file.'-A6.tex', 'w');
//        if (!$ftex) return false;

	$ev = substr($ADAT['szemeszterAdat']['zarasDt'], 0, 4);
        $ho = substr($ADAT['szemeszterAdat']['zarasDt'], 5, 2);
        $nap = substr($ADAT['szemeszterAdat']['zarasDt'], 8, 2);
        $dtStr = $ev.'. '.kisbetus($Honapok[--$ho]).' '.$nap.'.';


	// fejléc
	$TeX = '\documentclass[8pt]{article}'."\n\n";
	$TeX .= '\usepackage[a6paper]{geometry} % A6-os méret'."\n";
//	$TeX .= '\usepackage[utf8]{inputenc} % UTF-8 kódolású forrás'."\n";
	$TeX .= '\usepackage[utf8x]{inputenc} % UTF-8 kódolású forrás (ucs)'."\n";
	$TeX .= '\usepackage{ucs} % Jobb UTF-8 támogatás'."\n";
	$TeX .= '\usepackage{t1enc}'."\n";
	$TeX .= '\usepackage[magyar]{babel} % magyar elválasztási szabályok'."\n";
	$TeX .= '\frenchspacing % a magyar tipográfiai szabályoknak megfelelő szóközök írásjelek után'."\n";
	$TeX .= '\usepackage{booktabs} % táblázatok magasabb szintű formázása'."\n";
//	$TeX .= '\usepackage{soul} % Ritkítás'."\n";
	$TeX .= '\usepackage{fancyhdr} % Ritkítás'."\n";
//	$TeX .= '\pagestyle{empty}'."\n";
	$TeX .= '\pagestyle{fancy}'."\n";

	$TeX .= '\def\mayor{%'."\n";
//	$TeX .= '\font\mayorfnt=cmsl6%'."\n";
//	$TeX .= '\font\Mayorfnt=cmsl9'."\n";
	$TeX .= '\font\mayorfnt=cmsl4%'."\n";
	$TeX .= '\font\Mayorfnt=cmsl6'."\n";
	$TeX .= '{\mayorfnt\lower0.5ex\hbox{\lower-0.5ex\hbox{Ma}\kern-0.3em\lower0.25ex\hbox{\Mayorfnt Y}\kern-0.2em\hbox{o}\lower0ex\hbox{R}}}}'."\n";

	$TeX .= '\renewcommand{\footnotesize}{\fontsize{6pt}{8pt}\selectfont}'."\n";
	$TeX .= '\addtolength{\skip\footins}{2mm}'."\n";
	$TeX .= '\addtolength{\textheight}{10mm}'."\n";
	$TeX .= '\setlength{\footskip}{16pt}'."\n";
	$TeX .= '\setlength{\headsep}{14pt}'."\n";

	$TeX .= '\cfoot{\tiny \copyright\mayor\ elektronikus napló - Nyomtatva: '.date('Y.m.d').'}';
	$TeX .= '\lhead{\tiny '.$ADAT['intezmeny']['nev'].'}'."\n";
	$TeX .= '\rhead{\tiny Értesítő '.$ADAT['szemeszterAdat']['tanev'].'-'.(1+$ADAT['szemeszterAdat']['tanev']).'/'.$ADAT['szemeszterAdat']['szemeszter'].'}';

	$TeX .= '\begin{document}'."\n\n";

        for ($i = 0; $i < count($ADAT['diakok']); $i++) {

            $diakId = $ADAT['diakok'][$i]['diakId'];
            $jegyek = $ADAT['jegyek'][$diakId];
            $hianyzas = $ADAT['hianyzas'][$diakId];
//2013NKT                if (_KESESI_IDOK_OSSZEADODNAK===true)
            $igazolatlan = intval($hianyzas['kesesPercOsszeg']/45)+intval($hianyzas['igazolatlan']);
//2013NKT                else
//2013NKT                    $igazolatlan = intval($hianyzas['igazolatlan']);
            $igazolt = intval($hianyzas['igazolt']);
            //$atlag = $ADAT['atlag'][$diakId];

	    $TeX .= '\begin{center}'."\n";

//		$TeX .= '{\large\bfseries\scshape\so{\\\'Ertes{\\\'\\i}t\\H o}}'."\n";
//		$TeX .= '\vspace{8pt}'."\n\n";
		$TeX .= '{\large '.$ADAT['diakok'][$i]['diakNev'].'}\\\\ '."\n";
//		$TeX .= '\vspace{2pt}'."\n\n";
//		$TeX .= '{\scriptsize '.$ADAT['osztaly']['osztalyJel'].' osztály\\\\'.$ADAT['intezmeny']['nev']."}\n\n";
		$TeX .= '{\scriptsize '.$ADAT['osztaly']['osztalyJel'].' osztály'."}\n\n";
		$TeX .= '\vspace{2pt}'."\n\n";
//		$TeX .= '{\small '.$ADAT['szemeszterAdat']['tanev'].'-'.(1+$ADAT['szemeszterAdat']['tanev']).'/'.$ADAT['szemeszterAdat']['szemeszter'].'}'."\n";
//		$TeX .= '\vspace{4pt}'."\n\n";


		// --!!--!!-- Magatartás és szorgalom jegyek, ID alapján kellenének, nem pedig targyNev alapján!
		$__magatartas = '';
		for ($m=0; $m<count($ADAT['magatartasIdk']); $m++) {
		    $__mId = $ADAT['magatartasIdk'][$m];
		    for ($m2=0; $m2<count($jegyek[$__mId]); $m2++) {
			if ($__magatartas!='') $__magatartas .= ' ';
			$__magatartas .= $KOVETELMENY['magatartás'][$jegyek[$__mId][$m2]['jegy']]['hivatalos'];
		    }
		}
		$__szorgalom = '';
		for ($m=0; $m<count($ADAT['szorgalomIdk']); $m++) {
		    $__szId = $ADAT['szorgalomIdk'][$m];
		    for ($m2=0; $m2<count($jegyek[$__szId]); $m2++) {
			if ($__szorgalom!='') $__szorgalom .= ' ';
			$__szorgalom .= $KOVETELMENY['szorgalom'][$jegyek[$__szId][$m2]['jegy']]['hivatalos'];
		    }
		}

		$TeX .= '\small'."\n";
		$TeX .= '\begin{tabular}{@{\ \ }l|r@{\ \ }}'."\n";
		$TeX .= '\toprule\hline magatartás & ';
		$TeX .= '\emph{'.$__magatartas.'}\\\\ '."\n";
		$TeX .= '\hline szorgalom & ';
		$TeX .= '\emph{'.$__szorgalom.'}\\\\ '."\n";
//		$TeX .= '\midrule\multicolumn{2}{c}{tantárgyak} \\\\'."\n";
//		$TeX .= '\midrule\hline'."\n";
		$TeX .= '\hline\hline'."\n";
//		$TeX .= '\hline\multicolumn{2}{c}{tantárgyak} \\\\'."\n";
//		$TeX .= '\hline'."\n";

		for ($j = 0; $j < count($ADAT['targyak']); $j++)  if (!in_array($ADAT['targyak'][$j]['targyId'], array_merge($ADAT['magatartasIdk'],$ADAT['szorgalomIdk']))) {
		    $__jegyek='';
		    for ($k=0; $k<count($jegyek[$ADAT['targyak'][$j]['targyId']]); $k++) {
        		$jegyAdat = $jegyek[$ADAT['targyak'][$j]['targyId']][$k];
			if ($jegyAdat['jegy'] != '' && $jegyAdat['jegy'] != 0) {
			     if ($__jegyek!='') $__jegyek .= ' ';
			     $__jegyek .= $KOVETELMENY[$jegyAdat['jegyTipus']][$jegyAdat['jegy']]['hivatalos'].' '.$bizonyitvanyMegjegyzesek[$jegyAdat['megjegyzes']];
			}
		    }
		    if ($__jegyek!='') {
			$TeX .= $ADAT['targyak'][$j]['targyNev'].' & \emph{'.$__jegyek.'} \\\\ '."\n";
			$TeX .= '\hline'."\n";
		    }

		}
		$TeX .= '\bottomrule'."\n";
		$TeX .= '\end{tabular}'."\n\n";

		$TeX .= '\vspace{4pt}'."\n";
		$TeX .= '\begin{tabular}{@{\ \ }l|r|l|r@{\ \ }}'."\n";
		$TeX .= '\multicolumn{4}{c}{mulasztott órák száma} \\\\ '."\n";
		$TeX .= '\midrule'."\n";
		$TeX .= 'igazolt&{\sl '.$igazolt.'}&';
		if (!__ZARO_SZEMESZTER) $TeX .= 'igazolatlan\footnotemark[1]&';
		else $TeX .= 'igazolatlan&';
		$TeX .= '{\sl '.$igazolatlan.'}\\\\ '."\n";

		$TeX .= '\bottomrule'."\n";
		$TeX .= '\end{tabular}'."\n";
		if (!__ZARO_SZEMESZTER) $TeX .= '\footnotetext[1]{Tartalmazhat még igazolható hiányzásokat is!}'."\n";

		$TeX .= '\vspace{4pt}\begin{flushleft}'."\n";
		$TeX .= '{\scriptsize '.$ADAT['intezmeny']['cimHelyseg'].', '.$dtStr."}\n";
		$TeX .= '\end{flushleft}'."\n";

		$TeX .= '\vspace{6pt}\slshape\scriptsize'."\n";
		$TeX .= '\begin{tabular}{ccc}'."\n";
		$TeX .= '\rule{3cm}{0.1pt}&&\rule{3cm}{0.1pt}\\\\ '."\n";
		$TeX .= 'osztályfőnök&&szülő\\\\ '."\n";
		$TeX .= '\end{tabular}'."\n";

	    $TeX .= '\end{center}'."\n";

	    $TeX .= '\newpage %%%%%%%%%%%%%%%%% új oldal %%%%%%%%%%%%%%%%%%%%'."\n\n";

	}

	// dokumentum lezárása
	$TeX .= '\end{document}'."\n";

	pdfLaTeXA6($TeX, $file);

        return true;


    }

?>
