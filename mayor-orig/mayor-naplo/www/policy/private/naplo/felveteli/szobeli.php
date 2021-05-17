<?php

    if (_RIGHTS_OK !== true) die();

    global $ADAT,$nev,$oId;

    global $EREDMENY,$nev,$oktid, $JEL;

    putEredmenyKereso($nev,'',$oId);

if (_SZOBELI_LEKERDEZHETO === true) {
//    if (is_array($EREDMENY[0])) putIrasbeliEredmeny($EREDMENY[0]);
    if (is_array($ADAT) && $ADAT['oId']!='' && (__FELVETELIADMIN===true || $nev!='')) {
	putFelvetelizoAdatok($ADAT);
//	putJelentkezes($JEL,$ADAT);
	putSzobeli($ADAT);
//	putIdeiglenesEredmeny($EREDMENY,$ADAT,$JEL);
//	$stamp = time();
//	if (
//	    __FELVETELIADMIN===true ||
//	    (strtotime(_VEGEREDMENY_PUBLIKALAS_DT)<=$stamp 
//	    && strtotime(_VEGEREDMENY_PUBLIKALAS_ENDDT)>$stamp)
//	) {
	    putVegeredmeny($ADAT);
//	}
    } elseif ($nev!='') {
	//if (count($EREDMENY)==1 && $nev!='' && (_CATEGORY=='admin' || $oktid!='') ) {
	    //putIrasbeliEredmeny($EREDMENY[0]);
	    //putFelvetelizoAdatok($ADAT);
	    //putSzobeliEredmeny($EREDMENY,$ADAT);
	//} elseif ($nev!='') {
	    putFelveteliError($nev,$oId);
	//}
    }
} else {
    // echo 'nem lekérdezhető';
}
?>
