<?php

    global $ADAT;

    require_once('include/share/date/names.php');

    // legyen az alapértelmezett a mostani tanév és a mostani tanév okt 1-je
    $tanev = __TANEV;
    $dt = readVariable($_POST['dt'],'date',date('Y-m-d',mktime(0,0,0,10,1,__TANEV)));
    $dt_stamp = strtotime($dt);

    $q = "SELECT zarasDt,tanev FROM szemeszter WHERE statusz!='tervezett' ORDER BY zarasDt DESC";
    $_napok = db_query($q,array('modul'=>'naplo_intezmeny','result'=>'indexed'));
    for ($i=0; $i<count($_napok); $i++) {
	$_dt = $_napok[$i]['zarasDt'];
	$okt10 = date('Y-m-d',mktime(0,0,0,10,1,date('Y',strtotime($_dt))-1));
	if (!is_array($NAPOK) || !in_array($okt10,$NAPOK)) $NAPOK[] = $okt10;
	$NAPOK[] = $_dt;
	if (strtotime($dt) <= strtotime($_dt)) $tanev = $_napok[$i]['tanev'];
    }

    $ADAT['tanev'] = $tanev;
    $ADAT['dt'] = $dt;


/* Létszám adatok */
{
    
    $lr = db_connect('naplo_intezmeny');

    $q = "CREATE TEMPORARY TABLE _diakOkt SELECT diakId FROM diakJogviszony WHERE dt <= '".$dt."' GROUP BY diakId HAVING MAX(IF(statusz IN ('jogviszonyban van'),dt,'0000-00-00')) > MAX(IF(statusz NOT IN ('jogviszonyban van') AND dt <= '".$dt."',dt,'0000-00-00'))";
    $r = db_query($q,array('modul'=>'naplo_intezmeny'),$lr);
    $q = "SELECT CONCAT(IF(telephelyId IS NULL,0,telephelyId),LPAD(osztalyJel,4,0)) AS sorrend,osztalyId,osztalyJel,osztaly.kezdoTanev,osztaly.vegzoTanev,count(_diakOkt.diakId) AS dbDiak, YEAR(szuletesiIdo) AS szuletesiEv,
	    IF(diak.nem IS NOT NULL,nem,'fiú') AS neme,nem FROM `_diakOkt` 
	  LEFT JOIN `osztalyDiak` ON (_diakOkt.diakId=osztalyDiak.diakId AND beDt<='".$dt."' AND (kiDt IS NULL OR kiDt>='".$dt."'))
	  LEFT JOIN `osztaly` USING (osztalyId)
	  LEFT JOIN `diak` ON (_diakOkt.diakId=diak.diakId)
	  LEFT JOIN `naplo_".__INTEZMENY."_".$tanev."`.osztalyNaplo USING (osztalyId)
	  WHERE osztaly.kezdoTanev<=".$tanev." AND osztaly.vegzoTanev>=".$tanev."
	  GROUP BY osztalyId,YEAR(szuletesiIdo),neme
	  ORDER BY szuletesiEv,LPAD(osztalyJel,10,0),nem";
    $r = db_query($q,array('modul'=>'naplo_intezmeny','result'=>'indexed'),$lr);
    $x = reindex($r,array('szuletesiEv','osztalyId','nem')) ;

    for ($i=0; $i<count($r); $i++) {
	$_TMP[$r[$i]['sorrend']]= array('osztalyJel'=>$r[$i]['osztalyJel'],'osztalyId'=>$r[$i]['osztalyId']);
    }
    ksort($_TMP);
    $ADAT['osztalyok'] = $_TMP;

}

    $ADAT['eletkor'] = $x;



/* Nyelvek statisztikája */
// ennyi darab tankör van nyelvek szerint, bár ezt senki nem kérdezte :) select targyId,count(*) from tankor left join tankorTipus USING (tankorTipusId) WHERE rovidNev='első nyelv' group by targyId;
// select targyId,count(*) from tankor left join tankorTipus USING (tankorTipusId) LEFT JOIN tankorDiak USING (tankorId) WHERE rovidNev='első nyelv' group by targyId;

/*

$q = "select targyId,count(*) from tankor left join tankorTipus USING (tankorTipusId) LEFT JOIN tankorDiak USING (tankorId) LEFT JOIN _diakOkt USING (diakId)
WHERE _diakOkt.diakId is not null AND rovidNev='első nyelv'
AND beDt<='".$dt."' AND (kiDt is null OR kiDt>='".$dt."')
group by targyId";

    $r = db_query($q,array('modul'=>'naplo_intezmeny','result'=>'indexed'),$lr);

echo '<pre>';
var_dump($r);


    $_SESSION['alert'][] = 'info::Nyelvek hibásak lehetnek, ha van olyan tárgy, ami nem első-második nyelvnek van megjelölve';
*/

    $_SESSION['alert'][] = 'info::csak a kiválasztott dátumkor "jogviszonyban van" jogviszonyú diákok látszanak ebben a táblázatban (a fiú/lány mezők hiánya hibás adatokhoz vezethet)';

    $TOOL['datumSelect'] = array(
        'tipus'=>'cella', 'post' => array(),
        'paramName' => 'dt', 'hanyNaponta' => 1,
//        'tolDt' => date('Y-m-d', strtotime($_TANEV['kezdesDt'])),
//        'igDt' => $_TANEV['zarasDt'],
        'napok' => $NAPOK
    );


?>
