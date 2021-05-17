<?php

function putTeXLapdobas() {
    return '\vfil\eject%'."\n";
}

function putTeXUresLap() {
    return '\eject\null\vfill\eject%'."\n";
}


function putTeXDefineFootline($osztalyJel = '', $ofo = '') {

    global $_TANEV;

        $tanev = $_TANEV['tanev'].'/'.substr($_TANEV['zarasDt'],0,4);

        if ($osztalyJel != '') {
	    list($evf,$oJel) = explode('.', $osztalyJel);
	    $osztalySTR = TeXSpecialChars($evf.'/'.($evf>8?nagybetus($oJel):kisbetus($oJel)));
            $return = '\footline{\ifodd\pageno\rightfootline\else\leftfootline\fi}
\def\rightfootline{\hbox to \hsize{\copyright\ \mayor\ elektronikus napló -- Nyomtatva: '.date('Y.m.d').'.\hfil\folio}}
\def\leftfootline{\hbox to \hsize{\folio\hfil '.$tanev.' -- '.$osztalySTR.', of.: '.$ofo.'}}';

        } else {

            $return = '\footline{\ifodd\pageno\rightfootline\else\leftfootline\fi}
\def\rightfootline{\hbox to \hsize{\copyright\ \mayor\ elektronikus napló '.$tanev.'\hfil\folio}}
\def\leftfootline{\hbox to \hsize{\folio\hfil Nyomtatva: '.date('Y.m.d.').'}}';

        }
        return $return;

}

function endTeXDocument() {

    return '\bye';

}

