<?php

    function pdfLevel($file, $ADAT) {

	global $V2L, $OSZTALYABA;
        global $IKTSZ;

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
//        $TeX .= '\addtolength{\textheight}{10mm}'."\n";
        $TeX .= '\parindent 0mm'."\n";
#        $TeX .= '\linespread{1.3}'."\n";
        $TeX .= '\setlength{\footskip}{16pt}'."\n";
        $TeX .= '\setlength{\headsep}{20pt}'."\n"; // 14pt helyett

        $TeX .= '\cfoot{\tiny \copyright\mayor\ elektronikus adminisztráció - Nyomtatva: '.date('Y.m.d').'}'."\n";
	$TeX .= '\setlength{\voffset}{0mm}'."\n";
	$TeX .= '\setlength{\headheight}{40mm}'."\n";
	$TeX .= '\renewcommand{\headrulewidth}{0pt}'."\n";
        $TeX .= '\chead{\includegraphics[width=160mm]{/var/mayor/www/skin/classic/module-felveteli/img/fejlec.png}}'."\n";
#         $TeX .= '\lhead{\includegraphics[width=160mm]{/var/mayor/www/skin/classic/module-felveteli/img/iskola.png}}'."\n";
#        $TeX .= '\rhead{\tiny Felvételi értesítő}';


        $TeX .= '\begin{document}'."\n";
#	$TeX .= ''."\n";
        $TeX .= '\newpage'."\n";
        for ($i = 0; $i < count($ADAT['level']); $i++) {

	    $D = $ADAT['level'][$i];
	    $eredmeny = $V2L[$D['vegeredmeny']];
	    $nev = $D['nev'];
	    $kernev = $nev;
	    $tagozatId = $eredmeny;
	    $D['iktsz'] = $IKTSZ[$eredmeny].' ['.$eredmeny.']';
	    $TeX .= '\begin{flushleft}'."\n";

	    $TeX .= putTeXLevelFejlec($D);

	    $TeX .= '\end{flushleft}'."\n";

#	    $TeX .= '\parskip'."\n";
            $TeX .= '\vspace{40pt}'."\n";
            $TeX .= '\vspace{20pt}'."\n";

    	    $TeX .= '\noindent '.'Kedves Kisdiák! Tisztelt Szülő!'."\n\n";

            $TeX .= '\vspace{20pt}'."\n";

	    $TeX .= '{\baselineskip16pt'."\n";

	    if ($eredmeny>2) {

		$TeX .= 'Örömmel értesítem, hogy '.$nev.' tanuló az általános iskolai eredménye és a felvételi vizsgán mutatott teljesítménye alapján (a Felvételi Központ által megküldött végleges listák sze\-rint)'."\n\n";
		$TeX .= '\begin{center}'."\n";
		$TeX .= ' felvételt nyert '."\n\n";
		$TeX .= '\end{center}'."\n";
		$TeX .= '\begin{center}'."\n";
		$TeX .= ' a Városmajori Gimnázium '.$OSZTALYABA[$tagozatId].".\n\n";
		$TeX .= '\end{center}'."\n\n";
		$TeX .= 'Tájékoztatom, hogy az első szülői értekezlet '._SZULOI_ERTEKEZLET_IDOPONTBAN.' lesz, amelyre ezúton hívom meg a Szülőket.'."\n";
		if (in_array($tagozatId,array(1,2,11,12,3,4,5,6))) {
		    $TeX .= 'A szülői értekezlet ideje alatt a tanulók nyelvi szintfelmérőt írnak.\footnote{Amennyiben a tanuló az első idegen nyelvet nem tanulta, a szintfelmérőt nem szükséges megírnia.} ';
		}
		$TeX .= 'A beiratkozással és tankönyvrendeléssel kapcsolatos aktuális teendőkért kérjük ne felejtsék rendszeresen felkeresni honlapunkat.'." ";
		$TeX .= 'Hogy a család számára a nyári programtervezést megkönnyítsük, előre jelezzük, hogy a gólyatábor várható időpontja '._GOLYATABOR_IDOPONT.' lesz.'."\n\n";
        	$TeX .= '\vspace{14pt}'."\n";
		$TeX .= 'Remélem, az iskolánkban eltöltött évek hasznosak és eredményesek lesznek.'."\n\n";

    	    } elseif ($eredmeny==2) {

        	$TeX .= 'Sajnálattal vettük tudomásul, hogy '.$kernev.' másik iskolát választott, ezért nem vehettük fel a Városmajori Gimnáziumba.'."\n\n";
        	$TeX .= '\vspace{14pt}'."\n";
        	$TeX .= 'Remélem, hogy a választása jó döntésnek fog bizonyulni. Sikeres tanulmányi éveket és minden jót kívánok!'."\n\n";

    	    } elseif ($eredmeny==1) {

        	$TeX .= 'Sajnálattal értesítem, hogy '.$nev.' tanuló a felvételi vizsgán megfelelt, de helyhiány miatt nem nyert felvételt gimnáziumunkba.'."\n\n";
        	$TeX .= '\vspace{14pt}'."\n";
        	$TeX .= 'A Szülő e döntés ellen jogorvoslati lehetőséggel (a kézhezvételtől számított 15 napon belül) akkor élhet, ha az utolsó helyen megjelölt iskola elutasító értesítése megérkezett. Jogorvoslati kérelmét az általános iskola igazgatójának kell benyújtani. '
			.'Felhívom figyelmét arra, hogy gyermeke tankötelezettségének teljesítésére vonatkozó kérelemmel a lakóhely szerinti önkormányzat jegyzőjéhez fordulhat.'."\n\n";

    	    } elseif ($eredmeny==0) {

        	$TeX .= 'Sajnálattal értesítem, hogy '.$nev.' tanuló - a Felvételi Központ által megküldött végleges listák szerint, nem nyert felvételt gimnáziumunkba.'."\n\n";
        	$TeX .= '\vspace{14pt}'."\n\n";
        	$TeX .= 'A Szülő e döntés ellen jogorvoslati lehetőséggel (a kézhezvételtől számított 15 napon belül) akkor élhet, ha az utolsó helyen megjelölt iskola elutasító értesítése megérkezett. Jogorvoslati kérelmét az általános iskola igazgatójának kell benyújtani. '
			.'Felhívom figyelmét arra, hogy gyermeke tankötelezettségének teljesítésére vonatkozó kérelemmel a lakóhely szerinti önkormányzat jegyzőjéhez fordulhat.'."\n\n";

    	    } else {

    	    }
	    $TeX .= '}'."\n";
            $TeX .= '\vspace{20pt}'."\n";
            $TeX .= '\noindent Budapest, '._ERTESITES_DT.' \par%'."\n";


            $TeX .= '\vspace{14pt}'."\n\n";
	    if ($eredmeny > 2 ) $TeX .= '\vspace{14pt}'."\n\n";

            $TeX .= '\begin{flushright}'."\n";



$TeX .= '\begin{tabular}{ccc}'."\n";

//if ($eredmeny <= 2 ) {
    $TeX .= '&&\includegraphics[width=30mm]{/var/mayor/www/skin/classic/module-felveteli/img/tasai.png}\\\\ '."\n";
//}
$TeX .= '&&\rule{4cm}{0.1pt}\\\\ '."\n";
$TeX .= '&&Dr. Szebedy Tas\\\\ '."\n";
$TeX .= '&&igazgató\\\\ '."\n";
$TeX .= '\end{tabular}'."\n";
	    

            $TeX .= '\end{flushright}'."\n";



            $TeX .= '\newpage'."\n\n";
            $TeX .= '\setcounter{footnote}{0}'."\n\n";

        }

        // dokumentum lezárása
        $TeX .= '\end{document}'."\n";
        return pdfLaTeX($TeX, $file);

    }

    function putTeXLevelFejlec($ADAT) {



        $TeX.= "Cím: ".$ADAT['lvaros'].', '.$ADAT['lirsz'].' '.$ADAT['lutca']."\n\n";
        if ($ADAT['varos']!='') $TeX.= "Levelezési cím: ".$ADAT['varos'].', '.$ADAT['irsz'].' '.$ADAT['utca']."\n\n";
#       $TeX.= "Cím: ".$ADAT['varos'].', '.$ADAT['irsz'].' '.$ADAT['utca']."\n\n";
#       if ($ADAT['lvaros']!='') $TeX.= "Levelezési cím: ".$ADAT['lvaros'].', '.$ADAT['lirsz'].' '.$ADAT['lutca']."\n\n";
	$TeX.= "Oktatási azonosító: ".$ADAT['oktid']."\n\n";
	$TeX.= "MaYoR azonosító: ".$ADAT['id'].'/'.$ADAT['OM']."\n\n";
	$TeX.= "Iktatószám: ".$ADAT['iktsz']."\n\n";
	$TeX.= "Tárgy: Értesítés ".$ADAT['nev']." (".str_replace('_','',$ADAT['an'])." ".str_replace('-','.',$ADAT['szuldt']).".) felvételi eredményéről\n\n";

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
