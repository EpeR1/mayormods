<?php

    if (_RIGHTS_OK !== true) die();
    if (!__NAPLOADMIN && !__VEZETOSEG) $_SESSION['alert'][] = 'page:insufficient_access';

    $tanev = __TANEV;
    
        require_once('include/modules/naplo/share/tanar.php');
	require_once('include/modules/naplo/share/diak.php');
	require_once('include/modules/naplo/share/targy.php');
	require_once('include/modules/naplo/share/munkakozosseg.php');
	require_once('include/modules/naplo/share/tankor.php');
	require_once('include/modules/naplo/share/osztaly.php');
	require_once('include/modules/naplo/share/kepzes.php');
	require_once('include/modules/naplo/share/orarend.php');
	require_once('include/modules/naplo/share/intezmenyek.php');
	require_once('include/share/date/names.php');
        require_once('include/modules/naplo/share/terem.php');
        require_once('include/modules/naplo/share/ora.php');
        require_once('include/modules/naplo/share/oraModifier.php');
        require_once('include/modules/naplo/share/helyettesitesModifier.php');
        require_once('include/modules/naplo/share/tankorBlokk.php');
	     
    $targyId = readVariable($_POST['targyId'], 'id');
    $tankorId = readVariable($_POST['tankorId'], 'id');
    $osztalyId = readVariable($_POST['osztalyId'], 'id');
    $tanarId = readVariable($_POST['tanarId'], 'id');
    $diakId = readVariable($_POST['diakId'], 'id');
    $teremId = readVariable($_POST['teremId'], 'id');
    $mkId = readVariable($_POST['mkId'], 'id');
    $het = readVariable($_POST['het'], 'numeric unsigned');

    $refTolDt = readVariable($_POST['refTolDt'],'date');
    $refIgDt = readVariable($_POST['refIgDt'],'date');

    if ($refTolDt=='' || $refIgDt=='') $_SESSION['alert'][] = 'info:nincs_intervallum';

    $ADAT['telephelyek'] = getTelephelyek();
    $ADAT['haladasiModositando'] = readVariable($_POST['haladasiModositando'], 'bool');

    $telephely = readVariable($_POST['telephely'], 'enum', null,$ADAT['telephelyek']);

    $tolDt = readVariable($_POST['tolDt'], 'date');
    $dt = readVariable($_POST['dt'], 'date'); // mutatni

	if ($mkId=='' && $tanarId=='' && $diakId=='' && $osztalyId=='' && $tankorId=='' && $teremId=='') { // ez itt mind isnotset
	    if (__DIAK && defined('__USERDIAKID')) $diakId=__USERDIAKID;
	    if (__TANAR && defined('__USERTANARID')) $tanarId=__USERTANARID;
	}

    /* Az órarendihét kiválasztása */
    if (isset($dt)) $tolDt = date('Y-m-d', strtotime('last Monday', strtotime('+1 days', strtotime($dt) )));
    if (!isset($tolDt))
	    // A következő nap előtti hétfő
	    $tolDt = date('Y-m-d', strtotime('last Monday', strtotime('+1 days', time())));
