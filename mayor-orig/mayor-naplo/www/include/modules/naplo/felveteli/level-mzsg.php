<?php

    if (__PORTAL_CODE!=='mzsg') {
	die('hiba, nem támogatott intézmény');
    }

    function pdfLevel($file, $ADAT) {

#	global $V2L, $OSZTALYABA;
#	global $IKTSZ;

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
        $TeX .= '\setlength{\headsep}{14pt}'."\n"; // 20pt helyett

        $TeX .= '\cfoot{\tiny \copyright\mayor\ elektronikus adminisztráció - https://mayor.moricz-bp.edu.hu - Nyomtatva: '.date('Y.m.d').'}'."\n";
	$TeX .= '\setlength{\voffset}{0mm}'."\n";
	$TeX .= '\setlength{\headheight}{25mm}'."\n";
	$TeX .= '\renewcommand{\headrulewidth}{0pt}'."\n";
        $TeX .= '\chead{\includegraphics[width=140mm]{/var/mayor/www/skin/classic/module-portal/img/fejlec.png}}'."\n";
#         $TeX .= '\lhead{\includegraphics[width=160mm]{/var/mayor/www/skin/classic/module-felveteli/img/iskola.png}}'."\n";
#        $TeX .= '\rhead{\tiny Felvételi értesítő}';


        $TeX .= '\begin{document}'."\n";
#	$TeX .= ''."\n";
        $TeX .= '\newpage'."\n";
        for ($i = 0; $i < count($ADAT['level']); $i++) {

	    $D = $ADAT['level'][$i];

	    $A['0001']=array('osztalyJel'=>'7.A', 'tagozatNev'=>'6 évfolyamos természettudományi');
	    $A['0002']=array('osztalyJel'=>'7.B', 'tagozatNev'=>'6 évfolyamos társadalomtudományi');
	    $A['0003']=array('osztalyJel'=>'9.C', 'tagozatNev'=>'4 évfolyamos matematika emelt óraszámú');
	    $A['0004']=array('osztalyJel'=>'9.C', 'tagozatNev'=>'4 évfolyamos természettudományi');
	    $A['0005']=array('osztalyJel'=>'9.D', 'tagozatNev'=>'4 évfolyamos törénelem emelt óraszámú');
	    $A['0006']=array('osztalyJel'=>'9.D', 'tagozatNev'=>'4 évfolyamos angol nyelv emelt óraszámú');
	    $A['0007']=array('osztalyJel'=>'9/Ny.E', 'tagozatNev'=>'1+4 évfolyamos angol nyelvi előkészítő');

	    $D['iktsz'] = 'klik037775001/04322-1/2024'; //$IKTSZ[$eredmeny].' ['.$D['vegeredmeny'].']';

	    $TeX .= putLevelFejlec($D, $tagozat);
	    $TeX .= putLevel($D, $A);

	    $TeX .= putAlairas();

	    if ($D['felvett'] != '') {
		$tagozat = $D['felvett'];
		$osztaly = $A[$tagozat]['osztalyJel'];
		$kepzes = $A[$tagozat]['tagozatNev'];
		$TeX .= putTeXLevelFejlec($D, $tagozat);
		$TeX .= putHatarozat('elfogadom',$D, $tagozat);
		$TeX .= putFelvetel($osztaly, $kepzes);
		$TeX .= putIndoklas($tagozat, 'megfelelt', 'a \textbf{felvételi eljárásban elért pontszáma és a felvételi rangsorolás alapján} nyert felvételt');
		$TeX .= putAlairas();
	    }
	    foreach ($D['mashova'] as $tagozat) {
		$TeX .= putTeXLevelFejlec($D, $tagozat);
		$TeX .= putHatarozat('elutasítom',$D, $tagozat);
		$TeX .= putJogorvoslat();
		$TeX .= putIndoklas($tagozat, 'megfelelt', 'de \textbf{egy előbbre rangsorolt tanulmányi területre már felvették}');
		$TeX .= putAlairas();
	    }
	    foreach ($D['helyhiany'] as $tagozat) {
		$TeX .= putTeXLevelFejlec($D, $tagozat);
		$TeX .= putHatarozat('elutasítom', $D, $tagozat);
		$TeX .= putJogorvoslat();
		$TeX .= putIndoklas($tagozat, 'megfelelt', 'de \textbf{a tanulmányi területhez megadott felvehető létszámkeret betelt}, ezért nem nyert felvételt');
		$TeX .= putAlairas();
	    }
	    foreach ($D['elutasitott'] as $tagozat) {
		$TeX .= putTeXLevelFejlec($D, $tagozat);
		$TeX .= putHatarozat('elutasítom', $D, $tagozat);
		$TeX .= putJogorvoslat();
		$TeX .= putIndoklas($tagozat, 'nem felelt meg', '\textbf{a felvételi eljárásban elért pontszáma és a felvételirangsorolás alapján} nem nyert felvételt');
		$TeX .= putAlairas();
	    }

        } // for

        // dokumentum lezárása
        $TeX .= '\end{document}'."\n";
        return pdfLaTeX($TeX, $file);

    }



