<?php

/* - Tankötelesség -
2011. évi CXC. törvény a nemzeti köznevelésről

27. A gyermekek, a tanulók kötelességei és jogai, a tankötelezettség(3)195 A tankötelezettség annak a tanévnek a végéig tart, amelyben a tanuló a tizenhatodik életévét betölti. A sajátos nevelési igényű tanuló tankötelezettsége meghosszabbítható annak a tanítási évnek a végéig, amelyben a huszonharmadik életévét betölti. A tankötelezettség meghosszabbításáról a szakértői bizottság szakértői véleménye alapján az iskola igazgatója dönt.

13. A gyógypedagógiai, konduktív pedagógiai nevelési-oktatási intézmény60
(4)64 A fejlesztő nevelés-oktatásban a tanuló annak a tanítási évnek az utolsó napjáig köteles részt venni, amelyben betölti a tizenhatodik életévét és annak a tanítási évnek az utolsó napjáig vehet részt, amelyben betölti a huszonharmadik életévét. A fejlesztő nevelés-oktatásban a tanulókat a sajátos nevelési igényük, fejlettségük és életkoruk alapján osztják be fejlesztő csoportokba.

54. Átmeneti és vegyes rendelkezések
97. § (1)485 Azok a tanulók, akik tanulmányaikat az iskolai nevelés-oktatás kilencedik évfolyamán a 2011/2012. tanévben vagy azt megelőzően kezdték meg, tankötelezettségük azon tanítási év végéig tart, amelyben a tizennyolcadik életévüket betöltik vagy sikeres érettségi vizsgát vagy szakmai vizsgát tettek. Azon sajátos nevelési igényű tanulók tankötelezettsége, akik esetében a szakértői és rehabilitációs bizottság e törvény hatálybalépése előtt a tankötelezettség huszadik életévükig történő meghosszabbításáról döntött, annak a tanévnek a végéig tart, amelyben a huszadik életévüket betöltik.
*/

    require_once('include/modules/naplo/share/hianyzas.php');

    function getOktoberiStatisztika($tanev=__TANEV, $overrideLezart = false) {

	$TA = getTanevAdat($tanev);

	if ($TA['statusz'] != 'lezárt') {
	    $overrideLezart = true;
	    $_SESSION['alert'][] = 'info:nem_lezart_tanev'; 
	}
	$lr = db_connect('naplo_intezmeny');

	// van értelmes adat az aggregált táblában? mert ha nincs, vagy nincs lezárva, akkor számoljuk ki az online adatokból
	$q = "select count(*) AS db from hianyzasOsszesites where tanev=%u and igazolt!=0 or igazolatlan!=0";
	$v = array($tanev);
	$dbAdat = db_query($q,array('fv' => 'hianyzasOsszesites', 'modul' => 'naplo_intezmeny', 'values' => $v, 'result'=>'value'), $lr);

	if ( $overrideLezart === true || $dbAdat==0 ) {

            $Wnemszamit = defWnemszamit();
            // A tanévhez tartozó hiányzási adatok lekérdezése és rögzítése
            $tanevDb = tanevDbNev(__INTEZMENY, $tanev);
	    $j = 0;
            foreach ($TA['szemeszter'] as $i => $szAdat) {
                if ($j==0)    	$q = "CREATE TEMPORARY TABLE ".__INTEZMENYDBNEV.".__hianyzasOsszesites ";
		else 		$q = "INSERT INTO ".__INTEZMENYDBNEV.".__hianyzasOsszesites ";
                $q .= "            SELECT diakId, %u AS tanev, %u AS szemeszter,
                                COUNT(IF(tipus='hianyzas' AND statusz='igazolt',1,NULL)) AS igazolt,
                                COUNT(IF(tipus='hianyzas' AND statusz='igazolatlan',1,NULL)) AS igazolatlan,
                                SUM(IF(tipus='késés' AND statusz='igazolatlan',perc,NULL)) AS kesesPercOsszeg
                            FROM `%s`.hianyzas ".$Wnemszamit['join']."             
                            WHERE (                                                
                                tipus = 'hiányzás'                                 
                                OR (tipus='késés' AND statusz='igazolatlan' AND perc IS NOT NULL)
                            ) AND dt<='%s'                                         
                            ".$Wnemszamit['nemszamit']."
                            GROUP BY diakId";
                    $v = array($tanev, $szAdat['szemeszter'], $tanevDb, $szAdat['zarasDt']);
                    db_query($q, array('fv' => 'hianyzasOsszesites', 'modul' => 'naplo_intezmeny', 'values' => $v), $lr);
                    // A hozott hiányzások hozzáadása
                    $q = "UPDATE ".__INTEZMENYDBNEV.".__hianyzasOsszesites SET 
                            igazolt = igazolt + (
                                SELECT IFNULL(SUM(dbHianyzas),0) FROM `%s`.hianyzasHozott AS `hh` 
                                WHERE hh.diakId = __hianyzasOsszesites.diakId AND hh.statusz='igazolt' AND hh.dt<='%s'
                            ),
                            igazolatlan = igazolatlan + (
                                SELECT IFNULL(SUM(dbHianyzas),0) FROM `%s`.hianyzasHozott AS `hh` 
                                WHERE hh.diakId = __hianyzasOsszesites.diakId AND hh.statusz='igazolatlan' AND hh.dt<='%s'
                            )
                        WHERE tanev=%u AND szemeszter=%u";
                    $v = array($tanevDb, $szAdat['zarasDt'], $tanevDb, $szAdat['zarasDt'], $tanev, $szAdat['szemeszter']);
                    db_query($q, array('fv' => 'hianyzasOsszesites/hozott', 'modul' => 'naplo_intezmeny', 'values' => $v), $lr);
		$j++;
                }
	}

// -----------------------------

	$SQL_hianyzasOsszesites = ( ($overrideLezart === true) ? '__hianyzasOsszesites' : 'hianyzasOsszesites');

	$q = "select IFNULL(telephelyId,0) AS telephelyId, osztalyJel as 'osztály',
count(if(igazolatlan>0,1,null)) as `van igazolatlanja`,
count(if(igazolatlan=1,1,null)) as `1 igazolatlan`,
count(if(1<igazolatlan and igazolatlan<10,1,null)) as `2-9 igazolatlan`,
count(if(9<igazolatlan and igazolatlan<30,1,null)) as `10-29 igazolatlan`,
count(if(29<igazolatlan and igazolatlan<51,1,null)) as `30-50 igazolatlan`,
count(if(50<igazolatlan,1,null)) as `több mint 50 igazolatlan`,
count(if(igazolt>0,1,null)) as igazolt,
count(if(249<igazolatlan+igazolt,1,null)) as `250 vagy több hiányzás`,
CONCAT(IFNULL(telephelyId,0),osztalyJel) AS csop
from
    naplo_".__INTEZMENY."_%u.osztalyNaplo 
    left join osztalyDiak using (osztalyId)
    left join osztaly using (osztalyId)
    left join ".$SQL_hianyzasOsszesites." using (diakId) 
where 
tanev=%u and szemeszter=%u 
and beDt<='%s' and (kiDt is null or '%s'<=kiDt) 
group by csop order by telephelyId,lpad(osztalyJel,4,' ')";

	$v = array($tanev,$tanev,count($TA['szemeszter']),$TA['zarasDt'],$TA['zarasDt']);

	$r['osszes'] = db_query($q, array('fv'=>'oktstat','modul'=>'naplo_intezmeny','values'=>$v,'result'=>'indexed'), $lr);

    $q = "select IFNULL(telephelyId,0) AS telephelyId, osztalyJel as `osztály`, 
count(if(igazolatlan>0,1,null)) as `van igazolatlanja`,
count(if(igazolatlan=1,1,null)) as `1 igazolatlan`,
count(if(1<igazolatlan and igazolatlan<10,1,null)) as `2-9 igazolatlan`,
count(if(9<igazolatlan and igazolatlan<30,1,null)) as `10-29 igazolatlan`,
count(if(29<igazolatlan and igazolatlan<51,1,null)) as `30-50 igazolatlan`,
count(if(50<igazolatlan,1,null)) as `több mint 50 igazolatlan`,
count(if(igazolt>0,1,null)) as igazolt,
count(if(249<igazolatlan+igazolt,1,null)) as `250 vagy több hiányzás`,
CONCAT(IFNULL(telephelyId,0),osztalyJel) AS csop
from 
    naplo_".__INTEZMENY."_%u.osztalyNaplo 
    left join osztalyDiak using (osztalyId) 
    left join osztaly using (osztalyId)
    left join diak using (diakId)
    left join ".$SQL_hianyzasOsszesites." using (diakId) 
where
diak.szuletesiIdo>='%s'
and tanev=%u and szemeszter=%u 
and beDt<='%s' and (kiDt is null or '%s'<=kiDt)
group by csop order by telephelyId,lpad(osztalyJel,4,' ')
";

	$v = array($tanev,date('Y-m-d',strtotime('-16 years', strtotime($TA['kezdesDt']))),$tanev,count($TA['szemeszter']),$TA['zarasDt'],$TA['zarasDt']);
	$r['tankoteles'] = db_query($q, array('fv'=>'oktstat','modul'=>'naplo_intezmeny','values'=>$v,'result'=>'indexed'), $lr);

        db_query('DROP TABLE IF EXISTS __hianyzasOsszesites', array('fv' => 'hianyzasOsszesites/hozott', 'modul' => 'naplo_intezmeny', 'values' => $v), $lr);
	db_close($lr);

	$r['a04t17'] = getStat_a04t17($tanev);

        return $r;
    }

    function getStat_a04t17($tanev) {

	$stat = 'a04t17';

	$LJ = "LEFT JOIN osztalyDiak USING (diakId) LEFT JOIN osztaly USING (osztalyId)";

	// az iskolába lépőnek tekintjük az 1 évfolyamosokat, vagyis azokat, akik annak az osztálynak a tagjai szept 1-jén
	// amelyik kezdoEvfolyamSorszam=1, kezdoTanev=$tanev és osztalyJellegId IN (1,21,22,65) az adott tanévben
	$W  = " AND osztaly.kezdoTanev=$tanev AND kezdoEvfolyamSorszam=1 AND osztalyJellegId IN (1,21,22,65)"; // kezdoEvfolyam=1 volt eredetileg

	$q = "SELECT nem,count(DISTINCT diakId) AS db FROM diak $LJ WHERE szuletesiIdo+INTERVAL 6 YEAR>'$tanev-09-01' $W GROUP BY nem"; // nincs egyenlő!!!
	$R[$stat.'_4'] = db_query($q, array('fv'=>'oktstat','modul'=>'naplo_intezmeny','result'=>'assoc','keyfield'=>'nem'));
	$q = "SELECT nem,count(DISTINCT diakId) AS db FROM diak $LJ WHERE szuletesiIdo+INTERVAL 6 YEAR<='$tanev-08-31' and szuletesiIdo+INTERVAL 6 YEAR>='$tanev-06-01' $W GROUP BY nem";
	$R[$stat.'_3'] = db_query($q, array('fv'=>'oktstat','modul'=>'naplo_intezmeny','result'=>'assoc','keyfield'=>'nem'));
	$q = "SELECT nem,count(DISTINCT diakId) AS db FROM diak $LJ WHERE szuletesiIdo+INTERVAL 6 YEAR<='$tanev-05-31' $W GROUP BY nem"; // nincs egyenlő!!!
	$R[$stat.'_2'] = db_query($q, array('fv'=>'oktstat','modul'=>'naplo_intezmeny','result'=>'assoc','keyfield'=>'nem'));

	return $R;
    }

//    var_dump(getStat_a04t17(2014));

?>
