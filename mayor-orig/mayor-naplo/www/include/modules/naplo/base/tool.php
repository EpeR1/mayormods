<?php

if ( !isset($TOOL) || !is_array($TOOL))
$TOOL = array();

function getToolParameters() {

	global $TOOL;

	$TOOLVARS = array(
	    'diakId'=> array('type'=>'id'),
	    'tanarId'=> array('type'=>'id'),
	    'szuloId'=> array('type'=>'id'),
	    'teremId'=> array('type'=>'id'),
	    'osztalyId'=> array('type'=>'id'),
	    'targyId'=> array('type'=>'id'),
	    'mkId'=> array('type'=>'id'),
	    'tankorId'=> array('type'=>'id'),
	    'telephelyId' => array('type'=>'id'),

	    'tolDt' => array('type'=>'datetime'),
	    'igDt' => array('type'=>'datetime'),
	    'refDt' => array('type'=>'datetime'),
	    'dt' => array('type'=>'datetime'),

	    'tanev' => array('type'=>'numeric unsigned'),
	    'het' => array('type'=>'numeric unsigned'),

	    'fileName' => array('type'=>'strictstring'),
	    'conv' => array('type'=>'strictstring'),
	    'sorrendNev' => array('type'=>'enum','allowOnly' => array('napló','anyakönyv','ellenőrző','bizonyítvány','egyedi')),
	    'targySorrend' => array('type'=>'strictstring'),
	    'vizsgaTipus' => array('type'=>'enum','allowOnly' => array('osztályozó vizsga','javítóvizsga','különbözetivizsga','beszámoltatóvizsga')),


	    // ellenőrizendő még:	    
	    'ho' => array('type'=>'strictstring'),
	    'ora' => array('type'=>'strictstring'),
	    'tipus' => array('type'=>'strictstring'),
//	    'telephely' => array('type'=>'strictstring'),
	    // ...
	);


	foreach ( $TOOL as $tool => $params ) if (is_array($params)) {

	    for ($i = 0; $i < count($params['post']); $i++) {
		$_var = $params['post'][$i];
		//if ($TOOLVARS[$_var]=='') $_SESSION['alert'][] = '::toolvars:'.$_var; // nem üzenünk hibát
		if ( $_POST[$_var]!='' && ($TOOLVARS[$_var]['type']!='') ) { // ellenőrizzük a fenti tömb szerinti változókat.
		    // itt típuskonverzió is történik
		    $_POST[$_var] = readVariable($_POST[$_var],$TOOLVARS[$_var]['type'],null,$TOOLVARS[$_var]['allowOnly']);
		}
	    }

	    if ( function_exists( $func = "get$tool" ) ) {
			$func();
	    }

	}

}


/* AUDIT */

/*
getAuditInfo();
function getAuditInfo() {

global $page,$sub,$f;

$_SESSION['alert'][] = '::'.$page.$sub.$f;

$WORK = $_POST;

for ($i
reset($WORK);
ksort($WORK);
$X = unserialize(serialize($WORK));

var_dump($X);

}
  */


/* ------------------------- */


function getSzamSelect() {

	global $TOOL;

	if (!is_array($TOOL['szamSelect']['szamok'])) {
	    $minValue = (isset($TOOL['szamSelect']['minValue']))?$TOOL['szamSelect']['minValue']:1;
	    $maxValue = (isset($TOOL['szamSelect']['maxValue']))?$TOOL['szamSelect']['maxValue']:100;
	    $TOOL['szamSelect']['szamok'] = range($minValue, $maxValue);
	}

	if ( !isset($TOOL['szamSelect']['paramName']) || $TOOL['szamSelect']['paramName']=='' )
	$TOOL['szamSelect']['paramName'] = 'szam';

}

function getIntezmenySelect() {

	global $TOOL;

	if (!is_array($TOOL['intezmenySelect']['intezmenyek'])) {
	    require_once('include/modules/naplo/share/intezmenyek.php');
	    $TOOL['intezmenySelect']['intezmenyek'] = getIntezmenyek();
	}
	if ( !isset($TOOL['intezmenySelect']['paramName']) || $TOOL['intezmenySelect']['paramName'] == '' )
	    $TOOL['intezmenySelect']['paramName'] = 'intezmeny';

}