function putHatarozat($dontes, $D, $tagozat) {
            $TeX .= '\vspace{12pt}'."\n";
	    $TeX .= '\begin{center}'."\n";
	    $TeX .= '\textbf{HATÁROZAT}'."\n\n";;
	    $TeX .= '\end{center}'."\n";
	    $TeX .= '\textbf{'. $D['nev'] . '} (születési helye és ideje: '.$D['szulhely'].', '.str_replace('-','.',$D['szuldt']).'; anyja neve: '.str_replace('_','',$D['an']).';
oktatási azonosítója: '.$D['oId'].'; lakcíme: '.$D['lakcim_irsz'].' '.$D['lakcim_telepules'].', '.$D['lakcim_utcahazszam'].'
továbbiakban: \textsl{Tanuló}) középfokú jelentkezése tárgyában a Tanuló szülei által a Budapest II.
Kerületi II. Móricz Zsigmond Gimnázium (címe: 1025 Budapest, Törökvész út 48-54., OM:
037775) köznevelési intézmény (továbbiakban: \textsl{Intézmény}) '."\n\n";

	    $TeX .= '\begin{center}'."\n";
	    $TeX .= '\textbf{' . $tagozat . ' tagozatkóddal }' . "\n\n";
	    //$TeX .= '\end{center}'."\n";
	    $TeX .= "\n\n" . 'meghirdetett tanulmányi területére benyújtott felvételi kérelmét' . "\n\n";
	    $TeX .= '\vspace{12pt}'."\n";
	    $TeX .= '\textbf{' . $dontes . '.}'."\n\n";
	    $TeX .= '\end{center}'."\n";

	    return $TeX;
}

function putFelvetel($osztaly, $kepzes) {

	$TeX .= '\begin{center}'."\n";
	$TeX .= 'A Tanulót \textbf{a '.$osztaly.' osztályba}, '.$kepzes.' képzésre \newline\textbf{felveszem.}';
	$TeX .= '\end{center}'."\n\n";
	$TeX .= 'A jogorvoslatról való tájékoztatást az általános közigazgatási rendtartásról szóló 2016. évi CL.
törvény 81. (2) bekezdés a) pontja alapján mellőztem.'."\n\n";

	return $TeX;
}

function putJogorvoslat() {
	    $TeX .= '\vspace{12pt}'."\n";
	    $TeX .= '\begin{center}'."\n\n";
	    $TeX .= '\textbf{Jogorvoslat}'."\n\n";
	    $TeX .= '\end{center}'."\n";
	    $TeX .= 'Döntésem ellen a nemzeti köznevelésről szóló 2011. évi CXC. törvény 37.§ (2) bekezdésében
meghatározottak szerint a nagykorú tanuló vagy a kiskorú tanuló szülője a közléstől, ennek
hiányában a tudomására jutástól számított 15 napon belül jogorvoslati eljárást indíthat. Az
eljárást megindító kérelmet a döntést hozó köznevelési intézményt fenntartó Közép-Budai
Tankerületi Központ vezetőjének kell címezni, és azt az intézmény vezetőjéhez kell
benyújtani.'."\n\n";

	    return $TeX;
}

