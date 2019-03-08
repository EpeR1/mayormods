<?php

    return true;

    if (_RIGHTS_OK !== true) die();

    if (!__NAPLOADMIN && !__VEZETOSEG && !__TITKARSAG && !__TANAR) {
        $_SESSION['alert'][] = 'message:insufficient_access';
    } else {

	require_once('include/modules/naplo/share/osztaly.php');
	require_once('include/modules/naplo/share/szulo.php');
	require_once('include/modules/naplo/share/file.php');
	require_once('include/share/date/names.php');

	$ADAT['formatum'] = readVariable($_POST['formatum'], 'emptystringnull', 'csv', array('csv','xml','pdf'));
	if ($ADAT['formatum'] == 'xml') {
	    $ADAT['mime'] = 'application/vnd.ms-excel';
	    $ADAT['formatum'] = 'xml';
	}
	$ADAT['osztalyId'] = $osztalyId = readVariable($_POST['osztalyId'], 'numeric unsigned', null);
	$ADAT['dt'] = $dt = readVariable($_POST['dt'], 'datetime', date('Y-m-d'));
	$ADAT['tanev'] = $tanev = readVariable($_POST['tanev'], 'numeric unsigned', __TANEV); 
	if ($tanev != __TANEV) $TA = getTanevAdat($tanev); else $TA = $_TANEV;

	$ADAT['fields'] = getTableFields('diak','naplo_intezmeny',array('osztalyJel','diakNaploSorszam'));
	$szuloMezok = getTableFields('szulo');
	foreach (array('anya','apa','gondviselő','nevelő') as $szulo) 
	    foreach ($szuloMezok as $attr => $attrNev)
		$ADAT['fields'][ekezettelen($szulo).ucfirst($attr)] = ucfirst($szulo).' '.kisbetus($attrNev);
	$ADAT['fields']['telephelyId'] = 'telephelyId';
	foreach ($ADAT['fields'] as $attr => $attrNev) 
	    if (!is_array($_POST['mezok']) || in_array($attr, $_POST['mezok'])) $ADAT['mezok'][$attr] = $attrNev;

	$ADAT['export'] = diakExport($ADAT);
        if ($action == 'diakExport') {

            if (is_array($ADAT['export']) && createFile($ADAT))
		header('Location: '.location('index.php?page=session&f=download&download=true&dir=export&file=diakExport.'.$ADAT['formatum'].'&mimetype='.$ADAT['mime']));
	    else echo 'SEMMI: '.__DIAK_EXPORT_FILE;

        }


	$TOOL['tanevSelect'] = array('tipus' => 'cella', 'paramName' => 'tanev', 'post' => array('osztalyId'));
        $TOOL['datumSelect'] = array(
            'tipus'=>'cella', 'post' => array('osztalyId', 'tanev'),
            'paramName' => 'dt', 'hanyNaponta' => 1,
            'tolDt' => date('Y-m-d', strtotime($TA['kezdesDt'])),
            'igDt' => $TA['zarasDt'],
        );
	$TOOL['osztalySelect'] = array('tipus' => 'cella', 'paramName' => 'osztalyId', 'post' => array('tanev', 'dt'));
	getToolParameters();
    }

?>