function getTelephelySelect() {

	global $TOOL;

	if (!is_array($TOOL['telephelySelect']['telephelyek'])) {
	    require_once('include/modules/naplo/share/intezmenyek.php');
	    $TOOL['telephelySelect']['telephelyek'] = getTelephelyek();
	}
	if ( !isset($TOOL['telephelySelect']['paramName']) || $TOOL['telephelySelect']['paramName'] == '' )
	    $TOOL['telephelySelect']['paramName'] = 'telephelyId';
	if (count($TOOL['telephelySelect']['telephelyek']) < 2) unset($TOOL['telephelySelect']);

}

function getTanevSelect() {

	global $TOOL;

	require_once('include/modules/naplo/share/intezmenyek.php');
	if (!is_array($TOOL['tanevSelect']['tanevek'])) $TOOL['tanevSelect']['tanevek'] = getTanevek($TOOL['tanevSelect']['tervezett']);

	if ( !isset($TOOL['tanevSelect']['paramName']) || $TOOL['tanevSelect']['paramName']=='' )
	$TOOL['tanevSelect']['paramName'] = 'tanev';

}

function getSzemeszterSelect() {

	global $TOOL;

	require_once('include/modules/naplo/share/szemeszter.php');
	$TOOL['szemeszterSelect']['szemeszterek'] = getSzemeszterek($TOOL['szemeszterSelect']);

	if ( !isset($TOOL['szemeszterSelect']['paramName']) || $TOOL['szemeszterSelect']['paramName'] == '' )
	$TOOL['szemeszterSelect']['paramName'] = 'szemeszterId';

}

function getTargySorrendSelect() {

	global $TOOL;

	require_once('include/modules/naplo/share/targy.php');
	if (!isset($TOOL['targySorrendSelect']['tanev'])) $TOOL['targySorrendSelect']['tanev'] = __TANEV;
	$TOOL['targySorrendSelect']['sorrendNevek'] = getTargySorrendNevek($TOOL['targySorrendSelect']['tanev']);

	if ( !isset($TOOL['targySorrendSelect']['paramName']) || $TOOL['targySorrendSelect']['paramName'] == '' )
	$TOOL['targySorrendSelect']['paramName'] = 'sorrendNev';

}

function getMunkakozossegSelect() {

	global $TOOL;

	if (!is_array($TOOL['munkakozossegSelect']['munkakozossegek'])) 
	    $TOOL['munkakozossegSelect']['munkakozossegek'] = getMunkakozossegek();
	if ( !isset($TOOL['munkakozossegSelect']['paramName']) || $TOOL['munkakozossegSelect']['paramName']=='' )
	$TOOL['munkakozossegSelect']['paramName'] = 'mkId';

}

function getTargySelect() {

	global $TOOL;

	if (!is_array($TOOL['targySelect']['targyak']))
	    $TOOL['targySelect']['targyak'] = getTargyak(array('mkId' => $TOOL['targySelect']['mkId']));
	if ( !isset($TOOL['targySelect']['paramName']) || $TOOL['targySelect']['paramName']=='' )
	$TOOL['targySelect']['paramName'] = 'targyId';

}

function getMunkatervSelect() {

	global $TOOL;

	if (!is_array($TOOL['munkatervSelect']['munkatervek']))
	    $TOOL['munkatervSelect']['munkatervek'] = getMunkatervek();
	if ( !isset($TOOL['munkatervSelect']['paramName']) || $TOOL['munkatervSelect']['paramName']=='' )
	$TOOL['munkatervSelect']['paramName'] = 'munkatervId';

}

