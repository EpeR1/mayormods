<?php

    if (_RIGHTS_OK !== true) die();

    require_once('include/modules/naplo/share/diak.php');
    require_once('include/modules/naplo/share/kereso.php');

    $ADAT['tipus'] = readVariable($_POST['tipus'],'enum',null,array('diak','tanar','szulo'));
    $ADAT['pattern'] = readVariable($_POST['pattern'],'string',null);
    $ADAT['strict'] = readVariable($_POST['strict'],'id',null);

    if ($ADAT['pattern']!='') {
	// search
	if ($ADAT['tipus']=='diak') $ADAT['diakok']=getDiakokByPattern($ADAT['pattern']);
	elseif ($ADAT['tipus']=='tanar') $ADAT['tanarok']=getTanarokByPattern($ADAT['pattern']);
	elseif ($ADAT['tipus']=='szulo') $ADAT['szulok']=getSzulokByPattern($ADAT['pattern'],array('diakokkal'=>true));
    }

    if ($ADAT['strict']==1) {
	$_JSON = $ADAT;
    } else {
	$_JSON[] = $ADAT; // hozzáfűzzük valami random indexszel?!?!
    }

?>