<?php

    if (__PORTAL_CODE!=='kanizsay') {
	die('hiba, nem támogatott intézmény');
    }

    function pdfLevel($file, $ADAT) {

#	global $V2L, $OSZTALYABA;
#        global $IKTSZ;

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

        $TeX .= '\cfoot{\tiny \copyright\mayor\ elektronikus adminisztráció - https://kanizsay.edu.hu - Nyomtatva: '.date('Y.m.d').'}'."\n";
	$TeX .= '\setlength{\voffset}{0mm}'."\n";
	$TeX .= '\setlength{\headheight}{40mm}'."\n";
	$TeX .= '\renewcommand{\headrulewidth}{0pt}'."\n";
        $TeX .= '\chead{\includegraphics[width=160mm]{/var/mayor/www/skin/classic/module-portal/img/fejlec.png}}'."\n";
#         $TeX .= '\lhead{\includegraphics[width=160mm]{/var/mayor/www/skin/classic/module-felveteli/img/iskola.png}}'."\n";
#        $TeX .= '\rhead{\tiny Felvételi értesítő}';


        $TeX .= '\begin{document}'."\n";
#	$TeX .= ''."\n";
        $TeX .= '\newpage'."\n";
        for ($i = 0; $i < count($ADAT['level']); $i++) {

	    $D = $ADAT['level'][$i];
	    $eredmeny = $D['vegeredmeny'];

	    if (strstr($D['vegeredmeny'],'nem nyert')!==false) $eredmeny=0; elseif(strstr($D['extra'],'202')!==false) $eredmeny = 3; else $eredmeny=0;

$A['eü. 9.B']=array('osztalyJel'=>'9.B', 'tagozatNev'=>'Technikumi képzés egészségügyi ágazatban');
$A['eü. 9.C']=array('osztalyJel'=>'9.C', 'tagozatNev'=>'Technikumi képzés egészségügyi ágazatban');
$A['gimi 9.G']=array('osztalyJel'=>'9.G', 'tagozatNev'=>'Gimnáziumi képzés');
$A['nyek 9/Ny.N']=array('osztalyJel'=>'9/Ny.N', 'tagozatNev'=>'Gimnáziumi képzés nyelvi előkészítő évfolyammal');
$A['ped 9.C']=array('osztalyJel'=>'9.C', 'tagozatNev'=>'Szakgimnáziumi képzés pedagógiai ágazatban');
$A['szoc 9.C']=array('osztalyJel'=>'9.C', 'tagozatNev'=>'Technikumi képzés szociális ágazatban');

$A['2022/a/eü']=array('osztalyJel'=>'9.A', 'tagozatNev'=>'Technikumi képzés egészségügyi ágazatban');
$A['2022/b/szoc']=array('osztalyJel'=>'9.B', 'tagozatNev'=>'Technikumi képzés szociális ágazatban');
$A['2022/g/gimi']=array('osztalyJel'=>'9.G', 'tagozatNev'=>'Gimnáziumi képzés');
$A['2022/ny/nyek']=array('osztalyJel'=>'9/Ny.N', 'tagozatNev'=>'Gimnáziumi képzés nyelvi előkészítő évfolyammal');
$A['2022/b/ped']=array('osztalyJel'=>'9.B', 'tagozatNev'=>'Szakgimnáziumi képzés pedagógiai ágazatban');

	    $nev = $D['nev'];
	    $kernev = $nev;
	    $tagozatId = $eredmeny;
	    $D['iktsz'] = $IKTSZ[$eredmeny].' ['.$D['vegeredmeny'].']';

	    $TeX .= '\begin{flushleft}'."\n";

	    $TeX .= putTeXLevelFejlec($D);

	    $TeX .= '\end{flushleft}'."\n";

#	    $TeX .= '\parskip'."\n";
            $TeX .= '\vspace{40pt}'."\n";
            $TeX .= '\vspace{20pt}'."\n";

    	    $TeX .= '\noindent '.'Kedves Felvételiző! Tisztelt Szülő!'."\n\n";

            $TeX .= '\vspace{20pt}'."\n";

	    $TeX .= '{\baselineskip16pt'."\n";

	    if ($eredmeny>2) {

		$TeX .= 'Nagy Örömmel értesítem, hogy '.$nev.' tanuló az általános iskolai eredménye és a felvételi vizsgán mutatott teljesítménye alapján (a Felvételi Központ által megküldött végleges listák sze\-rint)'."\n\n";
		$TeX .= '\begin{center}'."\n";
		$TeX .= '\textbf{ felvételt nyert }'."\n\n";
		$TeX .= '\end{center}'."\n";
		$TeX .= '\begin{center}'."\n";
		$TeX .= ' a Kanizsay Dorottya Katolikus Középiskola '.$A[$D['extra']]['osztalyJel']." osztályába.\n\n";
		$TeX .= '('.$A[$D['extra']]['tagozatNev'].")\n\n";
		$TeX .= '\end{center}'."\n\n";
		//if (defined('_SZULOI_ERTEKEZLET_IDOPONTBAN')) {
		    $TeX .= 'Tájékoztatom, hogy az első szülői értekezlet 2022. május 16-án (hétfőn) 17:00-kor lesz, amelyre ezúton hívom meg a Szülőket. A további teendőkkel (pl. beiratkozás, nyelvi szintfelmérő stb.) kapcsolatos teendőkért kérjük ne felejtsék el rendszeresen felkeresni honlapunkat.'."\n";
		//}

#		$TeX .= 'Figyelembe véve a jelenlegi vírushelyzetet, a nyár folyamán két (egymástól különböző) gólya-napot fogunk tartani. Ezekről bővebb tájékoztatást a szülői értekezleten kapnak majd.'."\n";
		$TeX .= "\n\n";
        	$TeX .= '\vspace{14pt}'."\n";
		$TeX .= 'Remélem, az iskolánkban eltöltött évek hasznosak és eredményesek lesznek.'."\n\n";

    	    } elseif ($eredmeny==2) {

//        	$TeX .= 'Sajnálattal vettük tudomásul, hogy '.$kernev.' másik iskolát választott, ezért nem vehettük fel a Városmajori Gimnáziumba.'."\n\n";
//        	$TeX .= '\vspace{14pt}'."\n";
//        	$TeX .= 'Remélem, hogy a választása jó döntésnek fog bizonyulni. Sikeres tanulmányi éveket és minden jót kívánok!'."\n\n";

    	    } elseif ($eredmeny==1) {

//        	$TeX .= 'Sajnálattal értesítem, hogy '.$nev.' tanuló a felvételi vizsgán megfelelt, de helyhiány miatt nem nyert felvételt gimnáziumunkba.'."\n\n";
//        	$TeX .= '\vspace{14pt}'."\n";
//        	$TeX .= 'A Szülő e döntés ellen jogorvoslati lehetőséggel (a kézhezvételtől számított 15 napon belül) akkor élhet, ha az utolsó helyen megjelölt iskola elutasító értesítése megérkezett. Jogorvoslati kérelmét az általános iskola igazgatójának kell benyújtani. '
//			.'Felhívom figyelmét arra, hogy gyermeke tankötelezettségének teljesítésére vonatkozó kérelemmel a lakóhely szerinti önkormányzat jegyzőjéhez fordulhat.'."\n\n";

    	    } elseif ($eredmeny==0) {

        	$TeX .= 'Sajnálattal értesítem, hogy '.$nev.' tanuló - a Felvételi Központ által megküldött végleges listák szerint - nem nyert felvételt a Kanizsay Dorottya Katolikus Középiskolába.'."\n\n";
        	$TeX .= '\vspace{14pt}'."\n\n";
        	$TeX .= 'A Szülő e döntés ellen jogorvoslati lehetőséggel (a kézhezvételtől számított 15 napon belül) jogorvoslati lehetőséggel élhet, ha az utolsó megjelölt iskola elutasító értesítése is megérkezett. Jogorvoslati kérelmét az általános iskola igazgatójának kell benyújtania. Felhívom a figyelmét arra, hogy gyermeke tankötelezettségének teljesítésére vonatkozó kérelemmel a lakóhely szerinti önkormányzat jegyzőjéhez fordulhat.'."\n\n";

    	    } else {

    	    }
	    $TeX .= '}'."\n";
            $TeX .= '\vspace{20pt}'."\n";
            $TeX .= '\noindent Budapest, 2022. április 23.'.' \par%'."\n";


            $TeX .= '\vspace{14pt}'."\n\n";
	    if ($eredmeny > 2 ) $TeX .= '\vspace{14pt}'."\n\n";

            $TeX .= '\begin{flushright}'."\n";



$TeX .= '\begin{tabular}{ccc}'."\n";

//if ($eredmeny <= 2 ) {
//    $TeX .= '&&\includegraphics[width=30mm]{/var/mayor/www/skin/classic/module-felveteli/img/bp.png}\\\\ '."\n";
//}
$TeX .= '&&\rule{4cm}{0.1pt}\\\\ '."\n";
$TeX .= '&&Bärnkopf Péter\\\\ '."\n";
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


        if ($ADAT['lakcim_telepules']!='') $TeX.= "Cím: ".$ADAT['lakcim_telepules'].', '.$ADAT['lakcim_irsz'].' '.$ADAT['lakcim_utcahazszam']."\n\n";
        if ($ADAT['tartozkodasi_telepules']!='') $TeX.= "Levelezési cím: ".$ADAT['tartozkodasi_telepules'].', '.$ADAT['tartozkodasi_irsz'].' '.$ADAT['tartozkodasi_utcahazszam']."\n\n";
	$TeX.= "Oktatási azonosító: ".$ADAT['oId']."\n\n";
	$TeX.= "MaYoR azonosító: ".$ADAT['felveteliId'].'/'.$ADAT['omkod']."\n\n";
	$TeX.= "Iktatószám: C8-144/2022"."\n\n";
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
