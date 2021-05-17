<?php

if (_RIGHTS_OK!==true) die();


global $ADAT,$ADATASSOC;

if (__NAPLOADMIN===true) {


    putDokumentumLista($ADATASSOC);
    putDokumentumListaAdmin($ADAT, true);
    putDokumentumAdmin($ADAT);

    echo '<iframe src="policy/private/naplo/dokumentum/tinyfilemanager.php" style="width:100%; height:800px; border: solid 1px #eee;"></iframe>';
} else {

    putDokumentumLista($ADATASSOC);

}
?>
