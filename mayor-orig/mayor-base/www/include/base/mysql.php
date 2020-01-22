<?php

    error_reporting(E_ALL && ~E_NOTICE);
//    error_reporting(E_ALL);

    //if (!defined("MYSQLI_ENABLED")) define("MYSQLI_ENABLED",function_exists('mysqli_connect'));
    if (!defined("MYSQLI_ENABLED")) define("MYSQLI_ENABLED",false); // egyelőre kikapcsoljuk, mert az Illyés-ben pl egfeküdt a szerver ettől
    if (is_array($AUTH)) foreach ($AUTH as $_policy => $config) {
	if ($config['backend'] == 'mysql') {
	    $MYSQL_DATA[$_policy . ' auth'] = array(
		'user' => $config['mysql user'],
		'host' => $config['mysql host'],
		'pw' => $config['mysql pw'],
		'db' => $config['mysql db'],
	    );
	    if ($MYSQL_DATA[$_policy . ' auth']['host']=='') 
		if ($MYSQL_DATA['host']=='')
		    $MYSQL_DATA[$_policy . ' auth']['host'] = 'localhost';
		else
		    $MYSQL_DATA[$_policy . ' auth']['host'] = $MYSQL_DATA['host'];
	}
    }    
//-----------------------------------------------------------------------//

    function db_selectDb($db,$lr) {
	if (MYSQLI_ENABLED===true)
	    return mysqli_select_db($lr,$db);
	else
	    return mysql_select_db($db,$lr);
    }

    function db_connect($modul, $SET = array('priv' => null, 'force' => true, 'host'=> null, 'username' => null, 'password' => null, 'db' => null, 'fv' => null)) {

	global $MYSQL_DATA;

	$lr = false;
	extract($SET);
	if (!isset($force)) $force = true;
	if (!isset($priv))  $priv = '';

	//if ($MYSQL_DATA['persistent']===true) $mysql_connect = 'mysql_pconnect'; else $mysql_connect = 'mysql_connect';
	$mysql_connect = 'mysql_connect';
	/* setting host */
	if (!isset($host)) {
	    if ($MYSQL_DATA[$modul]['host'.$priv]=='') {
		if ($MYSQL_DATA[$modul]['host']=='') {
		    if ($MYSQL_DATA['host']=='') {
			$host = 'localhost';
		    } else {
			$host = $MYSQL_DATA['host'];
		    }
		} else {
		    $host = $MYSQL_DATA[$modul]['host'];
		}
	    } else {
		$host = $MYSQL_DATA[$modul]['host'.$priv];
	    }
	}
	/* --- */
	if ($priv == 'root') {
	    if (MYSQLI_ENABLED===true)
		$lr = @mysqli_connect($host, $username, $password); // force new ???
	    else
		$lr = @$mysql_connect($host, $username, $password, $force);
	} else {
	    /* --- */
	    if (is_array($MYSQL_DATA[$modul])) {
		if (isset($priv) && $priv != '' && isset($MYSQL_DATA[$modul]['user'.$priv])) { // Először megpróbálunk $priv szerinti privilégiummal csatlakozni
		    if (MYSQLI_ENABLED===true)
			$lr = @mysqli_connect($host, $MYSQL_DATA[$modul]['user'.$priv], $MYSQL_DATA[$modul]['pw'.$priv], $MYSQL_DATA[$modul]['db']);
		    else
			$lr = @$mysql_connect($host, $MYSQL_DATA[$modul]['user'.$priv], $MYSQL_DATA[$modul]['pw'.$priv], $force);
		}
		if ($lr === false) {// Ha nem sikerült, vagy nem volt megadva privilégium, akkor próbáljunk anélkül csatlakozni
		    if (MYSQLI_ENABLED===true)
			$lr = @mysqli_connect($host, $MYSQL_DATA[$modul]['user'], $MYSQL_DATA[$modul]['pw'], $MYSQL_DATA[$modul]['db']);
		    else
			$lr = @$mysql_connect($host, $MYSQL_DATA[$modul]['user'], $MYSQL_DATA[$modul]['pw'], $force);
		}
	    } else {
		$_SESSION['alert'][] = "message:sql_failure/${SET['fv']}:db_connect:modul $modul has no config";
	    }
	}
	if ($lr) {
	    // mysql_set_charset('utf8', $lr);
	    if ($priv != 'root') $db = $MYSQL_DATA[$modul]['db'];
	    if ($db != '') {
		$result = db_selectDb($db , $lr);
		if ($result === true) {
		    if (MYSQLI_ENABLED===true) {
			mysqli_set_charset($lr, "utf8");
			mysqli_query($lr, "SET NAMES utf8");
			mysqli_query($lr, "SET collation_connection='utf8_hungarian_ci'");
		    } else {
			mysql_query("SET NAMES utf8", $lr);
			mysql_query("SET collation_connection='utf8_hungarian_ci'", $lr);
		    }
		} else {
		    $_SESSION['alert'][] = "message:sql_select_db_failure:db_connect/${SET['fv']}:$modul:".$MYSQL_DATA[$modul]['db'];
		    mysql_close($lr);
		    return false;
		}
	    } elseif (!isset($MYSQL_DATA[$modul]['db'])) {
		$_SESSION['alert'][] = "message:sql_warning:db_connect/${SET['fv']}:modul $modul has empty database config value";
	    }
	} else {
	    if (__DEBUG || (defined('__DETAILED') && __DETAILED)) $_SESSION['alert'][] = "message:sql_connect_failure:db_connect/${SET['fv']}:host - $host, modul - $modul, priv - $priv, username - $username, db - $db";
	    else $_SESSION['alert'][] = "message:sql_connect_failure:db_connect/${SET['fv']}:$modul modul";
	}
	return $lr;

    }

    function db_close($lr) {
	if ($MYSQL_DATA['persistent']!==true) {
	    if (MYSQLI_ENABLED===true)
		return mysqli_close($lr);
	    else
		return mysql_close($lr);
	} else
	    return true; // not closing;
    }

