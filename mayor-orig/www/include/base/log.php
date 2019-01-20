<?php
/*
    Module: base
    
    function naploz($aCode)
    function szamlal($policy,$page)
*/
    define('CLIENTIPADDRESS',_clientIp());
    function _clientIp() {
        return ($_SERVER['HTTP_X_FORWARDED_FOR']!='')?$_SERVER['HTTP_X_FORWARDED_FOR']:$_SERVER['REMOTE_ADDR'];
    }

    function logLogin($policy, $userAccount, $flag) { //loginLog
	$q = "INSERT INTO loginLog (dt,ip,userAccount,policy,flag) VALUES (NOW(),'%s','%s','%s', %u)";
	db_query($q, array('fv' => 'logLogin', 'modul' => 'login', 'values' => array(_clientIp(), $userAccount, $policy, $flag)));
    }

    function szamlal($policy, $page) {
	$q = "INSERT INTO stat (dt, policy, page) VALUES (NOW(),'%s','%s')";
	db_query($q, array('fv' => 'szamlal', 'modul' => 'login', 'values' => array($policy, $page)));
    }

    function mayorLogger($loglevel, $modul, $message, $userAccount='') {
	if ($loglevel>_LOGLEVEL) {
	    $fp = fopen(_LOGDIR.'/'.$modul.'.log','a+');
	    if ($fp!=false) {
		$msg = date('Y-m-d H:i:s').' '.$userAccount.': '.$message."\n";
		fputs($fp,$msg);
		fclose($fp);
	    }
	}
    }
?>
