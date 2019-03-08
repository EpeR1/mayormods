<?php
{
	if (_RIGHTS_OK !== true) die();

	global $fileName, $ADATOK, $attrList, $MEZO_LISTA;

	if ($fileName == '') {
		putFileSelectForm('naplo_intezmeny:tankor');
	} elseif ($MEZO_LISTA == '') {
		if (count($ADATOK) > 0)	putFieldSelectForm($fileName, $ADATOK, $attrList, 'naplo_intezmeny:tankor');
		else echo 'NINCS ADAT!';
	} else {
		echo 'Adatfeldolgozás kész.';
	}
}
?>
