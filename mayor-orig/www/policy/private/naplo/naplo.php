<?php

    if (_RIGHTS_OK !== true) die();

    global $beirasiAdatok;
    // if (__TANAR) putBeirasiAdatok($beirasiAdatok);
    // else putBeirasiAdatokDiak();

    echo '<script type="text/javascript">includeCSS(\'/skin/classic/module-naplo/css/hirnok/hirnok.css\')</script>';
    echo ajaxUpdaterForm('hirnok','index.php?page=naplo&sub=hirnok&f=hirnok',array(),'post',true);

    echo '<script type="text/javascript">includeCSS(\'/skin/classic/module-naplo/css/uzeno/uzeno.css\')</script>';
    echo ajaxUpdaterForm('uzenoKozep','index.php?page=naplo&sub=uzeno&f=uzeno',array(),'post',true);

?>
