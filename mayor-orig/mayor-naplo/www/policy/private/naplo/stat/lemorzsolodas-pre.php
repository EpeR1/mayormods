<?php

    global $ADAT,$TANEV;

    require_once('include/share/date/names.php');

    // legyen az alapértelmezett a mostani tanév és a mostani tanév okt 1-je
    $tanev = __TANEV;
    $dt = readVariable($_POST['dt'],'date',$_TANEV['szemeszter'][1]['zarasDt']);
    $dt_stamp = strtotime($dt);

    $q = "SELECT zarasDt,tanev,szemeszter FROM szemeszter WHERE statusz!='tervezett' ORDER BY zarasDt DESC";
    $_napok = db_query($q,array('modul'=>'naplo_intezmeny','result'=>'indexed'));
    for ($i=0; $i<count($_napok); $i++) {
	$_dt = $_napok[$i]['zarasDt'];
	$NAPOK[] = $_dt;
	if (strtotime($dt) <= strtotime($_dt)) {
	    $tanev = $_napok[$i]['tanev'];
	    $szemeszter = $_napok[$i]['szemeszter'];
	    $_valasztottIndex = $i;
	}
    }

    $ADAT['tanev'] = $tanev;
    $ADAT['szemeszter'] = $szemeszter;
    $ADAT['dt'] = $dt;
    $ADAT['elozoDt'] = $_napok[$_valasztottIndex+1]['zarasDt'];

    $ADAT['lemorzsolodas'] = getLemorzsolodas($ADAT);

    $TOOL['datumSelect'] = array(
        'tipus'=>'cella', 'post' => array(),
        'paramName' => 'dt', 'hanyNaponta' => 1,
        'napok' => $NAPOK
    );


?>
