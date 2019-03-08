<?php

    function ujEsemeny($ADAT) {

/*
| kosziEsemenyId          | int(10) unsigned                                                                                                                | NO   | PRI | NULL    |       |
| kosziEsemenyNev         | varchar(50)                                                                                                                     | NO   |     | NULL    |       |
| kosziEsemenyLeiras      | varchar(255)                                                                                                                    | NO   |     | NULL    |       |
| kosziEsemenyTipus       | enum('iskolai rendezvény','DÖK rendezvény','tanulmányi verseny','sportverseny','foglalkozás','tevékenység','hiányzás')          | NO   |     | NULL    |       |
| kosziEsemenyIntervallum | tinyint(1) unsigned                                                                                                             | YES  |     | 0       |       |
*/
	$q = "INSERT INTO kosziEsemeny (kosziEsemenyNev,kosziEsemenyLeiras,kosziEsemenyTipus,kosziEsemenyIntervallum) VALUES ('%s','%s','%s',%u)";
	$v = array($ADAT['kosziEsemenyNev'],$ADAT['kosziEsemenyLeiras'],$ADAT['kosziEsemenyTipus'],0);

	return db_query($q, array('modul'=>'naplo_intezmeny','fv'=>'koszi_ujEsemeny','values'=>$v, 'result'=>'insert'));

    }

    function ujKosziPont($ADAT) {

	// kosziHelyezes NULL / 0 ??

	if ($ADAT['kosziPontTipus']=='') return false;

	$q = "INSERT INTO kosziPont (kosziEsemenyId,kosziPontTipus,kosziPont,kosziHelyezes) VALUES (%u,'%s',%u,%u)";
	$v = array($ADAT['kosziEsemenyId'],$ADAT['kosziPontTipus'],$ADAT['kosziPont'],intval($ADAT['kosziHelyezes']));

	return db_query($q, array('modul'=>'naplo_intezmeny','fv'=>'koszi_ujPont','values'=>$v, 'result'=>'insert'));

    }

    function ujKoszi($ADAT) {
/*
+----------------+-----------------------------------------+------+-----+---------+----------------+
| kosziId        | int(10) unsigned                        | NO   | PRI | NULL    | auto_increment |
| kosziEsemenyId | int(10) unsigned                        | NO   | MUL | NULL    |                |
| dt             | date                                    | YES  |     | NULL    |                |
| tanev          | smallint(5) unsigned                    | YES  |     | NULL    |                |
| felev          | tinyint(3) unsigned                     | YES  |     | NULL    |                |
| igazolo        | set('diák','tanár','osztályfőnök')      | YES  |     | NULL    |                |
+----------------+-----------------------------------------+------+-----+---------+----------------+
*/

	$keys = array('kosziEsemenyId','dt','igazolo');
	$pattern = array("%u","'%s'","'%s'");
	$v = array($ADAT['kosziEsemenyId'],$ADAT['dt'],$ADAT['igazolo']);

	if (!is_null($ADAT['targyId'])) { $keys[] = 'targyId'; $pattern[] = "%u"; $v[]=$ADAT['targyId'];}
	if (!is_null($ADAT['felev'])) { $keys[] = 'felev'; $pattern[] = "%u"; $v[]=$ADAT['felev'];}
	if (!is_null($ADAT['tolDt'])) { $keys[] = 'tolDt'; $pattern[] = "'%s'"; $v[]=$ADAT['tolDt'];}
	if (!is_null($ADAT['igDt'])) { $keys[] = 'igDt'; $pattern[] = "'%s'"; $v[]=$ADAT['igDt'];}

	$q = "INSERT INTO koszi (".implode(',',$keys).") VALUES (".implode(',',$pattern).")";
	return db_query($q, array('modul'=>'naplo','fv'=>'koszi_ujKoszi','values'=>$v, 'result'=>'insert'));

    }

    function delKoszi($kosziIds) {
	for ($i=0; $i<count($kosziIds); $i++) {
	    $kosziId = $kosziIds[$i];
	    $q = "DELETE FROM koszi WHERE kosziId=%u";
	    $v = array($kosziId);
	    db_query($q, array('modul'=>'naplo','fv'=>'koszi_del','values'=>$v, 'result'=>'delete'));
	}
    }

    function kosziIgazolo($kosziId, $IDK,$tipus) {

/*
| kosziId | int(10) unsigned | NO   | PRI | NULL    |       |
| diakId  | int(10) unsigned | NO   | PRI | NULL    |       |
*/

	if ($tipus=='Diak') $t = 'diak'; else $t='tanar';

	for ($i=0; $i<count($IDK); $i++) {
	    $q = "INSERT INTO kosziIgazolo".$tipus." (kosziId,".$t."Id) VALUES (%u,%u)";
	    $v = array($kosziId,$IDK[$i]);
	    db_query($q, array('modul'=>'naplo','fv'=>'koszi_ujKapcsolat','values'=>$v, 'result'=>'insert'));
	}

	return;

    }

?>
