<?php

    if (_RIGHTS_OK !== true) die();

    global $EREDMENY,$ADATOK,$SZOBELI,$nev,$oktid, $JEL;


    putEredmenyKereso($nev,$diak,$oktid);

if (_LEKERDEZHETO) {
//    if (is_array($EREDMENY[0])) putIrasbeliEredmeny($EREDMENY[0]);
    if (is_array($ADATOK) && (__FELVETELIADMIN===true || $nev!='')) {
	putFelvetelizoAdatok($ADATOK);
	//if ($ADATOK['evfolyam']!='hat') 
//	putJelentkezes($JEL,$ADATOK);
//	putSzobeliEredmeny($SZOBELI,$ADATOK);
	putIdeiglenesEredmeny($EREDMENY,$ADATOK,$JEL);
	$stamp = time();
	if (
	    strtotime(_VEGEREDMENY_PUBLIKALAS_DT)<=$stamp 
	    && strtotime(_VEGEREDMENY_PUBLIKALAS_ENDDT)>$stamp
	) {
	    putVegeredmeny($ADATOK);
	}
    } elseif ($nev!='') {
	//if (count($EREDMENY)==1 && $nev!='' && (_CATEGORY=='admin' || $oktid!='') ) {
	//    putIrasbeliEredmeny($EREDMENY[0]);
	    //putFelvetelizoAdatok($ADATOK);
	    //putSzobeliEredmeny($EREDMENY,$ADATOK);
	//} elseif ($nev!='') {
	    putFelveteliError();
	//}
    }

}
?>
