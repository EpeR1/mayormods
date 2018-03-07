#!/usr/bin/env php
<?php


$set['db_user'] = "mayor-munin";
$set['db_pass'] = "";
$set['db_db']	= "mayor_login";
$set['db_host'] = "localhost";
$set['naplo_host'] = "mayor.iskolaneve.hu";
$set['t_active'] = "10";   //pl: 10 perc (Az elmúlt x percben aktívak voltak)
$ret = array();


if (isset($argv[1]) and $argv[1] == "config"){

        $cf  = "host_name ".$set['naplo_host']."\n";
        $cf .= "graph_title Napló rendszerterhelés (Mayor)\n";
        $cf .= "graph_args --base 1000 \n";
        $cf .= "graph_vlabel fő\n";
        $cf .= "graph_category mayor\n";
        $cf .= "graph_info Naplo users\n";

        $cf .= "mayor_ip_sum.label Összes\n";
        $cf .= "mayor_ip_sum.draw LINE2\n";
        $cf .= "mayor_ip_sum.info Összes\n";

        $cf .= "mayor_ip_b.label Belső IP\n";
        $cf .= "mayor_ip_b.draw LINE2\n";
        $cf .= "mayor_ip_b.info Belső IP\n";

        $cf .= "mayor_ip_k.label Kulső IP\n";
        $cf .= "mayor_ip_k.draw LINE2\n";
        $cf .= "mayor_ip_k.info Kulső IP\n";
        
        $cf .= "mayor_p_pri.label Policy pri.\n";
        $cf .= "mayor_p_pri.draw LINE1.2\n";
        $cf .= "mayor_p_pri.info Policy pri.\n";

        $cf .= "mayor_p_par.label Policy par.\n";
        $cf .= "mayor_p_par.draw LINE1.2\n";
        $cf .= "mayor_p_par.info Policy par.\n";

        $cf .= "mayor_a_t1.label Aktív: ".$set['t_active']."perc\n";
        $cf .= "mayor_a_t1.draw LINE2\n";
        $cf .= "mayor_a_t1.info Aktív: ".$set['t_active']."perc\n";

        $cf .= "mayor_a_t2.label Aktív: ".($set['t_active']*2)."perc\n";
        $cf .= "mayor_a_t2.draw LINE1.2\n";
        $cf .= "mayor_a_t2.info Aktív: ".($set['t_active']*2)."perc\n";

        $cf .= "mayor_a_tt.label Aktív: tétlen\n";
        $cf .= "mayor_a_tt.draw LINE1.2\n";
        $cf .= "mayor_a_tt.info Aktív: tétlen\n";
	
	echo iconv("UTF-8", "ISO-8859-2", $cf), PHP_EOL;

} else {

	if (function_exists('mysqli_connect') and PHP_MAJOR_VERSION >= 7 ) { //MySQLi (Improved) és php7  kell!!!

	    $ret['ip_b'] = 0;
	    $ret['ip_k'] = 0;
	    $ret['p_pri'] = 0;
	    $ret['p_par'] = 0;
	    $ret['a_t1'] = 0;
	    $ret['a_t2'] = 0;
	    $ret['a_tt'] = 0;
	    $l = mysqli_connect($set['db_host'], $set['db_user'], $set['db_pass'], $set['db_db']);
	    if(!$l){
//		echo "hiba\n ";
		$ret['ip_b'] = "U";
	    	$ret['ip_k'] = "U";
	    	$ret['p_pri'] = "U";
	    	$ret['p_par'] = "U";
	    	$ret['a_t1'] = "U";
	    	$ret['a_t2'] = "U";
		$ret['a_tt'] = "U";

	    } else{
		mysqli_set_charset($l, "utf8");
		$r = mysqli_query($l," SELECT session.userAccount,session.policy, UNIX_TIMESTAMP(session.activity) AS activity, loginLog.ip
					FROM mayor_login.session, mayor_login.loginLog
					WHERE session.dt = loginLog.dt AND session.userAccount=loginLog.userAccount AND loginLog.flag=0; ");
		
		while ($row = mysqli_fetch_array($r, MYSQLI_ASSOC)) {
	        	if($row['policy'] == "private") { 
				$ret['p_pri'] ++; 
			}
			if($row['policy'] == "parent") { 
				$ret['p_par'] ++; 
			}
	
	                if( $row['activity'] >= time()-$set['t_active']*60){  //Az elmúlt x percben aktívak voltak
				$ret['a_t1']++;
			} else if( $row['activity'] >= time()-$set['t_active']*2*60){	//Az elmúlt 2*x percben aktívak voltak
	                        $ret['a_t2']++;
	                } else {
				$ret['a_tt']++;
			}

			if( preg_match('/^((10\.)|(192\.168\.)|(172\.16\.)|(fd..\:)).*/', $row['ip']) ){
				$ret['ip_b']++ ;
			} else{
				$ret['ip_k']++ ;
			}
		}
	    }
	    
	    @mysqli_free_result($r);
	    @mysqli_close($l);
	    echo "mayor_ip_sum.value ".($ret['ip_b']+$ret['ip_k'])."\n".  "mayor_ip_b.value ".$ret['ip_b']."\n".  "mayor_ip_k.value ".$ret['ip_k']."\n" ;
	    echo "mayor_p_pri.value ".$ret['p_pri']."\n".  "mayor_p_par.value ".$ret['p_par']."\n";
	    echo "mayor_a_t1.value ".$ret['a_t1']."\n".  "mayor_a_t2.value ".$ret['a_t2']."\n".  "mayor_a_tt.value ".$ret['a_tt']."\n" ;
	
	} else{
		echo "mayor_ip_sum.value U\n".  "mayor_ip_b.value U\n".  "mayor_ip_k.value U\n" ;
		echo "mayor_p_pri.value U\n".  "mayor_p_par.value U\n";
		echo "mayor_a_t1.value U\n".  "mayor_a_t2.value U\n".  "mayor_a_tt.value U\n";
	}
}

?>