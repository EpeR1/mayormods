<?php

    require_once('include/modules/portal/share/hirek.php');
    require_once('include/modules/portal/share/nevnap.php');
    require_once('include/modules/portal/share/kerdoiv.php');
    require_once('include/modules/session/groupInfo.php');

    $ADAT['hirek'] = getHirek(array('tolDt'=>date('Y-m-d H:i:s'), 'igDt'=>date('Y-m-d H:i:s'),'flag'=>array(1),'class'=>array(6),'csoport'=>$AUTH['my']['categories']));
    $ADAT['kerdoiv'] = getKerdoiv(_POLICY);

    if (in_array($AUTH[_POLICY]['backend'],array('mysql','ads')) && _POLICY=='private') {
	$ADAT['hirekAdmin'] = getGroupInfo('hirekadmin','private',array('withNewAccounts' => false));
	$ADAT['vezetoseg'] = getGroupInfo('vezetoseg','private',array('withNewAccounts' => false));
	$ADAT['titkarsag'] = getGroupInfo('titkarsag','private',array('withNewAccounts' => false));
	$ADAT['useradmin'] = getGroupInfo('useradmin','private',array('withNewAccounts' => false));
	$ADAT['diakadmin'] = getGroupInfo('diakadmin','private',array('withNewAccounts' => false));
    }
    $kerdoivId = $ADAT['kerdoiv']['kerdes']['sorszam'];
    $vId = readVariable($_POST['vId'],'numeric');
    $szavazotte = szavazotte($kerdoivId);
    // Kérdőív - <<<<
    if ($action == 'szavaz' && !$szavazotte) {
        szavaz($vId,1,$kerdoivId);
	$szavazotte=true;
	$_SESSION['kerdoivSzavazott'] = true;
    }
    $ADAT['kerdoiv'] = getKerdoiv(_POLICY);
    $ADAT['kerdoiv']['szavazott'] = $szavazotte;
    // Kérdőív - >>>>

    require('skin/classic/module-portal/html/share/doboz.phtml');
    require('skin/classic/module-portal/html/share/hirek.phtml');
    require('skin/classic/module-portal/html/share/kerdoiv.phtml');

?>
