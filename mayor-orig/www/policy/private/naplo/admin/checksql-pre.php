<?php
                                                                                                                                    
    if (_RIGHTS_OK !== true) die();                                                                                                  
    if (__NAPLOADMIN !== true) {                                                                                                             
        $_SESSION['alert'][] = 'page:insufficient_access';                                                                                       
    } else {      
	/* Attach shared lib */
	require_once('include/modules/naplo/share/szemeszter.php');                                                                  
        require_once('include/modules/naplo/share/file.php');

	$Q_ERR = array();
	$queryFile = fileNameNormal(__INTEZMENY_DB_FILE); // __ALAP|__TANEV
	$db = 'naplo_intezmeny';
	checkSqlConsistency($queryFile,$db,$Q_ERR);

	$queryFile = fileNameNormal(__TANEV_DB_FILE); // __ALAP|__TANEV
	$db = 'naplo';
	checkSqlConsistency($queryFile,$db,$Q_ERR);

    }

?>