function getTanarSelect() {

    global $TOOL;

    if (!is_array($TOOL['tanarSelect']['tanarok'])) {
	if (!isset($TOOL['tanarSelect']['tanev']) && defined('__TANEV')) $TOOL['tanarSelect']['tanev'] = __TANEV;
	if (is_array($TOOL['tanarSelect']['Param'])) $Param = $TOOL['tanarSelect']['Param'];
	else $Param = array(
	    'mkId' => $TOOL['tanarSelect']['mkId'],
	    'tanev' => $TOOL['tanarSelect']['tanev'],
	    'beDt' => $TOOL['tanarSelect']['beDt'],
	    'kiDt' => $TOOL['tanarSelect']['kiDt'],
	    'összes' => $TOOL['tanarSelect']['összes'],
	    'override' => $TOOL['tanarSelect']['override'],
	);
	$TOOL['tanarSelect']['tanarok'] = getTanarok($Param);
    }
    if (!isset($TOOL['tanarSelect']['paramName']) || $TOOL['tanarSelect']['paramName']=='' )
	$TOOL['tanarSelect']['paramName'] = 'tanarId';

}

function getDiakSelect() {

	global $TOOL, $osztalyId;

	if (!isset($TOOL['diakSelect']['osztalyId']) && isset($osztalyId))
		$TOOL['diakSelect']['osztalyId'] = $osztalyId;
	if (!is_array($TOOL['diakSelect']['diakok']))
	    $TOOL['diakSelect']['diakok'] = getDiakok(array(
		'osztalyId' => $TOOL['diakSelect']['osztalyId'], 
		'tanev' => $TOOL['diakSelect']['tanev'], 
		'statusz' => $TOOL['diakSelect']['statusz'],
		'tolDt' => $TOOL['diakSelect']['tolDt'],
		'igDt' => $TOOL['diakSelect']['igDt'],
	    ));
	if (!is_array($TOOL['diakSelect']['statusz'])) 
	    $TOOL['diakSelect']['statusz'] = array('jogviszonyban van','magántanuló','egyéni munkarend','vendégtanuló','jogviszonya felfüggesztve','jogviszonya lezárva','felvételt nyert');
	if ( !isset($TOOL['diakSelect']['paramName']) || $TOOL['diakSelect']['paramName']=='' )
	$TOOL['diakSelect']['paramName'] = 'diakId';
}

function getDiakLapozo() {

	global $TOOL, $osztalyId;

	if (!isset($TOOL['diakLapozo']['osztalyId']) && isset($osztalyId))
		$TOOL['diakLapozo']['osztalyId'] = $osztalyId;
	if (!is_array($TOOL['diakLapozo']['diakok']))
	    $TOOL['diakLapozo']['diakok'] = getDiakok(array(
		'osztalyId' => $TOOL['diakLapozo']['osztalyId'], 
		'tanev' => $TOOL['diakLapozo']['tanev'], 
		'statusz' => $TOOL['diakLapozo']['statusz'],
		'tolDt' => $TOOL['diakLapozo']['tolDt'],
		'igDt' => $TOOL['diakLapozo']['igDt'],
	    ));
	if (!is_array($TOOL['diakLapozo']['statusz'])) 
	    $TOOL['diakLapozo']['statusz'] = array('jogviszonyban van','magántanuló','egyéni munkarend','vendégtanuló','jogviszonya felfüggesztve','jogviszonya lezárva','felvételt nyert');
	if ( !isset($TOOL['diakLapozo']['paramName']) || $TOOL['diakLapozo']['paramName']=='' )
	$TOOL['diakLapozo']['paramName'] = 'diakId';

}


function getTableSelect () {

	global $TOOL;

	$TOOL['tableSelect']['naplo'] =$TOOL['tableSelect']['naplo_intezmeny'] = array();
	if (defined('__INTEZMENY')) {
		$TOOL['tableSelect']['naplo_intezmeny'] = db_query('SHOW TABLES', array('fv' => 'getTableSelect', 'modul' => 'naplo_intezmeny', 'result' => 'idonly'));
	}
	if (defined('__TANEV')) {
		$TOOL['tableSelect']['naplo'] = 
		db_query('SHOW TABLES', array('fv' => 'getTableSelect', 'modul' => 'naplo', 'result' => 'idonly'));
	}
	if ( !isset($TOOL['tableSelect']['paramName']) || $TOOL['tableSelect']['paramName']=='' )
	$TOOL['tableSelect']['paramName'] = 'dbtable';
	 
}

