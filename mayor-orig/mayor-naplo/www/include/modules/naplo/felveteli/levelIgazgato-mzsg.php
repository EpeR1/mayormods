<?php

    function pdfLevel($file, $ADAT) {

	for ($i=0; $i<count($ADAT['diak']); $i++) {
	    $D = $ADAT['diak'][$i];
	    $omkod = $ADAT['diak'][$i]['OM'];
	    //if (strstr($D['vegeredmeny'],'nem nyert')!==false) $eredmeny=0; elseif(strstr($D['extra'],'9.')!==false) $eredmeny = 3; else $eredmeny=0;
	    //if (strstr($D['vegeredmeny'],'nem nyert')!==false) $eredmeny=0; elseif(strstr($D['extra'],'202')!==false) $eredmeny = 3; else $eredmeny=0;
	    //if ($eredmeny>2) $DATA[$omkod]['ok'][] = $ADAT['diak'][$i];
	    if ($D['felvett'] != '') $DATA[$omkod]['ok'][] = $ADAT['diak'][$i];
	    elseif ($D['evfolyam'] != '6') $DATA[$omkod]['nemok'][] = $ADAT['diak'][$i];
	    else $DATA[$omkod]['6nemok'][] = $ADAT['diak'][$i];
	}

        $TeX = '\documentclass[8pt]{article}'."\n\n";
        $TeX .= '\usepackage[a4paper]{geometry} % A4-os méret'."\n";
	$TeX .= '\usepackage{graphicx}'."\n";
//      $TeX .= '\usepackage[utf8]{inputenc} % UTF-8 kódolású forrás'."\n";
        $TeX .= '\usepackage[utf8x]{inputenc} % UTF-8 kódolású forrás (ucs)'."\n";
        $TeX .= '\usepackage{ucs} % Jobb UTF-8 támogatás'."\n";
        $TeX .= '\usepackage{t1enc}'."\n";
        $TeX .= '\usepackage[magyar]{babel} % magyar elválasztási szabályok'."\n";
        $TeX .= '\frenchspacing % a magyar tipográfiai szabályoknak megfelelő szóközök írásjelek után'."\n";
        $TeX .= '\usepackage{booktabs} % táblázatok magasabb szintű formázása'."\n";
//      $TeX .= '\usepackage{soul} % Ritkítás'."\n";
        $TeX .= '\usepackage{fancyhdr} % Ritkítás'."\n";
//      $TeX .= '\pagestyle{empty}'."\n";
        $TeX .= '\pagestyle{fancy}'."\n";

        $TeX .= '\def\mayor{%'."\n";
//      $TeX .= '\font\mayorfnt=cmsl6%'."\n";
//      $TeX .= '\font\Mayorfnt=cmsl9'."\n";
        $TeX .= '\font\mayorfnt=cmsl4%'."\n";
        $TeX .= '\font\Mayorfnt=cmsl6'."\n";
        $TeX .= '{\mayorfnt\lower0.5ex\hbox{\lower-0.5ex\hbox{Ma}\kern-0.3em\lower0.25ex\hbox{\Mayorfnt Y}\kern-0.2em\hbox{o}\lower0ex\hbox{R}}}}'."\n";

        $TeX .= '\renewcommand{\footnotesize}{\fontsize{6pt}{8pt}\selectfont}'."\n";
        $TeX .= '\addtolength{\skip\footins}{2mm}'."\n";
#        $TeX .= '\addtolength{\textheight}{10mm}'."\n";
        $TeX .= '\parindent 0mm'."\n";
#        $TeX .= '\linespread{1.3}'."\n";
        $TeX .= '\setlength{\footskip}{16pt}'."\n";
        $TeX .= '\setlength{\headsep}{14pt}'."\n"; // 14pt helyett

        $TeX .= '\cfoot{\tiny \copyright\mayor\ elektronikus adminisztráció - Nyomtatva: '.date('Y.m.d').'}';
	$TeX .= '\setlength{\voffset}{0mm}'."\n";
	$TeX .= '\setlength{\headheight}{25mm}'."\n";
	$TeX .= '\renewcommand{\headrulewidth}{0pt}'."\n";
        $TeX .= '\chead{\includegraphics[width=160mm]{/var/mayor/www/skin/classic/module-portal/img/fejlec.png}}'."\n";
#        $TeX .= '\lhead{\includegraphics[width=160mm]{/var/mayor/www/skin/classic/module-felveteli/img/iskola.png}}'."\n";
#        $TeX .= '\rhead{\tiny Felvételi értesítő}';


        $TeX .= '\begin{document}'."\n\n";

	foreach ($DATA as $omkod => $DA) {

	    $TeX .= '\begin{flushleft}'."\n";

	    $TeX .= putTeXIgLevelFejlec($ADAT['iskola'][$omkod],$ADAT['iktsz']);

	    $TeX .= '\end{flushleft}'."\n";

#	    $TeX .= '\parskip'."\n";
            $TeX .= '\vspace{25pt}'."\n";

	    $TeX .= '{'."\n";
	    $TeX .= 'Kedves Igazgató Kolléga!'."\n\n";

            $TeX .= '\vspace{20pt}'."\n\n";

	    if (count($DA['ok'])==1) { $vanfelvett=true;
        	$TeX .= 'Értesítem, hogy az Önök iskolájából az alábbi tanuló nyert felvételt iskolánkba:'."\n\n";
	    } elseif (count($DA['ok'])>1) {  $vanfelvett=true;
        	$TeX .= 'Értesítem, hogy az Önök iskolájából az alábbi tanulók nyertek felvételt iskolánkba:'."\n\n";
	    } else {
		$vanfelvett=false;
	    }

	    if ($vanfelvett) {
		$TeX .= '\begin{itemize}'."\n";
		for ($i=0; $i<count($DA['ok']); $i++) {
		    $TeX .= '\item '.$DA['ok'][$i]['nev'].'';
//			$DA['ok'][$i]['vegeredmeny']. '} '."\n".
//			$DA['ok'][0]['vegeredmeny']."\n";
		}
		$TeX .= '\end{itemize}'."\n";
        	$TeX .= '\vspace{14pt}'."\n\n";
	    }
//dump($TeX);
//die();

	    if (count($DA['nemok'])>0) {
		if ($vanfelvett) { $TeX .= 'Egyúttal s'; $kieg=' iskolánkba'; }
		else { $TeX .= 'S'; $kieg = ' iskolánkba';}
		if (count($DA['nemok'])==1)
		    $TeX .= 'ajnálattal tájékoztatom, hogy a következő tanuló nem nyert felvételt'.$kieg.':'."\n\n";
		else
		    $TeX .= 'ajnálattal tájékoztatom, hogy a következő tanulók nem nyertek felvételt'.$kieg.':'."\n\n";

        	$TeX .= '\vspace{14pt}'."\n\n";

		$TeX .= '\begin{itemize}'."\n";
		for ($i=0; $i<count($DA['nemok']); $i++) {
		    $TeX .= '\item '.$DA['nemok'][$i]['nev']."\n";
		}
		$TeX .= '\end{itemize}'."\n";
	    }

#################################### 6666666666666666666 ################################
/*
	    if (count($DA['6nemok'])>0) {
		if ($vanfelvett) { $TeX .= 'Egyúttal s'; $kieg=' iskolánkba'; }
		else { $TeX .= 'S'; $kieg = ' iskolánkba';}
		if (count($DA['6nemok'])==1)
		    $TeX .= 'ajnálattal tájékoztatom, hogy a következő 6. évfoyamos tanuló nem nyert felvételt'.$kieg.':'."\n\n";
		else
		    $TeX .= 'ajnálattal tájékoztatom, hogy a következő 6. évfoyamos tanulók nem nyertek felvételt'.$kieg.':'."\n\n";

        	$TeX .= '\vspace{14pt}'."\n\n";

		$TeX .= '\begin{itemize}'."\n";
		for ($i=0; $i<count($DA['6nemok']); $i++) {
		    $TeX .= '\item '.$DA['6nemok'][$i]['nev']."\n";
		}
		$TeX .= '\end{itemize}'."\n";
	    }
*/
#################################### 6666666666666666666 ################################

	    $TeX .= '}'."\n";

	    $TeX .= putAlairas();

#            $TeX .= '\vspace{20pt}'."\n";
#            $TeX .= '\noindent Budapest, 2024. április 29. \par%'."\n";
#            $TeX .= '\vspace{14pt}'."\n\n";
#	    if ($eredmeny > 2 ) $TeX .= '\vspace{14pt}'."\n\n";
#            $TeX .= '\begin{flushright}'."\n";

# #$TeX .= '\begin{tabular}{ccc}'."\n";
# #$TeX .= '&&\includegraphics[width=30mm]{/var/mayor/www/skin/classic/module-felveteli/img/tasai.png}\\\\ '."\n";
# #$TeX .= '&&\rule{4cm}{0.1pt}\\\\ '."\n";
# #$TeX .= '&&Dr. Szebedy Tas\\\\ '."\n";
# #$TeX .= '&&igazgató\\\\ '."\n";
# #$TeX .= '\end{tabular}'."\n";

#$TeX .= '\begin{tabular}{ccc}'."\n";
#$TeX .= '&&\rule{4cm}{0.1pt}\\\\ '."\n";
#$TeX .= '&&Veleczki Viktória\\\\ '."\n";
#$TeX .= '&&igazgató\\\\ '."\n";
#$TeX .= '\end{tabular}'."\n";

#            $TeX .= '\end{flushright}'."\n";



#            $TeX .= '\newpage %%%%%%%%%%%%%%%%% új oldal %%%%%%%%%%%%%%%%%%%%'."\n\n";

        }

        // dokumentum lezárása
        $TeX .= '\end{document}'."\n";
        return pdfLaTeX($TeX, $file);

    }

