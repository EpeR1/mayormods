<?php

function getEvfolyamJelek($SET = array('result'=>'indexed')) {
    $J = array(
'1','2','3','4','5','6','7','8',
'9N','9/N','9Ny','9/Ny','9Kny','9/Kny','9AJTP','9/AJTP','9AJKP','9/AJKP','9','10','11','12','13','14','15',
'H1','H/I','H2','H/II/1','H/II/2',
'1/8','2/9','3/10','1/9','2/10','3/11',
'1/11','2/12','1/13','2/14','3/15','4/16','5/13'
);
    if ($SET['result'] == 'idonly') return $J;
    $ret = array();
    foreach ($J as $evfolyamJel) $ret[] = array('evfolyamJel'=>$evfolyamJel);
    return $ret;
}

?>