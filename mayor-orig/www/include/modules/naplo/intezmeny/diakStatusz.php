<?php

    function getHibasJogviszony() {

	$q = "select distinct diakId from diakJogviszony as d1 where d1.statusz=(select statusz from diakJogviszony as d2 
		where diakId=d1.diakId and d2.dt<d1.dt order by dt desc limit 1) order by diakId, dt";
	$ret['tobbszoros'] = db_query($q, array('fv' => 'getHibasJogviszony', 'modul' => 'naplo_intezmeny', 'result' => 'idonly'));

	return $ret;
    }

?>
