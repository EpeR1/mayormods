<?php

    if (_RIGHTS_OK !== true) die();

    $action = readVariable($_POST['action'],'strictstring',array('lekerdezes'));
    $stamp = mktime();

    if (strtotime(_IRASBELI_PUBLIKALAS_DT)<=$stamp && strtotime(_IRASBELI_PUBLIKALAS_ENDDT)>$stamp)
	define('_IDOSZAK','irasbeli');
    elseif (strtotime(_IDEIGLENES_PUBLIKALAS_DT)<=$stamp && strtotime(_IDEIGLENES_PUBLIKALAS_ENDDT)>$stamp)
	define('_IDOSZAK','ideiglenes');
    elseif (strtotime(_VEGEREDMENY_PUBLIKALAS_DT)<=$stamp && strtotime(_VEGEREDMENY_PUBLIKALAS_ENDDT)>$stamp)
        define('_IDOSZAK','vegeredmeny');
    elseif (strtotime(_SZOBELI_PUBLIKALAS_DT)<=$stamp && strtotime(_SZOBELI_PUBLIKALAS_ENDDT)>$stamp)                                                                                        
        define('_IDOSZAK','szobeliEredmeny');
    else
	define('_IDOSZAK',false);

    if 	(    _CATEGORY == 'admin' || __FELVETELIADMIN===true ||
	    (strtotime(_IRASBELI_PUBLIKALAS_DT)<=$stamp
	    &&
	     strtotime(_IRASBELI_PUBLIKALAS_ENDDT)>$stamp
	    ) ||
	    (strtotime(_SZOBELI_PUBLIKALAS_DT)<=$stamp
	    &&
	     strtotime(_SZOBELI_PUBLIKALAS_ENDDT)>$stamp
	    ) ||
	    (strtotime(_VEGEREDMENY_PUBLIKALAS_DT)<=$stamp
	    &&
	     strtotime(_VEGEREDMENY_PUBLIKALAS_ENDDT)>$stamp
	    ) ||
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
	$an = readVariable($_POST['an'],'sql');
	$szuldt = readVariable($_POST['szuldt'], 'date');
        if (
	    __FELVETELIADMIN==true 
	    || ($nev != '' 
		&& ($oktid !='' || ($szuldt != '' && $an != ''))
	    )
	) {
	    if (_IDOSZAK!='irasbeli') $ADATOK = getFelvetelizoAdatok($nev,$oktid); // ez név nélkül nem ad vissza eredményt - adminnak sem...
	    if (is_array($ADATOK)) {
		if (_IDOSZAK==='szobeliBeosztas') $SZOBELI = getSzobeli(intval($ADATOK['id']));
		if (_IDOSZAK==='ideiglenes') $EREDMENY = getIdeiglenesRangsor(intval($ADATOK['oktid']));
	    }
//2012//	    if (_IDOSZAK==='szobeliEredmeny') $EREDMENY = getSzobeliEredmeny($ADATOK['id']);
	    if (_IDOSZAK==='szobeliEredmeny') $SZOBELI = getSzobeli($ADATOK['id']);
	    if (_IDOSZAK==='irasbeli') $EREDMENY = getIrasbeliEredmeny($nev,$oktid, $an, $szuldt); // ha nincs oktid, akkor a másik kettő...
	    if (_IDOSZAK==='vegeredmeny' && $ADATOK['oktid']!='') {
		// token generálás
		$ADATOK['token'] = updateLevelToken($ADATOK['oktid']);
	    }
	}
    }

?>
