<?php

    function saveHir($DATA) {
	global $LANGUAGES;

	$cim = $DATA['cim'];
	$txt = $DATA['txt'];
	$pic = $DATA['pic'];
	$owner = $DATA['owner'];
	$hirId = $DATA['hirId'];
	$kdt = $DATA['kdt'];
	$vdt = $DATA['vdt'];
	$flag = intval($DATA['flag']);
	$class = intval($DATA['class']);
	$lang = (in_array($DATA['lang'],$LANGUAGES)) ? $DATA['lang'] : _DEFAULT_LANG;
	if ($DATA['cid']>0) $cid = $DATA['cid']; // -- TODO!!!!
	$csoport = $DATA['csoport'];
	if ($hirId!='' && __HIREKADMIN == false ) {
	    $q = "SELECT owner FROM hirek WHERE owner='%s'";
	    $v = array($owner);
	    $_owner = db_query($q,array('modul'=>'portal','result'=>'value','values'=>$v));
	    $isOwner = ($_owner===$owner);
	} else $isOwner = false;
	if ($hirId=='') {
	    $keys = array('`pic`','`owner`','`cim`','`txt`','`kdt`','`vdt`','`flag`','`class`','`lang`','`csoport`');
	    $pattern = array("'%s'","'%s'","'%s'","'%s'","'%s'","'%s'","%u","%u","'%s'","'%s'");
	    $v = array($pic,$owner,$cim,$txt,$kdt,$vdt,$flag,$class,$lang,$csoport);
	    if (isset($cid)) {
		$keys[] = 'cid';
		$pattern[] = '%u';
		$v[] = $cid;
	    }
	    $q = "insert INTO `hirek` (".implode(',',$keys).") VALUES (".implode(',',$pattern).")";
	} elseif (isset($hirId) && (__HIREKADMIN || $isOwner)) {
	    if (isset($cid)) {
		$q = "update `hirek` SET pic='%s', cim='%s', txt='%s',kdt='%s',vdt='%s',flag=%u,class=%u,lang='%s',csoport='%s',cid=%u WHERE id=%u";
		$v = array($pic,$cim,$txt,$kdt,$vdt,$flag,$class,$lang,$csoport, $cid, $hirId);
	    } else {
		$q = "update `hirek` SET pic='%s', cim='%s', txt='%s',kdt='%s',vdt='%s',flag=%u,class=%u,lang='%s',csoport='%s' WHERE id=%u";
		$v = array($pic,$cim,$txt,$kdt,$vdt,$flag,$class,$lang,$csoport, $hirId);
	    }
	} else $q = '';
	if ($q!='') $r = db_query($q,array('modul'=>'portal','result'=>'insert','values'=>$v));
	if (is_array($DATA['kategoriaId'])) {
	    for ($i=0; $i<count($DATA['kategoriaId']); $i++) {
		$q = "INSERT IGNORE INTO `hirKategoria` (hirId,kategoriaId) VALUES (%u,%u)";
		$v = array($hirId,$DATA['kategoriaId'][$i]);
		db_query($q,array('modul'=>'portal','result'=>'insert','values'=>$v));
	    }
	}
	return $r;
    }

?>
