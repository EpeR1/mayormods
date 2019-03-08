<?php

    if (_RIGHTS_OK !== true) die();
    if (!__TANAR && !__NAPLOADMIN && !__VEZETOSEG) {
	$_SESSION['alert'][] = 'page:insufficient_access';
    } else {

	require_once('include/modules/naplo/share/tankor.php');
	require_once('include/modules/naplo/share/tanar.php');
	require_once('include/modules/naplo/share/kerdoiv.php');
	require_once('include/modules/naplo/share/file.php');
	require_once('include/share/print/pdf.php');

	$ADAT['kerdoiv'] = getOsszesKerdoiv();
	$ADAT['kerdoivIds'] = array();
	for ($i = 0; $i < count($ADAT['kerdoiv']); $i++) $ADAT['kerdoivIds'][] = $ADAT['kerdoiv'][$i]['kerdoivId'];

	$ADAT['tanarId'] = $tanarId = readVariable($_POST['tanarId'], 'numeric unsigned');
	if (__TANAR && !__NAPLOADMIN && !__VEZETOSEG) $tanarId = __USERTANARID;
	$ADAT['tankorId'] = $tankorId = readVariable($_POST['tankorId'], 'numeric unsigned');

	$ADAT['kerdoivId'] = $kerdoivId = readVariable($_POST['kerdoivId'], 'numeric', null, $ADAT['kerdoivIds']);

	if (isset($kerdoivId)) {

	    $ADAT['kerdoivStat'] = getKerdoivStat($kerdoivId);

//	    if (isset($tankorId)) {
//	    } else {
		$Table = array();
		foreach (array('tankor','tankorSzulo') as $key => $cimzettTipus) {
		if (is_array($ADAT['kerdoivStat']['cimzett'][$cimzettTipus])) { // Ha tanköröket értékeltünk
		    // Elválasztó - a két címzettTípus között
//		    $Table[] = ''; $Table[] = $cimzettTipus; $Table[] = '';
		    $sor = count($Table); // A következő sor
		    // táblázat elkészítése - két dimenziós tömb
		    // első sor: Tanár | Tankör | Kerdes |.|.|.| Kerdes |.|...
		    $Table[$sor] = array('Tanár/Tankör','Létszám');
		    foreach ($ADAT['kerdoivStat']['kerdes'] as $kerdesId => $kAdat) {
			$Table[$sor][] = $kAdat['kerdes'].' ('.$kerdesId.')';
			for ($i = 1; $i < count($kAdat['valasz']); $i++) $Table[$sor][] = '';
		    }
		    // Második sor: | valasz1/1 | Valasz1/2 ...
		    $Table[$sor+1] = array('','');
		    foreach ($ADAT['kerdoivStat']['kerdes'] as $kerdesId => $kAdat) {
			$j=1;
			foreach ($kAdat['valasz'] as $valaszId => $valasz) 
			    $Table[$sor+1][] = ($j++).'. '.$valasz.' ('.$valaszId.')';
		    }
		    // Tanáronként megyünk
		    if (isset($tanarId)) $Tanarok = array($tanarId => $ADAT['kerdoivStat']['tanarNev'][$tanarId]);
		    else $Tanarok = $ADAT['kerdoivStat']['tanarNev'];
    		    foreach ($Tanarok as $tanarId => $tanarNev) {
			$Table[] = array($tanarNev,''); // Ebbe a sorba lehetne esetleg átlagolni
			// tanköröknként
			for ($i = 0; $i < count($ADAT['kerdoivStat']['tanarTankorei'][$tanarId]); $i++) {
			    $tankorId = $ADAT['kerdoivStat']['tanarTankorei'][$tanarId][$i];
			    $tankorNev = $ADAT['kerdoivStat']['tankorAdat'][$tankorId]['tankorNev'];
			    $Row = array($tankorNev.' ('.$tankorId.')', $ADAT['kerdoivStat']['tankorAdat'][$tankorId]['letszam']);
			    // Kérdésenként
			    foreach ($ADAT['kerdoivStat']['kerdes'] as $kerdesId => $kAdat) {
				// Válszonként
				foreach ($kAdat['valasz'] as $valaszId => $valasz) {
				    $Row[] = $ADAT['kerdoivStat']['szavazat'][$cimzettTipus][$tankorId][$valaszId];
				}
			    }
			    $Table[] = $Row;
			}
		    }

		}} // if / foreach

		if (strstr($_SERVER["HTTP_USER_AGENT"], 'Linux')) {
		    $fileName = fileNameNormal('kerdoiv-'.$kerdoivId.'.csv');
		    if (generateCSV($fileName, $Table, 'Kérdőív összesítés')) 
			header('Location: '.location('index.php?page=session&f=download&download=true&dir=naplo/ertekeles/osszesites&file='.$fileName));
		} else {
		    $fileName = fileNameNormal('kerdoiv-'.$kerdoivId.'.xls');
		    if (generateXLS($fileName, $Table, 'Kérdőív összesítés')) 
			header('Location: '.location('index.php?page=session&f=download&download=true&dir=naplo/ertekeles/osszesites&file='.$fileName));
		}
//	    } // isset($tankorId)
	}

    }

    if (__NAPLOADMIN || __VEZETOSEG)
        $TOOL['tanarSelect'] = array('tipus' => 'cella', 'post' => array('kerdoivId'));
//        $TOOL['osztalySelect'] = array('tipus' => 'cella', 'paramName' => 'osztalyId', 'post' => array('tolDt', 'igDt'));
//        if (isset($osztalyId) || isset($tanarId))
//            $TOOL['tankorSelect'] = array('tipus' => 'sor', 'paramName' => 'tankorId', 'post' => array('osztalyId', 'tanarId', 'kerdoivId'));


    $TOOL['kerdoivSelect'] = array('tipus' => 'cella', 'paramName' => 'kerdoivId', 'kerdoiv' => $ADAT['kerdoiv'], 'post' => array('tanarId', 'tankorId'));
    getToolParameters();

?>
