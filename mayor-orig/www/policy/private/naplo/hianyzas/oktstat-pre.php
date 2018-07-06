<?php
/*
    Module: naplo
*/

    if (_RIGHTS_OK !== true) die();

    if (!__TANAR && !__NAPLOADMIN && !__DIAK && !__VEZETOSEG) 
	$_SESSION['alert'][] = 'page:illegal_access';

#	require_once('include/modules/naplo/share/ora.php');
#	require_once('include/modules/naplo/share/orarend.php');
#	require_once('include/modules/naplo/share/tankor.php');
#	require_once('include/modules/naplo/share/diak.php');
#	require_once('include/modules/naplo/share/tanar.php');
#	require_once('include/modules/naplo/share/hianyzas.php');
#	require_once('include/modules/naplo/share/hianyzasModifier.php');
#	require_once('include/modules/naplo/share/szemeszter.php');
#	require_once('include/modules/naplo/share/osztaly.php');
#	require_once('include/modules/naplo/share/kepzes.php');
#	require_once('include/share/date/names.php');
#
#        require_once('skin/classic/module-naplo/html/share/hianyzas.phtml');

    $tanev = readVariable($_POST['tanev'],'numeric unsigned',__TANEV);

    $ADAT = getOktoberiStatisztika($tanev);

    $TOOL['tanevSelect'] = array('tipus' => 'cella', 'action' => 'tanevValasztas', 'post' => array('tanev'));

    getToolParameters();

?>
