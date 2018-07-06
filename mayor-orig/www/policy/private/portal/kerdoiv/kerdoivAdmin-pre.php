<?php

    if (__HIREKADMIN) {

	require_once('include/modules/portal/share/kerdoiv.php');

	if ($action == 'ujKerdes') {
	    addKerdoiv($_POST['kerdes'],$_POST['valaszok']);
	} elseif ($action == 'delKerdes') {
	    $sorszamok = (readVariable($_POST['sorszam'],'id'));
	    if (is_array($sorszamok)) delKerdoivKerdes($sorszamok);
	}
	$ADAT = getRegiKerdesek();

    }

?>
