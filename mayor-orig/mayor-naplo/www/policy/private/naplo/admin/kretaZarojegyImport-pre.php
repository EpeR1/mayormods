<?php

// FIGYELEM!!! AZONOS OSZLOPTÁRGYNEVEKNÉL NEM VÁRT MUKODÉS LÉPHET FEL
// -- TODO oszlopindex szinkronizálás!

    if (_RIGHTS_OK !== true) die();
    if (__NAPLOADMIN!==true) {

        $_SESSION['alert'][] = 'page:insufficient_access';

    }

    require_once('include/modules/naplo/share/intezmenyek.php');
    require_once('include/modules/naplo/share/tanar.php');
    require_once('include/modules/naplo/share/file.php');
    require_once('include/modules/naplo/share/ora.php');

    global $_TANEV;

    $ADAT['osztalyId'] = $osztalyId = readVariable($_POST['osztalyId'],'id');
    $MODIFYSQL = readVariable($_POST['MODIFYSQL'],'bool');

if ($osztalyId>0) {

    $ADAT['osztalyAdat'] = getOsztalyAdat($osztalyId);

    ini_set('max_execution_time', 120);

    $lr_intezmeny = db_connect('naplo_intezmeny');
    $lr_naplo = db_connect('naplo');

    $q = "select getNev(diakId,'diak') COLLATE utf8_hungarian_ci AS diakNev,diakId,statusz,oId from ".__INTEZMENYDBNEV.".diak WHERE statusz!='jogviszonya lezárva' ORDER BY diakNev";
    $v = array();
    $DIAKNEV2diakId = db_query($q, array('debug'=>false,'modul'=>'naplo','values'=>$v,'result'=>'multiassoc','keyfield'=>'diakNev'),$lr_naplo);

    $q = "select targyNev COLLATE utf8_hungarian_ci AS targyNev, targyId, targyJelleg, zaroKovetelmeny, mkId, munkakozosseg.leiras AS mkNev FROM ".__INTEZMENYDBNEV.".targy LEFT JOIN ".__INTEZMENYDBNEV.".munkakozosseg USING (mkId) WHERE zaroKovetelmeny IN ('jegy','magatartás','szorgalom','féljegy') ORDER BY targyNev";
    $v = array();
    $TARGYNEV2targyId = db_query($q, array('debug'=>false,'modul'=>'naplo','values'=>$v,'result'=>'multiassoc','keyfield'=>'targyNev'),$lr_naplo);
    $file = fopen("/tmp/kretaZarojegyImport.tsv.tsv","r");

    $nofrow=0;
    if ($file!==false)
    while(! feof($file)) {
	$nofrow++;
	$line = (chop(fgets($file))); // no trim!
	$record = explode("\t",$line);
	dump($record);

	if ($nofrow==1) {

	} elseif($nofrow==2) {
	    for($i=0; $i<count($record); $i++) {
		$oszlopIndex = $i;
		$ADAT['oszlop2targyId'][$oszlopIndex] = null;
		$_targyOszlopNev = kisbetus($record[$i]);
		if ($_targyOszlopNev!='' && count($TARGYNEV2targyId[$_targyOszlopNev])>=1) {
		    $ADAT['targyMatrix'][$_targyOszlopNev] = $TARGYNEV2targyId[$_targyOszlopNev] ;
		    if (count($TARGYNEV2targyId[$_targyOszlopNev])==1) {
			$ADAT['oszlop2targyId'][$oszlopIndex] = $TARGYNEV2targyId[$_targyOszlopNev][0]['targyId'];
		    } else {
			$_tmp = readVariable($_POST['oszlop_'.($oszlopIndex)],'id');
			if (is_numeric($_tmp) && $_tmp>0) {
			    $ADAT['oszlop2targyId'][$oszlopIndex] = $_tmp;
			} else {
			    // $ADAT['oszlop2targyId'][$oszlopIndex] = 10000+$oszlopIndex;
			}
		    }
		} else {
		    $ADAT['targyMatrix'][$_targyOszlopNev] = '';
		    $_SESSION['alert'][] = 'info:none:'.serialize(1);
		    $ADAT['hiba'][] = ($record[$i]);
		}
	    }
	} else {

	    $D = array();
	    $D['diakNev'] = $record[0];
	    if (count($DIAKNEV2diakId[$D['diakNev']])!=1) {
		$_SESSION['alert'][] = 'info:dup_or_none:'.serialize($D['diakNev']).':'.serialize($DIAKNEV2diakId[$D['diakNev']]);
		$ADAT['hiba'][] = $D;
		continue;;
	    } else {
		$D['diakId'] = $DIAKNEV2diakId[$D['diakNev']][0]['diakId'];
		$D['oId'] = $DIAKNEV2diakId[$D['diakNev']][0]['oId'];
	    }
	    for($i=0; $i<count($record); $i++) {
		$oszlopIndex = $i;
		if (intval($ADAT['oszlop2targyId'][$oszlopIndex])==0 || intval($record[$i])==0)
		    continue;;
		
		$D['targyId'] = intval($ADAT['oszlop2targyId'][$oszlopIndex]);
		$D['evfolyam'] = $ADAT['osztalyAdat']['evfolyam'];
		$D['evfolyamJel'] = $ADAT['osztalyAdat']['evfolyamJel'];
		$D['felev'] = 2; // TODO!
		$D['hivatalosDt'] = $_TANEV['zarasDt'];
		$D['jegy'] = intval($record[$i]);
		$D['jegyTipus'] = 'jegy';
		//dump($ADAT['osztalyAdat']['evfolyamJel']);
		$q = "SELECT count(*) AS db FROM zaroJegy WHERE diakId=%u AND targyId=%u AND evfolyamJel='%s' AND felev=%u";
		$v = array($D['diakId'],
		    $D['targyId'],
		    $D['evfolyamJel'],
		    $D['felev']);
		$result = db_query($q, array('fv'=>'kretaImport','values'=>$v,'result'=>'value'),$lr_intezmeny);
		if ($result==0) {
		    $q = "INSERT INTO zaroJegy (diakId,targyId,evfolyam,evfolyamJel,felev,jegy,jegyTipus,modositasDt,hivatalosDt)
		      VALUES (%u,%u,'%s','%s',%u,'%s','%s',NOW(),'%s')"; 
		    $v = array($D['diakId'],
		    $D['targyId'],
		    $D['evfolyam'],
		    $D['evfolyamJel'],
		    $D['felev'],
		    $D['jegy'],
		    $D['jegyTipus'],
		    $D['hivatalosDt']);
		    if ($MODIFYSQL===true) db_query($q, array('fv'=>'kretaImport','values'=>$v),$lr_intezmeny);
		    dump($v);
		    dump($q);
		}
		
	    }
	} // nofrow else
    } // while

    fclose($file);
    db_close($lr);

}




    $TOOL['osztalySelect'] = array('tipus'=>'cella');
    getToolParameters();

?>