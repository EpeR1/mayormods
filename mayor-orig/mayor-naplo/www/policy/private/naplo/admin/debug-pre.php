<?php

if (__NAPLOADMIN!==true) {
        $_SESSION['alert'][] = 'page:insufficient_access';
} else {

    $ADAT['debug_result']['mayor rev'] = _MAYORREV;
    $ADAT['debug_result']['apache version'] = $_SERVER['SERVER_SOFTWARE'];
    $ADAT['debug_result']['php version'] = (function_exists('phpversion') ? phpversion() : '');
    $ADAT['debug_result']['sql_server']['sql_mode']=db_query('SHOW GLOBAL variables like "%sql_%"',array('modul'=>'naplo','fb'=>'debug',result=>'indexed'));
    $ADAT['debug_result']['sql_server']['sql_version']=db_query('SELECT VERSION()',array('modul'=>'naplo','fb'=>'debug',result=>'indexed'));
    $ADAT['debug_result']['tex_cli'] = (function_exists('shell_exec') ? shell_exec('tex --version') : '');
    $ADAT['debug_result']['xetex_cli'] = (function_exists('shell_exec') ? shell_exec('xetex --version'): '');
    //security!    $ADAT['debug_result']['constants'] = get_defined_constants();

    ini_set('xdebug.var_display_max_depth', '3');
    ini_set('xdebug.var_display_max_children', '4096');
    ini_set('xdebug.var_display_max_data', '4096');

    $ADAT['debug_result']['post_max_size'] = ini_get('post_max_size');

    $ADAT['debug_result']['config']['backend'] = $config['backend'];
    $ADAT['debug_result']['config']['MYSQLI_ENABLED'] = MYSQLI_ENABLED;
    $ADAT['debug_result']['config']['__PORTAL_CODE'] = __PORTAL_CODE;
    $ADAT['debug_result']['config']['__EMAIL_ENABLED'] = __EMAIL_ENABLED;

}
?>