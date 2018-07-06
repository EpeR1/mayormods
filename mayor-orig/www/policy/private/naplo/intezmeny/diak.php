<?php

    if (_RIGHTS_OK !== true) die();
    if (!__NAPLOADMIN && !__VEZETOSEG && !__TITKARSAG && !__TANAR && !__DIAK) {
	return;
    }

    global $fileName, $ADATOK, $MEZO_LISTA, $Szulok, $tanev, $magantanuloUtkozes, $tolDt, $igDt, $ADAT;
    global $osztalyId,$diakId;


    if (is_array($ADAT['diakAdat']) && $skin!='ajax') {
	putBizonyitvanyTorzslap($ADAT);
	putDiakAdatForm($ADAT, $Szulok, $tanev);
	if (_MODOSITHAT===true) {
	    putDiakJogviszonyValtozas($ADAT); //['diakAdat']
	    if ($ADAT['diakAdat']['statusz']=='felvételt nyert') putDiakTorol($ADAT);
	}
	if (__NAPLOADMIN && __ALLOW_SULIX_REST===true) putSuliXRESTForm($ADAT);

    } elseif (_MODOSITHAT) {
	// Új diák felvétele
	putUjDiak($ADAT);
    }
    if (_MODOSITHAT===true || _VEZETOSEG===true) {
	putDiakExportForm($ADAT);
    }


?>