function getOsztalySelect() {

	global $TOOL, $tanev;

	if (!isset($TOOL['osztalySelect']['tanev'])) {
	    if (isset($tanev)) $TOOL['osztalySelect']['tanev'] = $tanev;
	    elseif (defined('__TANEV')) $TOOL['osztalySelect']['tanev'] = __TANEV;
	}
	global $telephelyId;
	if (!isset($TOOL['osztalySelect']['osztalyok']))
	    if (isset($TOOL['osztalySelect']['tanev']))
		$TOOL['osztalySelect']['osztalyok'] = getOsztalyok($TOOL['osztalySelect']['tanev'],array('mindenOsztalyfonok'=>true, 'result'=>'indexed','telephelyId' => $telephelyId));
	    else 
		$TOOL['osztalySelect']['osztalyok'] = array();

	if ( !isset($TOOL['osztalySelect']['paramName']) || $TOOL['osztalySelect']['paramName']=='' )
	$TOOL['osztalySelect']['paramName'] = 'osztalyId';

}

function getTanmenetSelect() {

	global $TOOL, $tanev, $tanarId, $targyId;

	if (!isset($TOOL['tanmenetSelect']['tanev'])) {
	    if (isset($tanev)) $TOOL['tanmenetSelect']['tanev'] = $tanev;
	    elseif (defined('__TANEV')) $TOOL['tanmenetSelect']['tanev'] = __TANEV;
	}

	if (!isset($TOOL['tanmenetSelect']['tanmenetek']))
	    if (isset($TOOL['tanmenetSelect']['tanev'])) {
		if (isset($tanarId)) $TOOL['tanmenetSelect']['tanmenetek'] = getTanmenetByTanarId($tanarId, array('tanev' => $TOOL['tanmenetSelect']['tanev']));
		elseif (isset($targyId)) $TOOL['tanmenetSelect']['tanmenetek'] = getTanmenetByTargyId($targyId, array('tanev' => $TOOL['tanmenetSelect']['tanev']));
//	    } else {
//		$TOOL['tanmenetSelect']['tanmenetek'] = array();
	    }

	if ( !isset($TOOL['tanmenetSelect']['paramName']) || $TOOL['tanmenetSelect']['paramName']=='' )
	$TOOL['tanmenetSelect']['paramName'] = 'tanmenetId';

}

