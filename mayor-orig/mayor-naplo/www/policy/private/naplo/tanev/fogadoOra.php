<?php

    if (_RIGHTS_OK !== true) die();

    global $FogadoDt, $tanarId, $tanarFogado, $Tanarok, $FogadoOsszes, $Termek, $TermekAsszoc, $diakTanarai, $Alkalmak, $szuloId;
    global $szuloJelentkezes, $Szulok, $Lista;

    if (__NAPLOADMIN===true || __VEZETOSEG===true) {
	if (count($FogadoDt['dates']) > 0 && isset($tanarId)) {
	    tanarFogadoIdopontModosito($tanarFogado['adatok'], $Termek);
	} elseif (is_array($Lista)) {
	    putFogadoOraLista($Lista, $Tanarok, $Szulok);
	} else {
	    kovetkezoFogadoIdopont($tanarFogado, $Termek);
	    if (is_array($FogadoOsszes)) putFogadoOsszes($FogadoOsszes, $Tanarok);
	}

    }
    if ((__TANAR===true || _TITKARSAG===true) && count($FogadoDt['dates']) > 0 && isset($tanarId)) {
	tanarFogadoIdopont($tanarFogado, $Termek, $Szulok);
    }
    if (__DIAK===true) { // Szülő - jelentkezés
	putFogadoOraJelentkezes($szuloId, $diakTanarai, $Alkalmak, $szuloJelentkezes, $TermekAsszoc);
    }
							    
?>
