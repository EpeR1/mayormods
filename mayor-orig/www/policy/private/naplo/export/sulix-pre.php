<?php

    if (_RIGHTS_OK !== true) die();

    if (!__NAPLOADMIN) {
        $_SESSION['alert'][] = 'message:insufficient_access';
    } else {

	require_once('include/modules/naplo/share/diak.php');
	require_once('include/modules/naplo/share/tanar.php');
	require_once('include/modules/naplo/share/intezmenyek.php');
	require_once('include/modules/session/search/searchAccount.php');
	require_once('include/modules/naplo/share/file.php');

	if ($action == 'sulixExport') {
    	    $ADAT['diak'] = getDiakAccounts();
	    $ADAT['tanar'] = getTanarAccounts();
	    if (createTGZ($ADAT)) {
		header('Location: '.location('index.php?page=session&f=download&download=true&dir=naplo/export/sulix&file=mayor2sulix.tgz&mimetype=text/csv'));
	    }
	}


    }

?>