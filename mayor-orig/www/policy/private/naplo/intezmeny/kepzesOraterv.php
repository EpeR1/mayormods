<?php


    if (_RIGHTS_OK !== true) die();

    global $ADAT;

//    putKepzesInfo($ADAT);
    if ($ADAT['oraterv']==array() && count($ADAT['hasonloKepzesek']) > 1) putOratervMasolas($ADAT);
    putKepzesOraterv($ADAT);

?>
