<?php

    if (_RIGHTS_OK !== true) die();

    if (
        !__NAPLOADMIN && !__VEZETOSEG && !__TITKARSAG
    ) {
        $_SESSION['alert'][] = 'page:insufficient_access';
    } else {

	require_once('include/modules/naplo/share/tanar.php');
	require_once('include/modules/naplo/share/targy.php');
	require_once('include/modules/naplo/share/tankor.php');
	require_once('include/modules/naplo/share/kepesites.php');
	require_once('include/modules/naplo/share/osztaly.php');
	require_once('include/modules/naplo/share/file.php');

        $ADAT['formatum'] = readVariable($_POST['formatum'], 'enum', null, array('csv','ods','xml'));
        if ($ADAT['formatum'] == 'xml') $ADAT['mime'] = 'application/vnd.ms-excel';

	if (isset($ADAT['formatum'])) {

	    // tanárok
	    $ADAT['tanarok'] = getTanarok(array('extraAttrs'=> 'hetiKotelezoOraszam, besorolas'));
	    $ADAT['tanarKepesitesIds'] = getTanarKepesitesIds();
	    $ADAT['kepesitesAdat'] = getKepesitesek(array('result' => 'assoc'));
	    for ($i = 0; $i < count($ADAT['tanarok']); $i++) {
		$ADAT['tanarAdat'][ $ADAT['tanarok'][$i]['tanarId'] ] = $ADAT['tanarok'][$i];
		$ADAT['tanarAdat'][ $ADAT['tanarok'][$i]['tanarId'] ]['targyIds'] = getTargyIdsByTanarId($ADAT['tanarok'][$i]['tanarId']);
	    }
	    unset($ADAT['tanarok']);
	    // tankörök
	    $ADAT['tankorok'] = getTankorOraszamok();
	    foreach ($ADAT['tankorok'] as $idx => $tAdat) {
		$tankorId = $tAdat['tankorId'];
		$targyId = $tAdat['targyId'];
		$osztalyId = $tAdat['osztalyIds'][0]; // mi legyen a több osztályos tankörökkel???
		$szemeszter = $tAdat['szemeszter'];
		$tanarDb = count($tAdat['tanarIds']);
		$oraszam = $tAdat['oraszam'] / $tanarDb;
		foreach ($tAdat['tanarIds'] as $tanarId) {
		    $ADAT['export'][$tanarId][$targyId][$osztalyId][$szemeszter] += $oraszam;
		}
	    }
	    // tárgyak
	    $ADAT['targyak'] = getTargyak();
	    foreach ($ADAT['targyak'] as $idx => $tAdat) $ADAT['targyAdat'][ $tAdat['targyId'] ] = $tAdat;
	    unset($ADAT['targyak']);
	    // osztalyok
	    $ADAT['osztalyok'] = getOsztalyok();

//dump($ADAT['export']);

    	    $file = fileNameNormal('tantargyFelosztas-'.__TANEV.'-'.date('Ymd'));
    	    if (exportTantargyFelosztas($file, $ADAT))
        	header('Location: '.location('index.php?page=session&f=download&download=true&dir=naplo/export/tantargyFelosztas&file='.$file.'.'.$ADAT['formatum'].'&mimetype='.$ADAT['mime']));

//	    dump($ADAT);

	}

    }
?>