<?php

    require_once('include/modules/naplo/share/hianyzas.php');

    $SSSHH .= '<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js" async defer></script>';

    $dt = $refTolDt = readVariable($_POST['refTolDt'],'date',date('Y-m-d',strtotime('-7 day')));
    $refIgDt = readVariable($_POST['refIgDt'],'date',date('Y-m-d'));

    $datediff = strtotime($refIgDt) - strtotime($refTolDt);
    $dbNap = floor($datediff / (60 * 60 * 24));

    $ADAT = array();

    if (strtotime($dt) <= strtotime($refIgDt)) {
	while (strtotime($dt)<= strtotime($refIgDt)) {
	    if (date('N',strtotime($dt))<=5) $ADAT[$dt] = getDarabDiakHianyzas($dt);
	    $dt = date('Y-m-d',strtotime('+1 day',strtotime($dt)));
	}
    }

       $TOOL['datumTolIgSelect'] = array(
            'tipus' => 'sor', 'title' => 'REFDT',
            'post'=>array('tolDt','tanarId','osztalyId','tankorId','mkId','diakId','telephely'),
            'tolParamName' => 'refTolDt', 'igParamName' => 'refIgDt', 'hanyNaponta' => 1,
            'tolDt' => $_TANEV['elozoZarasDt'],
            'igDt' => $_TANEV['kovetkezoKezdesDt'],
            'override' => true,
            'tolDt' => date('Y-m-d', strtotime('last Monday', strtotime($_TANEV['kezdesDt']))),
            'igDt' => $_TANEV['zarasDt'],
        );
?>