//-----------------------------------------------------------------------//

    function db_query($q, $SET, $olr = null) {
	/*
	    $SET = array(
		'modul' => '...'
		'fv' => '...'
		'result' => 'indexed'|'assoc'|'multiassoc'|'idonly'|'value'|'record'|'keyvaluepair'|'insert'|'affected rows'
		'keyfield' => '...' (if result in (assoc,multiassoc))
		'detailed' => true | false (default) (echo the query in error messages)
		'debug' => true | false (default) (echo the query before quering it)
		'rollback' => true | false (default)
		'values' => array(...) (if $q is an sprintf format string)
	    );
	*/
	global $_JSON;

	if (!isset($q) || $q == '') {
            $_SESSION['alert'][] = 'message:sql_query_failure:db_query/'.$SET['fv'].':query is empty';
            return false;
        }

	if (!isset($SET['result'])) $SET['result'] = '';

	// Adatbázis csatlakozás (ha szükséges)
	if (isset($olr) && $olr != '') {
	    if (MYSQLI_ENABLED===true)
		$mysql_get_server_info = mysqli_get_server_info($olr);
	    else
		$mysql_get_server_info = @mysql_get_server_info($olr);
	}
        if (isset($olr) && $olr != '' && $mysql_get_server_info !== false) {
            $lr = $olr;
        } else {
	    unset($olr);
            if (in_array(substr(strtolower($q),0,4), array('sele','show','expl','set ','use '))) $lr = @db_connect($SET['modul'], array('priv' => 'Read', 'fv' => $SET['fv']));
            else $lr = @db_connect($SET['modul'], array('priv' => 'Write', 'fv' => $SET['fv']));
        }
        if ($lr === false) {
            if ($SET['detailed'] === true || __DETAILED) $_SESSION['alert'][] = 'message:sql_connect_failure:db_query/'.$SET['fv'].':'.$SET['modul'].':'.$q;
	    else $_SESSION['alert'][] = 'message:sql_connect_failure:db_query/'.$SET['fv'];
            return false;
        }

	// Ha behelyettesítendő paraméterek vannak
	if (isset($SET['values']) && is_array($SET['values']) && count($SET['values']) > 0) {
	    $SET['values'] = array_map('db_escape_string', $SET['values'], array_fill(0 , count($SET['values']), $lr));
	    array_unshift($SET['values'], $q);
	    $q_pattern = $q;
	    $q = @call_user_func_array('sprintf', $SET['values']);
	    if ($q === false) {
		$_SESSION['alert'][] = 'message:wrong_data:db_query:behelyettesítés:'.$SET['fv'];
		return false;
	    }
	}
	if ((isset($SET['debug']) && $SET['debug']===true) || (defined('__DEBUG') && __DEBUG === true)) {
	    $_q = str_replace("	",'',$q);
	    echo '<pre>info:debug:'.htmlspecialchars($SET['modul'].':'.$SET['fv'].':'.date('Y-m-d H:i:s').': '." \n".$_q)."<hr /></pre>";
	    if ($_GET['skin'] == 'ajax') $_JSON['sql log'][] = $SET['modul'].':'.$SET['fv'].':'.date('Y-m-d H:i:s').': '." \n".$_q;
	    if ($_GET['skin'] == 'rpc') {
		openlog("MaYoR", LOG_PID | LOG_PERROR, LOG_LOCAL0);
		syslog(LOG_WARNING, '[RPC]MySQL: '.(json_encode($SET)).", query: $_q {$_SERVER['REMOTE_ADDR']} ({$_SERVER['HTTP_USER_AGENT']})");
		closelog();
	    }
	}
	if (
	    ($SET['detailed'] === true || __DETAILED)
	    && strpos($q_pattern, '%s') !== false 
	    && (strpos($q_pattern, '`%s`') === false && strpos($q_pattern, "'%s'") === false)
	) $_SESSION['alert'][] = 'message:lehet hiba?:db_query/'.$SET['fv'].':'.$SET['modul'].':'.$q_pattern;

	if ($SET['log']===true) mayorLogger(10,'mysql',$q,_USERACCOUNT);
	if (MYSQLI_ENABLED===true) {
	    $r = mysqli_query($lr,$q);
	    $_insert_id = mysqli_insert_id($lr); // itt lekérdezzük, hogy a warning lekérdezés ne rontsa el debug=true esetén!!
	} else {
    	    $r = @mysql_query($q, $lr);
	    $_insert_id = mysql_insert_id($lr);
	}
	define(MYSQL_LOGGER,false);
	if (MYSQL_LOGGER === true) {
	    $filename = '/tmp/mysql.log';
    	    $fp = fopen($filename, "a+");
    	    fputs ($fp, $q."\n");
    	    fclose ($fp);
	}
	/* WARNING HANDLER */
//	if ((isset($SET['debug']) && $SET['debug']===true) || (defined('__DEBUG') &&__DEBUG === true) || (defined('__DETAILED') && __DETAILED===true)) {
	if ((isset($SET['debug']) && $SET['debug']===true) || (defined('__DEBUG') &&__DEBUG === true)) {
	    if (MYSQLI_ENABLED===true)
		$warningCountResult = mysqli_query($lr,"SELECT @@warning_count");
	    else
		$warningCountResult = mysql_query("SELECT @@warning_count",$lr);
	    if ($warningCountResult) {
		if (MYSQLI_ENABLED===true)
		    $warningCount = mysqli_fetch_row($lr,$warningCountResult);
		else
		    $warningCount = mysql_fetch_row($warningCountResult);
		if ($warningCount[0] > 0) {
    		//Have warnings
		    if (MYSQLI_ENABLED===true)
    			$warningDetailResult = mysqli_query($lr, "SHOW WARNINGS");
		    else
    			$warningDetailResult = mysql_query("SHOW WARNINGS",$lr);
    		    if ($warningDetailResult ) {
			if (MYSQLI_ENABLED===true)
        		    while ($warning = mysqli_fetch_assoc($lr, $warningDetailResult)) {dump($warning); mayorLogger(2,'mysql',$q.' '.$warning,_USERACCOUNT);}
			else
        		    while ($warning = mysql_fetch_assoc($warningDetailResult)) {dump($warning); mayorLogger(2,'mysql',$q.' '.$warning,_USERACCOUNT);}
    		    }
		}//Else no warnings
	    }
	}
	/* WARNING HANDLER */
	if (!$r) {
            // if ($SET['detailed'] === true || __DETAILED) $_SESSION['alert'][] = 'message:sql_query_failure:'.$SET['fv'].':'.':'.$q;
            if ($SET['detailed'] === true || __DETAILED) {
		if (MYSQLI_ENABLED===true) {
		    $_SESSION['alert'][] = 'message:sql_query_failure:mysqli:'.$SET['fv'].':'.mysqli_error($lr).':'.$q;
		} else  {
		    $_SESSION['alert'][] = 'message:sql_query_failure:mysql:'.$SET['fv'].':'.mysql_error($lr).':'.$q;
		}
            } else {
		$_SESSION['alert'][] = 'message:sql_query_failure:'.$SET['fv'];
	    }
	    if ($SET['rollback'] === true) db_rollback($lr, $SET['fv']);
	    if (!isset($olr)) db_close($lr);
	    return false;
	}

	if (MYSQLI_ENABLED === true) {

	    if (in_array(substr(strtolower($q),0,4), array('sele','show','expl','(sel'))) {
        	$RESULT = array();
        	switch($SET['result']) {
                case 'indexed':
                    while ($A = mysqli_fetch_assoc($r)) $RESULT[] = $A;
                    break;
                case 'assoc':
                    while ($A = mysqli_fetch_assoc($r)) $RESULT[$A[$SET['keyfield']]] = $A;
                    break;
                case 'multiassoc':
                    while  ($A = mysqli_fetch_assoc($r)) $RESULT[$A[$SET['keyfield']]][] = $A;
                    break;
                case 'idonly':
                    while ($A = mysqli_fetch_row($r)) $RESULT[] = $A[0];
		    break;
                case 'value':
                    if (mysqli_num_rows($r) > 0) {
			$A = mysqli_fetch_row($r); $RESULT = $A[0];
		    } else { $RESULT = null; }
		    break;
                case 'record':
                    if (mysqli_num_rows($r) > 0) {
                	$A = mysqli_fetch_assoc($r); $RESULT = $A;
		    } else { $RESULT = null; }
		    break;
                case 'keyvaluepair':
                    while ($A = mysqli_fetch_row($r)) $RESULT[$A[0]] = $A[1];
                    break;
                case 'keyvalues':
                    while ($A = mysqli_fetch_row($r)) $RESULT[$A[0]][] = $A[1];
                    break;
        	}
    	    } elseif ($SET['result'] == 'insert' && (substr(strtolower($q),0,6) == 'insert' || substr(strtolower($q),0,7) == 'replace')) {
                $RESULT = $_insert_id; // mysqli_insert_id($lr); - ez itt már elromlik debug=true esetén a warning lekérdezés miatt
		mayorLogger(1,'mysql',$q,_USERACCOUNT);
	    } elseif ($SET['result'] == 'affected rows') {
		$RESULT = mysqli_affected_rows($lr);
		mayorLogger(1,'mysql',$q,_USERACCOUNT);
    	    } else { // create, insert, de nem olyan resulttal...
                $RESULT = $r;
		mayorLogger(1,'mysql',$q,_USERACCOUNT);
    	    }
	} else { // OLD DRIVER

	    if (in_array(substr(strtolower($q),0,4), array('sele','show','expl','(sel'))) {
        	$RESULT = array();
        	switch($SET['result']) {
                case 'indexed':
                    while ($A = mysql_fetch_assoc($r)) $RESULT[] = $A;
                    break;
                case 'assoc':
                    while ($A = mysql_fetch_assoc($r)) $RESULT[$A[$SET['keyfield']]] = $A;
                    break;
                case 'multiassoc':
                    while  ($A = mysql_fetch_assoc($r)) $RESULT[$A[$SET['keyfield']]][] = $A;
                    break;
                case 'idonly':
                    while ($A = mysql_fetch_row($r)) $RESULT[] = $A[0];
		    break;
                case 'value':
                    if (mysql_num_rows($r) > 0) {
			$A = mysql_fetch_row($r); $RESULT = $A[0];
		    } else { $RESULT = null; }
		    break;
                case 'record':
                    if (mysql_num_rows($r) > 0) {
                	$A = mysql_fetch_assoc($r); $RESULT = $A;
		    } else { $RESULT = null; }
		    break;
                case 'keyvaluepair':
                    while ($A = mysql_fetch_row($r)) $RESULT[$A[0]] = $A[1];
                    break;
                case 'keyvalues':
                    while ($A = mysql_fetch_row($r)) $RESULT[$A[0]][] = $A[1];
                    break;
        	}
    	    } elseif ($SET['result'] == 'insert' && (substr(strtolower($q),0,6) == 'insert' || substr(strtolower($q),0,7) == 'replace')) {
                $RESULT = $_insert_id; // mysql_insert_id($lr); - ez itt már elromlik debug=true esetén a warning lekérdezés miatt
		mayorLogger(1,'mysql',$q,_USERACCOUNT);
	    } elseif ($SET['result'] == 'affected rows') {
		$RESULT = mysql_affected_rows($lr);
		mayorLogger(1,'mysql',$q,_USERACCOUNT);
    	    } else { // create, insert, de nem olyan resulttal...
                $RESULT = $r;
		mayorLogger(1,'mysql',$q,_USERACCOUNT);
    	    }
	} // DRIVER

	if (!isset($olr)) db_close($lr);
	return $RESULT;

    }

