<?php

    if (_RIGHTS_OK !== true) die();
    if (!__NAPLOADMIN && !__TITKARSAG && !__TANAR) {

	$_SESSION['alert'][] = 'page:insufficient_access';

    } else {

	require_once('include/modules/naplo/share/osztaly.php');
	require_once('include/modules/naplo/share/diak.php');
	require_once('include/modules/naplo/share/file.php');
	require_once('include/modules/naplo/share/szulo.php');
	require_once('include/modules/naplo/share/intezmenyek.php');
	require_once('include/share/date/names.php');
	require_once('include/share/str/tex.php');
	require_once('include/share/print/pdf.php');

	$fields = array(
	    'anyaszuloId','anyanevElotag','anyacsaladinev','anyautonev','anyaszuleteskoricsaladinev','anyaszuleteskoriutonev','anyanem','anyacimOrszag',
	    'anyacimIrsz','anyacimHelyseg','anyacimKozteruletNev','anyacimKozteruletJelleg','anyacimHazszam','anyacimEmelet','anyacimAjto','anyamobil',
	    'anyatelefon','anyaemail','anyauserAccount','anyafoglalkozas','anyamunkahely','anyaszuletesiEv','anyaszuloNev','anyaStat','anyastatusz',

	    'apaszuloId','apanevElotag','apacsaladinev','apautonev','apaszuleteskoricsaladinev','apaszuleteskoriutonev','apanem','apacimOrszag',
	    'apacimIrsz','apacimHelyseg','apacimKozteruletNev','apacimKozteruletJelleg','apacimHazszam','apacimEmelet','apacimAjto','apamobil',
	    'apatelefon','apaemail','apauserAccount','apafoglalkozas','apamunkahely','apaszuletesiEv','apaszuloNev','apaStat','apastatusz',

	    'gondviseloszuloId','gondviselonevElotag','gondviselocsaladinev','gondviseloutonev','gondviseloszuleteskoricsaladinev','gondviseloszuleteskoriutonev','gondviselonem','gondviselocimOrszag',
	    'gondviselocimIrsz','gondviselocimHelyseg','gondviselocimKozteruletNev','gondviselocimKozteruletJelleg','gondviselocimHazszam','gondviselocimEmelet','gondviselocimAjto','gondviselomobil',
	    'gondviselotelefon','gondviseloemail','gondviselouserAccount','gondviselofoglalkozas','gondviselomunkahely','gondviseloszuletesiEv','gondviseloszuloNev','gondviseloStat','gondviselostatusz',

	    'neveloszuloId','nevelonevElotag','nevelocsaladinev','neveloutonev','neveloszuleteskoricsaladinev','neveloszuleteskoriutonev','nevelonem','nevelocimOrszag',
	    'nevelocimIrsz','nevelocimHelyseg','nevelocimKozteruletNev','nevelocimKozteruletJelleg','nevelocimHazszam','nevelocimEmelet','nevelocimAjto','nevelomobil',
	    'nevelotelefon','neveloemail','nevelouserAccount','nevelofoglalkozas','nevelomunkahely','neveloszuletesiEv','neveloszuloNev','neveloStat','nevelostatusz',
	);
	$ADAT['base'] = array_fill_keys($fields, '');

	$tanev = readVariable($_POST['tanev'], 'numeric unsigned', defined('__TANEV')?__TANEV:null );
	if ($tanev != __TANEV) $TA = getTanevAdat($tanev);
	else $TA = $_TANEV;
	$diakId = readVariable($_POST['diakId'],'id');
	$osztalyId = readVariable($_POST['osztalyId'],'id');

	$ADAT['szocialisHelyzet'] = getSetField('naplo_intezmeny', 'diak', 'szocialisHelyzet');
	$ADAT['penzugyiStatusz'] = getEnumField('naplo_intezmeny', 'diak', 'penzugyiStatusz');
	$ADAT['fogyatekossag'] = getSetField('naplo_intezmeny', 'diak', 'fogyatekossag');
	$ADAT['kozteruletJelleg'] = getEnumField('naplo_intezmeny', 'diak', 'lakhelyKozteruletJelleg');

	if (isset($diakId)) {
	    $ADAT['base']['diak'] = array($diakId);
	} elseif (isset($osztalyId)) {
	    $_DIAKOK = getDiakok(array('osztalyId'=>$osztalyId,'tanev'=>$tanev,'result'=>'csakId')); // Ennek a függvénynek a szerkezete nyáron megváltozott!
	    for ($i=0; $i<count($_DIAKOK); $i++)
		$ADAT['base']['diak'][] = $_DIAKOK[$i]['diakId'];
	}
	list($ADAT['base']['ev'],$ADAT['base']['honap'],$ADAT['base']['nap']) = explode('-', date('Y-m-d'));
	$intezmeny = getIntezmenyByRovidnev(__INTEZMENY);
	foreach ($intezmeny as $attr => $value) $ADAT['base']['intezmeny'.$attr] = $value;
	$Szulok = getSzulok();
	$Osztalyok = getOsztalyok($tanev, array('result' => 'assoc'));
	$ADAT['base']['hoNev'] = kisbetus($Honapok[ $ADAT['base']['honap'] - 1 ]);
	$ADAT['file'] = 'diakAdatlap';

	if (is_array($ADAT['base']['diak'])) 
	for ($i=0; $i<count($ADAT['base']['diak']); $i++) {
	    $diakId = $ADAT['base']['diak'][$i];
	    $diakAdat = getDiakAdatById($diakId);

	    // Lekérdezzük a diák tényleges jogviszony adatait...
	    $DJ = getDiakJogviszony($diakId);
	    $diakAdat['jogviszony'] = array();
	    // Összes bejegyzés ("ciklussal" iratható ki)
	    foreach ($DJ as $key => $jAdat) $diakAdat['jogviszony'][] = array('dt' => dateToString($jAdat['dt']), 'statusz' => $jAdat['statusz']);
	    // Max. 5 bejegyzés (külön-külön, fix 10 db mező)
	    for ($j = 0; $j < 5; $j++) $diakAdat['jvDt'.$j] = $diakAdat['jvStat'.$j] = '';
	    foreach ($DJ as $key => $jAdat) {
		$diakAdat['jvDt'.$key] = dateToString($jAdat['dt']);
		$diakAdat['jvStat'.$key] = $jAdat['statusz'];
	    }
	    foreach ($diakAdat as $attr => $value) if (!is_array($value)) $diakAdat[$attr] = LaTeXSpecialChars($value);
	    list($diakAdat['szuletesiEv'],$diakAdat['szuletesiHonap'],$diakAdat['szuletesiNap']) = explode('-', $diakAdat['szuletesiIdo']);
	    $diakAdat['szuletesiHoNev'] = kisbetus($Honapok[ $diakAdat['szuletesiHonap'] - 1 ]);
	    list($diakAdat['jogviszonyKEv'],$diakAdat['jogviszonyKHonap'],$diakAdat['jogviszonyKNap']) = explode('-', $diakAdat['jogviszonyKezdete']);
	    $diakAdat['jogviszonyKHoNev'] = kisbetus($Honapok[ $diakAdat['jogviszonyKHonap'] - 1 ]);
	    list($diakAdat['jogviszonyVEv'],$diakAdat['jogviszonyVHonap'],$diakAdat['jogviszonyVNap']) = explode('-', $diakAdat['jogviszonyVege']);
	    $diakAdat['jogviszonyVHoNev'] = kisbetus($Honapok[ $diakAdat['jogviszonyVHonap'] - 1 ]);
	    $diakAdat['torvenyesKepviselo'] = str_replace(',', ', ', $diakAdat['torvenyesKepviselo']);
	    $diakAdat['anyaNev'] = $Szulok[ $diakAdat['anyaId'] ]['szuleteskoriCsaladinev']?
                trim(implode(' ', array(
                    $Szulok[ $diakAdat['anyaId'] ]['szuleteskoriNevElotag'],
                    $Szulok[ $diakAdat['anyaId'] ]['szuleteskoriCsaladinev'],
                    $Szulok[ $diakAdat['anyaId'] ]['szuleteskoriUtonev']
                ))):$Szulok[ $diakAdat['anyaId'] ]['szuloNev'];
	    foreach (array('anya','apa','gondviselo','nevelo') as $szt) { // szt=szuloTipus
		$szulo = $Szulok[ $diakAdat[$szt.'Id'] ];
		if (is_array($szulo)) {
		    foreach ($szulo as $attr => $value) $diakAdat[$szt.$attr] = LaTeXSpecialChars($value);
		    if ($diakAdat[$szt.'statusz']=='elhunyt') $diakAdat[$szt.'Stat'] = '\dag';
		    elseif ($diakAdat[$szt.'statusz']=='házas') $diakAdat[$szt.'Stat'] = '$\infty$';
		    elseif ($diakAdat[$szt.'statusz']=='egyedülálló') $diakAdat[$szt.'Stat'] = '$\odot$';
		    elseif ($diakAdat[$szt.'statusz']=='hajadon / nőtlen') $diakAdat[$szt.'Stat'] = '$\oslash$';
		    elseif ($diakAdat[$szt.'statusz']=='elvált') $diakAdat[$szt.'Stat'] = '$\triangleleft\ominus\triangleright$';
		    elseif ($diakAdat[$szt.'statusz']=='özvegy') $diakAdat[$szt.'Stat'] = '$\oplus$';
		    elseif ($diakAdat[$szt.'statusz']=='élettársi kapcsolatban él') $diakAdat[$szt.'Stat'] = '$\circ\circ$';
		    else $diakAdat[$szt.'Stat'] = $diakAdat[$szt.'statusz'].'';
		}
	    }
	    $diakAdat['fogyatekossag'] = str_replace(',',', ',$diakAdat['fogyatekossag']);
	    $diakAdat['szocialisHelyzet'] = str_replace(',',', ',$diakAdat['szocialisHelyzet']);
            $diakAdat['osztaly'] = getDiakOsztalya($diakId, array('tanev'=>$tanev));
	    $diakAdat['osztalyJel'] = $Osztalyok[ $diakAdat['osztaly'][0]['osztalyId'] ]['kezdoTanev'].'-'.
		($Osztalyok[ $diakAdat['osztaly'][0]['osztalyId'] ]['vegzoTanev']+1).'/'.nagybetus($Osztalyok[ $diakAdat['osztaly'][0]['osztalyId'] ]['jel']);

	    $ADAT['diak'][ $diakId ] = $diakAdat;

	}
	if (count($ADAT['diak']) > 0) {
        	$printFile = fileNameNormal(nyomtatvanyKeszites($ADAT));
        	if ($printFile !== false && file_exists(_DOWNLOADDIR."/$policy/$page/$sub/$f/$printFile"))
            	    header('Location: '.location("index.php?page=session&f=download&download=true&dir=$page/$sub/$f/&file=$printFile"));
    	}


	// ToolBar
	$TOOL['tanevSelect'] = array('tipus' => 'cella', 'action' => 'tanevValasztas', 'post' => array('tanev','diakId'));
	$TOOL['osztalySelect'] = array('tipus' => 'cella', 'tanev' => $tanev, 'post' => array('tanev'));
	$TOOL['diakSelect'] = array('tipus'=>'cella', 'tanev'=>$tanev, 'osztalyId' => $osztalyId,
	    'statusz' => array('jogviszonyban van','vendégtanuló','magántanuló','egyéni munkarend','jogviszonya felfüggesztve','jogviszonya lezárva'),
	    'post' => array('tanev','osztalyId')
	);
	getToolParameters();

    }

?>
