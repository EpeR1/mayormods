<?php

    if (_RIGHTS_OK !== true) die();

    global $fileName, $ADATOK, $MEZO_LISTA, $attrList;
    global $ADAT;

    if (is_array($ADAT) && $skin != 'ajax') {

	putTanarAdatForm($ADAT);

    } else {
	putUjTanar();
        if ($fileName == '') {
                putFileSelectForm('naplo_intezmeny:tanar',array('mkId','tanev'));
        } elseif ($MEZO_LISTA == '') {
            if (count($ADATOK) > 0)
                putFieldSelectForm($fileName, $ADATOK, $attrList, 'naplo_intezmeny:tanar',array('mkId','tanev'));
            else
                echo 'NINCS ADAT!';
        } else {
            echo 'Adatfeldolgozás kész.';
        }
    }


?>
