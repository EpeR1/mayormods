<?php

    if (_RIGHTS_OK !== true) die();

    global $EREDMENY,$ADATOK,$SZOBELI,$nev,$oktid;

    putEredmenyKereso($nev,$diak,$oktid);

if (_LEKERDEZHETO) {
    if (_IDOSZAK==='irasbeli' && is_array($EREDMENY[0])) putIrasbeliEredmeny($EREDMENY[0]);
    elseif(_IDOSZAK==='ideiglenes' && is_array($ADATOK)) {
	putFelvetelizoAdatok($ADATOK);
	putIdeiglenesEredmeny($EREDMENY,$ADATOK);
    }
    elseif(_IDOSZAK==='vegeredmeny' && is_array($ADATOK)) {
        putFelvetelizoAdatok($ADATOK);
        putVegeredmeny($ADATOK);
    }
    elseif (_IDOSZAK==='szobeliEredmeny' && is_array($ADATOK)) {
        putFelvetelizoAdatok($ADATOK);
	putSzobeliEredmeny($SZOBELI,$ADATOK);
    }

/*
    if (is_array($ADATOK)) {
	putFelvetelizoAdatok($ADATOK);
	//if ($ADATOK['evfolyam']!='hat') 
	if (_IDOSZAK==='szobeliEredmeny') putSzobeliEredmeny($SZOBELI,$ADATOK);
	$stamp = time();
	if ( _IDOSZAK==='vegeredmeny') {
	    putVegeredmeny($ADATOK);
	} elseif (_IDOSZAK==='ideiglenesEredmeny') {

	}
    } elseif ($nev!='') {
	//if (count($EREDMENY)==1 && $nev!='' && (_CATEGORY=='admin' || $oktid!='') ) {
	//    putIrasbeliEredmeny($EREDMENY[0]);
	    //putFelvetelizoAdatok($ADATOK);
	    //putSzobeliEredmeny($EREDMENY,$ADATOK);
	//} elseif ($nev!='') {
	// putFelveteliError();
	//}
    }
*/

}
?>
