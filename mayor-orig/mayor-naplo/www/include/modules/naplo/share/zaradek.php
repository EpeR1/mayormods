<?php

    function zaradekRogzites($ADAT, $olr='') {
	/**
	 * $ADAT:
	 *	csere - array(mit => mire...)
	 *	zaradekId - a lecserélendő záradék (optional)
	 *	diakId, dt, zaradekIndex (require)
	**/

	global $Zaradek;

	$lr = ($olr=='') ? db_connect('naplo_intezmeny') : $olr;
	if (!is_array($ADAT['csere'])) $ADAT['csere'] = array();

	if (isset($ADAT['zaradekId'])) {
	    $q = "UPDATE zaradek SET diakId=%u,dt='%s',sorszam='%s',dokumentum='%s',szoveg='%s',zaradekIndex=%u WHERE zaradekId=%u";
	    $v = array(
		$ADAT['diakId'], $ADAT['dt'], $Zaradek[$ADAT['zaradekIndex']]['sorszam'], 
		str_replace(', ', ',', $Zaradek[$ADAT['zaradekIndex']]['dokumentum']),
		str_replace(array_keys($ADAT['csere']), array_values($ADAT['csere']), $Zaradek[$ADAT['zaradekIndex']]['szoveg']),
		$ADAT['zaradekIndex'], $ADAT['zaradekId']
	    );
	    $ret = db_query($q, array('modul' => 'naplo_intezmeny', 'fv' => 'zaradekRogzites', 'values' => $v), $lr);
	} else {
	    $q = "INSERT INTO zaradek (diakId, dt, sorszam, dokumentum, szoveg, zaradekIndex,iktatoszam) VALUES
		    (%u, '%s', '%s', '%s', '%s', %u,'%s')";
	    $v = array(
		$ADAT['diakId'], $ADAT['dt'], $Zaradek[$ADAT['zaradekIndex']]['sorszam'], 
		str_replace(', ', ',', $Zaradek[$ADAT['zaradekIndex']]['dokumentum']),
		str_replace(array_keys($ADAT['csere']), array_values($ADAT['csere']), $Zaradek[$ADAT['zaradekIndex']]['szoveg']),
		$ADAT['zaradekIndex'], $ADAT['iktatoszam']
	    );
	    $ret = db_query($q, array('modul' => 'naplo_intezmeny', 'fv' => 'zaradekRogzites', 'result' => 'insert', 'values' => $v), $lr);
	}

	if ($olr=='') db_close($lr);
	return $ret;
    }

    function getZaradekok() {
	global $Zaradek;
	return $Zaradek;
    }

    function getDiakZaradekok($diakId, $SET = array('tolDt' => null, 'igDt' => null, 'dokumentum' => null, 'tipus' => null, 'result' => 'indexed', 'keyfield' => null)) {

	global $Zaradek, $ZaradekIndex;

	$dokumentum = readVariable($SET['dokumentum'], 'enum', null, array('beírási napló','osztálynapló','törzslap','bizonyítvány'));
	$tolDt = readVariable($SET['tolDt'], 'datetime', '1940-01-01');
	$igDt = readVariable($SET['igDt'], 'datetime', '2050-01-01');
	$tipus = readVariable($SET['tipus'], 'enum', null, array_keys($ZaradekIndex));
	if ($SET['result']=='') $SET['result'] = 'indexed';	
	
	$q = "SELECT zaradekId,diakId,dt,sorszam,dokumentum,IF(iktatoszam!='',CONCAT(szoveg,' (',iktatoszam,')'),szoveg) AS szoveg,zaradekIndex,iktatoszam FROM zaradek WHERE diakId=%u AND '%s' <= dt AND dt <= '%s'";
	$v = array($diakId, $tolDt, $igDt);
	if (isset($dokumentum)) {
	    $q .= " AND dokumentum LIKE '%%%s%%'";
	    $v[] = $dokumentum;
	}
	if (isset($tipus)) {
	    $q .= " AND zaradekIndex IN (".implode(',',array_values($ZaradekIndex[$tipus])).")";
	}
	$q .= " ORDER BY dt,zaradekId,sorszam";
	return db_query($q, array(
	    'modul' => 'naplo_intezmeny', 'fv' => 'getDiakZaradekok', 'result' => $SET['result'], 'keyfield' => $SET['keyfield'], 'values' => $v)
	);

    }

    function getZaradekokByDiakIds($diakIds, $SET = array('tolDt' => null, 'igDt' => null, 'dokumentum' => null, 'tipus' => null, 'result' => 'indexed', 'keyfield' => null)) {

	global $Zaradek, $ZaradekIndex;

	if (!is_array($diakIds) || count($diakIds) < 1) return false;

	$dokumentum = readVariable($SET['dokumentum'], 'enum', null, array('beírási napló','osztálynapló','törzslap','bizonyítvány'));
	$tolDt = readVariable($SET['tolDt'], 'datetime', '1940-01-01');
	$igDt = readVariable($SET['igDt'], 'datetime', '2050-01-01');
	$tipus = readVariable($SET['tipus'], 'enum', null, array_keys($ZaradekIndex));
	if ($SET['result']=='') { $SET['result'] = 'assoc'; $SET['keyfield'] = 'diakId'; }
	
	$q = "SELECT * FROM zaradek WHERE diakId IN (".implode(',', array_fill(0, count($diakIds), '%u')).") AND '%s' <= dt AND dt <= '%s'";
	$v = $diakIds;
	$v[] = $tolDt; $v[] = $igDt;
	if (isset($dokumentum)) {
	    $q .= " AND dokumentum LIKE '%%%s%%'";
	    $v[] = $dokumentum;
	}
	if (isset($tipus)) {
	    $q .= " AND zaradekIndex IN (".implode(',',array_values($ZaradekIndex[$tipus])).")";
	}
	$q .= " ORDER BY dt,sorszam,zaradekId";
	return db_query($q, array(
	    'modul' => 'naplo_intezmeny', 'fv' => 'getDiakZaradekok', 'result' => $SET['result'], 'keyfield' => $SET['keyfield'], 'values' => $v)
	);

    }

    function getZaradekokByIndexes($zaradekIndexes) {

	global $Zaradek;

	$ret = array();
	if (is_array($zaradekIndexes)) foreach ($zaradekIndexes as $key => $zaradekIndex) {
	    $ret[$zaradekIndex] = $Zaradek[$zaradekIndex];
	}
	return $ret;

    }

    function getZaradekokByTipus($tipus) {
    /**
     *  A tipus paraméter vesszővel elválasztva több típust is tartalmazhat.
    **/
	global $ZaradekIndex;

	foreach (explode(',', $tipus) as $idx => $_tipus) {
	    foreach ($ZaradekIndex[trim($_tipus)] as $key => $zIndex) $zaradekIndexes[] = $zIndex;
	}
	return getZaradekokByIndexes($zaradekIndexes);

    }

    function zaradekTorles($zaradekId) {
	$q = "DELETE FROM zaradek WHERE zaradekId=%u";
	return db_query($q, array('fv'=>'zaradekTorles','modul'=>'naplo_intezmeny','values'=>array($zaradekId)));
    }

