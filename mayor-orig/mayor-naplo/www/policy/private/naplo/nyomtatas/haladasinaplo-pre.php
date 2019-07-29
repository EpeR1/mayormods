<?php

if (_RIGHTS_OK !== true) die();

if (!__NAPLOADMIN && !__VEZETOSEG && !__TITKARSAG) {
    $_SESSION['alert'][] = 'page:insufficient_access';
} else {

    $ADAT['tanev'] = $tanev = __TANEV;
    $ADAT['tanevAdat'] = $_TANEV;
    $tolDt = $ADAT['tanevAdat']['kezdesDt'];
    $igDt = $ADAT['tanevAdat']['zarasDt'];
    // Mert hétfőtől, vagy csütörtöktől kezdődik a nyomtatott napló!!!
//$tolDt='2011-11-31';
    if (date('w',strtotime($tolDt)) > 4 || date('w',strtotime($tolDt))==0) $tolDt=date('Y-m-d',strtotime('LAST Thursday',strtotime($tolDt)));
    elseif (date('w',strtotime($tolDt))!=1) $tolDt=date('Y-m-d',strtotime('LAST MONDAY',strtotime($tolDt)));

    if (isset($_POST['osztalyId']) && $_POST['osztalyId'] != '') $osztalyId = $_POST['osztalyId'];

    require_once('include/modules/naplo/share/osztaly.php');

    if (isset($osztalyId)) {

	require_once('include/modules/naplo/share/intezmenyek.php');
	require_once('include/modules/naplo/share/diak.php');
	require_once('include/modules/naplo/share/tanar.php');
	require_once('include/modules/naplo/share/orarend.php');
	require_once('include/modules/naplo/share/ora.php');
	require_once('include/modules/naplo/share/tankor.php');
	require_once('include/modules/naplo/share/targy.php');
	require_once('include/modules/naplo/share/file.php');
	require_once('include/modules/naplo/share/kepzes.php');
	require_once('include/modules/naplo/nyomtatas/tex.php');
	require_once('include/share/date/names.php');
	require_once('include/share/str/hyphen.php');
	require_once('include/share/str/tex.php');


	$ADAT['intezmenyAdat'] = getIntezmenyByRovidnev(__INTEZMENY);
	// Tanárok adatai
	$ADAT['tanarok'] = getTanarok($Param = array('tanev' => $tanev,   'result' => 'assoc'));
	// osztály adatainak lekérdezése
	$ADAT['osztalyAdat'] = getOsztalyAdat($osztalyId);
	$ADAT['munkatervId'] = getMunkatervByOsztalyId($osztalyId);
	$ADAT['nevsor'] = getDiakok(array(
	    'osztalyId' => $osztalyId, 'tanev' => $tanev, 
	    'statusz' => array('jogviszonyban van','magántanuló','egyéni munkarend','jogviszonya felfüggesztve','jogviszonya lezárva')
	));
	$ADAT['tankorok'] = getTankorByOsztalyId($osztalyId, $ADAT['tanev'], array('csakId' => false, 'tanarral' => true));
	$ADAT['naploTankor'] = getNaploTankorei($osztalyId);
	$ADAT['tankorNaploja'] = getTankorokNaploja();
	$ADAT['napok'] = reindex(getTanevNapjai( getMunkatervByOsztalyId($osztalyId) ), array('dt'));
        // osztályok lekérdezése
	$ADAT['osztalyId'] = $osztalyId;
        $ADAT['osztalyok'] = getOsztalyok();
        $ADAT['osztalyJele'] = array();
        for ($i =0; $i < count($ADAT['osztalyok']); $i++) {
	    $ADAT['osztalyJele'][ $ADAT['osztalyok'][$i]['osztalyId'] ] = $ADAT['osztalyok'][$i]['osztalyJel'];
	    if ($osztalyId == $ADAT['osztalyok'][$i]['osztalyId']) {
		$ADAT['osztalyJel'] = $ADAT['osztalyok'][$i]['osztalyJel'];
		$ADAT['ofo'] = $ADAT['osztalyok'][$i]['osztalyfonokNev'];
	    }
	}
	for ($i = 0; $i < count($ADAT['tankorok']); $i++) {
	    $_tankorId = $ADAT['tankorok'][$i]['tankorId'];
            $_osztalyId = $ADAT['tankorNaploja'][$_tankorId];
            if ($_osztalyId!==null) {
                $targyNev .= ' ('.$ADAT['osztalyJele'][$_osztalyId].')';
		$tmp = $ADAT['tankorok'][$i]; $tmp['tanarok'] = array();
		foreach ($ADAT['tankorok'][$i]['tanarok'] as $tanarAdat) $tmp['tanarok'][] = $tanarAdat['tanarNev'];
		$ADAT['tankorokNaploElejere'][] = $tmp;
	    }
	}	
//-----------------------------------------------------------------------/
	$ret = getTargyakByOsztalyId($osztalyId, $tanev);
	$ADAT['magatartasId'] = getMagatartas(array('result' => 'value'));
	$ADAT['szorgalomId'] = getSzorgalom(array('result' => 'value'));
	$ADAT['ofoTargyId'] = getOsztalyfonoki(array('result' => 'value'));
//echo '<pre>'; var_dump($ADAT['ofoTargyId']); echo '</pre>';
	for ($i = 0; $i < count($ret); $i++) {
	    $ADAT['targyAdat'][ $ret[$i]['targyId'] ] = $ret[$i];
	    $szavak = explode(' ', ($huHyphen->hyphen(trim($ret[$i]['targyNev']))));
	    $ADAT['targyAdat'][ $ret[$i]['targyId'] ]['tordeltTargyNev'] = tordel($szavak);
	    $ADAT['targyAdat'][ $ret[$i]['targyId'] ]['tankor'] = array();
	    //if ($ret[$i]['targyNev'] == 'osztályfőnöki') $ADAT['ofoTargyId'] = $ret[$i]['targyId'];
	}
	for ($i = 0; $i < count($ADAT['tankorok']); $i++) {
	    $ADAT['targyAdat'][ $ADAT['tankorok'][$i]['targyId'] ]['db']++;
	    $tankorId = $ADAT['tankorok'][$i]['tankorId'];
	    $targyId = $ADAT['tankorok'][$i]['targyId'];
	    if (is_array($ADAT['naploTankor']) && in_array($tankorId, $ADAT['naploTankor'])) {
//		$ADAT['naploTargyak'][$targyId][] = $tankorId;
		$ADAT['targyAdat'][$targyId]['tankor'][] = $tankorId;
		$ADAT['tankorTargy'][$tankorId] = $targyId;
		if ($ADAT['tankorok'][$i]['targyId'] == $ADAT['ofoTargyId']) $ADAT['ofoTankorId'] = $tankorId;
//		else $ADAT['oszlopTankore'][] = $tankorId;
	    }
    	}
	$ADAT['targyFejlec'] = $ADAT['oszlopTankore'] = array(); $db = 0;
	$ADAT['helyek'] = array(6,15,15,15); $Foglalt = array(0,0,0,0); $lap = 0;
	foreach ($ADAT['targyAdat'] as $targyId => $tAdat) {

	    if (
		count($tAdat['tankor']) == 0
		|| $targyId == $ADAT['ofoTargyId'] 
		|| $targyId == $ADAT['magatartasId']
		|| $targyId == $ADAT['szorgalomId']
	    ) continue;

	    $tDb = count($tAdat['tankor']);
	    $tmp = array('targyId' => $targyId, 'sorsz' => 1);
	    while ($tDb > 0) {
		$szabad = $ADAT['helyek'][$lap] - $Foglalt[$lap];

		$db = min($szabad, $tDb);
		$tmp['db'] = $db;
		$ADAT['targyFejlec'][$lap][] = $tmp;
		$Foglalt[$lap] += $db;
		$tmp['sorsz'] += $db;
		$tDb -= $db;
		if ($ADAT['helyek'][$lap] == $Foglalt[$lap]) $lap++;
	    }
	    // Ha 21-nél több tankör van és nem jön ki pont 21-re a tárgy határ
/*
	    if ($db < 21 && $db + count($tAdat['tankor']) > 21) while ($db < 21) {
		$ADAT['oszlopTankore'][] = '';
		$db++;
	    }
*/
	    for ($i = 0; $i < count($tAdat['tankor']); $i++) {
		$tankorId = $tAdat['tankor'][$i];
		if ($tankorId != $ADAT['ofoTankorId']) {
		    $ADAT['oszlopTankore'][] = $tankorId;
		    $db++;
		}
	    }
	}
	// Tanuló-tankör mátrix
	$ADAT['diakIds'] = array();
	for ($i = 0; $i < count($ADAT['nevsor']); $i++) {
	    $ADAT['diakTankor'][ $ADAT['nevsor'][$i]['diakId'] ] = getTankorByDiakId(
		$ADAT['nevsor'][$i]['diakId'], $tanev,
		array('csakId' => true, 'tolDt' => $tolDt, 'igDt' => $igDt, 'result'=>'', 'jelenlet'=>'' )
	    );
	    $ADAT['diakIds'][] = $ADAT['nevsor'][$i]['diakId'];
	    $ADAT['diakAdat'][ $ADAT['nevsor'][$i]['diakId'] ] = $ADAT['nevsor'][$i];
	}
	// Órák lekérdezése
	getNaploOrak($ADAT);
	getNaploHianyzasok($ADAT);

	// Melyik tárgyhoz mely (és hány) tankörök tartoznak

        $filename = str_replace(' ','','Haladasi_'.date('Ymd').'_'.$ADAT['osztalyAdat']['osztalyJel']);

        $content = ''.

    	    putTeXHaladasiOldalbeallitas().

	    putTeXElolap($ADAT).
    	    putTeXLapdobas().
	    putTeXDefineFootline($ADAT['osztalyJel'], $ADAT['ofo']).
	    putTeXTanuloTankorMatriX($ADAT).
	    putTeXLapdobas().

	    putTeXUresLap().

	    putTeXAllandoFejlec().
	    putTeXOrarendMacro();  // putTechPage1 és putTechPage3-ban kell majd paraméteresen meghívni

	$dt = $tolDt;
	while(strtotime($dt)<=strtotime($igDt)) {
	    $ADAT['tanitasiNapOk'] = array();
	    $vanOra = false; // Ha a három nap egyikén sincs óra akkor ne rakjuk ki ezeket a napokat...
    	    for ($i = 0; $i < 3; $i++) {
            	$ADAT['tanitasiNapOk'][$i] = date('Y-m-d',strtotime('+'.$i.' days',strtotime($dt)));
		if (count($ADAT['orak'][ $ADAT['tanitasiNapOk'][$i] ])) $vanOra = true;
    	    }
	    if ($vanOra) {
		$content .=
            	    putTeXPage1($ADAT).
            	    putTeXLapdobas().
            	    putTeXPage2($ADAT).
            	    putTeXLapdobas().
		    '';

		if (count($ADAT['oszlopTankore']) > 21)
		    $content .=
            		putTeXPage34($ADAT).
            		putTeXLapdobas();
	    }
    	    $dt = date('Y-m-d',strtotime('+3 days',strtotime($dt)));
    	    if (date('w', strtotime($dt)) == 0) $dt = date('Y-m-d', strtotime('+1 day', strtotime($dt))); // == vasárnap...
	}


        $content .= endTeXDocument();

	$filename = fileNameNormal($filename);
        if (!defined('_TEX_ERROR') && generatePDF($filename, _DOWNLOADDIR.'/private/nyomtatas/haladasi', $content, __NYOMTATAS_FUZETKENT === true)) {
    	    header('Location: '.location('index.php?page=session&f=download&download=true&dir=nyomtatas/haladasi&file='.$filename.'.pdf'));
        }

    }

    $TOOL['osztalySelect'] = array('tipus' => 'cella','paramName' => 'osztalyId', 'post' => array());
    getToolParameters();

}


?>
