<?php

/* EZ NEM CSAK OSZTALYOZO!!!!!!! */

    if (_RIGHTS_OK !== true) die();
    
    define('_TIME', strtotime(date('Y-m-d')));

    if (!defined('__SHOWSTATZARASMINDENTARGY')) define('__SHOWSTATZARASMINDENTARGY',false);

    if (
	!__NAPLOADMIN && !__VEZETOSEG && !__TANAR && !__TITKARSAG && !__DIAK
    ) {
	$_SESSION['alert'][] = 'page:insufficient_access';
    } else {
    
	require_once('include/share/date/names.php');
	require_once('include/modules/naplo/share/intezmenyek.php');
	require_once('include/modules/naplo/share/osztaly.php');
	require_once('include/modules/naplo/share/hianyzas.php');
	require_once('include/modules/naplo/share/szemeszter.php');
	require_once('include/modules/naplo/share/osztalyzatok.php');
	require_once('include/modules/naplo/share/jegy.php');
	require_once('include/modules/naplo/share/diak.php');
	require_once('include/modules/naplo/share/kepzes.php');
	require_once('include/modules/naplo/share/targy.php');
	require_once('include/modules/naplo/share/file.php');
	require_once('include/modules/naplo/share/nap.php');
        require_once('include/modules/naplo/share/zaroJegyModifier.php');
        require_once('include/modules/naplo/share/zaradek.php');

	$ADAT['magatartasTargyIdk'] = getMagatartas();
	$ADAT['szorgalomTargyIdk'] = getSzorgalom();

	$ADAT['beallitasok']['targyak'] = readVariable($_POST['beallitasok_targyak'],'bool',true);
	$ADAT['beallitasok']['oraszamok'] = readVariable($_POST['beallitasok_oraszamok'],'bool',true);
	$ADAT['beallitasok']['zaradek'] = readVariable($_POST['beallitasok_zaradek'],'bool',false);

	// melyik szemeszter adatait nézzük
	if (isset($_POST['szemeszterId']) && $_POST['szemeszterId'] != '') {
	    $szemeszterId = readVariable($_POST['szemeszterId'],'id');
	} elseif (!isset($_POST['szemeszterId'])) {
	    for ($i = 1; $i <= count($_TANEV['szemeszter']); $i++) {
		if (
		    strtotime($_TANEV['szemeszter'][$i]['kezdesDt']) <= _TIME
		    && strtotime($_TANEV['szemeszter'][$i]['zarasDt']) >= _TIME
		) {
		    $_POST['szemeszterId'] = $szemeszterId = $_TANEV['szemeszter'][$i]['szemeszterId'];
		    break;
		}
	    }
	}
        if (isset($_POST['sorrendNev']) && $_POST['sorrendNev'] != '')  
	    $ADAT['sorrendNev'] = $sorrendNev = readVariable($_POST['sorrendNev'], 'emptystringnull', '', getTargySorrendNevek(__TANEV));
//	$ADAT['telephelyId'] = $telephelyId = readVariable($_POST['telephelyId'], 'id',__TELEPHELYID);

        $ADAT['telephelyAdat'] = getTelephelyek(array('result' => 'assoc', 'keyfield' => 'telephelyId'));
        $ADAT['telephelyIds'] = array_keys($ADAT['telephelyAdat']);
        $ADAT['telephelyId'] = $telephelyId = readVariable($_GET['telephelyId'], 'id', readVariable(
                $_POST['telephelyId'], 'id', (isset($_POST['telephelyId'])?null:readVariable(__TELEPHELYID,'id')), $ADAT['telephelyIds']
            ), $ADAT['telephelyIds']);
	
    	if (!__DIAK) {
	    if (isset($_POST['osztalyId']) && $_POST['osztalyId'] != '') { $osztalyId = $ADAT['osztalyId'] = readVariable($_POST['osztalyId'],'id'); }
	    elseif (__OSZTALYFONOK && !isset($_POST['osztalyId'])) { $osztalyId = $ADAT['osztalyId'] = $_POST['osztalyId'] = $_OSZTALYA[0]; }
	} // diák, szülő csak az iskola statisztikát látja

	if (isset($szemeszterId)) {

	    $ADAT['szemeszterAdat'] = getSzemeszterAdatById($szemeszterId);
	    $Osztalyok = getOsztalyok($ADAT['szemeszterAdat']['tanev'],array('result' => 'indexed', 'minden'=>false, 'telephelyId' => $telephelyId));
	    if (isset($osztalyId)) {
		define('TANITASI_HETEK_SZAMA', getTanitasiHetekSzama(array('osztalyId'=>$osztalyId)));
		define('__OSZTALYFONOKE', (__OSZTALYFONOK === true && in_array($osztalyId, $_OSZTALYA)));
		$ADAT['evfolyam'] = getEvfolyam($osztalyId, $ADAT['szemeszterAdat']['tanev']);
		$ADAT['evfolyamJel'] = getEvfolyamJel($osztalyId, $ADAT['szemeszterAdat']['tanev']);
		$ADAT['kovetkezoEvfolyamJel'] = getKovetkezoEvfolyamJel($ADAT['evfolyamJel']);
		$ADAT['utolsoTanitasiNap'] = getOsztalyUtolsoTanitasiNap($osztalyId, $ADAT['szemeszterAdat']['tanev']);

		// magatartás és szorgalom jegyek beírásának jogosultsága
		if ($ADAT['szemeszterAdat']['statusz'] != 'aktív') {
		    define('_BEIRHATO', false);
		} else {
		    $time = time();
                    // Keresünk bitonyítvány írás időszakot
                    foreach ($ADAT['szemeszterAdat']['idoszak'] as $i => $idoszakAdat) {
                        if (
                            $idoszakAdat['tipus'] == 'bizonyítvány írás'
                            && strtotime($idoszakAdat['tolDt']) <= $time
                            && $time <= strtotime($idoszakAdat['igDt'])
                        ) {
                            $idoszak = $idoszakAdat;
                            break;
                        }
                    }

		    if (__NAPLOADMIN) define('_BEIRHATO', true);
		    elseif (__VEZETOSEG) define('_BEIRHATO', (isset($idoszak['tolDt'])) );
		    elseif (__OSZTALYFONOKE) define('_BEIRHATO', (isset($idoszak['tolDt'])) );
		    else define('_BEIRHATO', false);

		    if ((__VEZETOSEG || __OSZTALYFONOKE) && !isset($idoszak['tolDt'])) $_SESSION['alert'][] = 'info:idoszak_bizir_nincs';

		}
		/* ---- action ---- */

		if (_BEIRHATO && $action == 'jegyLezaras') {

			$zaroJegyek = $_POST['zaroJegy'];
                        if (is_array($zaroJegyek)) {
                            /* Prepare */
                            for($i=0; $i<count($zaroJegyek); $i++) {
                                $X = explode('|',$zaroJegyek[$i]);
                                for ($j=0; $j<count($X); $j++) {
                                    list($key,$value) = explode('=',$X[$j]);
                                    $beirandoJegyek[$i][$key] = $value;
                                }

                            }
                    	    zaroJegyBeiras($beirandoJegyek);
                        }
			$zaradekAdat = $_POST['zaradekAdat'];
			if (is_array($zaradekAdat)) {
			    foreach ($zaradekAdat as $zAdat) {
				if ($zAdat != '') {
				    list($_diakId, $_zaradekIndex, $zaradekId, $csereStr) = explode('/', $zAdat); // --TODO per jel hibás lehet!
				    if ($zaradekId == '') $zaradekId = null;
				    if (in_array($_zaradekIndex, array_values($ZaradekIndex['konferencia bukás']))) $csere = array('%évfolyam%' => $ADAT['evfolyamJel'].".");
				    else $csere = array('%évfolyam%' => ($ADAT['kovetkezoEvfolyamJel']).".", '%évfolyam betűvel%' => ($_EVFOLYAMJEL_BETUVEL[ $ADAT['kovetkezoEvfolyamJel'] ]).".");
				    $csere['%tantárgy%'] = str_replace('=',', ',$csereStr); // TODO, és-re cserélhetjük vessző helyett
				    zaradekRogzites(array('diakId'=>$_diakId, 'zaradekId' => $zaradekId, 'zaradekIndex'=>$_zaradekIndex, 'dt'=>$ADAT['utolsoTanitasiNap'], 'csere' => $csere));
				}
			    }
			}

		}

		/* ---- action vége ---- */

		// osztály statisztikák
		$ADAT['osztaly'] = getOsztalyAdat($osztalyId, $ADAT['szemeszterAdat']['tanev']);

		if (!isset($ADAT['osztalyId'])) $ADAT['osztalyId'] = $ADAT['osztaly'][0]['osztalyId'];
		// Az adott szemeszterben létezik-e az osztály
		for ($i = 0; ($i < count($Osztalyok) && $Osztalyok[$i]['osztalyId'] != $osztalyId); $i++);
		if ($i < count($Osztalyok)) {
		    $ADAT['diakok'] = getDiakok(array(
			'osztalyId' => $osztalyId, 'tanev' => $ADAT['szemeszterAdat']['tanev'],
			'tolDt' => $ADAT['szemeszterAdat']['tanevAdat']['kezdesDt'], 'igDt' => $ADAT['szemeszterAdat']['zarasDt'], // A tanév kezdetétől a szemeszter végéig
			'statusz' => array('jogviszonyban van','magántanuló','egyéni munkarend','jogviszonya felfüggesztve','jogviszonya lezárva')
		    ));
		    $ADAT['zaraskoriDiakIds'] = getDiakok(array(
			'osztalyId' => $osztalyId, 'tanev' => $ADAT['szemeszterAdat']['tanev'],
			'tolDt' => $ADAT['szemeszterAdat']['zarasDt'], 'igDt' => $ADAT['szemeszterAdat']['zarasDt'],
			'statusz' => array('jogviszonyban van','magántanuló','egyéni munkarend'), 'result' => 'idonly'
//			'statusz' => array('jogviszonyban van','magántanuló','jogviszonya felfüggesztve','jogviszonya lezárva'), 'result' => 'idonly'
		    ));
		    for ($i = 0; $i < count($ADAT['diakok']); $i++) $ADAT['diakIds'][] = $ADAT['diakok'][$i]['diakId'];
		    $ADAT['targyak'] = getTargyakByDiakIds($ADAT['diakIds'], $ADAT['szemeszterAdat'], $osztalyId, $sorrendNev); // TODO
		    $targyIds = array($ADAT['magatartasTargyIdk'][0], $ADAT['szorgalomTargyIdk'][0]);
		    if (is_array($ADAT['targyak'])) foreach ($ADAT['targyak'] as $index => $tAdat) $targyIds[] = $tAdat['targyId'];
		    //$ADAT['targyAdat'] = getTargyAdatByIds($ADAT['targyak']);
		    $ADAT['targyAdat'] = getTargyAdatByIds($targyIds);
		    $ADAT['tanarok'] = getTanarokByDiakIds($ADAT['diakIds'], $ADAT['szemeszterAdat']);
		    $ADAT['evkoziJegyAtlag'] = getDiakJegyAtlagok($ADAT['diakIds']); // évközi!
		    $ADAT['jegyek'] = getDiakZarojegyekByEvfolyamJel($ADAT['diakIds'], $ADAT['evfolyamJel'], $ADAT['szemeszterAdat'], array('felevivel'=>true)); // TODO: ellenőrzés: evfolyam-->evfolyamJel
		    $ADAT['atlagok'] = getDiakZarojegyAtlagok($ADAT['zaraskoriDiakIds'], $ADAT['szemeszterAdat']['tanev'], $ADAT['szemeszterAdat']['szemeszter']);
		    $ADAT['tantargyiAtlagok'] = getTargyZarojegyAtlagok($ADAT['zaraskoriDiakIds'], $ADAT['szemeszterAdat']['tanev'], $ADAT['szemeszterAdat']['szemeszter']);
		    $ADAT['hianyzas'] = getDiakHianyzasOsszesites($ADAT['diakIds'], $ADAT['szemeszterAdat']);
		    $ADAT['zaradekok'] = getDiakKonferenciaZaradekok($ADAT['diakIds'], $ADAT['utolsoTanitasiNap']);
		    $ADAT['adhatoZaradekok'] = getZaradekokByTipus('konferencia, konferencia bukás');

		    //foreach ($ADAT['hianyzas'] as $diakId => $hianyzasAdat) {
		    for ($i=0; $i<count($ADAT['diakIds']); $i++) {
			$diakId = intval($ADAT['diakIds'][$i]);
			$hianyzasAdat = $ADAT['hianyzas'][$diakId];
			// A hozott hiányzások kezelése átkerült a központi getDiakHianyzasOsszesites
			//$HOZOTT = getDiakHozottHianyzas($diakId,array('tanev'=>$tanev));

//2013NKT            		if (_KESESI_IDOK_OSSZEADODNAK===true)
                	    $ADAT['hianyzas'][$diakId]['igazolatlan'] 
				= $hianyzasAdat['igazolatlan'] 
				= floor($hianyzasAdat['kesesPercOsszeg']/45)+intval($hianyzasAdat['igazolatlan']);

			//$ADAT['hianyzas'][$diakId]['igazolatlan'] += intval($HOZOTT['igazolatlan']['db']);
			//$ADAT['hianyzas'][$diakId]['igazolt'] += intval($HOZOTT['igazolt']['db']);

			if (in_array($diakId, $ADAT['zaraskoriDiakIds'])) { // Csak a záráskori névsort vesszük figyelembe
			    $ADAT['stat']['igazolt'] += $hianyzasAdat['igazolt'];
			    $ADAT['stat']['igazolatlan'] += $hianyzasAdat['igazolatlan'];
			    //$ADAT['stat']['igazolatlan'] += intval($HOZOTT['igazolatlan']['db']);
			    //$ADAT['stat']['igazolt'] += intval($HOZOTT['igazolt']['db']);
			    if ($hianyzasAdat['igazolatlan'] >= 10) $ADAT['stat']['tiznel tobb']++;
			    elseif ($hianyzasAdat['igazolatlan'] >= 5) $ADAT['stat']['otnel tobb']++;
			    elseif ($hianyzasAdat['igazolatlan'] == 0) $ADAT['stat']['nincs']++;
			}
		    }
		    // Diákok statisztikai adatai
		    //jegyek tömb: [diakId][targyId][$INDEX!!!]
// -----------------------------------------------------------------
		    $utolsoTanitasiNap = getOsztalyUtolsoTanitasiNap($osztalyId);
		    for ($i=0; $i<count($ADAT['diakIds']); $i++) {
			$diakId = intval($ADAT['diakIds'][$i]);
        		// éves óraszámok lekérdezése - tárgyanként - másolva a biz oldalról
        		$q = "SELECT targyId,oraszam FROM tankorDiak LEFT JOIN tankorSzemeszter USING (tankorId) LEFT JOIN tankor USING (tankorId) WHERE diakId=%u AND tanev=%u AND beDt<='%s' AND (kiDt IS NULL OR '%s'<=kiDt)";
        		$v = array($diakId, $ADAT['szemeszterAdat']['tanev'], $utolsoTanitasiNap, $utolsoTanitasiNap);
        		$jres = db_query($q, array('fv' => 'getDiakBizonyitvany/óraszám', 'modul' => 'naplo_intezmeny', 'result' => 'multiassoc', 'keyfield' => 'targyId', 'values' => $v));
        		$szDb = $ADAT['szemeszterAdat']['tanevAdat']['maxSzemeszter']; // Feltételezzük, hogy a szemeszterek számozása 1-től indul és folyamatos
        		foreach ($jres as $targyId => $tAdat) {
            		    $oraszam = 0;
            		    for ($j = 0; $j < count($tAdat); $j++) {
                		$oraszam += $tAdat[$j]['oraszam'];
            		    }
            		    $ADAT['targyOraszam'][$diakId][$targyId]['hetiOraszam'] = $oraszam / $szDb;
            		    if (defined('TANITASI_HETEK_SZAMA')) $ADAT['targyOraszam'][$diakId][$targyId]['evesOraszam'] = $oraszam / $szDb * TANITASI_HETEK_SZAMA;
        		}
		    }
// ------------------------------------------------------------------
		    $_tmp=array();
		    if (is_array($ADAT['jegyek']))
		    foreach ($ADAT['jegyek'] as $diakId => $jegyek) {
			$zaraskorTag = in_array($diakId, $ADAT['zaraskoriDiakIds']);
			$atlag = floatval($ADAT['atlagok'][$diakId]);
			// kitűnők száma
			if ($atlag >= _KITUNO_ATLAG) {
			    if ($zaraskorTag) $ADAT['stat']['kituno']++;
			    $ADAT['diakAdat'][$diakId]['kituno'] = true;
			// jelesek száma
			} elseif ($atlag >= _JELES_ATLAG) {
			    reset($jegyek);
			    $found = false;
			    while (list($key, $jegyAdatok) = each($jegyek)) {
				for ($i=0; $i<count($jegyAdatok); $i++)  {
				    if ($jegyAdatok[$i]['jegyTipus'] == 'jegy' && $jegyAdatok[$i]['jegy'] < _JELES_LEGGYENGEBB_JEGY) $found = true;
				}
			    }
			    if ($found===false) {
				if ($zaraskorTag) $ADAT['stat']['jeles']++;
				$ADAT['diakAdat'][$diakId]['jeles'] = true;
			    }
			}
			// bukottak és bukások száma
			foreach ($jegyek as $targyId => $jegyAdatok) {
			    $ADAT['targyboljegy'][$targyId] = true;
			    for ($i=0; $i<count($jegyAdatok); $i++) {
				$jegyAdat = $jegyAdatok[$i];
				if ((intval($jegyAdat['jegy'])==1 && in_array($jegyAdat['jegyTipus'],array('jegy','féljegy')))) {
				    if ($ADAT['diakAdat'][$diakId]['bukott'] != true) {
					$ADAT['diakAdat'][$diakId]['bukott'] = true;
					if ($zaraskorTag) $ADAT['stat']['bukott']++;
				    }
				    if ($zaraskorTag) {
					$_tmp[$diakId]++;
					$ADAT['stat']['bukas']++;
				    }
				    // záradékhoz (2015)
 				    $ADAT['diakAdat'][$diakId]['bukottTargy'][] = $ADAT['targyAdat'][$targyId]['targyNev'];
				} 
				if (isset($jegyAdat['megjegyzes']) && $zaraskorTag) {
				    $ADAT['stat'][ $jegyAdat['megjegyzes'] ]++;
				    if (in_array($jegyAdat['jegyTipus'],array('jegy','féljegy'))) {
					$ADAT['jegyEloszlas'][$targyId][$jegyAdat['jegy']]++;
					if (!in_array($targyId,array_merge($ADAT['szorgalomTargyIdk'],$ADAT['magatartasTargyIdk'])) && $zaraskorTag) $ADAT['jegyEloszlas']['osszes'][$jegyAdat['jegy']]++;
				    }
				}
			    }
			}

		    }
		    foreach ($_tmp as $_dbBukottTargy) {
			    if ($_dbBukottTargy<=2) $ADAT['stat']['dbBukott'][$_dbBukottTargy]++;
			    else $ADAT['stat']['dbBukott'][3]++;
		    }

// ------------------------------------------------------------------
		} else {
		    unset($osztalyId);
		}

	    }

	    if (!isset($osztalyId)) { // lehet, hogy az előzőben lett "törölve" az osztalyId
		// iskolai statisztika
		$ADAT['osztaly'] = getOsztalyok($ADAT['szemeszterAdat']['tanev'],array('result' => 'indexed', 'minden'=>false, 'telephelyId' => $telephelyId));
		$ADAT['targyak'] = getTargyakBySzemeszter($ADAT['szemeszterAdat']); // nem kéne minden tárgy?
		$ADAT['mindenTargy'] = getTargyak(array('targySorrendNev' => $sorrendNev) );
		$ADAT['jegyek'] = getZarojegyStatBySzemeszter($ADAT['szemeszterAdat'],array('telephelyId'=>$telephelyId));
		$ADAT['tantargyiAtlagok'] = getTargyAtlagokBySzemeszter($ADAT['szemeszterAdat']);
		$ADAT['hianyzas'] = getOsztalyHianyzasOsszesites($ADAT['szemeszterAdat']);
		$ADAT['vizsgaSzint'] = getEnumField('naplo_intezmeny','diakNyelvvizsga','vizsgaSzint');
		$ADAT['nyelvvizsgak'] = getNyelvvizsgak(array('igDt'=>$ADAT['szemeszterAdat']['zarasDt']));
		$intezmeny_lr = db_connect('naplo_intezmeny');
		for ($x=0; $x<count($ADAT['nyelvvizsgak']); $x++) {
		    $_diakId = $ADAT['nyelvvizsgak'][$x]['diakId'];
		    $ADAT['nyelvvizsgak'][$x]['osztalyAdat'] = getDiakOsztalya($_diakId, array('tanev'=>__TANEV,'tolDt'=>$ADAT['szemeszterAdat']['kezdesDt'],'igDt'=>$ADAT['szemeszterAdat']['zarasDt']), $intezmeny_lr);
		    $ADAT['nyelvvizsgak'][$x]['osztalyId'] = $ADAT['nyelvvizsgak'][$x]['osztalyAdat'][0]['osztalyId'];
		}
		db_close($intezmeny_lr);
		$ADAT['nyelvvizsgak'] = reindex($ADAT['nyelvvizsgak'],array('targyId','osztalyId','vizsgaSzint'));
		//select * from osztalyJelleg where osztalyJellegNev like '%nyelvi%';
    		$ADAT['nyekJellegu'] = array(36,46,53,54,63,76);
	    }

	    $ADAT['targyTargy'] = getTargyTargy();

	}

	$TOOL['telephelySelect'] = array('tipus' => 'cella','paramName'=>'telephelyId');
	$TOOL['szemeszterSelect'] = array('tipus'=>'cella','paramName' => 'szemeszterId', 'statusz' => array('aktív','lezárt') , 'post' => array('sorrendNev', 'osztalyId', 'telephelyId'));
	if (!__DIAK) {
	    $TOOL['osztalySelect'] = array('tipus'=>'cella','paramName' => 'osztalyId', 'tanev' => $ADAT['szemeszterAdat']['tanev'], 'post' => array('szemeszterId', 'sorrendNev', 'telephelyId'));
	}
	$TOOL['targySorrendSelect'] = array('tipus'=>'cella','paramName' => 'sorrendNev', 'post' => array('szemeszterId', 'osztalyId', 'telephelyId'));
	if (isset($osztalyId) && isset($szemeszterId))
	    $TOOL['nyomtatasGomb'] = array('titleConst' => '_NYOMTATAS','tipus'=>'cella', 'url'=>'index.php?page=naplo&sub=nyomtatas&f=ertesito','post' => array('osztalyId','szemeszterId','sorrendNev', 'telephelyId'));
	getToolParameters();
    
    }
?>