if (__TANEV < 2013) {

$Zaradek = array(
1 => array('sorszam' => '1.', 'szoveg' => 'Felvéve a(z) %iskola címe% iskolába.', 'dokumentum' => 'beírási napló, osztálynapló, törzslap, bizonyítvány'),
2 => array('sorszam' => '1.', 'szoveg' => 'Átvéve a(z) %iskola címe% iskolába.', 'dokumentum' => 'beírási napló, osztálynapló, törzslap, bizonyítvány'),
3 => array('sorszam' => '1.', 'szoveg' => 'A(z) %határozat száma% számú határozattal áthelyezve a(z) %iskola címe% iskolába.', 'dokumentum' => 'beírási napló, osztálynapló, törzslap, bizonyítvány'),
4 => array('sorszam' => '1/A', 'szoveg' => 'Az első évfolyam követelményeit nem teljesítette, munkája előkészítőnek minősül, tanulmányait az első évfolyamon folytathatja.', 'dokumentum' => 'beírási napló, osztálynapló, törzslap, bizonyítvány'),
5 => array('sorszam' => '2.', 'szoveg' => 'A %fordítás száma% számú fordítással hitelesített bizonyítvány alapján tanulmányait a(z) %évfolyam betűvel% évfolyamon folytatja.', 'dokumentum' => 'beírási napló, törzslap'),
6 => array('sorszam' => '3.', 'szoveg' => 'Felvette a(z) %iskola címe% iskola.', 'dokumentum' => 'beírási napló, törzslap, osztálynapló'),
7 => array('sorszam' => '4.', 'szoveg' => 'Tanulmányait évfolyamismétléssel kezdheti meg, illetve osztályozó vizsga letételével folytathatja.', 'dokumentum' => 'beírási napló, törzslap, osztálynapló'),
8 => array('sorszam' => '4/A', 'szoveg' => '%Tantárgy% tantárgyból tanulmányait egyéni továbbhaladás szerint végzi.', 'dokumentum' => 'osztálynapló, törzslap, bizonyítvány'),
9 => array('sorszam' => '4/B', 'szoveg' => 'Mentesítve %tantárgy% tantárgyból az értékelés és a minősítés alól.', 'dokumentum' => 'osztálynapló, törzslap, bizonyítvány'),
10 => array('sorszam' => '4/C', 'szoveg' => '%Tantárgy% tantárgy %évfolyamok% évfolyamainak követelményeit egy tanévben teljesítette a következők szerint: %jegyek%', 'dokumentum' => 'osztálynapló, törzslap, bizonyítvány'),
11 => array('sorszam' => '5.', 'szoveg' => 'Egyes tantárgyak tanórai látogatása alól az %tanév jele% tanévben felmentve %felmentés oka% miatt.', 'dokumentum' => 'osztálynapló, törzslap, bizonyítvány)'),
12 => array('sorszam' => '5.', 'szoveg' => 'Egyes tantárgyak tanórai látogatása alól az %tanév jele% tanévben felmentve %felmentés oka% miatt. Osztályozó vizsgát köteles tenni', 'dokumentum' => 'osztálynapló, törzslap, bizonyítvány'),
13 => array('sorszam' => '6.', 'szoveg' => 'Tanulmányait a szülő kérésére (szakértői vélemény alapján) magántanulóként folytatja.', 'dokumentum' => 'osztálynapló, törzslap'),
14 => array('sorszam' => '7.', 'szoveg' => 'Mentesítve a(z) %tantárgyak neve% tantárgy tanulása alól.', 'dokumentum' => 'osztálynapló, törzslap, bizonyítvány'),
15 => array('sorszam' => '8.', 'szoveg' => 'Tanulmányi idejének megrövidítése miatt a(z) %évfolyam% évfolyam tantárgyból osztályozó vizsgát köteles tenni.', 'dokumentum' => 'osztálynapló, törzslap'),
16 => array('sorszam' => '9.', 'szoveg' => 'A(z) évfolyamra megállapított tantervi követelményeket a tanulmányi idő megrövidítésével teljesítette.', 'dokumentum' => 'osztálynapló, törzslap, bizonyítvány'),
17 => array('sorszam' => '10.', 'szoveg' => 'A(z) %tantárgy% tantárgy %ezen óráinak% óráinak látogatása alól felmentve %tólDt%-tól %igDt%-ig.', 'dokumentum' => 'osztálynapló'),
18 => array('sorszam' => '10.', 'szoveg' => 'A(z) %tantárgy% tantárgy %ezen óráinak% óráinak látogatása alól felmentve %tólDt%-tól %igDt%-ig. Osztályozó vizsgát köteles tenni.', 'dokumentum' => 'osztálynapló'),
19 => array('sorszam' => '11.', 'szoveg' => 'Mulasztása miatt nem osztályozható, a nevelőtestület határozata értelmében osztályozó vizsgát tehet.', 'dokumentum' => 'osztálynapló, törzslap'),
20 => array('sorszam' => '12.', 'szoveg' => 'A nevelőtestület határozata: a(z) %évfolyam betűvel% évfolyamba léphet.', 'dokumentum' => 'osztálynapló, törzslap, bizonyítvány'),
21 => array('sorszam' => '12.', 'szoveg' => 'A nevelőtestület határozata: iskolai tanulmányait befejezte, tanulmányait %évfolyam% évfolyamon folytathatja.', 'dokumentum' => 'osztálynapló, törzslap, bizonyítvány'),
22 => array('sorszam' => '13.', 'szoveg' => 'A tanuló az %évfolyam% évfolyam követelményeit egy tanítási évnél hosszabb ideig, %hónap szám% hónap alatt teljesítette.', 'dokumentum' => 'osztálynapló, törzslap'),
23 => array('sorszam' => '14.', 'szoveg' => 'A(z) %tantárgy% tantárgyból javítóvizsgát tehet.', 'dokumentum' => 'osztálynapló, törzslap, bizonyítvány'),
24 => array('sorszam' => '14.', 'szoveg' => 'A javítóvizsgán %tantárgy% tantárgyból %osztályzat% osztályzatot kapott, %évfolyam% évfolyamba léphet.', 'dokumentum' => 'törzslap, bizonyítvány'),
25 => array('sorszam' => '15.', 'szoveg' => 'A(z) %évfolyam% évfolyam követelményeit nem teljesítette, az évfolyamot megismételheti.', 'dokumentum' => 'osztálynapló, törzslap, bizonyítvány'),
26 => array('sorszam' => '15/A', 'szoveg' => 'Az %évfolyam% évfolyamot az 1993. évi LXXIX. törvény 72. §-ának (4) bekezdésében foglaltak alapján megismételte.', 'dokumentum' => 'osztálynapló, törzslap, bizonyítvány'),
27 => array('sorszam' => '16.', 'szoveg' => 'A javítóvizsgán %tantárgy% tantárgyból elégtelen osztályzatot kapott. Évfolyamot ismételni köteles.', 'dokumentum' => 'törzslap, bizonyítvány'),
28 => array('sorszam' => '17.', 'szoveg' => 'A(z) %tantárgy% tantárgyból %dátum%-án osztályozó vizsgát tett.', 'dokumentum' => 'osztálynapló, törzslap'),
29 => array('sorszam' => '18.', 'szoveg' => 'Osztályozó vizsgát tett.', 'dokumentum' => 'törzslap, bizonyítvány'),
30 => array('sorszam' => '19.', 'szoveg' => 'A(z) %tantárgy% tantárgy alól %felmentés oka% okból felmentve.', 'dokumentum' => 'törzslap, bizonyítvány'),
31 => array('sorszam' => '20.', 'szoveg' => 'A(z) %tanóra% tanóra alól %felmentés oka% okból felmentve.', 'dokumentum' => 'törzslap, bizonyítvány'),
32 => array('sorszam' => '21.', 'szoveg' => 'Az osztályozóvizsga letételére %igDt%-ig halasztást kapott.', 'dokumentum' => 'törzslap, bizonyítvány'),
33 => array('sorszam' => '21.', 'szoveg' => 'Az beszámoltatóvizsga letételére %igDt%-ig halasztást kapott.', 'dokumentum' => 'törzslap, bizonyítvány'),
34 => array('sorszam' => '21.', 'szoveg' => 'Az különbözetivizsga letételére %igDt%-ig halasztást kapott.', 'dokumentum' => 'törzslap, bizonyítvány'),
35 => array('sorszam' => '21.', 'szoveg' => 'Az javítóvizsga letételére %igDt%-ig halasztást kapott.', 'dokumentum' => 'törzslap, bizonyítvány'),
36 => array('sorszam' => '22.', 'szoveg' => 'Az osztályozó vizsgát engedéllyel a(z) %iskola% iskolában független vizsgabizottság előtt tette le.', 'dokumentum' => 'törzslap, bizonyítvány'),
37 => array('sorszam' => '22.', 'szoveg' => 'Az javítóvizsgát engedéllyel a(z) %iskola% iskolában független vizsgabizottság előtt tette le.', 'dokumentum' => 'törzslap, bizonyítvány'),
38 => array('sorszam' => '23.', 'szoveg' => 'A(z) %szakképesítés% szakképesítés évfolyamán folytatja tanulmányait.', 'dokumentum' => 'törzslap, bizonyítvány, osztálynapló'),
39 => array('sorszam' => '24.', 'szoveg' => 'Tanulmányait %ok% okból megszakította, a tanulói jogviszonya %igDt%-ig szünetel.', 'dokumentum' => 'beírási napló, törzslap'),
40 => array('sorszam' => '25.', 'szoveg' => 'A tanuló jogviszonya kimaradással megszűnt, a létszámból törölve.', 'dokumentum' => 'beírási napló, törzslap, bizonyítvány, osztálynapló'),
41 => array('sorszam' => '25.', 'szoveg' => 'A tanuló jogviszonya %igazolatlan órák száma% óra igazolatlan mulasztás miatt megszűnt, a létszámból törölve.', 'dokumentum' => 'beírási napló, törzslap, bizonyítvány, osztálynapló'),
42 => array('sorszam' => '25.', 'szoveg' => 'A tanuló jogviszonya egészségügyi alkalmasság miatt megszűnt, a létszámból törölve.', 'dokumentum' => 'beírási napló, törzslap, bizonyítvány, osztálynapló'),
43 => array('sorszam' => '25.', 'szoveg' => 'A tanuló jogviszonya térítési díj fizetési hátralék miatt megszűnt, a létszámból törölve.', 'dokumentum' => 'beírási napló, törzslap, bizonyítvány, osztálynapló'),
44 => array('sorszam' => '25.', 'szoveg' => 'A tanuló jogviszonya tandíj fizetési hátralék miatt megszűnt, a létszámból törölve.', 'dokumentum' => 'beírási napló, törzslap, bizonyítvány, osztálynapló'),
45 => array('sorszam' => '25.', 'szoveg' => 'A tanuló jogviszonya %iskola% iskolába való átvétel miatt megszűnt, a létszámból törölve.', 'dokumentum' => 'beírási napló, törzslap, bizonyítvány, osztálynapló'),
46 => array('sorszam' => '26.', 'szoveg' => '%Fegyelmező intézkedés% fegyelmező intézkedésben részesült.', 'dokumentum' => 'osztálynapló'),
47 => array('sorszam' => '27.', 'szoveg' => '%Fegyelmi büntetés% fegyelmi büntetésben részesült. A büntetés végrehajtása %igDt%-ig felfüggesztve.', 'dokumentum' => ''),
48 => array('sorszam' => '28.', 'szoveg' => 'Tanköteles tanuló igazolatlan mulasztása esetén a tanuló %igazolatlan órák száma% óra igazolatlan mulasztása miatt a szülőt felszólítottam.', 'dokumentum' => 'osztálynapló'),
49 => array('sorszam' => '28.', 'szoveg' => 'Tanköteles tanuló igazolatlan mulasztása esetén a tanuló ismételt %igazolatlan órák száma% óra igazolatlan mulasztása miatt a szülő ellen szabálysértési eljárást kezdeményeztem.', 'dokumentum' => 'beírási napló, törzslap, osztálynapló'),
50 => array('sorszam' => '29.', 'szoveg' => 'Tankötelezettség megszűnt.', 'dokumentum' => 'beírási napló'),
51 => array('sorszam' => '30.', 'szoveg' => 'A %szó% szót %helyesbítés%-ra/re helyesbítettem.', 'dokumentum' => 'törzslap, bizonyítvány'),
52 => array('sorszam' => '30.', 'szoveg' => 'A %szavak% szavakat %helyesbítés%-ra/re helyesbítettem.', 'dokumentum' => 'törzslap, bizonyítvány'),
53 => array('sorszam' => '30.', 'szoveg' => 'A %osztályzat% osztályzatot %helyesbítés%-ra/re helyesbítettem.', 'dokumentum' => 'törzslap, bizonyítvány'),
54 => array('sorszam' => '30.', 'szoveg' => 'A %osztályzatok% osztályzatokat %helyesbítés%-ra/re helyesbítettem.', 'dokumentum' => 'törzslap, bizonyítvány'),
55 => array('sorszam' => '31.', 'szoveg' => 'A bizonyítvány %lap% lapját téves bejegyzés miatt érvénytelenítettem.', 'dokumentum' => 'bizonyítvány'),
56 => array('sorszam' => '32.', 'szoveg' => 'Ezt a póttörzslapot a(z) %ok% következtében elvesztett (megsemmisült) eredeti helyett %adatforrás% adatai (adatok) alapján állítottam ki.', 'dokumentum' => 'Pót. törzslap'),
57 => array('sorszam' => '33.', 'szoveg' => 'Ezt a bizonyítványmásodlatot az elveszett (megsemmisült) eredeti helyett %adatforrás% adatai (adatok) alapján állítottam ki.', 'dokumentum' => 'Pót. törzslap'),
58 => array('sorszam' => '33/A', 'szoveg' => 'A bizonyítványt %kérelmező% kérelmére a %bizonyítványszám% számú bizonyítvány alapján, téves bejegyzés miatt állítottam ki.', 'dokumentum' => 'törzslap, bizonyítvány'),
59 => array('sorszam' => '34.', 'szoveg' => 'Pótbizonyítvány. Igazolom, hogy név %Név%, anyja neve %Anyja neve% a(z) %iskola% iskola %szak% szak (szakmai, speciális osztály, két tanítási nyelvű osztály, tagozat) %évfolyam% évfolyamát a(z) %tanév jele% tanévben eredményesen elvégezte.', 'dokumentum' => 'Pót. bizonyítvány'),
60 => array('sorszam' => '35.', 'szoveg' => 'Az iskola a tanulmányi eredmények bejegyzéséhez, a kiemelkedő tanulmányi eredmények elismeréséhez, a felvételi vizsga eredményeinek bejegyzéséhez %vizsga% vizsga eredményének befejezéséhez vagy egyéb, a záradékok között nem szereplő, a tanulóval kapcsolatos közlés dokumentálásához a záradékokat megfelelően alkalmazhatja, illetve megfelelően záradékot alakíthat ki.', 'dokumentum' => ''),
61 => array('sorszam' => '36.', 'szoveg' => 'Érettségi vizsgát tehet.', 'dokumentum' => 'törzslap, bizonyítvány'),
62 => array('sorszam' => '37.', 'szoveg' => 'Gyakorlati képzésről mulasztását %tólDt%-tól %igDt%-ig pótolhatja.', 'dokumentum' => 'törzslap, bizonyítvány, osztálynapló'),
63 => array('sorszam' => '38.', 'szoveg' => 'Beírtam a %iskola% iskola első osztályába.', 'dokumentum' => ''),
64 => array('sorszam' => '39.', 'szoveg' => 'Ezt a haladási naplót %tanítási napok száma% tanítási nappal lezártam.', 'dokumentum' => 'osztálynapló'),
65 => array('sorszam' => '39.', 'szoveg' => 'Ezt a haladási naplót %tanítási órák száma% tanítási órával lezártam.', 'dokumentum' => 'osztálynapló'),
66 => array('sorszam' => '40.', 'szoveg' => 'Ezt az osztályozó naplót %tanulók száma% azaz %tanulók száma betűvel% osztályozott tanulóval lezártam.', 'dokumentum' => 'osztálynapló'),

67 => array('sorszam' => '1/B', 'szoveg' => 'Felvéve a(z) %iskola címe% %osztály% osztályába.', 'dokumentum' => 'beírási napló, osztálynapló, törzslap, bizonyítvány'),
68 => array('sorszam' => '17/A', 'szoveg' => 'A(z) %tantárgy% tantárgyból %dátum%-án beszámoltatóvizsgát tett.', 'dokumentum' => 'osztálynapló, törzslap'),
69 => array('sorszam' => '17/B', 'szoveg' => 'A(z) %tantárgy% tantárgyból %dátum%-án különbözetivizsgát tett.', 'dokumentum' => 'osztálynapló, törzslap'),
70 => array('sorszam' => '12/A', 'szoveg' => 'A nevelőtestület határozata: iskolai tanulmányait befejezte.', 'dokumentum' => 'osztálynapló, törzslap, bizonyítvány'),
71 => array('sorszam' => '35/D', 'szoveg' => 'A %iktatószám% alapján tanulmányait a %osztály% osztályban folytatja.', 'dokumentum' => 'beírási napló, törzslap'),
72 => array('sorszam' => '16/A', 'szoveg' => 'Az osztályozó vizsgán a %tantárgy% tantárgy követelményeit nem teljesítette - %osztályzat%.', 'dokumentum' => 'törzslap, bizonyítvány'),
73 => array('sorszam' => '16/B', 'szoveg' => 'A beszámoltatóvizsgán a %tantárgy% tantárgy követelményeit nem teljesítette - %osztályzat%.', 'dokumentum' => 'törzslap, bizonyítvány'),
74 => array('sorszam' => '16/C', 'szoveg' => 'A különbözetivizsgán a %tantárgy% tantárgy követelményeit nem teljesítette - %osztályzat%.', 'dokumentum' => 'törzslap, bizonyítvány'),
75 => array('sorszam' => '16/D', 'szoveg' => 'A javítóvizsgán a %tantárgy% tantárgy követelményeit nem teljesítette - %osztályzat%. Évfolyamot ismételni köteles.', 'dokumentum' => 'törzslap, bizonyítvány'),

100 => array('sorszam' => '35/A', 'szoveg' => '%Egyedi osztálynapló záradék%', 'dokumentum' => 'osztálynapló'),
101 => array('sorszam' => '35/B', 'szoveg' => '%Egyedi törzslap záradék%', 'dokumentum' => 'törzslap'),
102 => array('sorszam' => '35/C', 'szoveg' => '%Egyedi bizonyítvány záradék%', 'dokumentum' => 'bizonyítvány'),
103 => array('sorszam' => '35/F', 'szoveg' => 'A(z) %zaradekId% nyilvántartási számú záradékban rögzített felmentést %dt% napon hatályon kívül helyeztem. ', 'dokumentum' => 'osztálynapló, törzslap, bizonyítvány'),
104 => array('sorszam' => '4/C', 'szoveg' => '%miatt% miatt mentesítve a(z) %mi% értékelés(e) és a minősítés(e) alól.', 'dokumentum' => 'osztálynapló, törzslap, bizonyítvány'),
105 => array('sorszam' => '35/E', 'szoveg' => 'A(z) %tankorDiakFelmentesId% nyilvántartási számú felmentést %dt% napon hatályon kívül helyeztem. ', 'dokumentum' => 'osztálynapló, törzslap, bizonyítvány'),
106 => array('sorszam' => '17/C', 'szoveg' => 'A(z) %tantárgy% tantárgyból a(z) %évfolyam% évfolyam anyagából %dátum% napon %osztályzat% eredménnyel osztályozó vizsgát tett.', 'dokumentum' => 'osztálynapló, törzslap'),

107 => array('sorszam' => '25/A', 'szoveg' => 'A tanuló jogviszonya haláleset miatt megszűnt, a létszámból törölve.', 'dokumentum' => 'beírási napló, törzslap, bizonyítvány, osztálynapló'),

);

$ZaradekIndex = array(
    'konferencia' => array(
	// továbbléphet
	'következő évfolyamba léphet' => 20, // %évfolyam betűvel%
	'tanulmányait befejezte' => 70,
	// vizsgázhat
	'mulasztás miatt osztályozóvizsga' => 19,
	'javítóvizsgát tehet' => 23, // %tantárgy%
	// évfolyamot ismételhet
	'előkészítőt végzett' => 4,
	'követelményeket nem teljesítette, évfolyamot ismételhet' => 25, // %évfolyam%
    ),
    'jogviszony' => array(
	'megnyitás' => array(
	    'felvétel' => 1,
	    'felvétel osztályba' => 67,
	    'átvétel' => 2, 
	    'áthelyezés' => 3
	),
	'változás' => array(
	    'magántanuló' => 13, 
	    'felfüggesztés' => 39
	),
	'lezárás' => array(
	    'kimaradás' => 40,
	    'igazolatlan órák' => 41,
	    'egészségügyi alkalmasság' => 42,
	    'térítési díj hátralék' => 43,
	    'tandíj hátralék' => 44,
	    'átvétel' => 45,
	    'haláleset' => 107,
	    'tanulmányait folytathatja' => 21,
	    'tanulmányait befejezte' => 70
	)
    ),
    'vizsga halasztás' => array(
	'osztályozó vizsga' => 32,
	'beszámoltatóvizsga' => 33,
	'különbözetivizsga' => 34,
	'javítóvizsga' => 35,
    ),
    'vizsga' => array(
	'osztályozó vizsga' => 106, // 28 volt
	'osztályozó vizsga bukás' => 72,
	'beszámoltatóvizsga' => 68,
	'beszámoltatóvizsga bukás' => 73,
	'különbözetivizsga' => 69,
	'különbözetivizsga bukás' => 74,
	'javítóvizsga' => 24,
	'javítóvizsga bukás' => 27,
	'javítóvizsga nem teljesített' => 75,
    ),
    'felmentés' => array(
	'értékelés alól' =>9,
	'értékelés és minősítés alól' => 104,
	'óra látogatása alól' => 17,
	'óra látogatása alól osztályozóvizsgával' => 18,
	'tárgy tanulása alól' => 14,
	'törlés' => 105
    ),
    'törzslap feljegyzés' => array(
	'egyedi törzslap záradék' => 101
    )

);

} else { // 20/2012 EMMI rendelet

$Zaradek = array(
1 => array('sorszam' => '1.', 'szoveg' => 'Felvéve a(z) %iskola címe% iskolába.', 
	    'dokumentum' => 'beírási napló, osztálynapló, törzslap, bizonyítvány'),
2 => array('sorszam' => '1.', 'szoveg' => 'Átvéve a(z) %iskola címe% iskolába.', 
	    'dokumentum' => 'beírási napló, osztálynapló, törzslap, bizonyítvány'),
3 => array('sorszam' => '1.', 'szoveg' => 'A(z) %határozat száma% számú határozattal áthelyezve a(z) %iskola címe% iskolába.', 
	    'dokumentum' => 'beírási napló, osztálynapló, törzslap, bizonyítvány'),
4 => array('sorszam' => '1/A', 'szoveg' => 'Az első évfolyam követelményeit nem teljesítette, munkája előkészítőnek minősül, tanulmányait az első évfolyamon folytathatja.', 
	    'dokumentum' => 'beírási napló, osztálynapló, törzslap, bizonyítvány'),
5 => array('sorszam' => '2.', 'szoveg' => 'A %fordítás száma% számú fordítással hitelesített bizonyítvány alapján tanulmányait a(z) %évfolyam betűvel% évfolyamon folytatja.', 
	    'dokumentum' => 'beírási napló, törzslap'),
6 => array('sorszam' => '3.', 'szoveg' => 'Felvette a(z) %iskola címe% iskola.', 
	    'dokumentum' => 'beírási napló, törzslap, osztálynapló'),
7 => array('sorszam' => '4.', 'szoveg' => 'Tanulmányait évfolyamismétléssel kezdheti meg, vagy osztályozó vizsga letételével folytathatja.', 
	    'dokumentum' => 'beírási napló, törzslap, osztálynapló'),
8 => array('sorszam' => '5.', 'szoveg' => '%Tantárgy% tantárgyból tanulmányait egyéni továbbhaladás szerint végzi.', 
	    'dokumentum' => 'osztálynapló, törzslap, bizonyítvány'),
9 => array('sorszam' => '6.', 'szoveg' => 'Mentesítve %tantárgy% tantárgyból az értékelés és a minősítés alól.', 
	    'dokumentum' => 'osztálynapló, törzslap, bizonyítvány'),
10 => array('sorszam' => '7.', 'szoveg' => '%Tantárgy% tantárgy %évfolyamok% évfolyamainak követelményeit egy tanévben teljesítette a következők szerint: %jegyek%', 
	    'dokumentum' => 'osztálynapló, törzslap, bizonyítvány'),
11 => array('sorszam' => '8.', 'szoveg' => 'Egyes tantárgyak tanórai látogatása alól az %tanév jele% tanévben felmentve %felmentés oka% miatt.', 
	    'dokumentum' => 'osztálynapló, törzslap, bizonyítvány)'),
12 => array('sorszam' => '8.', 'szoveg' => 'Egyes tantárgyak tanórai látogatása alól az %tanév jele% tanévben felmentve %felmentés oka% miatt. Osztályozó vizsgát köteles tenni', 
	    'dokumentum' => 'osztálynapló, törzslap, bizonyítvány'),
13 => array('sorszam' => '9.', 'szoveg' => 'Tanulmányait a szülő kérésére (szakértői vélemény alapján) magántanulóként folytatja.', 
	    'dokumentum' => 'osztálynapló, törzslap'),
14 => array('sorszam' => '10.', 'szoveg' => 'Mentesítve a(z) %tantárgyak neve% tantárgy tanulása alól.', 
	    'dokumentum' => 'osztálynapló, törzslap, bizonyítvány'),
15 => array('sorszam' => '11.', 'szoveg' => 'Tanulmányi idejének megrövidítése miatt a(z) %évfolyam% évfolyam tantárgyaiból osztályozó vizsgát köteles tenni.', 
	    'dokumentum' => 'osztálynapló, törzslap'),
16 => array('sorszam' => '12.', 'szoveg' => 'A(z) %évfolyam% évfolyamra megállapított tantervi követelményeket a tanulmányi idő megrövidítésével teljesítette.', 
	    'dokumentum' => 'osztálynapló, törzslap, bizonyítvány'), // %evfolyam% paraméter nincs a rendeletben
17 => array('sorszam' => '13.', 'szoveg' => 'A(z) %tantárgy% tantárgy %ezen óráinak% óráinak látogatása alól felmentve %tólDt%-tól %igDt%-ig.', 
	    'dokumentum' => 'osztálynapló'), // %ezen óráinak% paraméter nincs a rendeletben
18 => array('sorszam' => '13.', 'szoveg' => 'A(z) %tantárgy% tantárgy %ezen óráinak% óráinak látogatása alól felmentve %tólDt%-tól %igDt%-ig. Osztályozó vizsgát köteles tenni.', 
	    'dokumentum' => 'osztálynapló'), // %ezen óráinak% paraméter nincs a rendeletben
19 => array('sorszam' => '14.', 'szoveg' => 'Mulasztása miatt nem osztályozható, a nevelőtestület határozata értelmében osztályozó vizsgát tehet.', 
	    'dokumentum' => 'osztálynapló, törzslap'),
20 => array('sorszam' => '15.', 'szoveg' => 'A nevelőtestület határozata: a(z) %évfolyam betűvel% évfolyamba léphet.', 
	    'dokumentum' => 'osztálynapló, törzslap, bizonyítvány'),
21 => array('sorszam' => '15.', 'szoveg' => 'A nevelőtestület határozata: iskolai tanulmányait befejezte, tanulmányait %évfolyam% évfolyamon folytathatja.', 
	    'dokumentum' => 'osztálynapló, törzslap, bizonyítvány'), // csak "iskolai tanulmányait befejezte - nincs?
22 => array('sorszam' => '16.', 'szoveg' => 'A tanuló az %évfolyam% évfolyam követelményeit egy tanítási évnél hosszabb ideig, %hónap szám% hónap alatt teljesítette.', 
	    'dokumentum' => 'osztálynapló, törzslap'),
23 => array('sorszam' => '17.', 'szoveg' => 'A(z) %tantárgy% tantárgyból javítóvizsgát tehet.', 
	    'dokumentum' => 'osztálynapló, törzslap, bizonyítvány'),
24 => array('sorszam' => '17.', 'szoveg' => 'A javítóvizsgán %tantárgy% tantárgyból %osztályzat% osztályzatot kapott, %évfolyam% évfolyamba léphet.', 
	    'dokumentum' => 'törzslap, bizonyítvány'),
25 => array('sorszam' => '18.', 'szoveg' => 'A(z) %évfolyam% évfolyam követelményeit nem teljesítette, az évfolyamot meg kell ismételnie.', 
	    'dokumentum' => 'osztálynapló, törzslap, bizonyítvány'),
26 => array('sorszam' => '', 'szoveg' => '', 'dokumentum' => 'osztálynapló, törzslap, bizonyítvány'),
27 => array('sorszam' => '19', 'szoveg' => 'A javítóvizsgán %tantárgy% tantárgyból elégtelen osztályzatot kapott. Évfolyamot ismételni köteles.', 
	    'dokumentum' => 'törzslap, bizonyítvány'),
28 => array('sorszam' => '20.', 'szoveg' => 'A(z) %tantárgy% tantárgyból %dátum%-án osztályozó vizsgát tett.', 
	    'dokumentum' => 'osztálynapló, törzslap'),
29 => array('sorszam' => '21.', 'szoveg' => 'Osztályozó vizsgát tett.', 
	    'dokumentum' => 'törzslap, bizonyítvány'),
30 => array('sorszam' => '22.', 'szoveg' => 'A(z) %tantárgy% tantárgy alól %felmentés oka% okból felmentve.', 
	    'dokumentum' => 'törzslap, bizonyítvány'),
31 => array('sorszam' => '23.', 'szoveg' => 'A(z) %tanóra% tanóra alól %felmentés oka% okból felmentve.', 
	    'dokumentum' => 'törzslap, bizonyítvány'),
32 => array('sorszam' => '24.', 'szoveg' => 'Az osztályozóvizsga letételére %igDt%-ig halasztást kapott.', 
	    'dokumentum' => 'törzslap, bizonyítvány'),
33 => array('sorszam' => '24.', 'szoveg' => 'Az beszámoltatóvizsga letételére %igDt%-ig halasztást kapott.', 
	    'dokumentum' => 'törzslap, bizonyítvány'),
34 => array('sorszam' => '24.', 'szoveg' => 'Az különbözetivizsga letételére %igDt%-ig halasztást kapott.', 
	    'dokumentum' => 'törzslap, bizonyítvány'),
35 => array('sorszam' => '24.', 'szoveg' => 'Az javítóvizsga letételére %igDt%-ig halasztást kapott.', 
	    'dokumentum' => 'törzslap, bizonyítvány'),
36 => array('sorszam' => '25.', 'szoveg' => 'Az osztályozó vizsgát engedéllyel a(z) %iskola% iskolában független vizsgabizottság előtt tette le.', 
	    'dokumentum' => 'törzslap, bizonyítvány'),
37 => array('sorszam' => '25.', 'szoveg' => 'Az javítóvizsgát engedéllyel a(z) %iskola% iskolában független vizsgabizottság előtt tette le.', 
	    'dokumentum' => 'törzslap, bizonyítvány'),
38 => array('sorszam' => '26.', 'szoveg' => 'A(z) %szakképesítés% szakképesítés évfolyamán folytatja tanulmányait.', 
	    'dokumentum' => 'törzslap, bizonyítvány, osztálynapló'),
39 => array('sorszam' => '27.', 'szoveg' => 'Tanulmányait %ok% okból megszakította, a tanulói jogviszonya %igDt%-ig szünetel.', 
	    'dokumentum' => 'beírási napló, törzslap'),
40 => array('sorszam' => '28.', 'szoveg' => 'A tanuló jogviszonya kimaradással megszűnt, a létszámból törölve.', 
	    'dokumentum' => 'beírási napló, törzslap, bizonyítvány, osztálynapló'),
41 => array('sorszam' => '28.', 'szoveg' => 'A tanuló jogviszonya %igazolatlan órák száma% óra igazolatlan mulasztás miatt megszűnt, a létszámból törölve.', 
	    'dokumentum' => 'beírási napló, törzslap, bizonyítvány, osztálynapló'),
42 => array('sorszam' => '28.', 'szoveg' => 'A tanuló jogviszonya egészségügyi alkalmasság miatt megszűnt, a létszámból törölve.', 
	    'dokumentum' => 'beírási napló, törzslap, bizonyítvány, osztálynapló'),
43 => array('sorszam' => '28.', 'szoveg' => 'A tanuló jogviszonya térítési díj fizetési hátralék miatt megszűnt, a létszámból törölve.', 
	    'dokumentum' => 'beírási napló, törzslap, bizonyítvány, osztálynapló'),
44 => array('sorszam' => '28.', 'szoveg' => 'A tanuló jogviszonya tandíj fizetési hátralék miatt megszűnt, a létszámból törölve.', 
	    'dokumentum' => 'beírási napló, törzslap, bizonyítvány, osztálynapló'),
45 => array('sorszam' => '28.', 'szoveg' => 'A tanuló jogviszonya %iskola% iskolába való átvétel miatt megszűnt, a létszámból törölve.', 
	    'dokumentum' => 'beírási napló, törzslap, bizonyítvány, osztálynapló'),
46 => array('sorszam' => '29.', 'szoveg' => '%Fegyelmező intézkedés% fegyelmező intézkedésben részesült.', 
	    'dokumentum' => 'osztálynapló'),
47 => array('sorszam' => '30.', 'szoveg' => '%Fegyelmi büntetés% fegyelmi büntetésben részesült. A büntetés végrehajtása %igDt%-ig felfüggesztve.', 
	    'dokumentum' => 'törzslap'),
48 => array('sorszam' => '31.', 'szoveg' => 'A tanuló %igazolatlan órák száma% óra igazolatlan mulasztása miatt a szülőt felszólítottam.', 
	    'dokumentum' => 'osztálynapló'), // A "Tanköteles tanuló igazolatlan mulasztása esetén" szöveget magyarázatnak tekintettem
49 => array('sorszam' => '31.', 'szoveg' => 'A tanuló ismételt %igazolatlan órák száma% óra igazolatlan mulasztása miatt a szülő ellen szabálysértési eljárást kezdeményeztem.', 
	    'dokumentum' => 'beírási napló, törzslap, osztálynapló'), // A "Tanköteles tanuló igazolatlan mulasztása esetén" szöveget magyarázatnak tekintettem
50 => array('sorszam' => '32.', 'szoveg' => 'Tankötelezettség megszűnt.', 'dokumentum' => 'beírási napló'),
51 => array('sorszam' => '33.', 'szoveg' => 'A %szó% szót %helyesbítés%-ra/re helyesbítettem.', 
	    'dokumentum' => 'törzslap, bizonyítvány'),
52 => array('sorszam' => '33.', 'szoveg' => 'A %szavak% szavakat %helyesbítés%-ra/re helyesbítettem.', 
	    'dokumentum' => 'törzslap, bizonyítvány'),
53 => array('sorszam' => '33.', 'szoveg' => 'A %osztályzat% osztályzatot %helyesbítés%-ra/re helyesbítettem.', 
	    'dokumentum' => 'törzslap, bizonyítvány'),
54 => array('sorszam' => '33.', 'szoveg' => 'A %osztályzatok% osztályzatokat %helyesbítés%-ra/re helyesbítettem.', 
	    'dokumentum' => 'törzslap, bizonyítvány'),
55 => array('sorszam' => '34.', 'szoveg' => 'A bizonyítvány %lap% lapját téves bejegyzés miatt érvénytelenítettem.', 
	    'dokumentum' => 'bizonyítvány'),
56 => array('sorszam' => '35.', 'szoveg' => 'Ezt a póttörzslapot a(z) %ok% következtében elvesztett (megsemmisült) eredeti helyett %adatforrás% adatai (adatok) alapján állítottam ki.', 
	    'dokumentum' => 'Pót. törzslap'),
57 => array('sorszam' => '36.', 'szoveg' => 'Ezt a bizonyítványmásodlatot az elveszett (megsemmisült) eredeti helyett %adatforrás% adatai (adatok) alapján állítottam ki.', 
	    'dokumentum' => 'Pót. törzslap'),
58 => array('sorszam' => '37.', 'szoveg' => 'A bizonyítványt %kérelmező% kérelmére a %bizonyítványszám% számú bizonyítvány alapján, téves bejegyzés miatt állítottam ki.', 
	    'dokumentum' => 'törzslap, bizonyítvány'),
59 => array('sorszam' => '38.', 'szoveg' => 'Pótbizonyítvány. Igazolom, hogy név %Név%, anyja neve %Anyja neve% a(z) %iskola% iskola %szak% szak (szakmai, speciális osztály, két tanítási nyelvű osztály, tagozat) %évfolyam% évfolyamát a(z) %tanév jele% tanévben eredményesen elvégezte.', 
	    'dokumentum' => 'Pót. bizonyítvány'),
60 => array('sorszam' => '39.', 'szoveg' => 'Az iskola a tanulmányi eredmények bejegyzéséhez, a kiemelkedő tanulmányi eredmények elismeréséhez, a felvételi vizsga eredményeinek bejegyzéséhez %vizsga% vizsga eredményének befejezéséhez vagy egyéb, a záradékok között nem szereplő, a tanulóval kapcsolatos közlés dokumentálásához a záradékokat megfelelően alkalmazhatja, illetve megfelelően záradékot alakíthat ki.', 
	    'dokumentum' => ''),
61 => array('sorszam' => '40.', 'szoveg' => 'Érettségi vizsgát tehet.', 
	    'dokumentum' => 'törzslap, bizonyítvány'),
62 => array('sorszam' => '41.', 'szoveg' => 'Gyakorlati képzésről mulasztását %tólDt%-tól %igDt%-ig pótolhatja.', 
	    'dokumentum' => 'törzslap, bizonyítvány, osztálynapló'),
63 => array('sorszam' => '42.', 'szoveg' => 'Beírtam a %iskola% iskola első osztályába.', 
	    'dokumentum' => ''),
64 => array('sorszam' => '43.', 'szoveg' => 'Ezt a naplót %tanítási napok száma% tanítási nappal lezártam.', 
	    'dokumentum' => 'osztálynapló'),
65 => array('sorszam' => '43.', 'szoveg' => 'Ezt a naplót %tanítási órák száma% tanítási órával lezártam.', 
	    'dokumentum' => 'osztálynapló'),
66 => array('sorszam' => '44.', 'szoveg' => 'Ezt az osztályozó naplót %tanulók száma% azaz %tanulók száma betűvel% osztályozott tanulóval lezártam.', 
	    'dokumentum' => 'osztálynapló'),
67 => array('sorszam' => '45.', 'szoveg' => 'Ezt az osztályozó naplót %tanulók száma% azaz %tanulók száma betűvel% osztályozott tanulóval lezártam.', 
	    'dokumentum' => 'osztálynapló'),
68 => array('sorszam' => '46.', 'szoveg' => 'Igazolom, hogy a tanuló a %tanév jele% tanévben %elvégzett órák száma% óra közösségi szolgálatot teljesített.', 
	    'dokumentum' => 'osztálynapló, törzslap, bizonyítvány'),
69 => array('sorszam' => '47.', 'szoveg' => 'A tanuló teljesítette az érettségi bizonyítvány kiadásához szükséges közösségi szolgálatot', 
	    'dokumentum' => 'osztálynapló, törzslap, bizonyítvány'),
70 => array('sorszam' => '48.', 'szoveg' => '%nemzetiség megnevezése% kiegészítő nemzetiségi tanulmányait a nyolcadik/tizenkettedik évfolyamon befejezte', 
	    'dokumentum' => 'törzslap, bizonyítvány'),




100 => array('sorszam' => '1/A', 'szoveg' => 'Felvéve a(z) %iskola címe% %osztály% osztályába.', 
	    'dokumentum' => 'beírási napló, osztálynapló, törzslap, bizonyítvány'),
101 => array('sorszam' => '6/B', 'szoveg' => '%miatt% miatt mentesítve a(z) %mi% értékelés(e) és a minősítés(e) alól.', 
	    'dokumentum' => 'osztálynapló, törzslap, bizonyítvány'),
102 => array('sorszam' => '15/A', 'szoveg' => 'A nevelőtestület határozata: iskolai tanulmányait befejezte.', 
	    'dokumentum' => 'osztálynapló, törzslap, bizonyítvány'),
103 => array('sorszam' => '19/A', 'szoveg' => 'Az osztályozó vizsgán a %tantárgy% tantárgy követelményeit nem teljesítette - %osztályzat%.', 
	    'dokumentum' => 'törzslap, bizonyítvány'),
104 => array('sorszam' => '19/B', 'szoveg' => 'A beszámoltatóvizsgán a %tantárgy% tantárgy követelményeit nem teljesítette - %osztályzat%.', 
	    'dokumentum' => 'törzslap, bizonyítvány'),
105 => array('sorszam' => '19/C', 'szoveg' => 'A különbözetivizsgán a %tantárgy% tantárgy követelményeit nem teljesítette - %osztályzat%.', 
	    'dokumentum' => 'törzslap, bizonyítvány'),
106 => array('sorszam' => '19/D', 'szoveg' => 'A javítóvizsgán a %tantárgy% tantárgy követelményeit nem teljesítette - %osztályzat%. Évfolyamot ismételni köteles.', 
	'dokumentum' => 'törzslap, bizonyítvány'), // Ez nem kell, mert az eredeti 19-es épp erről az esetről szól - nem?
107 => array('sorszam' => '20/A', 'szoveg' => 'A(z) %tantárgy% tantárgyból %dátum%-án beszámoltatóvizsgát tett.', 
	    'dokumentum' => 'osztálynapló, törzslap'),
108 => array('sorszam' => '20/B', 'szoveg' => 'A(z) %tantárgy% tantárgyból %dátum%-án különbözetivizsgát tett.', 
	    'dokumentum' => 'osztálynapló, törzslap'),
109 => array('sorszam' => '20/C', 'szoveg' => 'A(z) %tantárgy% tantárgyból a(z) %évfolyam% évfolyam anyagából %dátum% napon %osztályzat% eredménnyel osztályozó vizsgát tett.', 
	    'dokumentum' => 'osztálynapló, törzslap'),
110 => array('sorszam' => '28/A', 'szoveg' => 'A tanuló jogviszonya haláleset miatt megszűnt, a létszámból törölve.', 
	    'dokumentum' => 'beírási napló, törzslap, bizonyítvány, osztálynapló'),
111 => array('sorszam' => '39/A', 'szoveg' => '%Egyedi osztálynapló záradék%', 
	    'dokumentum' => 'osztálynapló'),
112 => array('sorszam' => '39/B', 'szoveg' => '%Egyedi törzslap záradék%', 
	    'dokumentum' => 'törzslap'),
113 => array('sorszam' => '39/C', 'szoveg' => '%Egyedi bizonyítvány záradék%', 
	    'dokumentum' => 'bizonyítvány'),
114 => array('sorszam' => '39/D', 'szoveg' => 'A %iktatószám% alapján tanulmányait a %osztály% osztályban folytatja.', 
	    'dokumentum' => 'beírási napló, törzslap'),
115 => array('sorszam' => '39/E', 'szoveg' => 'A(z) %tankorDiakFelmentesId% nyilvántartási számú felmentést %dt% napon hatályon kívül helyeztem. ', 
	    'dokumentum' => 'osztálynapló, törzslap, bizonyítvány'),
116 => array('sorszam' => '39/F', 'szoveg' => 'A(z) %zaradekId% nyilvántartási számú záradékban rögzített felmentést %dt% napon hatályon kívül helyeztem. ', 
	    'dokumentum' => 'osztálynapló, törzslap, bizonyítvány'),
117 => array('sorszam' => '18/A', 'szoveg' => 'A(z) %évfolyam% évfolyam követelményeit nem teljesítette, az évfolyamot megismételheti.', // nem tanköteles diák esetén 
	    'dokumentum' => 'osztálynapló, törzslap, bizonyítvány'),
118 => array('sorszam' => '9/A', 'szoveg' => 'Tanulmányait egyéni munkarendben folytatja.', 
	    'dokumentum' => 'osztálynapló, törzslap'),

);

$ZaradekIndex = array(
    'konferencia' => array(
	// továbbléphet
	'következő évfolyamba léphet' => 20, // %évfolyam betűvel%
	'tanulmányait befejezte, következő évfolyamon folytathatja' => 21, // %évfolyam%
	'tanulmányait befejezte' => 102,
	'érettségi vizsgát tehet' => 61,
	'szakképző évfolyamba léphet' => 38, // %szakképesítés%
	// mulasztás - de vizsgázat
	'mulasztás miatt osztályozóvizsga' => 19,
    ),
    'konferencia bukás' => array(
	'javítóvizsgát tehet' => 23, // %tantárgy%
	'előkészítőt végzett' => 4, // csak első évfolyamon!!
	'követelményeket nem teljesítette, évfolyamot ismétel' => 25, // %évfolyam% - tanköteles eset
	'követelményeket nem teljesítette, évfolyamot ismételhet' => 117, // %évfolyam% - nem tanköteles eset
    ),
    'jogviszony megnyitás' => array(
	'felvétel' => 1,
	'felvétel osztályba' => 100, // %iskola címe%, %osztály%
	'átvétel' => 2, // %iskola címe%
	'áthelyezés' => 3, // %határozat száma%, %iskola címe%
    ),
    'jogviszony változás' => array(
	'magántanuló' => 13, 
	'egyéni munkarend' => 118,
	'felfüggesztés' => 39 // %ok%, %igDt%
    ),
    'jogviszony lezárás' => array(
	'kimaradás' => 40,
	'igazolatlan órák' => 41, // %igazolatlan órák száma%
	'egészségügyi alkalmasság' => 42,
	'térítési díj hátralék' => 43,
	'tandíj hátralék' => 44,
	'átvétel' => 45, // %iskola%
	'haláleset' => 110,
	'tanulmányait folytathatja' => 21, // %évfolyam%
	'tanulmányait befejezte' => 102,
    ),
    'vizsga halasztás' => array(
	'osztályozó vizsga' => 32, // %igDt%
	'beszámoltatóvizsga' => 33, // %igDt%
	'különbözetivizsga' => 34, // %igDt%
	'javítóvizsga' => 35, // %igDt%
    ),
    'vizsga' => array(
	'osztályozó vizsga' => 109, // %tantárgy%, %évfolyam%, %dátum%, %osztályzat%
	'osztályozó vizsga bukás' => 103, // %tantárgy%, %osztályzat%
	'beszámoltatóvizsga' => 107, // %tantárgy%
	'beszámoltatóvizsga bukás' => 104, // %tantárgy%, %osztályzat%
	'különbözetivizsga' => 108, // %tantárgy%
	'különbözetivizsga bukás' => 105, // %tantárgy%, %osztályzat%
	'javítóvizsga' => 24, // %tárgy%, %osztályzat%, %évfolyam%
	'javítóvizsga bukás' => 27, // %tantárgy%
	'javítóvizsga nem teljesített' => 25, // %évfolyam% (az évfolyam követelményeit)
    ),
    'felmentés' => array(
	'értékelés alól' => 9, // %tantárgy%
	'értékelés és minősítés alól' => 101, // %miatt%, %mi%
	'óra látogatása alól' => 17, // %tantárgy%, %ezen óráinka%, %tólDt%, %igDt%
	'óra látogatása alól osztályozóvizsgával' => 18, // %tantárgy%, %ezen óráinka%, %tólDt%, %igDt%
	'tárgy tanulása alól' => 14, // %tantárgyak neve%
	'törlés' => 115, // %tankorDiakFelmentesId%
    ),
    'törzslap feljegyzés' => array(
	'egyedi törzslap záradék' => 112
    )

);

} // __TANEV > 2012

?>