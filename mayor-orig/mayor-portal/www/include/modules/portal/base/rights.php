<?php

    if (_POLICY=='private' && @memberOf(_USERACCOUNT, 'hirekadmin')) {
        $AUTH['my']['categories'][] = 'hirekadmin';
        define('__HIREKADMIN', true);
        define('_FILEMANAGER_ENABLED', true);
    } else {
        define('__HIREKADMIN', false);
        define('_FILEMANAGER_ENABLED', false);
    }

?>
