<?php

    global $ADAT;

//    if (count($_SESSION['alert'])==0)
//    if (!in_array('info:nincs_intervallum', $_SESSION['alert']) && is_array($ADAT['orarend']) && count($ADAT['orarend']) != 0) putOrarend($ADAT);
    if (
	(!is_array($_SESSION['alert']) || !in_array('info:nincs_intervallum', $_SESSION['alert'])) 
	&& is_array($ADAT['felvehetoTankorok']) 
	&& count($ADAT['felvehetoTankorok']) > 0
    ) 
	putOrarend($ADAT);
    // if ($ADAT['orarendiOra']) putOrarendLebego($ADAT);

    if (count($ADAT['felvehetoTankorok']) === 0) {
	echo 'Nincs felvehető tanköre a megadott tanárnak!';
    }

?>