function getTankorSelect() {

	global $TOOL, $tanev, $mkId, $targyId, $osztalyId, $tanarId, $diakId;

	// Tanév beállítás: paraméter, globális változó, konstans
	if (!isset($TOOL['tankorSelect']['tanev'])) {
	    if (isset($tanev)) $TOOL['tankorSelect']['tanev'] = $tanev;
	    elseif (defined('__TANEV')) $TOOL['tankorSelect']['tanev'] = __TANEV;
	}
	$tolDt=$TOOL['tankorSelect']['tolDt'];
	$igDt=$TOOL['tankorSelect']['igDt'];

	// Paraméter neve
	if ( !isset($TOOL['tankorSelect']['paramName']) || $TOOL['tankorSelect']['paramName']=='' )
	$TOOL['tankorSelect']['paramName'] = 'tankorId';

	// tankörök lekérdezése - ha még nem történt meg
	if (!is_array($TOOL['tankorSelect']['tankorok'])) {
	    if (isset($diakId) && $diakId!='') { // diák tankörei
		$TOOL['tankorSelect']['tankorok'] = getTankorByDiakId($diakId, $TOOL['tankorSelect']['tanev'], array('tolDt'=>$tolDt, 'igDt'=>$igDt));
	    } elseif (isset($osztalyId) && $osztalyId!='') { // osztály tankörei
		$TOOL['tankorSelect']['tankorok'] = getTankorByOsztalyId($osztalyId, $TOOL['tankorSelect']['tanev'], array('tolDt'=>$tolDt, 'igDt'=>$igDt));
	    } elseif (isset($tanarId) && $tanarId!='') { // tanár tankörei
		$TOOL['tankorSelect']['tankorok'] = getTankorByTanarId($tanarId, $TOOL['tankorSelect']['tanev'], array('tolDt'=>$tolDt, 'igDt'=>$igDt));
	    } else { // általános tankörlekérdző
		$WHERE = array();
		if (isset($targyId) && $targyId != '') { // leszűkítés adott tárgyra
		    $WHERE[] = 'targyId='.$targyId;
		} elseif (isset($mkId) && $mkId != '') { // leszűkítés adott munkaközösségre
		    $TARGYAK = getTargyakByMkId($mkId);
		    for ($i = 0; $i < count($TARGYAK); $i++) $T[] = $TARGYAK[$i]['targyId'];
		    if (count($T) > 0) $WHERE[] = 'targyId IN ('.implode(',', $T).')';
		}

		if (isset($TOOL['tankorSelect']['tanev'])) // szűkítés adott tanévre
		    $WHERE[] = 'tankorSzemeszter.tanev='.$TOOL['tankorSelect']['tanev'];

		$TOOL['tankorSelect']['tankorok'] = getTankorok($WHERE);
	    }
	} else {
	    // A megadott tankörök csoportosításához
	    if (!is_array($TOOL['tankorSelect']['tankorIds'])) {
		if (isset($diakId) && $diakId != '') { // diák tankörei
		    $TOOL['tankorSelect']['tankorIds'] = getTankorByDiakId($diakId, $TOOL['tankorSelect']['tanev'],array('csakId' => true, 'tolDt'=>$tolDt, 'igDt'=>$igDt ));
		} elseif (isset($osztalyId) && $osztalyId != '') { // osztály tankörei
		    $TOOL['tankorSelect']['tankorIds'] = getTankorByOsztalyId($osztalyId, $TOOL['tankorSelect']['tanev'], array('csakId' => true, 'tolDt'=>$tolDt, 'igDt'=>$igDt));
		} elseif (isset($tanarId) && $tanarId != '') { // tanár tankörei
		    $TOOL['tankorSelect']['tankorIds'] = getTankorByTanarId($tanarId, $TOOL['tankorSelect']['tanev'], array('csakId' => true,'tolDt'=>$tolDt, 'igDt'=>$igDt));
		}
	    }
	}
	if ($tolDt!='' || $igDt!='')
	    $TOOL['tankorSelect']['tankorIdsDt'] = $tolDt.'-'.$igDt;

}

function getDatumSelect() {

	global $TOOL, $tanev;

	if (isset($tanev)) $TOOL['datumSelect']['tanev'] = $tanev;
	elseif (defined('__TANEV')) $TOOL['datumSelect']['tanev'] = __TANEV;
	
	if (
	    (is_array($TOOL['datumSelect']['napTipusok']) || isset($TOOL['datumSelect']['napokSzama']))
	    && !is_array($TOOL['datumSelect']['napok'])
	) {
	    $TOOL['datumSelect']['napok'] = getNapok(
		array(
		    'tanev' => $TOOL['datumSelect']['tanev'],
		    'tolDt' => $TOOL['datumSelect']['tolDt'],
		    'igDt' => $TOOL['datumSelect']['igDt'],
		    'tipus' => $TOOL['datumSelect']['napTipusok'],
		    'napokSzama' => $TOOL['datumSelect']['napokSzama'],
		)
	    );
	} else {

	    $tolDt = $TOOL['datumSelect']['tolDt']; $igDt = $TOOL['datumSelect']['igDt'];
	    initTolIgDt($TOOL['datumSelect']['tanev'], $tolDt, $igDt, $TOOL['datumSelect']['override']);
	    $TOOL['datumSelect']['tolDt'] = $tolDt; $TOOL['datumSelect']['igDt'] = $igDt;

	    if (!isset($TOOL['datumSelect']['hanyNaponta']) || $TOOL['datumSelect']['hanyNaponta']=='' )
	    $TOOL['datumSelect']['hanyNaponta'] = 1;

	};

	if (!isset($TOOL['datumSelect']['paramName']) || $TOOL['datumSelect']['paramName']=='' )
	$TOOL['datumSelect']['paramName'] = 'dt';
}

