<?php

    if (_RIGHTS_OK !== true) die();
    require_once('include/modules/naplo/share/szulo.php');

    $diakId = readVariable($_POST['diakId'],'id',null);
    $SZULODIAKJAI = getSzuloDiakjai();

    if (!is_array($SZULODIAKJAI) || count($SZULODIAKJAI)==0) {
	$_SESSION['alert'][] = ':!E12:nincs_egy_diakja_se:'._USERACCOUNT.':'._POLICY;
    }
    if ($action == 'valaszt') {
	for ($i=0; $i<count($SZULODIAKJAI); $i++) {
	    if ($SZULODIAKJAI[$i]['diakId']==$diakId) {
		updateSessionParentDiakId($diakId) ;
//		setcookie(_SESSIONID.'-diakIdOk', true, 0, '/');
		$_SESSION['szuloDiakIdOk']=true;
		if ($DEFAULT_PSF['parent']['f'] != 'diakValaszto' || $DEFAULT_PSF['parent']['page'] != 'naplo')
		    header('Location: '.location('index.php?page='.$DEFAULT_PSF['parent']['page'].'&sub='.$DEFAULT_PSF['parent']['sub'].'&f='.$DEFAULT_PSF['parent']['f']));
		elseif ($DEFAULT_PSF['parent']['f'] == 'diakValaszto') 
		    header('Refresh:0'); // a konstans előbb definiálódik
		break;
	    }
	}
	//----
    } elseif (count($SZULODIAKJAI)==1) {
	    updateSessionParentDiakId($SZULODIAKJAI[0]['diakId']) ;
//	    setcookie(_SESSIONID.'-diakIdOk', true, 0, '/');
	    $_SESSION['szuloDiakIdOk']=true;
	    if ($DEFAULT_PSF['parent']['f'] != 'diakValaszto' || $DEFAULT_PSF['parent']['page'] != 'naplo')
		header('Location: '.location('index.php?page='.$DEFAULT_PSF['parent']['page'].'&sub='.$DEFAULT_PSF['parent']['sub'].'&f='.$DEFAULT_PSF['parent']['f']));
    }

?>
