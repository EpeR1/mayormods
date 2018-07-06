<?php

if (_RIGHTS_OK !== true) die();

if (!__NAPLOADMIN && !__VEZETOSEG && !__TITKARSAG) {
    $_SESSION['alert'][] = 'message:insufficient_access';
} else {

    require_once('include/modules/naplo/share/osztaly.php');
    require_once('include/modules/naplo/share/diak.php');
    require_once('include/modules/naplo/share/file.php');
    require_once('include/modules/naplo/share/zaradek.php');
    require_once('include/modules/naplo/share/bejegyzes.php');
    require_once('include/modules/naplo/share/tanar.php');
    require_once('include/modules/naplo/share/intezmenyek.php');
    require_once('include/share/str/tex.php');
    require_once('include/share/print/pdf.php');

    $ADAT['osztalyId'] = $osztalyId = readVariable($_POST['osztalyId'], 'id');
    $ADAT['tanev'] = readVariable($_POST['tanev'], 'numeric unsigned', __TANEV);
    if ($ADAT['tanev'] == __TANEV) $ADAT['tanevAdat'] = $_TANEV; else $ADAT['tanevAdat'] = getTanevAdat($tanev);
    $ADAT['tolDt'] = $ADAT['tanevAdat']['kezdesDt']; $ADAT['igDt'] = $ADAT['tanevAdat']['zarasDt'];

    if (isset($osztalyId)) {
	// intézmény adatok
        $ADAT['intezmeny'] = getIntezmenyByRovidnev(__INTEZMENY);
        // osztály statisztikák
        $ADAT['osztaly'] = getOsztalyAdat($osztalyId, $ADAT['tanev']);

	$ADAT['file'] = fileNameNormal('zaradekok-'.$ADAT['osztaly']['osztalyJel'].'-'.date('Ymd'));
	$ADAT['diak'] = getDiakokByOsztaly($osztalyId, $ADAT);
	// Azok a diákok, akik az adott időszakban voltak jogviszonyban (esetleg magáán- vagy venfégtanulóként
	$ADAT['diakIds'] = array_values(array_unique(array_merge($ADAT['diak']['jogviszonyban van'], $ADAT['diak']['magántanuló'], $ADAT['diak']['vendégtanuló'])));
	for ($i = 0; $i < count($ADAT['diakIds']); $i++) {
	    $ADAT['zaradek'][ $ADAT['diakIds'][$i] ] = getDiakZaradekok($ADAT['diakIds'][$i], array('result' => 'indexed', 'tolDt' => $ADAT['tolDt'], 'igDt' => $ADAT['igDt'], 'dokumentum' => 'osztálynapló'));
	    $ADAT['bejegyzes'][ $ADAT['diakIds'][$i] ] = getDiakBejegyzesekByTanev($ADAT['diakIds'][$i], $ADAT['tanev']);
	}
	$ADAT['file'] = fileNameNormal($ADAT['file']);
        if (pdfZaradekok($ADAT))
            header('Location: '.location('index.php?page=session&f=download&download=true&dir=naplo/nyomtatas/zaradekok&file='.$ADAT['file'].'.pdf'));

    }

    $TOOL['osztalySelect'] = array('tipus' => 'cella','paramName' => 'osztalyId', 'post' => array());
    getToolParameters();

}

?>