function getDatumTolIgSelect() {

	global $TOOL, $tanev;

	if (isset($tanev)) $TOOL['datumTolIgSelect']['tanev'] = $tanev;
	elseif (defined('__TANEV')) $TOOL['datumTolIgSelect']['tanev'] = __TANEV;
	
	if (
	    (is_array($TOOL['datumTolIgSelect']['napTipusok']) || isset($TOOL['datumTolIgSelect']['napokSzama']))
	    && !is_array($TOOL['datumTolIgSelect']['napok'])
	) {
	    $TOOL['datumTolIgSelect']['napok'] = getNapok(
		array(
		    'tanev' => $TOOL['datumTolIgSelect']['tanev'],
		    'tolDt' => $TOOL['datumTolIgSelect']['tolDt'],
		    'igDt' => $TOOL['datumTolIgSelect']['igDt'],
		    'tipus' => $TOOL['datumTolIgSelect']['napTipusok'],
		    'napokSzama' => $TOOL['datumTolIgSelect']['napokSzama'],
		)
	    );
	} else {
	    $tolDt = $TOOL['datumTolIgSelect']['tolDt']; $igDt = $TOOL['datumTolIgSelect']['igDt'];
	    initTolIgDt($TOOL['datumTolIgSelect']['tanev'], $tolDt, $igDt, $TOOL['datumTolIgSelect']['override']);
	    $TOOL['datumTolIgSelect']['tolDt'] = $tolDt; $TOOL['datumTolIgSelect']['igDt'] = $igDt;

	    if (!isset($TOOL['datumTolIgSelect']['hanyNaponta']) || $TOOL['datumTolIgSelect']['hanyNaponta']=='' )
	    $TOOL['datumTolIgSelect']['hanyNaponta'] = 1;

	};

	if (!isset($TOOL['datumTolIgSelect']['tolParamName']) || $TOOL['datumTolIgSelect']['tolParamName']=='' )
	$TOOL['datumTolIgSelect']['tolParamName'] = 'tolDt';
	if (!isset($TOOL['datumTolIgSelect']['igParamName']) || $TOOL['datumTolIgSelect']['igParamName']=='' )
	$TOOL['datumTolIgSelect']['igParamName'] = 'igDt';

}

function getOraSelect() {

	global $TOOL, $tanev;

	if (!isset($TOOL['oraSelect']['tol']) || $TOOL['oraSelect']['tol'] == '' )
	$TOOL['oraSelect']['tol'] = getMinOra();
	if (!isset($TOOL['oraSelect']['ig']) || $TOOL['oraSelect']['ig'] == '' )
	$TOOL['oraSelect']['ig'] = getMaxOra();

	if (!isset($TOOL['oraSelect']['paramName']) || $TOOL['oraSelect']['paramName']=='' )
	$TOOL['oraSelect']['paramName'] = 'ora';

}

function getTeremSelect() {

	global $TOOL;

	$telephelyId = $TOOL['teremSelect']['telephelyId'];
	if (!is_array($TOOL['teremSelect']['termek']))	$TOOL['teremSelect']['termek'] = getTermek(array('telephelyId' => $telephelyId));
	if (!isset($TOOL['teremSelect']['paramName']) || $TOOL['teremSelect']['paramName']=='' )
	    $TOOL['teremSelect']['paramName'] = 'teremId';

}

function getKepzesSelect() {

    global $TOOL;

    $TOOL['kepzesSelect']['kepzes'] = getKepzesek();
    if (!is_array($TOOL['kepzesSelect']['kepzes']) || count($TOOL['kepzesSelect']['kepzes']) == 0) {
	unset($TOOL['kepzesSelect']);
    } else {
	if ( !isset($TOOL['kepzesSelect']['paramName']) || $TOOL['kepzesSelect']['paramName'] == '' )
	    $TOOL['kepzesSelect']['paramName'] = 'kepzesId';
    }
}