function putAlairas() {
    $TeX .= '\vspace{20pt}'."\n";
    $TeX .= '\noindent Budapest, 2024. április 29.'.' \par%'."\n";

    $TeX .= '\begin{flushright}'."\n";

    $TeX .= '\begin{tabular}{ccc}'."\n";
    $TeX .= '&\includegraphics[width=30mm]{/var/mayor/www/skin/classic/module-naplo/img/pecset.jpg}';
    $TeX .= '&\parbox[b]{5cm}{\begin{center}';
	$TeX .= '\includegraphics[width=50mm]{/var/mayor/www/skin/classic/module-naplo/img/signo.png}';
	$TeX .= '\newline\rule{4cm}{0.1pt}';
	$TeX .= '\newline Veleczki Viktória';
	$TeX .= '\newline igazgató';
    $TeX .= '\end{center}}';
    $TeX .= '\\\\ '."\n";
    $TeX .= '\end{tabular}'."\n";

    $TeX .= '\end{flushright}'."\n";

    $TeX .= '\newpage'."\n\n";
    $TeX .= '\setcounter{footnote}{0}'."\n\n";

    return $TeX;
}


    function putTeXIgLevelFejlec($ADAT,$iktsz) {


	$TeX.= "\ \n\n";
	$TeX.= "Intézmény: ".$ADAT['nev']."\n\n";
	$TeX.= "Cím: ".$ADAT['telepules'].', '.$ADAT['irsz'].' '.$ADAT['cim']."\n\n";
	$TeX.= "OM kód: ".$ADAT['OM']."\n\n";
	$TeX.= "Email: ".$ADAT['email']."\n\n";
	$TeX.= "Iktatószám: ".$iktsz."\n\n";
	$TeX.= "MaYoR hivatkozási szám: ".$ADAT['OM'].'-'.$ADAT['id']."\n\n";
	$TeX.= "Tárgy: Értesítés felvételi eredményről\n\n";

#        $TeX.= '\hrule%'."\n";

#        $TeX.= '%'."\n";

#        $TeX.= '\vskip2cm\alap';

#        $TeX.= '\item{}C.mzett: '.$nev.' .s Sz.lei';
#        $TeX.= '\item{}Postai c.m: '.$cim;
#        $TeX.= '\item{}Iktat.sz.m: '.$IKTSZ[$eredmeny].' ('.$id.')%'."\n";
#        $TeX.= '\vskip0.8cm';
#        $TeX.= '{{Tárgy}: {Értesítés '.$nev.' (';
#            if ($an!="") $TeX.= 'a.n.: '.$an.', ';
#        $TeX.= 'sz.l.: '.str_replace('-','.',$szul_dt).'.) felv.teli eredm.ny.r.l}}';

#        $TeX.= '%'."\n";

        return $TeX;

    }



?>
