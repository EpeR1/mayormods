<?php
/*
    Module: naplo
*/

    if (_RIGHTS_OK !== true) die();

    if (!__TANAR && !__NAPLOADMIN && !__DIAK && !__VEZETOSEG) 
	$_SESSION['alert'][] = 'page:illegal_access';

	require_once('include/modules/naplo/share/ora.php');
	require_once('include/modules/naplo/share/orarend.php');
	require_once('include/modules/naplo/share/tankor.php');
	require_once('include/modules/naplo/share/diak.php');
	require_once('include/modules/naplo/share/tanar.php');
	require_once('include/modules/naplo/share/hianyzas.php');
	require_once('include/modules/naplo/share/hianyzasModifier.php');
	require_once('include/modules/naplo/share/szemeszter.php');
	require_once('include/modules/naplo/share/osztaly.php');
	require_once('include/modules/naplo/share/kepzes.php');
	require_once('include/modules/naplo/share/file.php');
	require_once('include/share/date/names.php');

        require_once('skin/classic/module-naplo/html/share/hianyzas.phtml');

	if (__DIAK) $diakId = __USERDIAKID;
	else {
	    $diakId = readVariable($_POST['diakId'],'numeric',readVariable($_GET['diakId'],'id'));
	    if ($diakId=='') return false;
	}
	$ADAT['diakId'] = $diakId;

	$dt = readVariable($_POST['dt'],'datetime',readVariable($_GET['dt'],'datetime'));
	if ($dt=='') return false;
	$nap = date('w',strtotime($dt));

	define('__MAX_ORA',getMaxOra());
	define('__MIN_ORA',getMinOra());

	$ADAT['nevsor'] = array(
	    'idk' => array($diakId),
	    'adatok' => getTankorByDiakId($diakId,__TANEV,array('tolDt'=>$dt,'igDt'=>$dt)),
	    'nevek' => getDiakokById(array($diakId))
	);

	{
	    /* jogosultság */
	    //$DIAKOSZTALYAI = getDiakokOsztalyai(array($diakId));
	    //define(__OFO, (is_array($_OSZTALYA) && count(array_intersect($DIAKOSZTALYAI[$diakId],$_OSZTALYA)) > 0 ));

	    /* --- */
	    $TANKORIDK = getTankorByDiakId($diakId, __TANEV, $SET = array('csakId' => true, 'tolDt' => $dt, 'igDt' => $dt));

	    $tmpOrak = getOrak($TANKORIDK,array('tolDt'=>$dt,'igDt'=>$dt,'elmaradokNelkul'=>true));
	    $ADAT['orak'] = $tmpOrak['orak'];
    	    if (is_array($ADAT['orak']) && count($ADAT['orak'])>0)
		foreach($ADAT['orak'] as $tankorId => $OA1)
        	    foreach($OA1 as $ora => $OA2)
            		foreach($OA2 as $tankorId => $OA)
                	    $ORAI[] = $OA;
	    $ADAT['jogosultsag'] = getHianyzasJogosultsag($ORAI, array('idk'=>array($diakId)));
            unset($ORAI);

	    /* rögzítés */
            if ($action == 'hianyzokRogzitese' && (__TANAR || __NAPLOADMIN || __VEZETOSEG)) {
                //$EDDIG = getHianyzasByOraId($ORAADAT['oraId'],array('csakId'=>true));
                $EDDIG = getHianyzasByDt(array($diakId),array($dt),array('csakId'=>true));
                $MARADNAK = $H = $TORLENDOK = array();
                // először vizsgáljuk meg, hogy hol hiányzott (mert ekkor f betűs nem lehet)
                reset($_POST);
                foreach($_POST as $_kulcs => $_ertek) {
                    if (substr($_kulcs,0,8)=='HIANYZOK') {
			list($_mi,$_oraId) = explode('_',$_kulcs);
                        list($_diakId,$_dt,$_ora,$_tipus,$_hid) = explode('/',$_ertek);
                        if ($_tipus=='hiányzás') { // akkor f betűsök nem lehetnek
                            $H[$_diakId][$_dt][$_ora] = true;
			    $H2[$_diakId][$_oraId] = true;
                        }
                    }
                }
                // majd állítsuk elő a tömböket
                reset($_POST);
                foreach($_POST as $_kulcs => $_ertek) {
                    if (substr($_kulcs,0,8)=='HIANYZOK') {
			/* Itt az __id most oraId */
                        list($__r1,$__id,$__r2) = explode('_',$_kulcs);
                        list($_diakId,$_dt,$_ora,$_tipus,$_hid) = explode('/',$_ertek);
//                        if (!isset($H[$_diakId][$_dt][$_ora]) || $_tipus=='hiányzás') {
                        if (!isset($H2[$_diakId][$__id]) || $_tipus=='hiányzás') {
                            if ($_hid!='') {
                                $MARADNAK[] = $_hid;
                                if ($_tipus=='késés') $PERCEK[$_hid]=intval($_POST['PERC_'.$__id]);
                            } elseif ($_tipus!='') {
                                $_perc = intval($_POST['PERC_'.$__id]);
				//$_oraId = $ADAT['orak'][$dt];
				//list($_tankorId,$OA) = each($ADAT['orak'][$dt][$_ora]); 
				// EZ HIBÁS! Egy időben több óra is lehet! Az adat postként rendelkezsére áll, használjuk azt fel!
                                $BEIRANDOK[] = array('diakId'=>$_diakId,'tipus'=>$_tipus,'id'=>$_hid,'dt'=>$_dt,'ora'=>$_ora,'perc'=>$_perc,'oraId'=>$__id);
                            }
                        }
                    }
                }
                $TORLENDOK = (is_array($EDDIG)) ? array_diff($EDDIG,$MARADNAK) : array();
                reset($_POST);
                foreach($_POST as $_kulcs => $_ertek) {
                    if (substr($_kulcs,0,8) == 'IGAZOLAS') {
                        for ($i=0; $i<count($_ertek); $i++) {
                            list($_diakId,$ures1,$ures2,$_igtip,$_hid) = explode('/',$_ertek[$i]);
                            if ($_hid!='' && !in_array($_hid,$TORLENDOK)) {
                                if ($_igtip!='') $_statusz='igazolt' ; else $_statusz ='igazolatlan';
                                $IGAZOLANDOK[] = array('id'=> $_hid,'diakId'=>$_diakId,'statusz'=>$_statusz,'igazolas'=>$_igtip);
                            }
                        }
                    }
                }
                // végül töröljünk, regisztráljunk, igazoljunk
		// esetleg okosan kitalálhatjuk, hogy ha egy időpontban van órája, akkor csak egy helyen lehet? (Pl ahol kötelezően kellene lennie...)
                if (is_array($EDDIG) && is_array($MARADNAK)) {
                    hianyzasTorles($TORLENDOK);
                }
                hianyzasRegisztralas(array(),$BEIRANDOK);
                hianyzasIgazolas($IGAZOLANDOK);
                hianyzasPercUpdate($PERCEK);

	    }
	    // lekérdezés #2
	    // $ADAT['nevsor'] // lásd feljebb
	    $ADAT['hianyzok'] = array($diakId);
	    $ADAT['hianyzasok'] = getHianyzasByDt(array($diakId),array('dt'=>$dt));
	    $ADAT['tankorok'] = getTankorByDiakId($diakId, __TANEV, $SET = array('csakId' => false, 'tolDt' => $dt, 'igDt' => $dt,'result'=>'multiassoc'));
	    $ADAT['tankorTipus'] = getTankorTipusok();
	    // ez így egy kicsit túl megengedő. Ha pl egy tárgyból csak az első órán van felmentve, de ugyanaz a tankör szerepel később is (pl második), akkor ida is felmentett lesz...
	    // holott arról nincs fm-tése... Óránként külön kellene vizsgálni... :(
	    $ADAT['felmentett'] = getTankorDiakFelmentes($diakId, __TANEV, array('result'=>'multiassoc','tolDt' => $dt, 'igDt' => $dt, 'nap'=>$nap)); // !!!
	    $ADAT['dt'] = $dt;
	    $ADAT['diak']['nev'] = getDiakNevById($diakId);
            $OI = getDiakOsztalya($diakId);
            $osztalyId = $OI[0]['osztalyId'];
            $ADAT['osztaly'] = getOsztalyAdat($osztalyId);
	    $ADAT['napiMinOra'] = getMinOra();
	    $ADAT['napiMaxOra'] = getMaxOra();

	}
    $_diakJogviszony=getDiakJogviszonyByDts( array($ADAT['diakId']), array($ADAT['dt']) );
    $ADAT['diakJogviszony'] = $_diakJogviszony[$ADAT['diakId']][$ADAT['dt']]['statusz'];
    $ADAT['hianyzasKreta'] = getDiakKretaHianyzas($ADAT['diakId'],array('preprocess'=>'naptar','tolDt'=>$ADAT['dt'],'igDt'=>$ADAT['dt']));
    // tanarSelect tul képpen csak readnly
        $TOOL['datumSelect'] = array(
            'tipus' => 'sor', 'post' => array('diakId'),
            'paramName' => 'dt', 'hanyNaponta' => 1,
            'tolDt' => date('Y-m-d', strtotime($_TANEV['kezdesDt'])),
            'napTipusok' => array('tanítási nap', 'speciális tanítási nap'),
	    'lapozo' => true
        );
//	$TOOL['osztalySelect'] = array('tipus' => 'cella','paramName' => 'osztalyId', 'post' => array('ho'));
	if (!__DIAK) $TOOL['diakSelect'] = array('tipus' => 'cella', 'diakId' => $diakId, 'post' => array('dt'));
        if ($diakId!='') $TOOL['igazolasOsszegzo'] = array('tipus' => 'cella','paramName' => 'igazolasOsszegzo', 'post' => array());

//    $TOOL['tanuloNapLapozo'] = array('tipus'=>'sor', 'dt' => $dt, 'post'=>array('diakId','dt'));
    getToolParameters();

?>
