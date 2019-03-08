<?php
if (_RIGHTS_OK !== true) die();
if (!__NAPLOADMIN && !__VEZETOSEG) {
    $_SESSION['alert'] = 'page:insufficient_access'; 
} else {
    require_once('include/modules/naplo/share/osztaly.php');
    require_once('include/modules/naplo/share/munkakozosseg.php');
    require_once('include/modules/naplo/share/tanar.php');
    require_once('include/modules/naplo/share/targy.php');
    require_once('include/modules/naplo/share/file.php');


    $lr = db_connect('naplo_intezmeny');

    $OSZTALYOK = getOsztalyok();
    $TANAROK = getTanarok();
    $TARGYAK = getTargyak();
    $TARGYAK = reindex($TARGYAK,array('targyId'));
//dump($OSZTALYOK);
    for ($i=0; $i<count($OSZTALYOK); $i++) {
	$osztalyIdk[] = $OSZTALYOK[$i]['osztalyId'];
    }

    $q = "SELECT tankorId, count(szemeszter), avg(oraszam), sum(oraszam)/count(szemeszter) AS szummaOra, targyId from tankorSzemeszter
	    LEFT JOIN tankor USING (tankorId)
	  WHERE oraszam!=0 AND tanev=".__TANEV.' GROUP BY tankorId';
    $r = db_query($q, array('modul'=>'naplo_intezmeny','result'=>'indexed'));
//    dump($r);
    for ($i=0; $i<count($r); $i++) {
	$tankorAdat = $r[$i];
	$tankorId = $tankorAdat['tankorId'];
	//dump($tankorAdat);
	($T_OSZTALY = getTankorOsztalyaiByTanev($tankorId,__TANEV, null, $lr));
	($T_TANAR = getTankorTanaraiByInterval($tankorId,array('tanev'=>__TANEV),$lr));
	if (count($T_TANAR)==0) 'HIBÁS TANKÖR'.dump($tankorAdat);
	$ADAT[$T_TANAR[0]['tanarId']][$tankorAdat['targyId']][$T_OSZTALY[0]['osztalyId']][] = $tankorAdat['szummaOra'];
	if (!in_array($T_TANAR[0]['tanarId'].'_'.$tankorAdat['targyId'],$SOR)) $SOR[] = $T_TANAR[0]['tanarId'].'_'.$tankorAdat['targyId'];
    }

    db_close($lr);

    for ($i=0; $i<count($TANAROK); $i++) {
	$tanarId = $TANAROK[$i]['tanarId'];
	$TANAR_ADATOK[$tanarId] = $TANAROK[$i];
    }

    /* HEADER */
    $EXPORT[0][] = 'Név';
    $EXPORT[0][] = 'MaYoR Tanár Id';
    $EXPORT[0][] = 'MaYoR Tárgy név';
    $EXPORT[0][] = 'MaYoR Tárgy Id';
    $EXPORT[0][] = 'MaYoR Státusz';
    $EXPORT[0][] = 'MaYoR heti kötelező óraszám';
    $EXPORT[0][] = 'MaYoR heti munkaóra';
    for ($i=0; $i<count($OSZTALYOK); $i++) {
//	$osztalyIdk[] = $OSZTALYOK[$i]['osztalyId'];
	$EXPORT[0][] = $OSZTALYOK[$i]['osztalyJel'];
    }
    $EXPORT[0][] = 'MaYoR Sorösszeg';

    for ($i=0; $i<count($SOR); $i++) {
	list($tanarId,$targyId) = explode('_',$SOR[$i]);
	$tanarTargyak = array();
	foreach($ADAT[$tanarId] as $_targyId => $REST) {
	    $tanarTargyak[] = $_targyId;
	}
	// bázis adatok
	$EXPORT[($i+1)][] = $TANAR_ADATOK[$tanarId]['tanarNev'];
	$EXPORT[($i+1)][] =$tanarId;
	$EXPORT[($i+1)][] =$TARGYAK[$targyId][0]['targyNev'];	
	$EXPORT[($i+1)][] =$targyId;
	$EXPORT[($i+1)][] =$TANAR_ADATOK[$tanarId]['statusz'];
	$EXPORT[($i+1)][] =$TANAR_ADATOK[$tanarId]['hetiKotelezoOraszam'];
	$EXPORT[($i+1)][] =$TANAR_ADATOK[$tanarId]['hetiMunkaora'];

	$tanarSzum = 0;
	// osztályonként
	for ($j=0; $j<count($OSZTALYOK); $j++) {
	    $osztalyAdat = $OSZTALYOK[$j];
	    $osztalyId = $osztalyAdat['osztalyId'];
	    $SZUM = 0;
	    for ($k=0; $k<count($ADAT[$tanarId][$targyId][$osztalyId]); $k++) {
		$SZUM += $ADAT[$tanarId][$targyId][$osztalyId][$k];
	    }
	    $EXPORT[($i+1)][] =$SZUM;
	    $tanarSzum += $SZUM;
	}
	$EXPORT[($i+1)][] = "=".$tanarSzum;
    }
    $ADAT['EXPORT'] = $EXPORT;



        $ADAT['formatum'] = readVariable($_POST['formatum'], 'enum', null, array('csv','ods','xml'));
        if ($ADAT['formatum'] == 'xml') $ADAT['mime'] = 'application/vnd.ms-excel';
        if (isset($ADAT['formatum'])) {
            $file = _DOWNLOADDIR.'/private/naplo/export/tanarOsztalyOraszam'.'_'.date('Ymd');
            if (exportTanarOsztalyOraszam($file, $ADAT)) {
                header('Location: '.location('index.php?page=session&f=download&download=true&dir=naplo/export/&file='.$file.'.'.$ADAT['formatum'].'&mimetype='.$ADAT['mime']));
            }
        }

}

dump($EXPORT);
?>
