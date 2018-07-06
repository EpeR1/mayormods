<?php                                                                                                                                                                                                           
    function ujKosziDiak($ADAT) {
/*                                                                                                                                                                                                           
| Field        | Type             | Null | Key | Default             | Extra |
+--------------+------------------+------+-----+---------------------+-------+
| kosziId      | int(10) unsigned | NO   | PRI | NULL                |       |
| diakId       | int(10) unsigned | NO   | PRI | NULL                |       |
| rogzitesDt   | timestamp        | NO   |     | CURRENT_TIMESTAMP   |       |
| jovahagyasDt | timestamp        | NO   |     | 0000-00-00 00:00:00 |       |
| kosziPontId  | int(10) unsigned | NO   | MUL | NULL                |       |
| pont         | int(10) unsigned | NO   |     | NULL                |       |
+--------------+------------------+------+-----+---------------------+-------+
*/                                                                                       

	// check  a hibajelzés kedvéért, ilyet amúgy sem tudnánk beírni az adatbázisba
    	$q = "SELECT count(*) as db FROM kosziDiak WHERE kosziId=%u AND diakId=%u ";
    	$v = array($ADAT['kosziId'],$ADAT['diakId']);
	$db = db_query($q, array('modul'=>'naplo','fv'=>'koszi_ujKosziDiak','values'=>$v, 'result'=>'value'));

	if ($db>0) {
	    $_SESSION['alert'][] = 'info:koszi_dup';
	    return false;
	}


	$q = "SELECT kosziPont FROM kosziPont WHERE kosziPontId=%u";
        $v = array($ADAT['kosziPontId']);
        $pont = db_query($q, array('modul'=>'naplo_intezmeny','fv'=>'koszi_ujKosziDiak','values'=>$v, 'result'=>'value'));
        

	if (is_numeric($pont) && is_numeric($ADAT['kosziId']) && is_numeric($ADAT['diakId'])) {
    	    $q = "INSERT INTO kosziDiak (kosziId,diakId,kosziPontId,pont) VALUES (%u,%u,%u,%u)";
    	    $v = array($ADAT['kosziId'],$ADAT['diakId'],$ADAT['kosziPontId'],$pont);
	    return db_query($q, array('modul'=>'naplo','fv'=>'koszi_ujKosziDiak','values'=>$v, 'result'=>'insert'));                                                                                   
	} else {
	    return false;
	}
                                                                                                                                                                                                           
    }
?>                                    
