<?php

  if (_RIGHTS_OK !== true) die();
    require_once('include/modules/portal/share/hirek.php');
    $ADAT['hirek'] = getHirek(array('tolDt'=>date('Y-m-d'), 'igDt'=>date('Y-m-d'),'flag'=>array(1)));

?>