/*
	if (strtotime($tolDt) > strtotime($_TANEV['zarasDt'])) $_tolDt = $_TANEV['zarasDt'];
	elseif (strtotime($tolDt) < strtotime($_TANEV['kezdesDt'])) $_tolDt = $_TANEV['kezdesDt'];
	// és akkor korrigáljunk még egyszer
        if (isset($_tolDt)) // A következő nap előtti hétfő
	    $tolDt = date('Y-m-d', strtotime('last Monday', strtotime('+1 days', time())));
*/

	if ($tolDt != '') $het = getOrarendiHetByDt($tolDt);		
	if ($het == '') $het = getLastOrarend();
	$igDt = date('Y-m-d', mktime(0,0,0,date('m',strtotime($tolDt)), date('d',strtotime($tolDt))+6, date('Y',strtotime($tolDt))));

    // itt ellenőrizzük, hogy a dt (referenciadátum) beleesik-e a tolIg be!
    if (isset($tolDt) && isset($refTolDt) && isset($refIgDt)) {
	if (strtotime($tolDt)<strtotime($refTolDt)) $_SESSION['alert'][] = 'message:wrong_data:hibás referenciadátum!';
	if (strtotime($tolDt)>strtotime($refIgDt)) $_SESSION['alert'][] = 'message:wrong_data:hibás referenciadátum!';
	if (strtotime($refTolDt)>strtotime($refIgDt)) $_SESSION['alert'][] = 'message:wrong_data:hibás referenciadátum!';
    }
    if ($action==='do') {
	$HOT = readVariable($_POST['HALADASIORATOROL'],'id');
	//dump($HOT);
	for ($i=0; $i<count($HOT); $i++) {
	    oraElmarad($HOT[$i]);
	}
	for ($i=0; $i<count($_POST['ORARENDIORATOROL']); $i++) {
	    if ($_POST['ORARENDIORATOROL'][$i]!='') {
		$_ADAT = array();
		list($_het,$_nap,$_ora,$_tanarId,$_tolDt) = explode('%',$_POST['ORARENDIORATOROL'][$i]);
		$_ADAT['tanarId'] = readVariable($_tanarId,'id',null);
		$_ADAT['het'] = readVariable($_het,'id',null);
		$_ADAT['nap'] = readVariable($_nap,'id',null);
		$_ADAT['ora'] = readVariable($_ora,'id',null);
		$_ADAT['tolDt'] = readVariable($_tolDt,'date',null);
		orarendiOraTorol($_ADAT);
	    }
	}

	for ($i=0; $i<count($_POST['ORARENDIORAFELVESZ']); $i++) {
	    if ($_POST['ORARENDIORAFELVESZ'][$i]!='') {
		$_ADAT = array();
		list($_tanarId,$_osztalyJel,$_targyJel,$_tankorId) = explode('%',$_POST['ORARENDIORAFELVESZ'][$i]);
		$_ADAT['tanarId'] = readVariable($_tanarId,'id',null);
		$_ADAT['osztalyJel'] = readVariable($_osztalyJel,'string',null);
		$_ADAT['targyJel'] = readVariable($_targyJel,'string',null);
		$_ADAT['tankorId'] = readVariable($_tankorId,'id',null);
		orarendiOraTankorFelvesz($_ADAT);
	    }
	}

	for ($i=0; $i<count($_POST['ORARENDMINUSZ']); $i++) {
	    if ($_POST['ORARENDMINUSZ'][$i]!='') {
		$_ADAT = array();
		list($_het,$_nap,$_ora,$_tanarId,$_dt,$_kulcsTolDt) = explode('.',$_POST['ORARENDMINUSZ'][$i]);
		$_teremId = $_POST["T_".$_het."_".$_nap."_".$_ora]; // ez érdekes?
		$_ADAT['het'] = readVariable($_het,'id',null);
		$_ADAT['nap'] = readVariable($_nap,'id',null);
		$_ADAT['ora'] = readVariable($_ora,'id',null);
		$_ADAT['tanarId'] = readVariable($_tanarId,'id',null);
		$_ADAT['tolDt'] = readVariable($refTolDt,'date');
		$_ADAT['igDt'] = readVariable($refIgDt,'date');
		$_ADAT['kulcsTolDt'] = readVariable($_kulcsTolDt,'date');
		$_ADAT['dt'] = $_dt ; //readVariable($_POST['tolDt'],'date');

		$_ADAT['orarendiOraKulcs'] = readVariable($refIgDt,'date');
		/*
		    Az órarendből kiválasztott (órarendi)hét, nap, óra, tanárId alapján 
		    a $ADAT['dt'] időben érvényes bejegyzés $ADAT['tolDt'] és $ADAT['igDt'] közé eső szakaszát
		    töröljük az órarendből
		    ezért: $ADAT['tolDt'] <= $ADAT['dt'] <= $ADAT['igDt'] - feltétel!
		$_ADAT['dt'] = readVariable($_POST['tolDt'],'date',null,null,'('.strtotime($_ADAT['tolDt']).'<=strtotime($return) && strtotime($return) <= '.strtotime($_ADAT['igDt']).')');
		    Mégsem kell, mert lehet, hogy az intervallumon kívüli bejegyzés érvényessége belenyúlik az intervallumba
		*/
		if (isset($_ADAT['het']) && isset($_ADAT['nap']) && isset($_ADAT['ora']) && isset($_ADAT['tanarId']) && isset($_ADAT['dt'])) orarendiOraLezar($_ADAT);
	    }
	}

	for ($i=0; $i<count($_POST['ORARENDPLUSZ']); $i++) {
	    if ($_POST['ORARENDPLUSZ'][$i]!='') {
		$_ADAT = array();
		list($_TB,$_het,$_nap,$_ora,$_Id) = explode('.',$_POST['ORARENDPLUSZ'][$i]);
		$_teremId = readVariable($_POST["T_".$_het."_".$_nap."_".$_ora], 'id');
		$_ADAT['het'] = readVariable($_het,'id');
		$_ADAT['nap'] = readVariable($_nap,'id');
		$_ADAT['ora'] = readVariable($_ora,'id');
		$_ADAT['tolDt'] = readVariable($refTolDt,'date');
		$_ADAT['igDt'] = readVariable($refIgDt,'date');
		$_ADAT['tankorId'] = $_ADAT['blokkId'] =readVariable($_Id,'id'); // vagy tankör vagy blokk ID szerepel. a $_TB mondja meg
		$_ADAT['teremId'] = readVariable($_teremId,'id',null);
		$_ADAT['tanarId'] = readVariable($tanarId,'id',null);
		$_ADAT['haladasiModositando'] = $ADAT['haladasiModositando'];
		if ($_TB == 'T') pluszOraFelvesz($_ADAT);
		elseif ($_TB == 'B') pluszBlokkFelvesz($_ADAT);
		else $_SESSION['alert'][] = '::ajjajj';
	    }
	}
	/* A termeken külön */
	foreach($_POST as $key=>$value) {
    	    if ($value!='' && substr($key,0,2)=='T_') {
		$_ADAT = array();
		list($_pre,$_het,$_nap,$_ora,$_tanarId,$_kulcsTolDt) = explode('_',$key);
		$_ADAT['teremId'] = readVariable($value, 'id');
		$_ADAT['het'] = readVariable($_het,'id');
		$_ADAT['nap'] = readVariable($_nap,'id');
		$_ADAT['ora'] = readVariable($_ora,'id');
		$_ADAT['tolDt'] = readVariable($refTolDt,'date');
		$_ADAT['igDt'] = readVariable($refIgDt,'date');
		$_ADAT['refDt'] = readVariable($refDt,'date');
		$_ADAT['tanarId'] = readVariable($_tanarId,'id',null);
dump($_ADAT);
		teremModosit($_ADAT);
	    }
	}
	unset($_ADAT);
    }


    $ADAT['termek'] = getTermek(array('result'=>'assoc'));
    $ADAT['tanarok'] = getTanarok(array('result'=>'assoc'));
