<?php

    if (_POLICY=='private' && @memberOf(_USERACCOUNT, 'hirekadmin')) {
        $AUTH['my']['categories'][] = 'hirekadmin';
        define('__HIREKADMIN',true);
    } else {
        define('__HIREKADMIN',false);
    }

?>
