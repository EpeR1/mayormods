<?php

   function getValasztottTankorok($tanev, $szemeszter, $OSZTALYIDK) {

        if ($tanev=='') {
            $tanevAdat = $_TANEV;
        } else {
            $tanevAdat = getTanevAdat($tanev);
        }

        $tanevDbNev = tanevDbNev(__INTEZMENY, $tanev);

        $DT['tolDt'] = $tanevAdat['kezdesDt'];
        $DT['igDt']  = $tanevAdat['zarasDt'] ;

        $tankorBlokkok = getTankorBlokkok($tanev);
	$TID2B = $TID2BN = array();
	if (is_array($tankorBlokkok) && is_array($tankorBlokkok['idk']))
        foreach ($tankorBlokkok['idk'] as $blokkId => $TB) {
	    for ($j = 0; $j < count($TB); $j++) {
		$TID2B[$TB[$j]][] = $blokkId;
		$TID2BN[$TB[$j]][] = $tankorBlokkok['blokkNevek'][$blokkId];
	    }
	}
	$v = array();
	if (is_array($OSZTALYIDK) && count($OSZTALYIDK) > 0) {
	    $W = " AND osztalyId IN (".implode(',', array_fill(0, count($OSZTALYIDK), '%u')).")";
	    $v = $OSZTALYIDK;
	}
	$q = "SELECT DISTINCT tankorId, targyId, kovetelmeny, min, max, tanev, szemeszter, oraszam, tankorNev 
		FROM tankor LEFT JOIN tankorSzemeszter USING (tankorId) JOIN tankorOsztaly USING (tankorId)  
		WHERE tanev=%u and szemeszter=%u and tankor.felveheto =1".$W." ORDER BY tankorNev,tankor.tankorId";
	array_unshift($v, $tanev, $szemeszter);
	$felvehetoTankorok = db_query($q,array('debug'=>false,'fv' => 'getValasztottTankorok', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => $v));
        for ($i = 0; $i < count($felvehetoTankorok); $i++) {
	    $_tankorId = $felvehetoTankorok[$i]['tankorId'];
	    if ($_tankorId != null) {
		$fTankorok[$_tankorId] = $felvehetoTankorok[$i];
		$fTankorok[$_tankorId]['blokkIdk'] = $TID2B[$_tankorId];
		$fTankorok[$_tankorId]['blokkNevek'] = $TID2BN[$_tankorId];
		//$felvehetoTankorok[$_tankorid]['letszam'] =  getTankorLetszam($felvehetoTankorok[$i]['tankorId'],$DT);
		$fTankorok[$_tankorId]['tanarok'] =  getTankorTanaraiByInterval($_tankorId, array('tolDt' => $DT['tolDt'], 'igDt' => $DT['igDt'], 'result' => 'nevsor'));
		$FT[] = $_tankorId;
	    }
        }
	
	if (is_array($FT) && count($FT) > 0) {
	    if (is_array($OSZTALYIDK) && count($OSZTALYIDK)>0) {
		$W .= " AND osztalyDiak.beDt<='".$DT['tolDt']."' AND (osztalyDiak.kiDt IS NULL OR '".$DT['tolDt']."'<=osztalyDiak.kiDt)";
		$v = mayor_array_join($FT, $OSZTALYIDK);
	    } else { $v = $FT; }
	    $q = "SELECT tankorId,diakId FROM tankorDiak LEFT JOIN diak USING (diakId) LEFT JOIN osztalyDiak USING (diakId) 
		    WHERE tankorId IN (".implode(',', array_fill(0, count($FT), '%u')).") $W
		    ORDER BY CONCAT_WS(' ',viseltCsaladinev,viseltUtonev)";
	    $r = db_query($q,array('debug'=>false,'fv' => 'getValasztottTankorok', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => $v));
    	    for ($i = 0; $i < count($r); $i++) {
		$felvett[$r[$i]['diakId']][] = $r[$i]['tankorId'];
	    }
	}
	
	$ADAT['felveheto'] = $fTankorok;
	$ADAT['felvett'] = $felvett;
        return $ADAT;
    }

    function texLevelGeneralasMasodikNyelvValasztas($ADAT) {

	$return = '';
	$return .= '
\documentclass[8pt]{article}

\usepackage[a5paper]{geometry} % A5-os méret
\usepackage[utf8]{inputenc} % UTF-8 kódolású forrás
\usepackage{t1enc}
\usepackage[magyar]{babel} % magyar elválasztási szabályok
\frenchspacing % a magyar tipográfiai szabályoknak megfelelő szóközök írásjelek után
\usepackage{booktabs} % táblázatok magasabb szintű formázása
\usepackage{fancyhdr} % Ritkítás
\pagestyle{fancy}
\def\mayor{%
\font\mayorfnt=cmsl4%
\font\Mayorfnt=cmsl6
{\mayorfnt\lower0.5ex\hbox{\lower-0.5ex\hbox{Ma}\kern-0.3em\lower0.25ex\hbox{\Mayorfnt Y}\kern-0.2em\hbox{o}\lower0ex\hbox{R}}}}
\renewcommand{\footnotesize}{\fontsize{6pt}{8pt}\selectfont}
%\addtolength{\skip\footins}{2mm}
\addtolength{\textheight}{16mm}
\setlength{\footskip}{26pt}
\setlength{\headsep}{24pt}
\lhead{\tiny '.$ADAT['intezmeny']['nev'].'}
\rhead{\tiny 2. nyelv jelentkezés '.$ADAT['tanev'].'}
\cfoot{\tiny \copyright\mayor\ elektronikus napló - Nyomtatva: '.date('Y.m.d.').'}
\begin{document}
';
        $Tankorok = $tankorNev = $ADAT['valasztott']['felveheto'];
        if (is_array($ADAT['valasztott']['felvett'])) foreach ($ADAT['valasztott']['felvett'] as $diakId => $tankorIds) {
            $diakNev = $ADAT['diakAdat'][$diakId]['diakNev'];
            $diakOsztaly = $ADAT['osztalyok'][ $ADAT['diakOsztaly'][$diakId][0] ]['osztalyJel'];

	    $return .= '
\begin{center}
{\large\bfseries J E L E N T K E Z É S}\\\\
\small

\vspace{12pt}

A '.$ADAT['tanev'].'. szeptemberében induló\\\\
második idegen nyelv képzésre

\vspace{12pt}

{\bfseries\normalsize '.$diakNev.'}\\\\
'.$diakOsztaly.' osztály\\\\
\end{center}

\vspace{10pt}

\footnotesize
A Városmajori Gimnázium Pedagógiai Programja szerint az iskola tanulói a 9-12. évfolyamon
(kötelező módon) két különböző idegen nyelvet tanulnak. Az első idegen nyelv az adott osztálynak, 
ill. a nyelvi előkészítő osztály adott csoportjának már előzetesen meghatározott, azonban a második 
nyelvet mindenki – az iskola lehetőségeinek figyelembevétele mellett – szabadon választhatja meg. 
Amennyiben a választott idegen nyelvet elegendő számú diák jelölte meg, úgy lehetőség van a 
nyelvi csoport indítására. (A csoportok évfolyamszintű bontásban kerülnek kialakításra.) A nyelvi 
csoportok várható létszáma 12-18 fő közötti lesz. A 9. évfolyamtól a 12. évfolyam végéig ez a 
tantárgy is ugyanolyan kötelező lesz, mint a többi tárgy. 
nem lehet hiányozni róla és jegyet kell szerezni belőle.) 

A fentiek tudomásul vételével, az internetes jelentkezési felületen a '.$ADAT['tanev'].'/'.($ADAT['tanev']+1).' tanév 
9. évfolyamos diákjai számára meghirdetett képzések közül általam választott 2. idegen nyelv:

\vspace{16pt}

\begin{tabular}{l|l|c|l}
Tankör neve & Blokk & Óraszám & Tanár \\\\ 
\toprule
%%\hline
';



            for ($i = 0; $i < count($tankorIds); $i++) {
                $tankorId = $tankorIds[$i];
                $tankorNev = $Tankorok[$tankorId]['tankorNev'];
                $oraszam = intval($Tankorok[$tankorId]['oraszam']);
		if (is_array($Tankorok[$tankorId]['tanarok']) && count($Tankorok[$tankorId]['tanarok']) > 0) 
		    $tanarNev = $Tankorok[$tankorId]['tanarok'][0]['tanarNev'];
		else $tanarNev = '{\slshape n.a.}';
                if (is_array($Tankorok[$tankorId]['blokkNevek'])) $blokkNev = implode(', ', $Tankorok[$tankorId]['blokkNevek']);
                else $blokkNev = '';
		$return .= '\vbox to 1.2em {}'.$tankorNev.' & '.$blokkNev.' & '.$oraszam.' & '.$tanarNev.' \\\\ 
\hline';

//echo $tankorId.' '.$tankorNev.' '.$oraszam.':'.$blokkNev.'<br>';
//echo $diakId.' '.$diakNev.' '.$diakOsztaly.'<hr>';
//echo '<pre>';
//var_dump($ADAT); die();
            }

            $return .= '
%%\bottomrule
\end{tabular}

\vspace{16pt}


Amennyiben a választott nyelv az angol vagy a német, úgy a megfelelő szó aláhúzásával kérjük 
megadni, hogy az adott nyelvet kezdő vagy haladó szintről szeretné elkezdeni tanulni:

\begin{center}
\vspace{10pt}

{
\bfseries
\begin{tabular}{ccc}
kezdő szintet választom&\hspace{1cm}\ &haladó szintről szeretném kezdeni\\\\
\end{tabular}
}

\vspace{10pt}

(A haladó szintet választóknak egy szintfelmérő vizsgán kell részt venniük, ami alapján a nyelvi 
csoportbesorolást elvégezzük.)

\vspace{12pt}

{\slshape Az aláírt jelentkezési lap osztályfőnöknél történő leadásának határideje:} {\bfseries '.$ADAT['leadasiHatarido'].'}

\vspace{20pt}
\begin{flushleft}
{\scriptsize Budapest, '.$ADAT['tanev'].'. április}
\end{flushleft}
\vspace{20pt}\slshape\scriptsize
\begin{tabular}{ccc}
\rule{3.5cm}{0.1pt}&\hspace{3cm}\ &\rule{3.5cm}{0.1pt}\\\\
tanuló&&szülő/gondviselő\\\\
\end{tabular}

\end{center}
\newpage %%%%% új oldal %%%%%';


        }

	$return .= '
