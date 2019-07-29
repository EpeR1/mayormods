<?php

    function hianyzasPercUpdate($PERCEK) {
        if (count($PERCEK)>0) {
            $lr = db_connect('naplo', array('fv' => 'hianyzasPercUpdate'));
                foreach($PERCEK as $_hid=>$_perc) {
		    if (__TANAR===true || __NAPLOADMIN===true) { // ennél szűkebb feltételek is szabhatók!
            		$v = array($_perc, $_hid);
			/* csak az módosítsa, akinek ... */
			$W = '';
			if (__HIANYZASTOROLHETO!==true) {
			    if (__NAPLOADMIN===false && is_numeric(__USERTANARID)) {
				$W = " AND rogzitoTanarId = %u ";
				$v = mayor_array_join($v,array(__USERTANARID));
			    }
	    		}
                	$q = "UPDATE hianyzas SET perc=%u, modositasDt=NOW() WHERE hianyzasId=%u ".$W;
                	db_query($q, array('fv' => 'hianyzasIgazolas', 'modul' => 'naplo', 'values' => $v), $lr);
            	    } else {
            		$_SESSION['alert'][] = 'info:not_allowed';
            	    }
                }
            db_close($lr);
        }
    }                                                                                                                                          

    function hianyzasTorles($TORLENDOIDK, $tanev = __TANEV, $olr='') {

	if (!isset($tanev)) 
	    if (defined('__TANEV')) $tanev = __TANEV;
	    else return false;

	$lr = ($olr!='') ? $olr : db_connect('naplo_intezmeny');

	$tanevDb = tanevDbNev(__INTEZMENY, $tanev);

	if (!is_array($TORLENDOIDK) && $TORLENDOIDK!='')
	    $TORLENDOIDK[] = $TORLENDOIDK;
	    
        if (is_array($TORLENDOIDK) && count($TORLENDOIDK)>0) {

	    $v = mayor_array_join(array($tanevDb), $TORLENDOIDK);

	    /* Itt ellenőrizhetjük hogy csak azt töröljük amit tényleg kell */
	    $W = '';
	    if (__HIANYZASTOROLHETO!==true) {
		// __VEZETOSEG_TOROLHET_HIANYZAST, ha például tankörnévsort módosít...
		if (__NAPLOADMIN===false && __VEZETOSEG_TOROLHET_HIANYZAST!==true && is_numeric(__USERTANARID)) {
		    $W = " AND rogzitoTanarId = %u ";
		    $v = mayor_array_join($v,array(__USERTANARID));
		}
	    }

	    $q = "SELECT * FROM `%s`.hianyzas WHERE hianyzasId IN (".implode(',', array_fill(0, count($TORLENDOIDK), '%u')).")". $W;
	    $r = db_query($q, array('fv' => 'hianyzasTorles', 'modul' => 'naplo', 'result' => 'indexed', 'values' => $v));

	    for ($i = 0; $i < count($r); $i++) 
		logAction(
		    array(
			'szoveg'=>'del:'.implode(',',$r[$i]),
			'table'=>'hianyzas'
		    )
		);

            $q = "DELETE FROM `%s`.hianyzas WHERE hianyzasId IN (".implode(',', array_fill(0, count($TORLENDOIDK),'%u')).")". $W;
            $r = db_query($q, array('fv' => 'hianyzasTorles', 'modul' => 'naplo', 'values' => $v), $lr);

        } else { $r = true; /* Nincs mit törölni */ }

	if ($olr=='') db_close($lr);
	return $r;
    }

    function hianyzasRegisztralas($ORAADAT,$BEIR) {

        $REPL = $INS = array();
        $oraId = $ORAADAT['oraId'];
        $dt = $ORAADAT['dt'];
        $ora = $ORAADAT['ora'];
//	$rogzitoTanarStr = (__NAPLOADMIN===false && is_numeric(__USERTANARID)) ? __USERTANARID : 'null'; // null string az sql-nek!
// Miért ne rögzítsük a naplóadmin esetén a tanárId-t, ha van neki? Ahogy a Vezetőség esetén is rögzítjük...
	$rogzitoTanarStr = (is_numeric(__USERTANARID)) ? __USERTANARID : 'null'; // null string az sql-nek!

        for($i=0; $i<count($BEIR); $i++) {
            if ($BEIR[$i]['id']!='') $REPL[] = $BEIR[$i];
            else $INS[] = $BEIR[$i];
        }

        $lr =   db_connect('naplo');
        for ($i=0; $i<count($INS); $i++) {
            if ($INS[$i]['perc']=='') $INS[$i]['perc'] = 'NULL';
            if ($INS[$i]['statusz']=='') $INS[$i]['statusz'] = 'igazolatlan';
            if ($INS[$i]['ora']!='') $ora=$INS[$i]['ora'];
            if ($INS[$i]['oraId']!='') $oraId=$INS[$i]['oraId'];
            if ($INS[$i]['dt']!='') $dt=$INS[$i]['dt'];            
            //if ($INS[$i]['tankorTipus']=='') $INS[$i]['tankorTipus'] = 'NULL';
            $diakId = $INS[$i]['diakId'];
            /* ELLENŐRIZZÜK ITT */
            if ($diakId!='' && $oraId!='') {
        	/* Jogviszony ellenőrzés */
        	$diakJogviszony = getDiakJogviszonyByDts(array($diakId),array($dt));
		if (!in_array($diakJogviszony[$diakId][$dt]['statusz'], array('vendégtanuló','jogviszonyban van','magántanuló','egyéni munkarend'))
		) {
		    $_SESSION['alert'][] = 'info:diakJogviszony:'.$diakId.':'.$diakJogviszony[$diakId][$dt]['statusz'].':'.$dt;
		    continue;
		}           
		if ( in_array($INS[$i]['tipus'], array('felszerelés hiány','felmentés','egyenruha hiány'))) $_jogTipus = 'fbeirhato';
		else $_jogTipus = 'beirhato';
                if (getHianyzasJogosultsagSimple($oraId, $diakId, $INS[$i]['igazolas'], $INS[$i]['statusz'], $_jogTipus, $lr)) {
		    // lekérdezzük az óra tenkörének típusát
		    $q = "SELECT * FROM `ora` WHERE `oraId` = %u";
		    $_ORAADAT = db_query($q, array('fv'=>'hianyzasRegisztralas', 'modul'=>'naplo', 'result'=>'record', 'values' => array($oraId)), $lr);
		    $tankorId = $_ORAADAT['tankorId'];
		    $rogzitesIdoben = ((strtotime($_ORAADAT['dt']) >= strtotime(_HIANYZAS_HATARIDO)) ? 1:0); //--FIXME
		    // ezzel nem veszünk részt a tranzakcióban - intézményi db
		    //$q = "SELECT `tankorTipusId` FROM `tankor` WHERE `tankorId` = %u";
		    //$tankorTipusId = db_query($q, array('fv'=>'hianyzasRegisztralas', 'modul'=>'naplo_intezmeny', 'result'=>'value', 'values' => array($tankorId)));
		    $q = "SELECT `tankorTipusId`,`tankorTipus`.`jelleg` FROM `tankor` LEFT JOIN `tankorTipus` USING (`tankorTipusId`) WHERE `tankorId` = %u";
		    $TANKORADAT = db_query($q, array('fv'=>'hianyzasRegisztralas', 'modul'=>'naplo_intezmeny', 'result'=>'record', 'values' => array($tankorId)));
		    $tankorTipusId=$TANKORADAT['tankorTipusId'];
		    $tankorJelleg=$TANKORADAT['jelleg'];
		    if ($tankorJelleg!='gyakorlat' && $diakJogviszony[$diakId][$dt]['statusz'] == 'magántanuló') {
			$_SESSION['alert'][] = 'info:diakJogviszony:'.$diakId.':'.$diakJogviszony[$diakId][$dt]['statusz'].':'.$dt;
			continue;
		    }
		    if ($tankorJelleg!='gyakorlat' && $diakJogviszony[$diakId][$dt]['statusz'] == 'egyéni munkarend') {
			$_SESSION['alert'][] = 'info:diakJogviszony:'.$diakId.':'.$diakJogviszony[$diakId][$dt]['statusz'].':'.$dt;
			continue;
		    }
		    //
                    if ($INS[$i]['statusz']=='igazolatlan') $INS[$i]['igazolas']='';
                    
                    // Plusz ellenőrzés: ha hiányzást vagy késést írnánk be, a párjuk meglétekor ezt elutasítjuk
                    // Ilyen eset akkor állhat elő, ha pl nem az írta be a hiányzást, aki módosítani próbálja késésre (ekkor ugyanis nem törlődnek előtte a megfelelő bejegyzések)
                    if (in_array($INS[$i]['tipus'],array('hiányzás','késés'))) {
                	$q = "SELECT count(hianyzasId) AS db FROM hianyzas WHERE diakId=%u AND oraId=%u AND tipus IN ('hiányzás','késés')";
                	$v = array($INS[$i]['diakId'], $oraId, $INS[$i]['tipus']);
			$db = db_query($q, array('fv' => 'hianyzasRegisztralas/check', 'modul' => 'naplo', 'result' => 'value', 'values' => $v), $lr);
                    }
                    if ($db==0) {
                	$q = "INSERT INTO hianyzas (diakId,oraid,dt,ora,perc,tipus,statusz,igazolas,tankorTipusId,rogzitoTanarId,rogzitesIdoben,modositasDt)
                	    VALUES (%u, %u, '%s', %u, %u, '%s', '%s', '%s', %u, %s, %u, NOW())";
			$v = array($INS[$i]['diakId'], $oraId, $dt , $ora, $INS[$i]['perc'], $INS[$i]['tipus'], $INS[$i]['statusz'], $INS[$i]['igazolas'], $tankorTipusId, $rogzitoTanarStr, $rogzitesIdoben);
                	$ins = db_query($q, array('fv' => 'hianyzasRegisztralas', 'modul' => 'naplo', 'result' => 'insert', 'values' => $v), $lr);
			logAction(
			    array(
				'szoveg'=>'ins:'.$ins.':'.$INS[$i]['diakId'].",$oraId,$dt,$ora,".$INS[$i]['perc'].",".$INS[$i]['tipus'].",".$INS[$i]['statusz'].",".$INS[$i]['igazolas'],
				'table'=>'hianyzas'
			    ),
			    $lr
			);
		    } else {
			$_SESSION['alert'][] = 'info:wrong_data:Ez a mulasztás nem módosítható';
		    }
                } else {
		    // a hibaüzenetet a keletkezésének helyén generáljuk (simple függvényben)
                }
            } else {
                if ($diakId=='') $_SESSION['alert'][] = '::(hianyzasRegisztralas), nincs diak azonosito!';
                if ($oraId=='') $_SESSION['alert'][] = '::(hianyzasRegisztralas), nincs ora azonosito!';
            }
        }
        db_close($lr);

    }


   function hianyzasIgazolas($IGAZOLANDOK,$diakId='') {

/*
	if ($diakId=='') {
	    $_SESSION['alert'][] = '::Ismeretlen diák azonosító!('.$diakId.')';
	    return false;
	} 
*/

        if (count($IGAZOLANDOK)>0) {
            $lr = db_connect('naplo');
                for ($i=0; $i<count($IGAZOLANDOK); $i++) {
                    $_I = $IGAZOLANDOK[$i];
		    // figyelem, itt le kell kérdezni a hiányzás adatait: diakId, oraId!!!
		    if ($_I['oraId']=='') 
			$oraId = getOraIdByHianyzasId($_I['id'], $lr);
		    else 
			$oraId = $_I['oraId'];
		    if ($_I['diakId']!='') $diakId=$_I['diakId'];
		    
		    if ($diakId!='' && $oraId!='') {
			if (getHianyzasJogosultsagSimple($oraId,$diakId,$_I['igazolas'],$_I['statusz'],'igazolhato',$lr)) {
			    if ($_I['statusz']=='igazolatlan') $_I['igazolas']='';
			    $q = "UPDATE hianyzas SET statusz='%s', igazolas='%s' WHERE hianyzasId=%u";
			    $v = array($_I['statusz'], $_I['igazolas'], $_I['id']);
                	    db_query($q,array('fv' => 'hianyzasIgazolas', 'modul' => 'naplo', 'values' => $v),$lr);
			    logAction(
				array(
				    'szoveg'=>'update:'.$_I['id'].':'.$_I['statusz'].','.$_I['igazolas'], 
				    'table'=>'hianyzas'
				), 
				$lr
			    );
			} else {
			    //$_SESSION['alert'][] = '::Ez a típus elfogyott ('.$_I['igazolas'].')'.$oraId.'.'.$diakId.$_I['statusz'];
			}
		    } else {
			if ($diakId=='') $_SESSION['alert'][] = '::(hianyzasIgazolas), nincs diak azonosito!';
			if ($oraId=='') $_SESSION['alert'][] = '::(hianyzasIgazolas), nincs ora azonosito!';
		    }
                }
            db_close($lr);
        }

    }


    function getHianyzasJogosultsagSimple($oraId,$diakId,$igazolasTipus,$igazolasStatusz,$jogTipus, $olr='') { //$igTipus == SQL"igazolas"

	global $_TANEV;

	$lr = ($olr=='') ? db_connect('naplo') : $olr;

	$ORA = getOraAdatById($oraId, __TANEV, $lr); // a fv kapott tanev paramétert. default: __TANEV
	$JOG = getHianyzasJogosultsag(array($ORA), array('idk'=>array($diakId)));
	$oraElmaradt = in_array($ORA['tipus'],array('elmarad','elmarad máskor'));

	if ($lr=='') db_close($lr);
	//$_SESSION['alert'][] = '::DEBUG:'.in_array($igazolasTipus, $JOG[$diakId]['igazolas']['tipusok']);
	//if ($JOG[$diakId]['orak'][$ORA['ora']]['beirhato'|'fbeirhato'|'igazolhato']
	if ($JOG[$diakId]['orak'][$ORA['ora']][$jogTipus]===false)
	    $_SESSION['alert'][] = '::Nem '.$jogTipus;
	elseif ($igazolasStatusz != 'igazolatlan' && !@in_array($igazolasTipus, $JOG[$diakId]['igazolas']['tipusok'])) 
	    $_SESSION['alert'][] = 'info:tipus_elfogyott:'.$igazolasTipus;
	elseif ($_TANEV['statusz']!='aktív')
	    $_SESSION['alert'][] = 'info:nem_aktív_tanev';
	elseif ( !isset($JOG[$diakId]['orak'][$ORA['ora']][$jogTipus]) ) 
	    $_SESSION['alert'][] = 'info::debug##4';

	return ($_TANEV['statusz']=='aktív' && $JOG[$diakId]['orak'][$ORA['ora']][$jogTipus] && $oraElmaradt===false &&
		    ($igazolasStatusz == 'igazolatlan'  ||  
		     @in_array($igazolasTipus, $JOG[$diakId]['igazolas']['tipusok'])));
    }

    function getHianyzasJogosultsag($ORAK, $NEVSOR) {
	global $_OSZTALYA,$_TANEV;
	$DIAKIDK = $NEVSOR['idk'];
	$DIAKOSZTALYAI = getDiakokOsztalyai($DIAKIDK);

	    for ($i=0;$i<count($DIAKIDK);$i++) {
		$diakId = $DIAKIDK[$i];
		$munkatervId = getMunkatervByOsztalyId($DIAKOSZTALYAI[$diakId]);
		$nemTimeStamp = strtotime( getNemigazolhatoDt($diakId, $munkatervId) );
		for($j=0; $j<count($ORAK); $j++) {
		    $ORAADAT = $ORAK[$j];
		    $ora = $ORAADAT['ora'];
	
	    $jog = array('fbeirhato'=>false, 'beirhato' => false, 'igazolhato' => false);
		
		    if ($_TANEV['statusz']=='aktív') {
			$marElkezdodott = (strtotime($ORAADAT['dt'].' '.$ORAADAT['tolTime']) < strtotime(date('Y-m-d H:i:s')));
	    		if ($ORAADAT['ki'] == __USERTANARID) {
			    if ( !in_array($ORAADAT['tipus'], array('elmarad' , 'elmarad máskor')) && $marElkezdodott) {
				if ( strtotime($ORAADAT['dt']) >= strtotime(_HIANYZAS_HATARIDO) )
				    $jog['beirhato'] = true;
				if ( strtotime($ORAADAT['dt']) >= strtotime(_LEGKORABBAN_IGAZOLHATO_HIANYZAS) )
				    $jog['fbeirhato'] = true;
			    }
			} 

	    		if ( 
			    is_array($DIAKOSZTALYAI[$diakId]) && 
			    is_array($_OSZTALYA) &&
			    ($diakOfoje = (is_array($_OSZTALYA) && count(array_intersect($DIAKOSZTALYAI[$diakId],$_OSZTALYA)) > 0))
			) {
			    if ( !in_array($ORAADAT['tipus'], array('elmarad' , 'elmarad máskor')) ) {
				if ( strtotime($ORAADAT['dt']) >= strtotime(_OFO_HIANYZAS_HATARIDO) )
				    $jog['beirhato'] = true;
				if ( strtotime($ORAADAT['dt']) > $nemTimeStamp )
				    $jog['igazolhato'] = true;
			    }
			}

			if (__NAPLOADMIN || __VEZETOSEG) {
			    if ( strtotime($ORAADAT['dt']) >= strtotime(_ZARAS_HATARIDO) )
				$jog = array('fbeirhato'=>true, 'beirhato' => true, 'igazolhato' => true);
			}

		    }
		    $JOGOSULTSAG[$diakId]['orak'][$ora] = $jog;
		} // end of diakidk
	    } // end of orak

	/* */
	$felev = getFelevByDt($ORAK[0]['dt']);
	if ($felev=='') return false;

	for ($i=0;$i<count($DIAKIDK);$i++) {

	    $diakId = $DIAKIDK[$i];

	    // Van-e olyan ora amihez kellenek az adatok?
	    $global_acc = false;
	    for($j=0; $j<count($ORAK); $j++) {
		if ($JOGOSULTSAG[$diakId]['orak'][$ORAK[$j]['ora']]['igazolhato']) {
		    $global_acc=true; break;
		}
	    }

	    // Ha van, kerdezzuk le...
	    if ($global_acc) {
		$_IGAZOLAS = getIgazolasSzam($diakId,$ORAK[0]['dt']);

		$IG = array();
		{
		    $IG[] = 'orvosi';
		    if (
			(__SZULOI_IGAZOLAS_EVRE == 0 or __SZULOI_IGAZOLAS_EVRE > intval($_IGAZOLAS['napok']['osszesen']['szülői'])) and
			(__SZULOI_IGAZOLAS_FELEVRE == 0 or __SZULOI_IGAZOLAS_FELEVRE > intval($_IGAZOLAS['napok'][$felev]['szülői'])) and
			(__SZULOI_ORA_IGAZOLAS_EVRE ==0 or __SZULOI_ORA_IGAZOLAS_EVRE > intval($_IGAZOLAS['orak']['osszesen']['szülői'])) and
			(__SZULOI_ORA_IGAZOLAS_FELEVRE ==0 or __SZULOI_ORA_IGAZOLAS_FELEVRE > intval($_IGAZOLAS['orak'][$felev]['szülői']))
		    ) {
			$IG[] = 'szülői';
		    }
		    $IG[] = 'verseny';
		    $IG[] = 'vizsga';
		    if (
			(__NAPLOADMIN || $diakOfoje) && // !__VEZETOSEG && - ez nem jó, ha vezetőségi tag ofő is egyben
			(__OSZTALYFONOKI_IGAZOLAS_EVRE == 0 or __OSZTALYFONOKI_IGAZOLAS_EVRE > intval($_IGAZOLAS['napok']['osszesen']['osztályfőnöki'])) and
			(__OSZTALYFONOKI_IGAZOLAS_FELEVRE == 0 or __OSZTALYFONOKI_IGAZOLAS_FELEVRE > intval($_IGAZOLAS ['napok'][$felev]['osztályfőnöki'])) and
			(__OSZTALYFONOKI_ORA_IGAZOLAS_EVRE ==0 or __OSZTALYFONOKI_ORA_IGAZOLAS_EVRE > intval($_IGAZOLAS ['orak']['osszesen']['osztályfőnöki'])) and
			(__OSZTALYFONOKI_ORA_IGAZOLAS_FELEVRE ==0 or __OSZTALYFONOKI_ORA_IGAZOLAS_FELEVRE > intval($_IGAZOLAS ['orak'][$felev]['osztályfőnöki']))
		    ) {
			$IG[] = 'osztályfőnöki';
		    }
		}
		if (__VEZETOSEG || __NAPLOADMIN) {
		    $IG[] = 'igazgatói';
		}
		$IG[] = 'hatósági';
		$IG[] = 'pályaválasztás';
		$JOGOSULTSAG[$diakId]['igazolas']['tipusok'] = $IG;
		$JOGOSULTSAG[$diakId]['igazolas']['szamok'] = $_IGAZOLAS;
	    }
	}

	return $JOGOSULTSAG;
    }

    function hianyzasTankorTipusValtas($tankorId,$tankorTipusId,$SET = array('tanev'=>null)) {

	if (!isset($SET['tanev'])) return false;
	$TANEV = getTanevAdat($SET['tanev']);
	if ($TANEV['statusz']!='aktív') return false;

	$tanevDbNev = tanevDbNev(__INTEZMENY, $SET['tanev']);

	$q = "SELECT count(*) AS db FROM `%s`.hianyzas LEFT JOIN `%s`.ora USING(oraId) WHERE tankorTipusId!=%u AND tankorId=%u";
	$v = array($tanevDbNev,$tanevDbNev,$tankorTipusId,$tankorId);
	$affected = db_query($q, array('debug'=>false,'modul'=>'naplo','values'=>$v,'result'=>'value'));

	$q = "UPDATE `%s`.hianyzas LEFT JOIN `%s`.ora USING(oraId) SET tankorTipusId=%u WHERE tankorId=%u";
	$v = array($tanevDbNev,$tanevDbNev,$tankorTipusId,$tankorId);
	db_query($q, array('debug'=>false,'modul'=>'naplo','values'=>$v));

	return $affected; // sajnos af affected rows DEPRECATED lett

    }

?>
