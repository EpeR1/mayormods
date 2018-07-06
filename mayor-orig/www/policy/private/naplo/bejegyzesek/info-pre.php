<?php


    require_once('include/modules/naplo/share/bejegyzes.php');
    if (__DIAK) $diakId = $ADAT['diakId'] = __USERDIAKID;

    $ADAT['tolDt'] = date('Y-m-d 00:00:00', strtotime('-7 days'));
    if (isset($ADAT['diakId'])) $ADAT['db'] = getDarabBejegyzes($ADAT);

?>