\end{document}';


	return $return;
    }

/* ---- eredeti ---- */

    function texLevelGeneralas($ADAT) {

	$return = '';
	$return .= '
\documentclass[8pt]{article}

\usepackage[a5paper]{geometry} % A5-os méret
\usepackage[utf8]{inputenc} % UTF-8 kódolású forrás
\usepackage{t1enc}
\usepackage[magyar]{babel} % magyar elválasztási szabályok
\frenchspacing % a magyar tipográfiai szabályoknak megfelelő szóközök írásjelek után
\usepackage{booktabs} % táblázatok magasabb szintű formázása
\usepackage{fancyhdr} % Ritkítás
\pagestyle{fancy}
\def\mayor{%
\font\mayorfnt=cmsl4%
\font\Mayorfnt=cmsl6
{\mayorfnt\lower0.5ex\hbox{\lower-0.5ex\hbox{Ma}\kern-0.3em\lower0.25ex\hbox{\Mayorfnt Y}\kern-0.2em\hbox{o}\lower0ex\hbox{R}}}}
\renewcommand{\footnotesize}{\fontsize{6pt}{8pt}\selectfont}
%\addtolength{\skip\footins}{2mm}
\addtolength{\textheight}{16mm}
\setlength{\footskip}{26pt}
\setlength{\headsep}{24pt}
\lhead{\tiny '.$ADAT['intezmeny']['nev'].'}
\rhead{\tiny Fakultációs jelentkezés '.$ADAT['tanev'].'}
\cfoot{\tiny \copyright\mayor\ elektronikus napló - Nyomtatva: '.date('Y.m.d.').'}
\begin{document}
';
        $Tankorok = $tankorNev = $ADAT['valasztott']['felveheto'];
        if (is_array($ADAT['valasztott']['felvett'])) foreach ($ADAT['valasztott']['felvett'] as $diakId => $tankorIds) {
            $diakNev = $ADAT['diakAdat'][$diakId]['diakNev'];
            $diakOsztaly = $ADAT['osztalyok'][ $ADAT['diakOsztaly'][$diakId][0] ]['osztalyJel'];

	    $return .= '
\begin{center}
{\large\bfseries J E L E N T K E Z É S}\\\\
\small

\vspace{12pt}

A '.$ADAT['tanev'].'. szeptemberében induló\\\\
közép- és emeltszintű érettségire előkészítő képzésekre

\vspace{12pt}

{\bfseries\normalsize '.$diakNev.'}\\\\
'.$diakOsztaly.' osztály\\\\

\vspace{12pt}

A '.$ADAT['tanev'].'/'.($ADAT['tanev']+1).' tanév 11. évfolyamos diákjai számára meghirdetett képzések közül
–~az internetes jelentkezés adatai alapján~– 
%–~az elektronikus adminisztrációs rendszer adatai alapján~–
az alábbi képzéseket választottam: 

\vspace{16pt}

\begin{tabular}{l|l|c|l}
Tankör neve & Blokk & Óraszám & Tanár \\\\ 
\toprule
\hline
';



            for ($i = 0; $i < count($tankorIds); $i++) {
                $tankorId = $tankorIds[$i];
                $tankorNev = $Tankorok[$tankorId]['tankorNev'];
                $oraszam = intval($Tankorok[$tankorId]['oraszam']);
		if (is_array($Tankorok[$tankorId]['tanarok']) && count($Tankorok[$tankorId]['tanarok']) > 0) 
		    $tanarNev = $Tankorok[$tankorId]['tanarok'][0]['tanarNev'];
		else $tanarNev = '{\slshape n.a.}';
                if (is_array($Tankorok[$tankorId]['blokkNevek'])) $blokkNev = implode(', ', $Tankorok[$tankorId]['blokkNevek']);
                else $blokkNev = '';
		$return .= '\vbox to 1.2em {}'.$tankorNev.' & '.$blokkNev.' & '.$oraszam.' & '.$tanarNev.' \\\\ 
\hline';

//echo $tankorId.' '.$tankorNev.' '.$oraszam.':'.$blokkNev.'<br>';
//echo $diakId.' '.$diakNev.' '.$diakOsztaly.'<hr>';
//echo '<pre>';
//var_dump($ADAT); die();
            }

            $return .= '
\bottomrule
\end{tabular}

\vspace{16pt}

Tudomásul veszem, hogy az általam választott, két tanévre meghirdetett tantárgyak a következő tanévben számomra kötelezőek lesznek, leadásukra csak a 
11. osztályos követelmények teljesítése után lesz lehetőségem.\footnotemark[1]
\footnotetext[1]{Az iskola belső szabályai szerint a 12. évfolyamban is meg kell maradjon legalább heti 4 óra választott képzés, továbbá más tankör 
utólagos felvétele létszámkeretekhez, illetve a sikeres különbözeti vizsga letételéhez köthető.}

\vspace{12pt}

{\slshape Az aláírt jelentkezési lap osztályfőnöknél történő leadásának határideje:} {\bfseries '.$ADAT['leadasiHatarido'].'}

\vspace{20pt}
\begin{flushleft}
{\scriptsize Budapest, '.$ADAT['tanev'].'. június}
\end{flushleft}
\vspace{20pt}\slshape\scriptsize
\begin{tabular}{ccc}
\rule{3.5cm}{0.1pt}&\hspace{3cm}\ &\rule{3.5cm}{0.1pt}\\\\
tanuló&&szülő/gondviselő\\\\
\end{tabular}

\end{center}
\newpage %%%%% új oldal %%%%%';


        }

	$return .= '
\end{document}';


	return $return;
    }



?>
