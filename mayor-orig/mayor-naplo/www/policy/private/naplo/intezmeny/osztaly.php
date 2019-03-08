<?php

    if (_RIGHTS_OK !== true) die();

    global $ADAT, $fileName, $ADATOK, $MEZO_LISTA, $attrList;

	// Osztályhoz rendelés
	if (isset($ADAT['osztalyId']) && ($ADAT['osztalyAdat']["kezdoTanev"]<=$ADAT['tanev'] && $ADAT['osztalyAdat']["vegzoTanev"]>=$ADAT['tanev'])) {
		
	    putOsztalyAdatokForm($ADAT);

	    if ($ADAT['osztalyJellegek'][ $ADAT['osztalyAdat']['osztalyJellegId'] ]['kovOsztalyJellegId'] != 0) { // NyEK évfolyam
		putOsztalyLeptetes($ADAT);
	    }
	    if (is_array($ADAT['osztalyNevsor'])) {
        	putOsztalyNevsor($ADAT);
		putTagTorlesForm($ADAT);
    	    }
    	    putUjTagForm($ADAT);

	    if (__NAPLOADMIN) {
    		if ($fileName == '') {
            	    putFileSelectForm('naplo_intezmeny:diak',array('osztalyId','tanev'));
    		} elseif ($MEZO_LISTA == '') {
        	    if (count($ADATOK) > 0)
            		putFieldSelectForm($fileName, $ADATOK, $attrList, 'naplo_intezmeny:diak',array('osztalyId','tanev'));
        	    else
            		echo 'NINCS ADAT!';
    		} else {
        	    echo 'Adatfeldolgozás kész.';
		}
    		// osztalyTorlesForm($ADAT['osztalyId']);
    	    }
	} elseif (__NAPLOADMIN) {
		putUjOsztalyForm($ADAT);
	}



?>