// =====================
    if ($tankorId!='') {
	$ADAT['orarend'] = getOrarendByTankorId($tankorId, array('tolDt'=>$tolDt,'igDt'=>$igDt));
	//$ADAT['toPrint'] = getTankorNev($tankorId); // vagy getTankornev külön?
    } elseif($tanarId!='') {
        $ADAT['orarend'] = getOrarendByTanarId($tanarId,array('tolDt'=>$tolDt,'igDt'=>$igDt,'telephely'=>$telephely,'orarendiOraTankor'=>true));
	$ADAT['toPrint'] = $ADAT['tanarok'][$tanarId]['tanarNev'];
	$ADAT['felvehetoTankorok'] = getTankorByTanarId($tanarId,$tanev, array('csakId'=>false,'tolDt'=>$refTolDt, 'igDt'=>$refIgDt));

//========================
/* illesszük ide az Órarendi óra tankör összerendezés 4.6 */
	$ADAT['orarendioraTankor'] = getOrarendByTanarId($tanarId,array('tolDt'=>$tolDt,'igDt'=>$igDt,'orarendiOraTankor'=>true));
//========================
	$TANKORIDK = getTankorByTanarId($tanarId, __TANEV, array('csakId' => true, 'tolDt' => $tolDt, 'igDt' => $igDt, 'result' => 'indexed', 'tanarral' => false));
	$ADAT['haladasi'] = getOrak($TANKORIDK, array('tolDt'=>$tolDt,'igDt'=>$igDt, 'result'=>'likeOrarend', 'elmaradokNelkul'=>false));
	$ADAT['vanHaladasi'] = checkHaladasi(array('tolDt'=>$refTolDt,'igDt'=>$refIgDt));
    } elseif($diakId!='') {
        $ADAT['orarend'] = getOrarendByDiakId($diakId,array('tolDt'=>$tolDt,'igDt'=>$igDt));
    } elseif ($osztalyId!='') {
	$ADAT['orarend'] = getOrarendByOsztalyId($osztalyId,array('tolDt'=>$tolDt,'igDt'=>$igDt));
	$OADAT = getOsztalyAdat($osztalyId);
	$ADAT['toPrint'] = $OADAT['osztalyJel'];
    } elseif ($mkId!='') {
	$ADAT['orarend'] = getOrarendByMkId($mkId,array('tolDt'=>$tolDt,'igDt'=>$igDt,'telephely'=>$telephely));
    } elseif ($teremId!='') {
	$ADAT['orarend'] = getOrarendByTeremId($teremId,'',array('tolDt'=>$tolDt,'igDt'=>$igDt,'telephely'=>$telephely));
    }
        else $ADAT = array();

	$TANKOROK['erintett'] = $ADAT['orarend']['tankorok'];
	$TANKOROK['mindenByDt'] = $ADAT['orarend']['mindenTankorByDt'];

	$TANKOROK = (is_array($ADAT['orarend']['tankorok']) && is_array($ADAT['haladasi']['tankorok'])) ?
	    array_unique(array_merge($ADAT['orarend']['tankorok'],$ADAT['haladasi']['tankorok']))
	    :
	    $ADAT['orarend']['tankorok'];
	$ADAT['tankorok'] = getTankorAdatByIds($TANKOROK);
	/* tankörlétszámok */
	if (is_array($ADAT['tankorok'])) foreach ($ADAT['tankorok'] as $_tankorId =>$_T) {
	    $ADAT['tankorLetszamok'][$_tankorId] = getTankorLetszam($_tankorId,array('refDt'=>$tolDt));
	}

        /* quick fix */
	$_T = array();
        //if (count($TANKOROK)==0) {
            for ($i=0; $i<count($ADAT['felvehetoTankorok']); $i++) {
                $_T[] = $ADAT['felvehetoTankorok'][$i]['tankorId'];
            }
        //} else $_T = $TANKOROK;
        $ADAT['felvehetoBlokkok'] = getFelvehetoBlokk(array('tankorIds'=>$_T));

	$ADAT['hibasOrak'] = getHibasOrak();

	//--//
	$ADAT['dt'] = $dt; // show this... ha a skin ajax

	//$ADAT['napiMinOra'] = getMinOra();
	$ADAT['napiMinOra'] = 0; // Ha még nincs is 0. óra, lehessen felvenni, ha kell
	$ADAT['napiMaxOra'] = getMaxOra()+1; // +1 mindig legyen, ha nincs konstans, akkor is
	if ($ADAT['napiMaxOra'] < __MAXORA_MINIMUMA) $ADAT['napiMaxOra'] = __MAXORA_MINIMUMA; // Legalább __MAXORA_MINIMUMA órát fel lehessen venni
	$ADAT['hetiMaxNap'] = getMaxNap();
	if ($ADAT['hetiMaxNap'] < __HETIMAXNAP_MINIMUMA) $ADAT['hetiMaxNap'] = __HETIMAXNAP_MINIMUMA;

	$ADAT['tanarId'] = $tanarId;
	$ADAT['refTolDt'] = $refTolDt;
	$ADAT['refIgDt'] = $refIgDt;
	$ADAT['tolDt'] = $tolDt;
