<?php

    function naploGeneralas($filename, $tolDt, $igDt) {

	// A sablonfile meghatározása
        define('__TEMPLATE_DIR', _MAYOR_DIR.'/print/module-naplo/templates');
        if (file_exists(__TEMPLATE_DIR.'/'.__INTEZMENY.'/helyettesitesinaplo.tmpl')) {
            $templateFile = __TEMPLATE_DIR.'/'.__INTEZMENY.'/helyettesitesinaplo.tmpl';
        } elseif (file_exists(__TEMPLATE_DIR.'/default/helyettesitesinaplo.tmpl')) {
            $templateFile = __TEMPLATE_DIR.'/default/helyettesitesinaplo.tmpl';
        } else {
            $_SESSION['alert'][] = 'message:file_not_found:'.__TEMPLATE_DIR.'/default/helyettesitesinaplo.tmpl';
            return false;
        }

	$Helyettesitesek = getHelyettesitettOra($tolDt, $igDt);
	$Intezmeny = getIntezmenyByRovidnev(__INTEZMENY);

	$DATA = array(
	    'file' => $filename,
	    'base' => array(
		'intezmenyNev' => $Intezmeny['nev'], 'tanev' => __TANEV, 'nyDt' => date('Y.m.d'),
		'tolDt' => dateToString($tolDt), 'igDt' => dateToString($igDt), 'intezmenyHelyseg' => $Intezmeny['cimHelyseg'],
		'nyDatumStr' => dateToString(date('Y-m-d'))
	    )
	);
	for ($i = 0; $i < count($Helyettesitesek); $i++) {
	    $oraAdat = $Helyettesitesek[$i];
	    if ($oraAdat['eredet'] == 'plusz') $oraAdat['tipus'] = $oraAdat['tipus'].' '.$oraAdat['eredet'];
	    unset($oraAdat['ki']);
	    unset($oraAdat['kit']);
	    $oraAdat['tankorNev'] = LaTeXSpecialChars($oraAdat['tankorNev']);
	    $DATA['hDt'][ $oraAdat['dt'] ]['helyettesites'][ $oraAdat['oraId'] ] = $oraAdat;
	}
	if (is_array($DATA['hDt']) && count($DATA['hDt'])>0) {
	    $DATA['base']['hDt'] = array_keys($DATA['hDt']);
	    return template2file($templateFile, $DATA);
	} else {
	    $_SESSION['alert'][] = 'info:no_data';
    	    return false;
	}


    }

/*
    function naploGeneralasOld($filename, $tolDt, $igDt) {

	// Helyettesítések lekérdezése
	$Helyettesitesek = getHelyettesitettOra($tolDt, $igDt);
	$Intezmeny = getIntezmenyByRovidnev(__INTEZMENY);

    $TeX = '\documentclass[8pt]{article}
\usepackage[a4paper]{geometry} % A4-es méret
\usepackage[utf8]{inputenc} % UTF-8 kódolású forrás
\usepackage{t1enc}
\usepackage[magyar]{babel} % magyar elválasztási szabályok
\frenchspacing % a magyar tipográfiai szabályoknak megfelelő szóközök írásjelek után
\usepackage{booktabs} % táblázatok magasabb szintű formázása
\usepackage{longtable} % többoldalas táblázatok
\setlength\LTleft{-65pt}
\setlength\LTright{-65pt}
\usepackage{fancyhdr} % Fejléc és lábléc kezelés
\pagestyle{fancy}
\def\mayor{%
\font\mayorfnt=cmsl4%
\font\Mayorfnt=cmsl6
{\mayorfnt\lower0.5ex\hbox{\lower-0.5ex\hbox{Ma}\kern-0.3em\lower0.25ex\hbox{\Mayorfnt Y}\kern-0.2em\hbox{o}\lower0ex\hbox{R}}}}
\renewcommand{\footnotesize}{\fontsize{6pt}{8pt}\selectfont}
%\addtolength{\skip\footins}{2mm}
%\addtolength{\textheight}{16mm}
%\addtolength{\textwidth}{30mm}
\setlength{\footskip}{26pt}
\setlength{\headsep}{24pt}
\lhead{\small '.$Intezmeny['nev'].'}
\rhead{\small Helyettesítési-napló '.__TANEV.'}
\lfoot{\scriptsize\copyright\mayor\ elektronikus napló - Nyomtatva: '.date('Y.m.d.').'}
\rfoot{\scriptsize\thepage . oldal}
\cfoot{}
\begin{document}
';

	$TeX .= '
\begin{center}
{\large\bfseries H E L Y E T T E S Í T É S E K}\\\\

\vspace{12pt}

{\bfseries\normalsize '.dateToString($tolDt).' – '.dateToString($igDt).'}\\\\

\vspace{12pt}

\scriptsize
\begin{longtable}{@{\extracolsep{\fill}}c|c|l|l|l|c}
Dátum & Óra & Ki & Kit & Tankör & Típus \\\\
\toprule
\endfirsthead
Dátum & Óra & Ki & Kit & Tankör & Típus \\\\
\toprule
\endhead
\bottomrule
\endfoot
\bottomrule
\endlastfoot
\hline
';

	for ($i = 0; $i < count($Helyettesitesek); $i++) {
	    $oraAdat = $Helyettesitesek[$i];
	    if ($oraAdat['eredet'] == 'plusz') $oraAdat['tipus'] = $oraAdat['tipus'].' '.$oraAdat['eredet'];
	    $TeX .= '\vbox to 1.2em {}'.$oraAdat['dt'].'&'.$oraAdat['ora'].'&'.$oraAdat['kiCn'].'&'.$oraAdat['kitCn']
		    .'&'.$oraAdat['tankorNev'].'&'.$oraAdat['tipus'].'\\\\ ';
	    if ($Helyettesitesek[$i+1]['dt'] != $oraAdat['dt']) $TeX .= '\midrule'."\n";
	    //else $TeX .= '\hline';

	}

	$TeX .= '
\end{longtable}

\vspace{16pt}

\begin{flushleft}
{\scriptsize '.$Intezmeny['cimHelyseg'].', '.dateToString(date('Y-m-d')).'}
\end{flushleft}
\vspace{20pt}\slshape\scriptsize
\begin{tabular}{ccc}
%\rule{3.5cm}{0.1pt}
\hspace{3.5cm}%
&\hspace{3cm}\ &\rule{3.5cm}{0.1pt}\\\\
%tanuló
&&igazgató\\\\
\end{tabular}

\end{center}
';


	$TeX .= '
\end{document}';

	pdfLaTeX($TeX, $filename); // A longtable miatt többször kell fordítani
	return pdfLaTeX($TeX, $filename);

    }
*/

?>
