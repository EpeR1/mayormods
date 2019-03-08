<?php

    if (_RIGHTS_OK !== true) die();

    $action = readVariable($_POST['action'],'strictstring',array('lekerdezes'));
    $stamp = mktime();
    if 	(    _CATEGORY == 'admin' || __FELVETELIADMIN===true ||
	    (strtotime(_SZOBELI_PUBLIKALAS_DT)<=$stamp
	    &&
	     strtotime(_SZOBELI_PUBLIKALAS_ENDDT)>$stamp
	    )
||
	    (strtotime(_IDEIGLENES_PUBLIKALAS_DT)<=$stamp
	    &&
	     strtotime(_IDEIGLENES_PUBLIKALAS_ENDDT)>$stamp
	    )
	) {
	    define('_LEKERDEZHETO',true);
    } else {
	    define('_LEKERDEZHETO',false);
    }

    if ($action=='lekerdezes' && _LEKERDEZHETO === true) {
	$nev = readVariable($_POST['nev'],'sql');
	$oktid =  readVariable($_POST['oktid'],'strictstring');
        if (__FELVETELIADMIN==true || $oktid !='') {
	    $ADATOK = getFelvetelizoAdatok($nev,$oktid);
	    if (is_array($ADATOK)) {
		$SZOBELI = getSzobeliByOktid(intval($ADATOK['oktid']));
		$EREDMENY = getIdeiglenesRangsor(intval($ADATOK['oktid']));
		//$JEL = getJelentkezes(intval($ADATOK['id']));
	    }
	    //$EREDMENY = getSzobeliEredmeny($ADATOK['id']);
	    //$EREDMENY = getIrasbeliEredmeny($nev,$oktid);
	}
    }

?>