// ======= Haladási ====== //

    $HianyzasJeloles = array(
        'hiányzás'=>'H',
        'késés'=>'k',
        'felszerelés hiány'=>'f',
	'egyenruha hiány' => 'e'
    );

    function datumString($tanitasiNapOk) {
        global $Honapok;
        $tolStamp = strtotime($tanitasiNapOk[0]);
        $igStamp = strtotime($tanitasiNapOk[2]);
        $tolString = date('Y',$tolStamp) . '. '.$Honapok[intval(date('m',$tolStamp))-1].' '.date('j',$tolStamp).'.';
        $igString = date('Y',$igStamp) . '. '.$Honapok[intval(date('m',$igStamp))-1].' '.date('j',$igStamp).'.';
        $datumString = $tolString.' -- '.$igString;

        return kisbetus($datumString);
    }


    $hoRomai = array('01'=>'I',
                        '02' => 'II',
                        '03' => 'III',
                        '04' => 'IV',
                        '05' => 'V',
                        '06' => 'VI',
                        '07' => 'VII',
                        '08' => 'VIII',
                        '09' => 'IX',
                        '10' => 'X',
                        '11' => 'XI',
                        '12' => 'XII');

    function datumRomai($dt) {
	global $hoRomai;
        list($ev,$honap,$nap) = explode('-',date('Y-m-j',strtotime($dt)));
        return strtr($honap,$hoRomai).'.'.$nap.'.';
    }


    function putTeXOrarendParameterek($dt, $ADAT) {

        $return = '';
        for($ora = 1; $ora <= 8; $ora++) {
	    $oraAdat = $ADAT['orak'][$dt][$ora];
            $return .= '{';
	    $targyNev = array();
	    if (is_array($oraAdat)) for ($i = 0; $i < count($oraAdat); $i++) {
		$targyId = $ADAT['tankorTargy'][ $oraAdat[$i]['tankorId'] ];
		if ($ADAT['targyAdat'][$targyId]['targyRovidNev'] != '') {
		    $tNev = $ADAT['targyAdat'][$targyId]['targyRovidNev'];
		} else {
		    $tNev = $ADAT['targyAdat'][$targyId]['targyNev'];
		}
		if (!in_array($tNev, $targyNev)) $targyNev[] = $tNev;
	    }
	    $return .= TeXSpecialChars(implode(', ', $targyNev));
	    $return .= '}';
        }

	return $return;

    }

    function putTeXHaladasiOldalbeallitas() {

        /* ELválasztás: szám < pretolerance(100) --> sikeres
                        elválasztás
                        sikertelenség !< tolerance(200)
        */
	return '%% Oldalbeállítás %%
\\pretolerance=100
\\tolerance=10000
\\magnification=960
\\vsize=26cm
\\hsize=18.5cm
\\hoffset=-1cm%% később generáljuk
\\voffset=-1cm
\\normal
'; 
    }

    function putTeXFirstFootline($ADAT) {
        return '\\footline{Nyomtatta: '.TeXSpecialChars($ADAT['intezmenyAdat']['nev']).' (OM: '.$ADAT['intezmenyAdat']['OMKod'].')'.' -- Látta: VMG 2003. X. 10. Sz. T.\\hfill}%'."\n";
    }

    function putTeXElolap($ADAT) {

        global $targyTXT;

	$return = '';
	$dbSor = 0; 
	for ($i=0; $i < count($ADAT['tankorokNaploElejere']); $i++) 
	    $dbSor += count($ADAT['tankorokNaploElejere'][$i]['tanarok']);
	$maxSorPerLap = 36;


        $return .= putTeXFirstFootline($ADAT);

	$sor = 0; $i = 0; // hanyadik tankörnél tartunk
	while ($sor < $dbSor) {

	    $lapSorai = 0;
    	    $return .= '\\vbox to \\vsize{%%%%%%%%%%%%%%%%%%%%%%%';

	    if ($sor == 0) $return .='
\\vskip60pt%
\\centerline{\\hbox to 120pt{\\hrulefill}}%
\\centerline{az intézmény hosszú bélyegzője}%
';
	    $return .='\\hbox{\\vbox to 30pt{\\vfil\\hbox{\\centerline{'.TeXSpecialChars(nagybetus($ADAT['osztalyAdat']['osztalyJel']))
.' osztályának HALADÁSI NAPLÓJA a '.$ADAT['tanev'].'/'.substr($ADAT['tanevAdat']['zarasDt'],0,4).' tanévre}}\\vfil}}%
\\hbox{\\centerline{Osztályfőnök: '.$ADAT['osztalyAdat']['osztalyfonokNev'].'}}%
%% egész lapos vbox vége... %%%%%%%%%%%%%%%%%%%%%%%
%%ez a regi volt:\\vbox to 60pt{}%
\\vfill%
\\centerline{%
\\vbox{\\hsize=300pt\\baselineskip=15pt';

	    while (
		$i < count($ADAT['tankorokNaploElejere'])
		&& ($lapSorai + count($ADAT['tankorokNaploElejere'][$i]['tanarok'])) <= $maxSorPerLap
	    ) {
		$targyId = $ADAT['tankorokNaploElejere'][$i]['targyId'];
        	$targyNev = $ADAT['targyAdat'][$targyId]['targyNev'];

		$_tankorId = $ADAT['tankorokNaploElejere'][$i]['tankorId'];
		$_osztalyId = $ADAT['tankorNaploja'][$_tankorId];
		if ($_osztalyId!==null) {
		    $naplojaban = TeXSpecialChars($ADAT['osztalyJele'][$_osztalyId]);
            	    $return .= '\\hbox to 300pt{'.TeXSpecialChars($targyNev);
	    	    if ($ADAT['targyAdat'][$targyId]['db'] > 1) {
			$return .= ' '.(++$ADAT['targyAdat'][$targyId]['kiirtDb']).'. csoport';
		    }
		    if ($ADAT['osztalyId'] != $_osztalyId) $return .=' ('.$naplojaban.' naplójában)';
        	    $return .= '\\quad\\dotfill\\quad ';
		    // Egy tanár - az első - félrevezető, pontatlan
        	    // $return .= $ADAT['tankorokNaploElejere'][$i]['tanarok'][0]['tanarNev']; //.' tanár';
	    	    // -----------
		    // több tanár egy sorban, vesszővel elválasztva - esetleg nem fér ki a sorban
        	    //$return .= implode(', ', $ADAT['tankorokNaploElejere'][$i]['tanarok']); //.' tanár';
		    // -----------
		    // több tanár külön-külön sorban - esetleg nem fér ki az oldalra ($maxLap?)
        	    $return .= implode("}%\n\\hbox to 300pt{\\hfill\\quad ", $ADAT['tankorokNaploElejere'][$i]['tanarok']); //.' tanár';
        	    $return .= '}%'."\n";
		}
		$sor += count($ADAT['tankorokNaploElejere'][$i]['tanarok']);
		$lapSorai += count($ADAT['tankorokNaploElejere'][$i]['tanarok']);
		$i++;
    	    }
    	    $return .= '}%
}% eocenterline%
\\vfill%
}';

	    if ($sor < $dbSor) $return .= putTexUresLap();
	} // while

        return $return;

    }






    function putTeXTanuloTankorMatriX($ADAT) {

	define('__MAXTANKOR',30);

        $return .= '\\vbox to 20pt{\\vfil\\centerline{\\nagy A tanulók tankörbeosztása\\normal}\\vfil}%'."\n";

$k = 0; // Hányadik tankörtől indulunk a táblázat elején
$pageDb = 0;
while ($k < count($ADAT['tankorok'])) {

	$return .= '\\centerline{\\vbox{%'."\n";

        $return .= '\\halign{\\vrule width2pt\\strut\\kicsi\\space\\noindent#\\hfill\\vrule width2pt';
            for($i = $k; ($i < count($ADAT['tankorok'])) && ($i-$k<__MAXTANKOR); $i++) $return.= '&\\hbox to 10pt{\\hfil\kicsi#\\hfil}\\vrule';
        $return .= ' width2pt\\cr%'."\n";
        $return .='\\noalign{\\hrule height2pt}%'."\n";

        $return .= '\\vbox to 180pt{\\hsize=100pt\\parindent=0pt\\vfill\\centerline{Tankör mátrix}\\vfill}';
        for($i = $k; ($i < count($ADAT['tankorok'])) && ($i-$k<__MAXTANKOR); $i++) {
            $return .= '&';
            $return .= '\\setbox\\rotbox=\\hbox to 180pt{'. TeXSpecialChars( $ADAT['tankorok'][$i]['tankorNev'] ) .'\\hfill}';
            $return .= '\\rotl\\rotbox';
        }
        $return .= '\\cr%'."\n";
        $return .='\\noalign{\\hrule height2pt}%'."\n";

        for ($j = 0; $j < count($ADAT['nevsor']); $j++){
            $diakNev = $ADAT['nevsor'][$j]['diakNev'];
            $diakId = $ADAT['nevsor'][$j]['diakId'];
            $return .= $diakNev;
            for($i = $k; ($i < count($ADAT['tankorok'])) && ($i-$k<__MAXTANKOR); $i++) {
                $return.= '&';
                if (in_array($ADAT['tankorok'][$i]['tankorId'], $ADAT['diakTankor'][$diakId])) $return.= 'x';
            }
            $return .='\\cr%'."\n";
            $return .='\\noalign{\\hrule}%'."\n";
        }
        $return .='\\noalign{\\hrule height1.6pt}%'."\n";
        $return .= '}}}\\hoffset=-0.8cm';

	$k = $i;
$pageDb++;
}
if (($pageDb % 2) == 0) $return .= putTeXUresLap(); // hogy ne csússzon el a páros/páratlan...
        return $return;
    }

    function putTeXTanuloTankorMatriXOrig($ADAT) {

        $return .= '\\vbox to 20pt{\\vfil\\centerline{\\nagy A tanulók tankörbeosztása\\normal}\\vfil}%
\\centerline{\\vbox{%'."\n";

        $return .= '\\halign{\\vrule width2pt\\strut\\kicsi\\space\\noindent#\\hfill\\vrule width2pt';
            for($i = 0; $i < count($ADAT['tankorok']); $i++) $return.= '&\\hbox to 10pt{\\hfil\kicsi#\\hfil}\\vrule';
        $return .= ' width2pt\\cr%'."\n";
        $return .='\\noalign{\\hrule height2pt}%'."\n";

        $return .= '\\vbox to 180pt{\\hsize=100pt\\parindent=0pt\\vfill\\centerline{Tankör mátrix}\\vfill}';
        for($i = 0; $i < count($ADAT['tankorok']); $i++) {
            $return .= '&';
            $return .= '\\setbox\\rotbox=\\hbox to 180pt{'. TeXSpecialChars( $ADAT['tankorok'][$i]['tankorNev'] ) .'\\hfill}';
            $return .= '\\rotl\\rotbox';
        }
        $return .= '\\cr%'."\n";
        $return .='\\noalign{\\hrule height2pt}%'."\n";

        for ($j = 0; $j < count($ADAT['nevsor']); $j++){
            $diakNev = $ADAT['nevsor'][$j]['diakNev'];
            $diakId = $ADAT['nevsor'][$j]['diakId'];
            $return .= $diakNev;
            for($i = 0; $i < count($ADAT['tankorok']); $i++) {
                $return.= '&';
                if (in_array($ADAT['tankorok'][$i]['tankorId'], $ADAT['diakTankor'][$diakId])) $return.= 'x';
            }
            $return .='\\cr%'."\n";
            $return .='\\noalign{\\hrule}%'."\n";
        }
        $return .='\\noalign{\\hrule height1.6pt}%'."\n";
        $return .= '}}}\\hoffset=-0.8cm';


        return $return;
    }




    function putTeXAllandoFejlec() {

$return='% ======================================================================= %
% "Órán" táblázat (8.9pt?) 25.6pt
\\def\\oran{\\vbox{\\halign{%
\\vbox to 30pt{\\hsize=8.9pt\\vfill\\noindent\\hfil ##\\hfil\\vfill}\\vrule%
&\\vbox to 30pt{\\hsize=8.9pt\\vfill\\noindent\\hfil ##\\hfil\\vfill}\\vrule%
&\\vbox to 30pt{\\hsize=8.9pt\\vfill\\noindent\\hfil ##\\hfil\\vfill}\\vrule%
&\\vbox to 30pt{\\hsize=8.9pt\\vfill\\noindent\\hfil ##\\hfil\\vfill}\\vrule%
&\\vbox to 30pt{\\hsize=8.9pt\\vfill\\noindent\\hfil ##\\hfil\\vfill}\\vrule%
&\\vbox to 30pt{\\hsize=8.9pt\\vfill\\noindent\\hfil ##\\hfil\\vfill}\\vrule%
&\\vbox to 30pt{\\hsize=8.9pt\\vfill\\noindent\\hfil ##\\hfil\\vfill}\\vrule%
&\\vbox to 30pt{\\hsize=8.9pt\\vfill\\noindent\\hfil ##\\hfil\\vfill}\\vrule width0.8pt%
\\cr%
% ----------------------------------------------------------------------- %
1&2&3&4&5&6&7&8\\cr%
\\noalign{\\hrule}%
\\multispan8{\\vbox to 32pt{\\hsize=74pt\\vfill\\noindent\\hfil órán\\hfil\\vfill}\\vrule width 0.8pt}\\cr%
}}}%
% ======================================================================= %
% "Mulasztott" órák táblázat
\\def\\mulasztott{%
\\setbox\\rotbox=\\hbox to 40pt{\\vbox to 14pt{\\vfil\\noindent\\space összesen\\vfil}}%
\\vbox {%
\\halign{##&\\hfil##\\hfil\\cr%
\\oran&\\hbox{\\rotl\\rotbox}\\cr%
\\noalign{\\hrule}%
\\multispan2{\\strut\\hfil mulasztott\\hfil}\\cr%
}}}
% ======================================================================= %
\\def\\igazolas{%
\\setbox\\rotboxA=\\hbox to 60pt{\\vbox to 14pt{\\vfil\\noindent\\space igazolt\\vfil}}%
\\setbox\\rotboxB=\\hbox to 60pt{\\vbox to 14pt{\\vfil\\noindent\\space igazolatlan\\vfil}}%
\\lower3pt\\vbox{%
\\halign{##&##\\cr%
\\multispan2{\\strut\\space\\hfil Ebből\\hfil\\space}\\cr%
\\noalign{\\hrule}%
\\rotl\\rotboxA\\vrule&\\rotl\\rotboxB\\cr}}}
% ======================================================================= %
\\def\\hianyzasFejlec{%
\\vbox{%
\\halign{\\kozepen{2.4cm}{2.3cm}{##}\\vrule&##\\vrule width0.8pt&##\\cr%
\\vbox{\\centerline{A hiányzó} \\centerline{tanuló neve}}%
&\\mulasztott&\\igazolas\\cr}}}
'; return $return;

    }
    /* ------------------------------------------------------------------------------------- */

    /* ----- Haladási napló baloldalán a napi órarend. ------------------------------------- */
    function putTeXOrarendMacro() {

$return='% ====ORERDND MAKRÓ========================================================= %
\\def\\orarend#1#2#3#4#5#6#7#8{%
\\lower-3pt\\vbox to 160pt{%
\\baselineskip=10pt%
\\hsize=82.5pt%
\\hbox{\\vbox to 20.4pt{\\vfil\\leftskip=3pt\\noindent\\raggedright#1\\vfil}}%
\\hrule width82.5pt%
\\hbox{\\vbox to 20.4pt{\\vfil\\leftskip=3pt\\noindent\\raggedright#2\\vfil}}%
\\hrule width82.5pt%
\\hbox{\\vbox to 20.4pt{\\vfil\\leftskip=3pt\\noindent\\raggedright#3\\vfil}}%
\\hrule width82.5pt%
\\hbox{\\vbox to 20.4pt{\\vfil\\leftskip=3pt\\noindent\\raggedright#4\\vfil}}%
\\hrule width82.5pt%
\\hbox{\\vbox to 20.4pt{\\vfil\\leftskip=3pt\\noindent\\raggedright#5\\vfil}}%
\\hrule width82.5pt%
\\hbox{\\vbox to 20.4pt{\\vfil\\leftskip=3pt\\noindent\\raggedright#6\\vfil}}%
\\hrule width82.5pt%
\\hbox{\\vbox to 20.4pt{\\vfil\\leftskip=3pt\\noindent\\raggedright#7\\vfil}}%
\\hrule width82.5pt%
\\hbox{\\vbox to 20.4pt{\\vfil\\leftskip=3pt\\noindent\\raggedright#8\\vfil}}%
}}%
'; return $return;

    } // end of putTeXOrarendMacro()
    /* ------------------------------------------------------------------------------------- */

    /* ------------------------------------------------------------------------------------- */
    function putTeXHianyzasAlTablazat($napiHianyzas) {

$return='% ======================================================================= %
\\lower3pt\\vbox to 166pt{\\noindent{\\halign{%
\\lower-2pt\\hbox to 2.4cm{\\hfil\\kicsi#\\hfill}\\vrule%
&\\vbox to 10pt{\\hsize=8.9pt\\vfill\\noindent\\hfil #\\hfil\\vfill}\\vrule%
&\\vbox to 10pt{\\hsize=8.9pt\\vfill\\noindent\\hfil #\\hfil\\vfill}\\vrule%
&\\vbox to 10pt{\\hsize=8.9pt\\vfill\\noindent\\hfil #\\hfil\\vfill}\\vrule%
&\\vbox to 10pt{\\hsize=8.9pt\\vfill\\noindent\\hfil #\\hfil\\vfill}\\vrule%
&\\vbox to 10pt{\\hsize=8.9pt\\vfill\\noindent\\hfil #\\hfil\\vfill}\\vrule%
&\\vbox to 10pt{\\hsize=8.9pt\\vfill\\noindent\\hfil #\\hfil\\vfill}\\vrule%
&\\vbox to 10pt{\\hsize=8.9pt\\vfill\\noindent\\hfil #\\hfil\\vfill}\\vrule%
&\\vbox to 10pt{\\hsize=8.9pt\\vfill\\noindent\\hfil #\\hfil\\vfill}\\vrule width0.8pt%
&\\lower-2pt\\hbox to 14pt{\\hfil#\\hfil}\\vrule width0.8pt%
&\\lower-2pt\\hbox to 14pt{\\hfil#\\hfil}\\vrule%
&\\lower-2pt\\hbox to 14pt{\\hfil#\\hfil}\\cr%
% -----------------------------------------
';
    $i = 0; // Kiírt hiányzók száma (max 16)
    if (is_array($napiHianyzas)) { 
	foreach ($napiHianyzas as $diakId => $H) {
	    if ($i > 15) break;
    	    if ($i != 0) $return .= '\\noalign{\\hrule}';
    	    $return .= $H['diakNev'];
    	    for ($j = 1; $j <= 8; $j++) $return .= '&'.$H['ora'][$j];
    	    $return .= '&'.$H['összesen'].'&'.$H['igazolt'].'&'.$H['igazolatlan'].'\\cr';
	    $i++; 
	}
    }
    // Üres sorok
    for ($i;$i<=15; $i++) {
        if ($i != 0) $return .= '\\noalign{\\hrule}';
        $return .= '&&&&&&&&&&&\\cr';
    }
$return .= '}}}%
'; return $return;


    } // end of putTeXHianyzasAlTablazat($DATA)
    /* ------------------------------------------------------------------------------------- */

    function putTargyFejlec($lap, $ADAT) {

	$return = '';

	if ($lap > 0) {

    	    $return='%% PAGE '.($lap+1).' %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%'."\n";
	    if ($lap == 2) 
		$return .= '\\vbox to 20pt{\\vfil\\centerline{\\nagy'.datumString($ADAT['tanitasiNapOk']).'\\normal}\\vfil}'."\n";
	    else
		$return .= '\\vbox to 20pt{\\vfil\\centerline{\\nagy '.date('W',strtotime($ADAT['tanitasiNapOk'][1])).'. hét\\normal}\\vfil}'."\n";
	    $return .= '\\halign{%
\\vrule width2pt%
\\hbox to 34pt{#}\\vrule width0.8pt&%
\\hbox to 34pt{#}\\vrule width0.8pt&%
\\hbox to 34pt{#}\\vrule width0.8pt&%
\\hbox to 34pt{#}\\vrule width0.8pt&%
\\hbox to 34pt{#}\\vrule width0.8pt&%
\\hbox to 34pt{#}\\vrule width0.8pt&%
\\hbox to 34pt{#}\\vrule width0.8pt&%
\\hbox to 34pt{#}\\vrule width0.8pt&%
\\hbox to 34pt{#}\\vrule width0.8pt&%
\\hbox to 34pt{#}\\vrule width0.8pt&%
\\hbox to 34pt{#}\\vrule width0.8pt&%
\\hbox to 34pt{#}\\vrule width0.8pt&%
\\hbox to 34pt{#}\\vrule width0.8pt&%
\\hbox to 34pt{#}\\vrule width0.8pt&%
\\hbox to 34pt{#}\\vrule width2pt\\cr%
% -------------------------------------------------------------- %
\\noalign{\\hrule height2pt}%
%%+++ EZ ITT NEM JÓ ÁM MINDIG Hö... \\vbox to 75pt{}%
';

	}
$foglalt = 0;
for ($i = 0; $i < count($ADAT['targyFejlec'][$lap]); $i++) {
    $targyId = $ADAT['targyFejlec'][$lap][$i]['targyId'];
    $db = $ADAT['targyFejlec'][$lap][$i]['db'];
    $sorsz = $ADAT['targyFejlec'][$lap][$i]['sorsz'];
    $tAdat = $ADAT['targyAdat'][$targyId];

    $tordeltTargyNev = $ADAT['targyAdat'][$targyId]['tordeltTargyNev'];
    $targyNev = $ADAT['targyAdat'][$targyId]['targyNev'];

    $foglalt += $db;
    
    if ($db == 1) {
	if (count($tordeltTargyNev) < 5) $return .= '\\vbox to 72pt{}\\tetejen{34pt}{41.2pt}{\\vbox{';
	else $return .= '\\vbox to 72pt{}\\kozepen{34pt}{72pt}{\\vbox{';
    	for ($j = 0; $j < count($tordeltTargyNev); $j++) $return .= '\\centerline{'.TeXSpecialChars($tordeltTargyNev[$j]).'}';
        $return .= '}}&';
    } else {
	$return .= '\\multispan{'.$db.'}{';
	if ($lap != 0 && $foglalt == $db) $return .= '\\vrule width2pt\\vbox to 72pt{}';
	//$return .= '\\ennes{'.TeXSpecialChars($targyNev).'}{'.$db.'}';
	$return .= '\\emmes{'.TeXSpecialChars($targyNev).'}{'.$db.'}{'.$sorsz.'}';
	if ($foglalt == $ADAT['helyek'][$lap]) $return .= '\\vrule width2pt}&';
	else $return .= '\\vrule width0.8pt}&';
    }
}
for ($i = $foglalt; $i < $ADAT['helyek'][$lap]; $i++) {
    $return .= '\\tetejen{34pt}{41.2pt}{\\vbox{';
    $return .= '\\centerline{}';
    $return .= '}}&';
}
return substr($return,0,-1)."\\cr%\n";

    }

/* -------------------------------------- */

    function putOraleiras($lap, $ADAT) {
	$tol = 0;
	for ($tLap = 0; $tLap < $lap; $tLap++) $tol += $ADAT['helyek'][$tLap];
	$ig = $tol + $ADAT['helyek'][$lap];

$return = '';
$return .= '% ------------------------------------------------------------- %
\\noalign{\\hrule height2pt}%
';

    for ($i = 0; $i < 3; $i++) {

	$dt = $ADAT['tanitasiNapOk'][$i];
        if (count($ADAT['orak'][$dt]) > 0) {
            for ($j = $tol; $j < $ig; $j++) {
		$tankorId = $ADAT['oszlopTankore'][$j];
		if (is_array($ADAT['oraszam'][$dt][$tankorId])) {
		    if (is_numeric($ADAT['oraszam'][$dt][$tankorId][0]))
			$oraszam1 = $ADAT['oraszam'][$dt][$tankorId][0].'.';
		    else $oraszam1='---';
		    if (is_numeric($ADAT['oraszam'][$dt][$tankorId][ (count($ADAT['oraszam'][$dt][$tankorId])-1) ]))
			$oraszam2 = $ADAT['oraszam'][$dt][$tankorId][ (count($ADAT['oraszam'][$dt][$tankorId])-1) ].'.';
		    else $oraszam2='---';
		} else { $oraszam1 = $oraszam2 = ''; }
		if (is_array($ADAT['tananyag'][$dt][$tankorId])) $leiras = TeXSpecialChars(implode(' ', $ADAT['tananyag'][$dt][$tankorId]));
		else $leiras = '';
                // $dupla_orszam_formátum = \\vbox{\\hsize=20pt\\centerline{134.} \\centerline{135.}}
                if ($oraszam1 == $oraszam2) {
                    $return .= '\\tananyag{'.$leiras.'}{'.$oraszam1.'}';
                } else {
                    $return .= '\\tananyag{'.$leiras.'}{\\vbox{\\hsize=20pt\\centerline{'.$oraszam1.'} \\centerline{'.$oraszam2.'}}}';
                }
		if ($j < $ig - 1) $return .= '&';
            }
            $return .= '\\cr\\noalign{\\hrule height2pt}';
        } else {
            $return .= '\\multispan{15}{\\vrule width2pt\\vbox to 166pt{}\\hfil\\vrule width2pt}\\cr\\noalign{\\hrule height2pt}';
        }
    }


    $return .= '% ------------------------------------------------------------- %
\\multispan{15}{%
\\vrule width2pt\\megjegyzes{Látogatások és egyéb}{észrevételek}%
';
	return $return;
    }

    /* ------------------------------------------------------------------------------------- */
    function putTeXPage1($ADAT) {
        global $aHetNapjai;
$return='
%%%%%%%%%%%%%%%%%%% PAGE 1 %%%%%%%%%%%%%%%%%%%%%%%
\\vbox to 20pt{\\vfil\\centerline{\\nagy'.datumString($ADAT['tanitasiNapOk']).'\\normal}\\vfil}
\\halign{%
\\hbox to 22pt{\\hfil#}%
&\\vrule width0.8pt\\hbox to 82.5pt{#}\\vrule width0.8pt%
&#\\vrule width0.8pt&\\hbox to 34pt{#}\\vrule width0.8pt%
&\\hbox to 34pt{#}\\vrule width0.8pt%
&\\hbox to 34pt{#}\\vrule width0.8pt%
&\\hbox to 34pt{#}\\vrule width0.8pt%
&\\hbox to 34pt{#}\\vrule width0.8pt%
&\\hbox to 34pt{#}\\vrule width2pt\\cr%
% -------------------------------------------------------------- %
\\noalign{\\hrule height2pt}%
%xetex%\\vrule width2pt\\tanitasi&\\kozepen{82.5pt}{70pt}{Tant\\\'argy}&\\hianyzasFejlec&%
\\vrule width2pt\\tanitasi&\\kozepen{82.5pt}{70pt}{Tantárgy}&\\hianyzasFejlec&%
% -------------------------------------------------------------- %
';


$lap = 0;
$return .= putTargyFejlec($lap, $ADAT);

$return .= '% ------------------------------------------------------------- %
\\noalign{\\hrule height2pt}%
';

    for ($i = 0; $i <= 2; $i++) {
                            // ---------------------------------- Mintanap...
	$dt = $ADAT['tanitasiNapOk'][$i];
        if (count($ADAT['orak'][$dt]) > 0) {
            $return .= '\\datum{'.
            getTanitasiNapSzama($dt, $ADAT['munkatervId']).'}{'.
            datumRomai($dt).'}{'.
            $aHetNapjai[(date('w',strtotime($dt))-1)].'}&\\orarend'.
            putTeXOrarendParameterek($dt, $ADAT).'&'.
            putTeXHianyzasAlTablazat($ADAT['hianyzas'][$dt]);
            for ($j = 0; $j < 6; $j++) {
		$tankorId = $ADAT['oszlopTankore'][$j];
		if (is_array($ADAT['oraszam'][$dt][$tankorId])) {
		    if (is_numeric($ADAT['oraszam'][$dt][$tankorId][0]))
			$oraszam1 = $ADAT['oraszam'][$dt][$tankorId][0].'.';
		    else $oraszam1 = '---';
		    if (is_numeric($ADAT['oraszam'][$dt][$tankorId][ (count($ADAT['oraszam'][$dt][$tankorId])-1) ]))
			$oraszam2 = $ADAT['oraszam'][$dt][$tankorId][ (count($ADAT['oraszam'][$dt][$tankorId])-1) ].'.';
		    else $oraszam2='---';
		} else { $oraszam1 = $oraszam2 = ''; }
		if (is_array($ADAT['tananyag'][$dt][$tankorId])) $leiras = TeXSpecialChars(implode(' ', $ADAT['tananyag'][$dt][$tankorId]));
		else $leiras = '';
                // $dupla_orszam_formátum = \\vbox{\\hsize=20pt\\centerline{134.} \\centerline{135.}}
                if ($oraszam1 == $oraszam2) {
                    $return .= '&\\tananyag{'.$leiras.'}{'.$oraszam1.'}';
                } else {
                    $return .= '&\\tananyag{'.$leiras.'}{\\vbox{\\hsize=20pt\\centerline{'.$oraszam1.'} \\centerline{'.$oraszam2.'}}}';
                }
            }
            $return .= '\\cr\\noalign{\\hrule height2pt}';
        } elseif ($ADAT['napok'][$dt][0]['tipus'] == 'speciális tanítási nap') {
            $return .= '\\datum{'.
	    getTanitasiNapSzama($dt, $ADAT['munkatervId']).'}{'.
	    datumRomai($dt).'}{'.
	    $aHetNapjai[(date('w',strtotime($dt))-1)]
		.'}&\\multispan{8}{\\vrule width0.8pt\\vbox to 160pt{'
		.'\\line{}\\centerline{'.$ADAT['napok'][$dt][0]['tipus'].'}\\line{}\\centerline{'.$ADAT['napok'][$dt][0]['megjegyzes'].'}'
		.'}\\hfil\\vrule width2pt}\\cr\\noalign{\\hrule height2pt}';
        } else {
            $return .= '\\datum{}{'.datumRomai($ADAT['tanitasiNapOk'][$i] ).'}{'.$aHetNapjai[(date('w',strtotime($ADAT['tanitasiNapOk'][$i]))-1)]
		.'}&\\multispan{8}{\\vrule width0.8pt\\vbox to 160pt{}\\hfil\\vrule width2pt}\\cr\\noalign{\\hrule height2pt}';
        }
    }

                            // ---------------------------------- Mintanap VÉGE
$return .= '% ------------------------------------------------------------- %
\\vrule width2pt\\megjegyzes{Mulasztott órák és}{későn jövés igazolása}%
&\\multispan8{';

    for ($i = 0; $i <= 2; $i++) {

	// Kell ez?
        //$return .= '\\vrule width0.8pt\\quad\\vbox to 85pt{\\hsize=140pt\\hbox{'.  $aHetNapjai[(date('w',strtotime($ADAT['tanitasiNapOk'][$i]))-1)]  .':}';
        //$return .= '\\vfill}\\hfil';

	$dt = $ADAT['tanitasiNapOk'][$i];
        $pluszHIANYZASOK = array();

            $return .= '\\vrule width0.8pt';
            $return .= '\\vbox to 85pt{\\baselineskip=10pt\\leftskip=3pt\\hsize=159pt';
                $return .= '\\par{\\noindent '.$aHetNapjai[(date('w',strtotime($ADAT['tanitasiNapOk'][$i]))-1)].':}';

                if (count($ADAT['hianyzas'][$dt]) > 16) {
		    $napiHianyzas = $ADAT['hianyzas'][$dt];
		    $h = 0;
		    foreach ($napiHianyzas as $diakId => $H) {
			if ($h > 15) {
                    	    $return .= '\\par{'.$H['diakNev'];
                        if (intval($H['összesen']) != 0) {
                            $return .= ' '.intval($H['összesen'])
                                .'('.intval($H['igazolt']).')';
                            // Hiányzó hiányzásai, késései felsorolás
                            for ($k = 1; $k <= 8; $k++) { // itt k=8 a maximális óraszám!
                                if (isset($H['ora'][$k])) {
                                    $pluszHIANYZASOK[ $H['ora'][$k] ] .= $k.'.';
                                }
                            }
			    foreach($pluszHIANYZASOK as $key=>$val) {
                                $return .= ' '.$key.':'.$val;
                            }
                        } else { // nem hiányzott, csak késett
                            $return .= ' késett:';
                            for ($k = 1; $k <= 8; $k++) { // itt k=8 a maximális óraszám!
                                if (isset($H['ora'][$k])) {
                                    $return .= ' '.$k.'.';
                                }
                            }
                        }
                        $return .= '}'; // ez a vége a \par{} - nak.
			}
			$h++;
                    }
                }
            $return .= '\\vfill}';
    }


$return .='\\vrule width2pt%
}\\cr% multispan
\\noalign{\\hrule height2pt}%
}\\hoffset=-1cm
';
	return $return;


    } // end of putTeXElsoOldal()
    /* ------------------------------------------------------------------------------------- */

   /* ------------------------------------------------------------------------------------- */
    function putTeXPage2($ADAT) {

        global $aHetNapjai;

	$lap = 1; 
	$return .= putTargyFejlec($lap, $ADAT);
	$return .= putOraleiras($lap, $ADAT);

        for ($i = 0; $i < 3; $i++) {
	    $dt = $ADAT['tanitasiNapOk'][$i];
            $return .= '\\vrule width0.8pt';
            $return .= '\\vbox to 85pt{\\leftskip=3pt\\hsize=120pt';
                $return .= '\\par{\\noindent '.$aHetNapjai[(date('w',strtotime($dt))-1)].':}\\parindent=15pt';
                for ($k = 0; $k < count($ADAT['helyettesites'][$dt]); $k++) 
		    $return .= '\\item{'.$ADAT['helyettesites'][$dt][$k]['ora'].'.}{'
			.$ADAT['helyettesites'][$dt][$k]['tipus'].' \\dolt '
			.$ADAT['tanarok'][ $ADAT['helyettesites'][$dt][$k]['ki'] ]['tanarNev'].'}';
                for ($k = 0; $k < count($ADAT['oralatogatas'][$dt]); $k++) {
		    $return .= '\\item{'.$ADAT['oralatogatas'][$dt][$k]['ora'].'.}{'
			.'óralátogatás \\dolt ';
		    $tNev = array();
		    foreach ($ADAT['oralatogatas'][$dt][$k]['tanarIds'] as $tanarId) 
			$tNev[] = $ADAT['tanarok'][$tanarId]['tanarNev'];
		    $return .= implode(', ', $tNev).'}';
		}
            $return .= '\\vfill}';
        }

        $return .= '\\vrule width0.8pt';
        $return .= '\\vbox to 85pt{\\parindent=0pt\\leftskip=3pt\\hsize=120pt';
        $return .= '\\par{Osztályfőnöki óra:}';
	$oraszam1 = $oraszam2 = '';
        for ($i = 0; $i < 3; $i++) {
	    $dt = $ADAT['tanitasiNapOk'][$i];
            if ($ADAT['tananyag'][$dt][ $ADAT['ofoTankorId'] ] != '') $return .= '\\par{'.
		    TeXSpecialChars(implode('; ', array_unique($ADAT['tananyag'][$dt][ $ADAT['ofoTankorId'] ]))).'}';

	    // óraszám
	    if (is_array($ADAT['oraszam'][$dt][ $ADAT['ofoTankorId'] ])) {
		if (is_numeric($ADAT['oraszam'][$dt][ $ADAT['ofoTankorId'] ][0]))
		    $oraszam1 = $ADAT['oraszam'][$dt][ $ADAT['ofoTankorId'] ][0].'.'; 
		else $oraszam1 = '---';
		if (is_numeric($ADAT['oraszam'][$dt][ $ADAT['ofoTankorId'] ][ (count($ADAT['oraszam'][$dt][ $ADAT['ofoTankorId'] ])-1) ])) 
		    $oraszam2 = $ADAT['oraszam'][$dt][ $ADAT['ofoTankorId'] ][ (count($ADAT['oraszam'][$dt][ $ADAT['ofoTankorId'] ])-1) ].'.';
		else $oraszam2 = '---';
	    }

        }
        $return .= '\\vfill';
        $return .= '\\hbox to 138pt{\\hfill\\lower3pt\\oraszam{';

        if ($oraszam1 == $oraszam2) $return .= $oraszam1;
        else $return .= '\\vbox{\\hsize=20pt\\centerline{'.$oraszam1.'} \\centerline{'.$oraszam2.'}}';

        $return .= '}}';
        $return .= '}%end of vbox'."\n";
        $return .= '\\vrule width2pt';

	$return .= '}\\cr% multispan
\\noalign{\\hrule height2pt}%
}\\hoffset=-0.4cm';

	return $return;
    }
    /* ------------------------------------------------------------------------------------- */


   /* ------------------------------------------------------------------------------------- */
    function putTeXPage34($ADAT) {

        global $aHetNapjai;

	$lap = 2;
	$return .= putTargyFejlec($lap, $ADAT);
	$return .= putOraleiras($lap, $ADAT);
	for ($i = 0; $i < 3; $i++) {
	    $dt = $ADAT['tanitasiNapOk'][$i];
            $return .= '\\vrule width0.8pt';
            $return .= '\\vbox to 85pt{\\leftskip=3pt\\hsize=120pt';
                $return .= '\\par{\\noindent '.$aHetNapjai[(date('w',strtotime($dt))-1)].':}\\parindent=15pt';
            $return .= '\\vfill}';
        }

        $return .= '\\vrule width0.8pt';
        $return .= '\\vbox to 85pt{\\parindent=0pt\\leftskip=3pt\\hsize=120pt';
        $return .= '\\vfill';
        $return .= '\\hbox to 138pt{\\hfill';
        $return .= '}';
        $return .= '}%end of vbox'."\n";
        $return .= '\\vrule width2pt';

	$return .= '}\\cr% multispan
\\noalign{\\hrule height2pt}%
}\\hoffset=-1cm';

	$return .= putTeXLapdobas();

	$lap = 3;
	$return .= putTargyFejlec($lap, $ADAT);
	$return .= putOraleiras($lap, $ADAT);
        for ($i = 0; $i < 3; $i++) {
	    $dt = $ADAT['tanitasiNapOk'][$i];
            $return .= '\\vrule width0.8pt';
            $return .= '\\vbox to 85pt{\\leftskip=3pt\\hsize=120pt';
                $return .= '\\par{\\noindent '.$aHetNapjai[(date('w',strtotime($dt))-1)].':}\\parindent=15pt';
            $return .= '\\vfill}';
        }

        $return .= '\\vrule width0.8pt';
        $return .= '\\vbox to 85pt{\\parindent=0pt\\leftskip=3pt\\hsize=120pt';
        $return .= '\\vfill';
        $return .= '\\hbox to 138pt{\\hfill';
        $return .= '}';
        $return .= '}%end of vbox'."\n";
        $return .= '\\vrule width2pt';

	$return .= '}\\cr% multispan
\\noalign{\\hrule height2pt}%
}\\hoffset=-0.4cm';


	return $return;
    }
    /* ------------------------------------------------------------------------------------- */






// ======= Osztályozó ======= //

function putTeXOsztalyozoOldalbeallitas() {

    return '%% Oldalbeállítás %%
\pretolerance=10000
\tolerance=100
\magnification=960
\vsize=27.7cm
\hsize=19cm
\voffset=-1.54cm
\hoffset=-1.27cm';

}

function putTeXOsztalyozoFejlec($sorszam, $diakNev, $szuletesiHely, $szuletesiIdo) {

    global $Honapok;

    $ev = substr($szuletesiIdo, 0, 4);
    $ho = kisbetus($Honapok[ intval(substr($szuletesiIdo, 5, 2))-1 ]);
    $nap = intval(substr($szuletesiIdo, -2));
    if ($szuletesiHely == '') $szuletesiHely = 'n.a.';

    $return .= '%--Osztályozó napló egy diák fejléce--'.$sorszam.'
\ifodd\pageno\hoffset=-0,64cm\else\hoffset=-1,64cm\fi
\hbox to\hsize{\nagy '.$sorszam.'. '.$diakNev.'\hfil '.$szuletesiHely.", $ev. $ho $nap.".'}%
\medskip';

    return $return;

}

function putTeXOsztalyozoAllandoFejlec($Ho) {
    global $hoRomai;
    foreach ($Ho as $k=>$v) {
	$rHo[$k] = $hoRomai[$v].'.';
    }


    $return .= '
\halign{';

    if (__OSZTALYOZONAPLO_JEGYEK_FELEVENTE === true) {
	$return .= '\hbox to 194.8pt{\strut\hfil #\hfil}\vrule width1.2pt&';
	$return .= '\hbox to 194.8pt{\strut\hfil #\hfil}\vrule width1.2pt\cr%'."\n";
	$return .= 'I.&II.\cr%'."\n";
	$txt = 'félévben nyert érdemjegy';
	$return .= '\noalign{\hrule height0.8pt}%
\multispan{2}{\vbox to 25pt{\hsize=390.8pt\vfil\hbox to 390.8pt{\hfil '.$txt.'\hfil}\vfil}\vrule width1.2pt}\cr%
}%
';
    } else {
	for($i=1; $i<=10; $i++) $return .= '\hbox to 38pt{\strut\hfil #\hfil}\vrule width1.2pt&';
	$return = substr($return,0,-1);
	$return .= '\cr% 10 darab'."\n";
	// Ha a hónapokat írnánk ki?
	$return .= implode('&', $rHo).'\cr%'."\n";
	$txt = 'hónapban nyert érdemjegy';
// Vagy tíz "témakört"..
    //1.&2.&3.&4.&5.&6.&7.&8.&9.&10.\cr%
//    $return .= '1.';
//    for ($i = 1; $i < count($Ho); $i++) $return .= '&'.($i+1).'.';
//    $return .= '\cr%'."\n";
//    $txt = 'szakaszból (témakörből) nyert érdemjegy';
    $return .= '\noalign{\hrule height0.8pt}%
\multispan{10}{\vbox to 25pt{\hsize=390.8pt\vfil\hbox to 390.8pt{\hfil '.$txt.'\hfil}\vfil}\vrule width1.2pt}\cr%
}%
';

    }

    return $return;

}

function putTeXOsztalyozoJegyek($diakId, $ADAT, $start = 0) {
/*
    $sorszam - hány tárgy sora lett kiírva
    $i - Hányadik tárgynál tartunk az osztály tárgyai között
    $start - honnan indul a $i
*/
    global $KOVETELMENY, $_TANEV;

// csúnya megoldás, de nincs jobb ötletem:
    global $iGlobal;

    $return .= '%%%% Osztalyozó jegyek %%%%'."\n";
//    $return .= '\halign{\vrule width2pt\hbox to 85pt{\vbox to 30pt{\hsize=85pt\vfil{\ #}\vfil}\hfill}\vrule width1.2pt&'; // Tárgy oszlopa - balra igazított
    $return .= '\halign{\vrule width2pt\hbox to 85pt{\hglue 5pt plus 0pt minus 0pt \vbox to 30pt{\hsize=80pt\vfil{#}\vfil}\hfill}\vrule width1.2pt&'; // Tárgy oszlopa - balra igazított

    if (__OSZTALYOZONAPLO_JEGYEK_FELEVENTE === true) {
	for ($i = 0; $i < 2; $i++) { // A hónapok
    	    $return .= '\hbox to 194.8pt{\hfil\vbox to 30pt{\hsize=186.8pt{\baselineskip=9pt\vfil#\vfil}}\hfil}\vrule width1.2pt&'; // középre igazított
	}
    } else {
	for ($i = 0; $i < count($ADAT['honapok']); $i++) { // A hónapok
    	    $return .= '\hbox to 38pt{\hfil\vbox to 30pt{\hsize=30pt{\baselineskip=9pt\vfil#\vfil}}\hfil}\vrule width1.2pt&'; // középre igazított
	}
    }
    $return .= '\hbox to 28pt{\vbox to 30pt{\hsize=28pt\vfil\hfil{\nagyss #}\hfil\vfil}}\vrule&%
\hbox to 28pt{\vbox to 30pt{\hsize=28pt\vfil\hfil{\nagyss #}\hfil\vfil}}\vrule width2pt\cr%
%%%% Formátum sor vége %%%%
\noalign{\hrule height2pt}%
%%%% Tárgy fejlécsora %%%%
\omit{\vrule width2pt\hbox to 85pt{\vbox to 25pt{\hsize=85pt\vfil{\hfil Tantárgy\vfil}\vfil}\hfill}\vrule width1.2pt}&%
%%%% Hónapok fejlécsora %%%%'."\n";
if (__OSZTALYOZONAPLO_JEGYEK_FELEVENTE === true) $return .= '\multispan{2}{\lower3pt\vbox{'.putTeXOsztalyozoAllandoFejlec($ADAT['honapok']).'}}&%'."\n";
else $return .= '\multispan{10}{\lower3pt\vbox{'.putTeXOsztalyozoAllandoFejlec($ADAT['honapok']).'}}&%'."\n";
$return .= '%%%% Zárójegy fejlécsora %%%%
\multispan2{\vbox{%
\halign{\hbox to 28pt{\space\lower3pt\vbox to 25pt{\hsize=22pt\vfil\baselineskip=0pt #\vfil}\space}\vrule&%
\hbox to 28pt{\space\lower3pt\vbox to 25pt{\hsize=22pt\vfil\baselineskip=0pt #\vfil}\space}%
\cr%
\multispan2{\strut\hfil Osztályzata\hfil}\cr%
\noalign{\hrule height 0.8pt}%
félév- kor&év végén\cr%
}%
}\vrule width2pt}\cr%
%%%%%%%% Fejléc vége %%%%%%%%
\noalign{\hrule height1.2pt}%
';

## =====================================================
# Tárgynév formálása, és a 10 hónapban külön a rublikák
    $sorszam = 0;
    for ($i = $start; ($i < count($ADAT['targyak']) && $sorszam < 20); $i++) {

	$targyId = $ADAT['targyak'][$i]['targyId'];
	$targyNev = $ADAT['targyak'][$i]['targyNev'];
	// A hosszabb nevek esetén az első szóköz nem nyújtható - de ezt most az elválaszott alak kiiktatja 
	// if ($pos = strpos($targyNev, ' ')) $targyNev = substr($targyNev, 0, $pos).'\hglue 1ex plus 0pt minus 0pt '.substr($targyNev, $pos+1);
        if (
            (
		//---------IDE ÍRJ
		//is_array($ADAT['jegyek'][$diakId][$targyId])
		in_array($targyId,$ADAT['diakTargy'][$diakId])
		|| is_array($ADAT['zaroJegy'][1][$diakId][$targyId])
		|| is_array($ADAT['zaroJegy'][2][$diakId][$targyId])
	    )
	    && ($targyId != $ADAT['targyak']['magatartasId'])
	    && ($targyId != $ADAT['targyak']['szorgalomId'])
            && ($targyNev != 'osztályfőnöki')
            && ($targyNev != 'magatartás')
            && ($targyNev != 'szorgalom')
        ) {
            $sorszam++;
	    // $return .= $targyNev;
	    $return .= $ADAT['targyak'][$i]['elvalasztott'];
            $return .= '&';

	    if (__OSZTALYOZONAPLO_JEGYEK_FELEVENTE !== true) {
	      for ($k = 0; $k < count($ADAT['honapok']); $k++) {
            	$ho = $ADAT['honapok'][$k];
            	for($j = 0; $j < count($ADAT['jegyek'][$diakId][$targyId][$ho]); $j++) {
		    $_jegy = $ADAT['jegyek'][$diakId][$targyId][$ho][$j]['jegy'];
		    $_jegyTipus = $ADAT['jegyek'][$diakId][$targyId][$ho][$j]['jegyTipus'];
		    $return .= TeXSpecialChars($KOVETELMENY[$_jegyTipus][$_jegy]['rovid']).' ';
            	}
		$return .= '&';
    	      }
	    } else { /* Ha félévenként! */
              for ($felev=1; $felev<=2; $felev++) {
		for ($j=0; $j<count($ADAT['jegyek'][$diakId]['felevenkent'][$targyId][$felev]); $j++) {
		    $_jegy = $ADAT['jegyek'][$diakId]['felevenkent'][$targyId][$felev][$j]['jegy'];
		    $_jegyTipus = $ADAT['jegyek'][$diakId]['felevenkent'][$targyId][$felev][$j]['jegyTipus'];
		    $return .= TeXSpecialChars($KOVETELMENY[$_jegyTipus][$_jegy]['rovid']).' ';
		}
		$return .= '&';
	      }
	    }

	    if (
		strtotime($ADAT['diakAdat'][$diakId]['osztalyDiak'][0]['kiDt']) == ''
		|| strtotime($_TANEV['szemeszter'][1]['zarasDt']) <=strtotime($ADAT['diakAdat'][$diakId]['osztalyDiak'][0]['kiDt'])
	    ) {
		$zaroJegyek=$ADAT['zaroJegy'][1][$diakId][$targyId];
		for ($zji=0; $zji<count($zaroJegyek); $zji++) {
	    	    if ($zji>0) $return .= ' ';
        	    if ($zaroJegyek[$zji]['jegy'] != 0) $return .= TeXSpecialChars(
			$KOVETELMENY[ $zaroJegyek[$zji]['jegyTipus'] ][ $zaroJegyek[$zji]['jegy'] ]['rovid']
		    );
        	    $return .= TeXSpecialChars(nagybetus(substr(
			$zaroJegyek[$zji]['megjegyzes'],0,1
		    )));

		}
	    } // ha még tagja félévkor az osztálynak

            $return .= '&';

	    if (
		strtotime($ADAT['diakAdat'][$diakId]['osztalyDiak'][0]['kiDt']) == ''
		|| strtotime($_TANEV['szemeszter'][2]['zarasDt']) <= strtotime($ADAT['diakAdat'][$diakId]['osztalyDiak'][0]['kiDt'])
	    ) {
		$zaroJegyek=$ADAT['zaroJegy'][2][$diakId][$targyId];
		for ($zji=0; $zji<count($zaroJegyek); $zji++) {
	    	    if ($zji>0) $return .= ' ';
        	    if ($zaroJegyek[$zji]['jegy'] != 0) $return .= TeXSpecialChars(
			$KOVETELMENY[ $zaroJegyek[$zji]['jegyTipus'] ][ $zaroJegyek[$zji]['jegy'] ]['rovid']
		    );
        	    $return .= TeXSpecialChars(nagybetus(substr(
			$zaroJegyek[$zji]['megjegyzes'],0,1
		    )));
		}
	    } // ha még tagja év végén az osztálynak
            $return .= '\cr%
\noalign{\hrule height0.8pt}%
';
        }
    }


    for($j = $sorszam; $j < 20; $j++) {
	if (__OSZTALYOZONAPLO_JEGYEK_FELEVENTE === true) $return .= '&&&&\cr';
        else $return .= str_repeat('&', 2+count($ADAT['honapok'])).'\cr';
// 	$return .= '0&1&2&3&4&5&6&7&8&9&10&11&12\cr';
        $return .= '\noalign{\hrule height0.8pt}';
    }

    $return .= '}';

    $iGlobal = $i;

    return $return;
}

function putTeXOsztalyozoAdatok($diakId, $ADAT) {

    global $KOVETELMENY;

    $return .= '\halign{\vrule width2.0pt\hbox to 398.6pt{\vbox to 80pt{\hsize=398.6pt#}}\vrule width1.2pt&%
\hbox to 135.2pt{#}\vrule width2pt\cr%
\noalign{\hrule height1.2pt}%
\quad\vbox{\vbox to 8pt{}%
\settabs\+ Oktatási azonosító\quad&\quad Itt egy nagyon hosszzú név \quad&\quad Törvényes képviselő:\ &\quad adatsor3 \cr
\+ Oktatási azonosító:&'.
$ADAT['diakAdat'][$diakId]['oId']
.'&Törzslapszám:&'.
TeXSpecialChars($ADAT['diakAdat'][$diakId]['torzslapszam'])
#.'&Törvényes képviselő:&'.
#str_replace(',',', ',$ADAT['diakAdat'][$diakId]['torvenyesKepviselo'])
.'\cr%
\+ Anyja neve:&'.
# Ha van leánykori neve, akkor azt írjuk ki, különben a viselt nevet
(($ADAT['szulok'][ $ADAT['diakAdat'][$diakId]['anyaId'] ]['szuleteskoriCsaladinev']!='')?trim(
    $ADAT['szulok'][ $ADAT['diakAdat'][$diakId]['anyaId'] ]['szuleteskoriCsaladinev'].' '.
    $ADAT['szulok'][ $ADAT['diakAdat'][$diakId]['anyaId'] ]['szuleteskoriUtonev']
):$ADAT['szulok'][ $ADAT['diakAdat'][$diakId]['anyaId'] ]['szuloNev'])
.'&TAJ:&'.
implode('-', str_split($ADAT['diakAdat'][$diakId]['tajSzam'],3))
.'\cr%';
    $return .= '
\+ '
.'Apa neve:&'.
$ADAT['szulok'][ $ADAT['diakAdat'][$diakId]['apaId'] ]['szuloNev']
.'&Telefonszám:&'.
implode(
    ', ',
  array_unique( 
    array_diff(
	array($ADAT['szulok'][ $ADAT['diakAdat'][$diakId]['anyaId'] ]['telefon'],$ADAT['szulok'][ $ADAT['diakAdat'][$diakId]['apaId'] ]['telefon']),
	array(null,'')
    )
  )
)
.'\cr%';
    $return .= '
\+ Gondviselő neve:&'.
$ADAT['szulok'][ $ADAT['diakAdat'][$diakId]['gondviseloId'] ]['szuloNev']
.'&Törvényes képviselő:&'.
str_replace(',',', ',$ADAT['diakAdat'][$diakId]['torvenyesKepviselo'])
.'\cr%';
$return .= '
\+ Tanuló lakcíme:&'.TeXSpecialChars($ADAT['diakAdat'][$diakId]['lakhelyIrsz'].' '
    .$ADAT['diakAdat'][$diakId]['lakhelyHelyseg'].', '
    .$ADAT['diakAdat'][$diakId]['lakhelyKozteruletNev'].' '
    .$ADAT['diakAdat'][$diakId]['lakhelyKozteruletJelleg'].' '
    .$ADAT['diakAdat'][$diakId]['lakhelyHazszam'].' '
    .$ADAT['diakAdat'][$diakId]['lakhelyEmelet'].' '
    .$ADAT['diakAdat'][$diakId]['lakhelyAjto'].' ')
    .'\cr%';
if ($ADAT['diakAdat'][$diakId]['gondozasiSzam'] != '' || $ADAT['diakAdat'][$diakId]['fogyatekossag'] != '') {
    $return .= '
\+ Felmentés:&'.
str_replace(',',', ',$ADAT['diakAdat'][$diakId]['fogyatekossag']).' ('.TeXSpecialChars($ADAT['diakAdat'][$diakId]['gondozasiSzam']).')'
.'\cr%';
}

    $return .= '
}&%---------------------------------
\lower3pt\vbox{%
\halign{\hbox to 77.6pt{\vbox to 19.3pt{\hsize=77.6pt\vfil\space#\vfil}}&%
\vrule\hbox to 28pt{\vbox to 19.3pt{\hsize=28pt\vfil{\nagyss\hfil#\hfil}\vfil}}\vrule&%
\hbox to 28pt{\vbox to 19.3pt{\hsize=28pt\vfil{\nagyss\hfil#\hfil}\vfil}}\cr%
\quad Magatartás&';
$return .= $KOVETELMENY[ $ADAT['zaroJegy'][1][$diakId][ $ADAT['targyak']['magatartasId'] ][0]['jegyTipus'] 
		][ 	 $ADAT['zaroJegy'][1][$diakId][ $ADAT['targyak']['magatartasId'] ][0]['jegy'] ]['rovid'];
$return .= '&';
$return .= $KOVETELMENY[ $ADAT['zaroJegy'][2][$diakId][ $ADAT['targyak']['magatartasId'] ][0]['jegyTipus'] 
	        ][       $ADAT['zaroJegy'][2][$diakId][ $ADAT['targyak']['magatartasId'] ][0]['jegy'] ]['rovid'];
$return .= '\cr%
\noalign{\hrule}%
\quad Szorgalom&';
$return .= $KOVETELMENY[ $ADAT['zaroJegy'][1][$diakId][ $ADAT['targyak']['szorgalomId'] ][0]['jegyTipus'] 
	        ][       $ADAT['zaroJegy'][1][$diakId][ $ADAT['targyak']['szorgalomId'] ][0]['jegy'] ]['rovid'];
$return .= '&';
$return .= $KOVETELMENY[ $ADAT['zaroJegy'][2][$diakId][ $ADAT['targyak']['szorgalomId'] ][0]['jegyTipus'] 
	        ][       $ADAT['zaroJegy'][2][$diakId][ $ADAT['targyak']['szorgalomId'] ][0]['jegy'] ]['rovid'];
$return .= '\cr%
\noalign{\hrule height1.2pt}%
\omit\vbox to 42.4pt{\hsize=77.6pt{%======================
\halign{\hbox to 30.4pt{\space\vbox to 20.8pt{\hsize=28.4pt\vfil#\vfil}}\vrule&%
\hbox to 46.4pt{\hbox to 2pt{}\vbox to 20.8pt{\hsize=40.4pt#}}\cr%
igazolt&\vfil mulasztott\cr%
\noalign{\hrule width 30.4pt}%
\baselineskip9pt igazo\-latlan&órák száma\cr%
}%
%===========================
}}&%~~~~~~~~~~~~~~~~
\omit\vrule\vbox{\halign{\vbox to 20.8pt{\hsize=28pt\vfil\hfil{\nagyss #}\hfil\vfil}\cr%
'.intval($ADAT['hianyzas'][1][$diakId]['igazolt']).'\cr%
\noalign{\hrule}%
'.intval($ADAT['hianyzas'][1][$diakId]['igazolatlan']+floor($ADAT['hianyzas'][1][$diakId]['kesesPercOsszeg']/45)).'\cr%
}}\vrule%
%~~~~~~~~~~~~~~~~~~~
&%~~~~~~~~~~~~~~~~~~
\omit\vbox{\halign{\vbox to 20.8pt{\hsize=28pt\vfil\hfil{\nagyss #}\hfil\vfil}\cr%
'.intval($ADAT['hianyzas'][2][$diakId]['igazolt']).'\cr%
\noalign{\hrule}%
'.intval($ADAT['hianyzas'][2][$diakId]['igazolatlan']+floor($ADAT['hianyzas'][2][$diakId]['kesesPercOsszeg']/45)).'\cr%
}}%
%~~~~~~~~~~~~~~~~~~~
\cr%
}%
}%
%-----------------------------------
\cr%
\noalign{\hrule height2pt}%
}';
    return $return;

}

function putTeXTanarLista($ADAT, $lapDobasok) {

    $TANAROK = $ADAT['tanarok'];

    $return = '';
    $dbTanar = count($TANAROK);
    $maxTanarperlap = 24;
    $maxLap = ceil($dbTanar / $maxTanarperlap);

    if ($lapDobasok%2==1) $return .= putTexUresLap();

    for ($lap=0; $lap<$maxLap; $lap++) {
	if ($maxLap>1) $extStr = ' '.$maxLap.'/'.($lap+1);

	$return .= '\vbox to 32pt{}\centerline{\nagy Aláíróív'.$extStr.'}%'."\n";  //\bigskip
	$return .= '\vfill'."\n";
	$return .= '\centerline{Ezt a haladási naplót '.$ADAT['tanitasiNapokSzama'].' tanítási nappal lezártam.}'."\n";
	$return .= '\centerline{Ezt az osztályozó naplót '.count($ADAT['diakIds']);
	$return .= ' beírt tanulóval lezártam.}'."\n";

	$return .= '\vfill'."\n";
	for ($i = 0 + $lap*($maxTanarperlap); $i < $dbTanar && ($maxLap==1 || ($maxLap>=2 && $i<($lap+1)*$maxTanarperlap)); $i=$i+2) {
    	    $return .= '\line{\hfill'.
                        '\vbox to 45pt{\hsize=150pt\vfil'.
                                '\hbox to 150pt{\dotfill}'.
                                '\hbox to 150pt{\hfil '.$TANAROK[$i].'\hfil}'.
                        '}';
    	    if ($TANAROK[$i+1] != '' && $i+1<($lap+1)*$maxTanarperlap) {
        	$return .=  '\hbox to 80pt{}'.
                        '\vbox to 45pt{\hsize=150pt\vfil'.
                                '\hbox to 150pt{\dotfill}'.
                                '\hbox to 150pt{\hfil '.$TANAROK[$i+1].'\hfil}'.
                        '}';
    	    }
    	    $return .= '\hfill}';
	}
	$return .= '\vfill'."\n";

	$return .= putTeXUresLap();

    }

    return $return;
}

/* Páratlan mayTanarperlap-pal nem jó!!

function putTeXTanarLista($ADAT, $lapDobasok) {

// Gutbrod András

  $TANAROK = $ADAT['tanarok'];

// teszteléshez...
$TANAROK = array(
    'Tanár 01', 'Tanár 02', 'Tanár 03', 'Tanár 04', 'Tanár 05', 'Tanár 06', 'Tanár 07', 'Tanár 08', 'Tanár 09', 'Tanár 10',
    'Tanár 11', 'Tanár 12', 'Tanár 13', 'Tanár 14', 'Tanár 15', 'Tanár 16', 'Tanár 17', 'Tanár 18', 'Tanár 19', 'Tanár 20',
    'Tanár 21', 'Tanár 22', 'Tanár 23', 'Tanár 24', 'Tanár 25', 'Tanár 26', 'Tanár 27', 'Tanár 28', 'Tanár 29', 'Tanár 30',
    'Tanár 31', 'Tanár 32', 'Tanár 33', 'Tanár 34', 'Tanár 35', 'Tanár 36', 'Tanár 37', 'Tanár 38', 'Tanár 39', 'Tanár 40',
    'Tanár 41', 'Tanár 42', 'Tanár 43', 'Tanár 44', 'Tanár 45', 'Tanár 46', 'Tanár 47', 'Tanár 48', 'Tanár 49', 'Tanár 50', 'Tanár 51'
);


    $return = '';
    $dbTanar = count($TANAROK);
    $maxTanarperlap = 23;
    $maxLap = ceil($dbTanar / $maxTanarperlap);
    $lap = 1;

    if ($lapDobasok%2==1) $return .= putTexUresLap();

  for ($i=0; $i<$dbTanar; $i++) {

    // lap teteje?
    if ($i % $maxTanarperlap == 0) {

       if ($maxLap>1) $extStr = ' '.$maxLap.'/'.($lap);

       $return .= '\vbox to 32pt{}\centerline{\nagy Aláíróív'.$extStr.'}%'."\n";
       $return .= '\vfill'."\n";
       $return .= '\centerline{Ezt a haladási naplót '.$ADAT['tanitasiNapokSzama'].' tanítási nappal lezártam.}'."\n";
       $return .= '\centerline{Ezt az osztályozó naplót '.count($ADAT['diakIds']);
       $return .= ' beírt tanulóval lezártam.}'."\n";
       $return .= '\vfill'."\n";

    }

    if ($i % 2 == 0) {

        $return .= '\line{\hfill'.
                        '\vbox to 45pt{\hsize=150pt\vfil'.
                                '\hbox to 150pt{\dotfill}'.
                                '\hbox to 150pt{\hfil '.$TANAROK[$i].'\hfil}'.
                        '}';

        // utolsó?
        if (($i+1) == $dbTanar) {
           $return .= '\hfill}';
        }

    } else {

        $return .=  '\hbox to 80pt{}'.
                        '\vbox to 45pt{\hsize=150pt\vfil'.
                                '\hbox to 150pt{\dotfill}'.
                                '\hbox to 150pt{\hfil '.$TANAROK[$i].'\hfil}'.
                        '}';
        $return .= '\hfill}';

    }

    // lap alja?
    if ((($i+1) % $maxTanarperlap) == 0) {

       $return .= '\vfill'."\n";
       $return .= putTeXUresLap();
       $lap = $lap+1;

    } else {

       // Nem lap alja és utolsó.
       if (($i+1) == $dbTanar) {

          $return .= '\vfill'."\n";
          $return .= putTeXUresLap();

       }
    }
  }

  return $return;

}

*/

?>
