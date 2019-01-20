<?php

    function isOwner($hirId) {
	if ($hirId=='') return false;
	$q = "SELECT owner FROM hirek WHERE id=%u";
	$r = db_query($q,array('modul'=>'portal','result'=>'value','values'=>array($hirId)));
	return ($r === _USERACCOUNT);
    }

    function getKategoriak() {
	$q = "SELECT * FROM kategoriak ORDER BY leiras";
	$r = db_query($q,array('modul'=>'portal','result'=>'indexed'));
	return $r;
    }

    function getHirek($SET = array('all'=>true,'tolDt'=>'', 'igDt'=>'', 'id' => '', 'flag'=>array(), 'class'=>array(), 'cid'=>array(), 'limit'=>'', 'lang'=>'hu_HU') ) {

	$tolDt = $SET['tolDt']; $igDt = $SET['igDt'];
	if ($tolDt!='') $W[] = "kdt<='$tolDt'";
	if ($igDt!='')  $W[] = "vdt>='$igDt'";
	if (count($SET['flag'])>0) $W[] = "flag IN (".implode(',',$SET['flag']).")";
	if (count($SET['class'])>0) $W[] = "class IN (".implode(',',$SET['class']).")";
	if (count($SET['cid'])>0) $W[] = "cid IN (".implode(',',$SET['cid']).")";
	if ($SET['lang']!='')  $W[] = "lang='".$SET['lang']."'";

	if ($SET['limit']!='') $L = ' LIMIT '.$SET['limit'];
	if ($SET['id']!='') $W[] = 'id='.$SET['id'];

	if ($SET['id']=='') {
	    if (is_array($SET['csoport'])) {
		for ($i=0; $i<count($SET['csoport']); $i++) {
		    $GW[] = "csoport like '%".$SET['csoport'][$i]."%'";
		}
		if (count($GW)>0) $WOR = ' OR '.implode(' OR ',$GW);
	    } else {
		$WOR = '';
	    }
	    $W[] = '(csoport="" '.$WOR.')';
	}
	if (is_array($W) && count($W)>0) {
	    $WHERE = "WHERE ".implode(' AND ',$W);
	} else $WHERE = '';

	//hack
	if ($SET['all']===true) $WHERE='';
	$q = "SELECT * FROM hirek $WHERE ORDER BY kdt DESC,vdt DESC".$L;
	$HIREK['szovegek'] = db_query($q, array('modul'=>'portal','result'=>'indexed'));
	return $HIREK;
    }

?>
