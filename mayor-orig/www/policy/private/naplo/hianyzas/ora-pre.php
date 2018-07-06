<?php
/*
    Module: naplo
*/

    if (_RIGHTS_OK !== true) die();

    if (__VEZETOSEG !==true && __TANAR!==true && __NAPLOADMIN!==true) {
	$_SESSION['alert'][] = 'page:insufficient_access';
    } else {

	require_once('include/modules/naplo/share/ora.php');
	require_once('include/modules/naplo/share/orarend.php');
	require_once('include/modules/naplo/share/tankor.php');
	require_once('include/modules/naplo/share/diak.php');
	require_once('include/modules/naplo/share/tanar.php');
	require_once('include/modules/naplo/share/tankor.php');
	require_once('include/modules/naplo/share/hianyzas.php');
	require_once('include/modules/naplo/share/hianyzasModifier.php');
	require_once('include/modules/naplo/share/tankorDiakModifier.php');
	require_once('include/modules/naplo/share/szemeszter.php');
	require_once('include/modules/naplo/share/file.php');
	require_once('include/share/date/names.php');

        require_once('skin/classic/module-naplo/html/share/hianyzas.phtml');

	$napiMaxOra = getMaxOra();
	$napiMinOra = getMinOra();

	$oraId = readVariable($_POST['oraId'],'id',readVariable($_GET['oraId'],'id'));
	$tanarId = readVariable($_POST['tanarId'],'id',readVariable($_GET['tanarId'],'id'));

	if (is_null($oraId) && !is_null($tanarId)) {
	    $_tolDt = $_igDt = date('Y-m-d');
	    $tanarOrak = getTanarOrak(
        	$tanarId,array('tolDt' => $_tolDt, 'igDt' => $_igDt, 'tipus' => array('normál','normál máskor','helyettesítés','felügyelet','összevonás'))
    	    );
	    $oraId = $_POST['oraId'] = $tanarOrak[0]['oraId'];
	}

	if (isset($oraId)) {

	    $ORAADAT = getOraAdatById($oraId);

	    $nap = date('w',strtotime($ORAADAT['dt']));
	    $ora = $ORAADAT['ora'];

	    $tanarId = $ORAADAT['ki'];
	    $tolDt = $igDt = $ORAADAT['dt'];

	    /* tankör adatai - óratervi-e */
	    $TA = getTankorAdat($ORAADAT['tankorId'], __TANEV);
	    $ADAT['tankorAdat'] = $TA[$ORAADAT['tankorId']][0];
	    $ORAADAT['tankorTipus'] = $ADAT['tankorAdat']['tankorTipus'];
	    $ADAT['tagokFelvehetok'] = ($ADAT['tankorAdat']['nevsor']=='változtatható');
	    /* a tankör tagságának bővítése, ha lehet */
	    if ($action!='' && $ADAT['tagokFelvehetok']===true) {
		$D['diakId'] = readVariable($_POST['diakId'],id);
		if ($D['diakId']!=0) {
			$D['tankorId'] = $ORAADAT['tankorId'];
			$D['igDt'] = $D['tolDt'] = $ORAADAT['dt'];
			tankorDiakFelvesz($D);
		}
	    }

	    /* lekérdezés #1 */
	    $ADAT['nevsor'] = getTankorDiakjaiByInterval($ORAADAT['tankorId'], __TANEV, $ORAADAT['dt'], $ORAADAT['dt']);
	    for ($i=0; $i<count($ADAT['nevsor']['idk']); $i++) {
		$_diakId = ($ADAT['nevsor']['idk'][$i]);
		$ADAT['felmentes'][$_diakId] = getTankorDiakFelmentes($_diakId,__TANEV,array('csakId'=>true,'tolDt'=>$tolDt, 'igDt'=>$igDt, 'nap'=>$nap, 'ora'=>$ora));
	    }
	    $ADAT['diakJogviszony'] = getDiakJogviszonyByDts($ADAT['nevsor']['idk'], array($ORAADAT['dt']));
//dump($ADAT['nevsor']['idk']);
//dump($ORAADAT['dt']);
//dump($ADAT['diakJogviszony']);
	    if ($ORAADAT['tipus'] == 'elmarad') {
    		$_SESSION['alert'][] = 'page:elmaradt_ora';
	    } else {
		/* jogosultság */
		$ADAT['jogosultsag'] = getHianyzasJogosultsag(array($ORAADAT) , $ADAT['nevsor']); 
		/* rögzítés */
		if ($action == 'hianyzokRogzitese') {
		    $EDDIG = getHianyzasByOraId($ORAADAT['oraId'],array('csakId'=>true));
		    $MARADNAK = $H = $TORLENDOK = array();
		    // először vizsgáljuk meg, hogy hol hiányzott (mert ekkor f betűs nem lehet)
		    reset($_POST);
		    foreach($_POST as $_kulcs => $_ertek) {
			if (substr($_kulcs,0,8)=='HIANYZOK') {
			    list($_diakId,$_dt,$_ora,$_tipus,$_hid) = explode('/',$_ertek);
			    if ($_tipus=='hiányzás') { // akkor f betűsök nem lehetnek
				$H[$_diakId][$_dt][$_ora] = true;
			    }
			}
		    }
		    // majd állítsuk elő a tömböket
		    reset($_POST);
		    foreach($_POST as $_kulcs => $_ertek) {
			if (substr($_kulcs,0,8)=='HIANYZOK') {
			    // ez volt!!!! figyelem!!!list($_diakId,$_tipus,$_hid) = explode('/',$_ertek);
			    list($__r1,$__id,$__r2) = explode('_',$_kulcs);
			    list($_diakId,$_dt,$_ora,$_tipus,$_hid) = explode('/',$_ertek);
			    if (!isset($H[$_diakId][$_dt][$_ora]) || $_tipus=='hiányzás') {			    
				if ($_hid!='') {
				    $MARADNAK[] = $_hid;
				    if ($_tipus=='késés') $PERCEK[$_hid]=intval($_POST['PERC_'.$__id]);
				} elseif ($_tipus!='') {
				    $_perc = intval($_POST['PERC_'.$__id]);
				    $BEIRANDOK[] = array('diakId'=>$_diakId,'tipus'=>$_tipus,'id'=>$_hid,'dt'=>$_dt,'ora'=>$_ora,'perc'=>$_perc);
				}
			    }
			}
		    }
		    $TORLENDOK = array_diff($EDDIG,$MARADNAK);
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

		    //végül töröljünk, regisztráljunk, igazoljunk
		    if (is_array($EDDIG) && is_array($MARADNAK)) {
			hianyzasTorles($TORLENDOK);
		    }
		    hianyzasRegisztralas($ORAADAT,$BEIRANDOK);
		    hianyzasIgazolas($IGAZOLANDOK);
		    hianyzasPercUpdate($PERCEK);



    		}
		/* --- */
	    
		// lekérdezés #2
		// $ADAT['nevsor'] // lásd feljebb
		$ADAT['hianyzok'] = getHianyzasByOraId($ORAADAT['oraId']);
		$ADAT['napiHianyzasok'] = getHianyzasByDt($ADAT['nevsor']['idk'],$ORAADAT['dt']);

		if ($ADAT['tagokFelvehetok']===true) {
		    $_to = getTankorOsztalyaiByTanev($ORAADAT['tankorId'], __TANEV, array('result' => 'id', 'tagokAlapjan' => false));
		    $ADAT['diakok'] = getDiakok(array('osztalyId'=>$_to));
		    
		}
	    }
	} // Ha van oraId

    $TOOL['tanarSelect'] = array('tipus'=>'cella', 'tanarId' => $tanarId);
    $TOOL['vissza'] = array('tipus'=>'vissza',
	'paramName'=>'vissza',
	'icon'=>'',
	'postOverride' => array('igDt'=>$igDt,'tanarId'=>$tanarId,'page'=>'naplo','sub'=>'haladasi','f'=>'haladasi')
    );
    if (isset($oraId)) $TOOL['tanarOraLapozo'] = array('tipus'=>'sor', 'oraId' => $oraId, 'post'=>array('tanarId'));
    getToolParameters();

    } // Ha jogosult az oldal megtekintésére


?>