<?php

    if (_RIGHTS_OK !== true) die();
    require_once('include/modules/portal/share/hirek.php');
    $FILTER=array('tolDt'=>date('Y-m-d H:i:s'), 'igDt'=>date('Y-m-d H:i:s'),'flag'=>array(1),'class'=>array(1,6));
    $ADAT['hirek'] = getHirek($FILTER);

?>
