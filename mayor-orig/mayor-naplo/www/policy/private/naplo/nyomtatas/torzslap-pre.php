<?php

    if (_RIGHTS_OK !== true) die();
    if (!__NAPLOADMIN && !__VEZETOSEG && !__TANAR && !__TITKARSAG) {
	$_SESSION['alert'][] = 'page:insufficient_access';
    } else {


/*
2012/20. EMMI rendelet
99. § (1) A törzslap két részből áll, a tanulókról külön-külön kiállított egyéni törzslapokból és az egyéni törzslapok összefűzését szolgáló borítóból (törzslap külív). Az iskola a tanulókról - a tanévkezdést követő harminc napon belül - egyéni törzslapot állít ki.
(2) Ha az iskolai nevelés és oktatás nyelve a nemzetiség nyelve, a törzslapot magyar nyelven és az oktatás nyelvén is vezetni kell. Ha a két szöveg között eltérés van, és nem állapítható meg, hogy melyik a helyes szöveg, a magyar nyelvi bejegyzést kell hitelesként elfogadni.
(3) Az egyéni törzslap tartalmazza
+a) a törzslap sorszámát,
+b) a tanuló nevét,
     állampolgárságát,
     nem magyar
	 állampolgár esetén a tartózkodás jogcímét,
	 a jogszerű tartózkodást megalapozó okirat számát,
+     oktatási azonosító számát,
+     születési helyét és idejét,
+     anyja születéskori nevét,
+c) a tanuló osztálynaplóban szereplő sorszámát,
-+d) a tanévet és a tanuló által elvégzett évfolyamot,
e) a tanuló magatartásának és szorgalmának értékelését,
f) a tanuló által tanult tantárgyakat, és ezek év végi szöveges minősítését,
g) a közösségi szolgálat teljesítésével kapcsolatos adatokat,
h) az összes mulasztott óra számát, külön-külön megadva az igazolt és igazolatlan mulasztásokat,
i) a nevelőtestület határozatát,
j) a tanulmányok alatti vizsgára vonatkozó adatokat,
k) a tanulót érintő gyermekvédelmi intézkedéssel, hátrányos helyzet, halmozottan hátrányos helyzet megállapításával kapcsolatos 
és tanulói jogviszonyából következő
     döntéseket, határozatokat, záradékokat.
(4) Ha az iskola sajátos nevelési igényű tanuló nevelés-oktatását is ellátja, a törzslapon fel kell tüntetni 
    a szakvéleményt kiállító szakértői bizottság nevét, 
    címét, 
    a szakvélemény számát és 
    kiállításának keltét, 
    a felülvizsgálat időpontját.
(5) Az egyéni törzslapokat az alsó tagozat, a felső tagozat és a középfokú iskolai tanulmányok befejezését követően, a törzslap külívének teljes lezárása után 
szétválaszthatatlanul össze kell fűzni, és ilyen módon kell tárolni.
*/

	require_once('include/modules/naplo/share/osztaly.php');
	require_once('include/modules/naplo/share/osztalyModifier.php');
	require_once('include/modules/naplo/share/intezmenyek.php');
	require_once('include/modules/naplo/share/targy.php');
	require_once('include/modules/naplo/share/kepzes.php');
	require_once('include/modules/naplo/share/diak.php');
	require_once('include/modules/naplo/share/szulo.php');
	require_once('include/modules/naplo/share/szemeszter.php');
	require_once('include/modules/naplo/share/hianyzas.php');
	require_once('include/modules/naplo/share/szovegesErtekeles.php');
	require_once('include/share/date/names.php');
	require_once('include/share/print/pdf.php');
	require_once('include/modules/naplo/share/file.php');
	require_once('include/modules/naplo/share/zaradek.php');
	require_once('include/modules/naplo/share/osztalyzatok.php');
	require_once('include/modules/naplo/share/bejegyzes.php');
	require_once('include/modules/naplo/share/nap.php');

	// Ez a függvény hova való??
	function getDiakTargyOraszam($diakId, $osztalyId, $tanev, $ADAT) {
	    $utolsoTanitasiNap = getOsztalyUtolsoTanitasiNap($osztalyId);
            // éves óraszámok lekérdezése - tárgyanként
            $q = "SELECT targyId,oraszam FROM tankorDiak LEFT JOIN tankorSzemeszter USING (tankorId) LEFT JOIN tankor USING (tankorId)
                    WHERE diakId=%u AND tanev=%u AND beDt<='%s' AND (kiDt IS NULL OR '%s'<=kiDt)";
            $v = array($diakId, $tanev, $utolsoTanitasiNap, $utolsoTanitasiNap);
            $jres = db_query($q, array(
                'fv' => 'getDiakBizonyitvany/óraszám', 'modul' => 'naplo_intezmeny', 'result' => 'multiassoc', 'keyfield' => 'targyId', 'values' => $v
            ));

            $szDb = $ADAT['szemeszter']['tanevAdat']['maxSzemeszter']; // Feltételezzük, hogy a szemeszterek számozása 1-től indul és folyamatos
            foreach ($jres as $targyId => $tAdat) {
                $oraszam = 0;
                for ($i = 0; $i < count($tAdat); $i++) {
                    $oraszam += $tAdat[$i]['oraszam'];
                }
                $ret[$targyId]['hetiOraszam'] = $oraszam / $szDb;
                /*
                    A TANITASI_HETEK_SZAMA a diák (egyik) osztályához rendelt munkaterv alapján van meghatározva - így
                    csak az aktuális tanévben (__TANEV) van értelme. Ha több osztálya is van a tanulónak, akkor problémás...
                */
                if (defined('TANITASI_HETEK_SZAMA')) $ret[$targyId]['evesOraszam'] = $oraszam / $szDb * TANITASI_HETEK_SZAMA;
            }
	    return $ret;
	}


	$ADAT['magatartasIds'] = getMagatartas();
        $ADAT['szorgalomIds']= getSzorgalom();

	// A dátum, osztály és diákok kiválasztása
        $ADAT['szemeszterId'] = $szemeszterId = readVariable($_POST['szemeszterId'], 'numeric unsigned', null);
	if (isset($szemeszterId)) { // szemesztert záró értékelés - intézményi adatbázis
            $ADAT['szemeszter'] = getSzemeszterAdatById($ADAT['szemeszterId']);
            $ADAT['dt'] = $dt = $ADAT['szemeszter']['zarasDt'];
	    define('__ZARO_SZEMESZTER', $ADAT['szemeszter']['szemeszter'] == $ADAT['szemeszter']['tanevAdat']['maxSzemeszter']); //??
	    $ADAT['tanev'] = $tanev = $ADAT['szemeszter']['tanev'];
	}
	$ADAT['osztalyId'] = $osztalyId = readVariable($_POST['osztalyId'], 'numeric unsigned', null);
	$ADAT['targySorrendNev'] = $sorrendNev = readVariable($_POST['sorrendNev'], 'enum', 'anyakönyv', array('napló','bizonyítvány','anyakönyv','ellenőrző','egyedi'));
	// Ha egy diák van kiválasztva...
	$diakId = readVariable($_POST['diakId'], 'numeric unsigned', null, $diakIds);

	if (isset($osztalyId) && isset($szemeszterId)) {
	    define('TANITASI_HETEK_SZAMA', getTanitasiHetekSzama(array('osztalyId'=>$osztalyId)));
	    $diakIds = array();
	    $ADAT['osztalyAdat'] = getOsztalyAdat($osztalyId, $tanev);
	    $ADAT['evfolyamJel'] = getEvfolyamJel($osztalyId, $tanev);
	    $ADAT['file'] = fileNameNormal('torzslap-'.str_replace('.', '', $ADAT['osztalyAdat']['osztalyJel']));
	    $Diakok = getDiakok(array('osztalyId' => $osztalyId, 'tanev' => $tanev, 'tolDt' => $ADAT['szemeszter']['tanevAdat']['kezdesDt'], 'igDt' => $ADAT['szemeszter']['tanevAdat']['zarasDt'], 
		'statusz'=>array('jogviszonyban van','magántanuló','egyéni munkarend','vendégtanuló','jogviszonya felfüggesztve','jogviszonya lezárva'), 
		'extraAttrs'=>'oId as oktatasiAzonosito,torzslapSzam as torzslapszam,allampolgarsag,szuletesiHely,szuletesiIdo,anyaId')
	    );
	    osztalyTorzslapszamGeneralas($osztalyId);
	    $dTsz = getDiakokTorzslapszamaByOsztalyId($osztalyId);
	    if (is_array($Diakok)) for ($i = 0; $i < count($Diakok); $i++) {
		if (!isset($diakId) || $diakId == $Diakok[$i]['diakId']) {
		    $diakIds[] = $Diakok[$i]['diakId'];
		    $Diakok[$i]['anyaNev'] = getSzuloNevById($Diakok[$i]['anyaId'], $szuleteskori = true);
		    $Diakok[$i]['evfolyamJel'] = $ADAT['evfolyamJel']; // kell ez?? // getEvfolyamJel($osztalyId, $ADAT['tanev']);
		    $Diakok[$i]['szuletesiOrszag'] = ''; // Ezzel mi legyen??
		    $Diakok[$i]['feljegyzesek'] = ''; // Egyedi törzslap záradékok kellenek ide, semmi más!
		    $ADAT['diakAdat'][$Diakok[$i]['diakId']] = $Diakok[$i];
		    $ADAT['diakAdat'][$Diakok[$i]['diakId']]['szuletesiIdo'] = dateToString($Diakok[$i]['szuletesiIdo']);
		    $ADAT['diakAdat'][$Diakok[$i]['diakId']]['torzslapszam'] = $dTsz[$Diakok[$i]['diakId']];
		}
	    }
	    $kovetkezoTanev = __TANEV+1; $kovTA = getTanevAdat($kovetkezoTanev);
	    $ADAT['diakZaradekok'] = getZaradekokByDiakIds($diakIds, array('tolDt'=>$_TANEV['kezdesDt'], 'igDt'=>$kovTA['kezdesDt'], 'tipus'=>'törzslap feljegyzés','keyfield'=>'diakId','result'=>'multiassoc')); // kell a tol-ig szűrés??
	    $ADAT['diakZaradekok'] = getZaradekokByDiakIds($diakIds, array('tolDt'=>$_TANEV['kezdesDt'], 'igDt'=>$kovTA['kezdesDt'], 'dokumentum'=>'törzslap','keyfield'=>'diakId','result'=>'multiassoc')); // kell a tol-ig szűrés??
	    $ADAT['diakBejegyzesek'] = getTorzslapBejegyzesByDiakIds($diakIds, array('tanev' => $tanev));

	    $ADAT['diakIds'] = $diakIds;

	    if (count($ADAT['diakIds']) > 0) {
		$ADAT['hianyzas'] = getDiakHianyzasOsszesites($ADAT['diakIds'], $ADAT['szemeszter']);
		$ADAT['targyak'] = getTargyakByDiakIds($ADAT['diakIds'], $ADAT['szemeszter'], $osztalyId, $sorrendNev);
                $ADAT['jegyek'] = getDiakZarojegyekByEvfolyamJel($ADAT['diakIds'], $ADAT['evfolyamJel'], $ADAT['szemeszter'], array('felevivel'=>true)); // TODO: ellenőrzés

		foreach ($ADAT['jegyek'] as $diakId => $dJegyek) {
		    $ADAT['targyOraszam'][$diakId] = getDiakTargyOraszam($diakId, $osztalyId, $tanev, $ADAT);
		}

		$printFile = torzslapNyomtatvanyKeszites($ADAT); // ???
    		$printFile = fileNameNormal($printFile);
		if ($printFile !== false && file_exists(_DOWNLOADDIR."/$policy/$page/$sub/$f/$printFile"))
    		    header('Location: '.location('index.php?page=session&f=download&download=true&dir=naplo/nyomtatas/torzslap&file='.$printFile));
	    }
	} // van osztály és szemeszter


        $TOOL['osztalySelect'] = array('tipus' => 'cella', 'paramName' => 'osztalyId', 'post' => array('szemeszterId', 'tolDt', 'dt','sorrendNev'));
        if (isset($osztalyId))
    	    $TOOL['diakSelect'] = array('tipus' => 'cella', 'paramName' => 'diakId', 'diakok' => $Diakok, 'post' => array('szemeszterId', 'osztalyId', 'tolDt', 'dt','sorrendNev'));
        $TOOL['szemeszterSelect'] = array(
                'tipus'=>'cella','paramName' => 'szemeszterId', 'statusz' => array('aktív','lezárt') ,
            'post' => array('osztalyId', 'tanarId', 'diakId', 'tolDt', 'dt', 'tankorId', 'kepzesId', 'evfolyamJel','sorrendNev')
        );
	$TOOL['targySorrendSelect'] = array('tipus'=>'cella','paramName' => 'sorrendNev', 'post' => array('szemeszterId', 'osztalyId','diakId','tolDt','dt','tankorId','kepzesId','evfolyamJel'));
	getToolParameters();
    }

?>
