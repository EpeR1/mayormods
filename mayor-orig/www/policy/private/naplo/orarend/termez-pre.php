<?php

    if (_RIGHTS_OK !== true) die();
    if (__NAPLOADMIN!==true  &&  __VEZETOSEG!==true) {
        $_SESSION['alert'][] = 'page:insufficient_access';
    }

    require_once('include/modules/naplo/share/tanar.php');
    require_once('include/modules/naplo/share/targy.php');
    require_once('include/modules/naplo/share/orarend.php');
    require_once('include/modules/naplo/share/terem.php');

    $ADAT['tolDt'] = $tolDt = readVariable($_GET['tolDt'],'date',readVariable($_POST['tolDt'],'date',date('Y-m-d')));

    /* a program a megadott preferenciasorrend szerint teszi be az órákat a megadott helyekre */
    // ezt a részt egyelőre nem használjuk semmire
    $T = getTermek();
    for ($i=0; $i<count($T); $i++) {
	if (in_array($T[$i]['tipus'],array('tanári','tornaterem','díszterem','egyéb','könyvtár','labor'))) continue;;
	if ($T[$i]['ferohely']>24) $NAGYTEREM[$T[$i]['telephelyId']][] = $T[$i]['teremId'];
	elseif ($T[$i]['ferohely']>0) $KISTEREM[$T[$i]['telephelyId']][] = $T[$i]['teremId'];
    }
    //

    $ADAT['terem'] = getTermek(array('result'=>'assoc'));
    $ADAT['teremPreferencia'] = $P = getTeremPreferencia();

    $ADAT['tanar'] = getTanarok(array('result'=>'assoc'));
    $ADAT['targy'] = getTargyak(array('result'=>'assoc','arraymap'=>array('targyId')));

    if ($action=='del') {
	    $delTeremPreferenciaId = readVariable($_GET['delTeremPreferenciaId'],'id');
	    $q = "DELETE FROM teremPreferencia WHERE teremPreferenciaId=%u";
	    $v = array($delTeremPreferenciaId);
	    $ret = db_query($q, array('debug'=>false,'fv' => '****', 'modul' => 'naplo_intezmeny','values'=>$v));
	    $ADAT['teremPreferencia'] = getTeremPreferencia();
    } elseif ($action=='run') {
	    $runTeremPreferenciaId = readVariable($_GET['runTeremPreferenciaId'],'id');
	    $P = getTeremPreferencia(array('teremPreferenciaId'=>$runTeremPreferenciaId));
		//$q = "SELECT tanarId FROM teremPreferencia WHERE teremPreferenciaId=%u";
		//$_tanarId = db_query($q, array('debug'=>false,'fv' => '****', 'modul' => 'naplo','values'=>array($runTeremPreferenciaId)),$lr);

    for ($i=0; $i<count($P); $i++) {

	$lr = db_connect('naplo');

	$_t = $P[$i];
	$_tanarId=$P[$i]['tanarId'];
	$_targyId=$P[$i]['targyId'];
	$_termek=explode(',',$P[$i]['teremStr']);

	if ($_targyId>0) {
	    for ($t=0; $t<count($_termek); $t++) {

		$q = "CREATE TEMPORARY TABLE __o__ SELECT * FROM orarendiOra WHERE teremId=%u AND igDt>='%s'";
		db_query($q, array('debug'=>false,'fv' => '****', 'modul' => 'naplo','values'=>array($_termek[$t],$tolDt)),$lr);
		$q = "UPDATE orarendiOra AS y LEFT JOIN orarendiOraTankor USING (tanarId,osztalyJel,targyJel) LEFT JOIN ".__INTEZMENYDBNEV.".tankor USING (tankorId) 
SET teremId=%u WHERE tanarId=%u AND teremId IS NULL AND targyId=%u
AND (SELECT count(*) FROM __o__ AS x WHERE y.het=x.het AND y.nap=x.nap AND y.ora=x.ora)=0
";
		$v = array($_termek[$t],$_tanarId,$_targyId);
		$db += db_query($q, array('debug'=>false,'fv' => '****', 'modul' => 'naplo','values'=>$v,'result'=>'affected rows'),$lr);
		$q = "DROP TABLE __o__";
		db_query($q, array('debug'=>false,'fv' => '****', 'modul' => 'naplo'),$lr);
	    }
	} else {
	    for ($t=0; $t<count($_termek); $t++) {
		$q = "CREATE TEMPORARY TABLE __o__ SELECT * FROM orarendiOra WHERE teremId=%u AND igDt>='%s'";
		db_query($q, array('debug'=>false,'fv' => '****', 'modul' => 'naplo','values'=>array($_termek[$t],$tolDt)),$lr);

		$q = "UPDATE orarendiOra AS y SET teremId=%u WHERE tanarId=%u AND teremId IS NULL
AND (SELECT count(*) FROM __o__ AS x WHERE y.het=x.het AND y.nap=x.nap AND y.ora=x.ora)=0
";
		$v = array($_termek[$t],$_tanarId);
		$db += db_query($q, array('debug'=>false,'fv' => '****', 'modul' => 'naplo','values'=>$v,'result'=>'affected rows'),$lr);
		$q = "DROP TABLE __o__";
		db_query($q, array('debug'=>false,'fv' => '****', 'modul' => 'naplo'),$lr);
	    }
	}
	db_close($lr);
        $_SESSION['alert'][] = 'info:success:'.$db;
    }

    } elseif ($action=='beallitasokModositasa') {
	if (is_array($_POST)) {
	    foreach($_POST as $k=>$v) {
		if (strstr($k,'teremPreferenciaId')!==false) {
		    list($_rest,$_teremPreferenciaId) = explode('_',$k);
		    if ($_teremPreferenciaId!=$v) {
			echo 'csere';
			echo $_teremPreferenciaId.'==>'.$v;
			$q = "UPDATE teremPreferencia SET teremPreferenciaId=%u WHERE teremPreferenciaId=%u";
			$v = array($v,$_teremPreferenciaId);
			$ret = db_query($q, array('debug'=>false,'fv' => '****', 'modul' => 'naplo_intezmeny','values'=>$v,'result'=>'update'));
		    }
		}
	    }
	    $ujTeremPreferenciaId = readVariable($_POST['ujTeremPreferenciaId'],'id');
	    $ujTanarId = readVariable($_POST['ujTanarId'],'id');
	    $ujTargyId = readVariable($_POST['ujTargyId'],'id');
	    $ujTeremStr = readVariable($_POST['ujTeremStr'],'string'); // ezt validálni kellene...
	    // terem validál
	    $_UJTERMEK = explode(',',$ujTeremStr);
	    for ($i=0; $i<count($_UJTERMEK); $i++) {
		if (is_array($ADAT['terem'][trim($_UJTERMEK[$i])] )) {
		    $_TEREMOK[] = trim($_UJTERMEK[$i]);
		}
	    }
	    $ujTeremStr = implode(',',$_TEREMOK);
	    //
	    if ($ujTeremStr!='') {
		$q = "INSERT INTO teremPreferencia (teremPreferenciaId,tanarId,targyId,teremStr) VALUES (%u,%u,%s,'%s')";
		if ($ujTargyId==0) $ujTargyId='NULL';
		$v = array($ujTeremPreferenciaId,$ujTanarId,$ujTargyId,$ujTeremStr);
		$ret = db_query($q, array('debug'=>false,'fv' => '****', 'modul' => 'naplo_intezmeny','values'=>$v,'result'=>'update'));
	    }
	}
	/* RELOAD! */
	$ADAT['teremPreferencia'] = $P = getTeremPreferencia();
    } elseif ($action=='magic') {

    $lr = db_connect('naplo');
    for ($i=0; $i<count($P); $i++) {

	$_t = $P[$i];
	$_tanarId=$P[$i]['tanarId'];
	$_targyId=$P[$i]['targyId'];
	$_termek=explode(',',$P[$i]['teremStr']);

	if ($_targyId>0) {
	    for ($t=0; $t<count($_termek); $t++) {
		$q = "CREATE TEMPORARY TABLE __o__ SELECT * FROM orarendiOra WHERE teremId=%u AND igDt>='%s'";
		db_query($q, array('debug'=>false,'fv' => '****', 'modul' => 'naplo','values'=>array($_termek[$t],$tolDt)),$lr);
		$q = "UPDATE orarendiOra AS y LEFT JOIN orarendiOraTankor USING (tanarId,osztalyJel,targyJel) LEFT JOIN ".__INTEZMENYDBNEV.".tankor USING (tankorId) 
SET teremId=%u WHERE tanarId=%u AND teremId IS NULL AND targyId=%u
AND (SELECT count(*) FROM __o__ AS x WHERE y.het=x.het AND y.nap=x.nap AND y.ora=x.ora)=0
";
		$v = array($_termek[$t],$_tanarId,$_targyId);
		$db += db_query($q, array('debug'=>false,'fv' => '****', 'modul' => 'naplo','values'=>$v,'result'=>'affected rows'),$lr);
		$q = "DROP TABLE __o__";
		db_query($q, array('debug'=>false,'fv' => '****', 'modul' => 'naplo'),$lr);
	    }
	} else {
	    for ($t=0; $t<count($_termek); $t++) {
		$q = "CREATE TEMPORARY TABLE __o__ SELECT * FROM orarendiOra WHERE teremId=%u AND igDt>='%s'";
		db_query($q, array('debug'=>false,'fv' => '****', 'modul' => 'naplo','values'=>array($_termek[$t],$tolDt)),$lr);

		$q = "UPDATE orarendiOra AS y SET teremId=%u WHERE tanarId=%u AND teremId IS NULL
AND (SELECT count(*) FROM __o__ AS x WHERE y.het=x.het AND y.nap=x.nap AND y.ora=x.ora)=0
";
		$v = array($_termek[$t],$_tanarId);
		$db += db_query($q, array('debug'=>false,'fv' => '****', 'modul' => 'naplo','values'=>$v,'result'=>'affected rows'),$lr);
		$q = "DROP TABLE __o__";
		db_query($q, array('debug'=>false,'fv' => '****', 'modul' => 'naplo'),$lr);
	    }
	}
    }
    db_close($lr);
    $_SESSION['alert'][] = 'info:success:'.$db;

    } // action

        $TOOL['datumSelect'] = array(
            'tipus'=>'cella', 'post'=>array(),
            'paramName' => 'tolDt',
            'hanyNaponta' => 1,
            'override'=>true, // használathoz még át kell írni pár függvényt!!!
//          'tolDt' => date('Y-m-d', strtotime('Monday', strtotime($_TANEV['kezdesDt']))),
            'tolDt' => date('Y-m-d', strtotime('last Monday', strtotime($_TANEV['kezdesDt']))),
//          'tolDt' => $_TANEV['elozoZarasDt'],
            'igDt' => $_TANEV['kovetkezoKezdesDt'],
        );
?>