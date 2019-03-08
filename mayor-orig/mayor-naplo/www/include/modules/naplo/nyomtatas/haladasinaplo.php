<?php
/*
    Module: naplo
*/

    function getTankorokNaploja() {

        $q = "SELECT tankorId,osztalyId FROM tankorNaplo";
        return db_query($q, array('fv' => 'tankorokNaploja', 'modul' => 'naplo', 'result' => 'keyvaluepair'));

    }

    function getNaploTankorei($osztalyId) {

	$q = "SELECT tankorId FROM tankorNaplo WHERE osztalyId=%u";
	return db_query($q, array('fv' => 'getNaploTankorei', 'modul' => 'naplo', 'result' => 'idonly', 'values' => array($osztalyId)));

    }

    function getNaploOrak(&$ADAT) {


	$q = "SELECT * FROM ora WHERE tankorId IN (".implode(',', array_fill(0, count($ADAT['naploTankor']), '%u')).") 
		AND '%s'<=dt AND dt<='%s' ORDER BY dt,ora,tankorId";
	$v = mayor_array_join($ADAT['naploTankor'], array($ADAT['tanevAdat']['kezdesDt'], $ADAT['tanevAdat']['zarasDt']));
	$A = db_query($q, array('fv' => 'getNaploOrak', 'modul' => 'naplo', 'result' => 'indexed', 'values' => $v));
	$Oraszam = $oraIds = array();
	for ($i = 0; $i < count($A); $i++) {

	    $ADAT['orak'][ $A[$i]['dt'] ][ $A[$i]['ora'] ][] = $A[$i];
	    $oraIds[] = $A[$i]['oraId']; $oraId2dt[ $A[$i]['oraId'] ] = array('dt' => $A[$i]['dt'], 'ora' => $A[$i]['ora']);
            if (
		!in_array($A[$i]['tipus'], array('elmarad','elmarad máskor')) // Lehet olyan elmaradó óra, amihez van beírva tananyag...
		&& (
            	    !is_array($ADAT['tananyag'][ $A[$i]['dt'] ][ $A[$i]['tankorId'] ])
            	    || !in_array($A[$i]['leiras'], $ADAT['tananyag'][ $A[$i]['dt'] ][ $A[$i]['tankorId'] ])
		)
            ) $ADAT['tananyag'][ $A[$i]['dt'] ][ $A[$i]['tankorId'] ][] = $A[$i]['leiras'];

    	    if (defined('__ORASZAMOT_NOVELO_TIPUSOK')) {
        	$oraszamNoveloTipus = explode(',', __ORASZAMOT_NOVELO_TIPUSOK);
    	    } else {
        	$_SESSION['alert'][] = 'info:missing_constant:__ORASZAMOT_NOVELO_TIPUSOK';
        	$oraszamNoveloTipus = array('normál', 'normál máskor', 'helyettesítés', 'összevonás');
    	    }
	    if (in_array($A[$i]['tipus'], $oraszamNoveloTipus)) {
		$ADAT['oraszam'][ $A[$i]['dt'] ][ $A[$i]['tankorId'] ][] = (++$Oraszam[ $A[$i]['tankorId'] ]);
	    } else {
		$ADAT['oraszam'][ $A[$i]['dt'] ][ $A[$i]['tankorId'] ][] = '---';
	    }
	    if (in_array($A[$i]['tipus'], array('helyettesítés', 'összevonás', 'felügyelet'))) {
		$ADAT['helyettesites'][ $A[$i]['dt'] ][] = $A[$i];
	    }

	}
	$tmp = getOralatogatasByOraIds($oraIds, $SET = array('result' => 'assoc'));;
	if (is_array($tmp)) foreach ($tmp as $oraId => $olAdat) {
	    $olAdat['ora'] = $oraId2dt[$oraId]['ora'];
	    $ADAT['oralatogatas'][ $oraId2dt[$oraId]['dt'] ][] = $olAdat;
	} 
    }

    function getNaploHianyzasok(&$ADAT) {

	global $HianyzasJeloles;

	$q = "SELECT * FROM hianyzas WHERE '%s'<dt AND dt<'%s' 
		AND diakId IN (".implode(',', array_fill(0, count($ADAT['diakIds']), '%u')).") AND tipus IN ('hiányzás','késés')
		ORDER BY dt,ora,diakId";
	$v = mayor_array_join(array($ADAT['tanevAdat']['kezdesDt'], $ADAT['tanevAdat']['zarasDt']), $ADAT['diakIds']);
	$A = db_query($q, array('fv' => 'getNaploHianyzas', 'modul' => 'naplo', 'result' => 'indexed', 'values' => $v));
	for ($i = 0; $i < count($A); $i++) {
	    $diakId = $A[$i]['diakId'];
	    $dt = $A[$i]['dt'];
	    $ora = $A[$i]['ora'];
	    if (!is_array($ADAT['hianyzas'][$dt][$diakId])) {
		$nev = $ADAT['diakAdat'][$diakId]['diakNev'];
		if (mb_strlen($nev, 'UTF-8') > 20) {
 		    if (mb_substr($nev,20,1) == ' ') {
			$nev = mb_substr($nev, 0, 20, 'UTF-8');
		    } else {
			$nev = mb_substr($nev, 0, 20, 'UTF-8');
			$pos = mb_strrpos($nev, ' ', 0, 'UTF-8');
			if ($pos !== false) $nev = mb_substr($nev, 0, $pos, 'UTF-8');
			else $nev .= '.';
		    }
		}
		$ADAT['hianyzas'][$dt][$diakId] = array('ora' => array(), 'diakNev' => $nev);
	    }
	    $ADAT['hianyzas'][$dt][$diakId]['ora'][$ora] = $HianyzasJeloles[$A[$i]['tipus']];
	    if ($A[$i]['tipus'] == 'hiányzás') {
		$ADAT['hianyzas'][$dt][$diakId]['összesen']++;
		$ADAT['hianyzas'][$dt][$diakId][ $A[$i]['statusz'] ]++;
	    }
	}

    }

    function tordel($szavak) {

        $sorok = array(); // A tárgyNev sorokra bontása
        $maxHossz = 8; // egy sorba írható karakterek maximális száma

        for ($j = 0; $j < count($szavak); $j++) {
            $szo = str_replace('--', '~-', $szavak[$j]);
            $tagok = explode('-', $szo);
            $sor = str_replace('~', '-', $tagok[0]);
                for ($k = 1; $k < count($tagok); $k++) {
                    $tag = str_replace('~', '-', $tagok[$k]);
                    $tl = mb_strlen($tag, 'UTF-8');
                    $sl = mb_strlen($sor,'UTF-8');
                    $ct = count($tagok);
                    if (
                        (
                            $sl+$tl < $maxHossz // általában max $maxHossz karaktert engedünk meg
                            && !(
                                $k == ($ct - 2) // az utolsó előtti tag
                                && ($sl > 3) // a sor már elég hosszú
                                && ($tl + mb_strlen($targok[$k+1], 'UTF-8')) <= $maxHossz // és befér az utolsó sorba --> akkor hagyjuk a következő sorba
                            )
                        )
                        || (
                            $sl+$tl == $maxHossz
                            && ($k == ($ct-1) || substr($tag,-1) == '-')) // szóvégén, vagy kötőjeles szó kötőjelénél nincs újabb kötőjel
                    ) {
                        $sor .= $tag;
                    } else {
                        if ($k < $ct && substr($sor, -1) != '-') $sorok[] = $sor.'-'; // ha nem az utolsó és nincs még kötőjel (kötőjeles szavak)
                        else $sorok[] = $sor;
                        $sor = $tag;
                    }
                }
            $sorok[] = $sor;
        }
	return $sorok;

    }
			    
?>
