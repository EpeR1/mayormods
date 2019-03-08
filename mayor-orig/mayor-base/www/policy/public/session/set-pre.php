<?php

    if (_RIGHTS_OK !== true) die();

    global $_JSON;
    $layout = readVariable($_GET['layout'],'id',0);
    $_SESSION['pageLayout'] = $layout;
    $_JSON['success'] = true;
    $_JSON['pageLayout'] = $layout;
?>
