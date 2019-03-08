<?php
/*
    module: naplo
*/

    if (_RIGHTS_OK !== true) die();

    global $tools, $dt, $szabadOrak, $Hetek, $napTipusok, $napAdat, $vanTanitasiNap, $munkatervek;
    
    putNapInfo($napAdat, $munkatervek);
    napiOrakTorleseForm($napAdat, $napTipusok);
    if ($vanTanitasiNap) specialisNapForm($dt, $szabadOrak, $Hetek);
    // Csak akkor tölthetünk be órát, ha nincs óra még az adott napon
    if (count($szabadOrak) == getMaxOra() - getMinOra() + 1) orakBetolteseForm($napAdat, $Hetek);
    else orakTorleseForm($dt, $szabadOrak);
    
?>
