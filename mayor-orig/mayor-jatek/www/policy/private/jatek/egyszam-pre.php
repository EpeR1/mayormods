<?php


    if (!($ADAT['szavazott'] = egyszamSzavazott()) && $action=='szavaz') {

	$het  = getEgyszamHet();
	$szam = readVariable($_POST['szam'],'numeric');
	if ($szam>0 && $szam<200) {
	    egyszamszavaz($het,$szam);
	} // különben hibás számra akart szavazni. XSS??
	$ADAT['szavazott'] = true;

    }

    $ADAT['db'] = count(getEgyszamEredmeny(getegyszamHet()));
    $ADAT['elozoNyertes'] = getEgyszamNyertes(getegyszamHet()-1);
    
?>