function getKerdoivSelect() {

	global $TOOL;

	if (!is_array($TOOL['kerdoivSelect']['kerdoiv'])) $TOOL['kerdoivSelect']['kerdoiv'] = getKerdoiv();
	if ( !isset($TOOL['kerdoivSelect']['paramName']) || $TOOL['kerdoivSelect']['paramName'] == '' )
	    $TOOL['kerdoivSelect']['paramName'] = 'kerdoivId';

}

function getSzuloSelect() {

    global $TOOL;

    $TOOL['szuloSelect']['szulo'] = getSzulok(array('result' => 'indexed'));
    if (!is_array($TOOL['szuloSelect']['szulo']) || count($TOOL['szuloSelect']['szulo']) == 0) {
	unset($TOOL['szuloSelect']);
    } else {
	if ( !isset($TOOL['szuloSelect']['paramName']) || $TOOL['szuloSelect']['paramName'] == '' )
	    $TOOL['szuloSelect']['paramName'] = 'szuloId';
    }
}


/* TANEV FÜGGŐK */

function getOrarendiHetSelect() {

	global $TOOL;
	$TOOL['orarendiHetSelect']['hetek'] = getOrarendiHetek($TOOL['orarendiHetSelect']); // tolDt, igDt, tanev
	if ( !isset($TOOL['orarendiHetSelect']['paramName']) || $TOOL['orarendiHetSelect']['paramName']=='' )
	    $TOOL['orarendiHetSelect']['paramName'] = 'het';

}

function getTanarOraLapozo() {

	global $TOOL;
	global $tanarId,$tolDt,$igDt,$oraId;
	$_X = $TOOL['tanarOraLapozo']['orak'] = getTanarOrak(
	    $tanarId,array('tolDt' => $tolDt, 'igDt' => $igDt, 'tipus' => array('normál','normál máskor','helyettesítés','felügyelet','összevonás'))
	);
	for ($i = 0; $i < count($_X); $i++) {
	    if ($_X[$i]['oraId'] == $oraId) {
		$TOOL['tanarOraLapozo']['oraAdat'] = $_X[$i];
		if (is_array($_X[($i-1)])) $TOOL['tanarOraLapozo']['elozo'] = $_X[$i-1];
		if (is_array($_X[($i+1)])) $TOOL['tanarOraLapozo']['kovetkezo'] = $_X[$i+1];
		break;
	    }
	}
	if ( !isset($TOOL['tanarOraLapozo']['paramName']) || $TOOL['tanarOraLapozo']['paramName']=='' )
	    $TOOL['tanarOraLapozo']['paramName'] = 'oraId';

}

function getIgazolasOsszegzo() {

    global $TOOL;
    global $diakId;
    global $_TANEV;
    if ($diakId!='') {
	$TOOL['igazolasOsszegzo']['igazolasok'] = getIgazolasSzam($diakId);
	$_T = getDiakHianyzasOsszesites(array($diakId),$_TANEV);
	$TOOL['igazolasOsszegzo']['hianyzasok'] = $_T[$diakId];
	$TOOL['kretaIgazolasOsszegzo'] =  getKretaIgazolasOsszegzo($diakId);
    }

}

function getZaradekSelect() {

	global $TOOL;

	if (!is_array($TOOL['zaradekSelect']['zaradekok'])) $TOOL['zaradekSelect']['zaradekok'] = getZaradekok();

	if (!isset($TOOL['zaradekSelect']['paramName']) || $TOOL['zaradekSelect']['paramName']=='' )
	    $TOOL['zaradekSelect']['paramName'] = 'zaradekIndex';

}

function getKerelemStat() {
	global $TOOL;
	//$TOOL['kerelemStat']['stat'] = getKerelemOsszesito();
}

//function getTelephelySelect() {
//    global $TOOL;
//    if (!is_array($TOOL['telephelySelect']['telephelyek'])) {
//	$TOOL['telephelySelect']['telephelyek'] = getTelephely();
//    }
//
//}

function getVissza() {
    global $TOOL;
    if ($TOOL['vissza']['icon']=='') { $TOOL['vissza']['icon'] = 'arrow-left'; } // default, egyelőre csak a 'vissza' típusnál használjuk
}

?>
