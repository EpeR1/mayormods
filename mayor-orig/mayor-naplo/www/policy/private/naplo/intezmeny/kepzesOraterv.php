<?php


    if (_RIGHTS_OK !== true) die();

    global $ADAT;

    // putKepzesInfo($ADAT);
    if (__NAPLOADMIN ===true && $ADAT['oraterv']==array() && count($ADAT['hasonloKepzesek']) > 1)  putOratervMasolas($ADAT);
    if ($ADAT['kepzesId']>0) putKepzesOraterv($ADAT);

?>
