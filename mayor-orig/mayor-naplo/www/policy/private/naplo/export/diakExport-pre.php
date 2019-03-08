<?php

    if (_RIGHTS_OK !== true) die();

    if (!__NAPLOADMIN && !__VEZETOSEG && !__TITKARSAG && !__TANAR) {
        $_SESSION['alert'][] = 'message:insufficient_access';
    } else {

	require_once('include/modules/naplo/share/osztaly.php');
	require_once('include/modules/naplo/share/szulo.php');
	require_once('include/modules/naplo/share/file.php');
	require_once('include/share/date/names.php');

	$ADAT['formatum'] = isset($_POST['html'])?'html':(isset($_POST['csv'])?'csv':(isset($_POST['xml'])?'xml':null));
	if (is_null($ADAT['formatum'])) $ADAT['formatum'] = readVariable($_POST['formatum'], 'enum', null, array('csv','xml','html'));
	$_POST['formatum'] = $ADAT['formatum'];
	if ($ADAT['formatum'] == 'xml') {
	    $ADAT['mime'] = 'application/vnd.ms-excel';
	    $ADAT['formatum'] = 'xml';
	}
	$ADAT['osztalyId'] = $osztalyId = readVariable($_POST['osztalyId'], 'numeric unsigned', null);
	$ADAT['dt'] = $dt = readVariable($_POST['dt'], 'datetime', date('Y-m-d'));
	$ADAT['tanev'] = $tanev = readVariable($_POST['tanev'], 'numeric unsigned', __TANEV); 
	if ($tanev != __TANEV) $TA = getTanevAdat($tanev); else $TA = $_TANEV;

	$ret = getTableFields('diak','naplo_intezmeny',array('osztalyJel','diakNaploSorszam'), array('withType' => true));
	$ADAT['fields'] = $ret['names'];
	$ADAT['types'] = $ret['types']; $ADAT['types']['osztalyJel'] = 'string'; $ADAT['types']['diakNaploSorszam'] = 'int';
	$ret = getTableFields('szulo','naplo_intezmeny',array(), array('withType' => true));
	$szuloMezok = $ret['names'];
	foreach (array('anya','apa','gondviselő','nevelő') as $szulo) {
	    foreach ($szuloMezok as $attr => $attrNev) {
		$ADAT['fields'][ekezettelen($szulo).ucfirst($attr)] = ucfirst($szulo).' '.kisbetus($attrNev);
		$ADAT['types' ][ekezettelen($szulo).ucfirst($attr)] = $ret['types'][ $attrNev ];
	    }
	}
	$ADAT['fields']['telephelyId'] = 'telephelyId';
	$ADAT['types' ]['telephelyId'] = 'int';

	if (!is_array($_POST['mezok'])) $_POST['mezok'] = $exportFormatum['alapértelmezett'];
	foreach ($_POST['mezok'] as $i => $attr) {
	    if (isset($ADAT['fields'][$attr])) $ADAT['mezok'][$attr] = $ADAT['fields'][$attr];
	}

	if (isset($ADAT['formatum'])) {
	    $ADAT['export'] = diakExport($ADAT);
    	    if ($ADAT['formatum'] != 'html') {

        	if (is_array($ADAT['export']) && createFile($ADAT)) {
			header('Location: '.location('index.php?page=session&f=download&download=true&dir=export&file=diakExport.'.$ADAT['formatum'].'&mimetype='.$ADAT['mime']));
		} else {
			echo 'HIBA';
		}
	    }
        }

	$TOOL['tanevSelect'] = array('tipus' => 'cella', 'paramName' => 'tanev', 'post' => array('osztalyId'));
        $TOOL['datumSelect'] = array(
            'tipus'=>'cella', 'post' => array('osztalyId', 'tanev'),
            'paramName' => 'dt', 'hanyNaponta' => 1,
            'tolDt' => date('Y-m-d', strtotime($TA['kezdesDt'])),
            'igDt' => $TA['zarasDt'],
        );
	$TOOL['osztalySelect'] = array('tipus' => 'cella', 'paramName' => 'osztalyId', 'post' => array('tanev', 'dt','mezok','formatum'));
	getToolParameters();
    }

?>
