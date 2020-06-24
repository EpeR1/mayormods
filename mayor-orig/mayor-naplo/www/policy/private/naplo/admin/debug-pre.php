<?php

   if (!__NAPLOADMIN) {
        $_SESSION['alert'][] = 'page:insufficient_access';
    } else {

    $ADAT['debug_result']['mayor rev'] = _MAYORREV;
    $ADAT['debug_result']['apache version'] = @apache_get_version();
    $ADAT['debug_result']['php version'] = @phpversion();
//    $ADAT['debug_result']['php info'] = get_loaded_extensions();
    $ADAT['debug_result']['sql_server']['sql_mode']=db_query('SHOW GLOBAL variables like "%sql_%"',array('modul'=>'naplo','fb'=>'debug',result=>'indexed'));
    $ADAT['debug_result']['sql_server']['sql_version']=db_query('SELECT VERSION()',array('modul'=>'naplo','fb'=>'debug',result=>'indexed'));
    $ADAT['debug_result']['tex_cli'] = @shell_exec('tex --version');
    $ADAT['debug_result']['xetex_cli'] = @shell_exec('xetex --version');
//    $ADAT['debug_result']['constants'] = @get_defined_constants();

    @ini_set('xdebug.var_display_max_depth', '3');
    @ini_set('xdebug.var_display_max_children', '4096');
    @ini_set('xdebug.var_display_max_data', '4096');

    }
?>