<?php

    /* Ezen hibákat továbbra is reportoljuk */
    error_reporting(E_ERROR | E_PARSE);

    /* Saját Error Handler */
    function mayorErrorHandler($errno, $errmsg, $filename, $linenum, $vars) {

	$dt = date("Y-m-d H:i:s (T)");
	$errortype = array (
                E_ERROR              => 'Error', //1
                E_WARNING            => 'Warning', //2
                E_PARSE              => 'Parsing Error', //4
                E_NOTICE             => 'Notice', //8
                E_CORE_ERROR         => 'Core Error', //16
                E_CORE_WARNING       => 'Core Warning', //32
                E_COMPILE_ERROR      => 'Compile Error', //64
                E_COMPILE_WARNING    => 'Compile Warning', //128
                E_USER_ERROR         => 'User Error',
                E_USER_WARNING       => 'User Warning',
                E_USER_NOTICE        => 'User Notice', // 1024
                E_STRICT             => 'Runtime Notice', // 2048
                E_RECOVERABLE_ERROR  => 'Catchable Fatal Error', // 4096
		E_ALL		     => 'ALL', //binary 1111111111111
                );
	$userError = array(E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE);
	$trackOnly = array(E_USER_ERROR, E_ERROR, E_USER_WARNING, E_USER_NOTICE, E_WARNING);
	if (defined('__TESTERRORREPORTERWARN')) $trackOnly[] = E_NOTICE;
	if (in_array($errno,$userError)) {
	    $err = "$dt $errno ".$errtype[$errno]." $errmsg";
	} elseif (in_array($errno,$trackOnly)) {
	    $err = "$dt $errno ".$errtype[$errno]." $errmsg $filename $linenum";
	    /* if (in_array($errno, $user_errors)) { $err .= " vartrace(" . wddx_serialize_value($vars, "Variables") . ") ";} */
	    // if (defined('_LOGDIR')) error_log($err, 0, _LOGDIR.'/phperror.log');
	} 
	if ($err!='') $_SESSION['alert'][] = 'alert:raw:'._MAYORREV.':'._USERACCOUNT.':'.':'.$err;
	return false;
    }

    /* Írjuk felül a gyárit */
    $old_error_handler = set_error_handler("mayorErrorHandler");
    //restore_error_handler();

?>