function putIndoklas($tagozat, $megfeleltE, $indok) {
    $TeX .= '\vspace{12pt}'."\n";
    $TeX .= '\begin{center}'."\n\n";
    $TeX .= '\textbf{INDOKOLÁS}'."\n\n";
    $TeX .= '\end{center}'."\n";

    $TeX .= 'A Tanuló a 2024/2025. tanévre szóló felvételi eljárásban az Intézmény '.$tagozat.'
tagozatkóddal meghirdetett tanulmányi terület felvételi
követelményeinek '.$megfeleltE.', '.$indok.'.

\vspace{12pt}
Döntésemet a nemzeti köznevelésről szóló 2011. évi CXC. törvény 50. § (1) bekezdése által
biztosított jogkörömben és a nevelési-oktatási intézmények működéséről és a köznevelési
intézmények névhasználatáról szóló 20/2012. (VIII.31.) EMMI rendelet 41.§ (5) bekezdése
alapján hoztam.\par'."\n\n";

    return $TeX;
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

function putAlairasOrig() {
    $TeX .= '\vspace{20pt}'."\n";
    $TeX .= '\noindent Budapest, 2024. április 29.'.' \par%'."\n";

    $TeX .= '\begin{flushright}'."\n";
    $TeX .= '\begin{tabular}{ccc}'."\n";
    $TeX .= '&\includegraphics[width=30mm]{/var/mayor/www/skin/classic/module-naplo/img/pecset.jpg}&\includegraphics[width=50mm]{/var/mayor/www/skin/classic/module-naplo/img/signo.png}\\\\ '."\n";
    $TeX .= '&&\rule{4cm}{0.1pt}\\\\ '."\n";
    $TeX .= '&&Veleczki Viktória\\\\ '."\n";
    $TeX .= '&&igazgató\\\\ '."\n";
    $TeX .= '\end{tabular}'."\n";
    $TeX .= '\end{flushright}'."\n";

    $TeX .= '\newpage'."\n\n";
    $TeX .= '\setcounter{footnote}{0}'."\n\n";
    return $TeX;
}

/*
function regi_szoveg() {
#	    $TeX .= '\parskip'."\n";
            $TeX .= '\vspace{20pt}'."\n";

    	    $TeX .= '\noindent '.'Kedves Felvételiző! Tisztelt Szülő!'."\n\n";

            $TeX .= '\vspace{20pt}'."\n";

	    $TeX .= '{\baselineskip16pt'."\n";

	    if ($eredmeny>2) {

		$TeX .= 'Nagy Örömmel értesítem, hogy '.$D['nev'].' tanuló az általános iskolai eredménye és a felvételi vizsgán mutatott teljesítménye alapján (a Felvételi Központ által megküldött végleges listák sze\-rint)'."\n\n";
		$TeX .= '\begin{center}'."\n";
		$TeX .= '\textbf{ felvételt nyert }'."\n\n";
		$TeX .= '\end{center}'."\n";
		$TeX .= '\begin{center}'."\n";
		$TeX .= ' a Budapest II. Kerületi Móricz Zsigmond Gimnázium '.$A[$D['extra']]['osztalyJel']." osztályába.\n\n";
		$TeX .= '('.$A[$D['extra']]['tagozatNev'].")\n\n";
		$TeX .= '\end{center}'."\n\n";
		//if (defined('_SZULOI_ERTEKEZLET_IDOPONTBAN')) {
		    $TeX .= 'Tájékoztatom, hogy az első szülői értekezlet 2024. május 16-án (hétfőn) 17:00-kor lesz, amelyre ezúton hívom meg a Szülőket. A további teendőkkel (pl. beiratkozás, nyelvi szintfelmérő stb.) kapcsolatos teendőkért kérjük ne felejtsék el rendszeresen felkeresni honlapunkat.'."\n";
		//}

#		$TeX .= 'Figyelembe véve a jelenlegi vírushelyzetet, a nyár folyamán két (egymástól különböző) gólya-napot fogunk tartani. Ezekről bővebb tájékoztatást a szülői értekezleten kapnak majd.'."\n";
		$TeX .= "\n\n";
        	$TeX .= '\vspace{14pt}'."\n";
		$TeX .= 'Remélem, az iskolánkban eltöltött évek hasznosak és eredményesek lesznek.'."\n\n";

    	    } elseif ($eredmeny==2) {

        	$TeX .= 'Sajnálattal vettük tudomásul, hogy '.$D['nev'].' másik iskolát választott, ezért nem vehettük fel a Budapes II. Kerületi Móricz Zsigmond Gimnáziumba.'."\n\n";
        	$TeX .= '\vspace{14pt}'."\n";
        	$TeX .= 'Remélem, hogy a választása jó döntésnek fog bizonyulni. Sikeres tanulmányi éveket és minden jót kívánok!'."\n\n";

    	    } elseif ($eredmeny==1) {

//        	$TeX .= 'Sajnálattal értesítem, hogy '.$D['nev'].' tanuló a felvételi vizsgán megfelelt, de helyhiány miatt nem nyert felvételt gimnáziumunkba.'."\n\n";
//        	$TeX .= '\vspace{14pt}'."\n";
//        	$TeX .= 'A Szülő e döntés ellen jogorvoslati lehetőséggel (a kézhezvételtől számított 15 napon belül) akkor élhet, ha az utolsó helyen megjelölt iskola elutasító értesítése megérkezett. Jogorvoslati kérelmét az általános iskola igazgatójának kell benyújtani. '
//			.'Felhívom figyelmét arra, hogy gyermeke tankötelezettségének teljesítésére vonatkozó kérelemmel a lakóhely szerinti önkormányzat jegyzőjéhez fordulhat.'."\n\n";

    	    } elseif ($eredmeny==0) {

        	$TeX .= 'Sajnálattal értesítem, hogy '.$D['nev'].' tanuló - a Felvételi Központ által megküldött végleges listák szerint - nem nyert felvételt a Budapest II. Kerületi Móricz Zsigmond Gimnáziumba.'."\n\n";
        	$TeX .= '\vspace{14pt}'."\n\n";
        	$TeX .= 'A Szülő e döntés ellen jogorvoslati lehetőséggel (a kézhezvételtől számított 15 napon belül) jogorvoslati lehetőséggel élhet, ha az utolsó megjelölt iskola elutasító értesítése is megérkezett. Jogorvoslati kérelmét az általános iskola igazgatójának kell benyújtania. Felhívom a figyelmét arra, hogy gyermeke tankötelezettségének teljesítésére vonatkozó kérelemmel a lakóhely szerinti önkormányzat jegyzőjéhez fordulhat.'."\n\n";

    	    } else {

    	    }
	    $TeX .= '}'."\n";
            $TeX .= '\vspace{20pt}'."\n";
            $TeX .= '\noindent Budapest, 2024. május 2.'.' \par%'."\n";


            $TeX .= '\vspace{14pt}'."\n\n";
	    if ($eredmeny > 2 ) $TeX .= '\vspace{14pt}'."\n\n";

}


*/


    function putTeXLevelFejlec($D, $tagozat = '0000') {

	$TeX .= '\begin{flushleft}'."\n";

	$TeX.= '\begin{tabular}{l l}'."\n\n";
	$TeX.= "\hspace*{6 cm} & Iktatószám: ".$D['iktsz']." \\\\"."\n";
	$TeX.= "\hspace*{6 cm} & Oktatási azonosító: ".$D['oId']."\\\\\n";
	$TeX.= "\hspace*{6 cm} & MaYoR hivatkozási szám: ".$D['eredmenyId'].'/'.$tagozat.'/'.$D['OM']."\\\\\n";
	$TeX.= "\hspace*{6 cm} & Döntéshozó szerv:\\\\\n";
	$TeX.= "\hspace*{6 cm} & Budapest II. Kerületi Móricz Zsigmond Gimnázium\\\\\n";
	$TeX.= "\hspace*{6 cm} & Ügyintéző: Bärnkopf Bence igh.\\\\\n";
	$TeX.= "\hspace*{6 cm} & Tárgy: Középfokú beiskolázás -- felvételi döntés\\\\\n\n";
	$TeX.= '\end{tabular}'."\n\n";

	$TeX .= '\end{flushleft}'."\n";

        return $TeX;

#	$TeX.= '&\\\\%'."\n\n";
#        if ($D['lakcim_telepules']!='')
#	    $TeX.= "\hspace*{6 cm} & Cím: ".$D['lakcim_telepules'].', '.$D['lakcim_irsz'].' '.$D['lakcim_utcahazszam']."\\\\\n";
#        if ($D['tartozkodasi_telepules']!='')
#	    $TeX.= "\hspace*{6 cm} & Levelezési cím: ".$D['tartozkodasi_telepules'].', '.$D['tartozkodasi_irsz'].' '.$D['tartozkodasi_utcahazszam']."\\\\\n";
#	$TeX.= "Tárgy: Értesítés ".$D['nev']." (".str_replace('_','',$D['an'])." ".str_replace('-','.',$D['szuldt']).".) felvételi eredményéről\\\\\n";
#        $TeX .= '\vspace{14pt}'."\n\n";

#        $TeX .= '&\\\\%'."\n\n";


#        $TeX.= '\hrule%'."\n";

#        $TeX.= '%'."\n";

#        $TeX.= '\vskip2cm\alap';

#        $TeX.= '\item{}C.mzett: '.$D['nev'].' .s Sz.lei';
#        $TeX.= '\item{}Postai c.m: '.$cim;
#        $TeX.= '\item{}Iktat.sz.m: '.$IKTSZ[$eredmeny].' ('.$id.')%'."\n";
#        $TeX.= '\vskip0.8cm';
#        $TeX.= '{{Tárgy}: {Értesítés '.$D['nev'].' (';
#            if ($an!="") $TeX.= 'a.n.: '.$an.', ';
#        $TeX.= 'sz.l.: '.str_replace('-','.',$szul_dt).'.) felv.teli eredm.ny.r.l}}';

#        $TeX.= '%'."\n";


    }


    function putLevelFejlec($D, $tagozat = '0000') {

	$TeX .= '\begin{flushleft}'."\n\n";
	$TeX .= "\ \n\n";
        $TeX .= '\vspace{12pt}'."\n\n";

        if ($D['lakcim_telepules']!='')
	    $TeX.= "Cím: ".$D['lakcim_telepules'].', '.$D['lakcim_irsz'].' '.$D['lakcim_utcahazszam']."\n\n";
        if ($D['ert_telepules']!='')
	    $TeX.= 'Levelezési cím: '.$D['ert_irsz'].' '.$D['ert_telepules'].', '.$D['ert_utcahazszam']."\n\n";
	$TeX.= "Oktatási azonosító: ".$D['oId']."\n\n";
	$TeX.= "MaYoR hivatkozási szám: ".$D['eredmenyId'].'/'.$tagozat.'/'.$D['OM']."\n\n";
	$TeX.= "Tárgy: Értesítés ".$D['nev']." felvételi eredményéről\n\n";
	$TeX.= '\hspace{11.5mm}(a. n.: '.str_replace('_','',$D['an']).'; szül.: '.$D['szulhely'].' '.str_replace('-','.',$D['szuldt']).".)\n\n";
	$TeX .= '\end{flushleft}'."\n";

# 	$TeX .= '\parskip'."\n";
        $TeX .= '\vspace{24pt}'."\n";
	$TeX .= '\noindent '.'Kedves Felvételiző! Tisztelt Szülő!'."\n\n";
        $TeX .= '\vspace{20pt}'."\n";

        return $TeX;

    }

    function putLevel($D, $A) {
	$TeX .= '{\baselineskip16pt'."\n";
	    if ($D['felvett'] != '') {
		$TeX .= 'Nagy Örömmel értesítem, hogy \textbf{'.$D['nev'].'} tanuló az általános iskolai eredménye és a felvételi vizsgán mutatott teljesítménye alapján '.
			'(a Felvételi Központ által megküldött végleges listák sze\-rint)'."\n\n";
		$TeX .= '\begin{center}'."\n";
		$TeX .= '\textbf{ felvételt nyert }'."\n\n";
		$TeX .= '\end{center}'."\n";
		$TeX .= '\begin{center}'."\n";
		$TeX .= ' a Budapest II. Kerületi Móricz Zsigmond Gimnázium '.$A[$D['felvett']]['osztalyJel']." osztályába.\n\n";
		$TeX .= '('.$A[$D['felvett']]['tagozatNev']." képzés)\n\n";
		$TeX .= '\end{center}'."\n\n";
		$TeX .= 'Tájékoztatom, hogy az első szülői értekezlet terveink szerint 2024. június 13-án (cssütörtökön) 17:00-kor lesz, amelyre ezúton hívom meg a Szülőket. '
			.'A további információkért (pl. szülői értekezlet, beiratkozás, nyelvi szintfelmérő stb.) kérjük, rendszeresen keressék fel honlapunkat '
			.'(https://moricz-bp.hu).'."\n";
#		$TeX .= 'Figyelembe véve a jelenlegi vírushelyzetet, a nyár folyamán két (egymástól különböző) gólya-napot fogunk tartani. Ezekről bővebb tájékoztatást a szülői értekezleten kapnak majd.'."\n";
		$TeX .= "\n\n";
        	$TeX .= '\vspace{14pt}'."\n";
		$TeX .= 'Remélem, az iskolánkban eltöltött évek hasznosak és eredményesek lesznek.'."\n\n";
	    } elseif (count($D['mashova']) != 0) {
        	$TeX .= 'Sajnálattal vettük tudomásul, hogy \textbf{'.$D['nev'].'} másik iskolát választott, ezért nem vehettük fel a Budapes II. Kerületi Móricz Zsigmond Gimnáziumba.'."\n\n";
        	$TeX .= '\vspace{14pt}'."\n";
        	$TeX .= 'Remélem, hogy a választása jó döntésnek fog bizonyulni. Sikeres tanulmányi éveket és minden jót kívánok!'."\n\n";
	    } elseif (count($D['helyhiany']) != 0) {
        	$TeX .= 'Sajnálattal értesítem, hogy \textbf{'.$D['nev'].'} tanuló a felvételi vizsgán megfelelt, de helyhiány miatt nem nyert felvételt gimnáziumunkba.'."\n\n";
        	$TeX .= '\vspace{14pt}'."\n";
        	$TeX .= 'A Szülő e döntés ellen a kézhezvételtől számított 15 napon belül jogorvoslati lehetőséggel élhet. '
			.'Jogorvoslati kérelmét ez esetben az iskola igazgatójának kell benyújtania. '
			.'A benyújtást követően a középfokú jelentkezést elutasító igazgatói határozattal szembeni jogorvoslati eljárásban a középfokú intézmény '
			.'fenntartója -- a Közép-Budapi Tankerületi Központ -- jár el és hoz másodfokú döntést. '
			.'Felhívom továbbá figyelmét arra, hogy gyermeke tankötelezettségének teljesítésére vonatkozó kérelemmel '
			.'a lakóhely szerinti önkormányzat jegyzőjéhez fordulhat.'."\n\n";
	    } else {
        	$TeX .= 'Sajnálattal értesítem, hogy \textbf{'.$D['nev'].'} tanuló - a Felvételi Központ által megküldött végleges listák szerint - nem nyert felvételt a '
			.'Budapest II. Kerületi Móricz Zsigmond Gimnáziumba.'."\n\n";
        	$TeX .= '\vspace{14pt}'."\n\n";
        	$TeX .= 'A Szülő e döntés ellen a kézhezvételtől számított 15 napon belül jogorvoslati lehetőséggel élhet. '
			.'Jogorvoslati kérelmét ez esetben az iskola igazgatójának kell benyújtania. '
			.'A benyújtást követően a középfokú jelentkezést elutasító igazgatói határozattal szembeni jogorvoslati eljárásban a középfokú intézmény '
			.'fenntartója -- a Közép-Budapi Tankerületi Központ -- jár el és hoz másodfokú döntést. '
			.'Felhívom továbbá figyelmét arra, hogy gyermeke tankötelezettségének teljesítésére vonatkozó kérelemmel '
			.'a lakóhely szerinti önkormányzat jegyzőjéhez fordulhat.'."\n\n";
	    }
	$TeX .= '}'."\n\n";

	return $TeX;
    }

?>
    