//=====================================

	/* TOOL ME :) */
        $TOOL['datumSelect'] = array(
            'tipus'=>'cella', 'post'=>array('tanarId','osztalyId','tankorId','mkId','diakId','telephely','refTolDt','refIgDt'),
	    'paramName' => 'tolDt', 'title' => 'ORARENDDT',
	    'hanyNaponta' => 7,
	    'override'=>true, // használathoz még át kell írni pár függvényt!!!
//	    'tolDt' => date('Y-m-d', strtotime('Monday', strtotime($_TANEV['kezdesDt']))),
	    'tolDt' => date('Y-m-d', strtotime('last Monday', strtotime($_TANEV['kezdesDt']))),
//	    'tolDt' => $_TANEV['elozoZarasDt'],
	    'igDt' => $_TANEV['kovetkezoKezdesDt'],
	);
       $TOOL['datumTolIgSelect'] = array(
            'tipus' => 'sor', 'title' => 'REFDT',
	    'post'=>array('tolDt','tanarId','osztalyId','tankorId','mkId','diakId','telephely'),
            'tolParamName' => 'refTolDt', 'igParamName' => 'refIgDt', 'hanyNaponta' => 1,
	    'tolDt' => $_TANEV['elozoZarasDt'],
	    'igDt' => $_TANEV['kovetkezoKezdesDt'],
	    'override' => true,
            'tolDt' => date('Y-m-d', strtotime('last Monday', strtotime($_TANEV['kezdesDt']))),
            'igDt' => $_TANEV['zarasDt'],
        );

	//$TOOL['orarendiHetSelect'] = array('tipus'=>'cella' , 'paramName' => 'het', 'post'=>array('targyId','tankorId','osztalyId','tanarId'), 'disabled'=>true);