//-----------------------------------------------------------------------//

    function db_start_trans($lr) {
        if ($lr != '') 
	    if (MYSQLI_ENABLED === true)
		mysqli_query($lr, "START TRANSACTION");
	    else
		mysql_query("START TRANSACTION", $lr);
    }

    function db_commit($lr) {
        if ($lr != '') 
	    if (MYSQLI_ENABLED === true)
		mysqli_query($lr, "COMMIT");
	    else
		mysql_query("COMMIT", $lr);
    }

    function db_rollback($lr, $msg = '') {
        if ($lr != '') {
            $_SESSION['alert'][] = 'message:rollback:'.$msg;
	    if (MYSQLI_ENABLED === true)
    		mysqli_query($lr, "ROLLBACK");
	    else
    		mysql_query("ROLLBACK", $lr);
        }
    }

//---------------------------------------------------------------------//

    function db_escape_string($str, $olr = null) {

	if (isset($olr)) $lr = $olr;
	else $lr = db_connect('login');

	if (!$lr) return false;

        if(get_magic_quotes_gpc()) {
            $return = mysql_real_escape_string(stripslashes($str), $lr);
        } else {
	    if (MYSQLI_ENABLED===true)
        	$return = mysqli_real_escape_string($lr, $str);
	    else
        	$return = mysql_real_escape_string($str, $lr);
        }
        if (!isset($olr)) db_close($lr);

	return $return;
    }

?>
