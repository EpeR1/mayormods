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
        $cf .= "graph_vlabel db/fő\n";
        $cf .= "graph_category mayor\n";
        $cf .= "graph_info Naplo users\n";

        $cf .= "mayor_ip_sum.label Összes\n";
        $cf .= "mayor_ip_sum.draw AREA\n";
        $cf .= "mayor_ip_sum.info Összes bejelentkezett felhasználó\n";

        $cf .= "mayor_ip_k.label Külső IP\n";
        $cf .= "mayor_ip_k.draw AREA\n";
        $cf .= "mayor_ip_k.info Külső IP címről\n";

        $cf .= "mayor_ip_b.label Belső IP\n";
        $cf .= "mayor_ip_b.draw AREA\n";
        $cf .= "mayor_ip_b.info Belső IP címről\n";

        $cf .= "mayor_p_pri.label Policy pri.\n";
        $cf .= "mayor_p_pri.draw LINE1.2\n";
        $cf .= "mayor_p_pri.info Policy privát\n";

        $cf .= "mayor_p_par.label Policy par.\n";
        $cf .= "mayor_p_par.draw LINE1.2\n";
        $cf .= "mayor_p_par.info Policy parent\n";

        $cf .= "mayor_a_t1.label Aktív: ".$set['t_active']."perc\n";
        $cf .= "mayor_a_t1.draw AREA\n";
        $cf .= "mayor_a_t1.info Aktív felhasználók az elmúlt ".$set['t_active']."percben\n";

        $cf .= "mayor_a_t2.label Aktív: ".($set['t_active']*2)."perc\n";
        $cf .= "mayor_a_t2.draw LINE1\n";
        $cf .= "mayor_a_t2.info Aktív felhasználók az elmúlt ".($set['t_active']*2)."percben\n";

        $cf .= "mayor_a_tt.label Aktív: tétlen\n";
        $cf .= "mayor_a_tt.draw LINE1\n";
		$cf .= "mayor_a_tt.info Tétlen, de bejelentkezett felhasználók\n";
		
		$cf .= "mayor_villam.label Villámgyors\n";
        $cf .= "mayor_villam.draw LINE1\n";
		$cf .= "mayor_villam.info Azok a villámgyors felhasználók, akik kevesebb mint 5 perc alatt, be, és ki is jelentkeztenk.\n";
		
		$cf .= "mayor_s_len.label Sikertelen\n";
        $cf .= "mayor_s_len.draw LINE1\n";
		$cf .= "mayor_s_len.info Sikertelen bejelentkezések\n";
	
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
		$ret['villam'] = 0;
		$ret['s_len'] = 0;
	    $l = mysqli_connect($set['db_host'], $set['db_user'], $set['db_pass'], $set['db_db']);
	    if(!$l){
			// echo "hiba\n ";
			$ret['ip_b'] = "U";
			$ret['ip_k'] = "U";
			$ret['p_pri'] = "U";
			$ret['p_par'] = "U";
			$ret['a_t1'] = "U";
			$ret['a_t2'] = "U";
			$ret['a_tt'] = "U";
			$ret['villam'] = "U";
			$ret['s_len'] = "U";
	    } else{
			mysqli_set_charset($l, "utf8");
			$r = mysqli_query($l,"SELECT session.userAccount,session.policy, UNIX_TIMESTAMP(session.activity) AS activity, loginLog.ip
									FROM mayor_login.session, mayor_login.loginLog
									WHERE UNIX_TIMESTAMP(loginLog.dt) >= (UNIX_TIMESTAMP()-36000) 
										AND ( ABS(UNIX_TIMESTAMP(session.dt) - UNIX_TIMESTAMP(loginLog.dt))<2 
										AND session.userAccount=loginLog.userAccount )  AND loginLog.flag=0 
									GROUP BY session.sessionId ORDER BY activity ;"
								);
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

				$ret['ip_sum']++;		//Mindenki
			}
			@mysqli_free_result($r);

			$r = mysqli_query($l,"SELECT COUNT(*) AS cnt FROM loginLog
									WHERE UNIX_TIMESTAMP(dt) >= (UNIX_TIMESTAMP() - 300) 
										AND userAccount NOT IN (SELECT userAccount FROM session) 
										AND flag=0 
									ORDER BY dt desc ;"
								);
			$ret['villam'] = mysqli_fetch_array($r, MYSQLI_ASSOC)['cnt'];
			$ret['ip_sum'] += $ret['villam'];
			@mysqli_free_result($r);


			$r = mysqli_query($l,"SELECT COUNT(*) AS cnt FROM loginLog
									WHERE UNIX_TIMESTAMP(dt) >= (UNIX_TIMESTAMP() - 300) 
										AND userAccount NOT IN (SELECT userAccount FROM session) 
										AND flag!=0 
									ORDER BY dt desc ;"
								);
			$ret['s_len'] = mysqli_fetch_array($r, MYSQLI_ASSOC)['cnt'];
			@mysqli_free_result($r);
		}
	    
	    @mysqli_close($l);
	    echo "mayor_ip_sum.value ".($ret['ip_sum'])."\n".  "mayor_ip_k.value ".$ret['ip_k']."\n".  "mayor_ip_b.value ".$ret['ip_b']."\n" ;
	    echo "mayor_p_pri.value ".$ret['p_pri']."\n".  "mayor_p_par.value ".$ret['p_par']."\n";
		echo "mayor_a_t1.value ".$ret['a_t1']."\n".  "mayor_a_t2.value ".$ret['a_t2']."\n".  "mayor_a_tt.value ".$ret['a_tt']."\n" ;
		echo "mayor_villam.value ".$ret['villam']."\n".  "mayor_s_len.value ".$ret['s_len']."\n";
	
	} else{	// függvény/PHP hiány külön kezelve
		echo "mayor_ip_sum.value U\n".  "mayor_ip_b.value U\n".  "mayor_ip_k.value U\n" ;
		echo "mayor_p_pri.value U\n".  "mayor_p_par.value U\n";
		echo "mayor_a_t1.value U\n".  "mayor_a_t2.value U\n".  "mayor_a_tt.value U\n";
		echo "mayor_villam.value U\n".  "mayor_s_len.value U\n";
	}
}

?>