//	if ($osztalyId!='' || $diakId!='') {
//	    $TOOL['diakSelect'] = array('tipus'=>'sor','paramName'=>'diakId', 'post'=>array('refTolDt','refIgDt','tolDt','osztalyId','telephely'));
//	} else 
//	    $TOOL['munkakozossegSelect'] = array('tipus'=>'sor', 'paramName'=>'mkId', 'post'=>array('refTolDt','refIgDt','tolDt','telephely'));
	$TOOL['tanarSelect'] = array('tipus'=>'cella', 'paramName'=>'tanarId', 'post'=>array('refTolDt','refIgDt','tolDt','telephely'));
//	$TOOL['osztalySelect']= array('tipus'=>'cella', 'paramName'=>'osztalyId', 'post'=>array('refTolDt','refIgDt','tolDt'));
//	$TOOL['telephelySelect'] = array('tipus'=>'cella', 'paramName'=>'telephely', 'post'=>array('refTolDt','refIgDt','tolDt','mkId','tanarId'));
//	$TOOL['teremSelect'] = array('tipus'=>'cella', 'paramName'=>'teremId', 'telephely'=>$telephely, 'post'=>array('refTolDt','refIgDt','tolDt','telephely'));
        if ($osztalyId!='' || $tanarId!='' || $diakId!='' || $mkId!='') $TOOL['tankorSelect'] = array('tipus'=>'sor','paramName'=>'tankorId', 'post'=>array('refTolDt','refIgDt','tolDt','osztalyId','targyId','tanarId','diakId','telephely'));
	getToolParameters();

?>
