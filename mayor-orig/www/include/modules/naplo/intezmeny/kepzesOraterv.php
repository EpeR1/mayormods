<?php

    require_once('include/modules/naplo/share/kepzes.php');

    function modifyKepzesOraterv($ADAT,$kepzesId) {

	$lr = db_connect('naplo_intezmeny');

	if (is_array($ADAT['oraszamok'])) foreach($ADAT['oraszamok'] as $evfolyamJel => $D) {
	    $q = "REPLACE INTO kepzesOraszam (kepzesId,evfolyamJel,kotelezoOraszam,maximalisOraszam) VALUES (%u,'%s',%f,%f)";
	    $v = array($kepzesId,$evfolyamJel,$D['kotelezo'],$D['max']);
	    db_query($q, array('modul'=>'naplo_intezmeny','fv'=>'modifyKepzesOraterv', 'values'=>$v), $lr);
	}

	//blabla if
	if (is_array($ADAT['adatok'])) {
	  foreach ($ADAT['adatok'] as $targyTipus => $X) {
	    foreach ($X as $targyId => $EGYTARGY) {
						// [$evfolyamJel][$szemeszter]['oraszam'|'kovetelmeny']
		  if (is_array($EGYTARGY)) 
		  foreach ($EGYTARGY as $evfolyamJel => $DS) {
		    if (is_array($DS)) foreach($DS as $szemeszter => $D) {
			if ($D['kovetelmeny']!='' && isset($D['oraszam'])) {
			    if ($targyTipus!='mintatantervi') {
				$q = "SELECT kepzesOratervId FROM kepzesOraterv WHERE kepzesId=%u AND evfolyamJel='%s' AND szemeszter=%u AND tipus='%s'";
				$v = array($kepzesId,$evfolyamJel,$szemeszter,$targyTipus);
				$_oratervId = db_query($q, array('modul'=>'naplo_intezmeny','result'=>'value','fv'=>'modifyKepzesOraterv', 'debug'=>false, 'values'=>$v), $lr);
				if ($_oratervId>0) {
				    $q = "UPDATE kepzesOraterv SET hetiOraszam=%f,kovetelmeny='%s' WHERE kepzesOratervId=%u";
				    $v = array($D['oraszam'],$D['kovetelmeny'],$_oratervId);
				} else {
				    $q = "INSERT INTO kepzesOraterv (kepzesId,targyId,evfolyamJel,szemeszter,hetiOraszam,kovetelmeny,tipus)
			            VALUES (%u,null,'%s',%u,%f,'%s','%s')";
				    $v = array($kepzesId,$evfolyamJel,$szemeszter,$D['oraszam'],$D['kovetelmeny'],$targyTipus);
				}
			    } else {
				$q = "SELECT kepzesOratervId FROM kepzesOraterv WHERE kepzesId=%u AND evfolyamJel='%s' AND szemeszter=%u AND tipus='%s' AND targyId=%u";
				$v = array($kepzesId,$evfolyamJel,$szemeszter,$targyTipus,$targyId);
				$_oratervId = db_query($q, array('modul'=>'naplo_intezmeny','result'=>'value','fv'=>'modifyKepzesOraterv', 'debug'=>false, 'values'=>$v), $lr);
				if ($_oratervId>0) {
				    $q = "UPDATE kepzesOraterv SET hetiOraszam=%f,kovetelmeny='%s' WHERE kepzesOratervId=%u";
				    $v = array($D['oraszam'],$D['kovetelmeny'],$_oratervId);
				} else {
				    $q = "INSERT INTO kepzesOraterv (kepzesId,targyId,evfolyamJel,szemeszter,hetiOraszam,kovetelmeny,tipus) VALUES (%u,%u,'%s',%u,%f,'%s','%s')";
				    $v = array($kepzesId,$targyId,$evfolyamJel,$szemeszter,$D['oraszam'],$D['kovetelmeny'],$targyTipus);
				}
			    }
			} else { // ha nincs megadva követelmény, akkor töröljük
			    if ($targyTipus!='mintatantervi') {
				$q = "DELETE FROM kepzesOraterv WHERE kepzesId=%u AND evfolyamJel='%s' AND szemeszter=%u AND tipus='%s'";
				$v = array($kepzesId,$evfolyamJel,$szemeszter,$targyTipus);
			    } else {
				$q = "DELETE FROM kepzesOraterv WHERE kepzesId=%u AND targyId=%u AND evfolyamJel='%s' AND szemeszter=%u";
				$v = array($kepzesId,$targyId,$evfolyamJel,$szemeszter);
			    }
			}
			db_query($q, array('modul'=>'naplo_intezmeny','fv'=>'modifyKepzesOraterv', 'debug'=>false, 'values'=>$v), $lr);
		    } /* if */
		  } /* if-foreach */    
	    } /* foreach */
	  } /* foreach */
	} /* if */
	db_close($lr);
    }

    function dropKepzesOratervRekord($kepzesId,$tipus,$targyId) {
	if ($tipus=='mintatantervi') {
	    $q = "DELETE FROM kepzesOraterv WHERE kepzesId=%u AND targyId=%u";
	    $v = array($kepzesId,$targyId);
	} else {
	    $q = "DELETE FROM kepzesOraterv WHERE kepzesId=%u AND tipus='%s'";
	    $v = array($kepzesId,$tipus);
	}
	db_query($q, array('modul'=>'naplo_intezmeny','fv'=>'dropKepzesOratervRekord', 'debug'=>false, 'values'=>$v));
    }

    function getKepzesOraszam($kepzesId) {
	$q = "SELECT * FROM kepzesOraszam WHERE kepzesId=$kepzesId ORDER BY evfolyamJel ASC"; // order error!
	return db_query($q, array('modul'=>'naplo_intezmeny','fv'=>'getKepzesOraszam', 'result'=>'assoc', 'keyfield'=>'evfolyamJel'));
    }

    function kepzesOratervMasolas($masolandoKepzesId, $kepzesId) {

	$q ="INSERT INTO kepzesOraterv (kepzesId, targyId, evfolyamJel, szemeszter, hetiOraszam, kovetelmeny, tipus)
		SELECT %u AS kepzesId, targyId, evfolyamJel, szemeszter, hetiOraszam, kovetelmeny, tipus
		FROM kepzesOraterv WHERE kepzesId=%u";
	return db_query($q, array('debug'=>false,'fv'=>'kepzesOratervMasolas','modul'=>'naplo_intezmeny','values'=>array($kepzesId, $masolandoKepzesId)));


    }

?>
